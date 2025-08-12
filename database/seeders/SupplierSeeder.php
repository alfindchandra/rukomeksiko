<?php

namespace Database\Seeders;

use App\Models\Supplier;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    public function run(): void
    {
        $suppliers = [
            [
                'nama_supplier' => 'PT Multi Bintang Indonesia Tbk',
                'alamat' => 'Jl. Industri No. 123, Jakarta',
                'nomor_telepon' => '021-12345678',
                'email' => 'info@Bintang.co.id',
                'kontak_person' => 'Budi Santoso'
            ],
            [
                'nama_supplier' => 'PT. Adi Makmur Sentosa',
                'alamat' => 'Jl. Mode No. 456, Bandung',
                'nomor_telepon' => '022-87654321',
                'email' => 'order@fashioncenter.co.id',
                'kontak_person' => 'Siti Rahayu'
            ]
        ];

        foreach ($suppliers as $supplier) {
            Supplier::create($supplier);
        }
    }
}
