<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RecipeResource\Pages;
use App\Models\Menu;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class RecipeResource extends Resource
{
    protected static ?string $model = Menu::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationGroup = 'Inventori';

    protected static ?string $navigationLabel = 'Resep';

    protected static ?string $modelLabel = 'Resep';

    protected static ?string $pluralModelLabel = 'Resep';

    protected static ?int $navigationSort = 3;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Card::make()
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Menu')
                            ->disabled()
                            ->dehydrated(false),

                        Forms\Components\Repeater::make('recipes')
                            ->relationship('recipes')
                            ->label('Bahan Baku / Resep')
                            ->schema([
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
                            ->columns(3)
                            ->columnSpanFull()
                            ->defaultItems(0),
                    ])
                    ->columns(1)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Menu')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Kategori')
                    ->sortable(),
                Tables\Columns\TextColumn::make('recipes.rawMaterial.name')
                    ->label('Bahan Baku')
                    ->badge()
                    ->searchable(),
                Tables\Columns\TextColumn::make('recipes_count')
                    ->counts('recipes')
                    ->label('Jumlah Bahan Baku')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('raw_material')
                    ->relationship('recipes.rawMaterial', 'name')
                    ->label('Bahan Baku')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Ubah'),
            ])
            ->bulkActions([
                //
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
            'edit' => Pages\EditRecipe::route('/{record}/edit'),
        ];
    }
}
