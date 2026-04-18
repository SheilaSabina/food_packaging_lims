# 🏗️ Architectural Diagrams & Relationships

## Entity Relationship Diagram (ERD)

```
┌──────────────────────────┐
│   test_parameters        │
├──────────────────────────┤
│ id (PK)                  │
│ name                     │ ◄───────────────┐
│ description              │                 │
│ unit                     │                 │
│ data_type                │     1:N         │
│ decimal_places           │                 │
│ category                 │                 │
│ is_active                │                 │
│ created_at, updated_at   │                 │
└──────────────────────────┘                 │
           │                                  │
           │ 1:N                              │
           │                                  │
           ▼                                  │
┌──────────────────────────────────────┐     │
│      test_standards                  │     │
├──────────────────────────────────────┤     │
│ id (PK)                              │     │
│ test_parameter_id (FK) ──────────────┤─────┘
│ standard_type (SNI/BPOM/FDA/EFSA)   │
│ min_value                            │
│ max_value                            │
│ requirement_description              │
│ reference_document                   │
│ effective_date                       │
│ expired_date                         │
│ is_active                            │
│ created_at, updated_at               │
└──────────────────────────────────────┘
           ▲
           │ 1:N
           │
           │
┌──────────────────────────────────────┐
│      test_results                    │
├──────────────────────────────────────┤
│ id (PK)                              │
│ test_session_id (FK) ────────────┐   │
│ test_parameter_id (FK) ──────────┼──►│
│ measured_value (DECIMAL:8) ⭐     │   │
│ unit                             │   │
│ applied_standard_id (FK) ────────┼──►│
│ standard_min/max_value           │   │
│ result_status ⭐                  │   │
│ (PASS/FAIL/INCONCLUSIVE)        │   │
│ deviation_percentage             │   │
│ data_status ⭐                    │   │
│ (Alat Siap/In-Progress/Draft/   │   │
│  Verified)                       │   │
│ notes                            │   │
│ calculation_details (JSON) ⭐   │   │
│ created_at, updated_at           │   │
└──────────────────────────────────────┘
           ▲
           │ 1:N
           │
           │
┌──────────────────────────────────────┐
│      test_sessions                   │
├──────────────────────────────────────┤
│ id (PK)                              │
│ order_id (FK)                        │
│ technician_id (FK - to users)        │
│ supervisor_id (FK - to users)        │
│ equipment_id                         │
│ equipment_status ⭐                  │
│ (Alat Siap/In-Progress/Draft/      │
│  Verified)                          │
│ equipment_is_calibrated              │
│ equipment_calibration_expires_at     │
│ test_started_at                      │
│ test_ended_at                        │
│ test_method                          │
│ status ⭐                            │
│ (Draft/In-Progress/Ready.../       │
│  Verified/Rejected)                 │
│ rejection_reason                     │
│ verified_at                          │
│ created_at, updated_at               │
└──────────────────────────────────────┘
           │
           │ 1:N
           │
           ▼
┌──────────────────────────────────────┐
│      test_evidences                  │
├──────────────────────────────────────┤
│ id (PK)                              │
│ test_result_id (FK - nullable)       │
│ test_session_id (FK)                 │
│ file_name                            │
│ file_path                            │
│ file_type (mime type)                │
│ file_size                            │
│ evidence_type                        │
│ (Test Photo/Equipment Status/       │
│  Calibration Certificate/Other)     │
│ description                          │
│ uploaded_by                          │
│ uploaded_at                          │
│ metadata (JSON)                      │
│ is_verified                          │
│ created_at, updated_at               │
└──────────────────────────────────────┘
```

---

## Class Diagram (Models)

```
┌─────────────────────────────────────┐
│      « Model »                      │
│      TestParameter                  │
├─────────────────────────────────────┤
│ Properties:                         │
│  - name: string                     │
│  - unit: string                     │
│  - decimal_places: int              │
│  - is_active: boolean               │
├─────────────────────────────────────┤
│ Methods:                            │
│  + formatValue(value): string       │
│  + isValidNumericValue(v): bool     │
│  + activeStandards(): Collection    │
│  + standards(): HasMany             │
│  + results(): HasMany               │
└─────────────────────────────────────┘
           │
           │ has many
           │
           ▼
┌─────────────────────────────────────┐
│      « Model »                      │
│      TestStandard                   │
├─────────────────────────────────────┤
│ Properties:                         │
│  - standard_type: enum              │
│  - min_value: decimal               │
│  - max_value: decimal               │
│  - is_active: boolean               │
│  - effective_date: date             │
│  - expired_date: date               │
├─────────────────────────────────────┤
│ Methods:                            │
│  + isCurrentlyActive(): bool        │
│  + checkCompliance(value): array    │
│  + parameter(): BelongsTo           │
└─────────────────────────────────────┘


┌─────────────────────────────────────┐
│      « Model »                      │
│      TestSession                    │
├─────────────────────────────────────┤
│ Properties:                         │
│  - equipment_status: enum           │
│  - equipment_is_calibrated: bool    │
│  - status: enum                     │
│  - test_started_at: datetime        │
│  - test_ended_at: datetime          │
├─────────────────────────────────────┤
│ Methods:                            │
│  + isEquipmentReady(): bool         │
│  + areAllResultsInputted(): bool    │
│  + areAllResultsVerified(): bool    │
│  + getStatusSummary(): array        │
│  + markAsVerified(user): bool       │
│  + reject(reason, user): bool       │
│  + results(): HasMany               │
│  + evidences(): HasMany             │
└─────────────────────────────────────┘
           │
           │ has many
           │
           ▼
┌─────────────────────────────────────┐
│      « Model »                      │
│      TestResult                     │
├─────────────────────────────────────┤
│ Properties:                         │
│  - measured_value: decimal:8 ⭐    │
│  - result_status: enum ⭐           │
│  (PASS/FAIL/INCONCLUSIVE)          │
│  - data_status: enum ⭐             │
│  - deviation_percentage: decimal    │
│  - calculation_details: json ⭐    │
├─────────────────────────────────────┤
│ Methods:                            │
│  + getStatusBadge(): string         │
│  + updateDataStatus(status): bool   │
│  + getResultDescription(): string   │
│  + session(): BelongsTo             │
│  + parameter(): BelongsTo           │
│  + appliedStandard(): BelongsTo     │
│  + evidences(): HasMany             │
└─────────────────────────────────────┘
           │
           │ has many
           │
           ▼
┌─────────────────────────────────────┐
│      « Model »                      │
│      TestEvidence                   │
├─────────────────────────────────────┤
│ Properties:                         │
│  - file_path: string                │
│  - file_type: string (mime)         │
│  - file_size: bigint                │
│  - evidence_type: enum              │
│  - is_verified: boolean             │
├─────────────────────────────────────┤
│ Methods:                            │
│  + isValidFileType(mime): bool      │
│  + isValidFileSize(size): bool      │
│  + getFormattedFileSize(): string   │
│  + result(): BelongsTo              │
│  + session(): BelongsTo             │
└─────────────────────────────────────┘
```

---

## Service Layer Architecture

```
┌──────────────────────────────────────────────┐
│  TestResultComparisonService                 │
├──────────────────────────────────────────────┤
│                                              │
│  Core Methods:                               │
│  ┌────────────────────────────────────────┐ │
│  │ inputNumericData(                      │ │
│  │   session, parameter, value, notes     │ │
│  │ )                                      │ │
│  │ ├─ Step 1: Validasi numerik           │ │
│  │ ├─ Step 2: Cari standar aktif         │ │
│  │ ├─ Step 3: Bandingkan otomatis        │ │
│  │ ├─ Step 4: Hitung deviation           │ │
│  │ ├─ Step 5: Simpan ke DB               │ │
│  │ └─ Return: result array                │ │
│  └────────────────────────────────────────┘ │
│                                              │
│  ┌────────────────────────────────────────┐ │
│  │ compareWithAllStandards(                │ │
│  │   value, parameter, standards           │ │
│  │ )                                      │ │
│  │ ├─ Loop setiap standar                │ │
│  │ ├─ Bandingkan min/max value           │ │
│  │ ├─ Set overall status PASS/FAIL       │ │
│  │ └─ Return: comparison results         │ │
│  └────────────────────────────────────────┘ │
│                                              │
│  ┌────────────────────────────────────────┐ │
│  │ verifySessionResults(session)           │ │
│  │ ├─ Check kelengkapan                  │ │
│  │ ├─ Update semua hasil → Verified      │ │
│  │ ├─ Update session status              │ │
│  │ └─ Return: summary array              │ │
│  └────────────────────────────────────────┘ │
│                                              │
│  ┌────────────────────────────────────────┐ │
│  │ lockResults(session)                    │ │
│  │ ├─ Mark all results as immutable      │ │
│  │ ├─ Update session → Verified          │ │
│  │ └─ Return: boolean                    │ │
│  └────────────────────────────────────────┘ │
└──────────────────────────────────────────────┘
           ▲
           │ uses
           │
           │
┌──────────────────────────────────────────────┐
│  TestEvidenceService                         │
├──────────────────────────────────────────────┤
│                                              │
│  ┌────────────────────────────────────────┐ │
│  │ uploadEvidence(                         │ │
│  │   file, session, result, type, desc    │ │
│  │ )                                      │ │
│  │ ├─ Validate file type                 │ │
│  │ ├─ Validate file size                 │ │
│  │ ├─ Store to disk                      │ │
│  │ ├─ Create DB record                   │ │
│  │ └─ Return: evidence data              │ │
│  └────────────────────────────────────────┘ │
│                                              │
│  ┌────────────────────────────────────────┐ │
│  │ getSessionEvidences(session)            │ │
│  │ └─ Return: Collection of evidences   │ │
│  └────────────────────────────────────────┘ │
│                                              │
│  ┌────────────────────────────────────────┐ │
│  │ getResultEvidences(result)              │ │
│  │ └─ Return: Collection of evidences   │ │
│  └────────────────────────────────────────┘ │
│                                              │
│  ┌────────────────────────────────────────┐ │
│  │ verifyEvidence(evidence)                │ │
│  │ └─ Mark as verified by supervisor    │ │
│  └────────────────────────────────────────┘ │
│                                              │
│  ┌────────────────────────────────────────┐ │
│  │ deleteEvidence(evidence)                │ │
│  │ ├─ Remove from storage                │ │
│  │ ├─ Delete DB record                   │ │
│  │ └─ Return: boolean                    │ │
│  └────────────────────────────────────────┘ │
└──────────────────────────────────────────────┘
           ▲
           │ uses
           │
           │
┌──────────────────────────────────────────────┐
│  TestResultController                        │
├──────────────────────────────────────────────┤
│                                              │
│  Request Handlers:                           │
│  ├─ showInputForm(session)                  │
│  ├─ inputNumeric(request, session) ⭐      │
│  ├─ uploadEvidence(request, result)        │
│  ├─ show(result)                            │
│  ├─ verifySessions(request, session) ⭐   │
│  ├─ getStatusSummary(session)               │
│  └─ export(session)                         │
└──────────────────────────────────────────────┘
```

---

## Request/Response Flow Diagram

```
CLIENT REQUEST
    │
    ▼
┌─────────────────────────────────┐
│  HTTP POST /input-numeric       │
│  {                              │
│    test_parameter_id: 1,        │
│    measured_value: 25.5,        │
│    notes: "..."                 │
│  }                              │
└─────────────────────────────────┘
    │
    ▼
┌─────────────────────────────────┐
│  TestResultController           │
│  .inputNumeric()                │
└─────────────────────────────────┘
    │
    ▼
┌─────────────────────────────────┐
│  1. Validate Input              │
│  ├─ test_parameter_id: exists   │
│  ├─ measured_value: numeric     │
│  └─ notes: string, max 500      │
└─────────────────────────────────┘
    │ ✓ valid
    ▼
┌─────────────────────────────────────────────┐
│  2. Fetch Parameter & Validate              │
│  ├─ Get TestParameter record                │
│  ├─ Check: is_active = true                 │
│  └─ Validate numeric value precision        │
└─────────────────────────────────────────────┘
    │ ✓ valid
    ▼
┌──────────────────────────────────────┐
│  3. Call Service Layer              │
│  $comparisonService->inputNumericData()
└──────────────────────────────────────┘
    │
    ▼
┌──────────────────────────────────────────────────┐
│  SERVICE: TestResultComparisonService            │
│                                                  │
│  ┌────────────────────────────────────────────┐ │
│  │ Step 1: Validate Numeric Value             │ │
│  │ ├─ is_numeric($value)? ✓                  │ │
│  │ ├─ decimal_places check? ✓                │ │
│  │ └─ Format: 25.5000 (decimal:8)           │ │
│  └────────────────────────────────────────────┘ │
│                    │                            │
│                    ▼                            │
│  ┌────────────────────────────────────────────┐ │
│  │ Step 2: Fetch Active Standards             │ │
│  │ SELECT * FROM test_standards               │ │
│  │ WHERE parameter_id = 1                     │ │
│  │   AND is_active = true                     │ │
│  │   AND effective_date <= NOW()              │ │
│  │   AND (expired_date IS NULL OR >= NOW())  │ │
│  │                                            │ │
│  │ Result: [SNI, BPOM, FDA, EU]              │ │
│  └────────────────────────────────────────────┘ │
│                    │                            │
│                    ▼                            │
│  ┌────────────────────────────────────────────┐ │
│  │ Step 3: Compare with All Standards         │ │
│  │ ┌─ SNI: 25.5 ≤ 60? ✓ PASS                 │ │
│  │ ├─ BPOM: 25.5 ≤ 50? ✓ PASS                │ │
│  │ ├─ FDA: 25.5 ≤ 10? ✗ FAIL ← select       │ │
│  │ └─ EU: 25.5 ≤ 10? ✗ FAIL                 │ │
│  │                                            │ │
│  │ overall_status = FAIL (1 failed)          │ │
│  └────────────────────────────────────────────┘ │
│                    │                            │
│                    ▼                            │
│  ┌────────────────────────────────────────────┐ │
│  │ Step 4: Calculate Deviation                │ │
│  │ deviation = ((25.5 - 10) / 10) × 100      │ │
│  │ deviation = 155%                          │ │
│  └────────────────────────────────────────────┘ │
│                    │                            │
│                    ▼                            │
│  ┌────────────────────────────────────────────┐ │
│  │ Step 5: Save to Database (Transaction)     │ │
│  │ ├─ TestResult::create({                    │ │
│  │ │   session_id: 1,                         │ │
│  │ │   parameter_id: 1,                       │ │
│  │ │   measured_value: 25.5,                  │ │
│  │ │   result_status: 'FAIL',                 │ │
│  │ │   applied_standard_id: 2,                │ │
│  │ │   deviation_percentage: 155.0,           │ │
│  │ │   data_status: 'Draft',                  │ │
│  │ │   calculation_details: {...}             │ │
│  │ │ })                                       │ │
│  │ │                                          │ │
│  │ ├─ TestSession::update({                   │ │
│  │ │   status: 'In-Progress',                 │ │
│  │ │   test_started_at: now()                 │ │
│  │ │ })                                       │ │
│  │ │                                          │ │
│  │ └─ return success array                    │ │
│  └────────────────────────────────────────────┘ │
└──────────────────────────────────────────────────┘
    │
    ▼
┌──────────────────────────────────────┐
│  4. Prepare API Response              │
│  {                                   │
│    success: true,                    │
│    data: TestResult object,          │
│    messages: [...],                  │
│    status_summary: {...}             │
│  }                                   │
└──────────────────────────────────────┘
    │
    ▼
┌──────────────────────────────────────┐
│  HTTP 200 OK Response                │
│  {                                   │
│    "success": true,                  │
│    "data": {                         │
│      "id": 123,                      │
│      "measured_value": "25.5000",   │
│      "result_status": "FAIL",       │
│      "deviation_percentage": "155"   │
│    },                                │
│    "messages": [                     │
│      "✓ Nilai valid",                │
│      "✓ Standar FDA dipilih",        │
│      "✗ Melebihi max (10)"           │
│    ]                                 │
│  }                                   │
└──────────────────────────────────────┘
    │
    ▼
FRONTEND RESPONSE
```

---

## Status State Machine

```
                    ┌─────────────┐
                    │   Created   │
                    └──────┬──────┘
                           │
                           ▼
                    ┌─────────────┐
                    │   Draft     │ ◄─────────────┐
                    │ (Initial)   │               │
                    └──────┬──────┘               │
                           │ [Technician input   │
                           │  first parameter]   │
                           ▼                     │
                    ┌─────────────────────────┐  │
                    │  In-Progress            │  │
                    │ (More data being input) │  │
                    └──────┬──────────────────┘  │
                           │ [All data input]   │
                           ▼                    │
                    ┌─────────────────────────┐ │
                    │ Ready for Verification  │ │
                    │ (Awaiting supervisor)   │ │
                    └──────┬────────┬─────────┘ │
                           │        │           │
                [Approve]  │        │ [Reject]  │
                           │        └───────────┘
                           ▼
                    ┌─────────────┐
                    │ Verified    │
                    │ (Locked)    │ ✓ DATA IMMUTABLE
                    └─────────────┘

DATA STATUS: Alat Siap → In-Progress → Draft → Verified
RESULT STATUS: (none) → PASS/FAIL/INCONCLUSIVE
```

---

## Test Data Flow

```
TestParametersAndStandardsSeeder
├── 5 Parameters Created
│   ├── Migrasi Total (mg/dm2, 4 decimals)
│   ├── Kadar BPA (µg/L, 6 decimals)
│   ├── Konsentrasi Radon (Bq/L, 4 decimals)
│   ├── Kadar Phthalates (mg/kg, 4 decimals)
│   └── pH (-, 2 decimals)
│
└── 11 Standards Created
    ├── Migrasi Total
    │   ├── SNI 16371:2019 (max: 60)
    │   ├── FDA CFR Part 165 (max: 10)
    │   ├── EU Regulation 10/2011 (max: 10)
    │   └── BPOM (max: 50)
    ├── Kadar BPA
    │   ├── EFSA (max: 0.6)
    │   └── FDA (max: 2.5)
    ├── Konsentrasi Radon
    │   ├── WHO (max: 100)
    │   └── EU Directive (max: 50)
    └── Phthalates & pH
        └── EU & SNI standards
```

---

## File Upload Process

```
CLIENT
  │
  │ [Select Photo File]
  │
  ▼
┌──────────────────────────┐
│ TestResultController     │
│ .uploadEvidence()        │
└──────┬───────────────────┘
       │
       ▼
┌──────────────────────────────────┐
│ 1. Validate Input                │
│ ├─ file exists                   │
│ ├─ evidence_type valid           │
│ ├─ description (max 500)         │
└──────┬───────────────────────────┘
       │
       ▼
┌──────────────────────────────────┐
│ 2. Validate File                 │
│ ├─ Type: JPG/PNG/GIF/PDF         │
│ ├─ Size: max 10MB                │
└──────┬───────────────────────────┘
       │
       ▼
┌──────────────────────────────────┐
│ 3. Store to Disk                 │
│ Storage::disk('public')          │
│  .putFileAs(                     │
│    'test-evidences/{sessionId}', │
│    $file,                        │
│    {timestamp}_{uniqid}.{ext}    │
│  )                               │
└──────┬───────────────────────────┘
       │
       ▼
┌──────────────────────────────────┐
│ 4. Save DB Record                │
│ TestEvidence::create({           │
│   file_path: ...,                │
│   file_type: ...,                │
│   file_size: ...,                │
│   is_verified: false,            │
│   ...                            │
│ })                               │
└──────┬───────────────────────────┘
       │
       ▼
┌──────────────────────────────────┐
│ 5. HTTP 200 Response             │
│ {                                │
│   success: true,                 │
│   data: evidence object,         │
│   message: "Berhasil diunggah"  │
│ }                                │
└──────────────────────────────────┘
       │
       ▼
FRONTEND - Display Evidence
```

---

## Performance Optimization

```
Database Optimizations:
├── Indexes Created
│   ├── test_results: (test_session_id, result_status)
│   ├── test_results: (test_parameter_id, data_status)
│   ├── test_standards: (test_parameter_id, standard_type)
│   ├── test_evidences: (test_result_id, evidence_type)
│   └── test_standards: (is_active, effective_date)
│
├── Query Optimization
│   ├─ Use eager loading (.with())
│   ├─ Use select() to limit columns
│   ├─ Index on frequently filtered columns
│   └─ Use transactions for data consistency
│
└── Caching Strategy
    ├─ Cache active standards (5 min TTL)
    ├─ Cache parameter list (1 day TTL)
    └─ Invalidate on data change
```

---

**Generated**: 2026-04-16  
**Version**: 1.0
