<?php

namespace App\Enums;

enum TransactionTypeEnum: string
{
    case DEPOSIT = 'momo_deposit';
    case WITHDRAWAL = 'momo_withdrawal';
    case MEMBER_TO_MEMBER = 'member_to_member';
    case GROUP_TO_MEMBER = 'group_to_member';
    case MEMBER_TO_GROUP = 'member_to_group';

    public function label(): string
    {
        return match ($this) {
            self::DEPOSIT => 'Momo Deposit',
            self::WITHDRAWAL => 'Momo Withdrawal',
            self::MEMBER_TO_MEMBER => 'Member to Member',
            self::GROUP_TO_MEMBER => 'Group to Member',
            self::MEMBER_TO_GROUP => 'Member to Group',
        };
    }

    public function value(): string
    {
        return match ($this) {
            self::DEPOSIT => 'momo_deposit',
            self::WITHDRAWAL => 'momo_withdrawal',
            self::MEMBER_TO_MEMBER => 'member_to_member',
            self::GROUP_TO_MEMBER => 'group_to_member',
            self::MEMBER_TO_GROUP => 'member_to_group',
        };
    }
}