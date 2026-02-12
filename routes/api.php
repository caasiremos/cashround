<?php

use App\Http\Controllers\GroupApiController;
use App\Http\Controllers\MemberApiController;
use App\Http\Controllers\MemberLoginApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/member/login', [MemberLoginApiController::class, 'login']);
Route::post('/member/logout', [MemberLoginApiController::class, 'logout'])->middleware('auth:members');
Route::post('/member/register', [MemberApiController::class, 'register']);

Route::prefix('groups')->middleware('auth:members')->group(function () {
    Route::post('/', [GroupApiController::class, 'createGroup']);
    Route::get('/{group}', [GroupApiController::class, 'getGroupById']);
    Route::get('/{group}/members', [GroupApiController::class, 'getGroupMembers']);
});

Route::prefix('members')->middleware('auth:members')->group(function () {
    Route::get('/group/{group}', [MemberApiController::class, 'getGroupMembers']);
    Route::get('/{id}', [MemberApiController::class, 'getMemberById']);
});
