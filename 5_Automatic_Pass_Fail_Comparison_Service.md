[Automatic Pass/Fail Comparison Service]
An automated comparison engine that evaluates measured values against test standards, calculates pass/fail status, applies tolerance ranges, and flags out-of-specification results for supervisor review.

**Prompt:** "Generate Laravel automatic test comparison service for a LIMS using TestResultComparisonService, standard specification evaluation, tolerance range application, pass/fail status calculation, out-of-spec alerting, transaction persistence, and comparison audit logging."

**• Context File:** "app/Services/TestResultComparisonService.php"

**Skills:** "Laravel, PHP, Eloquent, Service Layer, Transactions, Validation Logic"

**Task:** "Generate code for the following user story: As a laboratory system, I want to automatically compare measured values against test standards so I can generate pass/fail determinations and flag non-conformances for supervisor action."

**Input:** @parameter "test_result_id, measured_value, test_standard_id"

**Output:** @return TestResult "Returns the test result with comparison_status, pass_fail_status, and variance_percentage calculated."
//@return Boolean true

**• Rules:**
- Retrieve test_standard by test_standard_id and extract specification ranges (lower_limit, upper_limit, acceptable_tolerance_percentage per BPOM/SNI ISO 9001:2015).
- Calculate variance_percentage as (measured_value - standard_value) / standard_value * 100; apply tolerance_percentage to determine acceptance criteria.
- Set pass_fail_status to PASS if measured_value falls within (upper_limit ± tolerance) range; otherwise set to FAIL.
- Create TestComparison record with measured_value, standard_value, variance_percentage, and status within a transaction; trigger out-of-spec notification if FAIL.
- Log comparison calculation details in Comparison_Audit table including timestamp, applied tolerances, and calculated variance for regulatory audit trail.

**• What Changed:**
- Service: Created TestResultComparisonService with comparison logic, tolerance application, and pass/fail determination algorithms.
- Model: Extended TestResult and created TestComparison model to store comparison details, variance metrics, and calculation timestamps.
- Transaction/Event: Implemented transactional comparison execution with out-of-spec event triggering and audit logging; dispatch notification events for failed comparisons.

Commit Message: "Refine automatic pass/fail comparison service with tolerance ranges and out-of-spec alerting"
