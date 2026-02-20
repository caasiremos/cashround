<?php

namespace App\Repositories;

use App\Exceptions\ExpectedException;
use App\Jobs\SendVerificationCodeEmailJob;
use App\Models\GeneralLedgerAccount;
use App\Models\Group;
use App\Models\Member;
use App\Models\Notification;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

class MemberRepository
{
    /**
     * Get the wallet balance of the member
     *
     * @param Member $member
     * @return array
     */
    public function getWalletBalance(Member $member): array
    {
        return [
            'balance' => $member->wallet->balance,
            'account_number' => $member->wallet->account_number
        ];
    }
    /**
     * Get all members of a group
     *
     * @param Group $group
     * @return Collection
     */
    public function getGroupMembers(Group $group): Collection
    {
        return $group->members;
    }

    /**
     * Get a member by id
     *
     * @param int $id
     * @return Member|null
     */
    public function getMemberById(int $id): ?Member
    {
        return Member::find($id);
    }

    /**
     * Create a new member
     *
     * @param array $data
     * @return Member
     */
    public function createMember(array $data): Member
    {
        return DB::transaction(function () use ($data) {
            $member = Member::create($data);
            $member->wallet()->create();
            $verificationCode = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $member->update([
                'verification_code' => $verificationCode,
                'verification_code_expires_at' => now()->addMinutes(15),
            ]);

            SendVerificationCodeEmailJob::dispatch($member);

            return $member;
        });
    }
    /**
     * Confirm a verification code
     *
     * @param Member $member
     * @param int $code
     * @return Member
     */
    public function confirmVerificationCode(Request $request): Member
    {
        $member = Member::query()->where('email', $request->email)->first();
        if (!$member) {
            throw new ExpectedException('Member not found');
        }
        if ($member->verification_code !== $request->code) {
            throw new ExpectedException('Invalid verification code');
        }

        if ($member->verification_code_expires_at < now()) {
            throw new ExpectedException('Verification code expired');
        }

        $member->update([
            'email_verified_at' => now(),
        ]);

        return $member;
    }

    /**
     * Get all notifications for a member
     *
     * @return Collection
     */
    public function getMemberNotifications(): Collection
    {
        return Notification::where('member_id', auth()->user()->id)
            ->orderBy('created_at', 'DESC')
            ->limit(20)
            ->get();
    }

    /**
     * Read a notification for a member
     *
     * @return Notification
     */
    public function readMemberNotification(Request $request): Notification
    {
        $notification = Notification::where('id', $request->id)->where('member_id', auth()->user()->id)->first();
        if (!$notification) {
            throw new ExpectedException('Notification not found');
        }
        $notification->update(['is_read' => true]);

        return $notification;
    }
}
