@php
    $statusLabels = [
        'pending_manager' => 'Pending manager',
        'manager_approved' => 'Manager approved',
        'manager_rejected' => 'Manager rejected',
        'pending_hr' => 'Pending HR',
        'approved' => 'Approved',
        'rejected' => 'Rejected',
        'cancelled' => 'Cancelled',
    ];
@endphp

<div class="table-wrap">
    <table class="data-table">
        <thead>
            <tr>
                @if($showEmployee ?? false)
                    <th>Employee</th>
                @endif
                <th>Leave type</th>
                <th>Dates</th>
                <th>Days</th>
                <th>Status</th>
                <th>Reason</th>
                @if(($actions ?? null) !== null)
                    <th>Action</th>
                @endif
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-200">
            @forelse($leaveRequests as $request)
                <tr>
                    @if($showEmployee ?? false)
                        <td>
                            <p class="font-semibold">{{ $request->user->name }}</p>
                            <p class="text-xs text-slate-500">{{ $request->user->department ?? 'No department' }}</p>
                        </td>
                    @endif
                    <td>{{ $request->leaveType->name }}</td>
                    <td>{{ $request->start_date->format('M d, Y') }} to {{ $request->end_date->format('M d, Y') }}</td>
                    <td>{{ $request->total_days }}</td>
                    <td><span class="badge">{{ $statusLabels[$request->status] ?? $request->status }}</span></td>
                    <td class="max-w-xs">{{ $request->reason }}</td>
                    @if(($actions ?? null) === 'employee')
                        <td>
                            @if(in_array($request->status, ['pending_manager', 'pending_hr'], true))
                                <form method="post" action="{{ route('employee.requests.cancel', $request) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button class="btn-secondary" type="submit">Cancel</button>
                                </form>
                            @else
                                <span class="text-sm text-slate-500">No action</span>
                            @endif
                        </td>
                    @elseif(($actions ?? null) === 'manager')
                        <td>
                            @if($request->status === 'pending_manager')
                                <form method="post" action="{{ route('manager.requests.decide', $request) }}" class="flex flex-col gap-2">
                                    @csrf
                                    @method('PATCH')
                                    <label class="sr-only" for="manager-remarks-{{ $request->id }}">Manager remarks</label>
                                    <input class="form-input mt-0" id="manager-remarks-{{ $request->id }}" name="remarks" placeholder="Remarks">
                                    <div class="flex gap-2">
                                        <button class="btn-primary" name="decision" value="approve" type="submit">Approve</button>
                                        <button class="btn-danger" name="decision" value="reject" type="submit">Reject</button>
                                    </div>
                                </form>
                            @else
                                <span class="text-sm text-slate-500">Reviewed</span>
                            @endif
                        </td>
                    @elseif(($actions ?? null) === 'admin')
                        <td>
                            @if($request->status === 'pending_hr')
                                <form method="post" action="{{ route('admin.requests.decide', $request) }}" class="flex flex-col gap-2">
                                    @csrf
                                    @method('PATCH')
                                    <label class="sr-only" for="hr-remarks-{{ $request->id }}">HR remarks</label>
                                    <input class="form-input mt-0" id="hr-remarks-{{ $request->id }}" name="remarks" placeholder="Remarks">
                                    <div class="flex gap-2">
                                        <button class="btn-primary" name="decision" value="approve" type="submit">Approve</button>
                                        <button class="btn-danger" name="decision" value="reject" type="submit">Reject</button>
                                    </div>
                                </form>
                            @elseif($request->status === 'approved')
                                <form method="post" action="{{ route('admin.requests.cancel', $request) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button class="btn-secondary" type="submit">Cancel leave</button>
                                </form>
                            @else
                                <span class="text-sm text-slate-500">No action</span>
                            @endif
                        </td>
                    @endif
                </tr>
            @empty
                <tr>
                    <td colspan="{{ ($showEmployee ?? false) ? 7 : 6 }}" class="text-center text-slate-600">No leave requests found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
