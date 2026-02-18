<?php

namespace App\Services;

use App\Exceptions\ExpectedException;
use App\Models\Member;
use App\Models\Otp;
use App\Models\TransactionAuth;
use App\Repositories\OtpRepository;
use Illuminate\Http\Request;

class OtpService
{
    public function __construct(private OtpRepository $otpRepository) {}

    /**
     * Generate a new OTP
     *
     * @param  string  $telephoneNumber
     */
    public function generateOtp(Request $request): Otp
    {
        if (blank($request->phone_number)) {
            throw new ExpectedException('Phone number is required');
        }
        $member = Member::where('phone_number', $request->phone_number)->first();
        if (! $member) {
            throw new ExpectedException('Phone number not found');
        }

        return $this->otpRepository->generateOtp($request->phone_number);
    }

    /**
     * @throws ExpectedException
     */
    public function verifyOtp(Request $request)
    {
        $otp = Otp::query()
            ->where('phone_number', $request->phone_number)
            ->where('code', $request->code)
            ->latest()
            ->first();

        if (! $otp) {
            throw new ExpectedException('Invalid OTP');
        }

        if ($otp->matched) {
            throw new ExpectedException('OTP already used');
        }

        if ($otp->expires_at->isPast()) {
            throw new ExpectedException('OTP has expired');
        }

        return $this->otpRepository->verifyOtp($request);
    }
}
