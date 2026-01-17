@extends('layouts.app')
@section('content')
  <h1 class="text-2xl font-semibold">Nova Amostra — Estudo #{{ $estudo->id }}</h1>
  @include('comparables.partials.form', ['estudo' => $estudo, 'amostra' => null, 'action' => route('estudos.amostras.store', $estudo), 'method' => 'POST'])
@endsection
