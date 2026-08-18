<?php

namespace App\Traits;

use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

trait LogsActivity
{
    protected function logActivity(string $description, ?Request $request = null, ?array $properties = []): void
    {
        $request = $request ?? request();

        ActivityLog::create([
            'user_id' => Auth::id(),
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'description' => $description,
            'properties' => $properties,
            'created_at' => now(),
        ]);
    }
}
