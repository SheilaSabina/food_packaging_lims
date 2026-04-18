<?php

namespace App\Http\Controllers;

use App\Models\TestParameter;
use App\Models\TestResult;
use App\Models\TestSession;
use App\Services\TestEvidenceService;
use App\Services\TestResultComparisonService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class TestResultController extends Controller
{
    private TestResultComparisonService $comparisonService;
    private TestEvidenceService $evidenceService;

    public function __construct(
        TestResultComparisonService $comparisonService,
        TestEvidenceService $evidenceService
    ) {
        $this->comparisonService = $comparisonService;
        $this->evidenceService = $evidenceService;
    }

    /**
     * Tampilkan form input data numerik (US-2.4)
     * GET /test-results/{sessionId}/input-form
     */
/**
     * Tampilkan form input data numerik (US-2.4)
     * GET /test-sessions/{session}/input
     */
    public function showInputForm(TestSession $session)
    {
        // 1. Cek status alat
        if (!$session->isEquipmentReady()) {
            // Jika diakses via browser, kita bisa redirect ke dashboard dengan pesan error
            return redirect()->back()->with('error', 'Alat tidak siap untuk pengujian.');
        }

        // 2. Ambil parameter yang belum diinput
        $parametersNotInput = TestParameter::where('is_active', true)
            ->whereNotIn('id', function ($query) use ($session) {
                $query->select('test_parameter_id')
                    ->from('test_results')
                    ->where('test_session_id', $session->id);
            })
            ->get();

        // 3. Ambil parameter yang sudah diinput
        $inputtedResults = $session->results()
            ->with(['parameter', 'appliedStandard'])
            ->get();

        // 4. KIRIM KE VIEW (Bukan JSON lagi)
        return view('technician.input', [
            'session' => $session->load('technician', 'supervisor', 'order'), // Load order juga untuk detail di UI
            'parameters_not_input' => $parametersNotInput,
            'inputted_results' => $inputtedResults,
        ]);
    }

    /**
     * Dashboard teknisi.
     * GET /
     */
    public function dashboard()
    {
        $sessions = TestSession::with(['order', 'technician'])
            ->whereIn('status', ['Draft', 'In-Progress'])
            ->orderByDesc('updated_at')
            ->get();

        return view('technician.dashboard', [
            'sessions' => $sessions,
        ]);
    }

    /**
     * Review semua parameter sebelum dikunci.
     * GET /test-sessions/{session}/review
     */
    public function review(TestSession $session)
    {
        $session->load(['order', 'technician', 'results.parameter', 'results.appliedStandard']);

        return view('technician.review', [
            'session' => $session,
            'summary' => $session->getStatusSummary(),
            'results' => $session->results->sortBy(fn($result) => $result->parameter->name),
        ]);
    }

    /**
     * Dashboard supervisor.
     * GET /supervisor/dashboard
     */
    public function supervisorDashboard()
    {
        $sessions = TestSession::with(['order', 'technician', 'results'])
            ->where('status', 'Ready for Verification')
            ->orderByDesc('updated_at')
            ->get();

        return view('supervisor.dashboard', [
            'sessions' => $sessions,
        ]);
    }

    /**
     * Input data numerik dengan perbandingan otomatis (US-2.4 & US-2.5)
     * POST /test-results/{sessionId}/input-numeric
     * 
     * Request body:
     * {
     *   "test_parameter_id": 1,
     *   "measured_value": 25.5,
     *   "notes": "Pengukuran dilakukan pada suhu ruangan"
     * }
     */
    public function inputNumeric(Request $request, TestSession $session)
    {
        // Validasi input
        $validated = $request->validate([
            'test_parameter_id' => 'required|integer|exists:test_parameters,id',
            'measured_value' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        // Ambil parameter
        $parameter = TestParameter::findOrFail($validated['test_parameter_id']);

        if (!$parameter->is_active) {
            return response()->json([
                'success' => false,
                'message' => "Parameter '{$parameter->name}' tidak aktif",
            ], 400);
        }

        // Jalankan perbandingan otomatis
        $result = $this->comparisonService->inputNumericData(
            $session,
            $parameter,
            (float)$validated['measured_value'],
            $validated['notes'] ?? null
        );

        if (!$result['success']) {
            return response()->json($result, 400);
        }

        // Refresh data untuk response
        $testResult = TestResult::with([
            'parameter',
            'appliedStandard',
            'evidences'
        ])->find($result['data']->id);

        return response()->json([
            'success' => true,
            'data' => $testResult,
            'messages' => $result['messages'],
            'status_summary' => $session->load('results')->getStatusSummary(),
        ]);
    }

    /**
     * Upload bukti foto untuk hasil uji (US-2.4 - tautkan foto bukti)
     * POST /test-results/{resultId}/upload-evidence
     * 
     * Form data:
     * - file: UploadedFile (required)
     * - evidence_type: string (optional, default: 'Test Photo')
     * - description: string (optional)
     */
    public function uploadEvidence(Request $request, TestResult $result)
    {
        $validated = $request->validate([
            'file' => 'required|file|mimes:jpeg,png,gif,pdf|max:10240',
            'evidence_type' => 'nullable|in:Test Photo,Equipment Status,Calibration Certificate,Other',
            'description' => 'nullable|string|max:500',
        ]);

        $uploadResult = $this->evidenceService->uploadEvidence(
        $request->file('file'),
        $result->session,
        $result,
        $validated['evidence_type'] ?? 'Test Photo',
        $validated['description'] ?? '',
        $request->user()?->name // Menggunakan $request daripada auth()
    );

        if (!$uploadResult['success']) {
            return response()->json($uploadResult, 400);
        }

        return response()->json([
            'success' => true,
            'data' => $uploadResult['data'],
            'message' => $uploadResult['message'],
        ]);
    }

    /**
     * Dapatkan detail hasil uji
     * GET /test-results/{resultId}
     */
    public function show(TestResult $result)
    {
        return response()->json([
            'success' => true,
            'data' => $result->load([
                'parameter',
                'session',
                'appliedStandard',
                'evidences',
            ]),
        ]);
    }

    /**
     * Verifikasi hasil uji oleh Supervisor (US-2.6)
     * POST /test-sessions/{sessionId}/verify
     * 
     * Request body:
     * {
     *   "action": "approve", // atau "reject"
     *   "rejection_reason": "Foto tidak jelas" // hanya jika action = reject
     * }
     */
    public function verifySessions(Request $request, TestSession $session)
    {
        $validated = $request->validate([
            'action' => 'required|in:approve,reject,submit',
            'rejection_reason' => 'required_if:action,reject|nullable|string|max:500',
        ]);

        // Cek apakah semua hasil sudah input
        if (!$session->areAllResultsInputted()) {
            return response()->json([
                'success' => false,
                'message' => 'Belum semua parameter diinput',
            ], 400);
        }

        if ($validated['action'] === 'submit') {
            if ($session->status !== 'In-Progress') {
                return response()->json([
                    'success' => false,
                    'message' => 'Sesi hanya dapat dikirim untuk verifikasi apabila sedang In-Progress.',
                ], 400);
            }

            $session->update(['status' => 'Ready for Verification']);

            return response()->json([
                'success' => true,
                'message' => 'Sesi berhasil dikirim untuk verifikasi. Data sekarang dikunci untuk pengeditan.',
                'status' => $session->status,
            ]);
        }

        if ($validated['action'] === 'approve') {
            // Approve dan lock data
            $verifyResult = $this->comparisonService->verifySessionResults($session);

            if ($verifyResult['success']) {
                $this->comparisonService->lockResults($session);
            }

            return response()->json([
                'success' => $verifyResult['success'],
                'message' => $verifyResult['message'],
                'summary' => $verifyResult['summary'] ?? null,
            ]);
        } else {
            // Reject dengan alasan
            $session->reject(
            $validated['rejection_reason'],
            $request->user() // Menggunakan $request daripada auth()
        );

            return response()->json([
                'success' => true,
                'message' => 'Hasil uji ditolak. Teknisi dapat memperbaiki data.',
                'rejection_reason' => $validated['rejection_reason'],
            ]);
        }
    }

    /**
     * Dapatkan ringkasan status pengujian
     * GET /test-sessions/{sessionId}/status-summary
     */
    public function getStatusSummary(TestSession $session)
    {
        $session = $session->load('results.parameter', 'results.appliedStandard');
        $summary = $session->getStatusSummary();
        
        $resultDetails = $session->results->map(function ($result) {
            return [
                'parameter_name' => $result->parameter->name,
                'measured_value' => $result->measured_value,
                'unit' => $result->unit,
                'status' => $result->result_status,
                'status_badge' => $result->getStatusBadge(),
                'description' => $result->getResultDescription(),
                'evidence_count' => $result->evidences()->count(),
            ];
        });

        return response()->json([
            'success' => true,
            'session_id' => $session->id,
            'session_status' => $session->status,
            'summary' => $summary,
            'results' => $resultDetails,
        ]);
    }

    /**
     * Export hasil uji ke PDF atau format lain
     * GET /test-results/{sessionId}/export
     */
    public function export(TestSession $session)
    {
        $session = $session->load([
            'results.parameter',
            'results.appliedStandard',
            'results.evidences',
            'technician',
            'supervisor',
        ]);

        $summary = $session->getStatusSummary();

        // Simple JSON export (bisa diperluas ke PDF)
        return response()->json([
            'success' => true,
            'export_data' => [
                'session_id' => $session->id,
                'technician' => $session->technician?->name,
                'supervisor' => $session->supervisor?->name,
                'test_started_at' => $session->test_started_at,
                'test_ended_at' => $session->test_ended_at,
                'summary' => $summary,
                'results' => $session->results->map(function ($result) {
                    return [
                        'parameter' => $result->parameter->name,
                        'measured_value' => $result->measured_value,
                        'unit' => $result->unit,
                        'status' => $result->result_status,
                        'description' => $result->getResultDescription(),
                        'applied_standard' => $result->appliedStandard?->reference_document,
                        'evidences' => $result->evidences->map(fn($e) => $e->file_name),
                    ];
                }),
            ],
        ]);
    }

    public function getSessionData(TestSession $session)
    {
        return response()->json([
            'success' => true,
            'session' => $session->only([
                'status',
                'equipment_is_calibrated',
                'equipment_calibration_expires_at'
            ])
        ]);
    }
}
