@extends('layouts.app')
@section('content')
  <div class="flex items-center justify-between">
    <h1 class="text-2xl font-semibold">Estudos</h1>
    <a class="px-3 py-2 rounded bg-slate-900 text-white" href="{{ route('estudos.create') }}">Novo Estudo</a>
  </div>

  <div class="mt-4 grid gap-3">
    @foreach($studies as $s)
      <div class="bg-white border rounded p-4 flex items-center justify-between">
        <div>
          <div class="font-semibold">{{ $s->owner_name }} — {{ $s->subject_city }}/{{ $s->subject_state }}</div>
          <div class="text-sm text-slate-600">{{ $s->subject_address }}</div>
        </div>
        <div class="flex gap-2">
          <a class="px-3 py-2 rounded border" href="{{ route('studies.presentation', $s) }}">Apresentação</a>
          <a class="px-3 py-2 rounded border" href="{{ route('estudos.edit', $s) }}">Editar</a>
        </div>
      </div>
    @endforeach
  </div>

  <div class="mt-4">
    {{ $studies->links() }}
  </div>
@endsection
