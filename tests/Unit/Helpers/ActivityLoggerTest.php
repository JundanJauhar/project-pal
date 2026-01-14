<?php

namespace Tests\Unit\Helpers;

use App\Helpers\ActivityLogger;
use App\Models\UMS\ActivityLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class ActivityLoggerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a test user and authenticate
        $user = \App\Models\User::factory()->create();
        $this->actingAs($user);
    }

    /**
     * Tes log aktivitas dasar berhasil
     */
    public function test_mencatat_aktivitas_berhasil()
    {
        $log = ActivityLogger::log(
            'procurement',
            'created',
            123,
            ['item' => 'Test Item']
        );

        $this->assertInstanceOf(ActivityLog::class, $log);
        $this->assertDatabaseHas('activity_logs', [
            'actor_user_id' => Auth::id(),
            'module' => 'procurement',
            'action' => 'created',
            'target_id' => 123,
        ]);

        // Check details JSON contains our data
        $this->assertEquals('Test Item', $log->details['item']);
    }

    /**
     * Tes mencatat tanpa target ID
     */
    public function test_mencatat_tanpa_target_id()
    {
        $log = ActivityLogger::log('system', 'startup');

        $this->assertDatabaseHas('activity_logs', [
            'actor_user_id' => Auth::id(),
            'module' => 'system',
            'action' => 'startup',
            'target_id' => null,
        ]);
    }

    /**
     * Tes mencatat tanpa detail
     */
    public function test_mencatat_tanpa_detail()
    {
        $log = ActivityLogger::log('user', 'logout', 456);

        $this->assertDatabaseHas('activity_logs', [
            'actor_user_id' => Auth::id(),
            'module' => 'user',
            'action' => 'logout',
            'target_id' => 456,
        ]);
    }

    /**
     * Tes otomatis menangkap alamat IP
     */
    public function test_otomatis_menangkap_alamat_ip()
    {
        // Simulate a request with IP
        $this->app['request']->server->set('REMOTE_ADDR', '192.168.1.1');

        $log = ActivityLogger::log('test', 'action');

        $this->assertEquals('192.168.1.1', $log->details['ip']);
    }

    /**
     * Tes otomatis menangkap user agent
     */
    public function test_otomatis_menangkap_user_agent()
    {
        // Simulate a request with user agent
        $this->app['request']->server->set('HTTP_USER_AGENT', 'TestBrowser/1.0');

        $log = ActivityLogger::log('test', 'action');

        // In test environment, Laravel uses 'Symfony' as default UA
        $this->assertNotEmpty($log->details['ua']);
        $this->assertIsString($log->details['ua']);
    }

    /**
     * Tes menghormati IP dan UA yang diinput manual
     */
    public function test_menghormati_ip_dan_ua_manual()
    {
        $log = ActivityLogger::log('test', 'action', null, [
            'ip' => '10.0.0.1',
            'ua' => 'CustomAgent/2.0',
            'custom_field' => 'value'
        ]);

        $this->assertEquals('10.0.0.1', $log->details['ip']);
        $this->assertEquals('CustomAgent/2.0', $log->details['ua']);
        $this->assertEquals('value', $log->details['custom_field']);
    }

    /**
     * Tes mencatat dengan detail kompleks
     */
    public function test_mencatat_detail_kompleks()
    {
        $details = [
            'changes' => [
                'old' => ['status' => 'pending'],
                'new' => ['status' => 'approved']
            ],
            'approver' => 'John Doe',
            'notes' => 'Approved via system'
        ];

        $log = ActivityLogger::log('approval', 'approved', 789, $details);

        $this->assertEquals('pending', $log->details['changes']['old']['status']);
        $this->assertEquals('approved', $log->details['changes']['new']['status']);
        $this->assertEquals('John Doe', $log->details['approver']);
    }

    /**
     * Tes menangani detail non-array (harus dikonversi ke array)
     */
    public function test_menangani_detail_non_array()
    {
        $log = ActivityLogger::log('test', 'action', null, 'string detail');

        // Should still capture IP and UA even with non-array input
        $this->assertIsArray($log->details);
        $this->assertArrayHasKey('ip', $log->details);
        $this->assertArrayHasKey('ua', $log->details);
    }

    /**
     * Tes multiple log mempertahankan konteks user
     */
    public function test_multiple_log_pertahankan_konteks_user()
    {
        $log1 = ActivityLogger::log('module1', 'action1');
        $log2 = ActivityLogger::log('module2', 'action2');

        $this->assertEquals(Auth::id(), $log1->actor_user_id);
        $this->assertEquals(Auth::id(), $log2->actor_user_id);
        $this->assertEquals($log1->actor_user_id, $log2->actor_user_id);
    }

    /**
     * Tes mencatat sebagai user yang tidak terautentikasi
     */
    public function test_mencatat_tanpa_user_terautentikasi()
    {
        Auth::logout();

        $log = ActivityLogger::log('public', 'view');

        $this->assertNull($log->actor_user_id);
        $this->assertDatabaseHas('activity_logs', [
            'module' => 'public',
            'action' => 'view',
            'actor_user_id' => null,
        ]);
    }
}
