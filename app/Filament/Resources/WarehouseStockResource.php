<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Product;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\WarehouseStock;
use Filament\Resources\Resource;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\WarehouseStockResource\Pages;
use App\Filament\Resources\WarehouseStockResource\RelationManagers;

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
                Select::make('id_product')
                    ->options(Product::query()->pluck('name', 'id'))
                    ->searchable(),
                TextInput::make('quantity'),
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
                    ->label('Qty')
                    ->color(function ($state) {
                        return $state < 5 ? 'danger' : 'success';
                    })
                    ->formatStateUsing(fn($state) => $state . ' pcs')
                    ->sortable()
                    ->badge(),
                TextColumn::make('keterangan')
                    ->label('Keterangan')
                    ->searchable()
                    ->state(fn($record) => $record->quantity < 5 ? 'Restok' : 'Stok Aman')
                    ->color(fn($record) => $record->quantity < 5 ? 'danger' : 'success'),
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
            // 'create' => Pages\CreateWarehouseStock::route('/create'),
            // 'edit' => Pages\EditWarehouseStock::route('/{record}/edit'),
        ];
    }
}
