<?php

declare(strict_types=1);

namespace App\Utils;

use App\Enums\PaymentProvider;
use Illuminate\Support\Str;

class PhoneNumberUtil
{
    /**
     * Format phone number for airtel
     */
    public static function formatAirtelPhoneNumber(string $phone_number): string
    {
        if (str_starts_with($phone_number, '25675')) {
            $phone_number = substr_replace($phone_number, '', 0, 3);
        }
        if (str_starts_with($phone_number, '25674')) {
            $phone_number = substr_replace($phone_number, '', 0, 3);
        }
        if (str_starts_with($phone_number, '25670')) {
            $phone_number = substr_replace($phone_number, '', 0, 3);
        }
        if (str_starts_with($phone_number, '+25675')) {
            $phone_number = substr_replace($phone_number, '', 0, 4);
        }
        if (str_starts_with($phone_number, '+25670')) {
            $phone_number = substr_replace($phone_number, '', 0, 4);
        }
        if (str_starts_with($phone_number, '+25674')) {
            $phone_number = substr_replace($phone_number, '', 0, 4);
        }

        return $phone_number;
    }

    /**
     * Normalize phone number (remove +, spaces, etc)
     */
    private static function normalize(string $number): string
    {
        return preg_replace('/\D/', '', $number);
    }

    /**
     * All providers and their prefixes
     */
    private static function providers(): array
    {
        return [
            PaymentProvider::MTN->value => [
                '25676', '25677', '25678', '25679',
                '076', '077', '078', '079',
                '25639', '039',
            ],
            PaymentProvider::AIRTEL->value => [
                '25670', '25674', '25675', '25620',
                '070', '074', '075', '020',
            ],
            PaymentProvider::PAXTEL->value => [
                '25671', '071',
            ],
            PaymentProvider::AGROTEL->value => [
                '25672', '072',
            ],
            PaymentProvider::SALAMTEL->value => [
                '25673', '073',
            ],
        ];
    }

    /**
     * Detect provider
     */
    public static function provider(string $number): string
    {
        $phone = self::normalize($number);

        foreach (self::providers() as $provider => $prefixes) {
            if (Str::startsWith($phone, $prefixes)) {
                return $provider;
            }
        }

        return 'UNKNOWN';
    }

    /**
     * Generic checker
     */
    public static function isProvider(string $number, string $provider): bool
    {
        return self::provider($number) === strtolower($provider);
    }

    /**
     * Backward compatibility helpers
     */

    public static function isMtnNumber(string $number): bool
    {
        return self::isProvider($number, 'MTN');
    }

    public static function isAirtelNumber(string $number): bool
    {
        return self::isProvider($number, 'AIRTEL');
    }

    public static function isPaxtelNumber(string $number): bool
    {
        return self::isProvider($number, 'PAXTEL');
    }

    public static function isAgroTelNumber(string $number): bool
    {
        return self::isProvider($number, 'AGROTEL');
    }

    public static function isSalamTelNumber(string $number): bool
    {
        return self::isProvider($number, 'SALAMTEL');
    }
}

