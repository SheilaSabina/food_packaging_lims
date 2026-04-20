[Approve_Reject_Leave_Admin.md]
An administrative approval workflow for leave requests with enforced status transitions, audit logging, and notification delivery.
**Prompt:** "Generate Laravel admin approval/rejection logic for a Leave Management System with authorization policy checks, transactional status updates, audit comment persistence, notification dispatch, and prevention of repeated state transitions."
**• Context File:** "app/Http/Controllers/Admin/LeaveApprovalController.php"
**Skills:** "Laravel, PHP, Eloquent, Authorization, Notifications, Transactions, Policy"
**Task:** "Generate code for the following user story: As an admin, I want to approve or reject leave requests so I can manage employee time off and keep records accurate."
**Input:** @parameter "request_id, admin_id, action, comment"
**Output:** @return LeaveRequest "Returns the updated leave request model after approval or rejection."
//@return Boolean/Type "true on success, false on failure"
**• Rules:**
- Validate that request_id refers to an existing pending leave request.
- Require action to be either approve or reject and enforce admin authorization.
- Record the admin_id, status transition timestamp, and optional comment for audit purposes.
- Prevent status changes on leave requests that are already approved, rejected, or cancelled.
- Dispatch a notification to the employee after the decision is persisted.
**• What Changed:**
- Controller: Added an admin endpoint that validates input, checks policies, and delegates approval logic to a service.
- Model: Implemented state transition methods and persisted audit fields on the LeaveRequest entity.
- Middleware/Policy: Enforced admin-only access, used DB transactions to guarantee consistent state, and triggered notifications after successful commits.
Commit Message: "Refine admin leave approval workflow with authorization, transaction safety, and notification delivery"
