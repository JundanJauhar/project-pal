<?php

namespace App\Http\Controllers;

use App\Models\PaymentSchedule;
use App\Models\Project;
use App\Models\Contract;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    /**
     * Display payment schedules
     */
    public function index()
    {
        $payments = PaymentSchedule::with(['project', 'contract'])
            ->orderBy('due_date', 'desc')
            ->paginate(20);

        return view('payments.index', compact('payments'));
    }

    /**
     * Create payment schedule
     */
    public function create($projectId)
    {
        $project = Project::with(['contracts'])->findOrFail($projectId);
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

        DB::transaction(function () use ($payment, $validated, $request) {
            $attachmentPath = null;
            if ($request->hasFile('attachment')) {
                $attachmentPath = $request->file('attachment')->store('payment_documents', 'public');
            }

            $updateData = [
                'verified_by_accounting' => Auth::id(),
                'verified_at_accounting' => now(),
                'notes' => $validated['notes'],
            ];

            if ($attachmentPath) {
                $updateData['attachment_path'] = $attachmentPath;
            }

            if ($validated['action'] === 'approve') {
                $updateData['status'] = 'verified_accounting';

                // Notify Treasury for LC/TT opening
                $this->notifyTreasury($payment, 'Dokumen pembayaran telah diverifikasi Accounting');
            } else {
                $updateData['status'] = 'rejected';
            }

            $payment->update($updateData);
        });

        return response()->json([
            'success' => true,
            'message' => 'Verifikasi accounting berhasil'
        ]);
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

        DB::transaction(function () use ($payment, $validated) {
            $updateData = [
                'verified_by_treasury' => Auth::id(),
                'verified_at_treasury' => now(),
                'notes' => $validated['notes'],
            ];

            if ($validated['action'] === 'approve') {
                $updateData['status'] = 'paid';
                $updateData['payment_date'] = $validated['payment_date'];

                // Update project status if final payment
                if ($payment->payment_type === 'final') {
                    $payment->project->update(['status_project' => 'selesai']);
                }
            } else {
                $updateData['status'] = 'rejected';
            }

            $payment->update($updateData);
        });

        return response()->json([
            'success' => true,
            'message' => 'Verifikasi treasury berhasil'
        ]);
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
        $treasuryUsers = \App\Models\User::where('roles', 'treasury')->get();

        foreach ($treasuryUsers as $user) {
            Notification::create([
                'user_id' => $user->id,
                'sender_id' => Auth::id(),
                'type' => 'payment_verification',
                'title' => 'Verifikasi Pembayaran',
                'message' => $message . ' - Proyek: ' . $payment->project->name_project,
                'reference_type' => 'App\Models\PaymentSchedule',
                'reference_id' => $payment->payment_schedule_id,
            ]);
        }
    }
}
