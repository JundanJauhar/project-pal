<?php

namespace App\Http\Controllers\UMS;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Division;
use App\Helpers\AuditLogger;
use App\Helpers\ActivityLogger;

class UsersController extends Controller
{
    /**
     * ===============================
     * LIST USER
     * ===============================
     */
    public function index(Request $request)
    {
        $query = User::with(['division', 'roles'])->orderBy('name');

        // SEARCH
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(
                fn($q) =>
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
            );
        }

        // FILTER DIVISION
        if ($request->filled('division')) {
            $query->whereHas(
                'division',
                fn($q) =>
                $q->where('division_name', $request->division)
            );
        }

        // FILTER ROLE
        if ($request->filled('role')) {
            $query->whereHas(
                'roles',
                fn($q) =>
                $q->where('role_code', $request->role)
            );
        }

        // FILTER STATUS
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return view('ums.users.index', [
            'users'     => $query->get(),
            'divisions' => Division::orderBy('division_name')->get(),
            'roles'     => \App\Models\Role::select('role_code')->distinct()->pluck('role_code'),
        ]);
    }

    /**
     * ===============================
     * CREATE FORM
     * ===============================
     */
    public function create()
    {
        return view('ums.users.create', [
            'divisions' => Division::orderBy('division_name')->get(),
            'roles' => [
                'superadmin',
                'admin',
                'sekretaris',
                'desain',
                'supply_chain',
                'treasury',
                'accounting',
                'qa',
                'user',
            ],
        ]);
    }

    /**
     * ===============================
     * STORE USER
     * ===============================
     */
    public function store(Request $request)
    {
        // ❌ hanya superadmin atau admin boleh membuat user
        if (!Auth::user()->hasRole('superadmin') && !Auth::user()->hasRole('admin')) {
            abort(403, 'Tidak memiliki izin.');
        }

        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'email'       => 'required|email|unique:users,email',
            'password'    => 'required|min:6',
            'roles'       => 'required|string',
            'division_id' => 'nullable|exists:divisions,division_id',
        ]);

        // Remove roles from data as it's handled by relationship
        $roleCode = $data['roles'];
        unset($data['roles']);

        $user = User::create([
            ...$data,
            'password' => Hash::make($data['password']),
            'status'   => 'active',
        ]);

        // Assign role using relationship
        $role = \App\Models\Role::where('role_code', $roleCode)->first();
        if ($role) {
            $user->roles()->attach($role->role_id);
        }

        AuditLogger::log(
            action: 'create_user',
            table: 'users',
            targetId: $user->user_id,
            details: ['created_by' => Auth::id()]
        );

        ActivityLogger::log(
            module: 'User Management',
            action: 'create',
            targetId: $user->user_id
        );

        return redirect()
            ->route('ums.users.index')
            ->with('success', 'User berhasil dibuat.');
    }

    /**
     * ===============================
     * EDIT FORM
     * ===============================
     */
    public function edit($user_id)
    {
        return view('ums.users.edit', [
            'user'      => User::with('division')->findOrFail($user_id),
            'divisions' => Division::orderBy('division_name')->get(),
            'roles' => [
                'superadmin',
                'admin',
                'sekretaris',
                'desain',
                'supply_chain',
                'treasury',
                'accounting',
                'qa',
                'user',
            ],
        ]);
    }

    /**
     * ===============================
     * UPDATE USER
     * ===============================
     */
    public function update(Request $request, $user_id)
    {
        $user = User::findOrFail($user_id);

        // ❌ Proteksi superadmin
        if ($user->hasRole('superadmin') && !Auth::user()->hasRole('superadmin')) {
            abort(403, 'Tidak memiliki izin.');
        }

        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'email'       => "required|email|unique:users,email,{$user_id},user_id",
            'roles'       => 'required|string',
            'division_id' => 'nullable|exists:divisions,division_id',
        ]);

        // Remove roles from data as it's handled by relationship
        $roleCode = $data['roles'];
        unset($data['roles']);

        $before = $user->toArray();
        $user->update($data);

        // Update role using relationship
        $role = \App\Models\Role::where('role_code', $roleCode)->first();
        if ($role) {
            $user->roles()->sync([$role->role_id]);
        }

        AuditLogger::log(
            action: 'update_user',
            table: 'users',
            targetId: $user->user_id,
            details: ['before' => $before, 'after' => $user->toArray()]
        );

        ActivityLogger::log(
            module: 'User Management',
            action: 'update',
            targetId: $user->user_id
        );

        return redirect()
            ->route('ums.users.index')
            ->with('success', 'User berhasil diperbarui.');
    }

    /**
     * ===============================
     * TOGGLE STATUS
     * ===============================
     */
    public function toggleStatus($user_id)
    {
        $user = User::findOrFail($user_id);

        // ❌ superadmin tidak boleh dinonaktifkan
        if ($user->hasRole('superadmin')) {
            return back()->with('error', 'Super Admin tidak dapat dinonaktifkan.');
        }

        $before = $user->status;
        $user->update([
            'status' => $before === 'active' ? 'inactive' : 'active',
        ]);

        AuditLogger::log(
            action: 'toggle_user_status',
            table: 'users',
            targetId: $user->user_id,
            details: ['before' => $before, 'after' => $user->status]
        );

        ActivityLogger::log(
            module: 'User Management',
            action: 'toggle_status',
            targetId: $user->user_id
        );

        return back()->with('success', 'Status user berhasil diperbarui.');
    }

    /**
     * ===============================
     * DELETE USER
     * ===============================
     */
    public function destroy($user_id)
    {
        $user = User::findOrFail($user_id);

        // ❌ superadmin tidak boleh dihapus
        if ($user->hasRole('superadmin')) {
            return back()->with('error', 'Super Admin tidak dapat dihapus.');
        }

        AuditLogger::log(
            action: 'delete_user',
            table: 'users',
            targetId: $user->user_id,
            details: ['deleted_by' => Auth::id()]
        );

        ActivityLogger::log(
            module: 'User Management',
            action: 'delete',
            targetId: $user->user_id
        );

        $user->delete();

        return back()->with('success', 'User berhasil dihapus.');
    }
}
