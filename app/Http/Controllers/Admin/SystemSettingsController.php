<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\RegistrationSettings;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SystemSettingsController extends Controller
{
    public function edit(): View
    {
        $this->ensureSuperAdmin();

        return view('admin.settings', ['settings' => RegistrationSettings::current()]);
    }
    public function update(Request $request): RedirectResponse
    {
        $this->ensureSuperAdmin();

        $values = $request->validate([
            'group_limit' => ['required', 'integer', 'min:1', 'max:2147483647'],
            'letter_contact' => ['required', 'string', 'max:120'],
        ]);
        DB::transaction(function () use ($values) {
            RegistrationSettings::upsert([['id' => 1] + $values], ['id'], array_keys($values));
            AuditLog::record('SYSTEM_SETTINGS_UPDATED', 'RegistrationSettings', 1, $values);
        });
        return back()->with('success', 'Pengaturan sistem berhasil disimpan.');
    }
}
