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
        $data = [
            ['username' => 'admin', 'password' => Hash::make('sembarang'), 'role' => 'admin', 'bidang_id' => '123'],
            ['username' => 'sekretariat', 'password' => Hash::make('sekretariat'), 'role' => 'user', 'bidang_id' => '12301'],
            ['username' => 'bidangp3dasi', 'password' => Hash::make('bidangp3dasi'), 'role' => 'user', 'bidang_id' => '12302'],
            ['username' => 'bidangpkph', 'password' => Hash::make('bidangpkph'), 'role' => 'user', 'bidang_id' => '12303'],
            ['username' => 'bidangmutasi', 'password' => Hash::make('bidangmutasi'), 'role' => 'user', 'bidang_id' => '12304'],
            ['username' => 'bidangpengembangan', 'password' => Hash::make('bidangpengembangan'), 'role' => 'user', 'bidang_id' => '12305'],
            ['username' => 'uptppp', 'password' => Hash::make('uptppp'), 'role' => 'user', 'bidang_id' => '12306'],
        ];

        foreach ($data as $item) {
            DB::table('users')->updateOrInsert(
                ['username' => $item['username'], 'password' => $item['password'], 'role' => $item['role'], 'bidang_id' => $item['bidang_id'], 'created_at' => now(), 'updated_at' => now()]
            );
        }
    }
}
