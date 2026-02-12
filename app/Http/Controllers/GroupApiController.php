<?php

namespace App\Http\Controllers;

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
     * Create a new group
     *
     * @param Request $request
     * @return ApiSuccessResponse
     */
    public function createGroup(Request $request)
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
