<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AuditLogController extends Controller
{
    /**
     * Tampilan Riwayat Log Audit & Aktivitas Sistem SIP-MAGANG
     */
    public function index(Request $request)
    {
        // Log audit memuat aksi lintas dinas, IP, dan detail reset password/penyamaran
        $this->ensureSuperAdmin('Log audit sistem hanya dapat dibuka oleh Super Administrator.');

        $query = AuditLog::with('user')->latest();

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $like = DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';
            $query->where(function ($q) use ($search, $like) {
                $q->where('action', $like, "%{$search}%")
                    ->orWhere('user_name', $like, "%{$search}%")
                    ->orWhere('target_type', $like, "%{$search}%")
                    ->orWhere('details', $like, "%{$search}%")
                    ->orWhere('ip_address', $like, "%{$search}%");
            });
        }

        $logs = $query->paginate(20)->withQueryString();
        $actions = AuditLog::select('action')->distinct()->pluck('action');

        return view('admin.audit_logs.index', compact('logs', 'actions'));
    }
}
