<?php

namespace App\Http\Controllers;

use App\Models\PaymentSchedule;
use App\Models\Project;
use App\Models\Contract;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Helpers\ActivityLogger;


class PaymentController extends Controller
{
    /**
     * Display payment schedules
     */
    public function index(Request $request)
    {
        $query = PaymentSchedule::with(['project', 'contract']);
        
        // Filter berdasarkan pencarian
        if ($request->has('q') && $request->q) {
            $search = $request->q;
            $query->whereHas('project', function($q) use ($search) {
                $q->where('code_project', 'like', "%{$search}%")
                  ->orWhere('name_project', 'like', "%{$search}%");
            });
        }
        
        // Filter berdasarkan status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }
        
        // Filter berdasarkan tipe pembayaran
        if ($request->has('type') && $request->type) {
            $query->where('payment_type', $request->type);
        }
        
        // Pagination
        $payments = $query->orderBy('due_date', 'asc')->paginate(15);
        
        // Hitung statistik untuk cards
        // Card 1: Perlu Verifikasi Accounting (pending) - semua role
        $pendingAccountingCount = PaymentSchedule::where('status', 'pending')->count();
        
        // Card 2: Perlu Dibayar Treasury (verified_accounting) - hidden untuk accounting
        $needPaymentCount = PaymentSchedule::where('status', 'verified_accounting')->count();
        
        // Card 3: Sudah Diverifikasi Accounting - hidden untuk treasury
        $verifiedAccountingCount = PaymentSchedule::whereIn('status', ['verified_accounting', 'verified_treasury'])->count();
        
        // Card 4: Sudah Terbayar (paid) - semua role
        $paidCount = PaymentSchedule::where('status', 'paid')->count();
        
        ActivityLogger::log(
            module: 'Payment',
            action: 'view_payment_list',
            targetId: null,
            details: [
                'user_id' => Auth::id(),
                'filters' => [
                    'search' => $request->q ?? '',
                    'status' => $request->status ?? '',
                    'type' => $request->type ?? '',
                ],
            ]
        );

        return view('payments.index', compact(
            'payments',
            'pendingAccountingCount',
            'needPaymentCount',
            'verifiedAccountingCount',
            'paidCount'
        ));
    }

    /**
     * Create payment schedule
     */
    public function create($projectId)
    {
        $project = Project::with(['contracts'])->findOrFail($projectId);

        ActivityLogger::log(
            module: 'Payment',
            action: 'open_payment_create_form',
            targetId: $projectId,
            details: ['user_id' => Auth::id()]
        );

        return view('payments.create', compact('project'));
    }

    /**
     * Store payment schedule
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,project_id',
            'contract_id' => 'nullable|exists:contracts,contract_id',
            'payment_type' => 'required|in:dp,termin,final,lc,tt,sekbun',
            'amount' => 'required|numeric|min:0',
            'percentage' => 'nullable|numeric|min:0|max:100',
            'due_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        $payment = PaymentSchedule::create($validated);

        ActivityLogger::log(
            module: 'Payment',
            action: 'open_payment_create_form',
            targetId: $validated['project_id'],
            details: ['user_id' => Auth::id()]
        );

        return redirect()->route('payments.show', $payment->payment_schedule_id)
            ->with('success', 'Jadwal pembayaran berhasil dibuat');
    }

    /**
     * Show payment details
     */
    public function show($id)
    {
        $payment = PaymentSchedule::with([
            'project',
            'contract.vendor',
            'accountingVerifier',
            'treasuryVerifier'
        ])->findOrFail($id);

        ActivityLogger::log(
            module: 'Payment',
            action: 'view_payment_detail',
            targetId: $payment->payment_schedule_id,
            details: [
                'user_id' => Auth::id(),
                'project_id' => $payment->project_id,
            ]
        );

        return view('payments.show', compact('payment'));
    }

    /**
     * Accounting verification
     */
    public function accountingVerification(Request $request, $id)
    {
        $payment = PaymentSchedule::findOrFail($id);

        $validated = $request->validate([
            'action' => 'required|in:approve,reject',
            'notes' => 'nullable|string',
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        try {
            DB::beginTransaction();

            $attachmentPath = null;
            if ($request->hasFile('attachment')) {
                $attachmentPath = $request->file('attachment')->store('payment_documents', 'public');
            }

            $updateData = [
                'verified_by_accounting' => Auth::id(),
                'verified_at_accounting' => now(),
                'notes' => $validated['notes'] ?? 'Verified by accounting',
            ];

            if ($attachmentPath) {
                $updateData['attachment_path'] = $attachmentPath;
            }

            if ($validated['action'] === 'approve') {
                $updateData['status'] = 'verified_accounting';

                // Update payment
                $payment->update($updateData);

                // Notify Treasury for LC/TT opening
                $this->notifyTreasury($payment, 'Dokumen pembayaran telah diverifikasi Accounting');
            } else {
                $updateData['status'] = 'rejected';
                $payment->update($updateData);
            }

            DB::commit();

            ActivityLogger::log(
                module: 'Payment',
                action: 'accounting_approved_payment',
                targetId: $payment->payment_schedule_id,
                details: [
                    'user_id' => Auth::id(),
                    'notes' => $validated['notes'] ?? '',
                    'project_id' => $payment->project_id,
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Verifikasi accounting berhasil'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            \Log::error('Accounting verification failed: ' . $e->getMessage());
            
            ActivityLogger::log(
                module: 'Payment',
                action: 'accounting_rejected_payment',
                targetId: $payment->payment_schedule_id,
                details: [
                    'user_id' => Auth::id(),
                    'notes' => $validated['notes'] ?? '',
                    'project_id' => $payment->project_id,
                ]
            );

            return response()->json([
                'success' => false,
                'message' => 'Gagal memverifikasi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Treasury verification and payment
     */
    public function treasuryVerification(Request $request, $id)
    {
        $payment = PaymentSchedule::findOrFail($id);

        // Check if accounting has verified
        if ($payment->status !== 'verified_accounting') {
            return response()->json([
                'success' => false,
                'message' => 'Payment belum diverifikasi oleh Accounting'
            ], 400);
        }

        $validated = $request->validate([
            'action' => 'required|in:approve,reject',
            'payment_date' => 'required_if:action,approve|date',
            'notes' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $updateData = [
                'verified_by_treasury' => Auth::id(),
                'verified_at_treasury' => now(),
                'notes' => $validated['notes'] ?? 'Payment processed by treasury',
            ];

            if ($validated['action'] === 'approve') {
                $updateData['status'] = 'paid';
                $updateData['payment_date'] = $validated['payment_date'];

                // Update payment
                $payment->update($updateData);

                // Update project status if final payment
                if ($payment->payment_type === 'final') {
                    $payment->project->update(['status_project' => 'completed']);
                }
            } else {
                $updateData['status'] = 'cancelled';
                $payment->update($updateData);
            }

            DB::commit();

            ActivityLogger::log(
                module: 'Payment',
                action: 'treasury_approved_payment',
                targetId: $payment->payment_schedule_id,
                details: [
                    'user_id' => Auth::id(),
                    'payment_date' => $validated['payment_date'],
                    'project_id' => $payment->project_id,
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Verifikasi treasury berhasil'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            \Log::error('Treasury verification failed: ' . $e->getMessage());
            
            ActivityLogger::log(
                module: 'Payment',
                action: 'treasury_rejected_payment',
                targetId: $payment->payment_schedule_id,
                details: [
                    'user_id' => Auth::id(),
                    'project_id' => $payment->project_id,
                ]
            );

            return response()->json([
                'success' => false,
                'message' => 'Gagal memverifikasi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Open LC/TT (Letter of Credit / Telegraphic Transfer)
     */
    public function openLcTt(Request $request, $projectId)
    {
        $validated = $request->validate([
            'type' => 'required|in:lc,tt',
            'amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $payment = PaymentSchedule::create([
            'project_id' => $projectId,
            'payment_type' => $validated['type'],
            'amount' => $validated['amount'],
            'status' => 'verified_treasury',
            'verified_by_treasury' => Auth::id(),
            'verified_at_treasury' => now(),
            'notes' => $validated['notes'],
            'due_date' => now(),
        ]);

        ActivityLogger::log(
            module: 'Payment',
            action: 'open_lc_tt',
            targetId: $payment->payment_schedule_id,
            details: [
                'user_id' => Auth::id(),
                'type' => $validated['type'],
                'amount' => $validated['amount'],
                'project_id' => $projectId,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'LC/TT berhasil dibuka',
            'payment' => $payment
        ]);
    }

    /**
     * Open Sekbun (for import items)
     */
    public function openSekbun(Request $request, $projectId)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $payment = PaymentSchedule::create([
            'project_id' => $projectId,
            'payment_type' => 'sekbun',
            'amount' => $validated['amount'],
            'status' => 'verified_treasury',
            'verified_by_treasury' => Auth::id(),
            'verified_at_treasury' => now(),
            'notes' => $validated['notes'],
            'due_date' => now(),
        ]);

        ActivityLogger::log(
            module: 'Payment',
            action: 'open_sekbun',
            targetId: $payment->payment_schedule_id,
            details: [
                'user_id' => Auth::id(),
                'amount' => $validated['amount'],
                'project_id' => $projectId,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Sekbun berhasil dibuka',
            'payment' => $payment
        ]);
    }

    /**
     * Get payment statistics
     */
    public function statistics()
    {
        $stats = [
            'pending' => PaymentSchedule::where('status', 'pending')->sum('amount'),
            'verified_accounting' => PaymentSchedule::where('status', 'verified_accounting')->sum('amount'),
            'verified_treasury' => PaymentSchedule::where('status', 'verified_treasury')->sum('amount'),
            'paid' => PaymentSchedule::where('status', 'paid')->sum('amount'),
            'total' => PaymentSchedule::sum('amount'),
        ];

        return response()->json($stats);
    }

    /**
     * Notify Treasury team
     */
    private function notifyTreasury($payment, $message)
    {
        // Load project relation if not loaded
        if (!$payment->relationLoaded('project')) {
            $payment->load('project');
        }

        // Get treasury users using user_id as primary key
        $treasuryUsers = \App\Models\User::where('roles', 'treasury')->get();

        if ($treasuryUsers->isEmpty()) {
            \Log::warning('No treasury users found for notification');
            return;
        }

        foreach ($treasuryUsers as $user) {
            try {
                Notification::create([
                    'user_id' => $user->user_id, // Use user_id consistently
                    'sender_id' => Auth::id(),
                    'type' => 'payment_verification',
                    'title' => 'Verifikasi Pembayaran',
                    'message' => $message . ' - Proyek: ' . ($payment->project->name_project ?? 'N/A'),
                    'reference_type' => 'App\Models\PaymentSchedule',
                    'reference_id' => $payment->payment_schedule_id,
                ]);
            } catch (\Exception $e) {
                // Log error but don't stop the main process
                \Log::error('Failed to create notification for user ' . $user->user_id . ': ' . $e->getMessage());
            }
        }
    }
}