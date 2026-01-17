@extends('layouts.app')
@section('content')
  <h1 class="text-2xl font-semibold">Editar Amostra</h1>
  @include('comparables.partials.form', ['estudo' => $estudo, 'amostra' => $amostra, 'action' => route('estudos.amostras.update', [$estudo, $amostra]), 'method' => 'PUT'])
@endsection
