<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PurchaseResource\Pages;
use App\Models\Purchase;
use App\Models\RawMaterial;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PurchaseResource extends Resource
{
    protected static ?string $model = Purchase::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';
    protected static ?string $navigationLabel = 'Pemesanan Bahan Baku';
    protected static ?string $modelLabel = 'Pemesanan Bahan Baku';
    protected static ?string $pluralModelLabel = 'Pemesanan Bahan Baku';
    protected static ?string $navigationGroup = 'Manajemen Bahan';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Pesanan')
                    ->schema([
                        Forms\Components\Select::make('supplier_id')
                            ->relationship('supplier', 'name')
                            ->label('Pemasok')
                            ->searchable()
                            ->preload()
                            ->reactive()
                            ->live()
                            ->required(),
                        Forms\Components\DatePicker::make('date')
                            ->label('Tanggal Pesanan')
                            ->default(now())
                            ->required(),
                        Forms\Components\Select::make('status')
                            ->label('Status Pembelian')
                            ->options([
                                'dalam proses' => 'Dalam Proses',
                                'selesai' => 'Selesai',
                            ])
                            ->default('dalam proses')
                            ->required(),
                        Forms\Components\Textarea::make('notes')
                            ->label('Catatan')
                            ->columnSpanFull(),
                    ])->columns(3),
                    
                Forms\Components\Section::make('Daftar Bahan Baku')
                    ->schema([
                        Forms\Components\Repeater::make('items')
                            ->relationship()
                            ->label('')
                            ->addActionLabel('Tambahkan')
                            ->schema([
                                Forms\Components\Select::make('raw_material_id')
                                    ->label('Bahan Baku')
                                    ->options(function () {
                                        return RawMaterial::all()->mapWithKeys(function ($item) {
                                            $stockNumber = (float) $item->stock;
                                            return [$item->id => "{$item->name} / {$stockNumber} / {$item->stock_status}"];
                                        });
                                    })
                                    ->searchable()
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, Forms\Set $set) {
                                        if ($state) {
                                            $material = RawMaterial::find($state);
                                            if ($material) {
                                                $set('unit', $material->unit);
                                                $set('buy_price', $material->buy_price);
                                                
                                                if ($material->supplier_id) {
                                                    $set('../../supplier_id', $material->supplier_id);
                                                }
                                            }
                                        }
                                    }),
                                Forms\Components\TextInput::make('unit')
                                    ->label('Satuan')
                                    ->required()
                                    ->disabled()
                                    ->dehydrated(),
                                Forms\Components\TextInput::make('quantity')
                                    ->label('Jumlah Pesanan')
                                    ->numeric()
                                    ->required()
                                    ->formatStateUsing(fn ($state) => $state !== null ? (float) $state : $state)
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                        $price = floatval($get('buy_price') ?? 0);
                                        $qty = floatval($state ?? 0);
                                        $set('subtotal', $price * $qty);
                                    }),
                                Forms\Components\TextInput::make('buy_price')
                                    ->label('Harga Beli')
                                    ->numeric()
                                    ->required()
                                    ->formatStateUsing(fn ($state) => $state !== null ? (float) $state : $state)
                                    ->reactive()
                                    ->disabled()
                                    ->dehydrated()
                                    ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                        $price = floatval($state ?? 0);
                                        $qty = floatval($get('quantity') ?? 0);
                                        $set('subtotal', $price * $qty);
                                    }),
                                Forms\Components\TextInput::make('subtotal')
                                    ->label('Subtotal')
                                    ->numeric()
                                    ->required()
                                    ->formatStateUsing(fn ($state) => $state !== null ? (float) $state : $state)
                                    ->disabled()
                                    ->dehydrated(),
                            ])
                            ->columns(5),
                        Forms\Components\Placeholder::make('total_pembelian')
                            ->label('Total Pembelian')
                            ->content(function (Forms\Get $get) {
                                $total = 0;
                                $items = $get('items') ?? [];
                                foreach ($items as $item) {
                                    $price = floatval($item['buy_price'] ?? 0);
                                    $qty = floatval($item['quantity'] ?? 0);
                                    $total += ($price * $qty);
                                }
                                return 'Rp ' . number_format($total, 0, ',', '.');
                            }),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query
                ->orderByRaw("FIELD(status, 'dalam proses', 'selesai')")
                ->orderBy('date', 'desc')
                ->orderBy('created_at', 'desc')
            )
            ->columns([
                Tables\Columns\TextColumn::make('date')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('supplier.name')
                    ->label('Pemasok')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_price')
                    ->label('Total Harga')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\SelectColumn::make('status')
                    ->label('Status')
                    ->options([
                        'dalam proses' => 'Dalam Proses',
                        'selesai' => 'Selesai',
                    ])
                    ->disabled(fn (Purchase $record) => $record->status === 'selesai')
                    ->afterStateUpdated(function (\App\Models\Purchase $record, $state) {
                        if ($state === 'selesai') {
                            $record->processCompletion();
                        }
                    })
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('invoice')
                    ->label('Lihat Invoice')
                    ->icon('heroicon-o-document-text')
                    ->color('info')
                    ->url(fn (Purchase $record): string => route('purchases.invoice', $record)),
                Tables\Actions\EditAction::make()
                    ->hidden(fn (Purchase $record): bool => $record->status === 'selesai'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListPurchases::route('/'),
            'create' => Pages\CreatePurchase::route('/create'),
            'edit' => Pages\EditPurchase::route('/{record}/edit'),
        ];
    }
}
