<?php

namespace App\Filament\Resources;

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
    protected static ?string $model = StockOut::class;

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
    public static function getEloquentQuery(): Builder
    {
        return StockOut::query()
            ->select('id_product', 'id_gudang', DB::raw('SUM(quantity) as total_keluar'))
            ->where('fulfillment_type', 'gudangs')
            ->groupBy('id_product', 'id_gudang');
    }
    public static function table(Table $table): Table
    {

        return $table
            ->columns([
                TextColumn::make('product.name')->label('Nama Produk'),
                TextColumn::make('gudang.nama')->label('Gudang'),
                TextColumn::make('total_keluar')->label('Jumlah Keluar'),
                TextColumn::make('product.name')
                    ->label('Nama Produk')
                    ->searchable(),

                TextColumn::make('gudang.name')
                    ->label('Gudang')
                    ->searchable(),

                TextColumn::make('total_keluar')
                    ->label('Jumlah Keluar'),

                TextColumn::make('order.created_at')
                    ->label('Tanggal Order')
                    ->date('d M Y'),
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
