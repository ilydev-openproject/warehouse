<?php

namespace App\Filament\Resources;

use Carbon\Carbon;
use Filament\Forms;
use App\Models\Toko;
use Filament\Tables;
use App\Models\DanaReal;
use App\Models\Platform;
use App\Models\Rekening;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\BadgeColumn;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\ToggleButtons;
use App\Filament\Resources\DanaRealResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\DanaRealResource\RelationManagers;

class DanaRealResource extends Resource
{
    protected static ?string $model = DanaReal::class;
    protected static ?string $navigationIcon = 'heroicon-o-cloud-arrow-down';
    protected static ?string $navigationLabel = 'Dana Real';
    protected static ?string $navigationGroup = 'Marketing';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Detail Toko')
                    ->schema([
                        Select::make('id_toko')
                            ->label('Nama Toko')
                            ->options(Toko::all()->pluck('name', 'id'))
                            ->native(false)
                            ->createOptionForm([
                                TextInput::make('name')
                                    ->unique('tokos', 'name')
                                    ->validationMessages([
                                        'unique' => 'Nama ini sudah ada di database.'
                                    ])
                                    ->required(),
                            ])
                            ->createOptionUsing(function (array $data) {
                                return Toko::create($data)->id;
                            })
                            ->required(),
                        Select::make('id_platform')
                            ->label('Nama Platform')
                            ->options(Platform::all()->pluck('name', 'id'))
                            ->native(false)
                            ->createOptionForm([
                                TextInput::make('name')
                                    ->unique('platforms', 'name')
                                    ->validationMessages([
                                        'unique' => 'Nama ini sudah ada di database.'
                                    ])
                                    ->required(),
                            ])
                            ->createOptionUsing(function (array $data) {
                                return Platform::create($data)->id;
                            })
                            ->required(),
                        Select::make('id_rekening')
                            ->label('Nama Rekening')
                            ->options(Rekening::all()->pluck('name', 'id'))
                            ->native(false)
                            ->createOptionForm([
                                TextInput::make('name')
                                    ->unique('rekenings', 'name')
                                    ->validationMessages([
                                        'unique' => 'Nama ini sudah ada di database.'
                                    ])
                                    ->required(),
                            ])
                            ->createOptionUsing(function (array $data) {
                                return Rekening::create($data)->id;
                            })
                            ->required(),
                    ])
                    ->columns(3)
                    ->columnSpanFull(),
                Group::make([
                    Section::make([
                        TextInput::make('saldo_awal')
                            ->label('Saldo Awal')
                            ->numeric()
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, callable $set, $get) {
                                $set('sisa_saldo', (int) $state - (int) $get('saldo_di_tarik'));
                            }),

                        TextInput::make('saldo_di_tarik')
                            ->label('Saldo di Tarik')
                            ->numeric()
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, callable $set, $get) {
                                $set('sisa_saldo', (int) $get('saldo_awal') - (int) $state);
                            }),

                        TextInput::make('sisa_saldo')
                            ->label('Sisa Saldo')
                            ->disabled()
                            ->dehydrated(false)
                            ->afterStateHydrated(function (callable $set, callable $get) {
                                $set('sisa_saldo', (int) $get('saldo_awal') - (int) $get('saldo_di_tarik'));
                            }),
                    ])
                        ->columnSpan(2),
                    Group::make([
                        Section::make([
                            TextInput::make('iklan')
                                ->label('Iklan')
                                ->numeric()
                                ->required(),
                            TextInput::make('omset')
                                ->label('Omset')
                                ->numeric()
                                ->required(),
                        ]),
                        Section::make([
                            ToggleButtons::make('status')
                                ->options([
                                    'audit' => 'Audit',
                                    'success' => 'Success',
                                ])
                                ->inline()
                                ->colors([
                                    'audit' => 'warning',
                                    'success' => 'success',
                                ])
                                ->icons([
                                    'audit' => 'heroicon-o-truck',
                                    'success' => 'heroicon-o-check-circle',
                                ])
                                ->default('audit')
                        ])
                    ])
                        ->columnSpan(1)
                ])
                    ->columns(3)
                    ->columnSpanFull()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('Tanggal Input')
                    ->dateTime('d M Y')
                    ->sortable(),

                TextColumn::make('toko.name')
                    ->label('Nama Toko')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('platform.name')
                    ->label('Platform')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('rekening.name')
                    ->label('Rekening')
                    ->searchable(),

                TextColumn::make('saldo_awal')
                    ->label('Saldo Awal')
                    ->money('IDR', true)
                    ->sortable(),

                TextColumn::make('saldo_di_tarik')
                    ->label('Saldo di Tarik')
                    ->money('IDR', true)
                    ->sortable(),

                TextColumn::make('sisa_saldo_formatted')
                    ->label('Sisa Saldo')
                    ->sortable(false)
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('iklan')
                    ->label('Iklan')
                    ->money('IDR', true)
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('omset')
                    ->label('Omset')
                    ->money('IDR', true)
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('status')
                    ->label('Status')
                    ->color(
                        fn(string $state): string => match ($state) {
                            'audit' => 'warning',
                            'success' => 'success',
                        }
                    )
                    ->icon(fn(string $state): string => match ($state) {
                        'audit' => 'heroicon-o-eye',
                        'success' => 'heroicon-o-check-circle',
                    })
                    ->badge()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
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
            'index' => Pages\ListDanaReals::route('/'),
            // 'create' => Pages\CreateDanaReal::route('/create'),
            // 'edit' => Pages\EditDanaReal::route('/{record}/edit'),
        ];
    }
}
