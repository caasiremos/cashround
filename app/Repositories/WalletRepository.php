<?php

namespace App\Repositories;

use App\Models\Group;
use App\Models\Member;
use App\Models\Wallet;

class WalletRepository
{
    public function getWalletByMember(Member $member): Wallet
    {
        return $member->wallet;
    }
    /**
     * Get the wallet of a group
     *
     * @param Group $group
     * @return Wallet
     */
    public function getGroupWallet(Group $group): Wallet
    {
        return $group->wallet;
    }

}