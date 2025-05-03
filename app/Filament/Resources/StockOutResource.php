<?php

namespace App\Filament\Resources;

use App\Models\Orders;
use Filament\Forms;
use Filament\Tables;
use App\Models\StockOut;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\DB;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\StockOutResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\StockOutResource\RelationManagers;

class StockOutResource extends Resource
{
    protected static ?string $model = Orders::class;

    protected static ?string $modelLabel = 'Stok Keluar';

    protected static ?string $navigationIcon = 'heroicon-o-archive-box-x-mark';

    protected static ?string $navigationGroup = 'Supply Chain';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Stok Keluar';
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
                TextColumn::make('created_at')
                    ->date('d M Y'),
                TextColumn::make('pruduct.name')
                    ->getStateUsing(function ($record) {
                        return $record->order_items->map(function ($order_items) {
                            return $order_items->product->name . ' - ' . $order_items->quantity . ' pcs';
                        })->implode(', ');
                    }),
                TextColumn::make('gudang')
                    ->label('Gudang')
                    ->getStateUsing(function ($record) {
                        return $record->order_items->map(function ($item) {
                            return $item->gudang->name ?? '-';
                        })->unique()->implode(', ');
                    }),
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
            'index' => Pages\ListStockOuts::route('/'),
            // 'create' => Pages\CreateStockOut::route('/create'),
            // 'edit' => Pages\EditStockOut::route('/{record}/edit'),
        ];
    }
}
