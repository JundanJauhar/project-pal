<?php

namespace App\Http\Controllers\UMS;

use App\Http\Controllers\Controller;
use App\Models\Division;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DivisionManagementController extends Controller
{
    public function index()
    {
        $divisions = Division::withCount('roles')
            ->with('roles')
            ->orderBy('division_name')
            ->get();

        return view('ums.divisions.index', compact('divisions'));
    }

    /**
     * =============================
     * ADD DIVISION + MULTI ROLES
     * =============================
     */
    public function store(Request $request)
    {
        $request->validate([
            'division_name' => 'required|string|max:100',
            'description'   => 'nullable|string',
            'roles'         => 'nullable|array',
            'roles.*'       => 'nullable|string|max:100',
        ]);

        DB::transaction(function () use ($request) {

            // 1. Create Division
            $division = Division::create([
                'division_name' => $request->division_name,
                'description'   => $request->description,
            ]);

            // 2. Create Roles (with role_code)
            if ($request->filled('roles')) {
                foreach ($request->roles as $roleName) {
                    if (!$roleName) continue;

                    $roleCode = $this->generateRoleCode($roleName);

                    // Cegah duplikasi (sesuai unique index)
                    $exists = Role::where('division_id', $division->division_id)
                        ->where('role_code', $roleCode)
                        ->exists();

                    if ($exists) {
                        continue; // skip silently
                    }

                    Role::create([
                        'division_id' => $division->division_id,
                        'role_code'   => $roleCode,
                        'role_name'   => $roleName,
                        'description' => null,
                    ]);
                }
            }
        });

        return redirect()->back()->with('success', 'Division & Roles berhasil ditambahkan.');
    }

    /**
     * =============================
     * EDIT DIVISION (LOCK NAME & DESC)
     * =============================
     */
    public function update(Request $request, $id)
    {
        // Sesuai arahan: edit division hanya untuk kelola role
        return redirect()->back()->with('success', 'Division info tidak diubah. Silakan kelola role.');
    }

    /**
     * =============================
     * DELETE DIVISION (SAFE)
     * =============================
     */
    public function destroy($id)
    {
        $division = Division::findOrFail($id);

        if ($division->roles()->count() > 0) {
            return redirect()->back()
                ->with('error', 'Division tidak dapat dihapus karena masih memiliki roles.');
        }

        $division->delete();

        return redirect()->back()->with('success', 'Division berhasil dihapus.');
    }

    /**
     * =============================
     * ADD ROLE TO EXISTING DIVISION
     * =============================
     */
    public function addRole(Request $request, $divisionId)
    {
        $request->validate([
            'role_name' => 'required|string|max:100',
        ]);

        $roleCode = $this->generateRoleCode($request->role_name);

        // Cegah duplikasi sesuai UNIQUE(division_id, role_code)
        $exists = Role::where('division_id', $divisionId)
            ->where('role_code', $roleCode)
            ->exists();

        if ($exists) {
            return redirect()->back()
                ->with('error', 'Role dengan nama tersebut sudah ada di division ini.');
        }

        Role::create([
            'division_id' => $divisionId,
            'role_code'   => $roleCode,
            'role_name'   => $request->role_name,
            'description' => null,
        ]);

        return redirect()->back()->with('success', 'Role berhasil ditambahkan.');
    }

    /**
     * =============================
     * DELETE ROLE (SAFE)
     * =============================
     */
    public function deleteRole($roleId)
    {
        $role = Role::findOrFail($roleId);

        DB::transaction(function () use ($roleId, $role) {
            // Hapus relasi pivot dulu (role_user)
            DB::table('role_user')->where('role_id', $roleId)->delete();

            // Hapus role
            $role->delete();
        });

        return redirect()->back()->with('success', 'Role berhasil dihapus.');
    }

    /**
     * =============================
     * ROLE CODE GENERATOR
     * =============================
     */
    private function generateRoleCode(string $name): string
    {
        return strtolower(
            preg_replace('/[^a-z0-9]+/i', '_', trim($name))
        );
    }
}
