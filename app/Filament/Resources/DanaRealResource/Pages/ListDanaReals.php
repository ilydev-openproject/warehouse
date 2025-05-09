<?php

namespace App\Filament\Resources\DanaRealResource\Pages;

use Carbon\Carbon;
use Filament\Actions;
use App\Models\DanaReal;
use App\Models\Template;
use Filament\Forms\Form;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Forms\Components\DatePicker;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\DanaRealResource;

class ListDanaReals extends ListRecords
{
    protected static string $resource = DanaRealResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('add_with_template')
                ->label('Tambah dengan Template')
                ->form([
                    TextInput::make('minggu_ke')
                        ->label('Minggu ke')
                        ->required(),
                    DatePicker::make('tanggal')
                        ->label('Tanggal')
                        ->displayFormat('F Y')
                        ->native(false)
                        ->required(),
                    Select::make('template')
                        ->options(Template::all()->pluck('name', 'id'))
                        ->native(false)
                        ->reactive()
                ])
                ->action(function (array $data) {
                    // Ambil template yang dipilih
                    $template = Template::find($data['template']);

                    // Validasi: Cek apakah sudah ada data dengan minggu_ke dan tanggal yang sama pada bulan dan tahun yang sama
                    $tanggal = Carbon::parse($data['tanggal']);
                    $existingData = DanaReal::whereMonth('tanggal', $tanggal->month)
                        ->whereYear('tanggal', $tanggal->year)
                        ->where('minggu_ke', $data['minggu_ke'])
                        ->exists();

                    if ($existingData) {
                        // Jika data sudah ada, kirimkan pesan error
                        Notification::make()
                            ->title('Gagal!')
                            ->danger()
                            ->body('Data dengan minggu ke ' . $data['minggu_ke'] . ' dan tanggal ' . $tanggal->format('F Y') . ' sudah ada.')
                            ->send();

                        return; // Hentikan eksekusi lebih lanjut jika ada data yang sudah ada
                    }

                    // Jika tidak ada data yang sama, lanjutkan dengan proses pembuatan data
                    foreach ($template->items as $item) {
                        DanaReal::create([
                            'tanggal' => $data['tanggal'],
                            'minggu_ke' => $data['minggu_ke'],
                            'id_toko' => $item->id_toko,
                            'id_platform' => $item->id_platform,
                            'id_rekening' => $item->id_rekening,
                            'status' => 'audit',
                            'template_id' => $data['template'],  // Pastikan Anda menyimpan ID template jika dibutuhkan
                        ]);
                    }

                    // Kirimkan notifikasi sukses setelah data berhasil ditambahkan
                    Notification::make()
                        ->title('Sukses!')
                        ->success()
                        ->body('Data berhasil ditambahkan!')
                        ->send();
                })
        ];
    }
}
