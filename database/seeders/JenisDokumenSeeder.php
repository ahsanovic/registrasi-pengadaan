<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class JenisDokumenSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            ['jenis_dokumen' => 'Berita Acara Pembukaan Penawaran'],
            ['jenis_dokumen' => 'Berita Acara Klarifikasi/Negosiasi'],
            ['jenis_dokumen' => 'Berita Acara Evaluasi Penawaran'],
            ['jenis_dokumen' => 'Berita Acara Hasil Pengadaan'],
            ['jenis_dokumen' => 'Berita Acara Hasil Pengadaan Langsung'],
            ['jenis_dokumen' => 'Berita Acara Penjelasan Anwizing'],
            ['jenis_dokumen' => 'Berita Acara Penjelasan Pekerjaan'],
            ['jenis_dokumen' => 'Berita Acara Pembelian Langsung'],
            ['jenis_dokumen' => 'Berita Acara Kemajuan Pekerjaan'],
            ['jenis_dokumen' => 'Berita Acara Pembayaran'],
            ['jenis_dokumen' => 'BASTHP'],
            ['jenis_dokumen' => 'BAHPA'],
            ['jenis_dokumen' => 'BAPP'],
            ['jenis_dokumen' => 'BAPBJ'],
            ['jenis_dokumen' => 'Undangan Permintaan Penawaran'],
            ['jenis_dokumen' => 'Undangan Persiapan Pengadaan Langsung'],
            ['jenis_dokumen' => 'Nota Dinas Penetapan'],
            ['jenis_dokumen' => 'Nota Dinas Penyampaian Berita Acara Pengadaan'],
            ['jenis_dokumen' => 'Penunjukan/Penetapan'],
            ['jenis_dokumen' => 'Surat Perintah Kerja'],
        ];

        foreach ($data as $item) {
            DB::table('ref_jenis_dokumen')->updateOrInsert(
                ['jenis_dokumen' => $item['jenis_dokumen'], 'created_at' => now(), 'updated_at' => now()]
            );
        }
    }
}
