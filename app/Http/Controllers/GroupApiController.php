<?php

namespace App\Http\Controllers;

use App\Http\Requests\GroupFormRequest;
use App\Http\Responses\ApiSuccessResponse;
use App\Models\Group;
use App\Services\GroupService;
use Illuminate\Http\Request;

class GroupApiController extends Controller
{
    public function __construct(
        private GroupService $groupService,
    ) {}

    /**
     * Get the wallet balance of a group
     *
     * @param  Request  $request
     * @return ApiSuccessResponse
     */
    public function getGroupWalletBalance(Group $group)
    {
        $balance = $this->groupService->getGroupWalletBalance($group);

        return new ApiSuccessResponse($balance, 'Group wallet balance fetched successfully');
    }

    /**
     * Get the groups of a member
     *
     * @param  Request  $request
     * @return ApiSuccessResponse
     */
    public function getMemberGroups()
    {
        $groups = $this->groupService->getMemberGroups(auth()->user());

        return new ApiSuccessResponse($groups, 'Groups fetched successfully');
    }

    /**
     * Create a new group
     *
     * @param  Request  $request
     * @return ApiSuccessResponse
     */
    public function createGroup(GroupFormRequest $request)
    {
        $group = $this->groupService->createGroup($request->all());

        return new ApiSuccessResponse($group, 'Group created successfully');
    }

    /**
     * Get a group by id
     *
     * @return ApiSuccessResponse
     */
    public function getGroupById(Request $request)
    {
        $group = $this->groupService->getGroupById($request->group);

        return new ApiSuccessResponse($group, 'Group fetched successfully');
    }

    /**
     * Get the members of a group
     *
     * @return ApiSuccessResponse
     */
    public function getGroupMembers(Request $request)
    {
        $members = $this->groupService->getGroupMembers($request->group);

        return new ApiSuccessResponse($members, 'Group members fetched successfully');
    }

    /**
     * Set the role of a member in a group
     *
     * @return ApiSuccessResponse
     */
    public function setMemberRole(Request $request)
    {
        $member = $this->groupService->setMemberRole($request->all());

        return new ApiSuccessResponse($member, 'Member role set successfully');
    }

    /**
     * Remove the role of a member in a group
     *
     * @return ApiSuccessResponse
     */
    public function removeMemberRole(Request $request)
    {
        $member = $this->groupService->removeMemberRole($request->group_id, $request->member_id);

        return new ApiSuccessResponse($member, 'Member role removed successfully');
    }

    /**
     * Get all transaction auths for a group
     *
     * @return ApiSuccessResponse
     */
    public function getGroupTransactionAuth(Group $group)
    {
        $transactionAuth = $this->groupService->getGroupTransactionAuth($group->id);

        return new ApiSuccessResponse($transactionAuth, 'Transaction auths fetched successfully');
    }
}
