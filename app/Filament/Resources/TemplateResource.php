<?php

namespace App\Filament\Resources;

use Filament\Forms;
use App\Models\Toko;
use Filament\Tables;
use App\Models\Platform;
use App\Models\Rekening;
use App\Models\Template;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Repeater;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Actions\Modal\Actions\Action;
use App\Filament\Resources\TemplateResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\TemplateResource\RelationManagers;

class TemplateResource extends Resource
{
    protected static ?string $model = Template::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';
    protected static ?string $navigationGroup = 'Marketing';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Group::make([
                    TextInput::make('name')
                        ->label('Nama Template')
                        ->required(),
                ])
                    ->columns(3),
                Repeater::make('items')
                    ->relationship()
                    ->reorderable()
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
                    ->columnSpanFull()
                    ->createItemButtonLabel('Tambah Item')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Template')
                    ->searchable(),

                TextColumn::make('items_count')
                    ->label('Jumlah Item')
                    ->counts('items'),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y'),
            ])
            ->actions([
                ViewAction::make()
                    ->label('Detail')
                    ->icon('heroicon-o-eye'),
                Tables\Actions\EditAction::make(),
            ])
            ->filters([
                //
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
            'index' => Pages\ListTemplates::route('/'),
            'create' => Pages\CreateTemplate::route('/create'),
            'edit' => Pages\EditTemplate::route('/{record}/edit'),
        ];
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                // Tambahkan schema atau field yang diperlukan di sini
            ]);
    }
}
