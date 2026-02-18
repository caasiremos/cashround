<?php

namespace App\Http\Controllers;

use App\Exceptions\ExpectedException;
use App\Http\Responses\ApiSuccessResponse;
use App\Services\OtpService;
use Illuminate\Http\Request;
use Throwable;

class OtpController extends Controller
{
    public function __construct(private OtpService $otpService) {}

    /**
     * Generate a new OTP
     * @param Request $request
     * @return ApiSuccessResponse
     * @throws ExpectedException
     */
    public function generateOtp(Request $request)
    {
        $request->validate([
            'phone_number' => 'required|string|max:255',
        ]);
        $otp = $this->otpService->generateOtp($request);
        return new ApiSuccessResponse($otp);
    }

    /**
     * Verify that the OTP is valid
     * @param Request $request
     * @return bool
     * @throws ExpectedException
     */
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'phone_number' => 'required|string|max:255|exists:otps,phone_number',
            'code' => 'required|string|max:255|exists:otps,code',
        ]);
        $verified = $this->otpService->verifyOtp($request);
        return new ApiSuccessResponse($verified, 'OTP verified successfully');
    }
}
