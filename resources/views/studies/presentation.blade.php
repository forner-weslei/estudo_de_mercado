@extends('layouts.app')
@section('content')
@php
  $c1 = $brand->color_primary;
  $c2 = $brand->color_secondary;
@endphp

<div class="flex items-center justify-between">
  <div>
    <h1 class="text-2xl font-semibold">Apresentação — Estudo #{{ $study->id }}</h1>
    <div class="text-sm text-slate-600">{{ $study->subject_address }} • {{ $study->subject_city }}/{{ $study->subject_state }}</div>
  </div>
  <div class="flex gap-2">
    <a class="px-3 py-2 rounded border" href="{{ route('estudos.edit', $study) }}">Editar</a>
    <a class="px-3 py-2 rounded border" href="{{ route('estudos.amostras.index', $study) }}">Amostras</a>
    <a class="px-3 py-2 rounded bg-slate-900 text-white" href="{{ route('studies.pdf', $study) }}">Exportar PDF</a>
  </div>
</div>

<div class="mt-6 bg-white border rounded overflow-hidden">
  <div class="p-5" style="border-top: 6px solid {{ $c2 }};">
    <div class="flex items-center justify-between gap-4">
      <div class="flex items-center gap-3">
        @if($brand->logo_url)
          <img src="{{ $brand->logo_url }}" class="h-12">
        @else
          <div class="h-12 w-12 rounded bg-slate-200"></div>
        @endif
        <div>
          <div class="text-sm uppercase tracking-wide text-slate-500">Estudo de Mercado Avançado</div>
          <div class="text-xl font-semibold" style="color: {{ $c1 }};">{{ $brand->company_name }}</div>
        </div>
      </div>
      <div class="text-right text-sm text-slate-600">
        <div><span class="font-semibold">Proprietário:</span> {{ $study->owner_name }}</div>
        <div><span class="font-semibold">Corretor:</span> {{ $brand->agent_name }} @if($brand->creci) ({{ $brand->creci }}) @endif</div>
      </div>
    </div>
  </div>

  <div class="p-5 border-t">
    <h2 class="text-lg font-semibold" style="color: {{ $c1 }};">Visão geral do imóvel</h2>
    <div class="mt-3 grid grid-cols-2 md:grid-cols-4 gap-3">
      <div class="border rounded p-3"><div class="text-xs text-slate-500">{{ $study->areaLabel() }}</div><div class="text-lg font-semibold">{{ number_format($study->subject_area_m2, 2, ',', '.') }} m²</div></div>
      @if($study->isLand())
        <div class="border rounded p-3"><div class="text-xs text-slate-500">Dormitórios</div><div class="text-lg font-semibold">Não se aplica</div></div>
        <div class="border rounded p-3"><div class="text-xs text-slate-500">Suítes</div><div class="text-lg font-semibold">Não se aplica</div></div>
        <div class="border rounded p-3"><div class="text-xs text-slate-500">Vagas</div><div class="text-lg font-semibold">Não se aplica</div></div>
      @else
        <div class="border rounded p-3"><div class="text-xs text-slate-500">Dormitórios</div><div class="text-lg font-semibold">{{ $study->subject_bedrooms ?? '-' }}</div></div>
        <div class="border rounded p-3"><div class="text-xs text-slate-500">Suítes</div><div class="text-lg font-semibold">{{ $study->subject_suites ?? '-' }}</div></div>
        <div class="border rounded p-3"><div class="text-xs text-slate-500">Vagas</div><div class="text-lg font-semibold">{{ $study->subject_parking ?? '-' }}</div></div>
      @endif
    </div>

    @if($study->notes)
      <div class="mt-4">
        <div class="text-sm font-semibold">O que identifiquei no imóvel</div>
        <div class="text-slate-700 whitespace-pre-line">{{ $study->notes }}</div>
      </div>
    @endif
  </div>

  <div class="p-5 border-t">
    <h2 class="text-lg font-semibold" style="color: {{ $c1 }};">Amostras de mercado</h2>
    <div class="mt-3 grid sm:grid-cols-2 lg:grid-cols-3 gap-3">
      @foreach($study->comparables as $c)
        <div class="border rounded overflow-hidden">
          <div class="h-40 bg-slate-100">
            @if($c->main_photo_url)
              <img class="w-full h-full object-cover" src="{{ $c->main_photo_url }}">
            @endif
          </div>
          <div class="p-3">
            <div class="font-semibold">{{ $c->title ?? 'Amostra' }}</div>
            <div class="text-sm text-slate-600">
              R$ {{ number_format($c->price, 0, ',', '.') }} • {{ number_format($c->area_m2, 2, ',', '.') }} m² •
              R$ {{ number_format($c->price_per_m2, 0, ',', '.') }}/m²
            </div>
            <div class="text-xs text-slate-500 mt-1">{{ $c->address }}</div>
          </div>
        </div>
      @endforeach
    </div>
  </div>

  <div class="p-5 border-t">
    <h2 class="text-lg font-semibold" style="color: {{ $c1 }};">Cenários de preço</h2>
    <div class="mt-3 grid md:grid-cols-3 gap-3">
      <div class="border rounded p-4">
        <div class="text-sm font-semibold" style="color: {{ $c1 }};">Otimista ({{ number_format($study->pct_optimistic,2,',','.') }}%)</div>
        <div class="text-2xl font-bold">R$ {{ number_format($computed['scenarios']['optimistic'], 0, ',', '.') }}</div>
        <div class="text-sm text-slate-600">R$ {{ number_format($computed['scenarios_m2']['optimistic'], 0, ',', '.') }}/m²{{ $study->isLand() ? ' (terreno)' : '' }}</div>
      </div>
      <div class="border rounded p-4" style="border-color: {{ $c2 }};">
        <div class="text-sm font-semibold" style="color: {{ $c2 }};">Mercado ({{ number_format($study->pct_market,2,',','.') }}%)</div>
        <div class="text-2xl font-bold">R$ {{ number_format($computed['scenarios']['market'], 0, ',', '.') }}</div>
        <div class="text-sm text-slate-600">R$ {{ number_format($computed['scenarios_m2']['market'], 0, ',', '.') }}/m²{{ $study->isLand() ? ' (terreno)' : '' }}</div>
      </div>
      <div class="border rounded p-4">
        <div class="text-sm font-semibold text-slate-700">Competitivo ({{ number_format($study->pct_competitive,2,',','.') }}%)</div>
        <div class="text-2xl font-bold">R$ {{ number_format($computed['scenarios']['competitive'], 0, ',', '.') }}</div>
        <div class="text-sm text-slate-600">R$ {{ number_format($computed['scenarios_m2']['competitive'], 0, ',', '.') }}/m²{{ $study->isLand() ? ' (terreno)' : '' }}</div>
      </div>
    </div>

    <div class="mt-4 text-sm text-slate-600">
      <div><span class="font-semibold">Média das amostras:</span> R$ {{ number_format($computed['avg_total'], 0, ',', '.') }}</div>
      <div><span class="font-semibold">Média por m² (amostras):</span> R$ {{ number_format($computed['avg_m2'], 0, ',', '.') }}/m²{{ $study->isLand() ? ' (terreno)' : '' }}</div>
    </div>
  </div>

  <div class="p-5 border-t">
    <h2 class="text-lg font-semibold" style="color: {{ $c1 }};">Mapa (MVP)</h2>
    <p class="text-sm text-slate-600">No MVP, o mapa é exibido como exemplo. A geolocalização automática por endereço pode ser adicionada na próxima etapa.</p>
    <div id="map" class="mt-3 w-full h-72 rounded border"></div>
  </div>

  <div class="p-4 border-t text-sm text-slate-600 flex items-center justify-between">
    <div>{{ $brand->footer_text }}</div>
    <div class="text-right">
      <div>{{ $brand->agent_name }} @if($brand->creci) • {{ $brand->creci }} @endif</div>
      <div>{{ $brand->whatsapp ?? $brand->phone }} • {{ $brand->email }}</div>
    </div>
  </div>
</div>

<script>
  // mapa exemplo (Curitiba) - será substituído por geocoding
  const map = L.map('map').setView([-25.4284, -49.2733], 12);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(map);
  L.marker([-25.4284, -49.2733]).addTo(map).bindPopup('Imóvel avaliado (exemplo)');
</script>
@endsection
