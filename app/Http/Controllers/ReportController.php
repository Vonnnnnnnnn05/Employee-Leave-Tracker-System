<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $from = $request->input('from');
        $to = $request->input('to');

        $leaveUsage = LeaveRequest::query()
            ->select('users.name', 'users.department', 'leave_types.name as leave_type', DB::raw('SUM(total_days) as days'))
            ->join('users', 'users.id', '=', 'leave_requests.user_id')
            ->join('leave_types', 'leave_types.id', '=', 'leave_requests.leave_type_id')
            ->where('leave_requests.status', LeaveRequest::STATUS_APPROVED)
            ->when($from, fn ($query) => $query->whereDate('leave_requests.start_date', '>=', $from))
            ->when($to, fn ($query) => $query->whereDate('leave_requests.end_date', '<=', $to))
            ->groupBy('users.name', 'users.department', 'leave_types.name')
            ->orderBy('users.name')
            ->get();

        $departmentUsage = LeaveRequest::query()
            ->select('users.department', DB::raw('SUM(total_days) as days'))
            ->join('users', 'users.id', '=', 'leave_requests.user_id')
            ->where('leave_requests.status', LeaveRequest::STATUS_APPROVED)
            ->when($from, fn ($query) => $query->whereDate('leave_requests.start_date', '>=', $from))
            ->when($to, fn ($query) => $query->whereDate('leave_requests.end_date', '<=', $to))
            ->groupBy('users.department')
            ->orderBy('users.department')
            ->get();

        $statusStats = LeaveRequest::query()
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $absences = AttendanceRecord::with('user')
            ->where('status', 'absent')
            ->when($from, fn ($query) => $query->whereDate('attendance_date', '>=', $from))
            ->when($to, fn ($query) => $query->whereDate('attendance_date', '<=', $to))
            ->latest('attendance_date')
            ->get();

        return view('reports.index', compact('leaveUsage', 'departmentUsage', 'statusStats', 'absences', 'from', 'to'));
    }
}
