<?php

namespace App\Filament\Resources;

use App\Enums\TableStatus;
use App\Filament\Resources\CafeTableResource\Pages;
use App\Models\CafeTable;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CafeTableResource extends Resource
{
    protected static ?string $model = CafeTable::class;

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';

    protected static ?string $navigationGroup = 'Data Master';

    protected static ?string $navigationLabel = 'Meja Kafe';

    protected static ?string $modelLabel = 'Meja Kafe';

    protected static ?string $pluralModelLabel = 'Meja Kafe';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Card::make()
                    ->schema([
                        Forms\Components\TextInput::make('table_number')
                            ->label('Nomor Meja')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),

                        Forms\Components\TextInput::make('name')
                            ->label('Nama Meja')
                            ->maxLength(255)
                            ->nullable(),

                        Forms\Components\TextInput::make('capacity')
                            ->label('Kapasitas')
                            ->numeric()
                            ->minValue(1)
                            ->nullable(),

                        Forms\Components\TextInput::make('qr_code')
                            ->label('Kode QR')
                            ->maxLength(255)
                            ->nullable(),

                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options(TableStatus::class)
                            ->required()
                            ->default(TableStatus::Available->value),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Aktif')
                            ->required()
                            ->default(true),
                    ])
                    ->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('table_number')
                    ->label('Nomor Meja')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Meja')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('capacity')
                    ->label('Kapasitas')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (TableStatus $state): string => match ($state) {
                        TableStatus::Available => 'success',
                        TableStatus::Occupied => 'danger',
                        TableStatus::Reserved => 'warning',
                        TableStatus::Inactive => 'gray',
                    })
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make()
                    ->label('Sampah'),
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options(TableStatus::class),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status Aktif')
                    ->boolean()
                    ->trueLabel('Hanya Aktif')
                    ->falseLabel('Hanya Non-aktif'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Ubah'),
                Tables\Actions\DeleteAction::make()
                    ->label('Hapus'),
                Tables\Actions\ForceDeleteAction::make()
                    ->label('Hapus Permanen'),
                Tables\Actions\RestoreAction::make()
                    ->label('Pulihkan'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Hapus Terpilih'),
                    Tables\Actions\ForceDeleteBulkAction::make()
                        ->label('Hapus Permanen Terpilih'),
                    Tables\Actions\RestoreBulkAction::make()
                        ->label('Pulihkan Terpilih'),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCafeTables::route('/'),
            'create' => Pages\CreateCafeTable::route('/create'),
            'edit' => Pages\EditCafeTable::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
