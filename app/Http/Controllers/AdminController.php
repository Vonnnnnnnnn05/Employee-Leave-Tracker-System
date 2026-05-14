<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use App\Models\LeaveApproval;
use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    public function dashboard()
    {
        return view('admin.dashboard', [
            'pendingHr' => LeaveRequest::where('status', LeaveRequest::STATUS_PENDING_HR)->count(),
            'approved' => LeaveRequest::where('status', LeaveRequest::STATUS_APPROVED)->count(),
            'employees' => User::where('role', 'employee')->count(),
            'absences' => AttendanceRecord::where('status', 'absent')->count(),
            'requests' => LeaveRequest::with('user', 'leaveType')->latest()->limit(8)->get(),
        ]);
    }

    public function employees()
    {
        $employees = User::query()->with('manager')->orderBy('name')->paginate(12);
        $managers = User::where('role', 'manager')->orderBy('name')->get();
        $leaveTypes = LeaveType::where('is_active', true)->orderBy('name')->get();

        return view('admin.employees', compact('employees', 'managers', 'leaveTypes'));
    }

    public function storeEmployee(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', 'in:employee,manager,admin'],
            'department' => ['nullable', 'string', 'max:255'],
            'manager_id' => ['nullable', 'exists:users,id'],
            'position' => ['nullable', 'string', 'max:255'],
            'employment_status' => ['required', 'in:active,inactive'],
            'hire_date' => ['nullable', 'date'],
        ]);

        User::create([...$data, 'password' => Hash::make($data['password'])]);

        return back()->with('status', 'Employee account created.');
    }

    public function updateEmployee(Request $request, User $user)
    {
        $data = $request->validate([
            'role' => ['required', 'in:employee,manager,admin'],
            'department' => ['nullable', 'string', 'max:255'],
            'manager_id' => ['nullable', 'exists:users,id'],
            'position' => ['nullable', 'string', 'max:255'],
            'employment_status' => ['required', 'in:active,inactive'],
            'hire_date' => ['nullable', 'date'],
        ]);

        $user->update($data);

        return back()->with('status', 'Employee profile updated.');
    }

    public function leaveTypes()
    {
        $leaveTypes = LeaveType::orderBy('name')->get();

        return view('admin.leave-types', compact('leaveTypes'));
    }

    public function storeLeaveType(Request $request)
    {
        LeaveType::create($request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:leave_types,name'],
            'default_days' => ['required', 'integer', 'min:0'],
            'is_paid' => ['nullable', 'boolean'],
            'requires_balance' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ]));

        return back()->with('status', 'Leave type created.');
    }

    public function balances()
    {
        $employees = User::whereIn('role', ['employee', 'manager'])->orderBy('name')->get();
        $leaveTypes = LeaveType::where('is_active', true)->orderBy('name')->get();
        $balances = LeaveBalance::with('user', 'leaveType')->orderBy('user_id')->get();

        return view('admin.balances', compact('employees', 'leaveTypes', 'balances'));
    }

    public function updateBalance(Request $request)
    {
        $data = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'leave_type_id' => ['required', 'exists:leave_types,id'],
            'available_days' => ['required', 'integer', 'min:0'],
        ]);

        LeaveBalance::updateOrCreate(
            ['user_id' => $data['user_id'], 'leave_type_id' => $data['leave_type_id']],
            ['available_days' => $data['available_days']]
        );

        return back()->with('status', 'Leave balance saved.');
    }

    public function requests()
    {
        $leaveRequests = LeaveRequest::with('user', 'leaveType', 'approvals.approver')->latest()->paginate(12);

        return view('admin.requests', compact('leaveRequests'));
    }

    public function decide(Request $request, LeaveRequest $leaveRequest)
    {
        abort_unless($leaveRequest->status === LeaveRequest::STATUS_PENDING_HR, 403);

        $data = $request->validate([
            'decision' => ['required', 'in:approve,reject'],
            'remarks' => ['nullable', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($data, $leaveRequest, $request): void {
            LeaveApproval::create([
                'leave_request_id' => $leaveRequest->id,
                'approver_id' => $request->user()->id,
                'stage' => 'hr',
                'decision' => $data['decision'] === 'approve' ? 'approved' : 'rejected',
                'remarks' => $data['remarks'] ?? null,
            ]);

            if ($data['decision'] === 'approve') {
                $this->approveBalance($leaveRequest);
                $leaveRequest->update([
                    'status' => LeaveRequest::STATUS_APPROVED,
                    'hr_finalized_at' => now(),
                ]);

                return;
            }

            $this->releasePendingDays($leaveRequest);
            $leaveRequest->update([
                'status' => LeaveRequest::STATUS_REJECTED,
                'hr_finalized_at' => now(),
            ]);
        });

        return back()->with('status', 'HR decision recorded.');
    }

    public function cancelApproved(LeaveRequest $leaveRequest)
    {
        abort_unless($leaveRequest->status === LeaveRequest::STATUS_APPROVED, 403);

        DB::transaction(function () use ($leaveRequest): void {
            if ($leaveRequest->leaveType->requires_balance) {
                LeaveBalance::query()
                    ->where('user_id', $leaveRequest->user_id)
                    ->where('leave_type_id', $leaveRequest->leave_type_id)
                    ->decrement('used_days', $leaveRequest->total_days);
            }

            $leaveRequest->update([
                'status' => LeaveRequest::STATUS_CANCELLED,
                'cancelled_by' => request()->user()->id,
                'cancelled_at' => now(),
            ]);
        });

        return back()->with('status', 'Approved leave cancelled and balance restored.');
    }

    public function attendance(Request $request)
    {
        $employees = User::orderBy('name')->get();
        $records = AttendanceRecord::with('user')
            ->when($request->filled('from'), fn ($query) => $query->whereDate('attendance_date', '>=', $request->date('from')))
            ->when($request->filled('to'), fn ($query) => $query->whereDate('attendance_date', '<=', $request->date('to')))
            ->latest('attendance_date')
            ->paginate(12);

        return view('admin.attendance', compact('employees', 'records'));
    }

    public function storeAttendance(Request $request)
    {
        $data = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'attendance_date' => ['required', 'date'],
            'status' => ['required', 'in:present,absent,late,on_leave'],
            'time_in' => ['nullable', 'date_format:H:i'],
            'time_out' => ['nullable', 'date_format:H:i'],
            'remarks' => ['nullable', 'string', 'max:1000'],
        ]);

        AttendanceRecord::updateOrCreate(
            ['user_id' => $data['user_id'], 'attendance_date' => $data['attendance_date']],
            $data
        );

        return back()->with('status', 'Attendance record saved.');
    }

    private function approveBalance(LeaveRequest $leaveRequest): void
    {
        if (! $leaveRequest->leaveType->requires_balance) {
            return;
        }

        LeaveBalance::query()
            ->where('user_id', $leaveRequest->user_id)
            ->where('leave_type_id', $leaveRequest->leave_type_id)
            ->update([
                'pending_days' => DB::raw('pending_days - '.$leaveRequest->total_days),
                'used_days' => DB::raw('used_days + '.$leaveRequest->total_days),
            ]);
    }

    private function releasePendingDays(LeaveRequest $leaveRequest): void
    {
        if (! $leaveRequest->leaveType->requires_balance) {
            return;
        }

        LeaveBalance::query()
            ->where('user_id', $leaveRequest->user_id)
            ->where('leave_type_id', $leaveRequest->leave_type_id)
            ->decrement('pending_days', $leaveRequest->total_days);
    }
}
