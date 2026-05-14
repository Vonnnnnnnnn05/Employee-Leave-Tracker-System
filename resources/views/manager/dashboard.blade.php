<x-layouts.app title="Manager Dashboard">
    <div class="flex flex-col gap-6">
        <div>
            <h1 class="text-2xl font-semibold">Manager dashboard</h1>
            <p class="text-sm text-slate-600">Review team leave requests before HR finalization.</p>
        </div>
        <section>
            <h2 class="mb-3 text-lg font-semibold">Pending team approvals</h2>
            @include('partials.requests-table', ['leaveRequests' => $pendingRequests, 'showEmployee' => true, 'actions' => 'manager'])
        </section>
        <section>
            <h2 class="mb-3 text-lg font-semibold">Team leave history</h2>
            @include('partials.requests-table', ['leaveRequests' => $teamRequests, 'showEmployee' => true, 'actions' => null])
        </section>
    </div>
</x-layouts.app>
