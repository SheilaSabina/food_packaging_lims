<?php

use App\Http\Controllers\TestResultController;
use Illuminate\Support\Facades\Route;

// Group routes untuk Test Results dengan middleware authentication
Route::middleware(['auth:sanctum'])->group(function () {
    // Routes untuk input data numerik (US-2.4 & US-2.5)
    Route::prefix('test-sessions/{session}')->group(function () {
        // Tampilkan form input
        Route::get('input-form', [TestResultController::class, 'showInputForm']);

        // Input data numerik
        Route::post('input-numeric', [TestResultController::class, 'inputNumeric']);

        // Verifikasi hasil uji oleh supervisor
        Route::post('verify', [TestResultController::class, 'verifySessions']);

        // Dapatkan status summary
        Route::get('status-summary', [TestResultController::class, 'getStatusSummary']);

        // Export hasil uji
        Route::get('export', [TestResultController::class, 'export']);
    });

    // Routes untuk hasil uji individual
    Route::prefix('test-results/{result}')->group(function () {
        // Dapatkan detail
        Route::get('/', [TestResultController::class, 'show']);

        // Upload bukti/foto
        Route::post('upload-evidence', [TestResultController::class, 'uploadEvidence']);
    });
});
