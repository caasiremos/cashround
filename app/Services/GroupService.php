<?php

namespace App\Services;

use App\Models\Group;
use App\Repositories\GroupRepository;
use Illuminate\Database\Eloquent\Collection;

class GroupService
{
    public function __construct(
        private GroupRepository $groupRepository,
    ) {
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
}