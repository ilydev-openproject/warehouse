<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Gudang;
use App\Models\Orders;
use App\Models\Platform;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\WarehouseStock;
use Illuminate\Support\Carbon;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Repeater;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Illuminate\Database\Eloquent\Builder;
use Filament\Infolists\Components\TextEntry;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Actions\DeleteBulkAction;
use App\Filament\Resources\OrderResource\Pages;
use Filament\Infolists\Components\RepeatableEntry;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Section as SectionLay;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\ToggleButtons;

class OrderResource extends Resource
{
    protected static ?string $model = Orders::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    protected static ?string $navigationGroup = 'Sale';

    protected static ?int $navigationSort = 0;

    protected static ?string $modelLabel = 'Pesanan';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                SectionLay::make('Detail Customer')
                    ->schema([
                        TextInput::make('resi')
                            ->label('Masukkan Nomor Resi')
                            ->unique(ignorable: fn($record) => $record)
                            ->required()
                            ->validationMessages([
                                'required' => 'Tidak Boleh Kosong',
                                'unique' => 'Resi sudah ada di database',
                            ]),
                        TextInput::make('customer_name')
                            ->label('Nama Customer')
                            ->required()
                            ->validationMessages([
                                'required' => 'Tidak Boleh Kosong'
                            ]),
                        TextInput::make('alamat')
                            ->label('Alamat')
                            ->required()
                            ->validationMessages([
                                'required' => 'Tidak Boleh Kosong'
                            ]),
                        Select::make('id_platform')
                            ->options(Platform::query()->pluck('name', 'id'))
                            ->label('Pilih Platform')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->validationMessages([
                                'required' => 'Tidak Boleh Kosong'
                            ]),
                        TextInput::make('kode_bigseller')

                    ])
                    ->columnSpan(1),

                Group::make()
                    ->schema([
                        SectionLay::make('Detail Keuangan')
                            ->schema([
                                // Perhitungan keuangan
                                TextInput::make('gross_amount')
                                    ->label('Gross Amount')
                                    ->numeric()
                                    ->required()
                                    ->dehydrated()
                                    ->validationMessages([
                                        'required' => 'Tidak Boleh Kosong'
                                    ]),

                                TextInput::make('shipping_cost')
                                    ->label('Biaya Kirim')
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0)
                                    ->required()
                                    ->reactive(),

                                TextInput::make('net_amount')
                                    ->label('Net Amount')
                                    ->numeric()
                                    ->dehydrated(),
                            ])
                            ->columnSpan(2)
                            ->columns(3),
                        SectionLay::make('Status Pesanan')
                            ->schema([
                                ToggleButtons::make('status')
                                    ->options([
                                        'process' => 'Proses',
                                        'shipped' => 'Delivered',
                                        'returned' => 'Retur',
                                        'lost' => 'Hilang',
                                    ])
                                    ->inline()
                                    ->colors([
                                        'returned' => 'gray',
                                        'process' => 'warning',
                                        'shipped' => 'success',
                                        'lost' => 'danger',
                                    ])
                                    ->icons([
                                        'returned' => 'heroicon-o-arrow-path',
                                        'process' => 'heroicon-o-truck',
                                        'shipped' => 'heroicon-o-check-circle',
                                        'lost' => 'heroicon-o-trash',
                                    ])
                            ])
                            ->columnSpan(2)
                            ->visible(fn($livewire) => $livewire instanceof \Filament\Resources\Pages\EditRecord),
                        SectionLay::make('Detail Produk')
                            ->schema([
                                Repeater::make('order_items')
                                    ->label('Produk Pesanan')
                                    ->relationship()
                                    ->schema([
                                        Select::make('fulfillment_type')
                                            ->label('Sumber Pengiriman')
                                            ->options([
                                                'warehouse' => 'Gudang',
                                                'dropship' => 'Dropship'
                                            ])
                                            ->required()
                                            ->searchable()
                                            ->live()
                                            ->afterStateUpdated(function ($state, $set) {
                                                $set('id_gudang', $state === 'warehouse' ? null : null);
                                            }),

                                        Select::make('id_gudang')
                                            ->label('Gudang')
                                            ->options(Gudang::query()->pluck('name', 'id'))
                                            ->searchable()
                                            ->required(fn($get) => $get('fulfillment_type') === 'warehouse')
                                            ->hidden(fn($get) => $get('fulfillment_type') !== 'warehouse'),

                                        Select::make('id_product')
                                            ->label('Produk')
                                            ->options(function (callable $get) {
                                                $gudangId = $get('id_gudang');
                                                $fulfillmentType = $get('fulfillment_type');

                                                // Cek jika fulfillment_type adalah 'warehouse'
                                                if ($fulfillmentType === 'warehouse' && $gudangId) {
                                                    // Tampilkan produk yang memiliki stok di gudang yang dipilih
                                                    return \App\Models\Product::all()->mapWithKeys(function ($product) use ($gudangId) {
                                                        $stock = \App\Models\WarehouseStock::where('id_product', $product->id)
                                                            ->where('id_gudang', $gudangId)
                                                            ->sum('quantity');
                                                        return [$product->id => $product->name . ' (' . $stock . ' stok)'];
                                                    });
                                                }

                                                // Jika fulfillment_type adalah 'dropship', tampilkan semua produk tanpa filter stok
                                                return \App\Models\Product::all()->mapWithKeys(function ($product) {
                                                    return [$product->id => $product->name];
                                                });
                                            })
                                            ->searchable()
                                            ->required()
                                            ->reactive()
                                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                                $gudangId = $get('id_gudang');
                                                $fulfillmentType = $get('fulfillment_type');

                                                // Jika fulfillment_type adalah 'warehouse', update stok produk
                                                if ($fulfillmentType === 'warehouse' && $gudangId) {
                                                    $stok = \App\Models\WarehouseStock::where('id_product', $state)
                                                        ->where('id_gudang', $gudangId)
                                                        ->sum('quantity');
                                                    $set('current_stock', $stok);
                                                } else {
                                                    $set('current_stock', null); // Jika dropship, set stok null
                                                }
                                            }),

                                        TextInput::make('quantity')
                                            ->label('Jumlah')
                                            ->numeric()
                                            ->default(1)
                                            ->required()
                                            ->minValue(1)
                                            ->live()
                                            ->rules([
                                                function (callable $get) {
                                                    return function (string $attribute, $value, \Closure $fail) use ($get) {
                                                        $productId = $get('id_product');
                                                        $gudangId = $get('id_gudang');
                                                        $fulfillmentType = $get('fulfillment_type');

                                                        if ($productId && $gudangId && $fulfillmentType === 'warehouse') {
                                                            $stok = \App\Models\WarehouseStock::where('id_product', $productId)
                                                                ->where('id_gudang', $gudangId)
                                                                ->sum('quantity');

                                                            if ($value > $stok) {
                                                                $fail("Jumlah melebihi stok gudang ($stok)");
                                                            }
                                                        }
                                                    };
                                                },
                                            ])
                                            ->validationMessages([
                                                'required' => 'Jumlah produk harus diisi.',
                                                'numeric' => 'Jumlah harus berupa angka.',
                                            ]),

                                    ])
                                    ->columns(3)
                                    ->columnSpanFull()
                                    ->required()
                            ])
                            ->columnSpan(2)
                    ])
                    ->columnSpan(2)
            ])
            ->columns(3)
            ->disabled(fn($record) => !is_null($record?->deleted_at));
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('updated_at')->formatStateUsing(fn($state) => date('d F Y', strtotime($state)))
                    ->sortable(),
                TextColumn::make('resi')
                    ->searchable(),
                TextColumn::make('customer_name')
                    ->limit(10),
                TextColumn::make('alamat')
                    ->limit(12),
                TextColumn::make('platform.name'),
                TextColumn::make('gross_amount')
                    ->money('idr')
                    ->label('Omset Kotor')
                    ->sortable(),
                TextInputColumn::make('net_amount')
                    ->label('Omset Bersih')
                    ->extraAttributes([
                        'style' => 'width: 100px; min-width: 100px;',
                    ])
                    ->afterStateUpdated(function ($state, $record) {
                        if (!empty($state)) {
                            $record->update([
                                'net_amount' => $state,
                                'status' => 'shipped',
                            ]);
                        }
                    }),

                TextColumn::make('status')
                    ->color(fn(string $state): string => match ($state) {
                        'returned' => 'gray',
                        'process' => 'warning',
                        'shipped' => 'success',
                        'lost' => 'danger',
                    })
                    ->icon(fn(string $state): string => match ($state) {
                        'returned' => 'heroicon-o-arrow-path',
                        'process' => 'heroicon-o-truck',
                        'shipped' => 'heroicon-o-check-circle',
                        'lost' => 'heroicon-o-trash',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'returned' => 'Retur',
                        'process' => 'Proses',
                        'shipped' => 'Terkirim',
                        'lost' => 'Hilang',
                        default => ucwords($state),
                    })
                    ->badge(),
                TextColumn::make('kode_bigseller'),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),

                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('mulai')
                            ->placeholder(fn($state): string => 'Dec 18, ' . now()->subYear()->format('Y')),
                        Forms\Components\DatePicker::make('sampai')
                            ->placeholder(fn($state): string => now()->format('M d, Y')),
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
                ActionGroup::make([
                    Action::make('ubahStatus')
                        ->label('Ubah Status')
                        ->icon('heroicon-o-cog-6-tooth')
                        ->color('warning')
                        ->form(fn(Orders $record) => [
                            Select::make('status')
                                ->label('Status Baru')
                                ->searchable()
                                ->options(collect([
                                    'process' => 'Proses',
                                    'shipped' => 'Delivered',
                                    'returned' => 'Retur',
                                    'lost' => 'Hilang',
                                ])->except($record->status))
                                ->required(),
                        ])
                        ->action(function (array $data, Orders $record) {
                            // Update status sesuai pilihan dari form
                            $record->status = $data['status'];
                            $record->save();
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Ubah Status Pesanan'),
                    Tables\Actions\ViewAction::make()
                        ->label('Lihat Produk')
                        ->color('primary')
                        ->icon('heroicon-o-shopping-bag'),
                    Tables\Actions\EditAction::make()
                        ->hidden(fn($record): bool => !is_null($record->deleted_at)),
                    DeleteAction::make()
                        ->action(function (Orders $record) {
                            DB::transaction(function () use ($record) {
                                foreach ($record->order_items as $item) {
                                    if ($item->fulfillment_type === 'warehouse') {
                                        WarehouseStock::where('id_product', $item->id_product)
                                            ->where('id_gudang', $item->id_gudang)
                                            ->increment('quantity', $item->quantity);
                                    }
                                    // Hapus itemnya
                                    $item->delete();
                                }

                                // Terakhir hapus ordernya
                                $record->delete();
                            });

                            Notification::make()
                                ->success()
                                ->title('Pesanan berhasil dihapus dan stok dikembalikan.')
                                ->send();
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Hapus Pesanan')
                        ->modalDescription('Semua item dan stoknya akan dikembalikan. Yakin ingin hapus?')
                ]),
            ])
            ->bulkActions([
                Tables\Actions\RestoreBulkAction::make()
                    ->label('Restore Data')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->action(function (Collection $records) {
                        DB::transaction(function () use ($records) {
                            foreach ($records as $record) {
                                // Mengembalikan pesanan dari soft deleted ke status aktif (restore ke tabel asli)
                                $record->restore();

                                foreach ($record->order_items as $item) {
                                    if ($item->fulfillment_type === 'warehouse') {
                                        // Stok dikembalikan saat restore (menambah kembali stok yang telah dibatalkan)
                                        WarehouseStock::where('id_product', $item->id_product)
                                            ->where('id_gudang', $item->id_gudang)
                                            ->decrement('quantity', $item->quantity); // Menambah stok
                                    }
                                }
                            }
                        });

                        Notification::make()
                            ->success()
                            ->title('Pesanan berhasil dipulihkan dan stok dikembalikan.')
                            ->send();
                    }),

                BulkAction::make('hapus-pesanan')
                    ->label('Buang ke Sampah')
                    ->icon('heroicon-o-trash')
                    ->requiresConfirmation()
                    ->modalHeading('Buang ke Sampah')
                    ->modalDescription('Orderan akan dipindahkan ke sampah, dan stok akan dikembalikan.')
                    ->action(function (Collection $records) {
                        DB::transaction(function () use ($records) {
                            foreach ($records as $record) {
                                foreach ($record->order_items as $item) {
                                    if ($item->fulfillment_type === 'warehouse') {
                                        WarehouseStock::where('id_product', $item->id_product)
                                            ->where('id_gudang', $item->id_gudang)
                                            ->increment('quantity', $item->quantity);
                                    }

                                    $item->delete(); // langsung force delete
                                }

                                $record->delete(); // langsung force delete
                            }
                        });

                        Notification::make()
                            ->success()
                            ->title('Pesanan di buang ke sampah dan stok dikembalikan.')
                            ->send();
                    }),

                DeleteBulkAction::make()
                    ->label('Hapus Permanen')
                    ->requiresConfirmation()
                    ->modalHeading('Hapus Permanen')
                    ->modalDescription('Orderan akan dihapus secara permanen, dan stok akan dikembalikan.')
                    ->action(function (Collection $records) {
                        DB::transaction(function () use ($records) {
                            foreach ($records as $record) {
                                foreach ($record->order_items as $item) {
                                    if ($item->fulfillment_type === 'warehouse') {
                                        WarehouseStock::where('id_product', $item->id_product)
                                            ->where('id_gudang', $item->id_gudang)
                                            ->increment('quantity', $item->quantity);
                                    }

                                    $item->forceDelete(); // langsung force delete
                                }

                                $record->forceDelete(); // langsung force delete
                            }
                        });

                        Notification::make()
                            ->success()
                            ->title('Pesanan berhasil dihapus permanen dan stok dikembalikan.')
                            ->send();
                    }),
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
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }


    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                RepeatableEntry::make('order_items')
                    ->schema([
                        // Kolom untuk setiap item produk
                        TextEntry::make('product.name')
                            ->label('Nama Produk')
                            ->weight('bold'),

                        TextEntry::make('quantity')
                            ->label('Jumlah')
                            ->formatStateUsing(fn($state) => "{$state} pcs"),
                        TextEntry::make('fulfillment_type')
                            ->label('Asal')
                            ->badge()
                            ->color(fn(string $state): string => match ($state) {
                                'warehouse' => 'info',
                                'dropship' => 'success',
                                default => 'gray',
                            })
                            ->formatStateUsing(fn(string $state): string => match ($state) {
                                'warehouse' => 'Gudang',
                                'dropship' => 'Dropship',
                            }),
                        TextEntry::make('gudang.name')
                    ])
                    ->columnSpanFull()
                    ->columns(4)
                    ->grid(1)
            ]);
    }
}
