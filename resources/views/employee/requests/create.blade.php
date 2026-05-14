<x-layouts.app title="Submit Leave">
    <div class="grid gap-6 lg:grid-cols-[1fr_360px]">
        <section class="card">
            <h1 class="text-2xl font-semibold">Submit leave request</h1>
            <form method="post" action="{{ route('employee.requests.store') }}" class="mt-6 grid gap-4 sm:grid-cols-2">
                @csrf
                <div class="sm:col-span-2">
                    <label class="form-label" for="leave_type_id">Leave type</label>
                    <select class="form-input" id="leave_type_id" name="leave_type_id" required>
                        <option value="">Select leave type</option>
                        @foreach($leaveTypes as $type)
                            <option value="{{ $type->id }}" @selected(old('leave_type_id') == $type->id)>{{ $type->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label" for="start_date">Start date</label>
                    <input class="form-input" id="start_date" name="start_date" type="date" value="{{ old('start_date') }}" required>
                </div>
                <div>
                    <label class="form-label" for="end_date">End date</label>
                    <input class="form-input" id="end_date" name="end_date" type="date" value="{{ old('end_date') }}" required>
                </div>
                <div class="sm:col-span-2">
                    <label class="form-label" for="reason">Reason</label>
                    <textarea class="form-input" id="reason" name="reason" rows="5" required>{{ old('reason') }}</textarea>
                </div>
                <div class="sm:col-span-2">
                    <button class="btn-primary" type="submit">Submit for approval</button>
                </div>
            </form>
        </section>
        <aside class="card">
            <h2 class="text-lg font-semibold">Current balances</h2>
            <div class="mt-4 space-y-3">
                @foreach($balances as $balance)
                    <div class="flex items-center justify-between border-b border-slate-200 pb-3">
                        <span class="font-medium">{{ $balance->leaveType->name }}</span>
                        <span class="badge">{{ $balance->remainingDays() }} days</span>
                    </div>
                @endforeach
            </div>
        </aside>
    </div>
</x-layouts.app>
