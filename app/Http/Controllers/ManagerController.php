<?php

namespace App\Http\Controllers;

use App\Models\LeaveApproval;
use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ManagerController extends Controller
{
    public function dashboard()
    {
        $manager = request()->user();
        $teamMemberIds = $manager->teamMembers()->pluck('id');
        $pendingRequests = LeaveRequest::query()
            ->with('user', 'leaveType')
            ->whereIn('user_id', $teamMemberIds)
            ->where('status', LeaveRequest::STATUS_PENDING_MANAGER)
            ->latest()
            ->get();
        $teamRequests = LeaveRequest::query()
            ->with('user', 'leaveType')
            ->whereIn('user_id', $teamMemberIds)
            ->latest()
            ->limit(12)
            ->get();

        return view('manager.dashboard', compact('pendingRequests', 'teamRequests'));
    }

    public function requests()
    {
        $teamMemberIds = request()->user()->teamMembers()->pluck('id');
        $leaveRequests = LeaveRequest::query()
            ->with('user', 'leaveType', 'approvals.approver')
            ->whereIn('user_id', $teamMemberIds)
            ->latest()
            ->paginate(12);

        return view('manager.requests', compact('leaveRequests'));
    }

    public function decide(Request $request, LeaveRequest $leaveRequest)
    {
        abort_unless($leaveRequest->user?->manager_id === $request->user()->id, 403);
        abort_unless($leaveRequest->status === LeaveRequest::STATUS_PENDING_MANAGER, 403);

        $data = $request->validate([
            'decision' => ['required', 'in:approve,reject'],
            'remarks' => ['nullable', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($data, $leaveRequest, $request): void {
            $decision = $data['decision'] === 'approve' ? 'approved' : 'rejected';

            LeaveApproval::create([
                'leave_request_id' => $leaveRequest->id,
                'approver_id' => $request->user()->id,
                'stage' => 'manager',
                'decision' => $decision,
                'remarks' => $data['remarks'] ?? null,
            ]);

            if ($data['decision'] === 'approve') {
                $leaveRequest->update([
                    'status' => LeaveRequest::STATUS_PENDING_HR,
                    'manager_approved_at' => now(),
                ]);

                return;
            }

            $this->releasePendingDays($leaveRequest);
            $leaveRequest->update(['status' => LeaveRequest::STATUS_MANAGER_REJECTED]);
        });

        return back()->with('status', 'Manager decision recorded.');
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
