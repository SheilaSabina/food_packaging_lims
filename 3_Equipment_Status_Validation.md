[Equipment Status Validation]
An equipment status monitoring system that validates measurement instruments are calibrated, operational, and compliant with laboratory quality standards before test execution.

**Prompt:** "Generate Laravel equipment status validation logic for a LIMS using calibration date verification with Carbon, equipment status checks, maintenance record tracking, scheduled calibration enforcement, authorization policy checks, and blocking of non-compliant equipment usage."

**• Context File:** "app/Http/Controllers/EquipmentController.php" or "app/Services/EquipmentValidationService.php"

**Skills:** "Laravel, PHP, Eloquent, Validation, Carbon, Authorization, Policy"

**Task:** "Generate code for the following user story: As a laboratory analyst, I want to verify equipment status and calibration before conducting tests so I can ensure measurement accuracy and maintain compliance with SNI ISO/IEC 17025:2017."

**Input:** @parameter "equipment_id, test_type"

**Output:** @return Boolean "Returns true when equipment is operational and calibrated; false when equipment is non-compliant."
//@return Boolean true

**• Rules:**
- Validate that equipment_id exists and its status is operational or in-service; reject non-operational equipment.
- Check that equipment calibration_expiry_date is current using Carbon and has not passed; enforce minimum calibration frequency per ISO 17025:2017.
- Verify equipment is assigned to the requested test_type and perform maintenance status check to confirm no pending maintenance issues.
- Record equipment status validation timestamp and operator (staff_id) for audit trail compliance and traceability.
- Prevent test execution and raise validation exception if equipment fails any compliance check; return detailed failure reason for staff action.

**• What Changed:**
- Controller: Added equipment validation endpoint that authorizes staff and delegates status checking to EquipmentValidationService.
- Model: Implemented Equipment model with calibration tracking, maintenance_status field, assigned_test_types relationship, and last_validation_timestamp.
- Service/Policy: Created transactional validation service that performs multi-step compliance checks and records validation outcomes in Equipment_Audit table for regulatory compliance.

Commit Message: "Refine equipment status validation with calibration enforcement and maintenance tracking for laboratory compliance"
