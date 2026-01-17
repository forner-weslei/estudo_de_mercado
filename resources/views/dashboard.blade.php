@extends('layouts.app')

@section('content')
  <div class="flex items-center justify-between">
    <h1 class="text-2xl font-semibold">Dashboard</h1>
    <a class="px-3 py-2 rounded bg-slate-900 text-white" href="{{ route('estudos.create') }}">Novo Estudo</a>
  </div>
  <p class="mt-3 text-slate-600">Crie estudos, adicione amostras e apresente para o cliente direto no sistema.</p>
@endsection
