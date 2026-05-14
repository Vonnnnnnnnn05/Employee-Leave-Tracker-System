<x-layouts.app title="Team Requests">
    <div class="flex flex-col gap-6">
        <div>
            <h1 class="text-2xl font-semibold">Team requests</h1>
            <p class="text-sm text-slate-600">Approve or reject requests assigned to your team.</p>
        </div>
        @include('partials.requests-table', ['leaveRequests' => $leaveRequests, 'showEmployee' => true, 'actions' => 'manager'])
        {{ $leaveRequests->links() }}
    </div>
</x-layouts.app>
