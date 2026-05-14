<x-layouts.app title="Admin Dashboard">
    <div class="flex flex-col gap-6">
        <div>
            <h1 class="text-2xl font-semibold">HR/admin dashboard</h1>
            <p class="text-sm text-slate-600">Monitor leave operations, attendance, balances, and reports.</p>
        </div>
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="card"><p class="text-sm text-slate-600">Pending HR</p><p class="mt-2 text-3xl font-semibold">{{ $pendingHr }}</p></div>
            <div class="card"><p class="text-sm text-slate-600">Approved leaves</p><p class="mt-2 text-3xl font-semibold">{{ $approved }}</p></div>
            <div class="card"><p class="text-sm text-slate-600">Employees</p><p class="mt-2 text-3xl font-semibold">{{ $employees }}</p></div>
            <div class="card"><p class="text-sm text-slate-600">Absences</p><p class="mt-2 text-3xl font-semibold">{{ $absences }}</p></div>
        </div>
        <section>
            <h2 class="mb-3 text-lg font-semibold">Recent requests</h2>
            @include('partials.requests-table', ['leaveRequests' => $requests, 'showEmployee' => true, 'actions' => 'admin'])
        </section>
    </div>
</x-layouts.app>
