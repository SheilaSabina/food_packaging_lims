[Test Result Input (measured_value)]
A data capture system for raw laboratory measurements that records measured values, equipment metadata, and operator information before automatic comparison against test standards.

**Prompt:** "Generate Laravel test result input logic for a LIMS using measured_value persistence, operator authorization, equipment metadata linking, timestamp recording with Carbon, decimal precision validation, transaction persistence, and pre-comparison data staging."

**• Context File:** "app/Http/Controllers/TestResultController.php" or "app/Services/TestResultInputService.php"

**Skills:** "Laravel, PHP, Eloquent, Validation, Transactions, Carbon, Authorization"

**Task:** "Generate code for the following user story: As a laboratory analyst, I want to input raw measurement values so I can stage test data for automatic comparison and create an audit trail of operator measurements."

**Input:** @parameter "test_session_id, measured_value, unit, operator_id, equipment_id, measurement_notes, measured_timestamp"

**Output:** @return TestResult "Returns the created test result record with measured_value stored and comparison status pending."
//@return Boolean true

**• Rules:**
- Require test_session_id, measured_value, unit, and operator_id; validate measured_value is numeric with appropriate decimal precision (minimum 2 decimal places per SNI standards).
- Verify operator_id has analyst role and authorization to input results for the associated test_type and sample_id.
- Link measured_value to equipment_id and record equipment calibration_version to create traceability; capture measurement timestamp using Carbon.
- Initialize TestResult status as pending_comparison and store all data within a transaction to preserve measurement integrity.
- Enforce unit consistency with test standard unit and validate measured_value range feasibility (alert if significantly outside expected bounds).

**• What Changed:**
- Controller: Added test result input endpoint that authorizes operator and delegates input validation to TestResultInputService.
- Model: Extended TestResult model with measured_value field, operator relationship, equipment_calibration_version linking, and measurement_timestamp tracking.
- Service/Transaction: Created input service that validates measurements, links equipment metadata, creates initial comparison staging record, and logs operator input in Audit_Log table.

Commit Message: "Refine test result input flow with measured_value persistence and operator audit trail for LIMS"
