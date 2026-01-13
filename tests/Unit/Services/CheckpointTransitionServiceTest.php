<?php

namespace Tests\Unit\Services;

use App\Models\Checkpoint;
use App\Models\Procurement;
use App\Models\ProcurementProgress;
use App\Models\User;
use App\Services\CheckpointTransitionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;


class CheckpointTransitionServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $procurement;
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create(['roles' => 'supply_chain']);
        $this->actingAs($this->user);
        
        $this->procurement = Procurement::factory()->create();
        
        // Create checkpoints
        for ($i = 1; $i <= 11; $i++) {
            Checkpoint::create([
                'point_sequence' => $i,
                'point_name' => "Checkpoint $i",
                'description' => "Description for checkpoint $i",
            ]);
        }
    }

    /**
     * Test successful transition from checkpoint 1 to 2
     */
    public function test_successful_transition_from_checkpoint_1_to_2()
    {
        // Create request procurement (required for transition)
        \App\Models\RequestProcurement::factory()->create([
            'procurement_id' => $this->procurement->procurement_id,
            'vendor_id' => \App\Models\Vendor::factory()->create()->id_vendor,
        ]);

        $service = new CheckpointTransitionService($this->procurement);
        $result = $service->transition(1, ['notes' => 'Moving to checkpoint 2']);

        $this->assertTrue($result['success']);
        $this->assertEquals('Checkpoint 1', $result['from_checkpoint']);
        $this->assertEquals('Checkpoint 2', $result['to_checkpoint']);
    }

    /**
     * Test validation failure when transitioning without required data
     */
    public function test_transition_fails_without_request_procurement()
    {
        $service = new CheckpointTransitionService($this->procurement);
        $result = $service->transition(1);

        $this->assertFalse($result['success']);
        $this->assertNotEmpty($result['errors']);
        $this->assertStringContainsString('request procurement', $result['message']);
    }

    /**
     * Test checkpoint 2 to 3 requires inquiry quotation
     */
    public function test_checkpoint_2_to_3_requires_inquiry_quotation()
    {
        $service = new CheckpointTransitionService($this->procurement);
        $result = $service->transition(2);

        $this->assertFalse($result['success']);
        $this->assertContains('Minimal harus ada 1 Inquiry & Quotation.', $result['errors']);
    }

    /**
     * Test checkpoint 3 to 4 transition (Evatek to Negotiation)
     */
    public function test_checkpoint_3_to_4_transition_passes()
    {
        // Checkpoint 3 to 4 has no validation (commented out in the code)
        $service = new CheckpointTransitionService($this->procurement);
        $result = $service->transition(3);

        $this->assertTrue($result['success']);
    }

    /**
     * Test checkpoint 4 to 5 requires negotiation
     */
    public function test_checkpoint_4_to_5_requires_negotiation()
    {
        $service = new CheckpointTransitionService($this->procurement);
        $result = $service->transition(4);

        $this->assertFalse($result['success']);
        $this->assertContains('Minimal 1 negotiation.', $result['errors']);
    }

    /**
     * Test checkpoint 5 to 6 requires pengadaan OC
     */
    public function test_checkpoint_5_to_6_requires_pengadaan_oc()
    {
        $service = new CheckpointTransitionService($this->procurement);
        $result = $service->transition(5);

        $this->assertFalse($result['success']);
        $this->assertContains('Minimal 1 pengadaanOC.', $result['errors']);
    }

    /**
     * Test checkpoint 6 to 7 requires pengesahan kontrak
     */
    public function test_checkpoint_6_to_7_requires_pengesahan_kontrak()
    {
        $service = new CheckpointTransitionService($this->procurement);
        $result = $service->transition(6);

        $this->assertFalse($result['success']);
        $this->assertContains('Minimal 1 pengesahanKontrak.', $result['errors']);
    }

    /**
     * Test checkpoint progress is created and updated correctly
     */
    public function test_creates_procurement_progress_records()
    {
        // Add request procurement
        $this->procurement->requestProcurements()->create([
            'vendor_id' => \App\Models\Vendor::factory()->create()->id_vendor,
            'request_name' => 'Test Request',
            'created_date' => now()->toDateString(),
            'item_name' => 'Test Item',
            'quantity' => 10,
            'unit_price' => 100000,
        ]);

        $service = new CheckpointTransitionService($this->procurement);
        $service->transition(1);

        // Check that checkpoint 1 is marked as completed
        $checkpoint1 = Checkpoint::where('point_sequence', 1)->first();
        $progress1 = ProcurementProgress::where([
            'procurement_id' => $this->procurement->procurement_id,
            'checkpoint_id' => $checkpoint1->point_id,
        ])->first();

        $this->assertNotNull($progress1);
        $this->assertEquals('completed', $progress1->status);

        // Check that checkpoint 2 is initialized as in_progress
        $checkpoint2 = Checkpoint::where('point_sequence', 2)->first();
        $progress2 = ProcurementProgress::where([
            'procurement_id' => $this->procurement->procurement_id,
            'checkpoint_id' => $checkpoint2->point_id,
        ])->first();

        $this->assertNotNull($progress2);
        $this->assertEquals('in_progress', $progress2->status);
    }

    /**
     * Test transition to completion marks procurement as completed
     */
    public function test_final_transition_marks_procurement_completed()
    {
        // Mock all requirements for checkpoint 11
        $this->user->update(['roles' => 'treasury']);
        
        \App\Models\PaymentSchedule::create([
            'project_id' => $this->procurement->project_id,
            'payment_type' => 'final',
            'status' => 'paid',
            'amount' => 1000000,
            'payment_date' => now(),
        ]);

        $service = new CheckpointTransitionService($this->procurement);
        $result = $service->transition(11, [
            'payment_date' => now()->toDateString()
        ]);

        $this->assertTrue($result['success']);
        $this->assertEquals('COMPLETION', $result['to_checkpoint']);
        
        $this->procurement->refresh();
        $this->assertEquals('completed', $this->procurement->status_procurement);
    }

    /**
     * Test only treasury can process final payment
     */
    public function test_checkpoint_11_to_completion_requires_treasury_role()
    {
        // User is supply_chain, not treasury
        $service = new CheckpointTransitionService($this->procurement);
        $result = $service->transition(11, ['payment_date' => now()->toDateString()]);

        $this->assertFalse($result['success']);
        $this->assertContains('Hanya Treasury yang dapat memproses pembayaran final', $result['errors']);
    }

    /**
     * Test checkpoint 11 requires payment date
     */
    public function test_checkpoint_11_requires_payment_date()
    {
        $this->user->update(['roles' => 'treasury']);
        
        $service = new CheckpointTransitionService($this->procurement);
        $result = $service->transition(11);

        $this->assertFalse($result['success']);
        $this->assertContains('Tanggal pembayaran final wajib diisi', $result['errors']);
    }

    /**
     * Test checkpoint 11 requires paid final payment
     */
    public function test_checkpoint_11_requires_paid_final_payment()
    {
        $this->user->update(['roles' => 'treasury']);
        
        $service = new CheckpointTransitionService($this->procurement);
        $result = $service->transition(11, ['payment_date' => now()->toDateString()]);

        $this->assertFalse($result['success']);
        $this->assertContains('Pembayaran final belum dikonfirmasi di sistem', $result['errors']);
    }

    /**
     * Test transition rolls back on exception
     */
    public function test_transition_rolls_back_on_exception()
    {
        // Try to transition from invalid checkpoint
        $service = new CheckpointTransitionService($this->procurement);
        
        $initialProgressCount = ProcurementProgress::count();
        
        try {
            $result = $service->transition(999); // Invalid checkpoint
            $this->assertFalse($result['success']);
        } catch (\Exception $e) {
            // Expected
        }

        // Progress count should not change
        $this->assertEquals($initialProgressCount, ProcurementProgress::count());
    }

    /**
     * Test transition notes are saved
     */
    public function test_transition_saves_notes()
    {
        $this->procurement->requestProcurements()->create([
            'vendor_id' => \App\Models\Vendor::factory()->create()->id_vendor,
            'request_name' => 'Test Request',
            'created_date' => now()->toDateString(),
            'item_name' => 'Test Item',
            'quantity' => 10,
            'unit_price' => 100000,
        ]);

        $notes = 'Custom transition notes';
        $service = new CheckpointTransitionService($this->procurement);
        $service->transition(1, ['notes' => $notes]);

        $checkpoint1 = Checkpoint::where('point_sequence', 1)->first();
        $progress = ProcurementProgress::where([
            'procurement_id' => $this->procurement->procurement_id,
            'checkpoint_id' => $checkpoint1->point_id,
        ])->first();

        $this->assertEquals($notes, $progress->note);
    }

    /**
     * Test user_id is recorded in progress
     */
    public function test_transition_records_user_id()
    {
        $this->procurement->requestProcurements()->create([
            'vendor_id' => \App\Models\Vendor::factory()->create()->id_vendor,
            'request_name' => 'Test Request',
            'created_date' => now()->toDateString(),
            'item_name' => 'Test Item',
            'quantity' => 10,
            'unit_price' => 100000,
        ]);

        $service = new CheckpointTransitionService($this->procurement);
        $service->transition(1);

        $checkpoint1 = Checkpoint::where('point_sequence', 1)->first();
        $progress = ProcurementProgress::where([
            'procurement_id' => $this->procurement->procurement_id,
            'checkpoint_id' => $checkpoint1->point_id,
        ])->first();

        $this->assertEquals($this->user->user_id, $progress->user_id);
    }
}
