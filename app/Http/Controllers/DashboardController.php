<?php

/**
 * DashboardController
 * 
 * Controller untuk mengelola halaman dashboard dan statistik proyek
 * Mengikuti prinsip User Centered Design dengan menampilkan data sesuai role user
 * 
 * @package App\Http\Controllers
 * @author PT PAL Indonesia
 */

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProcurementProgress;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Menampilkan halaman dashboard utama
     * 
     * Method ini menampilkan overview dashboard dengan:
     * - Statistik total pengadaan, proyek yang sedang proses, selesai, dan ditolak
     * - Daftar proyek terbaru berdasarkan role user (User Centered Design)
     * - Notifikasi yang belum dibaca untuk user yang sedang login
     * 
     * @return \Illuminate\View\View View dashboard dengan data statistik dan proyek
     */
    public function index()
    {
        // Mendapatkan user yang sedang login
        $user = Auth::user();

        // Mengambil statistik proyek untuk ditampilkan di dashboard
        // Statistik ini memberikan overview cepat kepada user tentang status proyek
        $stats = [
            'total_pengadaan' => Project::count(), // Total semua proyek pengadaan
            'sedang_proses' => Project::where('status_project', 'in_progress')->count(), // Proyek yang sedang berjalan
            'selesai' => Project::where('status_project', 'completed')->count(), // Proyek yang sudah selesai
            'ditolak' => Project::where('status_project', 'rejected')->count(), // Proyek yang ditolak
        ];

        // Mengambil daftar proyek berdasarkan role user
        // User Centered Design: setiap role melihat data yang relevan untuk mereka
        $projects = $this->getProjectsByRole($user);

        // Mengambil notifikasi yang belum dibaca untuk user
        // Membatasi 5 notifikasi terbaru untuk tidak membebani UI
        $notifications = Notification::where('user_id', $user->id)
            ->where('is_read', false) // Hanya notifikasi yang belum dibaca
            ->orderBy('created_at', 'desc') // Urutkan dari yang terbaru
            ->limit(5) // Batasi 5 notifikasi
            ->get();

        // Mengirim data ke view dashboard
        return view('dashboard.index', compact('stats', 'projects', 'notifications'));
    }

    /**
     * Mengambil daftar proyek berdasarkan role user
     * 
     * Method ini mengimplementasikan User Centered Design dengan:
     * - User biasa hanya melihat proyek dari divisinya sendiri
     * - Role tertentu (supply_chain, treasury, dll) melihat semua proyek
     * - Default behavior: user hanya melihat proyek divisinya
     * 
     * @param \App\Models\User $user User yang sedang login
     * @return \Illuminate\Database\Eloquent\Collection Koleksi proyek yang sesuai dengan role user
     */
    private function getProjectsByRole($user)
    {
        // Query dasar dengan relasi yang diperlukan untuk performa optimal
        $query = Project::with(['ownerDivision', 'contracts']);

        // Filter proyek berdasarkan role user (User Centered Design)
        switch ($user->roles) {
            case 'user':
                // User biasa hanya melihat proyek dari divisinya sendiri
                // Ini memberikan fokus pada pekerjaan mereka tanpa informasi yang tidak relevan
                $query->where('owner_division_id', $user->division_id);
                break;

            case 'supply_chain':
            case 'treasury':
            case 'accounting':
            case 'qa':
            case 'sekretaris_direksi':
                // Role-role ini memerlukan akses ke semua proyek untuk tugas koordinasi
                // Tidak perlu filter karena mereka butuh overview lengkap
                break;

            default:
                // Default behavior: user hanya melihat proyek divisinya
                // Keamanan: jika role tidak dikenali, batasi akses ke divisi sendiri
                $query->where('owner_division_id', $user->division_id);
        }

        // Mengembalikan 10 proyek terbaru untuk ditampilkan di dashboard
        // Membatasi jumlah untuk performa dan UX yang lebih baik
        return $query->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
    }

    /**
     * Menampilkan dashboard untuk divisi tertentu
     * 
     * Method ini memungkinkan user dengan role tertentu untuk melihat
     * dashboard khusus untuk divisi tertentu
     * 
     * @param int $divisionId ID divisi yang akan ditampilkan dashboardnya
     * @return \Illuminate\View\View View dashboard divisi dengan daftar proyek
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException Jika user tidak memiliki akses
     */
    public function divisionDashboard($divisionId)
    {
        $user = Auth::user();

        // Validasi akses: hanya supply_chain atau user dari divisi tersebut yang bisa akses
        // Keamanan: mencegah user melihat data divisi lain tanpa izin
        if ($user->roles !== 'supply_chain' && $user->division_id != $divisionId) {
            abort(403, 'Unauthorized access to division dashboard');
        }

        // Mengambil semua proyek dari divisi yang diminta
        $projects = Project::where('owner_division_id', $divisionId)
            ->with(['ownerDivision', 'contracts']) // Eager load relasi untuk performa
            ->orderBy('created_at', 'desc') // Urutkan dari yang terbaru
            ->get();

        return view('dashboard.division', compact('projects'));
    }

    /**
     * Mengambil statistik proyek berdasarkan status dan prioritas
     * 
     * Method ini digunakan untuk API endpoint yang menyediakan data statistik
     * dalam format JSON untuk digunakan di chart atau visualisasi lainnya
     * 
     * @return \Illuminate\Http\JsonResponse JSON response dengan statistik status dan prioritas
     */
    public function getStatistics()
    {
        // Mengambil jumlah proyek per status
        // Menggunakan selectRaw untuk performa query yang lebih baik
        $statusStats = Project::selectRaw('status_project, COUNT(*) as count')
            ->groupBy('status_project') // Group berdasarkan status
            ->get();

        // Mengambil jumlah proyek per prioritas
        $priorityStats = Project::selectRaw('priority, COUNT(*) as count')
            ->groupBy('priority') // Group berdasarkan prioritas
            ->get();

        // Mengembalikan data dalam format JSON untuk API
        return response()->json([
            'status' => $statusStats, // Statistik berdasarkan status
            'priority' => $priorityStats, // Statistik berdasarkan prioritas
        ]);
    }

    /**
     * Mengambil timeline progress pengadaan untuk proyek tertentu
     * 
     * Method ini digunakan untuk menampilkan progress pengadaan dalam bentuk timeline
     * Menunjukkan tahapan-tahapan yang sudah dilalui dalam proses pengadaan
     * 
     * @param int $projectId ID proyek yang akan diambil timeline-nya
     * @return \Illuminate\Http\JsonResponse JSON response dengan data progress timeline
     */
    public function getProcurementTimeline($projectId)
    {
        // Mengambil progress pengadaan dengan relasi checkpoint
        // Checkpoint menunjukkan tahapan dalam proses pengadaan
        $progress = ProcurementProgress::where('permintaan_pengadaan_id', $projectId)
            ->with('checkpoint') // Eager load checkpoint untuk menghindari N+1 query
            ->orderBy('titik_id') // Urutkan berdasarkan urutan checkpoint
            ->get();

        // Mengembalikan data dalam format JSON
        return response()->json($progress);
    }
}
