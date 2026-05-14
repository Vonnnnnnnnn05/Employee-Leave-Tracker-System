<x-layouts.app title="Attendance">
    <div class="grid gap-6 lg:grid-cols-[360px_1fr]">
        <section class="card">
            <h1 class="text-xl font-semibold">Record attendance</h1>
            <form method="post" action="{{ route('admin.attendance.store') }}" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label class="form-label" for="user_id">Employee</label>
                    <select class="form-input" id="user_id" name="user_id" required>
                        @foreach($employees as $employee)
                            <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div><label class="form-label" for="attendance_date">Date</label><input class="form-input" id="attendance_date" name="attendance_date" type="date" required></div>
                <div>
                    <label class="form-label" for="status">Status</label>
                    <select class="form-input" id="status" name="status" required>
                        <option value="present">Present</option>
                        <option value="absent">Absent</option>
                        <option value="late">Late</option>
                        <option value="on_leave">On leave</option>
                    </select>
                </div>
                <div><label class="form-label" for="time_in">Time in</label><input class="form-input" id="time_in" name="time_in" type="time"></div>
                <div><label class="form-label" for="time_out">Time out</label><input class="form-input" id="time_out" name="time_out" type="time"></div>
                <div><label class="form-label" for="remarks">Remarks</label><textarea class="form-input" id="remarks" name="remarks" rows="3"></textarea></div>
                <button class="btn-primary w-full" type="submit">Save attendance</button>
            </form>
        </section>
        <section>
            <div class="mb-3 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <h2 class="text-xl font-semibold">Attendance records</h2>
                <form class="flex gap-2" method="get">
                    <input class="form-input mt-0" type="date" name="from" value="{{ request('from') }}" aria-label="From date">
                    <input class="form-input mt-0" type="date" name="to" value="{{ request('to') }}" aria-label="To date">
                    <button class="btn-secondary" type="submit">Filter</button>
                </form>
            </div>
            <div class="table-wrap">
                <table class="data-table">
                    <thead><tr><th>Employee</th><th>Date</th><th>Status</th><th>Time</th><th>Remarks</th></tr></thead>
                    <tbody class="divide-y divide-slate-200">
                        @foreach($records as $record)
                            <tr>
                                <td>{{ $record->user->name }}</td>
                                <td>{{ $record->attendance_date->format('M d, Y') }}</td>
                                <td>{{ ucfirst(str_replace('_', ' ', $record->status)) }}</td>
                                <td>{{ $record->time_in ?? '-' }} to {{ $record->time_out ?? '-' }}</td>
                                <td>{{ $record->remarks }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $records->links() }}</div>
        </section>
    </div>
</x-layouts.app>
