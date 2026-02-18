<?php

namespace App\Services;

use App\Exceptions\ExpectedException;
use App\Repositories\OtpRepository;
use App\Models\Member;
use App\Models\Otp;
use Illuminate\Http\Request;
class OtpService
{
    public function __construct(private OtpRepository $otpRepository)
    {
    }

    /**
     * Generate a new OTP
     * @param string $telephoneNumber
     * @return Otp
     */
    public function generateOtp(Request $request): Otp
    {
        if (blank($request->phone_number)) {
            throw new ExpectedException('Phone number is required');
        }
        $member = Member::where('telephone_number', $request->phone_number)->first();
        if (!$member) {
            throw new ExpectedException('Phone number not found');
        }

        return $this->otpRepository->generateOtp($request->phone_number);
    }

    public function verifyOtp(Request $request): bool
    {
        $otp = Otp::query()
            ->where('telephone_number', $request->phone_number)
            ->where('code', $request->code)
            ->where('matched', false)
            ->where('expires_at', '>', now())
            ->first();

        if (!$otp) {
            throw new ExpectedException('Invalid OTP');
        }

        return $this->otpRepository->verifyOtp($request);
    }
}