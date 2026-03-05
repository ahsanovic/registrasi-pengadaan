<?php

namespace App\Http\Controllers;

use App\Models\NotdinKpa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SearchController extends Controller
{
    public function rencanaKegiatan(Request $request)
    {
        $keyword = trim((string) $request->query('q', ''));

        if (mb_strlen($keyword) < 2) {
            return response()->json([
                'data' => [],
            ]);
        }

        $user = Auth::user();

        if ($user->role != 'admin') {
            $results = NotdinKpa::query()
                ->select(['id', 'rencana_kegiatan'])
                ->whereNotNull('rencana_kegiatan')
                ->where('rencana_kegiatan', 'like', '%' . $keyword . '%')
                ->where('bidang_id', $user->bidang_id)
                ->orderByDesc('id')
                ->limit(10)
                ->get()
                ->map(fn (NotdinKpa $item) => [
                    'id' => $item->id,
                    'label' => (string) $item->rencana_kegiatan,
                    'url' => route('notdin-kpa', ['search' => (string) $item->rencana_kegiatan]),
                ])
                ->values();
        } else {
            $results = NotdinKpa::query()
                ->select(['id', 'rencana_kegiatan'])
                ->whereNotNull('rencana_kegiatan')
                ->where('rencana_kegiatan', 'like', '%' . $keyword . '%')
                ->orderByDesc('id')
                ->limit(10)
                ->get()
                ->map(fn (NotdinKpa $item) => [
                    'id' => $item->id,
                    'label' => (string) $item->rencana_kegiatan,
                    'url' => route('notdin-kpa', ['search' => (string) $item->rencana_kegiatan]),
                ])
                ->values();
        }

        return response()->json([
            'data' => $results,
        ]);
    }
}
