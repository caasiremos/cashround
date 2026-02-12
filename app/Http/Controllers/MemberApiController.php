<?php

namespace App\Http\Controllers;

use App\Http\Requests\MemberFormRequest;
use App\Http\Responses\ApiErrorResponse;
use App\Services\MemberService;
use App\Http\Responses\ApiSuccessResponse;
use Illuminate\Http\Request;

class MemberApiController extends Controller
{
    public function __construct(
        private MemberService $memberService,
    ) {
    }

    /**
     * Create a new member
     *
     * @param Request $request
     * @return ApiSuccessResponse
     */
    public function register(MemberFormRequest $request)
    {
        $member = $this->memberService->createMember($request->all());

        return new ApiSuccessResponse($member, 'Member created successfully');
    }

    /**
     * Get all members of a group
     *
     * @param Request $request
     * @return ApiSuccessResponse
     */
    public function getGroupMembers(Request $request)
    {
        $members = $this->memberService->getGroupMembers($request->group);

        return new ApiSuccessResponse($members, 'Group members fetched successfully');
    }

    /**
     * Get a member by id
     *
     * @param Request $request
     * @return ApiSuccessResponse
     */
    public function getMemberById(Request $request)
    {
        $member = $this->memberService->getMemberById($request->id);

        if (!$member) {
            return new ApiErrorResponse('Member not found');
        }

        return new ApiSuccessResponse($member, 'Member fetched successfully');
    }
}
