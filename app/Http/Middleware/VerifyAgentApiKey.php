<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Autentikasi agent PC lab lewat header X-API-KEY dibandingkan dengan
 * config('services.monitoring.api_key'). API key kosong dianggap fitur
 * belum dikonfigurasi sehingga semua permintaan ditolak (bukan lolos).
 */
class VerifyAgentApiKey
{
    public function handle(Request $request, Closure $next): Response
    {
        $expected = (string) config('services.monitoring.api_key');

        if ($expected === '') {
            return response()->json([
                'message' => 'MONITORING_API_KEY belum dikonfigurasi di server.',
            ], 503);
        }

        $given = (string) $request->header('X-API-KEY');

        if ($given === '' || ! hash_equals($expected, $given)) {
            return response()->json([
                'message' => 'X-API-KEY tidak valid.',
            ], 401);
        }

        return $next($request);
    }
}
