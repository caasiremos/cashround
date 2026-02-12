<?php

namespace App\Http\Controllers;

use App\Http\Responses\ApiErrorResponse;
use App\Http\Responses\ApiSuccessResponse;
use App\Models\Group;
use App\Models\GroupInvite;
use App\Services\GroupInviteService;
use Illuminate\Http\Request;

/**
 * Group invite API for mobile app.
 * - Owner sends invites from the app; invitee sees pending list and accepts/declines.
 * - Token is used for deep links (e.g. cashround://invite/{token}) to open the app and accept.
 */
class GroupInviteApiController extends Controller
{
    public function __construct(
        private GroupInviteService $groupInviteService,
    ) {
    }

    /**
     * GET /invites — Pending invites for the logged-in member (mobile "Invites" screen).
     */
    public function myPendingInvites(Request $request)
    {
        $invites = $this->groupInviteService->getPendingInvitesForMember($request->user());

        return new ApiSuccessResponse($invites, 'Pending invites fetched successfully');
    }

    /**
     * POST /groups/{group}/invites — Owner sends an invite (body: email, optional member_id).
     */
    public function sendInvite(Request $request, Group $group)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'member_id' => 'nullable|integer|exists:members,id',
        ]);

        try {
            $invite = $this->groupInviteService->sendInvite(
                $group,
                $request->user(),
                $validated['email'],
                $validated['member_id'] ?? null
            );
        } catch (\Exception $e) {
            return new ApiErrorResponse($e->getMessage(), $e, code: 400);
        }

        return new ApiSuccessResponse($invite->load(['group', 'inviter']), 'Invite sent successfully');
    }

    /**
     * POST /invites/{invite}/accept — Accept by invite id (user tapped Accept in app).
     */
    public function accept(Request $request, GroupInvite $invite)
    {
        try {
            $invite = $this->groupInviteService->acceptInvite($invite, $request->user());
        } catch (\Exception $e) {
            return new ApiErrorResponse($e->getMessage(), $e, code: 400);
        }

        return new ApiSuccessResponse($invite, 'Invite accepted successfully');
    }

    /**
     * POST /invites/{invite}/decline — Decline by invite id.
     */
    public function decline(Request $request, GroupInvite $invite)
    {
        try {
            $invite = $this->groupInviteService->declineInvite($invite, $request->user());
        } catch (\Exception $e) {
            return new ApiErrorResponse($e->getMessage(), $e, code: 400);
        }

        return new ApiSuccessResponse($invite, 'Invite declined');
    }

    /**
     * GET /invites/by-token/{token} — Resolve invite by token (e.g. from deep link). No auth required for preview.
     */
    public function getByToken(string $token)
    {
        $invite = $this->groupInviteService->getInviteByToken($token);

        if (!$invite) {
            return new ApiErrorResponse('Invite not found or no longer valid', code: 404);
        }

        return new ApiSuccessResponse($invite, 'Invite details');
    }

    /**
     * POST /invites/accept-by-token — Accept via token (user opened app from deep link and is logged in). Body: { "token": "..." }
     */
    public function acceptByToken(Request $request)
    {
        $validated = $request->validate(['token' => 'required|string']);

        try {
            $invite = $this->groupInviteService->acceptInviteByToken($validated['token'], $request->user());
        } catch (\Exception $e) {
            return new ApiErrorResponse($e->getMessage(), $e, code: 400);
        }

        return new ApiSuccessResponse($invite, 'Invite accepted successfully');
    }
}
