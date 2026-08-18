<?php

namespace App\Http\Middleware;

use App\Models\ActivityLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class LogActivity
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (Auth::check() && !$this->shouldSkip($request)) {
            ActivityLog::create([
                'user_id' => Auth::id(),
                'method' => $request->method(),
                'url' => $request->fullUrl(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'description' => $this->getDescription($request),
                'properties' => $this->getProperties($request),
                'created_at' => now(),
            ]);
        }

        return $response;
    }

    private function shouldSkip(Request $request): bool
    {
        // $request->path() tidak diawali slash, jadi normalkan dulu agar cocok dengan daftar skip
        $path = '/' . ltrim($request->path(), '/');

        $skipPaths = [
            '/admin/activity-log',
            '/admin/user/create',
            '/admin/user',
        ];

        foreach ($skipPaths as $skipPath) {
            if (str_starts_with($path, $skipPath)) {
                return true;
            }
        }

        return false;
    }

    private function getDescription(Request $request): string
    {
        $method = $request->method();
        $path = $request->path();

        return match (true) {
            $method === 'GET' && str_contains($path, 'export') => 'Mengunduh export: ' . basename($path),
            $method === 'GET' && str_contains($path, 'print') => 'Membuka print: ' . basename($path),
            $method === 'POST' => 'Mengirim data ke: ' . basename($path),
            $method === 'PUT' || $method === 'PATCH' => 'Memperbarui data: ' . basename($path),
            $method === 'DELETE' => 'Menghapus data: ' . basename($path),
            default => 'Mengakses: ' . $path,
        };
    }

    private function getProperties(Request $request): array
    {
        $properties = [];

        if ($request->has('search')) {
            $properties['search'] = $request->input('search');
        }

        if ($request->has('tahun_ajaran_id')) {
            $properties['tahun_ajaran_id'] = $request->input('tahun_ajaran_id');
        }

        if ($request->has('laboratorium_id')) {
            $properties['laboratorium_id'] = $request->input('laboratorium_id');
        }

        return $properties;
    }
}
