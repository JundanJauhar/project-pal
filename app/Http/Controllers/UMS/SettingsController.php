<?php

namespace App\Http\Controllers\UMS;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\UMS\Setting;
use App\Helpers\AuditLogger;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = Setting::orderBy('key', 'asc')->get();
        return view('ums.settings.index', compact('settings'));
    }

    public function create()
    {
        return view('ums.settings.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'key'         => 'required|string|max:120|unique:settings,key',
            'value'       => 'nullable|string|max:255',
            'description' => 'nullable|string|max:255',
        ]);

        $setting = Setting::create($validated);

        // 🔥 AUDIT: CREATE SETTING
        AuditLogger::log(
            action: 'create_setting',
            table: 'settings',
            targetId: $setting->id,
            details: [
                'created_by' => Auth::id(),
                'data'       => $setting->toArray(),
            ]
        );

        return redirect()->route('ums.settings.index')
            ->with('success', 'Setting berhasil dibuat!');
    }

    public function edit($id)
    {
        $setting = Setting::findOrFail($id);
        return view('ums.settings.edit', compact('setting'));
    }

    public function update(Request $request, $id)
    {
        $setting = Setting::findOrFail($id);

        $validated = $request->validate([
            'value'       => 'nullable|string|max:255',
            'description' => 'nullable|string|max:255',
        ]);

        // Simpan nilai sebelum update
        $before = $setting->toArray();

        // Update setting
        $setting->update($validated);

        // 🔥 AUDIT: UPDATE SETTING
        AuditLogger::log(
            action: 'update_setting',
            table: 'settings',
            targetId: $setting->id,
            details: [
                'updated_by' => Auth::id(),
                'before'     => $before,
                'after'      => $setting->toArray(),
            ]
        );

        return redirect()->route('ums.settings.index')
            ->with('success', 'Setting berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $setting = Setting::findOrFail($id);

        // 🔥 AUDIT: DELETE SETTING
        AuditLogger::log(
            action: 'delete_setting',
            table: 'settings',
            targetId: $setting->id,
            details: [
                'deleted_by' => Auth::id(),
                'data'       => $setting->toArray(),
            ]
        );

        $setting->delete();

        return redirect()->route('ums.settings.index')
            ->with('success', 'Setting berhasil dihapus!');
    }
}
