# Food Packaging LIMS - User Story 2.6 Implementation

## Prompt

Generate Laravel 13 MVC code untuk fitur verifikasi supervisor pada sistem *Food Packaging Laboratory Information Management System (LIMS)*. Sistem harus memungkinkan supervisor untuk menyetujui (approve) atau menolak (reject) hasil uji laboratorium, serta menerapkan mekanisme data locking untuk menjaga integritas data.

---

## Context

SKILLS.md - Laboratory Execution & Verification Skill

---

## Task

Sebagai supervisor QC, saya ingin meninjau hasil uji laboratorium yang telah diinput oleh teknisi, kemudian:

* Memberikan persetujuan (**Approve**) untuk mengunci data secara permanen
* Memberikan penolakan (**Reject**) dengan menyertakan alasan (**rejection_reason**)
* Memastikan data tidak dapat diubah setelah status menjadi **Verified**

Fitur ini merupakan implementasi dari **User Story 2.6 (US 2.6)**.

---

## Input

```php id="kq2h1a"
/**
 * Memproses verifikasi sesi pengujian oleh Supervisor
 *
 * @param Request $request
 * @param TestSession $session
 * @return \Illuminate\Http\RedirectResponse
 */
public function verifySession(Request $request, TestSession $session)
```

---

## Output

* Redirect kembali ke halaman sebelumnya dengan pesan sukses atau error
* Memperbarui status `test_sessions` menjadi:

  * `Verified` (jika disetujui)
  * `Rejected` (jika ditolak)
* Menyimpan `rejection_reason` jika status **Rejected**
* Mencatat waktu verifikasi (`verified_at`)

---

## Rules

### 1. Validation

* `rejection_reason` **wajib diisi** jika status = `Rejected`

---

### 2. Status Transition

* HANYA sesi dengan status:

  ```
  Ready for Verification
  ```

  yang boleh diverifikasi

---

### 3. Authorization

* HANYA user dengan role:

  ```
  supervisor
  ```

  yang dapat melakukan verifikasi

---

### 4. Data Locking

* Jika status = `Verified`:

  * Data menjadi **read-only**
  * Tidak dapat diubah oleh teknisi

---

## Implementation (Controller)

File: `app/Http/Controllers/TestResultController.php`

```php id="9vmbpl"
<?php

namespace App\Http\Controllers;

use App\Models\TestSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TestResultController extends Controller
{
    /**
     * Verifikasi hasil uji oleh Supervisor (US 2.6)
     */
    public function verifySession(Request $request, TestSession $session)
    {
        // Validasi status awal
        if ($session->status !== 'Ready for Verification') {
            return back()->with('error', 'Sesi belum siap untuk diverifikasi.');
        }

        DB::transaction(function () use ($request, $session) {

            // Approve
            if ($request->action === 'approve') {
                $session->update([
                    'status' => 'Verified',
                    'verified_at' => now(),
                    'rejection_reason' => null
                ]);
            } 
            // Reject
            else {
                $request->validate([
                    'rejection_reason' => 'required|string'
                ]);

                $session->update([
                    'status' => 'Rejected',
                    'rejection_reason' => $request->rejection_reason
                ]);
            }
        });

        return redirect()->back()->with('success', 'Status verifikasi berhasil diperbarui.');
    }
}
```

---

## Route

File: `routes/test-results.php`

```php id="3bc4pq"
use App\Http\Controllers\TestResultController;

Route::post('/sessions/{session}/verify', [TestResultController::class, 'verifySession'])
    ->middleware('auth')
    ->name('sessions.verify');
```

---

## View (Blade Example)

File: `resources/views/supervisor/dashboard.blade.php`

```html id="2r3y9x"
<form method="POST" action="{{ route('sessions.verify', $session->id) }}">
    @csrf

    <textarea name="rejection_reason" placeholder="Alasan penolakan (jika reject)"></textarea>

    <button type="submit" name="action" value="approve">
        Approve
    </button>

    <button type="submit" name="action" value="reject">
        Reject
    </button>
</form>
```

---

## What Changed

* **Modified**: `app/Http/Controllers/TestResultController.php`
  → Menambahkan method `verifySession()` untuk proses verifikasi

* **Modified**: `resources/views/supervisor/dashboard.blade.php`
  → Menambahkan form approve/reject + input rejection_reason

* **Modified**: `routes/test-results.php`
  → Menambahkan route POST untuk verifikasi

* **Modified**: Database (`test_sessions`)
  → Menambahkan kolom `rejection_reason`

---

## Commit Message

```bash id="n6o0ye"
feat: implement supervisor verification workflow with rejection_reason and data locking (US 2.6)
```

---

## Implementation Notes

* Gunakan **DB::transaction()** untuk menjaga konsistensi data
* Pastikan validasi dilakukan sebelum update status
* Terapkan **data locking** di sisi UI dan backend
* Gunakan status berbasis string (SQLite compatible)
* Controller hanya mengatur alur, bukan logika bisnis kompleks

---

## Acceptance Criteria
| Kriteria                                 | Status |
| ---------------------------------------- | ------ |
| Supervisor dapat approve hasil uji       | ✅      |
| Supervisor dapat reject dengan alasan    | ✅      |
| Data terkunci setelah verified           | ✅      |
| Validasi rejection_reason berjalan       | ✅      |
| Status hanya dari Ready for Verification | ✅      |

---