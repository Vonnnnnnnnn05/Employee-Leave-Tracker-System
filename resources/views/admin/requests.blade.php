<x-layouts.app title="HR Approvals">
    <div class="flex flex-col gap-6">
        <div>
            <h1 class="text-2xl font-semibold">HR approvals</h1>
            <p class="text-sm text-slate-600">Finalize manager-approved requests and manage cancellations.</p>
        </div>
        @include('partials.requests-table', ['leaveRequests' => $leaveRequests, 'showEmployee' => true, 'actions' => 'admin'])
        {{ $leaveRequests->links() }}
    </div>
</x-layouts.app>
