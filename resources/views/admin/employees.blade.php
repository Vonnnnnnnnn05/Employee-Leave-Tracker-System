<x-layouts.app title="Employees">
    <div class="grid gap-6 lg:grid-cols-[380px_1fr]">
        <section class="card">
            <h1 class="text-xl font-semibold">Add employee</h1>
            <form method="post" action="{{ route('admin.employees.store') }}" class="mt-4 space-y-4">
                @csrf
                <div><label class="form-label" for="name">Name</label><input class="form-input" id="name" name="name" required></div>
                <div><label class="form-label" for="email">Email</label><input class="form-input" id="email" name="email" type="email" required></div>
                <div><label class="form-label" for="password">Password</label><input class="form-input" id="password" name="password" type="password" required></div>
                <div>
                    <label class="form-label" for="role">Role</label>
                    <select class="form-input" id="role" name="role" required>
                        <option value="employee">Employee</option>
                        <option value="manager">Manager</option>
                        <option value="admin">Admin/HR</option>
                    </select>
                </div>
                <div><label class="form-label" for="department">Department</label><input class="form-input" id="department" name="department"></div>
                <div>
                    <label class="form-label" for="manager_id">Manager</label>
                    <select class="form-input" id="manager_id" name="manager_id">
                        <option value="">No manager</option>
                        @foreach($managers as $manager)
                            <option value="{{ $manager->id }}">{{ $manager->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div><label class="form-label" for="position">Position</label><input class="form-input" id="position" name="position"></div>
                <div><label class="form-label" for="hire_date">Hire date</label><input class="form-input" id="hire_date" name="hire_date" type="date"></div>
                <input type="hidden" name="employment_status" value="active">
                <button class="btn-primary w-full" type="submit">Create employee</button>
            </form>
        </section>

        <section>
            <h2 class="mb-3 text-xl font-semibold">Employee directory</h2>
            <div class="table-wrap">
                <table class="data-table">
                    <thead><tr><th>Name</th><th>Role</th><th>Department</th><th>Manager</th><th>Status</th><th>Update</th></tr></thead>
                    <tbody class="divide-y divide-slate-200">
                        @foreach($employees as $employee)
                            <tr>
                                <td><p class="font-semibold">{{ $employee->name }}</p><p class="text-xs text-slate-500">{{ $employee->email }}</p></td>
                                <td>{{ ucfirst($employee->role) }}</td>
                                <td>{{ $employee->department ?? 'None' }}</td>
                                <td>{{ $employee->manager->name ?? 'None' }}</td>
                                <td>{{ ucfirst($employee->employment_status) }}</td>
                                <td>
                                    <form method="post" action="{{ route('admin.employees.update', $employee) }}" class="grid min-w-80 gap-2">
                                        @csrf
                                        @method('PATCH')
                                        <select class="form-input mt-0" name="role">
                                            @foreach(['employee', 'manager', 'admin'] as $role)
                                                <option value="{{ $role }}" @selected($employee->role === $role)>{{ ucfirst($role) }}</option>
                                            @endforeach
                                        </select>
                                        <input class="form-input mt-0" name="department" value="{{ $employee->department }}" placeholder="Department">
                                        <select class="form-input mt-0" name="manager_id">
                                            <option value="">No manager</option>
                                            @foreach($managers as $manager)
                                                <option value="{{ $manager->id }}" @selected($employee->manager_id === $manager->id)>{{ $manager->name }}</option>
                                            @endforeach
                                        </select>
                                        <input class="form-input mt-0" name="position" value="{{ $employee->position }}" placeholder="Position">
                                        <select class="form-input mt-0" name="employment_status">
                                            <option value="active" @selected($employee->employment_status === 'active')>Active</option>
                                            <option value="inactive" @selected($employee->employment_status === 'inactive')>Inactive</option>
                                        </select>
                                        <input class="form-input mt-0" name="hire_date" type="date" value="{{ optional($employee->hire_date)->format('Y-m-d') }}">
                                        <button class="btn-secondary" type="submit">Save</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $employees->links() }}</div>
        </section>
    </div>
</x-layouts.app>
