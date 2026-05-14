<x-layouts.app title="Sign in">
    <div class="mx-auto max-w-md">
        <div class="card">
            <h1 class="text-2xl font-semibold text-slate-950">Sign in</h1>
            <p class="mt-1 text-sm text-slate-600">Access leave requests, approvals, balances, and reports.</p>

            <form method="post" action="{{ route('login.store') }}" class="mt-6 space-y-4">
                @csrf
                <div>
                    <label class="form-label" for="email">Email</label>
                    <input class="form-input" id="email" name="email" type="email" autocomplete="email" value="{{ old('email') }}" required>
                </div>
                <div>
                    <label class="form-label" for="password">Password</label>
                    <input class="form-input" id="password" name="password" type="password" autocomplete="current-password" required>
                </div>
                <label class="flex items-center gap-2 text-sm text-slate-700">
                    <input class="rounded border-slate-300 text-emerald-700 focus:ring-emerald-600" type="checkbox" name="remember" value="1">
                    Remember me
                </label>
                <button class="btn-primary w-full" type="submit">Sign in</button>
            </form>

            <p class="mt-4 text-sm text-slate-600">
                New employee?
                <a class="font-semibold text-emerald-800 underline" href="{{ route('register') }}">Create an account</a>
            </p>
        </div>
    </div>
</x-layouts.app>
