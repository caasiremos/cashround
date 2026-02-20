<?php

namespace App\Services;

use App\Exceptions\ExpectedException;
use App\Models\Group;
use App\Models\Member;
use App\Models\Notification;
use App\Models\Wallet;
use App\Repositories\MemberRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class MemberService
{
    public function __construct(
        protected MemberRepository $memberRepository,
    ) {}

    /**
     * Get a member by account number
     */
    public function getMemberByAccountNumber(string $accountNumber): Member
    {
        $accountNumber = trim($accountNumber);

        $wallet = Wallet::where('account_number', $accountNumber)
            ->whereNull('group_id')
            ->first();

        if (! $wallet) {
            throw new ExpectedException('Member with account number not found');
        }

        return $wallet->member;
    }

    /**
     * Get the wallet balance of the member
     */
    public function getWalletBalance(Member $member): array
    {
        return $this->memberRepository->getWalletBalance($member);
    }

    /**
     * Create a new member
     *
     * @return Member
     */
    public function createMember(array $data)
    {
        return $this->memberRepository->createMember($data);
    }

    /**
     * Confirm a verification code
     */
    public function confirmVerificationCode(Request $request): Member
    {
        if (! $request->has('code')) {
            throw new ExpectedException('Verification code is required');
        }

        return $this->memberRepository->confirmVerificationCode($request);
    }

    /**
     * Get all members of a group
     */
    public function getGroupMembers(Group $group): Collection
    {
        return $this->memberRepository->getGroupMembers($group);
    }

    /**
     * Get a member by id
     */
    public function getMemberById(int $id): ?Member
    {
        return $this->memberRepository->getMemberById($id);
    }

    /**
     * Get all notifications for a member
     */
    public function getMemberNotifications(): Collection
    {
        return $this->memberRepository->getMemberNotifications();
    }

    /**
     * Read a notification for a member
     */
    public function readMemberNotification(Request $request): Notification
    {
        return $this->memberRepository->readMemberNotification($request);
    }

    /**
     * Forgot password
     */
    public function forgotPassword(Request $request): Member
    {
        return $this->memberRepository->forgotPassword($request);
    }

    /**
     * Reset password
     */
    public function resetPassword(Request $request): Member
    {
        return $this->memberRepository->resetPassword($request);
    }
}
