# Dokumentasi Implementasi: User Journey 2 - US-2.4 & US-2.5

## Ringkasan Implementasi

Sistem ini mengimplementasikan User Journey 2 dari PRD: **Pelaksanaan Pengujian Laboratorium Teknis**, dengan fokus pada:
- **US-2.4 (Input Data Numerik)**: Teknisi input nilai hasil pengukuran dengan validasi numerik
- **US-2.5 (Otomasi Threshold)**: Sistem otomatis membandingkan hasil dengan standar SNI/BPOM/FDA/EFSA

---

## Arsitektur Sistem

### Database Schema (5 Tabel Utama)

```
test_parameters
├── id, name, unit, data_type, decimal_places, category, is_active
└── Menyimpan daftar parameter yang bisa diuji (e.g., "Migrasi Total", "Kadar BPA")

test_standards
├── id, test_parameter_id, standard_type, min_value, max_value
├── reference_document, effective_date, expired_date, is_active
└── Menyimpan standar ambang batas dari berbagai regulasi (SNI, BPOM, FDA, EFSA)

test_sessions
├── id, order_id, technician_id, supervisor_id
├── equipment_id, equipment_status, equipment_is_calibrated
├── test_started_at, test_ended_at, status, verified_at
└── Sesi pengujian dengan tracking status alat dan workflow

test_results
├── id, test_session_id, test_parameter_id
├── measured_value (decimal:8), unit
├── applied_standard_id, standard_min_value, standard_max_value
├── result_status (PASS/FAIL/INCONCLUSIVE), deviation_percentage
├── data_status (Alat Siap, In-Progress, Draft, Verified)
├── calculation_details (JSON - audit trail)
└── Hasil pengujian dengan presisi tinggi + perbandingan otomatis

test_evidences
├── id, test_result_id, test_session_id
├── file_name, file_path, file_type, file_size
├── evidence_type, is_verified
└── Bukti foto/dokumen untuk setiap hasil uji
```

---

## Model Relationships

```php
TestParameter (1) ──── (∞) TestStandard
                    └── (∞) TestResult

TestStandard (1) ──── (∞) TestResult

TestSession (1) ──── (∞) TestResult
                 └── (∞) TestEvidence

TestResult (1) ──── (∞) TestEvidence
```

---

## Workflow User Journey 2

### Status Data: 4 State
1. **Alat Siap** - Alat telah dikalibrasi dan siap digunakan
2. **In-Progress** - Pengujian sedang berlangsung, data mulai diinput
3. **Draft** - Semua data numerik sudah diinput, menunggu verifikasi supervisor
4. **Verified** - Supervisor approve, data terkunci (tidak bisa diubah)

### Sequence Diagram

```
Teknisi                    Sistem                      Supervisor
   |                         |                            |
   |--[US-2.2] Cek Kalibrasi-|                            |
   |<--------Equipment Ready--|                            |
   |                         |                            |
   |--[US-2.4] Input Nilai---| [Validasi Numerik]       |
   |           Numerik       | [Ambil Standar Aktif]    |
   |                         | [Bandingkan Otomatis]    |
   |                         | [Set PASS/FAIL]          |
   |<--Data Tersimpan-Draft--|                            |
   |                         |                            |
   |--[Tautkan Foto Bukti]---| [Validasi File]          |
   |                         | [Simpan ke Storage]      |
   |<--Evidence Recorded-----|                            |
   |                         |                            |
   |--[US-2.6] Submit untuk---| [Check Kelengkapan]      |
   |            Approval      | [Lock Data]              |
   |                         |--------Notifikasi-------->|
   |                         |                   [Review]|
   |                         |<--[Approve/Reject]---------|
   |                         | [Mark Verified]          |
   |<--Verifikasi Selesai----|                            |
```

---

## API Endpoints

### 1. **Tampilkan Form Input** (GET)
```
GET /test-sessions/{sessionId}/input-form

Response:
{
  "success": true,
  "session": { ... },
  "parameters_not_input": [ ... ],
  "inputted_results": [ ... ]
}
```

### 2. **Input Data Numerik dengan Perbandingan Otomatis** (POST) ⭐ [US-2.4 & US-2.5]
```
POST /test-sessions/{sessionId}/input-numeric

Request:
{
  "test_parameter_id": 1,
  "measured_value": 25.5,
  "notes": "Pengukuran dilakukan pada suhu ruangan"
}

Response:
{
  "success": true,
  "data": {
    "id": 123,
    "test_parameter_id": 1,
    "measured_value": "25.5000",
    "result_status": "PASS",  // ✅ PASS / ❌ FAIL / ⚠️ INCONCLUSIVE
    "data_status": "Draft",
    "applied_standard_id": 45,
    "deviation_percentage": 2.5,
    "calculation_details": { ... }
  },
  "messages": [
    "✓ Nilai numerik valid: 25.5000 mg/dm2",
    "✓ Standar yang digunakan: Standar Nasional Indonesia (SNI 16371:2019)",
    "✓ Nilai 25.5 memenuhi standar SNI"
  ],
  "status_summary": {
    "total_parameters": 3,
    "passed": 1,
    "failed": 0,
    "inconclusive": 2,
    "overall_status": "PASS"
  }
}
```

### 3. **Upload Bukti Foto** (POST)
```
POST /test-results/{resultId}/upload-evidence

Form Data:
- file: (binary) JPG, PNG, GIF, or PDF (max 10MB)
- evidence_type: "Test Photo" | "Equipment Status" | "Calibration Certificate" | "Other"
- description: "Foto proses pengujian"

Response:
{
  "success": true,
  "data": {
    "id": 999,
    "file_name": "1713245680_abc123.jpg",
    "file_path": "test-evidences/123/...",
    "evidence_type": "Test Photo",
    "file_size": 524288
  },
  "message": "Bukti pengujian berhasil diunggah"
}
```

### 4. **Dapatkan Detail Hasil Uji** (GET)
```
GET /test-results/{resultId}

Response:
{
  "success": true,
  "data": {
    "id": 123,
    "parameter": { "name": "Migrasi Total", "unit": "mg/dm2" },
    "measured_value": "25.5000",
    "result_status": "PASS",
    "applied_standard": { "reference_document": "SNI 16371:2019" },
    "evidences": [ ... ]
  }
}
```

### 5. **Verifikasi oleh Supervisor** (POST) ⭐ [US-2.6]
```
POST /test-sessions/{sessionId}/verify

Request:
{
  "action": "approve"  // atau "reject"
  // Jika reject:
  // "rejection_reason": "Foto tidak jelas, silakan diulang"
}

Response:
{
  "success": true,
  "message": "Semua hasil uji diverifikasi",
  "summary": {
    "total_results": 5,
    "passed_results": 5,
    "failed_results": 0,
    "overall_status": "PASS"
  }
}
```

### 6. **Dapatkan Ringkasan Status** (GET)
```
GET /test-sessions/{sessionId}/status-summary

Response:
{
  "success": true,
  "session_id": 1,
  "session_status": "In-Progress",
  "summary": {
    "total_parameters": 5,
    "passed": 5,
    "failed": 0,
    "inconclusive": 0,
    "overall_status": "PASS"
  },
  "results": [
    {
      "parameter_name": "Migrasi Total",
      "measured_value": "25.5000",
      "unit": "mg/dm2",
      "status": "PASS",
      "status_badge": "✅ LULUS",
      "description": "Migrasi Total: 25.5000 mg/dm2 ✅ Memenuhi standar",
      "evidence_count": 2
    }
  ]
}
```

### 7. **Export Hasil Uji** (GET)
```
GET /test-sessions/{sessionId}/export

Response:
{
  "success": true,
  "export_data": {
    "session_id": 1,
    "technician": "Budi Santoso",
    "supervisor": "Dr. Siti Nurhaliza",
    "test_started_at": "2026-04-16T09:00:00",
    "test_ended_at": "2026-04-16T12:30:00",
    "summary": { ... },
    "results": [ ... ]
  }
}
```

---

## Logika Perbandingan Otomatis (Core Logic)

### Input Numeric Data Flow

```
1. VALIDASI NUMERIK
   ├─ Cek apakah nilai adalah angka
   └─ Cek presisi desimal sesuai parameter

2. CARI STANDAR AKTIF
   ├─ Filter standar dengan is_active = true
   ├─ Cek effective_date <= now() OR NULL
   └─ Cek expired_date >= now() OR NULL

3. PERBANDINGAN DENGAN SEMUA STANDAR
   ├─ Loop setiap standar aktif
   ├─ Bandingkan: measured_value vs [min_value, max_value]
   ├─ Set status: PASS jika semua ok, FAIL jika ada yang tidak
   └─ Hitung deviation_percentage

4. SIMPAN KE DATABASE
   ├─ Buat/update TestResult record
   ├─ Update TestSession status → In-Progress
   └─ Catat calculation_details untuk audit trail

5. RESPONSE DENGAN MESSAGES
   ├─ ✓ Pesan sukses
   ├─ ✓ Detail standar yang digunakan
   └─ Ringkasan status pengujian
```

### Contoh Perhitungan (Migrasi Total)

```
Parameter: Migrasi Total
Standar SNI 16371:2019: Max = 60 mg/dm2
FDA/EFSA Standard: Max = 10 mg/dm2

Input Nilai: 25.5 mg/dm2

Comparison:
├─ SNI: 25.5 ≤ 60? ✅ PASS
└─ FDA: 25.5 ≤ 10? ❌ FAIL

Overall Status: FAIL (karena ada 1 standar yang fail)
Applied Standard: FDA (standar pertama yang fail)
Deviation: ((25.5 - 10) / 10) × 100 = 155%
```

---

## Service Classes

### TestResultComparisonService
- `inputNumericData()` - Input nilai dengan perbandingan otomatis
- `compareWithAllStandards()` - Bandingkan dengan semua standar
- `calculateDeviation()` - Hitung persentase deviasi
- `verifySessionResults()` - Verifikasi semua hasil
- `lockResults()` - Lock data setelah approval

### TestEvidenceService
- `uploadEvidence()` - Upload bukti foto/dokumen
- `getSessionEvidences()` - Dapatkan semua bukti sesi
- `getResultEvidences()` - Dapatkan bukti untuk hasil uji
- `deleteEvidence()` - Hapus bukti
- `verifyEvidence()` - Verifikasi bukti oleh supervisor

---

## Acceptance Criteria Terpenuhi

### ✅ US-2.4: Input Data Numerik
- [x] Hanya terima angka (validasi numerik)
- [x] Otomatis bandingkan dengan threshold SNI/BPOM/FDA
- [x] Tautkan foto bukti (TestEvidence)
- [x] Catat metadata dan audit trail (calculation_details)

### ✅ US-2.5: Perbandingan Otomatis
- [x] Indikator BERHASIL/GAGAL real-time (PASS/FAIL/INCONCLUSIVE)
- [x] Eskalasi ke admin jika threshold belum diatur
- [x] Hitung deviation percentage
- [x] Support multiple standards (SNI, BPOM, FDA, EFSA)

### ✅ Status Data
- [x] Alat Siap (equipment_status)
- [x] In-Progress (status = 'In-Progress')
- [x] Draft (data_status = 'Draft')
- [x] Verified (data_status = 'Verified')

### ✅ US-2.6: Approval Supervisor
- [x] Status "Verified" saat approve
- [x] Data terkunci setelah verifikasi (tidak bisa diubah)
- [x] Wajib isi alasan jika reject

---

## Implementasi Next Steps

### Untuk Production:
1. Setup migrations di database
2. Seed test data (parameters, standards)
3. Setup authentication & authorization
4. Implementasi file storage (AWS S3 / Local)
5. Tambah logging & audit trail
6. Setup cache untuk performa query standar
7. Implementasi queue untuk proses yang heavy
8. Tambah validation rules yang lebih ketat
9. Setup API rate limiting
10. Testing & QA

### Testing Coverage:
- Unit tests untuk validation logic
- Integration tests untuk API endpoints
- E2E tests untuk workflow lengkap
- Edge cases: multiple standards, precision handling, timestamp

---

## Notes & Considerations

1. **Precision Storage**: Gunakan decimal(18, 8) untuk fleksibilitas berbagai parameter
2. **Audit Trail**: calculation_details JSON berisi semua detail perbandingan
3. **Multiple Standards**: Sistem support multiple standards; FAIL jika ada 1 standar yang fail
4. **Equipment Calibration**: Validasi equipment_is_calibrated sebelum pengujian
5. **File Storage**: Gunakan Laravel Storage facade untuk fleksibilitas
6. **Locking Mechanism**: Data Verified tidak bisa diubah (implement soft-delete jika perlu revisi)
7. **Notification**: Perlu implementasi event/listener untuk notifikasi approval

---

## Troubleshooting

### Issue: "Tidak ada standar aktif untuk perbandingan otomatis"
**Solusi**: 
- Pastikan test_standards record exists dengan is_active = true
- Cek effective_date dan expired_date

### Issue: Nilai numerik ditolak
**Solusi**:
- Cek decimal_places di test_parameters
- Pastikan nilai sesuai dengan data_type (decimal/integer/float)

### Issue: Status tidak berubah ke Verified
**Solusi**:
- Pastikan semua parameter sudah diinput
- Cek evidences sudah terupload untuk setiap result
- Verify endpoint memerlukan "approve" action

---

Generated: 2026-04-16
Author: System Design
Version: 1.0
