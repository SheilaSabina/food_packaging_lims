[Sample Reception (Draft)]
An initial sample intake workflow that captures sample metadata, assigns unique identifiers, records sample status as draft, and validates receipt conditions per laboratory chain-of-custody protocols.

**Prompt:** "Generate Laravel sample reception logic for a LIMS using unique barcode generation, sample metadata validation, chain-of-custody documentation, draft status initialization, Carbon timestamps for sample arrival recording, transaction persistence, and pre-receipt validation checks."

**• Context File:** "app/Http/Controllers/SampleController.php" or "app/Services/SampleReceptionService.php"

**Skills:** "Laravel, PHP, Eloquent, Validation, Transactions, Carbon, Barcode Generation"

**Task:** "Generate code for the following user story: As a laboratory technician, I want to record sample reception in draft status so I can document sample arrival, capture metadata, and initiate chain-of-custody tracking before formal testing begins."

**Input:** @parameter "product_name, sample_quantity, sample_date, client_name, packaging_type, sampling_location, draft_notes"

**Output:** @return Sample "Returns the created sample model with draft status and unique barcode identifier."
//@return Boolean true

**• Rules:**
- Require product_name, sample_quantity, sample_date, and client_name as mandatory fields; validate sample_quantity > 0.
- Generate unique barcode identifier (prefixed with current year and sequential number) to comply with BPOM and ISO 17025:2017 chain-of-custody requirements.
- Initialize sample status as draft; record sample arrival timestamp using Carbon; persist all data within a transaction to ensure data integrity.
- Validate that sample_date is not in the future and matches laboratory operational hours to ensure sample freshness tracking.
- Capture sampling location, packaging type, and draft notes to document pre-test sample conditions and condition-of-receipt compliance.

**• What Changed:**
- Controller: Added sample reception endpoint that validates input and delegates to SampleReceptionService for barcode generation and atomic persistence.
- Model: Implemented Sample model with barcode generation logic, draft status initialization, and relationship to Staff and Client entities.
- Service/Middleware: Created transactional service layer for sample creation, barcode assignment, and initial chain-of-custody record generation.

Commit Message: "Refine sample reception flow with barcode generation, draft status, and chain-of-custody documentation"
