<x-layouts.app title="Leave Types">
    <div class="grid gap-6 lg:grid-cols-[360px_1fr]">
        <section class="card">
            <h1 class="text-xl font-semibold">Create leave type</h1>
            <form method="post" action="{{ route('admin.leave-types.store') }}" class="mt-4 space-y-4">
                @csrf
                <div><label class="form-label" for="name">Name</label><input class="form-input" id="name" name="name" required></div>
                <div><label class="form-label" for="default_days">Default days</label><input class="form-input" id="default_days" name="default_days" type="number" min="0" value="0" required></div>
                <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_paid" value="1" checked> Paid leave</label>
                <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="requires_balance" value="1" checked> Requires balance</label>
                <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_active" value="1" checked> Active</label>
                <button class="btn-primary w-full" type="submit">Create type</button>
            </form>
        </section>
        <section>
            <h2 class="mb-3 text-xl font-semibold">Leave types</h2>
            <div class="table-wrap">
                <table class="data-table">
                    <thead><tr><th>Name</th><th>Default days</th><th>Paid</th><th>Requires balance</th><th>Status</th></tr></thead>
                    <tbody class="divide-y divide-slate-200">
                        @foreach($leaveTypes as $type)
                            <tr>
                                <td>{{ $type->name }}</td>
                                <td>{{ $type->default_days }}</td>
                                <td>{{ $type->is_paid ? 'Yes' : 'No' }}</td>
                                <td>{{ $type->requires_balance ? 'Yes' : 'No' }}</td>
                                <td>{{ $type->is_active ? 'Active' : 'Inactive' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</x-layouts.app>
