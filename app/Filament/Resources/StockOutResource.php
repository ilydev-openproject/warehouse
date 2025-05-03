<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Gudang;
use App\Models\Product;
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
        return parent::getEloquentQuery()
            ->selectRaw('
            MIN(id) as id,
            DATE(created_at) as date,
            id_gudang,
            id_product,
            SUM(quantity) as total_quantity
        ')
            ->groupBy('date', 'id_gudang', 'id_product')
            ->orderBy('date', 'desc');
    }

    public static function table(Table $table): Table
    {

        return $table
            ->columns([
                TextColumn::make('date') // Ganti dari created_at ke date
                    ->label('Tanggal')
                    ->date()
                    ->sortable(),

                TextColumn::make('id_gudang')
                    ->label('Gudang')
                    ->formatStateUsing(fn($state) => Gudang::find($state)?->name ?? $state)
                    ->searchable(),

                TextColumn::make('id_product')
                    ->label('Produk')
                    ->formatStateUsing(fn($state) => Product::find($state)?->name ?? $state)
                    ->searchable(),

                TextColumn::make('total_quantity')
                    ->label('Jumlah Keluar')
                    ->numeric()
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->label('Total Keluar')
                    ]),
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
