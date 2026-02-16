<?php

use App\Http\Controllers\GroupApiController;
use App\Http\Controllers\GroupInviteApiController;
use App\Http\Controllers\GroupRotationApiController;
use App\Http\Controllers\MemberApiController;
use App\Http\Controllers\MemberLoginApiController;
use App\Http\Controllers\MomoTransactionApiController;
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

Route::prefix('groups')->middleware('auth:members')->group(function () {
    Route::get('/', [GroupApiController::class, 'getMemberGroups']);
    Route::post('/', [GroupApiController::class, 'createGroup']);
    Route::get('/{group}', [GroupApiController::class, 'getGroupById']);
    Route::post('/{group}/role', [GroupApiController::class, 'setMemberRole']);
    Route::delete('/{group}/role', [GroupApiController::class, 'removeMemberRole']);
    Route::get('/{group}/members', [GroupApiController::class, 'getGroupMembers']);
    Route::get('/{group}/rotation', [GroupRotationApiController::class, 'getRotation']);
    Route::post('/{group}/invites', [GroupInviteApiController::class, 'sendInvite']);
    Route::get('/{group}/wallet-balance', [GroupApiController::class, 'getGroupWalletBalance']);
});

// Invite by token (no auth) — for mobile deep link preview
Route::get('/invites/by-token/{token}', [GroupInviteApiController::class, 'getByToken']);

Route::prefix('invites')->middleware('auth:members')->group(function () {
    Route::get('/', [GroupInviteApiController::class, 'myPendingInvites']);
    Route::post('/accept-by-token', [GroupInviteApiController::class, 'acceptByToken']);
    Route::post('/{invite}/accept', [GroupInviteApiController::class, 'accept']);
    Route::post('/{invite}/decline', [GroupInviteApiController::class, 'decline']);
});

Route::prefix('member')->middleware('auth:members')->group(function () {
    Route::get('/wallet-balance', [MemberApiController::class, 'getWalletBalance']);
    Route::get('/group/{group}', [MemberApiController::class, 'getGroupMembers']);
    Route::get('/{id}', [MemberApiController::class, 'getMemberById']);
    Route::get('/deposit', [MomoTransactionApiController::class, 'deposit']);
});

Route::prefix('wallet-transactions')->middleware('auth:members')->group(function () {
    Route::post('/member-to-member', [WalletTransactionApiController::class, 'memberToMember']);
    Route::post('/group-to-member', [WalletTransactionApiController::class, 'groupToMember']);
    Route::post('/confirm-group-to-member', [WalletTransactionApiController::class, 'confirmGroupToMember']);
    Route::post('/member-to-group', [WalletTransactionApiController::class, 'memberToGroup']);
});
