<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum PaymentMethodType: string implements HasLabel
{
    case Cash = 'cash';
    case Qris = 'qris';
    case Transfer = 'transfer';
    case Debit = 'debit';
    case Ewallet = 'ewallet';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Cash => 'Tunai (Cash)',
            self::Qris => 'QRIS',
            self::Transfer => 'Transfer Bank',
            self::Debit => 'Kartu Debit',
            self::Ewallet => 'Dompet Digital (E-Wallet)',
        };
    }
}
