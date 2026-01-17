<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #111827; }
    .header { border-top: 6px solid {{ $brand->color_secondary }}; padding: 16px; }
    .title { font-size: 18px; font-weight: 700; color: {{ $brand->color_primary }}; }
    .section { padding: 14px 16px; border-top: 1px solid #e5e7eb; }
    .grid { display: table; width: 100%; }
    .col { display: table-cell; width: 25%; padding: 8px; border: 1px solid #e5e7eb; vertical-align: top; }
    .muted { color: #6b7280; font-size: 11px; }
    .big { font-size: 16px; font-weight: 700; }
  </style>
</head>
<body>
  <div class="header">
    <div class="grid">
      <div style="display: table-cell; width: 70%;">
        <div class="muted">Estudo de Mercado Avançado</div>
        <div class="title">{{ $brand->company_name }}</div>
        <div class="muted">{{ $study->subject_address }} • {{ $study->subject_city }}/{{ $study->subject_state }}</div>
      </div>
      <div style="display: table-cell; width: 30%; text-align: right;">
        <div><strong>Proprietário:</strong> {{ $study->owner_name }}</div>
        <div><strong>Corretor:</strong> {{ $brand->agent_name }} @if($brand->creci) ({{ $brand->creci }}) @endif</div>
      </div>
    </div>
  </div>

  <div class="section">
    <strong>Visão geral do imóvel</strong><br>
    {{ $study->areaLabel() }}: {{ number_format($study->subject_area_m2, 2, ',', '.') }} m²
    @if($study->isLand())
      • Tipologia: Não se aplica (terreno)
    @else
      • Dorms: {{ $study->subject_bedrooms ?? '-' }} • Suítes: {{ $study->subject_suites ?? '-' }} • Vagas: {{ $study->subject_parking ?? '-' }}
    @endif
    @if($study->notes)
      <p><strong>Observações:</strong><br>{{ $study->notes }}</p>
    @endif
  </div>

  <div class="section">
    <strong>Cenários de preço</strong>
    <div class="grid" style="margin-top: 10px;">
      <div class="col">
        <div class="muted">Otimista ({{ number_format($study->pct_optimistic,2,',','.') }}%)</div>
        <div class="big">R$ {{ number_format($computed['scenarios']['optimistic'], 0, ',', '.') }}</div>
        <div class="muted">R$ {{ number_format($computed['scenarios_m2']['optimistic'], 0, ',', '.') }}/m²{{ $study->isLand() ? ' (terreno)' : '' }}</div>
      </div>
      <div class="col" style="border-color: {{ $brand->color_secondary }};">
        <div class="muted">Mercado ({{ number_format($study->pct_market,2,',','.') }}%)</div>
        <div class="big">R$ {{ number_format($computed['scenarios']['market'], 0, ',', '.') }}</div>
        <div class="muted">R$ {{ number_format($computed['scenarios_m2']['market'], 0, ',', '.') }}/m²{{ $study->isLand() ? ' (terreno)' : '' }}</div>
      </div>
      <div class="col">
        <div class="muted">Competitivo ({{ number_format($study->pct_competitive,2,',','.') }}%)</div>
        <div class="big">R$ {{ number_format($computed['scenarios']['competitive'], 0, ',', '.') }}</div>
        <div class="muted">R$ {{ number_format($computed['scenarios_m2']['competitive'], 0, ',', '.') }}/m²{{ $study->isLand() ? ' (terreno)' : '' }}</div>
      </div>
      <div class="col">
        <div class="muted">Média amostras</div>
        <div class="big">R$ {{ number_format($computed['avg_total'], 0, ',', '.') }}</div>
        <div class="muted">R$ {{ number_format($computed['avg_m2'], 0, ',', '.') }}/m²{{ $study->isLand() ? ' (terreno)' : '' }}</div>
      </div>
    </div>
  </div>

  <div class="section">
    <strong>Amostras</strong>
    <table width="100%" cellspacing="0" cellpadding="6" style="margin-top: 8px; border-collapse: collapse;">
      <thead>
        <tr style="background:#f3f4f6;">
          <th align="left">Título</th>
          <th align="right">Preço</th>
          <th align="right">Área</th>
          <th align="right">R$/m²</th>
        </tr>
      </thead>
      <tbody>
        @foreach($study->comparables as $c)
          <tr>
            <td style="border-top:1px solid #e5e7eb;">{{ $c->title ?? 'Amostra' }}</td>
            <td align="right" style="border-top:1px solid #e5e7eb;">R$ {{ number_format($c->price, 0, ',', '.') }}</td>
            <td align="right" style="border-top:1px solid #e5e7eb;">{{ number_format($c->area_m2, 2, ',', '.') }} m²</td>
            <td align="right" style="border-top:1px solid #e5e7eb;">R$ {{ number_format($c->price_per_m2, 0, ',', '.') }}</td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>

  <div class="section" style="font-size: 10px; color: #6b7280;">
    {{ $brand->footer_text }} • {{ $brand->agent_name }} @if($brand->creci) • {{ $brand->creci }} @endif
  </div>
</body>
</html>
