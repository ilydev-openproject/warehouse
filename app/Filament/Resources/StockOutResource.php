<?php

namespace App\Filament\Resources;

use Carbon\Carbon;
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
            MIN(order_items.id) as id,
            DATE(order_items.created_at) as date,
            id_gudang,
            id_product,
            SUM(quantity) as total_quantity,
            COALESCE(products.hpp, 0) as hpp,
            COALESCE(products.het, 0) as het
        ')
            ->join('products', 'order_items.id_product', '=', 'products.id')
            ->groupBy('date', 'id_gudang', 'id_product', 'products.hpp', 'products.het')
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
                TextColumn::make('hpp')
                    ->label('Total HPP')
                    ->formatStateUsing(function ($state, $record) {
                        $productId = $record->id_product;
                        $quantity = $record->total_quantity;

                        if (!$productId || !$quantity) {
                            return '0';
                        }

                        $hpp = \App\Models\Product::find($productId)?->hpp ?? 0;
                        return number_format($hpp * $quantity, 0, ',', '.');
                    })
                    ->money('idr')
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->label('Total HPP')
                            ->money('idr')
                    ]),
                TextColumn::make('het')
                    ->label('Total HET')
                    ->formatStateUsing(function ($state, $record) {
                        $productId = $record->id_product;
                        $quantity = $record->total_quantity;

                        if (!$productId || !$quantity) {
                            return '0';
                        }

                        $het = \App\Models\Product::find($productId)?->het ?? 0;
                        return number_format($het * $quantity, 0, ',', '.');
                    })
                    ->money('idr')
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->label('Total HET')
                            ->money('idr')
                    ]),

            ])
            ->filters([
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('mulai')
                            ->placeholder(fn($state): string => 'Dec 18, ' . now()->subYear()->format('Y'))
                            ->native(false),
                        Forms\Components\DatePicker::make('sampai')
                            ->placeholder(fn($state): string => now()->format('M d, Y'))
                            ->native(false),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['mulai'] ?? null,
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['sampai'] ?? null,
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['mulai'] ?? null) {
                            $indicators['mulai'] = 'Order from ' . Carbon::parse($data['mulai'])->toFormattedDateString();
                        }
                        if ($data['sampai'] ?? null) {
                            $indicators['sampai'] = 'Order until ' . Carbon::parse($data['sampai'])->toFormattedDateString();
                        }

                        return $indicators;
                    }),
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
