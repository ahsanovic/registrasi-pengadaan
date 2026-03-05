<?php

namespace App\Services;

use App\Models\DokumenPengadaan;
use App\Models\NotdinKpa;
use App\Models\NotdinPpkom;
use App\Models\SpaceNomor;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AgendaNumberService
{
    public function reserveForDate(string $tanggal): int
    {
        $date = Carbon::parse($tanggal)->toDateString();
        $tahun = (int) Carbon::parse($tanggal)->year;
        $todayJakarta = Carbon::now('Asia/Jakarta')->toDateString();

        return DB::transaction(function () use ($date, $tahun, $todayJakarta) {
            $spaceNomor = SpaceNomor::query()
                ->where('tahun', $tahun)
                ->whereDate('tanggal', $date)
                ->whereNull('used_at')
                ->orderBy('nomor_agenda', 'asc')
                ->lockForUpdate()
                ->first();

            if ($spaceNomor) {
                $spaceNomor->update(['used_at' => now()]);
                return (int) $spaceNomor->nomor_agenda;
            }

            // Slot lama hanya dipakai untuk input tanggal mundur.
            if ($date < $todayJakarta) {
                $prevSpaceNomor = SpaceNomor::query()
                    ->where('tahun', $tahun)
                    ->whereDate('tanggal', '<', $date)
                    ->whereNull('used_at')
                    ->orderBy('tanggal', 'desc')
                    ->orderBy('nomor_agenda', 'asc')
                    ->lockForUpdate()
                    ->first();

                if ($prevSpaceNomor) {
                    $prevSpaceNomor->update(['used_at' => now()]);
                    return (int) $prevSpaceNomor->nomor_agenda;
                }
            }

            $nomorBaru = $this->getLastNumberInYear($tahun) + 1;

            SpaceNomor::create([
                'tahun' => $tahun,
                'tanggal' => $date,
                'nomor_agenda' => $nomorBaru,
                'used_at' => now(),
            ]);

            return (int) $nomorBaru;
        });
    }

    public function generateDailySpaceNumber(?Carbon $nowJakarta = null, int $jumlahNomor = 50): array
    {
        $jumlahNomor = max(1, $jumlahNomor);
        $now = $nowJakarta ?: Carbon::now('Asia/Jakarta');
        $tanggal = $now->toDateString();
        $tahun = (int) $now->year;

        return DB::transaction(function () use ($tanggal, $tahun, $jumlahNomor) {
            $lastNomorUserHariIni = $this->getMaxAgendaByDateAcrossMenus($tanggal);
            $lastNomorSpaceHariIni = (int) (SpaceNomor::query()
                ->where('tahun', $tahun)
                ->whereDate('tanggal', $tanggal)
                ->max('nomor_agenda') ?? 0);

            if ($lastNomorUserHariIni > 0 || $lastNomorSpaceHariIni > 0) {
                $nomorTerakhirHariIni = max($lastNomorUserHariIni, $lastNomorSpaceHariIni);
            } else {
                $spaceNomorTerakhirSebelumnya = SpaceNomor::query()
                    ->where('tahun', $tahun)
                    ->whereDate('tanggal', '<', $tanggal)
                    ->orderBy('tanggal', 'desc')
                    ->orderBy('nomor_agenda', 'desc')
                    ->first();

                if ($spaceNomorTerakhirSebelumnya) {
                    $nomorTerakhirHariIni = (int) $spaceNomorTerakhirSebelumnya->nomor_agenda;
                } else {
                    $nomorTerakhirHariIni = $this->getLastNumberInYear($tahun);
                }
            }

            $nomorAwal = $nomorTerakhirHariIni + 1;
            $jumlahDibuat = 0;
            $jumlahSudahAda = 0;

            for ($i = 0; $i < $jumlahNomor; $i++) {
                $nomorAgenda = $nomorAwal + $i;
                $spaceNomor = SpaceNomor::firstOrCreate([
                    'tahun' => $tahun,
                    'tanggal' => $tanggal,
                    'nomor_agenda' => $nomorAgenda,
                ]);

                if ($spaceNomor->wasRecentlyCreated) {
                    $jumlahDibuat++;
                } else {
                    $jumlahSudahAda++;
                }
            }

            return [
                'tanggal' => $tanggal,
                'nomor_awal' => $nomorAwal,
                'nomor_akhir' => $nomorAwal + $jumlahNomor - 1,
                'jumlah_diminta' => $jumlahNomor,
                'jumlah_dibuat' => $jumlahDibuat,
                'jumlah_sudah_ada' => $jumlahSudahAda,
            ];
        });
    }

    private function getLastNumberInYear(int $tahun): int
    {
        $lastNomorSpace = (int) (SpaceNomor::query()
            ->where('tahun', $tahun)
            ->max('nomor_agenda') ?? 0);

        $lastNomorUser = $this->getMaxAgendaByYearAcrossMenus($tahun);

        return max($lastNomorSpace, $lastNomorUser);
    }

    private function getMaxAgendaByDateAcrossMenus(string $tanggal): int
    {
        $maxKpa = (int) (NotdinKpa::query()
            ->whereDate('tanggal', $tanggal)
            ->max(DB::raw('CAST(nomor_agenda AS UNSIGNED)')) ?? 0);

        $maxPpkom = (int) (NotdinPpkom::query()
            ->whereDate('tanggal', $tanggal)
            ->max(DB::raw('CAST(nomor_agenda AS UNSIGNED)')) ?? 0);

        $maxDokumen = (int) (DokumenPengadaan::query()
            ->whereDate('tanggal', $tanggal)
            ->max(DB::raw('CAST(nomor_agenda AS UNSIGNED)')) ?? 0);

        return max($maxKpa, $maxPpkom, $maxDokumen);
    }

    private function getMaxAgendaByYearAcrossMenus(int $tahun): int
    {
        $maxKpa = (int) (NotdinKpa::query()
            ->whereYear('tanggal', $tahun)
            ->max(DB::raw('CAST(nomor_agenda AS UNSIGNED)')) ?? 0);

        $maxPpkom = (int) (NotdinPpkom::query()
            ->whereYear('tanggal', $tahun)
            ->max(DB::raw('CAST(nomor_agenda AS UNSIGNED)')) ?? 0);

        $maxDokumen = (int) (DokumenPengadaan::query()
            ->whereYear('tanggal', $tahun)
            ->max(DB::raw('CAST(nomor_agenda AS UNSIGNED)')) ?? 0);

        return max($maxKpa, $maxPpkom, $maxDokumen);
    }

}
