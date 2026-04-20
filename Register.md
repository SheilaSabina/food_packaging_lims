[Register.md]
A secure employee onboarding flow for the leave management system, designed for transactional persistence, role assignment, and abuse-resistant registration.
**Prompt:** "Generate secure Laravel employee registration logic for a Leave Management System using FormRequest validation, DB transactions, password hashing, unique employee identifiers, Carbon date stamping, employee profile association, default role assignment, and throttling for registration attempts."
**• Context File:** "app/Http/Controllers/Auth/RegisterController.php"
**Skills:** "Laravel, PHP, Eloquent, Authentication, Validation, Transactions, Security"
**Task:** "Generate code for the following user story: As an employee, I want to register for the leave management system so I can request leave and track my leave history."
**Input:** @parameter "name, email, password, password_confirmation, employee_id, department"
**Output:** @return User "Returns the created user model instance with associated employee profile on successful registration."
//@return Boolean/Type "true on success, false on failure"
**• Rules:**
- Require name, email, password, employee_id, and department as mandatory fields.
- Enforce unique email across users and unique employee_id across employee records.
- Validate password confirmation and require a minimum of 8 characters plus secure hashing before persistence.
- Use a transactional save so user and profile creation succeed or rollback together.
- Assign a default employee role, create or link the employee profile, and throttle repeated registration attempts per IP.
**• What Changed:**
- Controller: Added a registration action that delegates validation to a FormRequest and applies rate limiting.
- Model: Enabled atomic creation of User and EmployeeProfile records within a DB transaction.
- Middleware/Policy: Ensured guest-only access and centralized validation so registration is secure and consistent.
Commit Message: "Refine secure employee registration flow with validation, transaction, and role assignment"
