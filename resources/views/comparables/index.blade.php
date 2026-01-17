@extends('layouts.app')
@section('content')
  <div class="flex items-center justify-between">
    <div>
      <h1 class="text-2xl font-semibold">Amostras — Estudo #{{ $estudo->id }}</h1>
      <div class="text-sm text-slate-600">{{ $estudo->subject_address }}</div>
    </div>
    <div class="flex gap-2">
      <a class="px-3 py-2 rounded border" href="{{ route('estudos.edit', $estudo) }}">Voltar</a>
      <a class="px-3 py-2 rounded bg-slate-900 text-white" href="{{ route('estudos.amostras.create', $estudo) }}">Nova amostra</a>
    </div>
  </div>

  <div class="mt-4 grid gap-3">
    @forelse($comparables as $c)
      <div class="bg-white border rounded p-4 flex items-center justify-between">
        <div class="flex gap-3 items-center">
          <div class="w-16 h-16 bg-slate-100 rounded overflow-hidden">
            @if($c->main_photo_url)
              <img class="w-full h-full object-cover" src="{{ $c->main_photo_url }}" />
            @endif
          </div>
          <div>
            <div class="font-semibold">{{ $c->title ?? 'Amostra' }}</div>
            <div class="text-sm text-slate-600">
              R$ {{ number_format($c->price, 0, ',', '.') }} • {{ number_format($c->area_m2, 2, ',', '.') }} m² •
              R$ {{ number_format($c->price_per_m2, 0, ',', '.') }}/m²
            </div>
          </div>
        </div>
        <div class="flex gap-2">
          <a class="px-3 py-2 rounded border" href="{{ route('estudos.amostras.edit', [$estudo, $c]) }}">Editar</a>
          <form method="POST" action="{{ route('estudos.amostras.destroy', [$estudo, $c]) }}">
            @csrf @method('DELETE')
            <button class="px-3 py-2 rounded border text-red-600" type="submit">Excluir</button>
          </form>
        </div>
      </div>
    @empty
      <div class="bg-white border rounded p-4">Nenhuma amostra ainda.</div>
    @endforelse
  </div>
@endsection
