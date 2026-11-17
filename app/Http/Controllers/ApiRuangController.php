<?php

namespace App\Http\Controllers;

use App\Models\ApiRuang;
use App\Services\SimpttRuangSyncService;
use Throwable;

class ApiRuangController extends Controller
{
    public function index()
    {
        return view('api-ruang.index', [
            'ruangs' => ApiRuang::query()->orderBy('kode')->paginate(15),
        ]);
    }

    public function sync(SimpttRuangSyncService $service)
    {
        try {
            $stats = $service->sync();
        } catch (Throwable $exception) {
            return back()->with('error', 'Sinkron API ruang gagal: '.$exception->getMessage());
        }

        return back()->with('success', sprintf(
            'Sinkron ruangan SB selesai: %d ditemukan, %d baru, %d diperbarui, %d dilewati. Master laboratorium ikut diperbarui.',
            $stats['ditemukan'],
            $stats['baru'],
            $stats['diperbarui'],
            $stats['dilewati']
        ));
    }
}
