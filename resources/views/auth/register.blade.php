<x-layouts.app title="Register">
    <div class="mx-auto max-w-xl">
        <div class="card">
            <h1 class="text-2xl font-semibold text-slate-950">Create employee account</h1>
            <form method="post" action="{{ route('register.store') }}" class="mt-6 grid gap-4 sm:grid-cols-2">
                @csrf
                <div class="sm:col-span-2">
                    <label class="form-label" for="name">Full name</label>
                    <input class="form-input" id="name" name="name" value="{{ old('name') }}" required>
                </div>
                <div class="sm:col-span-2">
                    <label class="form-label" for="email">Email</label>
                    <input class="form-input" id="email" name="email" type="email" value="{{ old('email') }}" required>
                </div>
                <div>
                    <label class="form-label" for="department">Department</label>
                    <input class="form-input" id="department" name="department" value="{{ old('department') }}">
                </div>
                <div>
                    <label class="form-label" for="position">Position</label>
                    <input class="form-input" id="position" name="position" value="{{ old('position') }}">
                </div>
                <div>
                    <label class="form-label" for="password">Password</label>
                    <input class="form-input" id="password" name="password" type="password" required>
                </div>
                <div>
                    <label class="form-label" for="password_confirmation">Confirm password</label>
                    <input class="form-input" id="password_confirmation" name="password_confirmation" type="password" required>
                </div>
                <div class="sm:col-span-2">
                    <button class="btn-primary w-full" type="submit">Create account</button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>
