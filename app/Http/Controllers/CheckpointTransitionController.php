<?php

namespace App\Http\Controllers;

use App\Models\Procurement;
use App\Models\Checkpoint;
use App\Services\CheckpointTransitionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class CheckpointTransitionController extends Controller
{
    /**
     * Perform checkpoint transition
     * 
     * @param Request $request
     * @param int $procurementId
     * @return \Illuminate\Http\JsonResponse
     * 
     * Request body:
     * {
     *     "from_checkpoint": 1,
     *     "notes": "...",
     *     "additional_data": {...}
     * }
     */
    public function transition(Request $request, int $procurementId)
    {
        $procurement = Procurement::with(['requestProcurements.items'])->findOrFail($procurementId);

        $validated = $request->validate([
            'from_checkpoint' => 'required|integer|min:1|max:14',
            'notes' => 'nullable|string|max:1000',
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            // Additional data fields per checkpoint
            'evaluation_notes' => 'nullable|string',
            'negotiation_result' => 'nullable|string',
            'oc_document' => 'nullable|string',
            'contract_signed' => 'nullable|boolean',
            'delivery_note' => 'nullable|string',
            'payment_reference' => 'nullable|string',
            'target_arrival_date' => 'nullable|date',
            'goods_receipt_number' => 'nullable|string',
            'documents_received' => 'nullable|boolean',
            'inspection_status' => 'nullable|in:ok,ncr',
            'ncr_notes' => 'nullable|string',
            'berita_acara_number' => 'nullable|string',
            'verification_notes' => 'nullable|string',
            'payment_date' => 'nullable|date',
        ]);

        // Handle file upload
        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('checkpoint_documents', 'public');
            $validated['attachment_path'] = $attachmentPath;
        }

        // Create service instance
        $service = new CheckpointTransitionService($procurement);

        // Perform transition
        $result = $service->transition($validated['from_checkpoint'], $validated);

        // Return based on request type
        if ($request->wantsJson()) {
            return response()->json($result);
        }

        if ($result['success']) {
            return redirect()->route('procurements.show', $procurementId)
                ->with('success', $result['message']);
        } else {
            return back()
                ->withInput()
                ->with('error', $result['message']);
        }
    }

    /**
     * Get checkpoint transition form (dynamic based on checkpoint)
     * 
     * @param int $procurementId
     * @return \Illuminate\View\View
     */
    public function getTransitionForm(int $procurementId)
    {
        $procurement = Procurement::with(['procurementProgress.checkpoint'])->findOrFail($procurementId);

        $currentCheckpoint = $service = (new CheckpointTransitionService($procurement))->getCurrentCheckpoint();
        $nextCheckpoint = (new CheckpointTransitionService($procurement))->getNextCheckpoint();

        // Get form template for next checkpoint
        $formTemplate = $this->getCheckpointFormTemplate($nextCheckpoint?->point_sequence ?? 15);

        return view('checkpoints.transition-form', compact(
            'procurement',
            'currentCheckpoint',
            'nextCheckpoint',
            'formTemplate'
        ));
    }

    /**
     * Get checkpoint timeline
     * 
     * @param int $procurementId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTimeline(int $procurementId)
    {
        $procurement = Procurement::with(['procurementProgress.checkpoint'])->findOrFail($procurementId);

        $allCheckpoints = Checkpoint::orderBy('point_sequence')->get();
        $service = new CheckpointTransitionService($procurement);

        $timeline = $allCheckpoints->map(function ($checkpoint) use ($procurement, $service) {
            $progress = $procurement->procurementProgress
                ->where('checkpoint_id', $checkpoint->point_id)
                ->first();

            return [
                'sequence' => $checkpoint->point_sequence,
                'name' => $checkpoint->point_name,
                'responsible_division' => $checkpoint->responsible_division,
                'status' => $progress?->status ?? 'not_started',
                'is_final' => $checkpoint->is_final,
                'completed_at' => $progress?->end_date?->format('Y-m-d H:i'),
                'user_notes' => $progress?->note,
                'can_transition' => $this->canTransition($checkpoint, $procurement),
            ];
        });

        $currentCheckpoint = $service->getCurrentCheckpoint();
        $progressPercentage = $service->getProgressPercentage();

        return response()->json([
            'timeline' => $timeline,
            'current_checkpoint' => $currentCheckpoint ? [
                'sequence' => $currentCheckpoint->point_sequence,
                'name' => $currentCheckpoint->point_name,
            ] : null,
            'progress_percentage' => $progressPercentage,
            'status' => $procurement->status_procurement,
        ]);
    }

    /**
     * Get checkpoint details
     * 
     * @param int $procurementId
     * @param int $checkpointSequence
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCheckpointDetail(int $procurementId, int $checkpointSequence)
    {
        $procurement = Procurement::with(['procurementProgress.checkpoint'])->findOrFail($procurementId);

        $checkpoint = Checkpoint::where('point_sequence', $checkpointSequence)->firstOrFail();

        $progress = $procurement->procurementProgress()
            ->where('checkpoint_id', $checkpoint->point_id)
            ->first();

        return response()->json([
            'checkpoint' => [
                'sequence' => $checkpoint->point_sequence,
                'name' => $checkpoint->point_name,
                'responsible_division' => $checkpoint->responsible_division,
                'is_final' => $checkpoint->is_final,
                'description' => $checkpoint->description ?? '',
            ],
            'progress' => $progress ? [
                'status' => $progress->status,
                'note' => $progress->note,
                'completed_by' => $progress->user->name ?? 'Unknown',
                'started_at' => $progress->start_date?->format('Y-m-d H:i'),
                'completed_at' => $progress->end_date?->format('Y-m-d H:i'),
            ] : null,
        ]);
    }

    /**
     * Get form template for checkpoint
     */
    protected function getCheckpointFormTemplate(?int $checkpointSequence): array
    {
        $templates = [
            1 => ['title' => 'Penawaran Permintaan', 'fields' => []],
            2 => [
                'title' => 'Evatek',
                'fields' => [
                    ['name' => 'evaluation_notes', 'type' => 'textarea', 'label' => 'Catatan Evaluasi Teknis', 'required' => true],
                ]
            ],
            3 => [
                'title' => 'Negosiasi',
                'fields' => [
                    ['name' => 'negotiation_result', 'type' => 'textarea', 'label' => 'Hasil Negosiasi', 'required' => true],
                ]
            ],
            4 => [
                'title' => 'Usulan Pengadaan',
                'fields' => [
                    ['name' => 'oc_document', 'type' => 'text', 'label' => 'Nomor/Referensi Dokumen OC', 'required' => true],
                ]
            ],
            5 => [
                'title' => 'Pengesahan Kontrak',
                'fields' => [
                    ['name' => 'contract_signed', 'type' => 'checkbox', 'label' => 'Kontrak Sudah Ditandatangani', 'required' => true],
                ]
            ],
            6 => [
                'title' => 'Pengiriman Material',
                'fields' => [
                    ['name' => 'delivery_note', 'type' => 'text', 'label' => 'Nomor Nota Pengiriman', 'required' => true],
                ]
            ],
            7 => [
                'title' => 'Pembayaran DP',
                'fields' => [
                    ['name' => 'payment_reference', 'type' => 'text', 'label' => 'Referensi Pembayaran (No. Transfer/Kuitansi)', 'required' => true],
                ]
            ],
            8 => [
                'title' => 'Proses Importasi / Produksi',
                'fields' => [
                    ['name' => 'target_arrival_date', 'type' => 'date', 'label' => 'Target Tanggal Kedatangan', 'required' => true],
                ]
            ],
            9 => [
                'title' => 'Kedatangan Material',
                'fields' => [
                    ['name' => 'goods_receipt_number', 'type' => 'text', 'label' => 'Nomor Bukti Penerimaan Barang', 'required' => true],
                ]
            ],
            10 => [
                'title' => 'Serah Terima Dokumen',
                'fields' => [
                    ['name' => 'documents_received', 'type' => 'checkbox', 'label' => 'Dokumen Sudah Diterima', 'required' => true],
                ]
            ],
            11 => [
                'title' => 'Inspeksi Barang',
                'fields' => [
                    ['name' => 'inspection_status', 'type' => 'select', 'label' => 'Status Inspeksi', 'options' => ['ok' => 'OK', 'ncr' => 'NCR'], 'required' => true],
                    ['name' => 'ncr_notes', 'type' => 'textarea', 'label' => 'Catatan NCR (jika ada)', 'required' => false],
                ]
            ],
            12 => [
                'title' => 'Berita Acara / NCR',
                'fields' => [
                    ['name' => 'berita_acara_number', 'type' => 'text', 'label' => 'Nomor Berita Acara', 'required' => true],
                ]
            ],
            13 => [
                'title' => 'Verifikasi Dokumen (Accounting)',
                'fields' => [
                    ['name' => 'verification_notes', 'type' => 'textarea', 'label' => 'Catatan Verifikasi', 'required' => true],
                    ['name' => 'attachment', 'type' => 'file', 'label' => 'Lampiran Dokumen', 'required' => false, 'accept' => '.pdf,.jpg,.jpeg,.png'],
                ]
            ],
            14 => [
                'title' => 'Pembayaran (Treasury)',
                'fields' => [
                    ['name' => 'payment_date', 'type' => 'date', 'label' => 'Tanggal Pembayaran', 'required' => true],
                    ['name' => 'payment_reference', 'type' => 'text', 'label' => 'Referensi Pembayaran', 'required' => true],
                ]
            ],
            15 => ['title' => 'Procurement Selesai', 'fields' => []],
        ];

        return $templates[$checkpointSequence] ?? $templates[1];
    }

    /**
     * Check if user can transition from checkpoint
     */
    protected function canTransition(Checkpoint $checkpoint, Procurement $procurement): bool
    {
        // Check if checkpoint is completed
        $progress = $procurement->procurementProgress()
            ->where('checkpoint_id', $checkpoint->point_id)
            ->first();

        if (!$progress || $progress->status !== 'completed') {
            return false;
        }

        // Check user role matches responsible division
        // This is a simplified check - customize based on your auth system
        return true;
    }

    /**
     * Get checkpoint history
     */
    public function getHistory(int $procurementId)
    {
        $procurement = Procurement::with([
            'procurementProgress' => function($q) {
                $q->with(['checkpoint', 'user'])->orderBy('checkpoint_id');
            }
        ])->findOrFail($procurementId);

        $history = $procurement->procurementProgress->map(function ($progress) {
            return [
                'checkpoint' => $progress->checkpoint->point_name,
                'status' => $progress->status,
                'started_at' => $progress->start_date?->format('d/m/Y H:i'),
                'completed_at' => $progress->end_date?->format('d/m/Y H:i'),
                'duration' => $progress->end_date && $progress->start_date
                    ? $progress->end_date->diffInHours($progress->start_date) . ' jam'
                    : '-',
                'completed_by' => $progress->user->name ?? 'Unknown',
                'notes' => $progress->note,
            ];
        });

        return response()->json(['history' => $history]);
    }
}