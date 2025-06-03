<?php

namespace App\Filament\Resources;

use Carbon\Carbon;
use Filament\Forms;
use Filament\Tables;
use App\Models\Gudang;
use App\Models\Product;
use App\Models\StockOut;
use App\Models\Order;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\DB;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\StockOutResource\Pages;

class StockOutResource extends Resource
{
    protected static ?string $model = StockOut::class;

    protected static ?string $modelLabel = 'Stok Keluar';

    protected static ?string $navigationIcon = 'heroicon-o-archive-box-x-mark';

    protected static ?string $navigationGroup = 'Supply Chain';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Laporan Stok Keluar';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // This resource is for reporting, so no form fields are needed.
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->selectRaw('
                MIN(order_items.id) as id,
                DATE(order_items.created_at) as date,
                order_items.id_gudang,
                order_items.id_product,
                SUM(order_items.quantity) as total_quantity,
                COALESCE(products.hpp, 0) as hpp,
                COALESCE(SUM(order_items.quantity) * products.hpp, 0) as total_hpp
            ')
            ->join('products', 'order_items.id_product', '=', 'products.id')
            ->join('orders', 'order_items.id_order', '=', 'orders.id')
            // ->where('orders.status', 'shipped') // Filter only shipped orders
            ->where('order_items.fulfillment_type', 'warehouse') // Filter only from your own warehouse
            ->groupBy('date', 'order_items.id_gudang', 'order_items.id_product', 'products.hpp');
        // ->orderBy('date', 'desc')
        // ->orderBy('order_items.id_gudang')
        // ->orderBy('order_items.id_product');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('date')
                    ->label('Tanggal')
                    ->date('d F Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('gudang.name')
                    ->label('Gudang')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('product.name')
                    ->label('Produk')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('total_quantity')
                    ->label('Jumlah Keluar')
                    ->numeric()
                    ->sortable()
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->label('Total Keluar')
                            ->numeric(),
                    ]),
                TextColumn::make('hpp')
                    ->label('HPP Satuan')
                    ->money('IDR', 0)
                    ->sortable(),
                TextColumn::make('total_hpp')
                    ->label('Total HPP')
                    ->money('IDR', 0)
                    ->sortable()
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->label('Total HPP')
                            ->money('IDR', 0),
                    ]),
            ])
            ->filters([
                Filter::make('date')
                    ->form([
                        Forms\Components\DatePicker::make('mulai')
                            ->placeholder('Dari Tanggal')
                            ->native(false),
                        Forms\Components\DatePicker::make('sampai')
                            ->placeholder('Sampai Tanggal')
                            ->native(false),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['mulai'] ?? null,
                                fn(Builder $query, $date): Builder => $query->whereDate('order_items.created_at', '>=', $date),
                            )
                            ->when(
                                $data['sampai'] ?? null,
                                fn(Builder $query, $date): Builder => $query->whereDate('order_items.created_at', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['mulai'] ?? null) {
                            $indicators['mulai'] = 'Dari: ' . Carbon::parse($data['mulai'])->format('d M Y');
                        }
                        if ($data['sampai'] ?? null) {
                            $indicators['sampai'] = 'Sampai: ' . Carbon::parse($data['sampai'])->format('d M Y');
                        }
                        return $indicators;
                    })
                    ->label('Filter Tanggal Keluar'),

                // --- CORRECTED SelectFilter for Gudang ---
                SelectFilter::make('id_gudang')
                    ->label('Gudang')
                    ->native(false)
                    ->options(fn() => Gudang::pluck('name', 'id')->toArray())
                    ->query(function (Builder $query, array $data): Builder { // Pass `array $data`
                        return $query->when(
                            $data['value'] ?? null, // Access the value via $data['value']
                            fn(Builder $query, $value): Builder => $query->where('order_items.id_gudang', $value)
                        );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['value'] ?? null) {
                            $gudang = Gudang::find($data['value']);
                            $indicators['id_gudang'] = 'Gudang: ' . ($gudang->name ?? 'Unknown');
                        }
                        return $indicators;
                    }),

                // --- CORRECTED SelectFilter for Product ---
                SelectFilter::make('id_product')
                    ->label('Produk')
                    ->native(false)
                    ->options(fn() => Product::pluck('name', 'id')->toArray())
                    ->query(function (Builder $query, array $data): Builder { // Pass `array $data`
                        return $query->when(
                            $data['value'] ?? null, // Access the value via $data['value']
                            fn(Builder $query, $value): Builder => $query->where('order_items.id_product', $value)
                        );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['value'] ?? null) {
                            $product = Product::find($data['value']);
                            $indicators['id_product'] = 'Produk: ' . ($product->name ?? 'Unknown');
                        }
                        return $indicators;
                    }),
            ])
            ->actions([
                // No actions needed for a report resource.
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
            // No relations managers for this report resource.
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStockOuts::route('/'),
            // 'create' and 'edit' pages are removed as this is a report resource.
        ];
    }
}