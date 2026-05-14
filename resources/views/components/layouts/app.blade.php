<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Employee Leave Tracker' }}</title>
    @unless(app()->environment('testing'))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endunless
</head>
<body class="min-h-dvh bg-slate-50 text-slate-900 antialiased">
    <a href="#main" class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-50 focus:rounded-md focus:bg-white focus:px-4 focus:py-2 focus:shadow">Skip to content</a>
    <div class="min-h-dvh">
        @auth
            <header class="border-b border-slate-200 bg-white">
                <div class="mx-auto flex max-w-7xl flex-col gap-4 px-4 py-4 sm:px-6 lg:flex-row lg:items-center lg:justify-between lg:px-8">
                    <div>
                        <a href="{{ route('dashboard') }}" class="text-lg font-semibold text-slate-950">Employee Leave Tracker</a>
                        <p class="text-sm text-slate-600">{{ auth()->user()->name }} · {{ ucfirst(auth()->user()->role) }}</p>
                    </div>
                    <nav class="flex flex-wrap items-center gap-2 text-sm" aria-label="Main navigation">
                        <a class="nav-link" href="{{ route('dashboard') }}">Dashboard</a>
                        @if(auth()->user()->isEmployee())
                            <a class="nav-link" href="{{ route('employee.requests.index') }}">My Requests</a>
                            <a class="nav-link" href="{{ route('employee.requests.create') }}">Submit Leave</a>
                        @endif
                        @if(auth()->user()->isManager())
                            <a class="nav-link" href="{{ route('manager.requests') }}">Team Requests</a>
                        @endif
                        @if(auth()->user()->isAdmin())
                            <a class="nav-link" href="{{ route('admin.employees') }}">Employees</a>
                            <a class="nav-link" href="{{ route('admin.leave-types') }}">Leave Types</a>
                            <a class="nav-link" href="{{ route('admin.balances') }}">Balances</a>
                            <a class="nav-link" href="{{ route('admin.requests') }}">Approvals</a>
                            <a class="nav-link" href="{{ route('admin.attendance') }}">Attendance</a>
                            <a class="nav-link" href="{{ route('admin.reports') }}">Reports</a>
                        @endif
                        <form method="post" action="{{ route('logout') }}">
                            @csrf
                            <button class="btn-secondary" type="submit">Sign out</button>
                        </form>
                    </nav>
                </div>
            </header>
        @endauth

        <main id="main" class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            @if(session('status'))
                <div class="mb-6 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900" role="status">
                    {{ session('status') }}
                </div>
            @endif

            @if($errors->any())
                <div class="mb-6 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-900" role="alert">
                    <p class="font-semibold">Please fix the following:</p>
                    <ul class="mt-2 list-disc pl-5">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{ $slot }}
        </main>
    </div>
</body>
</html>
