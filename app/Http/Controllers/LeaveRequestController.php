<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLeaveRequestRequest;
use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use Illuminate\Support\Facades\DB;

class LeaveRequestController extends Controller
{
    public function index()
    {
        $leaveRequests = request()->user()
            ->leaveRequests()
            ->with('leaveType', 'approvals.approver')
            ->latest()
            ->paginate(10);

        return view('employee.requests.index', compact('leaveRequests'));
    }

    public function create()
    {
        $leaveTypes = LeaveType::query()->where('is_active', true)->orderBy('name')->get();
        $balances = request()->user()->leaveBalances()->with('leaveType')->get();

        return view('employee.requests.create', compact('leaveTypes', 'balances'));
    }

    public function store(StoreLeaveRequestRequest $request)
    {
        $data = $request->validated();
        $startDate = $request->date('start_date');
        $endDate = $request->date('end_date');
        $totalDays = LeaveRequest::businessDays($startDate, $endDate);

        DB::transaction(function () use ($data, $totalDays, $request): void {
            LeaveRequest::create([
                ...$data,
                'user_id' => $request->user()->id,
                'total_days' => $totalDays,
                'status' => LeaveRequest::STATUS_PENDING_MANAGER,
            ]);

            $leaveType = LeaveType::find($data['leave_type_id']);

            if ($leaveType?->requires_balance) {
                LeaveBalance::query()
                    ->where('user_id', $request->user()->id)
                    ->where('leave_type_id', $leaveType->id)
                    ->increment('pending_days', $totalDays);
            }
        });

        return redirect()->route('employee.requests.index')->with('status', 'Leave request submitted for manager review.');
    }

    public function cancel(LeaveRequest $leaveRequest)
    {
        abort_unless($leaveRequest->user_id === request()->user()->id, 403);
        abort_unless(in_array($leaveRequest->status, [
            LeaveRequest::STATUS_PENDING_MANAGER,
            LeaveRequest::STATUS_PENDING_HR,
        ], true), 403);

        DB::transaction(function () use ($leaveRequest): void {
            $this->releasePendingDays($leaveRequest);
            $leaveRequest->update([
                'status' => LeaveRequest::STATUS_CANCELLED,
                'cancelled_by' => request()->user()->id,
                'cancelled_at' => now(),
            ]);
        });

        return back()->with('status', 'Leave request cancelled.');
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
