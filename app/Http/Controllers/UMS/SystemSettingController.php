<?php

namespace App\Http\Controllers\UMS;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Helpers\ActivityLogger;

class SystemSettingController extends Controller
{
    public function index()
    {
        $settings = DB::table('settings')
            ->pluck('value', 'key')
            ->toArray();

        return view('ums.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        foreach ($request->except('_token') as $key => $value) {
            DB::table('settings')->updateOrInsert(
                ['key' => $key],
                ['value' => $value, 'updated_at' => now()]
            );
        }

        ActivityLogger::log(
            module: 'System Settings',
            action: 'update_settings',
            details: [
                'updated_keys' => array_keys($request->except('_token'))
            ]
        );

        return back()->with('success', 'System settings berhasil diperbarui.');
    }
}
