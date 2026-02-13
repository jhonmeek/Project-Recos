@extends('layouts.main')

@section('title', 'Modifier recommandation')

@section('content')
    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="text-lg font-semibold">Modifier la recommandation #{{ $recommendation->order_number }}</h2>
            <p class="text-sm text-slate-500">@if($user->isControleInterne())Edition complete.@else Mise a jour de l'avancement uniquement.@endif</p>
        </div>
    </div>

    <form method="POST" action="{{ route('recommendations.update', $recommendation) }}" enctype="multipart/form-data" class="space-y-4">
        @csrf
        @method('PUT')
        @include('recommendations._form', ['recommendation' => $recommendation, 'user' => $user])
    </form>

    @if($user->isControleInterne())
        <form method="POST" action="{{ route('recommendations.destroy', $recommendation) }}" class="mt-4">
            @csrf
            @method('DELETE')
            <button type="submit" onclick="return confirm('Supprimer cette recommandation ?')" class="rounded-md bg-rose-600 px-4 py-2 text-sm font-medium text-white hover:bg-rose-700">Supprimer</button>
        </form>
    @endif
@endsection
