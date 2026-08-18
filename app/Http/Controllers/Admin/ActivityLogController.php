<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Exports\ActivityLogExport;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $query = ActivityLog::query()->with('user');

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('url', 'like', "%{$search}%")
                  ->orWhere('method', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($q2) use ($search) {
                      $q2->where('name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('export') && $request->export === 'excel') {
            $logs = (clone $query)->latest()->get();

            return Excel::download(new ActivityLogExport($logs), 'log-aktivitas-' . now()->format('Y-m-d-H-i-s') . '.xlsx');
        }

        $logs = $query->latest()->paginate(20)->withQueryString();
        $users = User::orderBy('name')->get();

        return view('admin.activity-log.index', compact('logs', 'users'));
    }

    public function show(User $user)
    {
        $logs = ActivityLog::where('user_id', $user->id)
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.activity-log.show', compact('user', 'logs'));
    }
}
