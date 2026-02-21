<?php

namespace App\Repositories;

use App\Exceptions\ExpectedException;
use App\Jobs\SendResetPasswordEmailJob;
use App\Jobs\SendVerificationCodeEmailJob;
use App\Models\Group;
use App\Models\Member;
use App\Models\MemberPasswordResetToken;
use App\Models\Notification;
use App\Models\Wallet;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class MemberRepository
{
    /**
     * Get the wallet balance of the member
     */
    public function getWalletBalance(Member $member): array
    {
        return [
            'balance' => $member->wallet->balance,
            'account_number' => $member->wallet->account_number,
        ];
    }

    /**
     * Get all members of a group
     */
    public function getGroupMembers(Group $group): Collection
    {
        return $group->members;
    }

    /**
     * Get a member by id
     */
    public function getMemberById(int $id): ?Member
    {
        return Member::find($id);
    }

    /**
     * Create a new member
     */
    public function createMember(array $data): Member
    {
        return DB::transaction(function () use ($data) {
            $member = Member::create($data);
            $member->wallet()->create([
                'member_id' => $member->id,
                'account_number' => 'CRM' . str_pad(Wallet::max('id') + 1, 5, '0', STR_PAD_LEFT),
                'balance' => 0,
            ]);
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
     * @throws ExpectedException
     */
    public function confirmVerificationCode(Request $request): Member
    {
        $member = Member::query()->where('email', $request->email)->first();
        if (! $member) {
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
     */
    public function readMemberNotification(Request $request): Notification
    {
        $notification = Notification::where('id', $request->id)->where('member_id', auth()->user()->id)->first();
        if (! $notification) {
            throw new ExpectedException('Notification not found');
        }
        $notification->update(['is_read' => true]);

        return $notification;
    }

    /**
     * Forgot password
     */
    public function forgotPassword(Request $request): Member
    {
        $member = Member::query()->where('email', $request->email)->first();
        if (! $member) {
            throw new ExpectedException('Email not found');
        }
        $token = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        MemberPasswordResetToken::updateOrCreate(
            [
                'email' => $request->email,
            ],
            [
                'token' => $token,
                'expires_at' => now()->addHours(1),
            ]);
        SendResetPasswordEmailJob::dispatch($request->email, $token);

        return $member;
    }

    /**
     * Reset password
     */
    public function resetPassword(Request $request): Member
    {
        $token = MemberPasswordResetToken::query()->where('token', $request->token)->first();
        if (! $token) {
            throw new ExpectedException('Invalid token');
        }
        if ($token->expires_at < now()) {
            throw new ExpectedException('Token expired');
        }
        $member = Member::query()->where('email', $token->email)->first();
        if (! $member) {
            throw new ExpectedException('Email not found');
        }
        $member->password = Hash::make($request->password);
        $member->save();
        $token->delete();

        return $member;
    }
}
