<?php

namespace App\Services;

use App\Exceptions\ExpectedException;
use App\Models\Group;
use App\Models\GroupInvite;
use App\Models\Member;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class GroupInviteService
{
    /**
     * Send an invite to join a group. Only the group owner can invite (mobile: owner sends from app).
     */
    public function sendInvite(Group $group, Member $inviter, string $email, ?int $memberId = null): GroupInvite
    {
        if ((int) $group->owner_id !== (int) $inviter->id) {
            throw new \Exception('Only the group owner can send invites.');
        }

        $existing = GroupInvite::where('group_id', $group->id)
            ->where('email', $email)
            ->whereIn('status', ['pending'])
            ->first();

        if ($existing) {
            throw new ExpectedException('An invite has already been sent to this email for this group.');
        }

        if ($group->members()->where('members.id', $memberId ?? 0)->exists()) {
            throw new ExpectedException('This member is already in the group.');
        }

        return GroupInvite::create([
            'group_id' => $group->id,
            'inviter_id' => $inviter->id,
            'email' => $email,
            'member_id' => $memberId,
        ]);
    }

    /**
     * Pending invites for the authenticated member (by member_id or email). Used by mobile app to show "Invites" screen.
     */
    public function getPendingInvitesForMember(Member $member): Collection
    {
        return GroupInvite::query()
            ->where('status', 'pending')
            ->where(function ($q) use ($member) {
                $q->where('member_id', $member->id)
                    ->orWhere('email', $member->email);
            })
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->with(['group', 'inviter'])
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * Accept an invite (by id). Used when user taps Accept in the app.
     */
    public function acceptInvite(GroupInvite $invite, Member $member): GroupInvite
    {
        $this->assertInviteCanBeActedOn($invite, $member);

        return DB::transaction(function () use ($invite, $member) {
            $invite->group->members()->syncWithoutDetaching([$member->id]);
            $invite->update([
                'status' => 'accepted',
                'accepted_at' => now(),
                'member_id' => $member->id,
            ]);

            return $invite->fresh(['group', 'inviter']);
        });
    }

    /**
     * Decline an invite (by id).
     */
    public function declineInvite(GroupInvite $invite, Member $member): GroupInvite
    {
        $this->assertInviteCanBeActedOn($invite, $member);

        $invite->update(['status' => 'declined']);

        return $invite->fresh(['group', 'inviter']);
    }

    /**
     * Get invite by token (e.g. from deep link cashround://invite/{token}). Mobile can show preview then call acceptByToken when user confirms.
     */
    public function getInviteByToken(string $token): ?GroupInvite
    {
        $invite = GroupInvite::where('token', $token)
            ->with(['group', 'inviter'])
            ->first();

        if (!$invite || !$invite->isPending()) {
            return null;
        }

        return $invite;
    }

    /**
     * Accept an invite by token (after user opened app via deep link and is logged in).
     */
    public function acceptInviteByToken(string $token, Member $member): GroupInvite
    {
        $invite = GroupInvite::where('token', $token)->first();

        if (!$invite) {
            throw new \Exception('Invite not found.');
        }

        return $this->acceptInvite($invite, $member);
    }

    private function assertInviteCanBeActedOn(GroupInvite $invite, Member $member): void
    {
        if ($invite->status !== 'pending') {
            throw new \Exception('This invite is no longer valid.');
        }

        if ($invite->isExpired()) {
            $invite->update(['status' => 'expired']);
            throw new \Exception('This invite has expired.');
        }

        $isForMember = (int) $invite->member_id === (int) $member->id
            || strcasecmp($invite->email, $member->email) === 0;

        if (!$isForMember) {
            throw new \Exception('This invite was not sent to you.');
        }
    }
}
