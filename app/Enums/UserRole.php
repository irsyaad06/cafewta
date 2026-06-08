<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum UserRole: string implements HasLabel
{
    case SuperAdmin = 'super_admin';
    case Owner = 'owner';
    case AdminManager = 'admin_manager';
    case Cashier = 'cashier';
    case Kitchen = 'kitchen';
    case Waiter = 'waiter';
    case Customer = 'customer';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::SuperAdmin => 'Super Admin',
            self::Owner => 'Pemilik (Owner)',
            self::AdminManager => 'Manajer Admin',
            self::Cashier => 'Kasir',
            self::Kitchen => 'Dapur (Kitchen)',
            self::Waiter => 'Pelayan (Waiter)',
            self::Customer => 'Pelanggan (Customer)',
        };
    }
}
