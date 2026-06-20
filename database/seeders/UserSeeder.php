<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'name'       => 'admin',
                'email'      => 'admin@distributor.com',
                'password'   => Hash::make('password'),
                'role'       => 'admin',
                'nik'        => '001',
                'status'     => '1',
                'divisi'     => null,
                'sales'      => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name'       => 'gudang',
                'email'      => 'gudang@distributor.com',
                'password'   => Hash::make('password'),
                'role'       => 'gudang',
                'nik'        => '002',
                'status'     => '1',
                'divisi'     => 'gudang',
                'sales'      => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name'       => 'kasir',
                'email'      => 'kasir@distributor.com',
                'password'   => Hash::make('password'),
                'role'       => 'kasir',
                'nik'        => '003',
                'status'     => '1',
                'divisi'     => 'keuangan',
                'sales'      => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name'       => 'sales1',
                'email'      => 'sales1@distributor.com',
                'password'   => Hash::make('password'),
                'role'       => 'sales',
                'nik'        => '004',
                'status'     => '1',
                'divisi'     => 'sales',
                'sales'      => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name'       => 'owner',
                'email'      => 'owner@distributor.com',
                'password'   => Hash::make('password'),
                'role'       => 'owner',
                'nik'        => '005',
                'status'     => '1',
                'divisi'     => null,
                'sales'      => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // Gunakan upsert agar tidak duplikat jika dijalankan ulang
        foreach ($users as $user) {
            DB::table('users')->updateOrInsert(
                ['email' => $user['email']],
                $user
            );
        }

        $this->command->info('✅ UserSeeder selesai: ' . count($users) . ' user berhasil di-seed.');
    }
}
