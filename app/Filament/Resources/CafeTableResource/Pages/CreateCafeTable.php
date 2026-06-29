<?php

namespace App\Filament\Resources\CafeTableResource\Pages;

use App\Filament\Resources\CafeTableResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateCafeTable extends CreateRecord
{
    protected static string $resource = CafeTableResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
