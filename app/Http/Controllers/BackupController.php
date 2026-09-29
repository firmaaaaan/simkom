<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\DatabaseBackupService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use RuntimeException;
use Throwable;

class BackupController extends Controller
{
    public function __construct(private readonly DatabaseBackupService $backup) {}

    public function index()
    {
        $summary = $this->backup->summary();
        $files = DatabaseBackupService::files();

        return view('backups.index', compact('summary', 'files'));
    }

    public function download()
    {
        $payload = $this->backup->export();

        $json = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        return response($json, 200, [
            'Content-Type' => 'application/json',
            'Content-Disposition' => 'attachment; filename="backup-simkom-'.date('Y-m-d-His').'.json"',
        ]);
    }

    public function restore(Request $request)
    {
        $validated = $request->validate([
            'file' => 'required|file|max:51200|mimes:json,txt',
            'confirmation' => 'required|string',
        ], [
            'file.required' => 'Pilih file backup (.json) terlebih dahulu.',
            'file.file' => 'File backup tidak valid.',
            'file.max' => 'Ukuran file backup maksimal 50 MB.',
            'file.mimes' => 'File harus berformat JSON.',
            'confirmation.required' => 'Tulis RESTORE pada kolom konfirmasi.',
        ]);

        if ($validated['confirmation'] !== 'RESTORE') {
            return back()->with('error', 'Konfirmasi tidak sesuai. Ketik RESTORE (huruf besar) untuk melanjutkan.');
        }

        $payload = json_decode((string) file_get_contents($request->file('file')->getRealPath()), true);

        if (! is_array($payload)) {
            return back()->with('error', 'File backup bukan JSON valid.');
        }

        try {
            $stats = $this->backup->restore($payload);
        } catch (Throwable $e) {
            return back()->with('error', 'Restore gagal dan semua perubahan dibatalkan: '.$e->getMessage());
        }

        // Tabel sessions tidak ikut direstore, sehingga sesi login masih hidup —
        // tetapi akun yang login mungkin tidak ada di data hasil restore.
        if (! Auth::check() || ! User::query()->whereKey(Auth::id())->exists()) {
            Auth::logout();

            return redirect()->route('login')
                ->with('error', 'Restore berhasil, tetapi akun Anda tidak ada dalam data hasil restore. Silakan login ulang.');
        }

        return back()->with('success', sprintf(
            'Restore berhasil: %d tabel, %d baris data dimuat kembali.',
            $stats['tables'],
            $stats['rows']
        ));
    }

    public function downloadFile(string $filename)
    {
        $path = $this->resolveFilePath($filename);

        return response()->download($path, basename($path), [
            'Content-Type' => 'application/json',
        ]);
    }

    public function destroyFile(string $filename)
    {
        $path = $this->resolveFilePath($filename);

        if (! unlink($path)) {
            return back()->with('error', 'File backup gagal dihapus.');
        }

        return back()->with('success', 'File backup "'.basename($path).'" berhasil dihapus.');
    }

    /**
     * Validasi nama file (anti path traversal) lalu kembalikan path absolutnya.
     */
    private function resolveFilePath(string $filename): string
    {
        try {
            return DatabaseBackupService::filePath($filename);
        } catch (RuntimeException $e) {
            abort(404, $e->getMessage());
        }
    }
}
