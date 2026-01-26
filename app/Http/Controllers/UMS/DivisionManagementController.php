<?php

namespace App\Http\Controllers\UMS;

use App\Http\Controllers\Controller;
use App\Models\Division;
use Illuminate\Http\Request;

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

    public function store(Request $request)
    {
        $request->validate([
            'division_name' => 'required|string|max:100',
            'description'   => 'nullable|string',
        ]);

        Division::create($request->only(['division_name', 'description']));

        return redirect()->back()->with('success', 'Division berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $division = Division::findOrFail($id);

        $request->validate([
            'division_name' => 'required|string|max:100',
            'description'   => 'nullable|string',
        ]);

        $division->update($request->only(['division_name', 'description']));

        return redirect()->back()->with('success', 'Division berhasil diperbarui.');
    }

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
}
