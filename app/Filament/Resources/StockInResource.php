<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Gudang;
use App\Models\Product;
use App\Models\StockIn;
use Filament\Forms\Set;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Actions\Action;
use App\Models\WarehouseStock;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\DB;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\StockInResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\StockInResource\RelationManagers;

class StockInResource extends Resource
{
    protected static ?string $model = StockIn::class;

    protected static ?string $modelLabel = 'Stok Masuk';

    protected static ?string $navigationIcon = 'heroicon-o-archive-box-arrow-down';

    protected static ?string $navigationGroup = 'Supply Chain';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Stok Masuk';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('id_product')
                    ->options(Product::query()->pluck('name', 'id'))
                    ->searchable()
                    ->disabled(fn($operation) => $operation === 'edit')
                    ->preload()
                    ->label('Pilih Produk')
                    ->required(),
                TextInput::make('quantity')
                    ->numeric()
                    ->label('Qty')
                    ->required()
                    ->live() // Memperbarui nilai secara real-time
                    ->afterStateUpdated(function ($state, Set $set, $get) {
                        $set('total_harga', $state * $get('harga'));
                    }),
                TextInput::make('harga')
                    ->label('Harga')
                    ->numeric()
                    ->required()
                    ->live()
                    ->afterStateUpdated(function ($state, Set $set, $get) {
                        $set('total_harga', $get('quantity') * $state);
                    }),
                TextInput::make('total_harga')
                    ->label('Total Harga')
                    ->disabled()
                    ->numeric()
                    ->dehydrated(),
                Select::make('id_gudang')
                    ->label('Pilih Gudang')
                    ->options(Gudang::query()->pluck('name', 'id'))
                    ->searchable()
                    ->preload()
                    ->disabled(fn($operation) => $operation === 'edit')
                    ->required(),
                Select::make('keterangan')
                    ->label('Keterangan')
                    ->options([
                        'Kulakan' => 'Kulakan',
                        'Retur' => 'Retur'
                    ])
                    ->searchable()
                    ->preload()
                    ->required()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('updated_at')->formatStateUsing(fn($state) => date('d F Y', strtotime($state))),
                TextColumn::make('product.name')
                    ->searchable()
                    ->label('Nama Produk'),
                TextColumn::make('harga')
                    ->money('idr'),
                TextColumn::make('total_harga')
                    ->money('idr'),
                TextColumn::make('quantity'),
                TextColumn::make('gudang.name')
                    ->label('Gudang'),
                TextColumn::make('keterangan')
                    ->label('Keterangan'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListStockIns::route('/'),
            'create' => Pages\CreateStockIn::route('/create'),
            'edit' => Pages\EditStockIn::route('/{record}/edit'),
        ];
    }

    public static function deleteAction(): Action
    {
        return parent::deleteAction()
            ->action(function (StockIn $record) {
                DB::transaction(function () use ($record) {
                    // 1. Kurangi stok di warehouse_stocks
                    WarehouseStock::where('id_product', $record->id_product)
                        ->where('id_gudang', $record->id_gudang)
                        ->decrement('quantity', $record->quantity);

                    // 2. Hapus record stock_in
                    $record->delete();
                });
            })
            ->requiresConfirmation()
            ->modalHeading('Hapus Transaksi Stok')
            ->modalDescription('Stok akan dikurangi otomatis. Yakin lanjutkan?');
    }
}
