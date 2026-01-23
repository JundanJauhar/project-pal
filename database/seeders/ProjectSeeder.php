<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\Vendor;
use App\Models\Procurement;
use App\Models\RequestProcurement;
use App\Models\Item;
use App\Models\Checkpoint;
use App\Models\ProcurementProgress;
use App\Services\CheckpointTransitionService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ProjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        /**
         * ============================
         * PROJECT 1
         * ============================
         */
        Project::updateOrCreate(
            ['project_code' => 'W000301'],
            [
                'project_name' => 'Pengadaan Material Kapal Fregat',
                'description' => 'Pengadaan material utama untuk kapal fregat kelas sigma',
            ]
        );

        /**
         * PROJECT 2
         * ============================
         */
        Project::updateOrCreate(
            ['project_code' => 'W000302'],
            [
                'project_name' => 'Pengadaan Sistem Radar Navigasi',
                'description' => 'Pengadaan radar navigasi untuk kapal perang',
            ]
        );

        /**
         * PROJECT 3
         * ============================
         */
        Project::updateOrCreate(
            ['project_code' => 'W000303'],
            [
                'project_name' => 'Pengadaan Mesin Diesel Utama',
                'description' => 'Mesin diesel untuk kapal tanker',
            ]
        );

        /**
         * PROJECT 4
         * ============================
         */
        Project::updateOrCreate(
            ['project_code' => 'W000304'],
            [
                'project_name' => 'Pengadaan Peralatan Keselamatan Kapal',
                'description' => 'Life jacket, fire extinguisher, dll.',
            ]
        );

        /**
         * PROJECT 5
         * ============================
         */
        Project::updateOrCreate(
            ['project_code' => 'W000305'],
            [
                'project_name' => 'Pengadaan Cat Anti Karat & Coating',
                'description' => 'Cat marine grade untuk kapal',
            ]
        );

        /**
         * PROJECT 6
         * ============================
         */
        Project::updateOrCreate(
            ['project_code' => 'W000306'],
            [
                'project_name' => 'Pengadaan Sistem Komunikasi Satelit',
                'description' => 'Sistem satelit untuk kapal jelajah jauh',
            ]
        );

        /**
         * PROJECT 7
         * ============================
         */
        Project::updateOrCreate(
            ['project_code' => 'W000307'],
            [
                'project_name' => 'Pengadaan Generator Listrik',
                'description' => 'Generator cadangan 500 KVA',
            ]
        );

        /**
         * ============================
         * CREATE SAMPLE PROCUREMENT WITH 1 ITEM
         * ============================
         */
        $project1 = Project::where('project_code', 'W000301')->first();
        $dept1 = \App\Models\Department::first();

        // ✅ Create Procurement
        $procurement = Procurement::create([
            'project_id' => $project1->project_id,
            'code_procurement' => 'W000301-01',
            'name_procurement' => 'Pengadaan Plat Baja untuk Hull Kapal',
            'description' => 'Plat baja marine grade berkualitas tinggi',
            'department_procurement' => $dept1->department_id,
            'priority' => 'tinggi',
            'start_date' => Carbon::now(),
            'end_date' => Carbon::now()->addDays(30),
            'status_procurement' => 'in_progress',
        ]);

        // ✅ Create RequestProcurement
        $requestProcurement = RequestProcurement::create([
            'procurement_id' => $procurement->procurement_id,
            'project_id' => $project1->project_id,
            'request_name' => 'Request Plat Baja Hull',
            'created_date' => Carbon::now(),
            'deadline_date' => Carbon::now()->addDays(30),
            'request_status' => 'submitted',
            'department_id' => $dept1->department_id,
        ]);

        // ✅ Create Item 1
        Item::create([
            'request_procurement_id' => $requestProcurement->request_id,
            'item_name' => 'Plat Baja Marine Grade A131',
            'item_description' => 'Plat baja dengan ketebalan 15mm untuk struktur hull kapal',
            'specification' => 'Grade A131, Thickness 15mm, Width 2000mm',
            'amount' => 100,
            'unit' => 'ton',
        ]);

        // ✅ Create Item 2
        Item::create([
            'request_procurement_id' => $requestProcurement->request_id,
            'item_name' => 'Profil Baja IPN 400',
            'item_description' => 'Profil baja I-beam untuk penguatan struktur kapal',
            'specification' => 'IPN 400, Grade A36, Length 6000mm',
            'amount' => 50,
            'unit' => 'batang',
        ]);

        // ✅ Create Checkpoint Progress
        $checkpoints = Checkpoint::orderBy('point_sequence', 'asc')->take(2)->get();
        
        foreach ($checkpoints as $index => $checkpoint) {
            ProcurementProgress::create([
                'procurement_id' => $procurement->procurement_id,
                'checkpoint_id' => $checkpoint->point_id,
                'user_id' => 1,
                'status' => $index === 0 ? 'completed' : 'in_progress',
                'start_date' => Carbon::now()->subDays(2 - $index),
                'end_date' => $index === 0 ? Carbon::now()->subDays(1) : null,
                'note' => 'Checkpoint ' . ($index + 1) . ': ' . $checkpoint->point_name,
            ]);
        }

        echo "✅ Seeder berhasil! Project, Vendor, dan Procurement dengan 1 Item sudah dibuat.\n";
    }
}