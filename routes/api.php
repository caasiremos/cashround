<?php

use App\Http\Controllers\GroupApiController;
use App\Http\Controllers\GroupInviteApiController;
use App\Http\Controllers\GroupRotationApiController;
use App\Http\Controllers\MemberApiController;
use App\Http\Controllers\MemberLoginApiController;
use App\Http\Controllers\MomoTransactionApiController;
use App\Http\Controllers\OtpController;
use App\Http\Controllers\WalletTransactionApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/member/login', [MemberLoginApiController::class, 'login']);
Route::post('/member/logout', [MemberLoginApiController::class, 'logout'])->middleware('auth:members');
Route::post('/member/register', [MemberApiController::class, 'register']);
Route::post('/member/confirm-verification-code', [MemberApiController::class, 'confirmVerificationCode']);
Route::post('/member/forgot-password', [MemberApiController::class, 'forgotPassword']);
Route::post('/member/reset-password', [MemberApiController::class, 'resetPassword']);

Route::prefix('groups')->middleware('auth:members')->group(function () {
    Route::get('/', [GroupApiController::class, 'getMemberGroups']);
    Route::post('/', [GroupApiController::class, 'createGroup']);
    Route::get('/{group}', [GroupApiController::class, 'getGroupById']);
    Route::get('/{group}/transaction-auth', [GroupApiController::class, 'getGroupTransactionAuth']);
    Route::post('/{group}/role', [GroupApiController::class, 'setMemberRole']);
    Route::delete('/{group}/role', [GroupApiController::class, 'removeMemberRole']);
    Route::get('/{group}/members', [GroupApiController::class, 'getGroupMembers']);
    Route::get('/{group}/rotation', [GroupRotationApiController::class, 'getRotation']);
    Route::put('/{group}/rotation/order', [GroupRotationApiController::class, 'updateRotationOrder']);
    Route::post('/{group}/rotation/reschedule', [GroupRotationApiController::class, 'rescheduleCurrentRecipient']);
    Route::post('/{group}/invites', [GroupInviteApiController::class, 'sendInvite']);
    Route::get('/{group}/wallet-balance', [GroupApiController::class, 'getGroupWalletBalance']);
    Route::get('/{group}/wallet-transactions', [WalletTransactionApiController::class, 'getGroupWalletTransactions']);
});

Route::prefix('invites')->middleware('auth:members')->group(function () {
    Route::post('/send-invite', [GroupInviteApiController::class, 'sendInvite']);
    Route::post('/accept-invite', [GroupInviteApiController::class, 'acceptInvite']);
});

Route::prefix('member')->middleware('auth:members')->group(function () {
    Route::get('/wallet-balance', [MemberApiController::class, 'getWalletBalance']);
    Route::get('/group/{group}', [MemberApiController::class, 'getGroupMembers']);
    // Route::get('/{id}', [MemberApiController::class, 'getMemberById']);
    Route::post('/fcm-token', [MemberApiController::class, 'updateFcmToken']);
    Route::post('/deposit', [MomoTransactionApiController::class, 'deposit']);
    Route::post('/withdraw', [MomoTransactionApiController::class, 'withdrawal']);
    Route::post('/account-number', [MemberApiController::class, 'getMemberByAccountNumber']);
    Route::get('/notifications', [MemberApiController::class, 'getMemberNotifications']);
    Route::post('/notifications/read', [MemberApiController::class, 'readMemberNotification']);
    Route::get('/momo-transactions', [MomoTransactionApiController::class, 'getMemberMomoTransactions']);
    Route::get('/wallet-transactions', [WalletTransactionApiController::class, 'getMemberWalletTransactions']);
});

Route::prefix('wallet-transactions')->middleware('auth:members')->group(function () {
    Route::post('/member-to-member', [WalletTransactionApiController::class, 'memberToMember']);
    Route::post('/group-to-member', [WalletTransactionApiController::class, 'groupToMember']);
    Route::post('/member-to-group', [WalletTransactionApiController::class, 'memberToGroup']);
});

Route::controller(OtpController::class)->middleware('auth:members')->group(function () {
    Route::post('otp/generate', 'generateOtp');
    Route::post('otp/verify', 'verifyOtp');
});

Route::controller(MomoTransactionApiController::class)->group(function () {
    Route::post('relworx/callback/collection', 'relworxCollectionCallback');
    Route::post('relworx/callback/disbursement', 'relworxDisbursementCallback');
});
