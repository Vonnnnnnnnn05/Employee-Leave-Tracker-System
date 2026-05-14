<x-layouts.app title="My Leave Requests">
    <div class="flex flex-col gap-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-semibold">My leave requests</h1>
                <p class="text-sm text-slate-600">Track current and past leave applications.</p>
            </div>
            <a class="btn-primary" href="{{ route('employee.requests.create') }}">New request</a>
        </div>
        @include('partials.requests-table', ['leaveRequests' => $leaveRequests, 'showEmployee' => false, 'actions' => 'employee'])
        {{ $leaveRequests->links() }}
    </div>
</x-layouts.app>
