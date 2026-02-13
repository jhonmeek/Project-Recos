<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Suivi recommandations')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 text-slate-900">
    <header class="border-b border-slate-200 bg-white">
        <div class="mx-auto flex w-full max-w-7xl flex-wrap items-center justify-between gap-4 px-4 py-4 sm:px-6 lg:px-8">
            <div>
                <p class="text-sm font-semibold uppercase tracking-wider text-teal-700">ANBG</p>
                <h1 class="text-lg font-semibold">Suivi des recommandations DG</h1>
            </div>

            <nav class="flex flex-wrap items-center gap-2 text-sm">
                <a href="{{ route('dashboard') }}" class="rounded-md px-3 py-2 {{ request()->routeIs('dashboard') ? 'bg-teal-700 text-white' : 'bg-slate-200 text-slate-700 hover:bg-slate-300' }}">Statistiques</a>
                <a href="{{ route('performance.index') }}" class="rounded-md px-3 py-2 {{ request()->routeIs('performance.*') ? 'bg-teal-700 text-white' : 'bg-slate-200 text-slate-700 hover:bg-slate-300' }}">Graphiques</a>
                <a href="{{ route('recommendations.index') }}" class="rounded-md px-3 py-2 {{ request()->routeIs('recommendations.*') ? 'bg-teal-700 text-white' : 'bg-slate-200 text-slate-700 hover:bg-slate-300' }}">Recommandations</a>
                @if(auth()->user()?->isControleInterne())
                    <a href="{{ route('recommendations.create') }}" class="rounded-md bg-teal-700 px-3 py-2 text-white hover:bg-teal-800">Nouvelle</a>
                @endif
                <a href="{{ route('profile.edit') }}" class="rounded-md bg-slate-200 px-3 py-2 text-slate-700 hover:bg-slate-300">Profil</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="rounded-md bg-rose-600 px-3 py-2 text-white hover:bg-rose-700">Se deconnecter</button>
                </form>
            </nav>
        </div>
    </header>

    <main class="mx-auto w-full max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
        <div class="mb-6 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-sm text-slate-500">
                Connecte en tant que <span class="font-semibold text-slate-800">{{ auth()->user()?->name }}</span>
                ({{ auth()->user()?->roleLabel() }})
                @if(auth()->user()?->isDirecteur())
                    - Direction: <span class="font-semibold text-slate-800">{{ auth()->user()?->direction ?? 'Non definie' }}</span>
                @endif
            </p>
        </div>

        @if (session('success'))
            <div class="mb-6 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-700">{{ session('success') }}</div>
        @endif

        @if ($errors->any())
            <div class="mb-6 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-rose-700">
                <p class="font-medium">Erreurs de validation :</p>
                <ul class="mt-2 list-disc space-y-1 pl-6 text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>
</body>
</html>
