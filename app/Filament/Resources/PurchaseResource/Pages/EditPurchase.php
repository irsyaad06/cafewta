<?php

namespace App\Filament\Resources\PurchaseResource\Pages;

use App\Filament\Resources\PurchaseResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPurchase extends EditRecord
{
    protected static string $resource = PurchaseResource::class;
    
    public ?string $oldStatus = null;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
    
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->oldStatus = $this->record->status;
        return $data;
    }
    
    protected function afterSave(): void
    {
        if ($this->oldStatus === 'dalam proses' && $this->record->status === 'selesai') {
            $this->record->processCompletion();
        }
    }
}
