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
     */
    public function sendInvite(GroupInviteRequest $request)
    {
        try {
            $invite = $this->groupInviteService->sendInvite($request->all());
            return new ApiSuccessResponse($invite, 'Invite code generated successfully');
        } catch (\Exception $e) {
            return new ApiErrorResponse($e->getMessage(), $e, code: 400);
        }

        return new ApiSuccessResponse($invite->load(['group', 'inviter']), 'Invite sent successfully');
    }

    /**
     * POST /invites/confirm-invite — Accept by invite id (user tapped Accept in app).
     */
    public function confirmInvite(Request $request)
    {
        try {
            $invite = $this->groupInviteService->confirmInvite($request->all());
        } catch (\Exception $e) {
            return new ApiErrorResponse($e->getMessage(), $e, code: 400);
        }

        return new ApiSuccessResponse($invite, 'Invite accepted successfully');
    }
}
