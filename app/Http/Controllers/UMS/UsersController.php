<?php

namespace App\Http\Controllers\UMS;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Division;
use App\Helpers\AuditLogger;

class UsersController extends Controller
{
    /**
     * LIST USER
     */
    public function index()
    {
        $users = User::orderBy('name', 'asc')->get();

        return view('ums.users.index', compact('users'));
    }

    /**
     * FORM CREATE USER
     */
    public function create()
    {
        $divisions = Division::all();

        $roles = [
            'superadmin',
            'admin',
            'sekretaris',
            'sekretaris_direksi',
            'desain',
            'supply_chain',
            'treasury',
            'accounting',
            'qa',
            'user'
        ];

        return view('ums.users.create', compact('divisions', 'roles'));
    }

    /**
     * SIMPAN USER BARU
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'email'       => 'required|email|unique:users,email',
            'password'    => 'required|min:6',
            'roles'       => 'required|string',
            'division_id' => 'nullable|integer',
        ]);

        $user = User::create([
            'name'        => $validated['name'],
            'email'       => $validated['email'],
            'password'    => Hash::make($validated['password']),
            'roles'       => $validated['roles'],
            'division_id' => $validated['division_id'],
            'status'      => 'active',
        ]);

        AuditLogger::log(
            action: 'create_user',
            table: 'users',
            targetId: $user->user_id,
            details: [
                'created_by' => Auth::id(),
                'data'       => $user->toArray(),
            ]
        );

        return redirect()->route('ums.users.index')
            ->with('success', 'User berhasil dibuat.');
    }

    /**
     * FORM EDIT USER
     */
    public function edit($user_id)
    {
        $user = User::findOrFail($user_id);
        $divisions = Division::all();

        $roles = [
            'superadmin',
            'admin',
            'sekretaris',
            'sekretaris_direksi',
            'desain',
            'supply_chain',
            'treasury',
            'accounting',
            'qa',
            'user'
        ];

        return view('ums.users.edit', compact('user', 'divisions', 'roles'));
    }

    /**
     * UPDATE USER
     */
    public function update(Request $request, $user_id)
    {
        $user = User::findOrFail($user_id);

        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'email'       => "required|email|unique:users,email,{$user_id},user_id",
            'roles'       => 'required|string',
            'division_id' => 'nullable|integer',
        ]);

        $before = $user->toArray();

        $user->update([
            'name'        => $validated['name'],
            'email'       => $validated['email'],
            'roles'       => $validated['roles'],
            'division_id' => $validated['division_id'],
        ]);

        AuditLogger::log(
            action: 'update_user',
            table: 'users',
            targetId: $user->user_id,
            details: [
                'updated_by' => Auth::id(),
                'before'     => $before,
                'after'      => $user->toArray(),
            ]
        );

        return redirect()->route('ums.users.index')
            ->with('success', 'User berhasil diperbarui.');
    }

    /**
     * RESET PASSWORD
     */
    public function resetPassword(Request $request, $user_id)
    {
        $user = User::findOrFail($user_id);

        $request->validate([
            'password' => 'required|min:6'
        ]);

        $before = $user->toArray();

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        AuditLogger::log(
            action: 'reset_password',
            table: 'users',
            targetId: $user->user_id,
            details: [
                'reset_by' => Auth::id(),
                'before'   => $before,
            ]
        );

        return redirect()->route('ums.users.index')
            ->with('success', 'Password berhasil direset.');
    }

    /**
     * ⭐ FORCE LOGOUT USER
     * Menghapus session user di tabel `sessions`.
     */
    public function forceLogout($user_id)
    {
        $user = User::findOrFail($user_id);

        // Menghapus session user → user otomatis logout
        DB::table('sessions')->where('user_id', $user_id)->delete();

        AuditLogger::log(
            action: 'force_logout',
            table: 'users',
            targetId: $user->user_id,
            details: [
                'force_logout_by' => Auth::id(),
                'user'            => $user->toArray(),
            ]
        );

        return back()->with('success', 'User berhasil di-force logout.');
    }

    /**
     * DELETE USER
     */
    public function destroy($user_id)
    {
        $user = User::findOrFail($user_id);

        AuditLogger::log(
            action: 'delete_user',
            table: 'users',
            targetId: $user->user_id,
            details: [
                'deleted_by' => Auth::id(),
                'data'       => $user->toArray(),
            ]
        );

        $user->delete();

        return redirect()->route('ums.users.index')
            ->with('success', 'User telah dihapus.');
    }
}
