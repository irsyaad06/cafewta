<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RawMaterialResource\Pages;
use App\Models\RawMaterial;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RawMaterialResource extends Resource
{
    protected static ?string $model = RawMaterial::class;

    protected static ?string $navigationIcon = 'heroicon-o-beaker';

    protected static ?string $navigationGroup = 'Inventori';

    protected static ?string $navigationLabel = 'Bahan Baku';

    protected static ?string $modelLabel = 'Bahan Baku';

    protected static ?string $pluralModelLabel = 'Bahan Baku';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Card::make()
                    ->schema([
                        Forms\Components\Select::make('supplier_id')
                            ->relationship('supplier', 'name')
                            ->label('Pemasok')
                            ->searchable()
                            ->preload()
                            ->nullable(),

                        Forms\Components\TextInput::make('name')
                            ->label('Nama Bahan')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('sku')
                            ->label('SKU')
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->nullable(),

                        Forms\Components\TextInput::make('unit')
                            ->label('Satuan')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Contoh: gram, ml, pcs, kg'),

                        Forms\Components\TextInput::make('stock')
                            ->label('Stok')
                            ->numeric()
                            ->required()
                            ->default(0)
                            ->formatStateUsing(fn ($state) => $state !== null ? (float) $state : $state),

                        Forms\Components\TextInput::make('minimum_stock')
                            ->label('Stok Minimum')
                            ->numeric()
                            ->required()
                            ->default(0)
                            ->formatStateUsing(fn ($state) => $state !== null ? (float) $state : $state),

                        Forms\Components\TextInput::make('buy_price')
                            ->label('Harga Beli')
                            ->numeric()
                            ->required()
                            ->prefix('Rp')
                            ->default(0),

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
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Bahan')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('supplier.name')
                    ->label('Pemasok')
                    ->sortable()
                    ->placeholder('Tanpa Pemasok'),
                Tables\Columns\TextColumn::make('unit')
                    ->label('Satuan')
                    ->sortable(),
                Tables\Columns\TextColumn::make('stock')
                    ->label('Stok')
                    ->formatStateUsing(fn ($state) => $state !== null ? number_format((float) $state, (floor($state) == $state) ? 0 : strlen(substr(strrchr((string)(float)$state, "."), 1)), ',', '.') : '')
                    ->sortable(),
                Tables\Columns\TextColumn::make('minimum_stock')
                    ->label('Stok Minimum')
                    ->formatStateUsing(fn ($state) => $state !== null ? number_format((float) $state, (floor($state) == $state) ? 0 : strlen(substr(strrchr((string)(float)$state, "."), 1)), ',', '.') : '')
                    ->sortable(),
                Tables\Columns\TextColumn::make('buy_price')
                    ->label('Harga Beli')
                    ->money('idr')
                    ->sortable(),
                Tables\Columns\TextColumn::make('stock_status')
                    ->label('Status Stok')
                    ->badge()
                    ->state(fn (RawMaterial $record): string => $record->stock <= $record->minimum_stock ? 'Tipis' : 'Aman')
                    ->color(fn (string $state): string => $state === 'Tipis' ? 'danger' : 'success'),
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
                Tables\Filters\SelectFilter::make('supplier_id')
                    ->relationship('supplier', 'name')
                    ->label('Pemasok')
                    ->searchable()
                    ->preload(),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status Aktif')
                    ->boolean()
                    ->trueLabel('Hanya Aktif')
                    ->falseLabel('Hanya Non-aktif'),
                Tables\Filters\Filter::make('low_stock')
                    ->label('Stok Tipis Saja')
                    ->query(fn (Builder $query): Builder => $query->whereColumn('stock', '<=', 'minimum_stock')),
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
            'index' => Pages\ListRawMaterials::route('/'),
            'create' => Pages\CreateRawMaterial::route('/create'),
            'edit' => Pages\EditRawMaterial::route('/{record}/edit'),
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
