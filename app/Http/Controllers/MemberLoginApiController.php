<?php

namespace App\Http\Controllers;

use App\Http\Responses\ApiErrorResponse;
use App\Http\Responses\ApiSuccessResponse;
use App\Services\MemberLoginService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class MemberLoginApiController extends Controller
{
    public function __construct(
        private MemberLoginService $memberLoginService,
    ) {
    }

    /**
     * Handle member login request.
     */
    public function login(Request $request): ApiSuccessResponse|ApiErrorResponse
    {
        try {
            $validated = $request->validate([
                'email' => 'required|email',
                'password' => 'required',
            ]);

            $data = $this->memberLoginService->login(
                $validated['email'],
                $validated['password'],
            );

            return new ApiSuccessResponse($data);
        }  catch (Throwable $e) {
            return new ApiErrorResponse($e->getMessage(), $e);
        }
    }

    /**
     * Handle member logout request.
     */
    public function logout(Request $request): ApiSuccessResponse
    {
        $this->memberLoginService->logout($request->user());

        return  new ApiSuccessResponse(null, 'Logged out successfully', null, Response::HTTP_OK);
    }
}
