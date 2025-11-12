<?php

/**
 * ProcurementController
 * 
 * Controller untuk mengelola pembuatan dan penyimpanan request procurement (pengadaan)
 * Mengimplementasikan User Centered Design dengan menyederhanakan form untuk user desain
 * 
 * @package App\Http\Controllers
 * @author PT PAL Indonesia
 */

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\RequestProcurement;
use App\Models\Item;
use App\Models\Division;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProcurementController extends Controller
{
    /**
     * Menampilkan form untuk membuat request procurement baru
     * 
     * Method ini menyiapkan data yang diperlukan untuk form:
     * - Daftar semua proyek
     * - Daftar semua divisi
     * - Default project dan division untuk user desain
     * 
     * User Centered Design: Form disederhanakan untuk user desain
     * dengan field project dan division yang diisi otomatis
     * 
     * @return \Illuminate\View\View View form create procurement
     */
    public function create()
    {
        // Mengambil semua proyek untuk dropdown (jika diperlukan)
        $projects = Project::all();
        
        // Mengambil semua divisi untuk dropdown (jika diperlukan)
        $divisions = Division::all();
        
        // User Centered Design: Set default project dan division untuk user desain
        // Ini menyederhanakan proses input karena user tidak perlu memilih manual
        $defaultProject = $projects->first(); // Ambil proyek pertama sebagai default
        $defaultDivision = Auth::user()->division_id 
            ? Division::find(Auth::user()->division_id) // Gunakan divisi user jika ada
            : $divisions->first(); // Atau ambil divisi pertama sebagai fallback
        
        // Kirim data ke view
        return view('procurements.create', compact('projects', 'divisions', 'defaultProject', 'defaultDivision'));
    }

    /**
     * Menyimpan request procurement baru ke database
     * 
     * Method ini melakukan:
     * 1. Validasi input dari form
     * 2. Set default values untuk field yang tidak diisi (User Centered Design)
     * 3. Menyimpan procurement dan items dalam transaction
     * 4. Redirect dengan pesan sukses atau error
     * 
     * @param \Illuminate\Http\Request $request Request dari form
     * @return \Illuminate\Http\RedirectResponse Redirect dengan pesan sukses atau error
     */
    public function store(Request $request)
    {
        // User Centered Design: Siapkan default values jika tidak diisi
        // Ini memudahkan user karena tidak perlu mengisi semua field
        $defaultProject = Project::first(); // Proyek pertama sebagai default
        $defaultDivision = Auth::user()->division_id 
            ? Division::find(Auth::user()->division_id) // Divisi user sebagai default
            : Division::first(); // Atau divisi pertama sebagai fallback
        
        // Validasi input dari form
        // Field project_id dan applicant_department dibuat nullable karena diisi otomatis
        $validated = $request->validate([
            'project_id' => 'nullable|exists:projects,project_id', // Project ID opsional, harus valid jika diisi
            'request_name' => 'required|string|max:255', // Nama request wajib diisi
            'priority' => 'required|in:rendah,sedang,tinggi', // Prioritas wajib, harus salah satu nilai yang diizinkan
            'description' => 'nullable|string', // Deskripsi opsional
            'applicant_department' => 'nullable|exists:divisions,divisi_id', // Department opsional, harus valid jika diisi
            'created_date' => 'nullable|date', // Tanggal dibuat opsional
            'deadline_date' => 'nullable|date', // Tanggal deadline opsional
            'items' => 'required|array|min:1', // Items wajib, minimal 1 item
            'items.*.item_name' => 'required|string|max:255', // Nama item wajib untuk setiap item
            'items.*.specification' => 'required|string', // Spesifikasi wajib untuk setiap item
            'items.*.unit' => 'nullable|string', // Satuan opsional
            'items.*.unit_price' => 'nullable|numeric|min:0', // Harga satuan opsional, harus angka >= 0
            'items.*.estimated_price' => 'nullable|numeric|min:0', // Harga estimasi opsional, harus angka >= 0
            'items.*.amount' => 'nullable|integer|min:1', // Jumlah opsional, harus integer >= 1
        ]);

        // User Centered Design: Set default values jika tidak diisi
        // Ini memastikan data selalu lengkap meskipun user tidak mengisi semua field
        $validated['project_id'] = $validated['project_id'] ?? ($defaultProject ? $defaultProject->project_id : null);
        $validated['applicant_department'] = $validated['applicant_department'] ?? ($defaultDivision ? $defaultDivision->divisi_id : null);
        $validated['created_date'] = $validated['created_date'] ?? date('Y-m-d'); // Default: hari ini
        $validated['deadline_date'] = $validated['deadline_date'] ?? date('Y-m-d', strtotime('+30 days')); // Default: 30 hari dari sekarang

        // Validasi bahwa project dan department sudah terisi
        // Jika masih null, berarti tidak ada data default yang tersedia
        if (!$validated['project_id']) {
            return back()->withErrors(['error' => 'Project tidak ditemukan. Silakan hubungi administrator.'])->withInput();
        }
        if (!$validated['applicant_department']) {
            return back()->withErrors(['error' => 'Department tidak ditemukan. Silakan hubungi administrator.'])->withInput();
        }

        // Gunakan database transaction untuk memastikan data konsisten
        // Jika ada error saat menyimpan items, semua perubahan akan di-rollback
        DB::beginTransaction();
        try {
            // Membuat request procurement baru
            $procurement = RequestProcurement::create([
                'project_id' => $validated['project_id'], // ID proyek
                'request_name' => $validated['request_name'], // Nama request
                'applicant_department' => $validated['applicant_department'], // Department pemohon
                'created_date' => $validated['created_date'], // Tanggal dibuat
                'deadline_date' => $validated['deadline_date'], // Tanggal deadline
                'request_status' => 'submitted', // Status default: submitted
            ]);

            // Membuat items untuk procurement ini
            // Loop melalui setiap item yang diinput user
            foreach ($validated['items'] as $itemData) {
                // Hitung harga total dari harga estimasi dan jumlah
                $estimatedPrice = $itemData['estimated_price'] ?? 0; // Default: 0 jika tidak diisi
                $amount = $itemData['amount'] ?? 1; // Default: 1 jika tidak diisi
                $totalPrice = $estimatedPrice * $amount; // Total = harga estimasi × jumlah

                // Simpan item ke database
                Item::create([
                    'request_procurement_id' => $procurement->request_id, // ID procurement
                    'item_name' => $itemData['item_name'], // Nama item
                    'specification' => $itemData['specification'], // Spesifikasi item
                    'unit' => $itemData['unit'] ?? null, // Satuan (opsional)
                    'unit_price' => $itemData['unit_price'] ?? null, // Harga satuan (opsional)
                    'estimated_price' => $estimatedPrice, // Harga estimasi
                    'amount' => $amount, // Jumlah
                    'total_price' => $totalPrice, // Total harga
                ]);
            }

            // Commit transaction jika semua berhasil
            // Semua perubahan akan disimpan ke database
            DB::commit();

            // Redirect ke form dengan pesan sukses
            // User Centered Design: Memberikan feedback yang jelas bahwa data berhasil disimpan
            return redirect()->route('procurements.create')
                ->with('success', 'Pengadaan berhasil dikirim');
        } catch (\Exception $e) {
            // Rollback transaction jika ada error
            // Semua perubahan akan dibatalkan untuk menjaga konsistensi data
            DB::rollBack();
            
            // Redirect kembali dengan pesan error
            // User Centered Design: Memberikan pesan error yang jelas agar user tahu apa yang salah
            return back()->withErrors(['error' => 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage()])
                ->withInput(); // Kembalikan input user agar tidak perlu mengisi ulang
        }
    }
}
