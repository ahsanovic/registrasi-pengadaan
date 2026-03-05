<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RefBidangSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            ['id' => '123', 'parent_id' => '1', 'nama' => 'Badan Kepegawaian Daerah'],
            ['id' => '12301', 'parent_id' => '123', 'nama' => 'Sekretariat'],
            ['id' => '12302', 'parent_id' => '123', 'nama' => 'Bidang P3Dasi'],
            ['id' => '12303', 'parent_id' => '123', 'nama' => 'Bidang PKPH'],
            ['id' => '12304', 'parent_id' => '123', 'nama' => 'Bidang Mutasi'],
            ['id' => '12305', 'parent_id' => '123', 'nama' => 'Bidang Pengembangan'],
            ['id' => '12306', 'parent_id' => '123', 'nama' => 'UPT Penilaian Pusat Pegawai'],
        ];
        
        foreach ($data as $item) {
            DB::table('ref_bidang')->updateOrInsert(
                ['id' => $item['id'], 'parent_id' => $item['parent_id'], 'nama' => $item['nama'], 'created_at' => now(), 'updated_at' => now()]
            );
        }
    }
}
