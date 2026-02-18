<?php

namespace App\Http\Controllers;

use App\Http\Responses\ApiErrorResponse;
use App\Http\Responses\ApiSuccessResponse;
use App\Http\Requests\GroupInviteRequest;
use App\Services\GroupInviteService;
use Illuminate\Http\Request;

class GroupInviteApiController extends Controller
{
    public function __construct(
        private GroupInviteService $groupInviteService,
    ) {
    }

    /**
     * POST /invites/send-invite — Owner sends an invite (body: email, optional member_id).
     *
     * @param GroupInviteRequest $request
     * @return ApiSuccessResponse
     */
    public function sendInvite(GroupInviteRequest $request): ApiSuccessResponse
    {
        $invite = $this->groupInviteService->sendInvite($request->all());
        return new ApiSuccessResponse($invite, 'Invite code generated successfully');
    }

    /**
     * Accept an invite by invite code
     *
     * @param Request $request
     * @return ApiSuccessResponse
     */
    public function acceptInvite(Request $request): ApiSuccessResponse
    {
        $group = $this->groupInviteService->acceptInvite($request->invite_code);
        return new ApiSuccessResponse($group, 'Invite accepted successfully');
    }
}
