<?php
namespace App\Enums;

enum PaymentProvider: string
{
    case MTN = 'Mtn';
    case AIRTEL = 'Airtel';
    case AGROTEL = 'Agrotel';
    case PAXTEL = 'Paxtel';
    case SALAMTEL = 'Salamtel';
    case INTERSWITCH = 'Interswitch';

    public function label(): string
    {
        return match ($this) {
            self::MTN => 'MTN',
            self::AIRTEL => 'AIRTEL',
            self::AGROTEL => 'AGROTEL',
            self::PAXTEL => 'PAXTEL',
            self::SALAMTEL => 'SALAMTEL',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}