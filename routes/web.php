<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TestResultController;

// Rute Default
Route::get('/', [TestResultController::class, 'dashboard'])->name('technician.dashboard');

// Supervisor Dashboard
Route::get('/supervisor/dashboard', [TestResultController::class, 'supervisorDashboard'])->name('supervisor.dashboard');

// User Journey 2: Pelaksanaan Pengujian Laboratorium
// Halaman terpisah untuk dashboard, input numerik, dan review mandiri
Route::prefix('test-sessions/{session}')->group(function () {
    
    // --- AKTOR: TEKNISI ---
    // Halaman Form Input (US-2.4)
    Route::get('/input', [TestResultController::class, 'showInputForm'])->name('test.input');

    // Halaman Review Hasil Pra-Verifikasi
    Route::get('/review', [TestResultController::class, 'review'])->name('test.review');
    
    // Proses Simpan Data Numerik & Otomasi PASS/FAIL (US-2.4 & US-2.5)
    Route::post('/input-numeric', [TestResultController::class, 'inputNumeric'])->name('test.input.numeric');

    // --- AKTOR: SUPERVISOR ---
    // Endpoint ringkasan status untuk AJAX / supervisor
    Route::get('/verify', [TestResultController::class, 'getStatusSummary'])->name('test.verify.dashboard');
    
    // Proses Approve/Reject & Locking Data (US-2.6)
    Route::post('/approve-reject', [TestResultController::class, 'verifySessions'])->name('test.approve.reject');

    // Export Hasil (Persiapan Journey 3)
    Route::get('/export', [TestResultController::class, 'export'])->name('test.export');

    Route::get('/summary', [TestResultController::class, 'getStatusSummary']);

    #ENDPOINT khusus
    Route::get('/data', [TestResultController::class, 'getSessionData']);

});

// Rute untuk Upload Bukti (Global Result ID)
Route::post('/test-results/{result}/upload-evidence', [TestResultController::class, 'uploadEvidence'])->name('test.evidence.upload');