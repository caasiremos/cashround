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
    ) {
    }
    /**
     * Get a member by account number
     *
     * @param string $accountNumber
     * @return Member
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
     *
     * @param Member $member
     * @return array
     */
    public function getWalletBalance(Member $member): array
    {
        return $this->memberRepository->getWalletBalance($member);
    }
    /**
     * Create a new member
     *
     * @param array $data
     * @return Member
     */
    public function createMember(array $data)
    {
        return $this->memberRepository->createMember($data);
    }

    /**
     * Confirm a verification code
     *
     * @param Request $request
     * @return Member
     */
    public function confirmVerificationCode(Request $request): Member
    {
        if(!$request->has('code')) {
            throw new ExpectedException('Verification code is required');
        }
        return $this->memberRepository->confirmVerificationCode($request);
    }

    /**
     * Get all members of a group
     *
     * @param Group $group
     * @return Collection
     */
    public function getGroupMembers(Group $group): Collection
    {
        return $this->memberRepository->getGroupMembers($group);
    }

    /**
     * Get a member by id
     *
     * @param int $id
     * @return Member
     */
    public function getMemberById(int $id): ?Member
    {
        return $this->memberRepository->getMemberById($id);
    }

    /**
     * Get all notifications for a member
     *
     * @return Collection
     */
    public function getMemberNotifications(): Collection
    {
        return $this->memberRepository->getMemberNotifications();
    }

    /**
     * Read a notification for a member
     *
     * @param Request $request
     * @return Notification
     */
    public function readMemberNotification(Request $request): Notification
    {
        return $this->memberRepository->readMemberNotification($request);
    }
}
