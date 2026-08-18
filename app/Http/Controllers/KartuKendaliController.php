<?php

namespace App\Http\Controllers;

use App\Models\Komputer;
use App\Models\InventarisIoTJaringan;
use App\Models\Laboratorium;
use App\Models\KartuKendali;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\KartuKendaliKomputerExport;
use App\Exports\KartuKendaliIotExport;

class KartuKendaliController extends Controller
{
    public function index(Request $request)
    {
        $query = KartuKendali::query()->with(['inspectable', 'tahunAjaran']);

        if ($request->filled('tipe')) {
            if ($request->tipe === 'komputer') {
                $query->where('inspectable_type', Komputer::class);
            } elseif ($request->tipe === 'iot') {
                $query->where('inspectable_type', InventarisIoTJaringan::class);
            }
        }

        if ($request->filled('tahun_ajaran_id')) {
            $query->where('tahun_ajaran_id', $request->tahun_ajaran_id);
        }

        if ($request->filled('laboratorium_id')) {
            $labId = $request->laboratorium_id;
            $query->whereHas('inspectable', function ($q) use ($labId) {
                $q->where('laboratorium_id', $labId);
            });
        }

        $kartuKendali = $query->latest('tanggal_pemeriksaan')->paginate(15)->withQueryString();
        $laboratoriums = Laboratorium::orderBy('nama_laboratorium')->get();
        $tahunList = TahunAjaran::orderBy('nama', 'desc')->get();

        return view('admin.kartu-kendali.index', compact('kartuKendali', 'laboratoriums', 'tahunList'));
    }

    public function exportKomputer(Request $request)
    {
        $query = KartuKendali::query()->where('inspectable_type', Komputer::class)->with(['inspectable.laboratorium', 'tahunAjaran']);

        if ($request->filled('tahun_ajaran_id')) {
            $query->where('tahun_ajaran_id', $request->tahun_ajaran_id);
        }

        if ($request->filled('laboratorium_id')) {
            $labId = $request->laboratorium_id;
            $query->whereHas('inspectable', function ($q) use ($labId) {
                $q->where('laboratorium_id', $labId);
            });
        }

        $data = $query->latest('tanggal_pemeriksaan')->get();

        return Excel::download(new KartuKendaliKomputerExport($data), 'kartu-kendali-komputer-' . now()->format('Y-m-d-H-i-s') . '.xlsx');
    }

    public function exportIot(Request $request)
    {
        $query = KartuKendali::query()->where('inspectable_type', InventarisIoTJaringan::class)->with(['inspectable', 'tahunAjaran']);

        if ($request->filled('tahun_ajaran_id')) {
            $query->where('tahun_ajaran_id', $request->tahun_ajaran_id);
        }

        $data = $query->latest('tanggal_pemeriksaan')->get();

        return Excel::download(new KartuKendaliIotExport($data), 'kartu-kendali-iot-jaringan-' . now()->format('Y-m-d-H-i-s') . '.xlsx');
    }
}
