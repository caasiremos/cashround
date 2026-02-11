<?php

namespace App\Repositories;

use App\Models\Group;
use App\Models\Member;

class MemberRepository
{
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
     * @return Member
     */
    public function getMemberById(int $id): Member
    {
        return Member::find($id);
    }

    public function createMember(array $data): Member
    {
        $member = Member::create($data);
        $member->wallet()->create();
        return $member;
    }
}