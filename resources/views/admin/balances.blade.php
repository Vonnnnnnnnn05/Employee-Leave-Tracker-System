<x-layouts.app title="Leave Balances">
    <div class="grid gap-6 lg:grid-cols-[360px_1fr]">
        <section class="card">
            <h1 class="text-xl font-semibold">Adjust balance</h1>
            <form method="post" action="{{ route('admin.balances.update') }}" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label class="form-label" for="user_id">Employee</label>
                    <select class="form-input" id="user_id" name="user_id" required>
                        @foreach($employees as $employee)
                            <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label" for="leave_type_id">Leave type</label>
                    <select class="form-input" id="leave_type_id" name="leave_type_id" required>
                        @foreach($leaveTypes as $type)
                            <option value="{{ $type->id }}">{{ $type->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div><label class="form-label" for="available_days">Available days</label><input class="form-input" id="available_days" name="available_days" type="number" min="0" required></div>
                <button class="btn-primary w-full" type="submit">Save balance</button>
            </form>
        </section>
        <section>
            <h2 class="mb-3 text-xl font-semibold">Balances</h2>
            <div class="table-wrap">
                <table class="data-table">
                    <thead><tr><th>Employee</th><th>Leave type</th><th>Available</th><th>Used</th><th>Pending</th><th>Remaining</th></tr></thead>
                    <tbody class="divide-y divide-slate-200">
                        @foreach($balances as $balance)
                            <tr>
                                <td>{{ $balance->user->name }}</td>
                                <td>{{ $balance->leaveType->name }}</td>
                                <td>{{ $balance->available_days }}</td>
                                <td>{{ $balance->used_days }}</td>
                                <td>{{ $balance->pending_days }}</td>
                                <td>{{ $balance->remainingDays() }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</x-layouts.app>
