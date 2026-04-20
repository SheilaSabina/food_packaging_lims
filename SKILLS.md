# User Journey 2: Laboratory Execution & Verification Skill

## Overview

Skill ini memandu implementasi workflow laboratorium pada sistem pengujian keamanan kemasan pangan. Fokus pada User Journey 2 (US 2.1 - US 2.6), mencakup proses input hasil uji oleh teknisi, validasi otomatis terhadap ambang batas (threshold), serta verifikasi oleh supervisor untuk menjaga integritas dan auditabilitas data.

Sistem dibangun menggunakan arsitektur MVC (Model-View-Controller) dengan tambahan Service Layer untuk mengisolasi logika bisnis, khususnya pada proses evaluasi PASS/FAIL.

---

## Tech Stack

| Layer     | Technology                    |
| --------- | ----------------------------- |
| Language  | PHP 8                         |
| Framework | Laravel 13                    |
| View      | Blade (Server Side Rendering) |
| Styling   | Tailwind CSS                  |
| Database  | SQLite                        |
| Testing   | PHPUnit                       |

---

## Installation

```bash
# 1. Create Laravel Project
composer create-project laravel/laravel sistem-pengujian-kemasan
cd sistem-pengujian-kemasan

# 2. Setup SQLite
touch database/database.sqlite

# 3. Migration & Seeder
php artisan migrate --seed

# 4. Run server
php artisan serve
```

---

## Project Structure

```
app/
├── Http/Controllers/
│   └── TestResultController.php
├── Models/
│   ├── TestSession.php
│   ├── TestResult.php
│   ├── TestParameter.php
│   └── User.php
└── Services/
    ├── TestResultComparisonService.php
    └── TestEvidenceService.php

resources/
└── views/
    ├── supervisor/
    │   └── dashboard.blade.php
    └── technician/
        ├── input.blade.php
        └── review.blade.php
```

---

## Database Schema (SQLite)

### Tabel: users

### Tabel: users

| Kolom        | Tipe Data | Keterangan                                                   |
| ------------ | --------- | ------------------------------------------------------------ |
| id [PK]      | INTEGER   | Auto Increment                                               |
| name         | TEXT      | NOT NULL                                                     |
| email [UQ]   | TEXT      | NOT NULL UNIQUE                                              |
| password     | TEXT      | NOT NULL                                                     |
| role         | TEXT      | Default: 'technician' (technician, supervisor, manager)      |

---

### Tabel: test_sessions

| Kolom            | Tipe      | Keterangan                                                         |
| ---------------- | --------- | ------------------------------------------------------------------ |
| id               | INTEGER   | Primary Key                                                        |
| order_id         | INTEGER   | Relasi order                                                       |
| technician_id    | INTEGER   | User teknisi                                                       |
| equipment_status | TEXT      | Status alat                                                        |
| status           | TEXT      | Draft / In-Progress / Ready for Verification / Verified / Rejected |
| verified_at      | TIMESTAMP | Waktu verifikasi                                                   |
| rejection_reason | TEXT      | Alasan penolakan                                                   |

---

### Tabel: test_parameters

| Kolom       | Tipe Data | Keterangan                                               |
| ----------- | --------- | -------------------------------------------------------- |
| id [PK]     | INTEGER   | Auto Increment                                           |
| name        | TEXT      | Nama parameter (BPA, Radon, pH, dll)                     |
| description | TEXT      | Penjelasan parameter uji                                 |
| unit        | TEXT      | Satuan (mg/kg, µg/L, Bq/L, dll)                          |
| data_type   | TEXT      | decimal, integer, float                                  |
| category    | TEXT      | Contoh: Kontaminasi Kimia, Radiologi                     |

---

### Tabel: test_results

| Kolom             | Tipe    | Keterangan        |
| ----------------- | ------- | ----------------- |
| id                | INTEGER | Primary Key       |
| test_session_id   | INTEGER | Relasi ke session |
| test_parameter_id | INTEGER | Relasi parameter  |
| measured_value    | REAL    | Nilai hasil ukur  |
| result_status     | TEXT    | PASS / FAIL       |

---

### Tabel: test_sessions (Lab Workflow)

| Kolom                 | Tipe Data | Keterangan                                                                 |
| --------------------- | --------- | -------------------------------------------------------------------------- |
| id [PK]               | INTEGER   | Auto Increment                                                             |
| order_id [FK]         | INTEGER   | Relasi ke orders.id                                                        |
| technician_id [FK]    | INTEGER   | User (teknisi) yang mengerjakan uji                                        |
| equipment_status      | TEXT      | Status alat (Siap, Kalibrasi Kedaluwarsa, Tidak Tersedia)                  |
| status                | TEXT      | Draft, In-Progress, Ready for Verification, Verified, Rejected             |
| rejection_reason      | TEXT      | Alasan penolakan oleh Supervisor (diisi jika status = Rejected)            |

### Tabel: test_thresholds

| Kolom                  | Tipe Data | Keterangan                                                                 |
| ---------------------- | --------- | -------------------------------------------------------------------------- |
| id [PK]                | INTEGER   | Auto Increment                                                             |
| test_parameter_id [FK] | INTEGER   | Relasi ke test_parameters.id                                               |
| standard_type          | TEXT      | Standar acuan (SNI, BPOM, FDA, EFSA, EU, WHO)                              |
| max_value              | NUMERIC   | Ambang batas maksimal (threshold_max)                                      |
| product_type           | TEXT      | Jenis produk (cair, padat, kemasan, kering)                                |

### Tabel: test_evidences

| Kolom         | Tipe    | Keterangan  |
| ------------- | ------- | ----------- |
| id            | INTEGER | Primary Key |
| file_path     | TEXT    | Lokasi file |
| evidence_type | TEXT    | Jenis bukti |

---

## Status Flow

## Status Flow

| Status                 | Aktor          | Deskripsi Aktivitas                                                                 | Konsekuensi & Logika Sistem                                                                                  |
| ---------------------- | -------------- | ----------------------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------ |
| Draft                  | Teknisi Lab    | Sesi pengujian baru dibuat berdasarkan antrian order.                              | Sistem menyiapkan baris kosong di tabel test_results.                                                        |
| In-Progress            | Teknisi Lab    | Teknisi memasukkan nilai ukur (measured_value) dan mengunggah bukti foto.          | Sistem mencatat test_started_at dan melakukan validasi tipe data secara real-time.                          |
| Ready for Verification | Teknisi Lab    | Teknisi menyelesaikan input dan mengirimkan hasil untuk ditinjau supervisor.       | Data Locking Aktif: Form input menjadi Read-only. Teknisi tidak bisa lagi mengubah data.                     |
| Verified               | Supervisor QC  | Supervisor menyetujui hasil uji setelah memeriksa bukti dan kecocokan ambang batas.| Sistem mencatat verified_at dan mengunci data secara permanen. Status akhir menjadi PASS atau FAIL.         |
| Rejected               | Supervisor QC  | Supervisor menolak hasil karena data tidak valid atau bukti kurang lengkap.        | Data Unlocked: Sistem membuka kembali form input dan mewajibkan pengisian rejection_reason.                 |

---

## Coding Conventions

### 1. Service Layer First

Semua logika perbandingan threshold HARUS berada di:
`TestResultComparisonService`

---

### 2. Data Locking

Jika status:

* Ready for Verification
* Verified

Maka:

* Form teknisi = READ ONLY
* Tidak boleh update data

---

### 3. Atomic Transaction

Gunakan:

```php
DB::transaction()
```

Untuk:

* Verifikasi
* Update status

---

### 4. Validation Rules

* measured_value → numeric
* rejection_reason → wajib jika Rejected

---

### 5. Role-Based Access

* technician → input data
* supervisor → verifikasi

---

## Controller Example (Supervisor Verification)

```php
public function verifySession(Request $request, TestSession $session)
{
    if ($session->status !== 'Ready for Verification') {
        return back()->with('error', 'Sesi belum siap diverifikasi');
    }

    DB::transaction(function () use ($request, $session) {

        if ($request->action === 'approve') {
            $session->update([
                'status' => 'Verified',
                'verified_at' => now(),
                'rejection_reason' => null
            ]);
        } else {
            $request->validate([
                'rejection_reason' => 'required|string'
            ]);

            $session->update([
                'status' => 'Rejected',
                'rejection_reason' => $request->rejection_reason
            ]);
        }
    });

    return redirect()->back()->with('success', 'Verifikasi berhasil');
}
```

---

## Service Layer Example

```php
public function compareMeasuredValue(float $value, float $threshold): array
{
    $valid = $value <= $threshold;

    return [
        'is_valid' => $valid,
        'status' => $valid ? 'PASS' : 'FAIL',
        'message' => $valid
            ? 'Nilai memenuhi ambang batas'
            : 'Nilai melebihi ambang batas'
    ];
}
```

---

## Implementation Notes

* Controller hanya handle alur
* Service handle logika bisnis
* Gunakan TEXT untuk status (SQLite compatibility)
* UI teknisi harus disable saat status locked
* rejection_reason wajib untuk transparansi

---

## Acceptance Criteria (US 2.1 - US 2.6)

| User Story | Deskripsi Aktivitas   | Implementasi Teknis                                                     |
| ---------- | -------------------- | ----------------------------------------------------------------------- |
| US 2.1     | Sample reception     | Inisialisasi TestSession dengan status Draft.                          |
| US 2.2     | Pengecekan Alat      | Validasi equipment_status sebelum input diizinkan.                     |
| US 2.3     | Workflow handoff     | Perubahan status ke Ready for Verification untuk antrean Supervisor.   |
| US 2.4     | Input Hasil Uji      | Penyimpanan measured_value ke tabel test_results.                      |
| US 2.5     | Otomasi PASS/FAIL    | Logika perbandingan pada TestResultComparisonService.                  |
| US 2.6     | Verifikasi QC        | Fungsi verifySession pada Controller dengan penguncian data.           |

---

## Related User Stories

* US 2.1: Sample reception
* US 2.2: Measurement recording
* US 2.3: Workflow handoff
* US 2.4: Input hasil uji
* US 2.5: Threshold comparison
* US 2.6: Supervisor verification