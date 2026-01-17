<form class="mt-4 bg-white border rounded p-4" method="POST" action="{{ $action }}" enctype="multipart/form-data">
  @csrf
  @if($method !== 'POST')
    @method($method)
  @endif

  <div class="grid md:grid-cols-2 gap-4">
    <div>
      <h2 class="font-semibold mb-2">Proprietário</h2>
      <label class="block text-sm">Nome</label>
      <input class="w-full border rounded px-3 py-2" name="owner_name" value="{{ old('owner_name', $study->owner_name ?? '') }}" required>
      <label class="block text-sm mt-2">Contato</label>
      <input class="w-full border rounded px-3 py-2" name="owner_contact" value="{{ old('owner_contact', $study->owner_contact ?? '') }}">
    </div>

    <div>
      <h2 class="font-semibold mb-2">Imóvel Avaliado</h2>
      <label class="block text-sm">Tipo de imóvel</label>
      @php $stype = old('subject_type', $study->subject_type ?? 'house'); @endphp
      <select id="subject_type" class="w-full border rounded px-3 py-2" name="subject_type" required>
        <option value="house" @selected($stype==='house')>Casa / Sobrado</option>
        <option value="apartment" @selected($stype==='apartment')>Apartamento</option>
        <option value="commercial" @selected($stype==='commercial')>Comercial</option>
        <option value="land" @selected($stype==='land')>Terreno / Lote</option>
      </select>

      <label class="block text-sm">Endereço</label>
      <input class="w-full border rounded px-3 py-2" name="subject_address" value="{{ old('subject_address', $study->subject_address ?? '') }}" required>
      <div class="grid grid-cols-2 gap-2 mt-2">
        <div>
          <label class="block text-sm">Bairro</label>
          <input class="w-full border rounded px-3 py-2" name="subject_neighborhood" value="{{ old('subject_neighborhood', $study->subject_neighborhood ?? '') }}">
        </div>
        <div>
          <label class="block text-sm">Cidade</label>
          <input class="w-full border rounded px-3 py-2" name="subject_city" value="{{ old('subject_city', $study->subject_city ?? '') }}" required>
        </div>
      </div>
      <div class="grid grid-cols-2 gap-2 mt-2">
        <div>
          <label class="block text-sm">UF</label>
          <input class="w-full border rounded px-3 py-2" name="subject_state" value="{{ old('subject_state', $study->subject_state ?? '') }}" maxlength="2" required>
        </div>
        <div>
          <label id="area_label" class="block text-sm">Área privativa (m²)</label>
          <input class="w-full border rounded px-3 py-2" type="number" step="0.01" name="subject_area_m2" value="{{ old('subject_area_m2', $study->subject_area_m2 ?? '') }}" required>
        </div>
      </div>
      <div id="rooms_grid" class="grid grid-cols-3 gap-2 mt-2">
        <div>
          <label class="block text-sm">Dorms</label>
          <input class="w-full border rounded px-3 py-2" type="number" name="subject_bedrooms" value="{{ old('subject_bedrooms', $study->subject_bedrooms ?? '') }}">
        </div>
        <div>
          <label class="block text-sm">Suítes</label>
          <input class="w-full border rounded px-3 py-2" type="number" name="subject_suites" value="{{ old('subject_suites', $study->subject_suites ?? '') }}">
        </div>
        <div>
          <label class="block text-sm">Vagas</label>
          <input class="w-full border rounded px-3 py-2" type="number" name="subject_parking" value="{{ old('subject_parking', $study->subject_parking ?? '') }}">
        </div>
      </div>
    </div>
  </div>

  <div class="mt-4">
    <h2 class="font-semibold mb-2">Observações</h2>
    <textarea class="w-full border rounded px-3 py-2" rows="4" name="notes">{{ old('notes', $study->notes ?? '') }}</textarea>
  </div>

  <div class="mt-4 grid md:grid-cols-2 gap-4">
    <div class="bg-slate-50 border rounded p-3">
      <h2 class="font-semibold mb-2">Regras de cálculo (percentuais configuráveis)</h2>
      <label class="block text-sm">Base de cálculo</label>
      <select class="w-full border rounded px-3 py-2" name="scenario_base" required>
        @php $base = old('scenario_base', $study->scenario_base ?? 'avg_total'); @endphp
        <option value="avg_total" @selected($base==='avg_total')>Preço médio das amostras</option>
        <option value="avg_m2" @selected($base==='avg_m2')>Preço médio por m² (multiplica pela área do imóvel avaliado)</option>
        <option value="lowest_total" @selected($base==='lowest_total')>Menor preço das amostras</option>
        <option value="manual_total" @selected($base==='manual_total')>Valor manual</option>
      </select>
      <label class="block text-sm mt-2">Valor manual (se escolher “Valor manual”)</label>
      <input class="w-full border rounded px-3 py-2" type="number" step="0.01" name="manual_total_price" value="{{ old('manual_total_price', $study->manual_total_price ?? '') }}">

      <div class="grid grid-cols-3 gap-2 mt-3">
        <div>
          <label class="block text-xs">Otimista (%)</label>
          <input class="w-full border rounded px-2 py-2" type="number" step="0.01" name="pct_optimistic" value="{{ old('pct_optimistic', $study->pct_optimistic ?? 5) }}" required>
        </div>
        <div>
          <label class="block text-xs">Mercado (%)</label>
          <input class="w-full border rounded px-2 py-2" type="number" step="0.01" name="pct_market" value="{{ old('pct_market', $study->pct_market ?? 0) }}" required>
        </div>
        <div>
          <label class="block text-xs">Competitivo (%)</label>
          <input class="w-full border rounded px-2 py-2" type="number" step="0.01" name="pct_competitive" value="{{ old('pct_competitive', $study->pct_competitive ?? -8) }}" required>
        </div>
      </div>
    </div>

    <div class="bg-slate-50 border rounded p-3">
      <h2 class="font-semibold mb-2">Marca por estudo (opcional)</h2>
      <p class="text-sm text-slate-600">Se quiser, você pode sobrescrever a sua marca só nesse estudo (logo, cores e rodapé).</p>
      <label class="inline-flex items-center gap-2 mt-2">
        <input type="checkbox" name="override_branding" value="1" @checked(old('override_branding', $study->override_branding ?? false))>
        <span class="text-sm">Usar marca personalizada neste estudo</span>
      </label>
      <p class="text-xs text-slate-500 mt-2">No MVP, a edição completa da marca por estudo pode ser adicionada na próxima iteração.</p>
    </div>
  </div>

  <div class="mt-4 flex gap-2">
    <button class="px-4 py-2 rounded bg-slate-900 text-white" type="submit">Salvar</button>
    @if($study)
      <a class="px-4 py-2 rounded border" href="{{ route('estudos.amostras.index', $study) }}">Amostras</a>
    @endif
  </div>
</form>

<script>
  // Terrenos: esconder campos de tipologia e trocar label de área (sem impactar casas/apartamentos)
  (function(){
    const typeSel = document.getElementById('subject_type');
    const rooms = document.getElementById('rooms_grid');
    const areaLabel = document.getElementById('area_label');

    function applyTypeUI() {
      const isLand = typeSel.value === 'land';
      rooms.style.display = isLand ? 'none' : '';
      areaLabel.textContent = isLand ? 'Área do terreno (m²)' : 'Área privativa (m²)';
    }

    typeSel.addEventListener('change', applyTypeUI);
    applyTypeUI();
  })();
</script>
