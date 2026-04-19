# Laboratory Execution & Verification Skill

## Overview
This skill guides the AI to implement laboratory workflows for the food packaging safety testing system, including automated threshold validation against food safety regulations and strict data integrity through supervisor verification. It focuses on User Journey 2: Laboratory Execution & Verification (US 2.1 - US 2.6), ensuring that measured values are evaluated consistently, status transitions are enforced, and verification is managed via Livewire 4 components.

## Tech Stack
| Layer | Technology |
|---|---|
| Language | PHP 8.4.6 |
| Framework | Laravel 13 |
| UI Component | Tailwind CSS |
| Reactivity | Server Side Rendering |
| Styling | Tailwind CSS v4 |
| Database | SQLite (file: database/database.sqlite) |
| Auth | Built-in via Laravel Starter Kit (Breeze/Custom) |
| Queue | Laravel Queue (database driver) |
| Notification | Laravel Notifications (Mail) |
| Storage | Laravel Storage (local) |
| Testing | PHPUnit + Laravel Feature Tests |

## Project Structure
```
app/
  Livewire/
    Lab/
      SampleScanner.php
      TestExecution.php
    Supervisor/
      VerificationDashboard.php
  Services/
    TestResultComparisonService.php
    CalibrationGuard.php
```

## Database Schema (SQLite-Specific)
- Constraint: SQLite does not support ENUM types natively. Use string columns with application-level validation and explicit status guards.
- `test_parameters`
  - `id` (INTEGER, PK)
  - `threshold_max` (REAL)
  - `unit` (TEXT)
  - additional regulatory metadata fields as needed
- `test_order_parameters`
  - `id` (INTEGER, PK)
  - `test_order_id` (INTEGER, FK)
  - `test_parameter_id` (INTEGER, FK)
  - `measured_value` (REAL)
  - `result_status` (TEXT: pending/passed/failed)
  - `rejection_reason` (TEXT)
  - `status` (TEXT: received/in_lab/ready_for_verification/completed/failed)
  - audit fields for `verified_by`, `verified_at`, `updated_by`

## Status Flow
The laboratory execution and verification lifecycle follows a controlled status flow:
- `received` -> test sample is logged and awaiting processing
- `in_lab` -> technician is executing the test and recording `measured_value`
- `ready_for_verification` -> sample is complete and flagged for supervisor review
- `completed` (Verified) -> supervisor confirms the result, final approval is granted
- `failed` (Rejected for retesting) -> supervisor rejects the measurement and records `rejection_reason`

## Coding Conventions
- Service Layer: All business logic for `measured_value <= threshold_max` must be centralized in service classes such as `TestResultComparisonService` and `CalibrationGuard`.
- Data Locking: Implement status-based locking. When a record has `status` equal to `ready_for_verification` or `completed`, the technician-facing form must be read-only and cannot be modified by the technician.
- Transactions: Use `DB::transaction()` for all status transitions and updates that change verification state or test results.
- Validation: Use Livewire 4 `#[Validate]` attributes for all input validation rules instead of legacy protected `$rules` arrays.
- Architecture: Use MVC + Service Layer. Keep controllers and Livewire components thin, delegate validation and threshold comparison to services.

### Livewire 4 Example: Supervisor Verification
```php
<?php

namespace App\Livewire\Supervisor;

use App\Models\TestOrderParameter;
use App\Services\TestResultComparisonService;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Validate;
use Livewire\Component;

class VerificationDashboard extends Component
{
    public TestOrderParameter $parameter;
    public $status;
    #[Validate('required_if:status,failed')]
    public $rejection_reason;

    public function mount(TestOrderParameter $parameter)
    {
        $this->parameter = $parameter;
        $this->status = $parameter->status;
    }

    public function verify(TestResultComparisonService $comparisonService)
    {
        if ($this->parameter->status !== 'ready_for_verification') {
            session()->flash('error', 'Record is not ready for verification');
            return;
        }

        DB::transaction(function () use ($comparisonService) {
            $result = $comparisonService->compareMeasuredValue(
                $this->parameter->measured_value,
                $this->parameter->testParameter->threshold_max
            );

            $this->parameter->update([
                'status' => 'completed',
                'result_status' => $result['status'],
                'rejection_reason' => null,
                'verified_by' => auth()->id(),
                'verified_at' => now(),
            ]);
        });

        session()->flash('message', 'Verification completed');
    }

    public function reject()
    {
        $this->validate();

        if ($this->parameter->status !== 'ready_for_verification') {
            session()->flash('error', 'Record is not ready for verification');
            return;
        }

        DB::transaction(function () {
            $this->parameter->update([
                'status' => 'failed',
                'result_status' => 'failed',
                'rejection_reason' => $this->rejection_reason,
                'verified_by' => auth()->id(),
                'verified_at' => now(),
            ]);
        });

        session()->flash('message', 'Verification rejected');
    }

    public function render()
    {
        return view('livewire.supervisor.verification-dashboard');
    }
}
```

### Service Layer Example: Threshold Validation
```php
<?php

namespace App\Services;

use App\Models\TestOrderParameter;

class TestResultComparisonService
{
    public function compareMeasuredValue(float $measuredValue, float $thresholdMax): array
    {
        $valid = $measuredValue <= $thresholdMax;

        return [
            'is_valid' => $valid,
            'status' => $valid ? 'passed' : 'failed',
            'message' => $valid
                ? 'Measured value is within allowed threshold.'
                : 'Measured value exceeds threshold_max and requires rejection.',
        ];
    }
}
```

## Implementation Notes
- Keep `TestExecution.php` and `SampleScanner.php` focused on test data capture and workflow state updates.
- Keep `VerificationDashboard.php` focused on supervisor approval, rejection reasoning, and final status enforcement.
- Use `CalibrationGuard.php` for any tolerance or calibration-related exception handling prior to final comparison.
- Enforce SQLite compatibility with string-based status validation in the application layer.
- Ensure the UI renders `Flux UI` components in a read-only state when `status` is `ready_for_verification` or later.

## Related User Stories
- US 2.1: Sample reception and lab execution entry
- US 2.2: Measured value recording and unit compliance
- US 2.3: Workflow handoff from technician to supervisor
- US 2.4: Automated safety threshold validation
- US 2.5: Service Layer threshold comparison using `threshold_max` and `measured_value`
- US 2.6: Supervisor verification with status-based locking and rejection_reason handling
