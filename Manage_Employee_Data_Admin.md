[Manage_Employee_Data_Admin.md]
A centralized employee management module that ensures admin-level control over employee profiles, role assignments, and status lifecycle management.
**Prompt:** "Generate Laravel admin employee data management logic for a Leave Management System with CRUD operations, unique constraints, transactional persistence, role-based access control, soft status changes, and employee lifecycle validation."
**• Context File:** "app/Http/Controllers/Admin/EmployeeController.php"
**Skills:** "Laravel, PHP, Eloquent, Validation, Authorization, Transactions, Policy"
**Task:** "Generate code for the following user story: As an admin, I want to manage employee data so I can maintain accurate records for leave processing and reporting."
**Input:** @parameter "employee_id, name, email, department, role, status"
**Output:** @return Employee "Returns the created or updated employee model after persistence with validated fields."
//@return Boolean/Type "true on success, false on failure"
**• Rules:**
- Require employee_id, name, email, department, role, and status for create/update operations.
- Enforce unique email and employee_id values across active employee records.
- Restrict role values to a predefined set such as employee, manager, and admin.
- Restrict status values to active or inactive and prevent deactivation when there are pending approvals.
- Allow only admin users to perform employee data creation, updates, and status changes within a transaction.
**• What Changed:**
- Controller: Added admin CRUD endpoints that validate request data and enforce role-based access control.
- Model: Added employee persistence logic with unique constraint handling, status lifecycle rules, and transaction safety.
- Middleware/Policy: Applied admin authorization checks and encapsulated business rules to ensure employee data remains consistent and auditable.
Commit Message: "Refine admin employee management flow with validation, transaction safety, and role-based control"
