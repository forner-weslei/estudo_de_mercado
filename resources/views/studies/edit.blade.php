@extends('layouts.app')
@section('content')
  <div class="flex items-center justify-between">
    <h1 class="text-2xl font-semibold">Editar Estudo #{{ $study->id }}</h1>
    <a class="px-3 py-2 rounded bg-slate-900 text-white" href="{{ route('studies.presentation', $study) }}">Ver Apresentação</a>
  </div>

  <div class="mt-4">
    <a class="underline text-sm" href="{{ route('estudos.amostras.index', $study) }}">Gerenciar Amostras</a>
  </div>

  @include('studies.partials.form', ['study' => $study, 'action' => route('estudos.update', $study), 'method' => 'PUT'])
@endsection
