<?php

namespace App\Services;

use App\Models\Group;
use App\Models\GroupRole;
use App\Models\Member;
use App\Models\TransactionAuth;
use App\Repositories\GroupRepository;
use Illuminate\Database\Eloquent\Collection;

class GroupService
{
    public function __construct(
        private GroupRepository $groupRepository,
    ) {
    }
    /**
     * Get the wallet balance of a group
     *
     * @param Group $group
     * @return array
     */
    public function getGroupWalletBalance(Group $group): array
    {
        return $this->groupRepository->getGroupWalletBalance($group);
    }

    /**
     * Get the groups of a member
     *
     * @param Member $member
     * @return Collection
     */
    public function getMemberGroups(Member $member): Collection
    {
        return $this->groupRepository->getMemberGroups($member);
    }

    /**
     * Create a new group
     *
     * @param array $data
     * @return Group
     */
    public function createGroup(array $data)
    {
        return $this->groupRepository->createGroup($data);
    }

    /**
     * Get a group by id
     *
     * @param int $id
     * @return Group
     */
    public function getGroupById(int $id)
    {
        $group = $this->groupRepository->getGroupById($id);

        if (!$group) {
            throw new \Exception('Group not found');
        }

        return $group;
    }

    /**
     * Get the members of a group
     *
     * @param Group $group
     * @return Collection
     */
    public function getGroupMembers(Group $group): Collection
    {
        return $this->groupRepository->getGroupMembers($group);
    }

    /**
     * Set the role of a member in a group
     *
     * @param array $data
     * @return GroupRole   
     */
    public function setMemberRole(array $data): GroupRole
    {
        return $this->groupRepository->setMemberRole($data);
    }

    /**
     * Remove the role of a member in a group
     *
     * @param int $groupId
     * @param int $memberId
     */
    public function removeMemberRole(int $groupId, int $memberId)
    {
        return $this->groupRepository->removeMemberRole($groupId, $memberId);
    }

    /**
     * Get all transaction auths for a group
     *
     * @param int $groupId
     * @return ?    TransactionAuth
     */
    public function getGroupTransactionAuth(int $groupId): ?TransactionAuth
    {
        return $this->groupRepository->getGroupTransactionAuth($groupId);
    }
}