<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RecipeResource\Pages;
use App\Models\Recipe;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class RecipeResource extends Resource
{
    protected static ?string $model = Recipe::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationGroup = 'Inventori';

    protected static ?string $navigationLabel = 'Resep';

    protected static ?string $modelLabel = 'Resep';

    protected static ?string $pluralModelLabel = 'Resep';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Card::make()
                    ->schema([
                        Forms\Components\Select::make('menu_id')
                            ->relationship('menu', 'name')
                            ->label('Menu')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\Select::make('raw_material_id')
                            ->relationship('rawMaterial', 'name')
                            ->label('Bahan Baku')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\TextInput::make('quantity')
                            ->label('Jumlah Takaran')
                            ->numeric()
                            ->required(),

                        Forms\Components\TextInput::make('unit')
                            ->label('Satuan')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Textarea::make('note')
                            ->label('Catatan')
                            ->maxLength(65535)
                            ->columnSpanFull()
                            ->nullable(),
                    ])
                    ->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('menu.name')
                    ->label('Menu')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('rawMaterial.name')
                    ->label('Bahan Baku')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('quantity')
                    ->label('Jumlah Takaran')
                    ->numeric(3)
                    ->sortable(),
                Tables\Columns\TextColumn::make('unit')
                    ->label('Satuan')
                    ->sortable(),
                Tables\Columns\TextColumn::make('note')
                    ->label('Catatan')
                    ->searchable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('menu_id')
                    ->relationship('menu', 'name')
                    ->label('Menu')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('raw_material_id')
                    ->relationship('rawMaterial', 'name')
                    ->label('Bahan Baku')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Ubah'),
                Tables\Actions\DeleteAction::make()
                    ->label('Hapus'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Hapus Terpilih'),
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
            'index' => Pages\ListRecipes::route('/'),
            'create' => Pages\CreateRecipe::route('/create'),
            'edit' => Pages\EditRecipe::route('/{record}/edit'),
        ];
    }
}
