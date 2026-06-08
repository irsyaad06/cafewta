<?php

namespace App\Filament\Resources\CafeTableResource\Pages;

use App\Filament\Resources\CafeTableResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCafeTable extends EditRecord
{
    protected static string $resource = CafeTableResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }
}
