<?php

namespace App\Http\Controllers\UMS;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\UMS\AdminScope;
use App\Helpers\AuditLogger;

class AdminScopeController extends Controller
{
    public function index()
    {
        $scopes = AdminScope::orderBy('scope_key', 'asc')->get();
        return view('ums.admin_scopes.index', compact('scopes'));
    }

    public function create()
    {
        return view('ums.admin_scopes.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'scope_key'   => 'required|string|max:120|unique:admin_scopes,scope_key',
            'scope_value' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:255',
        ]);

        $scope = AdminScope::create($validated);

        // 🔥 AUDIT LOG
        AuditLogger::log(
            action: 'create_admin_scope',
            table: 'admin_scopes',
            targetId: $scope->id,
            details: [
                'created_by' => Auth::id(),
                'data'       => $scope->toArray(),
            ]
        );

        return redirect()->route('ums.admin_scopes.index')
            ->with('success', 'Admin scope berhasil dibuat!');
    }

    public function edit($id)
    {
        $scope = AdminScope::findOrFail($id);
        return view('ums.admin_scopes.edit', compact('scope'));
    }

    public function update(Request $request, $id)
    {
        $scope = AdminScope::findOrFail($id);

        $validated = $request->validate([
            'scope_value' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:255',
        ]);

        // simpan kondisi sebelum update
        $before = $scope->toArray();

        // lakukan update
        $scope->update($validated);

        // 🔥 AUDIT LOG
        AuditLogger::log(
            action: 'update_admin_scope',
            table: 'admin_scopes',
            targetId: $scope->id,
            details: [
                'updated_by' => Auth::id(),
                'before'     => $before,
                'after'      => $scope->toArray(),
            ]
        );

        return redirect()->route('ums.admin_scopes.index')
            ->with('success', 'Admin scope berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $scope = AdminScope::findOrFail($id);

        // 🔥 AUDIT LOG sebelum data dihapus
        AuditLogger::log(
            action: 'delete_admin_scope',
            table: 'admin_scopes',
            targetId: $scope->id,
            details: [
                'deleted_by' => Auth::id(),
                'data'       => $scope->toArray(),
            ]
        );

        $scope->delete();

        return redirect()->route('ums.admin_scopes.index')
            ->with('success', 'Admin scope berhasil dihapus!');
    }
}
