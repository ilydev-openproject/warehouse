<?php

namespace App\Imports;

use App\Models\Orders;
use Maatwebsite\Excel\Concerns\ToModel;

class OrdersImport implements ToModel
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        static $isHeader = true;

        if ($isHeader) {
            $isHeader = false;
            return null; // Skip baris header
        }

        Orders::firstOrCreate(
            ['resi' => $row[0] ?? null], // kolom unik
            [
                'customer_name' => $row[1] ?? null,
                'alamat' => $row[2] ?? null,
                'gross_amount' => is_numeric($row[4]) ? $row[4] : 0,
            ]
        );

        return null;
    }
}
