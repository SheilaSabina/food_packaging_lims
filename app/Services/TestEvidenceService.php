<?php

namespace App\Services;

use App\Models\TestEvidence;
use App\Models\TestResult;
use App\Models\TestSession;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class TestEvidenceService
{
    /**
     * Upload dan simpan bukti pengujian
     */
    public function uploadEvidence(
        UploadedFile $file,
        TestSession $session,
        ?TestResult $result = null,
        string $evidenceType = 'Test Photo',
        string $description = '',
        ?string $uploadedBy = null
    ): array {
        // Validasi file type
        if (!TestEvidence::isValidFileType($file->getMimeType())) {
            return [
                'success' => false,
                'message' => 'Tipe file tidak diizinkan. Gunakan JPG, PNG, GIF, atau PDF.',
            ];
        }

        // Validasi file size
        if (!TestEvidence::isValidFileSize($file->getSize())) {
            return [
                'success' => false,
                'message' => 'Ukuran file terlalu besar (maks 10MB).',
            ];
        }

        try {
            // Generate nama file unik
            $storagePath = "test-evidences/{$session->id}";
            $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();

            // Simpan file ke storage
            $filePath = Storage::disk('public')->putFileAs(
                $storagePath,
                $file,
                $fileName
            );

            // Ekstrak metadata jika diperlukan
            $metadata = [
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
            ];

            // Simpan record ke database
            $evidence = TestEvidence::create([
                'test_result_id' => $result?->id,
                'test_session_id' => $session->id,
                'file_name' => $fileName,
                'file_path' => $filePath,
                'file_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
                'evidence_type' => $evidenceType,
                'description' => $description,
                'uploaded_by' => $uploadedBy,
                'uploaded_at' => now(),
                'metadata' => $metadata,
            ]);

            return [
                'success' => true,
                'data' => $evidence,
                'message' => 'Bukti pengujian berhasil diunggah',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => "Gagal mengunggah file: {$e->getMessage()}",
            ];
        }
    }

    /**
     * Dapatkan semua bukti untuk sesi pengujian
     */
    public function getSessionEvidences(TestSession $session)
    {
        return TestEvidence::where('test_session_id', $session->id)
            ->with('result:id,test_parameter_id')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Dapatkan bukti untuk hasil uji tertentu
     */
    public function getResultEvidences(TestResult $result)
    {
        return TestEvidence::where('test_result_id', $result->id)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Hapus bukti (soft-delete atau hard-delete sesuai kebutuhan)
     */
    public function deleteEvidence(TestEvidence $evidence): bool
    {
        try {
            // Hapus file dari storage
            if ($evidence->file_path && Storage::disk('public')->exists($evidence->file_path)) {
                Storage::disk('public')->delete($evidence->file_path);
            }

            // Hapus record dari database
            $evidence->delete();

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Verifikasi bukti oleh supervisor
     */
    public function verifyEvidence(TestEvidence $evidence): bool
    {
        return $evidence->update(['is_verified' => true]);
    }
}
