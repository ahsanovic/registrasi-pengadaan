<?php

use App\Services\AgendaNumberService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('agenda:generate-space-nomor', function () {
    $result = app(AgendaNumberService::class)->generateDailySpaceNumber(null, 50);

    if ($result['skipped']) {
        $alasan = $result['reason'] === 'weekend' ? 'weekend (Sabtu/Minggu)' : 'tanggal libur';
        $this->line("Skip generate space_nomor {$result['tanggal']} karena {$alasan}.");
        return;
    }

    if ($result['jumlah_dibuat'] > 0) {
        $this->info(
            "Berhasil generate space_nomor {$result['tanggal']} | range {$result['nomor_awal']}-{$result['nomor_akhir']} | dibuat {$result['jumlah_dibuat']} nomor"
        );
        return;
    }

    $this->line(
        "Tidak ada nomor baru. range {$result['nomor_awal']}-{$result['nomor_akhir']} sudah ada semua ({$result['jumlah_sudah_ada']} nomor)."
    );
})->purpose('Generate nomor agenda harian ke tabel space_nomor');
