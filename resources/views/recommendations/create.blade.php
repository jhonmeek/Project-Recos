@extends('layouts.main')

@section('title', 'Nouvelle recommandation')

@section('content')
    <div class="mb-5">
        <h2 class="text-lg font-semibold">Nouvelle recommandation</h2>
        <p class="text-sm text-slate-500">Creation reservee au Controle interne. Tous les champs sont obligatoires.</p>
    </div>

    <form method="POST" action="{{ route('recommendations.store') }}" enctype="multipart/form-data">
        @csrf
        @include('recommendations._form', ['user' => auth()->user()])
    </form>
@endsection
