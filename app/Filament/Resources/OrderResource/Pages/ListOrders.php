<?php

namespace App\Filament\Resources\OrderResource\Pages;

use Filament\Actions;
use App\Imports\OrdersImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use Filament\Notifications\Notification;
use App\Filament\Resources\OrderResource;
use Filament\Forms\Components\FileUpload;
use Filament\Resources\Pages\ListRecords;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('Import Orders')
                ->label('Import Excel')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('success')
                ->form([
                    FileUpload::make('file')
                        ->label('Pilih File Excel')
                        ->disk('public') // => simpan di storage/app/public/
                        ->directory('imports')
                        ->acceptedFileTypes([
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            'application/vnd.ms-excel',
                        ])
                        ->required(),
                ])
                ->action(function (array $data) {
                    $filePath = storage_path('app/public/' . $data['file']);

                    if (!file_exists($filePath)) {
                        throw new \Exception("File [$filePath] does not exist.");
                    }

                    Excel::import(new OrdersImport, $filePath);

                    Notification::make()
                        ->title('Import Berhasil')
                        ->success()
                        ->send();
                })
                ->modalHeading('Import Data Order')
                ->modalSubmitActionLabel('Import'),
        ];
    }
}
