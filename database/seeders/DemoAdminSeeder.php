<?php

namespace Database\Seeders;

use App\Models\Umum;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoAdminSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            throw new \RuntimeException('Demo admin is allowed only in local or testing environments.');
        }

        Umum::firstOrCreate(['email' => 'admin@traveltour.test'], [
            'id_pengguna' => 'USR-DEMO-ADMIN',
            'nama_lengkap' => 'Admin Demo',
            'nomor_telepon' => '080000000000',
            'password' => Hash::make('password123'),
            'role' => 'Admin',
        ]);
    }
}
