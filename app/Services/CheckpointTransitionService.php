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

    /**
     * Pindah dari satu checkpoint (berdasarkan sequence) ke checkpoint berikutnya.
     */
    public function transition(int $fromCheckpointSequence, array $data = []): array
    {
        $this->data   = $data;
        $this->errors = [];

        try {
            DB::beginTransaction();

            // Checkpoint asal (wajib ada)
            $fromCheckpoint = Checkpoint::where('point_sequence', $fromCheckpointSequence)
                ->firstOrFail();

            // Checkpoint tujuan = sequence + 1 (jika ada)
            $toCheckpointSequence = $fromCheckpointSequence + 1;
            $toCheckpoint = Checkpoint::where('point_sequence', $toCheckpointSequence)->first();

            // Validasi sebelum pindah
            $this->validateTransitionPreconditions($fromCheckpoint, $toCheckpoint);

            if (!empty($this->errors)) {
                DB::rollBack();

                return [
                    'success' => false,
                    'errors'  => $this->errors,
                    'message' => implode('; ', $this->errors),
                ];
            }

            // Tandai checkpoint sekarang sebagai completed
            $this->completeCheckpoint($fromCheckpoint);

            // Jika masih ada checkpoint berikutnya → buat / update progress-nya
            if ($toCheckpoint) {
                $this->initializeCheckpoint($toCheckpoint);
            } else {
                // Kalau tidak ada lagi → procurement selesai
                $this->markProcurementCompleted();
            }

            // Aksi tambahan + notifikasi
            $this->executePostTransitionActions($fromCheckpoint, $toCheckpoint);
            $this->notifyStakeholders($fromCheckpoint, $toCheckpoint);

            DB::commit();

            return [
                'success'         => true,
                'message'         => $this->getTransitionMessage($fromCheckpoint, $toCheckpoint),
                'from_checkpoint' => $fromCheckpoint->point_name,
                'to_checkpoint'   => $toCheckpoint?->point_name ?? 'COMPLETION',
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Checkpoint transition failed: ' . $e->getMessage());

            return [
                'success' => false,
                'errors'  => [$e->getMessage()],
                'message' => 'Gagal melakukan perpindahan checkpoint: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Mapping validasi 11 checkpoint:
     *
     * 1  Permintaan Pengadaan
     * 2  Inquiry & Quotation
     * 3  Evatek
     * 4  Negotiation
     * 5  Usulan Pengadaan / OC      → Supply Chain kelola vendor
     * 6  Pengesahan Kontrak         → Sekretaris: kontrak disahkan (contract_signed)
     * 7  Pembayaran DP              → Accounting: bayar DP
     * 8  Pengiriman Material        → Supply Chain: tabel pengiriman material
     * 9  Kedatangan Material        → QA: inspeksi barang
     * 10 Verifikasi Dokumen         → Accounting
     * 11 Pembayaran                 → Treasury: pembayaran final
     */
    protected function validateTransitionPreconditions(Checkpoint $fromCheckpoint, ?Checkpoint $toCheckpoint): void
    {
        $sequence = $fromCheckpoint->point_sequence;

        switch ($sequence) {
            case 1: // 1 → 2
                $this->validateCheckpoint1To2();
                break;

            case 2: // 2 → 3
                $this->validateCheckpoint2To3();
                break;

            case 3: // 3 → 4
                $this->validateCheckpoint3To4();
                break;

            case 4: // 4 → 5
                $this->validateCheckpoint4To5();
                break;

            case 5: // 5 → 6 (Usulan Pengadaan / OC → Pengesahan Kontrak)
                $this->validateCheckpoint5To6();
                break;

            case 6: // 6 → 7 (Pengesahan Kontrak → Pembayaran DP)
                $this->validateCheckpoint6To7();
                break;

            case 7: // 7 → 8 (Pembayaran DP → Pengiriman Material)
                $this->validateCheckpoint7To8();
                break;

            case 8: // 8 → 9 (Pengiriman Material → Kedatangan Material)
                $this->validateCheckpoint8To9();
                break;

            case 9: // 9 → 10 (Kedatangan Material → Verifikasi Dokumen)
                $this->validateCheckpoint9To10();
                break;

            case 10: // 10 → 11 (Verifikasi Dokumen → Pembayaran)
                $this->validateCheckpoint10To11();
                break;

            case 11: // 11 → COMPLETION (Pembayaran → selesai)
                $this->validateCheckpoint11ToCompletion();
                break;
        }
    }

    // ===================== VALIDATION RULES =====================

    /** 1 → 2: minimal ada 1 request procurement */
    protected function validateCheckpoint1To2(): void
    {
        if (!$this->procurement->requestProcurements()->exists()) {
            $this->errors[] = 'Harus ada request procurement minimal 1';
        }
    }

    /** 2 → 3: bisa diisi validasi Inquiry & Quotation kalau dibutuhkan */
    protected function validateCheckpoint2To3(): void
    {
        if(!$this->procurement->inquiryQuotations()->exists()){
        $this->errors[] = 'Minimal harus ada 1 Inquiry & Quotation.';
        }
    }

    /** 3 → 4: Evatek → Negotiation */
    protected function validateCheckpoint3To4(): void
    {
        $allEvatek = $this->procurement->evatekItems()->get();

        if ($allEvatek->isEmpty()) {
            $this->errors[] = 'Belum ada EVATEK untuk procurement ini.';
            return;
        }

        $allItemIds = $allEvatek->pluck('item_id')->unique();

        $itemIdsWithApproved = $allEvatek
            ->where('status', 'approve')
            ->pluck('item_id')
            ->unique();

        $missingItems = $allItemIds->diff($itemIdsWithApproved);

        if ($missingItems->isNotEmpty()) {
            $this->errors[] = 'Setiap item harus memiliki minimal satu vendor EVATEK yang approve.';
        }
    }

    /** 4 → 5: Negotiation → Usulan Pengadaan / OC */
    protected function validateCheckpoint4To5(): void
    {
        if(!$this->procurement->negotiations()->exists()){
        $this->errors[] = 'Minimal 1 negotiation.';
        }
    }

    /** ✅ 5 → 6: Usulan Pengadaan / OC → Pengesahan Kontrak (WAJIB vendor) */
    protected function validateCheckpoint5To6(): void
    {
        if (!$this->procurement->requestProcurements()->whereNotNull('vendor_id')->exists()) {
            $this->errors[] = 'Vendor wajib dipilih sebelum melanjutkan ke Pengesahan Kontrak';
        }
    }

    /** ✅ 6 → 7: Pengesahan Kontrak → Pembayaran DP (WAJIB kontrak disahkan) */
    protected function validateCheckpoint6To7(): void
    {
        if (empty($this->data['contract_signed'])) {
            $this->errors[] = 'Kontrak harus ditandatangani sebelum melanjutkan ke Pembayaran DP';
        }
    }

    /** 7 → 8: Pembayaran DP → Pengiriman Material (opsional: cek DP sudah paid) */
    protected function validateCheckpoint7To8(): void
    {
        // Kalau modul PaymentSchedule sudah jalan, validasi bisa diaktifkan:
        // $payment = $this->procurement->paymentSchedules()
        //     ->where('payment_type', 'dp')
        //     ->where('status', 'paid')
        //     ->first();
        //
        // if (!$payment) {
        //     $this->errors[] = 'Pembayaran DP belum dikonfirmasi';
        // }
    }

    /** 8 → 9: Pengiriman Material → Kedatangan Material */
    protected function validateCheckpoint8To9(): void
    {
        if (empty($this->data['delivery_note'])) {
            // Bisa diganti sesuai field yang kamu pakai di tabel pengiriman material
            $this->errors[] = 'Data pengiriman material belum lengkap (delivery note kosong)';
        }
    }

    /** 9 → 10: Kedatangan Material → Verifikasi Dokumen (QA inspeksi) */
    protected function validateCheckpoint9To10(): void
    {
        if (empty($this->data['inspection_status'])) {
            $this->errors[] = 'Status inspeksi material wajib diisi sebelum verifikasi dokumen';
        }
    }

    /** 10 → 11: Verifikasi Dokumen → Pembayaran */
    protected function validateCheckpoint10To11(): void
    {
        if (empty($this->data['verification_notes'])) {
            $this->errors[] = 'Catatan verifikasi dokumen wajib diisi';
        }
    }

    /** 11 → COMPLETION: Pembayaran final oleh Treasury */
    protected function validateCheckpoint11ToCompletion(): void
    {
        if (!Auth::user() || Auth::user()->roles !== 'treasury') {
            $this->errors[] = 'Hanya Treasury yang dapat memproses pembayaran final';
        }

        if (empty($this->data['payment_date'])) {
            $this->errors[] = 'Tanggal pembayaran final wajib diisi';
        }

        $payment = $this->procurement->paymentSchedules()
            ->where('payment_type', 'final')
            ->where('status', 'paid')
            ->first();

        if (!$payment) {
            $this->errors[] = 'Pembayaran final belum dikonfirmasi di sistem';
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
                'status'     => 'completed',
                'note'       => $this->data['notes'] ?? $checkpoint->point_name . ' selesai',
                'user_id'    => Auth::id(),
                'start_date' => $progress?->start_date ?? now(),
                'end_date'   => now(),
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
                // setelah Permintaan Pengadaan selesai
                break;

            case 6:
                // setelah Pengesahan Kontrak selesai
                break;

            case 7:
                // setelah Pembayaran DP selesai
                break;

            case 10:
                // setelah Verifikasi Dokumen selesai
                break;

            case 11:
                // setelah Pembayaran final selesai
                break;
        }
    }

    /**
     * Contoh aksi tambahan otomatis setelah verifikasi dokumen (jika mau gunakan).
     */
    protected function actionAfterCheckpoint10(): void
    {
        $existingPayment = $this->procurement->paymentSchedules()
            ->where('payment_type', 'final')
            ->first();

        if (!$existingPayment) {
            $amount = $this->procurement->requestProcurements
                ->flatMap(fn ($rp) => $rp->items)
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
            case 1: // baru selesai Permintaan Pengadaan
                $this->notifyDivision(7, 'Evatek dimulai', 'Procurement siap evaluasi teknis');
                break;

            case 6: // selesai Pengesahan Kontrak → info ke Accounting soal DP
                $this->notifyDivision(3, 'DP Payment Ready', 'Pembayaran DP siap diproses');
                break;

            case 10: // selesai Verifikasi Dokumen → info ke Treasury
                $this->notifyDivision(4, 'Verifikasi Dokumen Selesai', 'Procurement siap diproses pembayaran final');
                break;

            case 11: // selesai Pembayaran final
                $this->notifyCheckpoint11Complete();
                break;
        }
    }

    protected function notifyCheckpoint11Complete(): void
    {
        // Info ke Accounting bahwa pembayaran final sudah dilakukan
        $this->notifyDivision(3, 'Pembayaran Final', 'Pembayaran final untuk procurement telah diproses');

        // Broadcast ke beberapa divisi terkait bahwa procurement selesai
        $divisions = [2, 3, 4, 5, 6, 7];
        foreach ($divisions as $divisionId) {
            $this->notifyDivision($divisionId, 'Procurement Selesai', 'Procurement telah selesai diproses');
        }
    }

    protected function notifyDivision(int $divisionId, string $title, string $message): void
    {
        $users = \App\Models\User::whereHas('division', fn ($q) =>
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
        if (!$to)   return 'Procurement selesai diproses';

        return "Berhasil berpindah dari '{$from->point_name}' ke '{$to->point_name}'";
    }

    /**
     * Ambil checkpoint yang sedang aktif (in_progress) atau terakhir yang completed.
     */
    public function getCurrentCheckpoint(): ?Checkpoint
    {
        // 1. Cari yang in_progress
        $inProgress = $this->procurement->procurementProgress()
            ->where('status', 'in_progress')
            ->with('checkpoint')
            ->first();

        if ($inProgress) {
            return $inProgress->checkpoint;
        }

        // 2. Kalau tidak ada, ambil yang terakhir completed
        $completed = $this->procurement->procurementProgress()
            ->where('status', 'completed')
            ->with('checkpoint')
            ->orderBy('checkpoint_id', 'desc')
            ->first();

        return $completed?->checkpoint;
    }

    /**
     * Checkpoint berikutnya dari posisi sekarang.
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
     * Persentase progress berdasarkan jumlah checkpoint completed.
     */
    public function getProgressPercentage(): int
    {
        $total = Checkpoint::count();
        if ($total === 0) return 0;

        $completed = $this->procurement->procurementProgress()
            ->where('status', 'completed')
            ->count();

        return round(($completed / $total) * 100);
    }

    /**
     * Selesaikan checkpoint aktif sekarang dan lanjut ke checkpoint berikutnya.
     */
    public function completeCurrentAndMoveNext(?string $note = null, array $extraData = []): array
    {
        $current = $this->getCurrentCheckpoint();

        if (!$current) {
            return [
                'success' => false,
                'message' => 'Tidak ada checkpoint aktif untuk dipindahkan.',
            ];
        }

        $data = array_merge(['notes' => $note], $extraData);

        return $this->transition($current->point_sequence, $data);
    }
}
