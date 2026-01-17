@extends('layouts.app')
@section('content')
  <h1 class="text-2xl font-semibold">Novo Estudo</h1>
  @include('studies.partials.form', ['study' => null, 'action' => route('estudos.store'), 'method' => 'POST'])
@endsection
