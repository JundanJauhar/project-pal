<?php

namespace App\Services;

use App\Models\Checkpoint;
use App\Models\Procurement;
use App\Models\ProcurementProgress;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * CheckpointTransitionService
 * 
 * Handles generic checkpoint transitions with validation rules.
 * Each checkpoint can have pre-conditions and post-actions defined.
 * 
 * Supported Transitions:
 * CP1 → CP2, CP2 → CP3, ... CP13 → CP14 → Completion
 */
class CheckpointTransitionService
{
    protected $procurement;
    protected $fromCheckpointId;
    protected $toCheckpointId;
    protected $data = [];
    protected $errors = [];

    public function __construct(Procurement $procurement)
    {
        $this->procurement = $procurement;
    }

    /**
     * Transition from current checkpoint to next checkpoint
     * 
     * @param int $fromCheckpointSequence - Starting checkpoint sequence
     * @param array $data - Data needed for transition (notes, attachments, etc)
     * @return array - Result with success flag and messages
     */
    public function transition(int $fromCheckpointSequence, array $data = []): array
    {
        $this->data = $data;
        $this->errors = [];

        try {
            DB::beginTransaction();

            // 1. Get current checkpoint
            $fromCheckpoint = Checkpoint::where('point_sequence', $fromCheckpointSequence)
                ->firstOrFail();

            // 2. Get next checkpoint
            $toCheckpointSequence = $fromCheckpointSequence + 1;
            $toCheckpoint = Checkpoint::where('point_sequence', $toCheckpointSequence)->first();

            // 3. Validate transition preconditions
            $this->validateTransitionPreconditions($fromCheckpoint, $toCheckpoint);

            if (!empty($this->errors)) {
                return [
                    'success' => false,
                    'errors' => $this->errors,
                    'message' => implode('; ', $this->errors),
                ];
            }

            // 4. Complete current checkpoint
            $this->completeCheckpoint($fromCheckpoint);

            // 5. Initialize next checkpoint (or mark as final)
            if ($toCheckpoint) {
                $this->initializeCheckpoint($toCheckpoint);
            } else {
                // No more checkpoints - mark procurement as completed
                $this->markProcurementCompleted();
            }

            // 6. Execute post-transition actions
            $this->executePostTransitionActions($fromCheckpoint, $toCheckpoint);

            // 7. Send notifications
            $this->notifyStakeholders($fromCheckpoint, $toCheckpoint);

            DB::commit();

            return [
                'success' => true,
                'message' => $this->getTransitionMessage($fromCheckpoint, $toCheckpoint),
                'from_checkpoint' => $fromCheckpoint->point_name,
                'to_checkpoint' => $toCheckpoint?->point_name ?? 'COMPLETION',
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Checkpoint transition failed: ' . $e->getMessage());

            return [
                'success' => false,
                'errors' => [$e->getMessage()],
                'message' => 'Gagal melakukan perpindahan checkpoint: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Validate preconditions for checkpoint transition
     * Each checkpoint has specific requirements
     */
    protected function validateTransitionPreconditions(Checkpoint $fromCheckpoint, ?Checkpoint $toCheckpoint): void
    {
        $sequence = $fromCheckpoint->point_sequence;

        // Get or create current progress
        $currentProgress = ProcurementProgress::where([
            'procurement_id' => $this->procurement->procurement_id,
            'checkpoint_id' => $fromCheckpoint->point_id,
        ])->first();

        // Validate based on checkpoint sequence
        switch ($sequence) {
            case 1: // Penawaran Permintaan → Evatek
                $this->validateCheckpoint1To2();
                break;

            case 2: // Evatek → Negosiasi
                $this->validateCheckpoint2To3();
                break;

            case 3: // Negosiasi → Usulan Pengadaan
                $this->validateCheckpoint3To4();
                break;

            case 4: // Usulan Pengadaan → Pengesahan Kontrak
                $this->validateCheckpoint4To5();
                break;

            case 5: // Pengesahan Kontrak → Pengiriman Material
                $this->validateCheckpoint5To6();
                break;

            case 6: // Pengiriman Material → Pembayaran DP
                $this->validateCheckpoint6To7();
                break;

            case 7: // Pembayaran DP → Proses Importasi
                $this->validateCheckpoint7To8();
                break;

            case 8: // Proses Importasi → Kedatangan Material
                $this->validateCheckpoint8To9();
                break;

            case 9: // Kedatangan Material → Serah Terima Dokumen
                $this->validateCheckpoint9To10();
                break;

            case 10: // Serah Terima Dokumen → Inspeksi Barang
                $this->validateCheckpoint10To11();
                break;

            case 11: // Inspeksi Barang → Berita Acara
                $this->validateCheckpoint11To12();
                break;

            case 12: // Berita Acara → Verifikasi Dokumen (Accounting)
                $this->validateCheckpoint12To13();
                break;

            case 13: // Verifikasi Dokumen → Pembayaran (Treasury)
                $this->validateCheckpoint13To14();
                break;

            case 14: // Pembayaran → Completion
                $this->validateCheckpoint14ToCompletion();
                break;
        }
    }

    // ============= VALIDATION RULES PER CHECKPOINT =============

    protected function validateCheckpoint1To2(): void
    {
        // Penawaran Permintaan → Evatek
        // Requirements: 
        // - Must have request procurement created
        if (!$this->procurement->requestProcurements()->exists()) {
            $this->errors[] = 'Harus ada request procurement minimal 1';
        }
    }

    protected function validateCheckpoint2To3(): void
    {
        // Evatek → Negosiasi
        // Requirements:
        // - Evaluation must be completed
        // - Must have evaluation notes/document
        if (empty($this->data['evaluation_notes'])) {
            $this->errors[] = 'Catatan evaluasi teknis wajib diisi';
        }
    }

    protected function validateCheckpoint3To4(): void
    {
        // Negosiasi → Usulan Pengadaan
        // Requirements:
        // - Negotiation completed with vendor
        // - Price agreement reached
        if (empty($this->data['negotiation_result'])) {
            $this->errors[] = 'Hasil negosiasi wajib didokumentasikan';
        }
    }

    protected function validateCheckpoint4To5(): void
    {
        // Usulan Pengadaan → Pengesahan Kontrak
        // Requirements:
        // - OC (Official Confirm) document ready
        // - Management approval received
        if (empty($this->data['oc_document'])) {
            $this->errors[] = 'Dokumen OC wajib dilampirkan';
        }
    }

    protected function validateCheckpoint5To6(): void
    {
        // Pengesahan Kontrak → Pengiriman Material
        // Requirements:
        // - Contract signed by both parties
        // - Contract on file
        if (empty($this->data['contract_signed'])) {
            $this->errors[] = 'Kontrak harus sudah ditandatangani';
        }
    }

    protected function validateCheckpoint6To7(): void
    {
        // Pengiriman Material → Pembayaran DP
        // Requirements:
        // - Material delivered to warehouse
        // - Delivery note received
        if (empty($this->data['delivery_note'])) {
            $this->errors[] = 'Nota pengiriman wajib dilampirkan';
        }
    }

    protected function validateCheckpoint7To8(): void
    {
        // Pembayaran DP → Proses Importasi/Produksi
        // Requirements:
        // - DP payment confirmed
        // - Bank transfer receipt or cash receipt
        if (empty($this->data['payment_reference'])) {
            $this->errors[] = 'Referensi pembayaran DP wajib diisi (nomor transfer/kuitansi)';
        }

        // Check if payment exists and confirmed
        $payment = $this->procurement->paymentSchedules()
            ->where('payment_type', 'dp')
            ->where('status', 'paid')
            ->first();

        if (!$payment) {
            $this->errors[] = 'Pembayaran DP belum dikonfirmasi di sistem pembayaran';
        }
    }

    protected function validateCheckpoint8To9(): void
    {
        // Proses Importasi/Produksi → Kedatangan Material
        // Requirements:
        // - Production/Import process started
        // - Have target arrival date
        if (empty($this->data['target_arrival_date'])) {
            $this->errors[] = 'Tanggal target kedatangan wajib diisi';
        }
    }

    protected function validateCheckpoint9To10(): void
    {
        // Kedatangan Material → Serah Terima Dokumen
        // Requirements:
        // - Material arrived
        // - Goods receipt document on file
        if (empty($this->data['goods_receipt_number'])) {
            $this->errors[] = 'Nomor bukti penerimaan barang wajib diisi';
        }
    }

    protected function validateCheckpoint10To11(): void
    {
        // Serah Terima Dokumen → Inspeksi Barang
        // Requirements:
        // - All shipping documents received
        // - Ready for inspection
        if (empty($this->data['documents_received'])) {
            $this->errors[] = 'Konfirmasi penerimaan dokumen wajib diisi';
        }
    }

    protected function validateCheckpoint11To12(): void
    {
        // Inspeksi Barang → Berita Acara/NCR
        // Requirements:
        // - Inspection completed
        // - Inspection report available
        if (empty($this->data['inspection_status'])) {
            $this->errors[] = 'Status inspeksi wajib diisi (OK/NCR)';
        }

        // If NCR (Non-Conformance Report), require notes
        if ($this->data['inspection_status'] === 'ncr' && empty($this->data['ncr_notes'])) {
            $this->errors[] = 'Jika ada NCR, catatan wajib diisi';
        }
    }

    protected function validateCheckpoint12To13(): void
    {
        // Berita Acara/NCR → Verifikasi Dokumen (Accounting)
        // Requirements:
        // - All NCR (if any) resolved
        // - Ready for payment verification
        if (empty($this->data['berita_acara_number'])) {
            $this->errors[] = 'Nomor Berita Acara wajib diisi';
        }
    }

    protected function validateCheckpoint13To14(): void
    {
        // Verifikasi Dokumen → Pembayaran (Treasury)
        // Requirements:
        // - All documents verified by accounting
        // - Accounting approval received
        // - Payment schedule created
        if (!Auth::user() || Auth::user()->roles !== 'accounting') {
            $this->errors[] = 'Hanya Accounting yang dapat melakukan verifikasi dokumen';
        }

        if (empty($this->data['verification_notes'])) {
            $this->errors[] = 'Catatan verifikasi wajib diisi';
        }

        // Check if payment schedule exists
        $payment = $this->procurement->paymentSchedules()
            ->where('payment_type', 'final')
            ->first();

        if (!$payment) {
            $this->errors[] = 'Payment schedule final belum dibuat';
        }
    }

    protected function validateCheckpoint14ToCompletion(): void
    {
        // Pembayaran → Completion
        // Requirements:
        // - Final payment completed by treasury
        // - Payment confirmed in system
        if (!Auth::user() || Auth::user()->roles !== 'treasury') {
            $this->errors[] = 'Hanya Treasury yang dapat memproses pembayaran final';
        }

        if (empty($this->data['payment_date'])) {
            $this->errors[] = 'Tanggal pembayaran wajib diisi';
        }

        // Check if payment confirmed as paid
        $payment = $this->procurement->paymentSchedules()
            ->where('payment_type', 'final')
            ->where('status', 'paid')
            ->first();

        if (!$payment) {
            $this->errors[] = 'Pembayaran final belum dikonfirmasi dibayar';
        }
    }

    // ============= CHECKPOINT COMPLETION =============

    protected function completeCheckpoint(Checkpoint $checkpoint): void
    {
        $progress = ProcurementProgress::updateOrCreate(
            [
                'procurement_id' => $this->procurement->procurement_id,
                'checkpoint_id' => $checkpoint->point_id,
            ],
            [
                'status' => 'completed',
                'note' => $this->data['notes'] ?? $checkpoint->point_name . ' selesai',
                'user_id' => Auth::id(),
                'start_date' => $progress->start_date ?? now(),
                'end_date' => now(),
            ]
        );
    }

    protected function initializeCheckpoint(Checkpoint $checkpoint): void
    {
        ProcurementProgress::updateOrCreate(
            [
                'procurement_id' => $this->procurement->procurement_id,
                'checkpoint_id' => $checkpoint->point_id,
            ],
            [
                'status' => 'in_progress',
                'note' => 'Menunggu ' . $checkpoint->point_name,
                'user_id' => Auth::id(),
                'start_date' => now(),
            ]
        );
    }

    protected function markProcurementCompleted(): void
    {
        $this->procurement->update([
            'status_procurement' => 'completed',
        ]);
    }

    // ============= POST-TRANSITION ACTIONS =============

    protected function executePostTransitionActions(?Checkpoint $fromCheckpoint, ?Checkpoint $toCheckpoint): void
    {
        if (!$fromCheckpoint) return;

        $sequence = $fromCheckpoint->point_sequence;

        // Special actions per checkpoint transition
        switch ($sequence) {
            case 1:
                $this->actionAfterCheckpoint1();
                break;

            case 6:
                $this->actionAfterCheckpoint6();
                break;

            case 7:
                $this->actionAfterCheckpoint7();
                break;

            case 11:
                $this->actionAfterCheckpoint11();
                break;

            case 13:
                $this->actionAfterCheckpoint13(); // Create payment schedule
                break;

            case 14:
                $this->actionAfterCheckpoint14(); // Mark as completed
                break;
        }
    }

    protected function actionAfterCheckpoint1(): void
    {
        // After Penawaran Permintaan - maybe create items in system
    }

    protected function actionAfterCheckpoint6(): void
    {
        // After Pengiriman Material - update inventory
    }

    protected function actionAfterCheckpoint7(): void
    {
        // After Pembayaran DP - create termin payment schedule if needed
    }

    protected function actionAfterCheckpoint11(): void
    {
        // After Inspeksi Barang - process NCR if any
    }

    protected function actionAfterCheckpoint13(): void
    {
        // After Verifikasi Dokumen - Create payment schedule for final payment
        $existingPayment = $this->procurement->paymentSchedules()
            ->where('payment_type', 'final')
            ->first();

        if (!$existingPayment) {
            $totalAmount = $this->procurement->requestProcurements
                ->flatMap(fn($rp) => $rp->items)
                ->sum('total_price');

            \App\Models\PaymentSchedule::create([
                'project_id' => $this->procurement->project_id,
                'procurement_id' => $this->procurement->procurement_id,
                'payment_type' => 'final',
                'amount' => $totalAmount,
                'percentage' => 100,
                'due_date' => Carbon::now()->addDays(5),
                'status' => 'pending',
                'notes' => 'Auto-generated from Procurement: ' . $this->procurement->code_procurement,
            ]);
        }
    }

    protected function actionAfterCheckpoint14(): void
    {
        // After Pembayaran - final completion actions
        // Maybe generate completion certificate, update project status, etc
    }

    // ============= NOTIFICATIONS =============

    protected function notifyStakeholders(?Checkpoint $fromCheckpoint, ?Checkpoint $toCheckpoint): void
    {
        if (!$fromCheckpoint) return;

        $sequence = $fromCheckpoint->point_sequence;

        switch ($sequence) {
            case 1:
                $this->notifyCheckpoint1To2();
                break;

            case 6:
                $this->notifyCheckpoint6To7();
                break;

            case 12:
                $this->notifyCheckpoint12To13();
                break;

            case 13:
                $this->notifyCheckpoint13To14();
                break;

            case 14:
                $this->notifyCheckpoint14Complete();
                break;
        }
    }

    protected function notifyCheckpoint1To2(): void
    {
        // Notify QA/Technical division for evaluation
        $this->notifyDivision(7, 'Evatek dimulai', 'Procurement ' . $this->procurement->code_procurement . ' siap untuk evaluasi teknis');
    }

    protected function notifyCheckpoint6To7(): void
    {
        // Notify Treasury for DP payment
        $this->notifyDivision(3, 'DP Payment Ready', 'Pembayaran DP untuk procurement ' . $this->procurement->code_procurement . ' siap diproses');
    }

    protected function notifyCheckpoint12To13(): void
    {
        // Notify Accounting for verification
        $this->notifyDivision(4, 'Verifikasi Dokumen', 'Procurement ' . $this->procurement->code_procurement . ' siap diverifikasi');
    }

    protected function notifyCheckpoint13To14(): void
    {
        // Notify Treasury for final payment
        $this->notifyDivision(3, 'Pembayaran Final', 'Procurement ' . $this->procurement->code_procurement . ' siap untuk pembayaran final');
    }

    protected function notifyCheckpoint14Complete(): void
    {
        // Notify all for completion
        $divisions = [2, 3, 4, 5, 6, 7];
        foreach ($divisions as $divisionId) {
            $this->notifyDivision($divisionId, 'Procurement Selesai', 'Procurement ' . $this->procurement->code_procurement . ' telah selesai diproses');
        }
    }

    protected function notifyDivision(int $divisionId, string $title, string $message): void
    {
        $users = \App\Models\User::whereHas('division', function($q) use ($divisionId) {
            $q->where('division_id', $divisionId);
        })->get();

        foreach ($users as $user) {
            try {
                Notification::create([
                    'user_id' => $user->user_id,
                    'sender_id' => Auth::id(),
                    'type' => 'procurement_checkpoint',
                    'title' => $title,
                    'message' => $message,
                    'reference_type' => 'App\Models\Procurement',
                    'reference_id' => $this->procurement->procurement_id,
                ]);
            } catch (\Exception $e) {
                \Log::error('Failed to notify: ' . $e->getMessage());
            }
        }
    }

    // ============= HELPERS =============

    protected function getTransitionMessage(?Checkpoint $from, ?Checkpoint $to): string
    {
        if (!$from) return 'Checkpoint transition failed';
        if (!$to) return 'Procurement selesai diproses';

        return "Berhasil melakukan perpindahan dari '{$from->point_name}' ke '{$to->point_name}'";
    }

    /**
     * Get current checkpoint info
     */
    public function getCurrentCheckpoint(): ?Checkpoint
    {
        $lastProgress = $this->procurement->procurementProgress()
            ->with('checkpoint')
            ->orderByDesc('checkpoint_id')
            ->first();

        return $lastProgress?->checkpoint;
    }

    /**
     * Get next checkpoint info
     */
    public function getNextCheckpoint(): ?Checkpoint
    {
        $current = $this->getCurrentCheckpoint();
        if (!$current) {
            return Checkpoint::where('point_sequence', 1)->first();
        }

        return Checkpoint::where('point_sequence', $current->point_sequence + 1)->first();
    }

    /**
     * Get checkpoint progress percentage
     */
    public function getProgressPercentage(): int
    {
        $totalCheckpoints = Checkpoint::count();
        if ($totalCheckpoints === 0) return 0;

        $completedCheckpoints = $this->procurement->procurementProgress()
            ->where('status', 'completed')
            ->count();

        return round(($completedCheckpoints / $totalCheckpoints) * 100);
    }

    /**
     * Transition inspection checkpoint (CP11 -> CP12/CP13)
     * FIXED VERSION - Properly handles ENUM status values
     * 
     * @param string $statusProc - 'lolos', 'gagal', atau 'sedang'
     * @return array - Result with success flag and messages
     */
    public function transitionInspection(string $statusProc): array
    {
        try {
            DB::beginTransaction();

            $procId = $this->procurement->procurement_id;
            $now    = now()->toDateString();

            // Ambil ID CP11, CP12, CP13
            $cp11 = Checkpoint::where('point_name', 'Inspeksi Barang')->value('point_id');
            $cp12 = Checkpoint::where('point_name', 'Berita Acara / NCR')->value('point_id');
            $cp13 = Checkpoint::where('point_name', 'Verifikasi Dokumen')->value('point_id');

            // Validasi checkpoint ada
            if (!$cp11 || !$cp12 || !$cp13) {
                return [
                    'success' => false,
                    'message' => 'Checkpoint tidak ditemukan. Pastikan "Inspeksi Barang", "Berita Acara / NCR", dan "Verifikasi Dokumen" sudah ada di database.',
                ];
            }

            // Helper untuk set progress dengan proper ENUM handling
            $setProgress = function (?int $checkpointId, string $status) use ($procId, $now) {
                if (!$checkpointId) return;

                // ✅ FIXED: Validasi status enum values
                $validStatuses = ['not_started', 'in_progress', 'completed', 'blocked'];
                if (!in_array($status, $validStatuses)) {
                    $status = 'not_started'; // Default fallback
                }

                ProcurementProgress::updateOrCreate(
                    [
                        'procurement_id' => $procId,
                        'checkpoint_id' => $checkpointId,
                    ],
                    [
                        'status' => $status,  // ✅ String properly quoted
                        'start_date' => ($status === 'in_progress') ? $now : null,
                        'end_date' => ($status === 'completed') ? $now : null,
                        'user_id' => Auth::id(),
                        'updated_at' => now(),
                    ]
                );
            };

            // LOGIC BERDASARKAN HASIL INSPEKSI
            if ($statusProc === 'lolos') {
                // Semua item LOLOS
                // CP11 (Inspeksi Barang) = completed
                // CP12 (Berita Acara) = not_started (skip)
                // CP13 (Verifikasi Dokumen) = in_progress (next)
                $setProgress($cp11, 'completed');
                $setProgress($cp12, 'not_started');
                $setProgress($cp13, 'in_progress');

            } elseif ($statusProc === 'gagal') {
                // Ada item yang GAGAL
                // CP11 (Inspeksi Barang) = completed
                // CP12 (Berita Acara / NCR) = in_progress (handle NCR)
                // CP13 (Verifikasi Dokumen) = not_started (wait for NCR done)
                $setProgress($cp11, 'completed');
                $setProgress($cp12, 'in_progress');
                $setProgress($cp13, 'not_started');

            } else {
                // SEDANG (sebagian inspected, sebagian belum)
                // CP11 (Inspeksi Barang) = in_progress (masih ongoing)
                // CP12, CP13 = not_started (wait for CP11 complete)
                $setProgress($cp11, 'in_progress');
                $setProgress($cp12, 'not_started');
                $setProgress($cp13, 'not_started');
            }

            DB::commit();

            return [
                'success' => true,
                'message' => 'Checkpoint berhasil diupdate berdasarkan status inspeksi: ' . $statusProc,
                'status_procs' => $statusProc,
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('transitionInspection error: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());

            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ];
        }
    }
}