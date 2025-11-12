<?php

/**
 * ProjectController
 * 
 * Controller untuk mengelola CRUD operasi pada Project
 * Mengimplementasikan User Centered Design dengan menampilkan data berbeda untuk role berbeda
 * 
 * @package App\Http\Controllers
 * @author PT PAL Indonesia
 */

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Division;
use App\Models\ProcurementProgress;
use App\Models\Notification;
use App\Models\RequestProcurement;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProjectController extends Controller
{
    /**
     * Menampilkan daftar semua proyek
     * 
     * Method ini mengimplementasikan User Centered Design:
     * - User dengan role 'desain' melihat daftar request procurement (pengadaan)
     * - User dengan role lain melihat daftar proyek standar
     * 
     * @return \Illuminate\View\View View daftar proyek atau pengadaan sesuai role
     */
    public function index()
    {
        // User Centered Design: Role 'desain' melihat tampilan yang berbeda
        // Mereka lebih fokus pada request procurement daripada proyek secara langsung
        if (Auth::user()->roles === 'desain') {
            // Mengambil request procurement dengan relasi project dan division
            // Eager loading untuk menghindari N+1 query problem
            $procurements = RequestProcurement::with(['project.ownerDivision', 'applicantDivision'])
                ->orderBy('created_date', 'desc') // Urutkan dari yang terbaru
                ->paginate(20); // Pagination untuk performa dan UX yang lebih baik

            return view('projects.index', compact('procurements'));
        }

        // Untuk role lain, tampilkan daftar proyek standar
        // Eager load relasi untuk performa optimal
        $projects = Project::with(['ownerDivision', 'contracts'])
            ->orderBy('created_at', 'desc') // Urutkan dari yang terbaru
            ->paginate(20); // Pagination untuk performa

        return view('projects.index', compact('projects'));
    }

    /**
     * Menampilkan form untuk membuat proyek baru
     * 
     * @return \Illuminate\View\View View form create project
     */
    public function create()
    {
        // Mengambil semua divisi untuk dropdown di form
        $divisions = Division::all();
        return view('projects.create', compact('divisions'));
    }

    /**
     * Menyimpan proyek baru ke database
     * 
     * Method ini melakukan:
     * 1. Validasi input dari user
     * 2. Membuat proyek baru dengan status 'draft'
     * 3. Mengirim notifikasi ke Supply Chain team
     * 4. Redirect ke halaman detail proyek
     * 
     * @param \Illuminate\Http\Request $request Request dari form
     * @return \Illuminate\Http\RedirectResponse Redirect ke halaman detail proyek
     */
    public function store(Request $request)
    {
        // Validasi input dari user
        // Memastikan data yang masuk sesuai dengan aturan bisnis
        $validated = $request->validate([
            'code_project' => 'required|string|unique:projects,code_project', // Kode proyek harus unik
            'name_project' => 'required|string|max:255', // Nama proyek wajib diisi
            'description' => 'nullable|string', // Deskripsi opsional
            'owner_division_id' => 'required|exists:divisions,divisi_id', // Divisi harus valid
            'priority' => 'required|in:rendah,sedang,tinggi', // Prioritas harus salah satu dari nilai yang diizinkan
            'start_date' => 'required|date', // Tanggal mulai wajib
            'end_date' => 'required|date|after:start_date', // Tanggal selesai harus setelah tanggal mulai
        ]);

        // Set status default untuk proyek baru
        // Semua proyek baru dimulai dengan status 'draft'
        $validated['status_project'] = 'draft';

        // Membuat proyek baru di database
        $project = Project::create($validated);

        // User Centered Design: Notifikasi otomatis ke Supply Chain
        // Mereka perlu tahu ada proyek baru yang perlu direview
        $this->notifySupplyChain($project, 'Proyek baru telah dibuat dan menunggu review');

        // Redirect ke halaman detail proyek dengan pesan sukses
        // Memberikan feedback yang jelas kepada user bahwa proyek berhasil dibuat
        return redirect()->route('projects.show', $project->project_id)
            ->with('success', 'Proyek berhasil dibuat');
    }

    /**
     * Menampilkan detail proyek tertentu
     * 
     * Method ini menampilkan:
     * - Informasi lengkap proyek
     * - Timeline progress pengadaan
     * - Daftar request procurement dan items
     * 
     * @param int $id ID proyek yang akan ditampilkan
     * @return \Illuminate\View\View View detail proyek
     */
    public function show($id)
    {
        // Mengambil proyek dengan semua relasi yang diperlukan
        // Eager loading untuk menghindari N+1 query problem
        $project = Project::with([
            'ownerDivision', // Divisi pemilik proyek
            'contracts', // Kontrak yang terkait
            'hps', // HPS (Harga Perkiraan Sendiri)
            'evaluations', // Evaluasi proyek
            'requestProcurements.items' // Request procurement beserta items-nya
        ])->findOrFail($id); // Jika tidak ditemukan, throw 404

        // Daftar stage untuk timeline tampilan di Blade
        // Urutan ini menentukan posisi di timeline visual
        $stages = [
            'draft', // Tahap draft
            'review_sc', // Review Supply Chain
            'persetujuan_sekretaris', // Persetujuan Sekretaris Direksi
            'pemilihan_vendor', // Pemilihan vendor
            'pengecekan_legalitas', // Pengecekan legalitas
            'pemesanan', // Pemesanan
            'pembayaran', // Pembayaran
            'selesai' // Selesai
        ];

        // Mencari posisi stage saat ini berdasarkan status proyek
        // Digunakan untuk menampilkan progress di timeline
        $currentStageIndex = array_search($project->status_project, $stages);

        // Jika status tidak ditemukan di array stages, set ke 0 (draft)
        // Fallback untuk menghindari error jika ada status yang tidak terdaftar
        if ($currentStageIndex === false) {
            $currentStageIndex = 0;
        }

        // Mengambil progress pengadaan untuk ditampilkan di tabel
        $progress = ProcurementProgress::where('permintaan_pengadaan_id', $id)
            ->with('checkpoint') // Eager load checkpoint
            ->orderBy('titik_id') // Urutkan berdasarkan urutan checkpoint
            ->get();

        return view('projects.show', compact('project', 'progress', 'stages', 'currentStageIndex'));
    }

    /**
     * Menampilkan form untuk mengedit proyek
     * 
     * @param int $id ID proyek yang akan diedit
     * @return \Illuminate\View\View View form edit project
     * @throws \Illuminate\Auth\Access\AuthorizationException Jika user tidak memiliki izin
     */
    public function edit($id)
    {
        // Mencari proyek yang akan diedit
        $project = Project::findOrFail($id);
        
        // Mengambil semua divisi untuk dropdown
        $divisions = Division::all();

        // Validasi authorization: cek apakah user memiliki izin untuk edit
        // Keamanan: mencegah user mengedit proyek yang bukan haknya
        $this->authorize('update', $project);

        return view('projects.edit', compact('project', 'divisions'));
    }

    /**
     * Mengupdate proyek yang sudah ada
     * 
     * @param \Illuminate\Http\Request $request Request dari form
     * @param int $id ID proyek yang akan diupdate
     * @return \Illuminate\Http\RedirectResponse Redirect ke halaman detail proyek
     * @throws \Illuminate\Auth\Access\AuthorizationException Jika user tidak memiliki izin
     */
    public function update(Request $request, $id)
    {
        // Mencari proyek yang akan diupdate
        $project = Project::findOrFail($id);

        // Validasi authorization: cek apakah user memiliki izin untuk update
        $this->authorize('update', $project);

        // Validasi input dari user
        $validated = $request->validate([
            'name_project' => 'required|string|max:255', // Nama proyek wajib
            'description' => 'nullable|string', // Deskripsi opsional
            'priority' => 'required|in:rendah,sedang,tinggi', // Prioritas harus valid
            'start_date' => 'required|date', // Tanggal mulai wajib
            'end_date' => 'required|date|after:start_date', // Tanggal selesai harus setelah mulai
            'status_project' => 'nullable|in:draft,review_sc,persetujuan_sekretaris,pemilihan_vendor,pengecekan_legalitas,pemesanan,pembayaran,selesai,rejected', // Status harus valid
        ]);

        // Update proyek dengan data yang sudah divalidasi
        $project->update($validated);

        // Redirect ke halaman detail dengan pesan sukses
        return redirect()->route('projects.show', $project->project_id)
            ->with('success', 'Proyek berhasil diupdate');
    }

    /**
     * Mengupdate status proyek
     * 
     * Method ini digunakan untuk mengubah status proyek dan mengirim notifikasi
     * ke user yang relevan berdasarkan perubahan status
     * 
     * @param \Illuminate\Http\Request $request Request dengan status baru
     * @param int $id ID proyek yang akan diupdate statusnya
     * @return \Illuminate\Http\JsonResponse JSON response dengan hasil update
     */
    public function updateStatus(Request $request, $id)
    {
        // Mencari proyek yang akan diupdate statusnya
        $project = Project::findOrFail($id);

        // Validasi input
        $validated = $request->validate([
            'status_project' => 'required|in:draft,review_sc,persetujuan_sekretaris,pemilihan_vendor,pengecekan_legalitas,pemesanan,pembayaran,selesai,rejected', // Status harus valid
            'notes' => 'nullable|string', // Catatan opsional
        ]);

        // Simpan status lama untuk notifikasi
        $oldStatus = $project->status_project;
        
        // Update status proyek
        $project->update(['status_project' => $validated['status_project']]);

        // User Centered Design: Kirim notifikasi ke user yang relevan
        // Setiap perubahan status memicu notifikasi ke role yang terkait
        $this->handleStatusChangeNotification($project, $oldStatus, $validated['status_project']);

        // Return JSON response untuk AJAX request
        return response()->json([
            'success' => true,
            'message' => 'Status proyek berhasil diupdate',
            'project' => $project
        ]);
    }

    /**
     * Menghapus proyek
     * 
     * @param int $id ID proyek yang akan dihapus
     * @return \Illuminate\Http\RedirectResponse Redirect ke halaman daftar proyek
     * @throws \Illuminate\Auth\Access\AuthorizationException Jika user tidak memiliki izin
     */
    public function destroy($id)
    {
        // Mencari proyek yang akan dihapus
        $project = Project::findOrFail($id);

        // Validasi authorization: cek apakah user memiliki izin untuk delete
        $this->authorize('delete', $project);

        // Hapus proyek dari database
        $project->delete();

        // Redirect ke halaman daftar proyek dengan pesan sukses
        return redirect()->route('projects.index')
            ->with('success', 'Proyek berhasil dihapus');
    }

    /**
     * Mencari proyek berdasarkan keyword, status, dan prioritas
     * 
     * Method ini digunakan untuk AJAX search di halaman daftar proyek
     * Mengembalikan data dalam format JSON untuk ditampilkan secara dinamis
     * 
     * @param \Illuminate\Http\Request $request Request dengan parameter search
     * @return \Illuminate\Http\JsonResponse JSON response dengan hasil pencarian
     */
    public function search(Request $request)
    {
        // Mengambil parameter pencarian dari request
        $q = $request->get('q'); // Keyword pencarian
        $status = $request->get('status'); // Filter status
        $priority = $request->get('priority'); // Filter prioritas
        $page = $request->get('page', 1); // Halaman untuk pagination

        // Query dasar dengan relasi yang diperlukan
        $projectsQuery = Project::with(['ownerDivision', 'contracts.vendor']);

        // Filter berdasarkan keyword pencarian
        // Mencari di nama proyek atau kode proyek
        if ($q) {
            $projectsQuery->where(function ($sub) use ($q) {
                $sub->where('name_project', 'LIKE', "%{$q}%") // Cari di nama proyek
                    ->orWhere('code_project', 'LIKE', "%{$q}%"); // Atau di kode proyek
            });
        }

        // Filter berdasarkan status jika ada
        if ($status) {
            $projectsQuery->where('status_project', $status);
        }

        // Filter berdasarkan prioritas jika ada
        if ($priority) {
            $projectsQuery->where('priority', $priority);
        }

        // Eksekusi query dengan pagination
        $projects = $projectsQuery->orderBy('created_at', 'desc')->paginate(10, ['*'], 'page', $page);

        // Transform data untuk frontend
        // Memformat data agar mudah digunakan di JavaScript
        $items = $projects->map(function ($p) {
            // Ambil vendor dari kontrak pertama jika ada
            $vendor = $p->contracts->first()->vendor->name_vendor ?? null;
            
            return [
                'project_id' => $p->project_id,
                'code_project' => $p->code_project,
                'name_project' => $p->name_project,
                'owner_division' => $p->ownerDivision->nama_divisi ?? '-', // Nama divisi atau '-'
                'start_date' => optional($p->start_date)->format('d/m/Y'), // Format tanggal Indonesia
                'end_date' => optional($p->end_date)->format('d/m/Y'),
                'vendor' => $vendor ?? '-', // Nama vendor atau '-'
                'priority' => $p->priority,
                'status_project' => $p->status_project,
            ];
        });

        // Return JSON response dengan data dan informasi pagination
        return response()->json([
            'data' => $items, // Data proyek yang sudah diformat
            'pagination' => [
                'current_page' => $projects->currentPage(), // Halaman saat ini
                'per_page' => $projects->perPage(), // Item per halaman
                'total' => $projects->total(), // Total item
                'last_page' => $projects->lastPage(), // Halaman terakhir
                'has_more' => $projects->hasMorePages(), // Apakah ada halaman selanjutnya
            ]
        ]);
    }

    /**
     * Mengirim notifikasi ke tim Supply Chain ketika proyek baru dibuat
     * 
     * Method private ini digunakan untuk mengirim notifikasi otomatis
     * ke semua user dengan role 'supply_chain' ketika ada proyek baru
     * 
     * @param \App\Models\Project $project Proyek yang baru dibuat
     * @param string $message Pesan notifikasi
     * @return void
     */
    private function notifySupplyChain($project, $message)
    {
        // Mencari semua user dengan role supply_chain
        $scUsers = User::where('roles', 'supply_chain')->get();

        // Kirim notifikasi ke setiap user supply chain
        foreach ($scUsers as $user) {
            Notification::create([
                'user_id' => $user->id, // User yang akan menerima notifikasi
                'sender_id' => Auth::id(), // User yang mengirim (user yang membuat proyek)
                'type' => 'project_created', // Tipe notifikasi
                'title' => 'Proyek Baru', // Judul notifikasi
                'message' => $message . ': ' . $project->name_project, // Pesan notifikasi
                'reference_type' => 'App\Models\Project', // Tipe model yang direferensikan
                'reference_id' => $project->project_id, // ID model yang direferensikan
            ]);
        }
    }

    /**
     * Menangani notifikasi ketika status proyek berubah
     * 
     * Method ini mengirim notifikasi ke user yang relevan berdasarkan
     * perubahan status proyek. Setiap status memiliki role yang berbeda
     * yang perlu diberitahu.
     * 
     * @param \App\Models\Project $project Proyek yang statusnya berubah
     * @param string $oldStatus Status lama proyek
     * @param string $newStatus Status baru proyek
     * @return void
     */
    private function handleStatusChangeNotification($project, $oldStatus, $newStatus)
    {
        // Mapping status ke role yang perlu diberitahu
        // User Centered Design: Setiap role hanya menerima notifikasi yang relevan
        $notifications = [
            'review_sc' => [
                'roles' => ['supply_chain'], // Supply Chain perlu review
                'message' => 'Proyek menunggu review SC'
            ],
            'persetujuan_sekretaris' => [
                'roles' => ['sekretaris_direksi'], // Sekretaris Direksi perlu approve
                'message' => 'Proyek menunggu persetujuan Sekretaris Direksi'
            ],
            'pemilihan_vendor' => [
                'roles' => ['supply_chain'], // Supply Chain menangani pemilihan vendor
                'message' => 'Proyek dalam tahap pemilihan vendor'
            ],
            'pembayaran' => [
                'roles' => ['accounting', 'treasury'], // Accounting dan Treasury menangani pembayaran
                'message' => 'Proyek menunggu pembayaran'
            ],
        ];

        // Jika status baru ada di mapping, kirim notifikasi
        if (isset($notifications[$newStatus])) {
            $config = $notifications[$newStatus];
            
            // Mencari semua user dengan role yang relevan
            $users = User::whereIn('roles', $config['roles'])->get();

            // Kirim notifikasi ke setiap user yang relevan
            foreach ($users as $user) {
                Notification::create([
                    'user_id' => $user->id, // User yang akan menerima notifikasi
                    'sender_id' => Auth::id(), // User yang mengubah status
                    'type' => 'status_change', // Tipe notifikasi
                    'title' => 'Update Status Proyek', // Judul notifikasi
                    'message' => $config['message'] . ': ' . $project->name_project, // Pesan dengan nama proyek
                    'reference_type' => 'App\Models\Project', // Tipe model
                    'reference_id' => $project->project_id, // ID proyek
                ]);
            }
        }
    }
}
