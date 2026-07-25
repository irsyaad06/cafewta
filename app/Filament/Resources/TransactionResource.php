<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransactionResource\Pages;
use App\Filament\Resources\TransactionResource\RelationManagers;
use App\Models\Transaction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;


class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';
    
    protected static ?string $navigationGroup = 'Keuangan';
    
    protected static ?string $navigationLabel = 'Pemasukan';
    
    protected static ?string $modelLabel = 'Transaksi';
    
    protected static ?string $pluralModelLabel = 'Transaksi';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Transaksi')
                    ->schema([
                        Forms\Components\TextInput::make('invoice_number')
                            ->label('Nomor Invoice')
                            ->disabled(),
                        Forms\Components\TextInput::make('created_at')
                            ->label('Tanggal Waktu')
                            ->disabled(),
                        Forms\Components\Select::make('payment_method_id')
                            ->relationship('paymentMethod', 'name')
                            ->label('Metode Pembayaran')
                            ->disabled(),
                        Forms\Components\TextInput::make('status')
                            ->label('Status')
                            ->disabled(),
                    ])->columns(2),
                
                Forms\Components\Section::make('Detail Item')
                    ->schema([
                        Forms\Components\Repeater::make('transactionDetails')
                            ->relationship()
                            ->schema([
                                Forms\Components\TextInput::make('menu_name')
                                    ->label('Nama Menu')
                                    ->disabled(),
                                Forms\Components\TextInput::make('price')
                                    ->label('Harga')
                                    ->disabled()
                                    ->numeric()
                                    ->prefix('Rp'),
                                Forms\Components\TextInput::make('hpp')
                                    ->label('Modal (HPP)')
                                    ->disabled()
                                    ->numeric()
                                    ->prefix('Rp'),
                                Forms\Components\TextInput::make('quantity')
                                    ->label('Jumlah')
                                    ->disabled(),
                                Forms\Components\TextInput::make('subtotal')
                                    ->label('Subtotal')
                                    ->disabled()
                                    ->numeric()
                                    ->prefix('Rp'),
                            ])
                            ->columns(5)
                            ->disableItemCreation()
                            ->disableItemDeletion()
                            ->disableItemMovement()
                    ]),
                
                Forms\Components\Section::make('Total')
                    ->schema([
                        Forms\Components\TextInput::make('total_amount')
                            ->label('Total Pemasukan')
                            ->disabled()
                            ->prefix('Rp'),
                        Forms\Components\TextInput::make('total_hpp')
                            ->label('Total Modal')
                            ->disabled()
                            ->prefix('Rp'),
                        Forms\Components\TextInput::make('total_profit')
                            ->label('Total Keuntungan')
                            ->disabled()
                            ->prefix('Rp'),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('invoice_number')
                    ->label('Invoice')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('paymentMethod.name')
                    ->label('Metode Bayar')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Pemasukan')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_profit')
                    ->label('Keuntungan')
                    ->money('IDR')
                    ->sortable()
                    ->color('success'),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\Filter::make('rentang_tanggal')
                    ->form([
                        Forms\Components\DatePicker::make('date_from')
                            ->label('Dari Tanggal')
                            ->native(false)
                            ->displayFormat('d F Y')
                            ->default(now()->startOfMonth()),
                        Forms\Components\DatePicker::make('date_until')
                            ->label('Hingga Tanggal')
                            ->native(false)
                            ->displayFormat('d F Y')
                            ->default(now()->endOfMonth()),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['date_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['date_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
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
            'index' => Pages\ListTransactions::route('/'),
        ];
    }
    
    public static function canCreate(): bool
    {
        return false;
    }
}
