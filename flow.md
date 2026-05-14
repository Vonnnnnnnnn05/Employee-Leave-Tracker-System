# Employee Leave Tracker System Flow

## 1. User Authentication Flow

1. User opens the system login page.
2. User enters email and password.
3. System validates credentials.
4. If credentials are invalid, the system returns the user to the login page with an error.
5. If credentials are valid, the system redirects the user based on role:
   - `employee` goes to the Employee Dashboard.
   - `manager` goes to the Manager Dashboard.
   - `admin` goes to the HR/Admin Dashboard.

## 2. Employee Leave Request Flow

1. Employee logs in.
2. Employee views current leave balances and recent leave history.
3. Employee opens the leave request form.
4. Employee selects leave type, start date, end date, and reason.
5. System validates the request:
   - Start date must not be before today.
   - End date must not be before start date.
   - Reason is required.
   - Leave period must include at least one working day.
   - Request must not overlap another active leave request.
   - Paid leave must have enough remaining balance.
6. If validation fails, the system shows the validation errors.
7. If validation succeeds:
   - System creates a leave request with status `pending_manager`.
   - System adds requested days to `pending_days` for the employee leave balance.
8. Employee can view the submitted request in My Requests.
9. Employee may cancel the request while it is still pending.

## 3. Manager Approval Flow

1. Manager logs in.
2. Manager views leave requests from assigned team members.
3. Manager reviews request details:
   - Employee name
   - Leave type
   - Date range
   - Total working days
   - Reason
4. Manager chooses either Approve or Reject.
5. If manager approves:
   - System records an approval entry with stage `manager`.
   - Request status changes to `pending_hr`.
   - Request moves to HR/admin for final decision.
6. If manager rejects:
   - System records a rejection entry with stage `manager`.
   - Request status changes to `manager_rejected`.
   - System removes requested days from `pending_days`.
   - Leave balance is not deducted.

## 4. HR/Admin Final Approval Flow

1. HR/admin logs in.
2. HR/admin opens the approvals page.
3. HR/admin reviews requests with status `pending_hr`.
4. HR/admin chooses either Approve or Reject.
5. If HR/admin approves:
   - System records an approval entry with stage `hr`.
   - Request status changes to `approved`.
   - System removes requested days from `pending_days`.
   - System adds requested days to `used_days`.
   - Employee remaining balance decreases.
6. If HR/admin rejects:
   - System records a rejection entry with stage `hr`.
   - Request status changes to `rejected`.
   - System removes requested days from `pending_days`.
   - Employee used balance is not changed.

## 5. Approved Leave Cancellation Flow

1. HR/admin opens the approvals page.
2. HR/admin selects an approved leave request.
3. HR/admin cancels the approved leave.
4. System changes request status to `cancelled`.
5. System records who cancelled the request and when it was cancelled.
6. System subtracts the leave days from `used_days`.
7. Employee leave balance is restored.

## 6. Leave Balance Flow

1. HR/admin creates or maintains leave types.
2. HR/admin assigns leave balances to employees.
3. When an employee submits a valid paid leave request:
   - Requested days are added to `pending_days`.
4. When a manager rejects the request:
   - Requested days are removed from `pending_days`.
5. When HR/admin rejects the request:
   - Requested days are removed from `pending_days`.
6. When HR/admin approves the request:
   - Requested days are removed from `pending_days`.
   - Requested days are added to `used_days`.
7. Remaining balance is calculated as:

```text
remaining_days = available_days - used_days - pending_days
```

## 7. Attendance Record Flow

1. HR/admin opens the attendance page.
2. HR/admin selects employee, date, status, time in, time out, and remarks.
3. System validates attendance data.
4. System creates or updates the attendance record for that employee and date.
5. Attendance records can be filtered by date range.
6. Absence records are included in reports.

## 8. Reporting Flow

1. HR/admin opens the reports page.
2. HR/admin optionally enters a date range.
3. System generates report sections:
   - Leave usage by employee
   - Leave usage by department
   - Absences
   - Leave request status totals
4. Reports are displayed as export-ready tables.

## 9. Role Access Flow

| Role | Allowed Actions |
| --- | --- |
| Employee | Submit leave, view own requests, cancel pending own requests, view own balances |
| Manager | View assigned team requests, approve/reject team requests |
| Admin/HR | Manage employees, leave types, balances, attendance, HR approvals, cancellations, and reports |

Unauthorized users are blocked with a `403 Forbidden` response.

## 10. Main Status Flow

```text
pending_manager
    ├── manager_rejected
    └── pending_hr
            ├── approved
            │       └── cancelled
            └── rejected
```

