<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $user = request()->user();

        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        if ($user->isManager()) {
            return redirect()->route('manager.dashboard');
        }

        $balances = $user->leaveBalances()->with('leaveType')->get();
        $requests = $user->leaveRequests()->with('leaveType')->latest()->limit(8)->get();
        $pendingCount = $user->leaveRequests()
            ->whereIn('status', [LeaveRequest::STATUS_PENDING_MANAGER, LeaveRequest::STATUS_PENDING_HR])
            ->count();

        return view('employee.dashboard', compact('balances', 'requests', 'pendingCount'));
    }
}
