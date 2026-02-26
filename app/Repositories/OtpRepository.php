<?php

namespace App\Repositories;

use App\Exceptions\ExpectedException;
use App\Jobs\OtpFcmJob;
use App\Jobs\SendSMSOtpJob;
use App\Models\Otp;
use App\Models\TransactionAuth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OtpRepository
{
    /**
     * Generate a new OTP
     */
    public function generateOtp(string $phoneNumber): Otp
    {
        $otp = new Otp;
        $otp->member_id = auth()->user()->id;
        $otp->phone_number = $phoneNumber;
        $otp->code = mt_rand(100000, 999999);
        $otp->matched = false;
        $otp->expires_at = now()->addMinutes(5);
        $otp->save();
        SendSMSOtpJob::dispatch($otp);
        OtpFcmJob::dispatch($otp);
        return $otp;
    }


    /**
     * Verify that the OTP is valid
     *
     * @return TransactionAuth
     */
    public function verifyOtp(Request $request): TransactionAuth
    {
        $otp = Otp::query()
            ->where('member_id', auth()->user()->id)
            ->where('phone_number', $request->phone_number)
            ->where('code', $request->code)
            ->where('matched', false)
            ->where('expires_at', '>', now())
            ->first();

        if (! $otp) {
            throw new ExpectedException('Invalid OTP');
        }

        return DB::transaction(function () use ($otp, $request) {

            $walletTransactionRepository = new WalletTransactionRepository;

            $otp->matched = true;
            $otp->expires_at = now();
            $otp->save();

            return $walletTransactionRepository->confirmGroupToWalletTransfer($request->all());
        });
    }
}
