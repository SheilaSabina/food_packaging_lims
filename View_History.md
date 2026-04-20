[View_History.md]
A polished leave history query component that scopes results to the authenticated employee, supports advanced filters, and returns paginated records.
**Prompt:** "Generate Laravel leave history retrieval logic for a Leave Management System with eager loading, authentication scoping, optional status and date range filtering, pagination limits, and descending order by request date."
**• Context File:** "app/Http/Controllers/LeaveHistoryController.php"
**Skills:** "Laravel, PHP, Eloquent, Query Builder, Pagination, Carbon"
**Task:** "Generate code for the following user story: As an employee, I want to view my leave history so I can see past applications, approvals, and rejections."
**Input:** @parameter "user_id, page, per_page, status_filter, start_date, end_date"
**Output:** @return LengthAwarePaginator "Returns paginated leave history records scoped to the authenticated user."
//@return Boolean/Type "true on success, false on failure"
**• Rules:**
- Validate that the user_id exists and matches the authenticated employee.
- Support optional status filtering and date range filters for start_date and end_date.
- Enforce a maximum per_page value and return paginated results.
- Order records by created_at descending and eager load related approver and leave-type metadata.
- Restrict the query to only the employee's own leave entries to preserve data privacy.
**• What Changed:**
- Controller: Added a history retrieval action that applies authentication scoping and optional filters.
- Model: Leveraged query scopes for status and date range selection, plus eager loading of related models.
- Middleware/Policy: Ensured only authorized employees may request their own leave history and enforced pagination constraints.
Commit Message: "Refine leave history retrieval with scoped filtering, pagination, and data privacy"
