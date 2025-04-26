<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WarehouseStockResource\Pages;
use App\Filament\Resources\WarehouseStockResource\RelationManagers;
use App\Models\WarehouseStock;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class WarehouseStockResource extends Resource
{
    protected static ?string $model = WarehouseStock::class;

    protected static ?string $navigationIcon = 'heroicon-o-inbox-stack';

    protected static ?string $navigationLabel = 'Stok Gudang';

    protected static ?string $navigationGroup = 'Supply Chain';

    protected static ?int $navigationSort = 0;

    protected static ?string $modelLabel = 'Stok Gudang';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('product.name')
                    ->searchable(),
                TextColumn::make('gudang.name'),
                TextColumn::make('quantity')
                    ->color(function ($state) {
                        return $state < 5 ? 'danger' : 'success'; // Merah jika <5, hijau jika >=5
                    })
                    ->formatStateUsing(fn($state) => $state . ' pcs') // imbuhi text di belakang angka
                    ->badge(),
            ])
            ->filters([
                //
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListWarehouseStocks::route('/'),
            'create' => Pages\CreateWarehouseStock::route('/create'),
            'edit' => Pages\EditWarehouseStock::route('/{record}/edit'),
        ];
    }
}
