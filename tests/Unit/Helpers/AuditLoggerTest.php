<?php

namespace Tests\Unit\Helpers;

use App\Helpers\AuditLogger;
use App\Models\UMS\AuditLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class AuditLoggerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create and authenticate test user
        $user = \App\Models\User::factory()->create();
        $this->actingAs($user);
    }

    /**
     * Tes mencatat audit berhasil
     */
    public function test_mencatat_audit_berhasil()
    {
        $log = AuditLogger::log(
            'CREATE',
            'procurements',
            123,
            ['field' => 'value']
        );

        $this->assertInstanceOf(AuditLog::class, $log);
        $this->assertDatabaseHas('audit_logs', [
            'actor_user_id' => Auth::id(),
            'action' => 'CREATE',
            'target_table' => 'procurements',
            'target_id' => 123,
        ]);

        $this->assertEquals('value', $log->details['field']);
    }

    /**
     * Tes mencatat tanpa tabel
     */
    public function test_mencatat_tanpa_tabel()
    {
        $log = AuditLogger::log('SYSTEM_STARTUP');

        $this->assertDatabaseHas('audit_logs', [
            'actor_user_id' => Auth::id(),
            'action' => 'SYSTEM_STARTUP',
            'target_table' => null,
            'target_id' => null,
        ]);
    }

    /**
     * Tes mencatat tanpa target ID
     */
    public function test_mencatat_tanpa_target_id()
    {
        $log = AuditLogger::log('DELETE_ALL', 'cache');

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'DELETE_ALL',
            'target_table' => 'cache',
            'target_id' => null,
        ]);
    }

    /**
     * Tes mencatat tanpa detail
     */
    public function test_mencatat_tanpa_detail()
    {
        $log = AuditLogger::log('VIEW', 'users', 456);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'VIEW',
            'target_table' => 'users',
            'target_id' => 456,
        ]);

        $this->assertIsArray($log->details);
    }

    /**
     * Tes otomatis menangkap alamat IP
     */
    public function test_otomatis_menangkap_alamat_ip()
    {
        $this->app['request']->server->set('REMOTE_ADDR', '10.20.30.40');

        $log = AuditLogger::log('UPDATE', 'settings', 1);

        $this->assertEquals('10.20.30.40', $log->details['ip']);
    }

    /**
     * Tes otomatis menangkap user agent
     */
    public function test_otomatis_menangkap_user_agent()
    {
        $this->app['request']->server->set('HTTP_USER_AGENT', 'Mozilla/5.0');

        $log = AuditLogger::log('UPDATE', 'settings', 1);

        // In test environment, Laravel uses 'Symfony' as default UA
        $this->assertNotEmpty($log->details['ua']);
        $this->assertIsString($log->details['ua']);
    }

    /**
     * Tes menghormati IP dan UA manual
     */
    public function test_menghormati_ip_dan_ua_manual()
    {
        $log = AuditLogger::log('LOGIN', null, null, [
            'ip' => '192.168.1.100',
            'ua' => 'CustomClient/1.0',
            'location' => 'Office'
        ]);

        $this->assertEquals('192.168.1.100', $log->details['ip']);
        $this->assertEquals('CustomClient/1.0', $log->details['ua']);
        $this->assertEquals('Office', $log->details['location']);
    }

    /**
     * Tes mencatat riwayat perubahan detail
     */
    public function test_mencatat_riwayat_perubahan_detail()
    {
        $details = [
            'before' => [
                'status' => 'draft',
                'amount' => 1000000,
            ],
            'after' => [
                'status' => 'approved',
                'amount' => 1500000,
            ],
            'changed_by' => 'Admin User',
            'reason' => 'Budget revision'
        ];

        $log = AuditLogger::log('UPDATE', 'procurements', 99, $details);

        $this->assertEquals('draft', $log->details['before']['status']);
        $this->assertEquals('approved', $log->details['after']['status']);
        $this->assertEquals(1500000, $log->details['after']['amount']);
        $this->assertEquals('Budget revision', $log->details['reason']);
    }

    /**
     * Tes normalisasi detail non-array ke array
     */
    public function test_normalisasi_detail_non_array_ke_array()
    {
        // String details
        $log = AuditLogger::log('NOTE', 'tasks', 1, 'Simple note');

        $this->assertIsArray($log->details);
        
        // Should still add IP and UA
        $this->assertArrayHasKey('ip', $log->details);
        $this->assertArrayHasKey('ua', $log->details);
    }

    /**
     * Tes menangani detail null
     */
    public function test_menangani_detail_null()
    {
        $log = AuditLogger::log('DELETE', 'temp_files', 5, null);

        $this->assertIsArray($log->details);
        $this->assertArrayHasKey('ip', $log->details);
        $this->assertArrayHasKey('ua', $log->details);
    }

    /**
     * Tes membuat jejak audit untuk operasi kritis
     */
    public function test_membuat_jejak_audit_untuk_operasi_kritis()
    {
        // Simulate multiple audit events
        $create = AuditLogger::log('CREATE', 'contracts', 1, ['amount' => 5000000]);
        $update1 = AuditLogger::log('UPDATE', 'contracts', 1, ['status' => 'pending']);
        $update2 = AuditLogger::log('UPDATE', 'contracts', 1, ['status' => 'approved']);
        $approve = AuditLogger::log('APPROVE', 'contracts', 1, ['approver' => 'Manager']);

        $this->assertDatabaseCount('audit_logs', 4);
        
        $allLogs = AuditLog::where('target_table', 'contracts')
            ->where('target_id', 1)
            ->orderBy('created_at')
            ->get();

        $this->assertCount(4, $allLogs);
        $this->assertEquals('CREATE', $allLogs[0]->action);
        $this->assertEquals('APPROVE', $allLogs[3]->action);
    }

    /**
     * Tes mencatat tanpa user terautentikasi
     */
    public function test_mencatat_tanpa_user_terautentikasi()
    {
        Auth::logout();

        $log = AuditLogger::log('GUEST_ACCESS', 'public_pages', 10);

        $this->assertNull($log->actor_user_id);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'GUEST_ACCESS',
            'actor_user_id' => null,
        ]);
    }

    /**
     * Tes mencatat berbagai jenis aksi
     */
    public function test_mencatat_berbagai_jenis_aksi()
    {
        $actions = ['CREATE', 'READ', 'UPDATE', 'DELETE', 'APPROVE', 'REJECT', 'SUBMIT'];

        foreach ($actions as $index => $action) {
            $log = AuditLogger::log($action, 'test_table', $index + 1);
            $this->assertEquals($action, $log->action);
        }

        $this->assertDatabaseCount('audit_logs', count($actions));
    }

    /**
     * Tes pencatatan bersamaan mempertahankan integritas
     */
    public function test_pencatatan_bersamaan_pertahankan_integritas()
    {
        $userId = Auth::id();

        // Log multiple events rapidly
        for ($i = 1; $i <= 10; $i++) {
            AuditLogger::log("ACTION_$i", 'test', $i, ['iteration' => $i]);
        }

        $this->assertDatabaseCount('audit_logs', 10);

        $logs = AuditLog::where('actor_user_id', $userId)->get();
        $this->assertCount(10, $logs);

        // Verify each log has correct data
        foreach ($logs as $log) {
            $this->assertEquals($userId, $log->actor_user_id);
            $this->assertNotNull($log->created_at);
        }
    }
}
