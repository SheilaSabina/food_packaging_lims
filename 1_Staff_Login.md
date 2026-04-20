[Staff Login]
A secure authentication system for laboratory staff, designed to enforce role-based access, audit login events, and maintain compliance with laboratory information security standards.

**Prompt:** "Generate secure Laravel staff login logic for a Laboratory Information Management System (LIMS) using credential validation, role-based access control, session regeneration with Carbon timestamps, failed login tracking, account lockout after repeated attempts, and audit logging of all authentication events."

**• Context File:** "app/Http/Controllers/Auth/StaffLoginController.php"

**Skills:** "Laravel, PHP, Authentication, Session Management, Validation, Security, Audit Logging"

**Task:** "Generate code for the following user story: As a laboratory staff member, I want to log in securely to the LIMS so I can access sample information, equipment status, and test result data according to my role permissions."

**Input:** @parameter "staff_id, password"

**Output:** @return Boolean "Returns true when authentication succeeds and the session is established with role-based access."
//@return Boolean true

**• Rules:**
- Require staff_id (unique identifier) and password; validate staff_id exists and is active.
- Verify password matches the hashed credential and enforce a minimum of 8 characters with secure hashing.
- Implement account lockout after 5 failed login attempts within 15 minutes to prevent brute force attacks.
- Regenerate the session on successful authentication and record login timestamp using Carbon for audit compliance.
- Load and cache staff role permissions (analyst, technician, supervisor, administrator) to enforce laboratory access control policies per SNI ISO/IEC 17025:2017.

**• What Changed:**
- Controller: Added StaffLoginController with credential validation, lockout enforcement, and role-based middleware checks.
- Model: Implemented Staff model with failed_login_attempts counter, locked_until timestamp, and role association with permission caching.
- Middleware/Audit: Created AuthAuditLog entries for all login attempts, rejections, and lockouts; used DB transactions to ensure audit consistency.

Commit Message: "Refine staff login authentication with role-based access, lockout policy, and audit logging for LIMS compliance"
