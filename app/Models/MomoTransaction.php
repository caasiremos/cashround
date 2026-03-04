<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MomoTransaction extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_SUCCESSFUL = 'successful';
    public const STATUS_FAILED = 'failed';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'wallet_id',
        'member_id',
        'transaction_type',
        'phone_number',
        'amount',
        'provider_fee',
        'service_fee',
        'internal_status',
        'telco_provider',
        'internal_id',
        'external_status',
        'external_id',
        'error_message',
    ];

    public static function providerDepositFee(float $amount): float
    {
        return $amount * 0.03;
    }
    public static function serviceDepositFee(float $amount): float
    {
        return $amount * 0.01;
    }
    public static function calculateDepositTotal($amount)
    {
        $serviceRate = 0.01;
        $gatewayRate = 0.03;

        $amountPlusService = $amount * (1 + $serviceRate);

        return round($amountPlusService / (1 - $gatewayRate), 2);
    }
    
    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }

    public function member()
    {
        return $this->belongsTo(Member::class);
    }
}
