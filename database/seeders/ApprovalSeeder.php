<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Approval;
use App\Models\Project;
use App\Models\User;
use Carbon\Carbon;

class ApprovalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ambil user sekdir
        $sekdir = User::where('roles', 'sekretaris')->first();
        // Ambil beberapa project
        $projects = Project::take(3)->get();

        foreach ($projects as $project) {
            Approval::create([
                'module' => 'project',
                'module_id' => $project->project_id,
                'approver_id' => $sekdir->user_id,
                'status' => 'verified',
                // Tambahkan field lain jika ada, misal dokumen link, notes, tanggal
                'approval_document_link' => 'https://drive.google.com/file/d/' . rand(100000,999999),
                'approval_notes' => 'Dokumen sudah diverifikasi oleh sekdir.',
                'approved_at' => Carbon::now()->subDays(rand(1,10)),
                'approved_by' => $sekdir->user_id,
            ]);
        }
    }
}
