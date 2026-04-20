[Apply_Leave.md]
A controlled leave request workflow that validates leave windows, enforces business rules, and persists pending leave requests within a transaction.
**Prompt:** "Generate Laravel leave application logic for a Leave Management System using Carbon date calculations, leave type validation, overlapping request detection, balance enforcement, authorization policy checks, DB transaction persistence, and pending status initialization."
**• Context File:** "app/Http/Controllers/LeaveController.php"
**Skills:** "Laravel, PHP, Eloquent, Validation, Authorization, Carbon, Transactions"
**Task:** "Generate code for the following user story: As an employee, I want to apply for leave so I can request time off and await approval."
**Input:** @parameter "user_id, leave_type, start_date, end_date, reason"
**Output:** @return LeaveRequest "Returns the created leave request model with pending status and calculated duration."
//@return Boolean/Type "true on success, false on failure"
**• Rules:**
- Require the user_id, leave_type, start_date, end_date, and reason.
- Validate that start_date and end_date are valid Carbon dates, start_date is not in the past, and end_date is the same or after start_date.
- Enforce allowed leave types and ensure the requested interval does not overlap existing pending or approved leave.
- Require a reason with a minimum length and check leave balance or entitlement before creation.
- Persist the leave request inside a transaction, initialize status as pending, and calculate the total leave duration.
**• What Changed:**
- Controller: Added a leave application action that applies policy authorization and delegates business validation to a FormRequest.
- Model: Introduced leave request creation with computed duration and status initialization, and leveraged query scopes for overlap detection.
- Middleware/Service: Used transactional persistence and authorization middleware to ensure only valid leave requests are accepted and saved.
Commit Message: "Refine leave application flow with validation, overlap detection, and transactional persistence"