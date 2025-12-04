<?php

namespace App\Services;

use App\Models\Checkpoint;
use App\Models\Procurement;
use App\Models\ProcurementProgress;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CheckpointTransitionService
{
    protected $procurement;
    protected $data = [];
    protected $errors = [];

    public function __construct(Procurement $procurement)
    {
        $this->procurement = $procurement;
    }

    public function transition(int $fromCheckpointSequence, array $data = []): array
    {
        $this->data = $data;
        $this->errors = [];

        try {
            DB::beginTransaction();

            $fromCheckpoint = Checkpoint::where('point_sequence', $fromCheckpointSequence)
                ->firstOrFail();

            $toCheckpointSequence = $fromCheckpointSequence + 1;
            $toCheckpoint = Checkpoint::where('point_sequence', $toCheckpointSequence)->first();

            $this->validateTransitionPreconditions($fromCheckpoint, $toCheckpoint);

            if (!empty($this->errors)) {
                return [
                    'success' => false,
                    'errors' => $this->errors,
                    'message' => implode('; ', $this->errors),
                ];
            }

            $this->completeCheckpoint($fromCheckpoint);

            if ($toCheckpoint) {
                $this->initializeCheckpoint($toCheckpoint);
            } else {
                $this->markProcurementCompleted();
            }

            $this->executePostTransitionActions($fromCheckpoint, $toCheckpoint);
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

    protected function validateTransitionPreconditions(Checkpoint $fromCheckpoint, ?Checkpoint $toCheckpoint): void
    {
        $sequence = $fromCheckpoint->point_sequence;

        switch ($sequence) {

            case 1: // Penawaran → Inquiry & Quotation
                $this->validateCheckpoint1To2();
                break;

            case 2: // Inquiry & Quotation → Evatek
                $this->validateCheckpoint2To3();
                break;

            case 3: // Evatek → Negotiation
                $this->validateCheckpoint3To4();
                break;

            case 4: // Negotiation → Usulan Pengadaan
                $this->validateCheckpoint4To5();
                break;

            case 5: // Usulan Pengadaan → Pengesahan Kontrak
                $this->validateCheckpoint5To6();
                break;

            case 6: // Pengesahan Kontrak → Pembayaran DP
                $this->validateCheckpoint6To7();
                break;

            case 7: // Pembayaran DP → Pengiriman Material
                $this->validateCheckpoint7To8();
                break;

            case 8: // Pengiriman Material → Kedatangan Material
                $this->validateCheckpoint8To9();
                break;

            case 9: // Kedatangan Material → Verifikasi Dokumen
                $this->validateCheckpoint9To10();
                break;

            case 10: // Verifikasi Dokumen → Pembayaran
                $this->validateCheckpoint10To11();
                break;
            
            case 11: // Pemabayaran → COMPLETION
                $this->validateCheckpoint10ToCompletion();
                break;
        }
    }

    // ===================== VALIDATION RULES =====================

    protected function validateCheckpoint1To2(): void
    {
        if (!$this->procurement->requestProcurements()->exists()) {
            $this->errors[] = 'Harus ada request procurement minimal 1';
        }
    }

    protected function validateCheckpoint2To3(): void
    {
        // if (empty($this->data['evaluation_notes'])) {
        //     $this->errors[] = 'Catatan evaluasi teknis wajib diisi';
        // }
    }

    protected function validateCheckpoint3To4(): void
    {
        
    }


    protected function validateCheckpoint4To5(): void
    {
        if (empty($this->data['contract_signed'])) {
            $this->errors[] = 'Kontrak harus ditandatangani';
        }
    }

    protected function validateCheckpoint5To6(): void
    {
        if (empty($this->data['delivery_note'])) {
            $this->errors[] = 'Nota pengiriman wajib dilampirkan';
        }
    }

    protected function validateCheckpoint6To7(): void
    {
        if (!$this->procurement->requestProcurements()->whereNotNull('vendor_id')->exists()) {
            $this->errors[] = 'Vendor wajib dipilih sebelum melanjutkan ke Pengesahan Kontrak';
        }

        $payment = $this->procurement->paymentSchedules()
            ->where('payment_type', 'dp')
            ->where('status', 'paid')
            ->first();

        if (!$payment) {
            $this->errors[] = 'Pembayaran DP belum dikonfirmasi';
        }
    }

    protected function validateCheckpoint7To8(): void
    {
        if (empty($this->data['target_arrival_date'])) {
            $this->errors[] = 'Tanggal target kedatangan wajib diisi';
        }
    }

    protected function validateCheckpoint8To9(): void
    {
        if (empty($this->data['goods_receipt_number'])) {
            $this->errors[] = 'Nomor bukti penerimaan barang wajib diisi';
        }
    }

    protected function validateCheckpoint9To10(): void
    {
        if (empty($this->data['documents_received'])) {
            $this->errors[] = 'Konfirmasi penerimaan dokumen wajib diisi';
        }
    }

    protected function validateCheckpoint10To11(): void
    {
        if (empty($this->data['inspection_status'])) {
            $this->errors[] = 'Status inspeksi wajib diisi';
        }

        if ($this->data['inspection_status'] === 'ncr' && empty($this->data['ncr_notes'])) {
            $this->errors[] = 'Jika ada NCR, catatan wajib diisi';
        }
    }

    protected function validateCheckpoint11To12(): void
    {
        if (empty($this->data['berita_acara_number'])) {
            $this->errors[] = 'Nomor Berita Acara wajib diisi';
        }
    }

    protected function validateCheckpoint12To13(): void
    {
        if (!Auth::user() || Auth::user()->roles !== 'accounting') {
            $this->errors[] = 'Hanya Accounting yang dapat verifikasi dokumen';
        }

        if (empty($this->data['verification_notes'])) {
            $this->errors[] = 'Catatan verifikasi wajib diisi';
        }

        $payment = $this->procurement->paymentSchedules()
            ->where('payment_type', 'final')
            ->first();

        if (!$payment) {
            $this->errors[] = 'Payment schedule final belum dibuat';
        }
    }

    protected function validateCheckpoint13ToCompletion(): void
    {
        if (!Auth::user() || Auth::user()->roles !== 'treasury') {
            $this->errors[] = 'Hanya Treasury yang dapat memproses pembayaran';
        }

        if (empty($this->data['payment_date'])) {
            $this->errors[] = 'Tanggal pembayaran wajib diisi';
        }

        $payment = $this->procurement->paymentSchedules()
            ->where('payment_type', 'final')
            ->where('status', 'paid')
            ->first();

        if (!$payment) {
            $this->errors[] = 'Pembayaran final belum dikonfirmasi';
        }
    }

    // ===================== PROGRESS HANDLING =====================

    protected function completeCheckpoint(Checkpoint $checkpoint): void
    {
        $progress = ProcurementProgress::where([
            'procurement_id' => $this->procurement->procurement_id,
            'checkpoint_id'  => $checkpoint->point_id,
        ])->first();

        ProcurementProgress::updateOrCreate(
            [
                'procurement_id' => $this->procurement->procurement_id,
                'checkpoint_id'  => $checkpoint->point_id,
            ],
            [
                'status'      => 'completed',
                'note'        => $this->data['notes'] ?? $checkpoint->point_name . ' selesai',
                'user_id'     => Auth::id(),
                'start_date'  => $progress?->start_date ?? now(),
                'end_date'    => now(),
            ]
        );
    }

    protected function initializeCheckpoint(Checkpoint $checkpoint): void
    {
        ProcurementProgress::updateOrCreate(
            [
                'procurement_id' => $this->procurement->procurement_id,
                'checkpoint_id'  => $checkpoint->point_id,
            ],
            [
                'status'     => 'in_progress',
                'note'       => 'Menunggu ' . $checkpoint->point_name,
                'user_id'    => Auth::id(),
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

    // ===================== POST ACTIONS =====================

    protected function executePostTransitionActions(?Checkpoint $fromCheckpoint, ?Checkpoint $toCheckpoint): void
    {
        if (!$fromCheckpoint) return;

        switch ($fromCheckpoint->point_sequence) {
            case 1:
                break;

            case 6:
                break;

            case 7:
                break;

            case 11:
                break;

            case 12:
                $this->actionAfterCheckpoint12();
                break;
        }
    }

    protected function actionAfterCheckpoint12(): void
    {
        $existingPayment = $this->procurement->paymentSchedules()
            ->where('payment_type', 'final')
            ->first();

        if (!$existingPayment) {
            $amount = $this->procurement->requestProcurements
                ->flatMap(fn($rp) => $rp->items)
                ->sum('total_price');

            \App\Models\PaymentSchedule::create([
                'project_id'     => $this->procurement->project_id,
                'procurement_id' => $this->procurement->procurement_id,
                'payment_type'   => 'final',
                'amount'         => $amount,
                'percentage'     => 100,
                'due_date'       => Carbon::now()->addDays(5),
                'status'         => 'pending',
                'notes'          => 'Auto-generated: ' . $this->procurement->code_procurement,
            ]);
        }
    }

    // ===================== NOTIFICATIONS =====================

    protected function notifyStakeholders(?Checkpoint $fromCheckpoint, ?Checkpoint $toCheckpoint): void
    {
        if (!$fromCheckpoint) return;

        switch ($fromCheckpoint->point_sequence) {

            case 1:
                $this->notifyDivision(7, 'Evatek dimulai', 'Procurement siap evaluasi teknis');
                break;

            case 6:
                $this->notifyDivision(3, 'DP Payment Ready', 'Pembayaran DP siap diproses');
                break;

            case 12:
                $this->notifyDivision(4, 'Verifikasi Dokumen', 'Procurement siap diverifikasi Accounting');
                break;

            case 13:
                $this->notifyCheckpoint13Complete();
                break;
        }
    }

    protected function notifyCheckpoint13Complete(): void
    {
        $this->notifyDivision(3, 'Pembayaran Final', 'Procurement siap pembayaran final');

        $divisions = [2, 3, 4, 5, 6, 7];
        foreach ($divisions as $divisionId) {
            $this->notifyDivision($divisionId, 'Procurement Selesai', 'Procurement telah selesai diproses');
        }
    }

    protected function notifyDivision(int $divisionId, string $title, string $message): void
    {
        $users = \App\Models\User::whereHas('division', fn($q) => 
            $q->where('division_id', $divisionId)
        )->get();

        foreach ($users as $user) {
            Notification::create([
                'user_id'        => $user->user_id,
                'sender_id'      => Auth::id(),
                'type'           => 'procurement_checkpoint',
                'title'          => $title,
                'message'        => $message,
                'reference_type' => 'App\Models\Procurement',
                'reference_id'   => $this->procurement->procurement_id,
            ]);
        }
    }

    // ===================== HELPERS =====================

    protected function getTransitionMessage(?Checkpoint $from, ?Checkpoint $to): string
    {
        if (!$from) return 'Checkpoint transition failed';
        if (!$to) return 'Procurement selesai diproses';

        return "Berhasil berpindah dari '{$from->point_name}' ke '{$to->point_name}'";
    }

    public function getCurrentCheckpoint(): ?Checkpoint
{
    // 1. Cari checkpoint yang status = in_progress
    $inProgress = $this->procurement->procurementProgress()
        ->where('status', 'in_progress')
        ->with('checkpoint')
        ->first();

    if ($inProgress) {
        return $inProgress->checkpoint;
    }

    // 2. Kalau tidak ada yang in_progress, ambil yang terakhir completed
    $completed = $this->procurement->procurementProgress()
        ->where('status', 'completed')
        ->with('checkpoint')
        ->orderBy('checkpoint_id', 'desc')
        ->first();

    return $completed?->checkpoint;
}


    public function getNextCheckpoint(): ?Checkpoint
    {
        $current = $this->getCurrentCheckpoint();
        if (!$current) return Checkpoint::where('point_sequence', 1)->first();

        return Checkpoint::where('point_sequence', $current->point_sequence + 1)->first();
    }

    public function getProgressPercentage(): int
    {
        $total = Checkpoint::count();
        if ($total === 0) return 0;

        $completed = $this->procurement->procurementProgress()
            ->where('status', 'completed')
            ->count();

        return round(($completed / $total) * 100);
    }

    public function completeCurrentAndMoveNext(?string $note = null, array $extraData = []): array
{
    // 1. Cari checkpoint yang sedang aktif (in_progress)
    $current = $this->getCurrentCheckpoint();

    if (!$current) {
        return [
            'success' => false,
            'message' => 'Tidak ada checkpoint aktif untuk dipindahkan.',
        ];
    }

    // 2. Gabungkan note + data tambahan untuk dikirim ke transition()
    $data = array_merge(['notes' => $note], $extraData);

    // 3. Panggil transition() berdasarkan sequence checkpoint sekarang
    return $this->transition($current->point_sequence, $data);
}

}
