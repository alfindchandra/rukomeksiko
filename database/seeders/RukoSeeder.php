<?php
namespace Database\Seeders;

use App\Models\Ruko;
use Illuminate\Database\Seeder;

class RukoSeeder extends Seeder
{
    public function run(): void
    {
        $rukos = [
            [
                'kode_ruko' => 'BJN001',
                'nama_ruko' => 'Ruko Dander',
                'alamat' => 'Jl. Dander',
                'kota' => 'Dander',
                'nomor_telepon' => '081382176161'
            ],
            [
                'kode_ruko' => 'BJN002',
                'nama_ruko' => 'Ruko Temayang',
                'alamat' => 'Jl. Temayang',
                'kota' => 'Temayang',
                'nomor_telepon' => '0817171627162'
            ],
            [
                'kode_ruko' => 'BJN003',
                'nama_ruko' => 'Ruko Kalitidu',
                'alamat' => 'Jl. Kalitidu',
                'kota' => 'Kalitidu',
                'nomor_telepon' => '087526178921'
            ],
            [
                'kode_ruko' => 'BJN004',
                'nama_ruko' => 'Ruko Balen',
                'alamat' => 'Jl. Balen',
                'kota' => 'Balen',
                'nomor_telepon' => '085261781653'
            ]
        ];

        foreach ($rukos as $ruko) {
            Ruko::create($ruko);
        }
    }
}
