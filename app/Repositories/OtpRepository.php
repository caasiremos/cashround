<?php

namespace App\Repositories;

use App\Exceptions\ExpectedException;
use App\Models\Otp;
use Illuminate\Http\Request;

class OtpRepository
{
    /**
     * Generate a new OTP
     * @param string $phoneNumber
     * @return Otp
     */
    public function generateOtp(string $phoneNumber): Otp
    {
        $otp = new Otp();
        $otp->phone_number = $phoneNumber;
        $otp->code = mt_rand(100000, 999999);
        $otp->matched = false;
        $otp->expires_at = now()->addMinutes(5);
        $otp->save();
        return $otp;
    }

    /**
     * Verify that the OTP is valid
     * @param string $telephoneNumber
     * @param string $code
     * @return bool
     */
    public function verifyOtp(Request $request): bool
    {
        $otp = Otp::query()
            ->where('phone_number', $request->phone_number)
            ->where('code', $request->code)
            ->where('matched', false)
            ->where('expires_at', '>', now())
            ->first();

        if (!$otp) {
            throw new ExpectedException('Invalid OTP');
        }

        $otp->matched = true;
        $otp->expires_at = now();
        return $otp->save();
    }
}