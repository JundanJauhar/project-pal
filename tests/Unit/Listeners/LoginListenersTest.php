<?php

namespace Tests\Unit\Listeners;

use App\Helpers\AuditLogger;
use App\Listeners\LogSuccessfulLogin;
use App\Listeners\LogFailedLogin;
use App\Models\UMS\AuditLog;
use App\Models\User;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Failed;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginListenersTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test successful login is logged
     */
    public function test_logs_successful_login()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com'
        ]);

        $listener = new LogSuccessfulLogin();
        $event = new Login('web', $user, false);

        $listener->handle($event);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'login',
            'target_table' => 'users',
            'target_id' => $user->user_id,
        ]);

        $log = AuditLog::where('action', 'login')->first();
        $this->assertEquals('test@example.com', $log->details['email']);
    }

    /**
     * Test successful login captures user details
     */
    public function test_successful_login_captures_user_details()
    {
        $user = User::factory()->create([
            'email' => 'admin@example.com',
            'name' => 'Admin User',
        ]);

        $this->app['request']->server->set('REMOTE_ADDR', '192.168.1.100');
        $this->app['request']->server->set('HTTP_USER_AGENT', 'TestBrowser/1.0');

        $listener = new LogSuccessfulLogin();
        $event = new Login('web', $user, false);

        $listener->handle($event);

        $log = AuditLog::where('action', 'login')->first();

        $this->assertNotNull($log);
        $this->assertEquals('admin@example.com', $log->details['email']);
        $this->assertEquals('192.168.1.100', $log->details['ip']);
        // In test environment, Laravel uses 'Symfony' as default UA
        $this->assertNotEmpty($log->details['ua']);
    }

    /**
     * Test failed login is logged
     */
    public function test_logs_failed_login_attempt()
    {
        $credentials = ['email' => 'wrong@example.com', 'password' => 'wrongpass'];
        $user = null;

        $listener = new LogFailedLogin();
        $event = new Failed('web', $user, $credentials);

        $listener->handle($event);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'login_failed',
            'target_table' => 'users',
            'target_id' => null,
        ]);

        $log = AuditLog::where('action', 'login_failed')->first();
        $this->assertEquals('wrong@example.com', $log->details['email']);
    }

    /**
     * Test failed login captures IP and user agent
     */
    public function test_failed_login_captures_ip_and_user_agent()
    {
        $this->app['request']->server->set('REMOTE_ADDR', '10.0.0.1');
        $this->app['request']->server->set('HTTP_USER_AGENT', 'AttackerBot/2.0');

        $credentials = ['email' => 'hacker@example.com', 'password' => 'hack'];
        $listener = new LogFailedLogin();
        $event = new Failed('web', null, $credentials);

        $listener->handle($event);

        $log = AuditLog::where('action', 'login_failed')->first();

        $this->assertEquals('hacker@example.com', $log->details['email']);
        $this->assertEquals('10.0.0.1', $log->details['ip']);
        // In test environment, Laravel uses 'Symfony' as default UA
        $this->assertNotEmpty($log->details['ua']);
    }

    /**
     * Test failed login without email in credentials
     */
    public function test_failed_login_without_email()
    {
        $credentials = ['username' => 'test', 'password' => 'wrong'];
        
        $listener = new LogFailedLogin();
        $event = new Failed('web', null, $credentials);

        $listener->handle($event);

        $log = AuditLog::where('action', 'login_failed')->first();
        $this->assertNull($log->details['email']);
    }

    /**
     * Test multiple successful logins create separate logs
     */
    public function test_multiple_successful_logins_create_separate_logs()
    {
        $user1 = User::factory()->create(['email' => 'user1@example.com']);
        $user2 = User::factory()->create(['email' => 'user2@example.com']);

        $listener = new LogSuccessfulLogin();

        $event1 = new Login('web', $user1, false);
        $listener->handle($event1);

        $event2 = new Login('web', $user2, false);
        $listener->handle($event2);

        $this->assertDatabaseCount('audit_logs', 2);

        $logs = AuditLog::where('action', 'login')->get();
        $this->assertCount(2, $logs);

        $emails = $logs->pluck('details')->pluck('email')->toArray();
        $this->assertContains('user1@example.com', $emails);
        $this->assertContains('user2@example.com', $emails);
    }

    /**
     * Test multiple failed login attempts are tracked
     */
    public function test_tracks_multiple_failed_login_attempts()
    {
        $listener = new LogFailedLogin();

        for ($i = 1; $i <= 3; $i++) {
            $credentials = ['email' => 'test@example.com', 'password' => "wrong$i"];
            $event = new Failed('web', null, $credentials);
            $listener->handle($event);
        }

        $failedLogs = AuditLog::where('action', 'login_failed')
            ->where('details->email', 'test@example.com')
            ->get();

        $this->assertCount(3, $failedLogs);
    }

    /**
     * Test successful login after failed attempts
     */
    public function test_logs_successful_login_after_failed_attempts()
    {
        $user = User::factory()->create(['email' => 'user@example.com']);

        // Failed attempt
        $failedListener = new LogFailedLogin();
        $failedEvent = new Failed('web', null, ['email' => 'user@example.com', 'password' => 'wrong']);
        $failedListener->handle($failedEvent);

        // Successful login
        $successListener = new LogSuccessfulLogin();
        $successEvent = new Login('web', $user, false);
        $successListener->handle($successEvent);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'login_failed',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'login',
            'target_id' => $user->user_id,
        ]);

        $this->assertDatabaseCount('audit_logs', 2);
    }

    /**
     * Test login listener handles remember me flag
     */
    public function test_handles_remember_me_login()
    {
        $user = User::factory()->create(['email' => 'remember@example.com']);

        $listener = new LogSuccessfulLogin();
        $event = new Login('web', $user, true); // remember = true

        $listener->handle($event);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'login',
            'target_id' => $user->user_id,
        ]);

        $log = AuditLog::where('action', 'login')->first();
        $this->assertEquals('remember@example.com', $log->details['email']);
    }

    /**
     * Test login audit trail chronology
     */
    public function test_maintains_login_audit_trail_chronology()
    {
        $user = User::factory()->create(['email' => 'timeline@example.com']);

        $successListener = new LogSuccessfulLogin();
        $failedListener = new LogFailedLogin();

        // Create a timeline of events
        $event1 = new Login('web', $user, false);
        $successListener->handle($event1);

        sleep(1); // Ensure timestamp difference

        $event2 = new Failed('web', null, ['email' => 'timeline@example.com', 'password' => 'wrong']);
        $failedListener->handle($event2);

        $event3 = new Login('web', $user, false);
        $successListener->handle($event3);

        $logs = AuditLog::orderBy('created_at')->get();

        $this->assertEquals('login', $logs[0]->action);
        $this->assertEquals('login_failed', $logs[1]->action);
        $this->assertEquals('login', $logs[2]->action);
    }
}
