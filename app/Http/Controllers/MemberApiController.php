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
     * Confirm a member account number
     *
     * @param Request $request
     * @return ApiSuccessResponse
     */
    public function getMemberByAccountNumber(Request $request)
    {
        $member = $this->memberService->getMemberByAccountNumber($request->account_number);
        return new ApiSuccessResponse($member, 'Member account number confirmed successfully');
    }

    /**
     * Get the wallet balance of the member
     *
     * @return ApiSuccessResponse
     */
    public function getWalletBalance()
    {
        $balance = $this->memberService->getWalletBalance(auth()->user());

        return new ApiSuccessResponse($balance, 'Wallet balance fetched successfully');
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
     * Confirm a verification code
     *
     * @param Request $request  
     * @return ApiSuccessResponse
     */
    public function confirmVerificationCode(Request $request)
    {
        $member = $this->memberService->confirmVerificationCode($request);
        return new ApiSuccessResponse($member, 'Verification code confirmed successfully');
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

    /**
     * Update the authenticated member's FCM token for push notifications.
     * Call this from the app after login or when the token is refreshed.
     *
     * @param Request $request
     * @return ApiSuccessResponse
     */
    public function updateFcmToken(Request $request)
    {
        $request->validate([
            'fcm_token' => ['nullable', 'string', 'max:500'],
        ]);

        $member = auth('members')->user();
        $member->update(['fcm_token' => $request->input('fcm_token')]);

        return new ApiSuccessResponse(
            ['fcm_token_registered' => (bool) $member->fcm_token],
            'FCM token updated successfully'
        );
    }

    /**
     * Get all notifications for a member
     *
     * @return ApiSuccessResponse
     */
    public function getMemberNotifications()
    {
        $notifications = $this->memberService->getMemberNotifications();
        return new ApiSuccessResponse($notifications, 'Notifications fetched successfully');
    }

    /**
     * Read a notification for a member
     *
     * @param Request $request
     * @return ApiSuccessResponse
     */
    public function readMemberNotification(Request $request)
    {
        $notification = $this->memberService->readMemberNotification($request);
        return new ApiSuccessResponse($notification, 'Notification read successfully');
    }
}
