<?php

namespace App\Http\Controllers;

use App\Http\Requests\GroupFormRequest;
use App\Http\Responses\ApiSuccessResponse;
use Illuminate\Http\Request;
use App\Services\GroupService;

class GroupApiController extends Controller
{
    public function __construct(
        private GroupService $groupService,
    ) {
    }

    /**
     * Get the wallet balance of a group
     *
     * @param Request $request
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
     * @param Request $request
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
     * @param Request $request
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
     * @param Request $request
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
     * @param Request $request
     * @return ApiSuccessResponse
     */
    public function getGroupMembers(Request $request)
    {
        $members = $this->groupService->getGroupMembers($request->group);

        return new ApiSuccessResponse($members, 'Group members fetched successfully');
    }
}
