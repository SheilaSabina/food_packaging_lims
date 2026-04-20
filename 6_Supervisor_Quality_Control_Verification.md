[Supervisor Quality Control Verification]
A quality assurance workflow that empowers supervisors to review test results, verify comparison logic, approve/reject findings, and finalize test reports with digital certification.

**Prompt:** "Generate Laravel supervisor QC verification logic for a LIMS using authorization policy checks, out-of-spec result review, approval/rejection with comments, digital signature support, report finalization, transaction persistence, and compliance audit logging per EU Annex 11."

**• Context File:** "app/Http/Controllers/QCVerificationController.php" or "app/Services/QCVerificationService.php"

**Skills:** "Laravel, PHP, Eloquent, Authorization, Notifications, Transactions, Policy, Digital Signatures"

**Task:** "Generate code for the following user story: As a laboratory supervisor, I want to review and verify test results, approve final determinations, and authorize test report issuance so I can ensure quality compliance and regulatory adherence."

**Input:** @parameter "test_result_id, supervisor_id, action, verification_notes, digital_signature"

**Output:** @return TestResult "Returns the verified test result with approval status, supervisor signature, and finalized timestamp."
//@return Boolean true

**• Rules:**
- Validate test_result_id and verify the record has comparison_status = completed; prevent approval of pending or incomplete test records.
- Enforce supervisor role authorization and ensure supervisor_id has QC authority; restrict action to approve, reject, or request_revision.
- Require verification_notes for all actions and record supervisor_id, action_timestamp (Carbon), and digital_signature for EU Annex 11 and CFR Part 11 compliance.
- Prevent re-approval of already verified results and enforce immutability of verification records through transaction locking and audit trail creation.
- Trigger Test_Report generation on approval; dispatch notification to client; update sample status to tested_completed and record verification completion timestamp.

**• What Changed:**
- Controller: Added QCVerificationController with authorization checks and delegation to QCVerificationService for review and approval logic.
- Model: Extended TestResult with supervisor_id, verification_action, verification_notes, digital_signature, and verified_timestamp fields; created TestReport generation trigger.
- Service/Policy: Implemented QCVerificationService with approval workflow, digital signature validation, transaction persistence, and automated report generation; created QC_Verification_Audit table for regulatory compliance.

Commit Message: "Refine supervisor QC verification workflow with approval authority, digital signatures, and report finalization"
