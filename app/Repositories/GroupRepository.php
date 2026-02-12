<?php

namespace App\Repositories;

use App\Models\GeneralLedgerAccount;
use App\Models\Group;
use App\Models\Wallet;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GroupRepository
{

    /**
     * Get the members of a group
     *
     * @param Group $group
     * @return Collection
     */
    public function getGroupMembers(Group $group): Collection
    {
        return $group->members;
    }
    
    /**
     * Create a new group
     *
     * @param array $data
     * @return Group
     */
    public function createGroup(array $data): Group
    {
        return DB::transaction(function () use ($data) {
            $group = Group::create([
                'owner_id' => auth()->user()->id,
                'name' => $data['name'],
                'frequency' => $data['frequency'],
                'start_date' => now(),
                'amount' => $data['amount'],
                'description' => $data['description'] ?? null,
                'slug' => Str::slug($data['name']) . '-' . Str::random(5),
            ]);
            $this->createGroupWallet($group);
            $this->createGroupGeneralLedgerAccount($group);

            return $group;
        });
    }  

    /**
     * Create a new wallet for a group
     *
     * @param Group $group
     * @return Wallet
     */
    public function createGroupWallet(Group $group): Wallet
    {
        return $group->wallet()->create();
    }

    /**
     * Create a new general ledger account for a group
     *
     * @param Group $group
     * @return GeneralLedgerAccount
     */
    public function createGroupGeneralLedgerAccount(Group $group): GeneralLedgerAccount
    {
        return $group->generalLedgerAccounts()->create([
            'name' => 'ACC - ' . $group->name,
            'slug' => Str::slug('ACC - ' . $group->name) . '-' . $group->id,
            'account_type' => 'asset',
            'wallet_id' => $group->wallet->id,
            'group_id' => $group->id
        ]);
    }

    /**
     * Get a group by id
     *
     * @param int $id
     * @return Group
     */
    public function getGroupById(int $id): Group
    {
        return Group::find($id);
    }
}