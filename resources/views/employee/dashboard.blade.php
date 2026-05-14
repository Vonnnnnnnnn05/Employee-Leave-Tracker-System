<x-layouts.app title="Employee Dashboard">
    <div class="flex flex-col gap-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-slate-950">Employee dashboard</h1>
                <p class="text-sm text-slate-600">Submit requests and monitor leave balances.</p>
            </div>
            <a class="btn-primary" href="{{ route('employee.requests.create') }}">Submit leave</a>
        </div>

        <div class="grid gap-4 md:grid-cols-3">
            <div class="card">
                <p class="text-sm font-medium text-slate-600">Pending requests</p>
                <p class="mt-2 text-3xl font-semibold">{{ $pendingCount }}</p>
            </div>
            @foreach($balances->take(2) as $balance)
                <div class="card">
                    <p class="text-sm font-medium text-slate-600">{{ $balance->leaveType->name }}</p>
                    <p class="mt-2 text-3xl font-semibold">{{ $balance->remainingDays() }}</p>
                    <p class="text-sm text-slate-600">days remaining</p>
                </div>
            @endforeach
        </div>

        <section class="card">
            <h2 class="text-lg font-semibold">Leave balances</h2>
            <div class="mt-4 grid gap-3 md:grid-cols-2 lg:grid-cols-3">
                @forelse($balances as $balance)
                    <div class="rounded-md border border-slate-200 p-4">
                        <p class="font-semibold">{{ $balance->leaveType->name }}</p>
                        <dl class="mt-3 grid grid-cols-3 gap-2 text-sm">
                            <div><dt class="text-slate-500">Available</dt><dd class="font-semibold">{{ $balance->available_days }}</dd></div>
                            <div><dt class="text-slate-500">Used</dt><dd class="font-semibold">{{ $balance->used_days }}</dd></div>
                            <div><dt class="text-slate-500">Pending</dt><dd class="font-semibold">{{ $balance->pending_days }}</dd></div>
                        </dl>
                    </div>
                @empty
                    <p class="text-sm text-slate-600">No leave balances assigned yet.</p>
                @endforelse
            </div>
        </section>

        <section>
            <h2 class="mb-3 text-lg font-semibold">Recent requests</h2>
            @include('partials.requests-table', ['leaveRequests' => $requests, 'showEmployee' => false, 'actions' => 'employee'])
        </section>
    </div>
</x-layouts.app>
