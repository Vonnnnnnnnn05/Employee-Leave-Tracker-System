<x-layouts.app title="Reports">
    <div class="flex flex-col gap-6">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <h1 class="text-2xl font-semibold">Leave reports</h1>
                <p class="text-sm text-slate-600">Review leave usage, absences, and request statistics.</p>
            </div>
            <form method="get" class="flex flex-wrap gap-2">
                <input class="form-input mt-0" type="date" name="from" value="{{ $from }}" aria-label="From date">
                <input class="form-input mt-0" type="date" name="to" value="{{ $to }}" aria-label="To date">
                <button class="btn-secondary" type="submit">Filter</button>
            </form>
        </div>

        <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach(['pending_manager', 'pending_hr', 'approved', 'rejected'] as $status)
                <div class="card">
                    <p class="text-sm text-slate-600">{{ ucfirst(str_replace('_', ' ', $status)) }}</p>
                    <p class="mt-2 text-3xl font-semibold">{{ $statusStats[$status] ?? 0 }}</p>
                </div>
            @endforeach
        </section>

        <section>
            <h2 class="mb-3 text-lg font-semibold">Leave usage by employee</h2>
            <div class="table-wrap">
                <table class="data-table">
                    <thead><tr><th>Employee</th><th>Department</th><th>Leave type</th><th>Days used</th></tr></thead>
                    <tbody class="divide-y divide-slate-200">
                        @forelse($leaveUsage as $row)
                            <tr><td>{{ $row->name }}</td><td>{{ $row->department ?? 'None' }}</td><td>{{ $row->leave_type }}</td><td>{{ $row->days }}</td></tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-slate-600">No approved leave usage in this period.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section>
            <h2 class="mb-3 text-lg font-semibold">Leave usage by department</h2>
            <div class="table-wrap">
                <table class="data-table">
                    <thead><tr><th>Department</th><th>Days used</th></tr></thead>
                    <tbody class="divide-y divide-slate-200">
                        @forelse($departmentUsage as $row)
                            <tr><td>{{ $row->department ?? 'None' }}</td><td>{{ $row->days }}</td></tr>
                        @empty
                            <tr><td colspan="2" class="text-center text-slate-600">No department usage found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section>
            <h2 class="mb-3 text-lg font-semibold">Absences</h2>
            <div class="table-wrap">
                <table class="data-table">
                    <thead><tr><th>Employee</th><th>Date</th><th>Remarks</th></tr></thead>
                    <tbody class="divide-y divide-slate-200">
                        @forelse($absences as $absence)
                            <tr><td>{{ $absence->user->name }}</td><td>{{ $absence->attendance_date->format('M d, Y') }}</td><td>{{ $absence->remarks }}</td></tr>
                        @empty
                            <tr><td colspan="3" class="text-center text-slate-600">No absences found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</x-layouts.app>
