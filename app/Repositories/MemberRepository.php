<?php

namespace App\Repositories;

use App\Exceptions\ExpectedException;
use App\Jobs\SendVerificationCodeEmailJob;
use App\Models\GeneralLedgerAccount;
use App\Models\Group;
use App\Models\Member;
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
     * @return float
     */
    public function getWalletBalance(Member $member): float
    {
        return $member->wallet->balance;
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
            $this->createGeneralLedgerAccount($member);

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
     * Create a new general ledger account for a member
     *
     * @param Member $member
     * @return GeneralLedgerAccount
     */
    public function createGeneralLedgerAccount(Member $member): GeneralLedgerAccount
    {
        return $member->generalLedgerAccounts()->create([
            'name' => 'ACC - ' . $member->first_name . ' ' . $member->last_name,
            'slug' => Str::slug('ACC - ' . $member->first_name . ' ' . $member->last_name) . '-' . $member->id,
            'account_type' => 'liability',
            'wallet_id' => $member->wallet->id,
            'member_id' => $member->id
        ]);
    }
}