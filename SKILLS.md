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

| Kolom | Tipe    | Keterangan              |
| ----- | ------- | ----------------------- |
| id    | INTEGER | Primary Key             |
| name  | TEXT    | Nama user               |
| role  | TEXT    | technician / supervisor |

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

| Kolom         | Tipe    | Keterangan     |
| ------------- | ------- | -------------- |
| id            | INTEGER | Primary Key    |
| name          | TEXT    | Nama parameter |
| unit          | TEXT    | Satuan         |
| threshold_max | REAL    | Ambang batas   |

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

### Tabel: test_evidences

| Kolom         | Tipe    | Keterangan  |
| ------------- | ------- | ----------- |
| id            | INTEGER | Primary Key |
| file_path     | TEXT    | Lokasi file |
| evidence_type | TEXT    | Jenis bukti |

---

## Status Flow

| Status                 | Aktor      | Deskripsi            | Konsekuensi             |
| ---------------------- | ---------- | -------------------- | ----------------------- |
| Draft                  | Teknisi    | Sesi dibuat          | Data kosong disiapkan   |
| In-Progress            | Teknisi    | Input hasil uji      | Validasi berjalan       |
| Ready for Verification | Teknisi    | Submit ke supervisor | Data dikunci (readonly) |
| Verified               | Supervisor | Disetujui            | Data terkunci permanen  |
| Rejected               | Supervisor | Ditolak              | Form dibuka kembali     |

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

| User Story | Implementasi                    |
| ---------- | ------------------------------- |
| US 2.1     | Create TestSession (Draft)      |
| US 2.2     | Validasi alat                   |
| US 2.3     | Status → Ready for Verification |
| US 2.4     | Input measured_value            |
| US 2.5     | Threshold validation (Service)  |
| US 2.6     | Verifikasi supervisor           |

---

## Related User Stories

* US 2.1: Sample reception
* US 2.2: Measurement recording
* US 2.3: Workflow handoff
* US 2.4: Input hasil uji
* US 2.5: Threshold comparison
* US 2.6: Supervisor verification