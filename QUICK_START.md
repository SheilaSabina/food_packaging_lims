# Quick Start Guide: US-2.4 & US-2.5 Implementation

## 🚀 Setup & Installation

### 1. Jalankan Migrations
```bash
php artisan migrate --path=database/migrations
```

### 2. Seed Test Data (Parameters & Standards)
```bash
php artisan db:seed --class=TestParametersAndStandardsSeeder
```

### 3. Register Routes (jika belum otomatis)
Update file `routes/web.php` atau `routes/api.php`:
```php
require __DIR__.'/test-results.php';
```

---

## 📋 Workflow Penggunaan

### Step 1: Teknisi Memulai Pengujian

**Validasi Alat:**
```php
$session = TestSession::find(1);

// Cek apakah alat siap
if (!$session->isEquipmentReady()) {
    return "Alat belum dikalibrasi!";
}
```

**Update status alat:**
```php
$session->update([
    'equipment_is_calibrated' => true,
    'equipment_calibration_expires_at' => now()->addMonths(3),
    'equipment_status' => 'Alat Siap'
]);
```

---

### Step 2: Input Data Numerik (US-2.4)

**Menggunakan Service:**
```php
use App\Services\TestResultComparisonService;

$comparisonService = app(TestResultComparisonService::class);

// Input nilai numerik
$result = $comparisonService->inputNumericData(
    session: $session,
    parameter: $migrasiTotal,
    measuredValue: 25.5,
    notes: 'Pengukuran dilakukan pada suhu 25°C'
);

if ($result['success']) {
    echo "Data tersimpan!";
    echo $result['data']->result_status; // PASS, FAIL, atau INCONCLUSIVE
}
```

**Menggunakan API (HTTP POST):**
```bash
curl -X POST http://localhost/test-sessions/1/input-numeric \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer TOKEN" \
  -d '{
    "test_parameter_id": 1,
    "measured_value": 25.5,
    "notes": "Pengukuran dilakukan pada suhu 25°C"
  }'
```

---

### Step 3: Upload Bukti Foto (US-2.4)

**Menggunakan Service:**
```php
use App\Services\TestEvidenceService;

$evidenceService = app(TestEvidenceService::class);

$uploadResult = $evidenceService->uploadEvidence(
    file: $request->file('photo'),
    session: $session,
    result: $testResult,
    evidenceType: 'Test Photo',
    description: 'Foto hasil pengujian',
    uploadedBy: auth()->user()->name
);

if ($uploadResult['success']) {
    echo "Foto tersimpan: " . $uploadResult['data']->file_path;
}
```

**Menggunakan API (multipart/form-data):**
```bash
curl -X POST http://localhost/test-results/123/upload-evidence \
  -H "Authorization: Bearer TOKEN" \
  -F "file=@/path/to/photo.jpg" \
  -F "evidence_type=Test Photo" \
  -F "description=Foto hasil pengujian"
```

---

### Step 4: Verifikasi oleh Supervisor (US-2.6)

**Approve:**
```php
$session = TestSession::find(1);

// Cek apakah semua parameter sudah input
if (!$session->areAllResultsInputted()) {
    return "Belum semua parameter diinput!";
}

// Verifikasi
$verifyResult = $comparisonService->verifySessionResults($session);

if ($verifyResult['success']) {
    // Lock data
    $comparisonService->lockResults($session);
    echo "Data terverifikasi dan terkunci!";
}
```

**Reject dengan alasan:**
```php
$session->reject(
    reason: "Foto bukti tidak jelas, silakan diulang",
    supervisor: auth()->user()
);
```

**Menggunakan API:**
```bash
# Approve
curl -X POST http://localhost/test-sessions/1/verify \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer TOKEN" \
  -d '{"action": "approve"}'

# Reject
curl -X POST http://localhost/test-sessions/1/verify \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer TOKEN" \
  -d '{
    "action": "reject",
    "rejection_reason": "Foto bukti tidak jelas"
  }'
```

---

## 📊 Cek Status & Ringkasan

### Dapatkan Ringkasan Status
```php
$summary = $session->getStatusSummary();

echo "Total Parameter: " . $summary['total_parameters'];
echo "Passed: " . $summary['passed'];
echo "Failed: " . $summary['failed'];
echo "Overall: " . $summary['overall_status']; // PASS, FAIL, INCONCLUSIVE
```

### API Endpoint
```bash
curl -X GET http://localhost/test-sessions/1/status-summary \
  -H "Authorization: Bearer TOKEN"
```

---

## 🔍 Debugging & Troubleshooting

### Issue: "Tidak ada standar aktif"

**Cek apakah standards sudah diinput:**
```php
$parameter = TestParameter::find(1);
$activeStandards = $parameter->activeStandards()->get();

echo count($activeStandards) . " standar aktif";
```

**Tambah standard manually:**
```php
TestStandard::create([
    'test_parameter_id' => 1,
    'standard_type' => 'SNI',
    'max_value' => 60,
    'reference_document' => 'SNI 16371:2019',
    'effective_date' => now(),
    'is_active' => true
]);
```

### Issue: Nilai ditolak "Presisi maksimal"

**Cek decimal_places parameter:**
```php
$parameter = TestParameter::find(1);
echo "Decimal places: " . $parameter->decimal_places;

// Jika perlu ubah
$parameter->update(['decimal_places' => 6]);
```

### Issue: Data tidak bisa diverifikasi

**Cek status data:**
```php
$session->results()->each(function($result) {
    echo "Parameter: {$result->parameter->name}";
    echo "Status: {$result->data_status}";
    echo "Result: {$result->result_status}";
});
```

---

## 📈 Monitoring & Analytics

### Export Hasil Uji
```bash
curl -X GET http://localhost/test-sessions/1/export \
  -H "Authorization: Bearer TOKEN" \
  | jq . > hasil_uji.json
```

### Generate Laporan
```php
use App\Models\TestSession;

$session = TestSession::with([
    'results.parameter',
    'results.appliedStandard',
    'results.evidences'
])->find(1);

// Custom report generation
foreach ($session->results as $result) {
    echo $result->getResultDescription();
}
```

---

## 📝 Database Queries Penting

### Lihat semua parameter aktif
```sql
SELECT * FROM test_parameters WHERE is_active = 1;
```

### Lihat standar aktif untuk parameter tertentu
```sql
SELECT * FROM test_standards 
WHERE test_parameter_id = 1 
AND is_active = 1
AND (effective_date IS NULL OR effective_date <= NOW())
AND (expired_date IS NULL OR expired_date >= NOW());
```

### Lihat hasil dengan status FAIL
```sql
SELECT tr.*, tp.name 
FROM test_results tr
JOIN test_parameters tp ON tr.test_parameter_id = tp.id
WHERE tr.result_status = 'FAIL';
```

### Lihat deviation terbesar
```sql
SELECT 
  ts.id as session_id,
  tp.name as parameter,
  tr.measured_value,
  tr.deviation_percentage
FROM test_results tr
JOIN test_sessions ts ON tr.test_session_id = ts.id
JOIN test_parameters tp ON tr.test_parameter_id = tp.id
ORDER BY ABS(tr.deviation_percentage) DESC
LIMIT 10;
```

---

## 🧪 Testing dengan Postman/Insomnia

### 1. Buat Environment Variable
```json
{
  "base_url": "http://localhost:8000",
  "token": "your-sanctum-token",
  "session_id": 1
}
```

### 2. Test Input Numeric
```
POST {{base_url}}/test-sessions/{{session_id}}/input-numeric
Authorization: Bearer {{token}}
Content-Type: application/json

{
  "test_parameter_id": 1,
  "measured_value": 25.5,
  "notes": "Test measurement"
}
```

### 3. Test Upload Evidence
```
POST {{base_url}}/test-results/123/upload-evidence
Authorization: Bearer {{token}}

Form Data:
- file: (select file)
- evidence_type: Test Photo
- description: Test photo
```

---

## 🎯 Checklist Implementasi

- [ ] Migrations dijalankan
- [ ] Seeds data test
- [ ] Routes terdaftar
- [ ] Authentication setup
- [ ] File storage configured
- [ ] Test cases running
- [ ] API dokumentasi updated
- [ ] UI forms created
- [ ] Error handling implemented
- [ ] Logging setup
- [ ] Performance optimized
- [ ] Production ready

---

## 📞 Support & References

- **PRD**: [PRD_Kelompok_4.md](PRD_Kelompok_4.md)
- **Dokumentasi Lengkap**: [IMPLEMENTASI_US_2_4_2_5.md](IMPLEMENTASI_US_2_4_2_5.md)
- **API Examples**: [API_EXAMPLES_US_2_4_2_5.json](API_EXAMPLES_US_2_4_2_5.json)

---

**Last Updated**: 2026-04-16  
**Status**: Ready for Development
