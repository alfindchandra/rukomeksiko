<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Admin Pusat
        User::create([
            'name' => 'Admin Pusat',
            'email' => 'admin@meksiko.com',
            'password' => Hash::make('password123'),
            'role' => 'admin_pusat',
            'ruko_id' => null
        ]);

        // Admin Ruko
        $rukos = \App\Models\Ruko::all();
        foreach ($rukos as $index => $ruko) {
            User::create([
                'name' => "Admin {$ruko->nama_ruko}",
                'email' => "{$ruko->kode_ruko}@meksiko.com",
                'password' => Hash::make('password123'),
                'role' => 'admin_ruko',
                'ruko_id' => $ruko->id
            ]);
        }
    }
}
