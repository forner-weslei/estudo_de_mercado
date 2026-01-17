<form class="mt-4 bg-white border rounded p-4" method="POST" action="{{ $action }}" enctype="multipart/form-data">
  @csrf
  @if($method !== 'POST')
    @method($method)
  @endif

  <div class="grid md:grid-cols-2 gap-4">
    <div>
      <label class="block text-sm">Título</label>
      <input class="w-full border rounded px-3 py-2" name="title" value="{{ old('title', $amostra->title ?? '') }}">
      <label class="block text-sm mt-2">Endereço/Referência</label>
      <input class="w-full border rounded px-3 py-2" name="address" value="{{ old('address', $amostra->address ?? '') }}">
      <div class="grid grid-cols-2 gap-2 mt-2">
        <div>
          <label class="block text-sm">Preço (R$)</label>
          <input class="w-full border rounded px-3 py-2" type="number" step="0.01" name="price" value="{{ old('price', $amostra->price ?? '') }}" required>
        </div>
        <div>
          <label class="block text-sm">Área (m²)</label>
          <input class="w-full border rounded px-3 py-2" type="number" step="0.01" name="area_m2" value="{{ old('area_m2', $amostra->area_m2 ?? '') }}" required>
        </div>
      </div>
      <div class="grid grid-cols-4 gap-2 mt-2">
        <div><label class="block text-xs">Dorms</label><input class="w-full border rounded px-2 py-2" type="number" name="bedrooms" value="{{ old('bedrooms', $amostra->bedrooms ?? '') }}"></div>
        <div><label class="block text-xs">Suítes</label><input class="w-full border rounded px-2 py-2" type="number" name="suites" value="{{ old('suites', $amostra->suites ?? '') }}"></div>
        <div><label class="block text-xs">Vagas</label><input class="w-full border rounded px-2 py-2" type="number" name="parking" value="{{ old('parking', $amostra->parking ?? '') }}"></div>
        <div><label class="block text-xs">Banheiros</label><input class="w-full border rounded px-2 py-2" type="number" name="bathrooms" value="{{ old('bathrooms', $amostra->bathrooms ?? '') }}"></div>
      </div>

      <label class="inline-flex items-center gap-2 mt-3">
        <input type="checkbox" name="include_in_calc" value="1" @checked(old('include_in_calc', $amostra->include_in_calc ?? true))>
        <span class="text-sm">Incluir esta amostra nos cálculos</span>
      </label>
    </div>

    <div>
      <label class="block text-sm">Foto principal</label>
      <input class="w-full" type="file" name="photo" accept="image/*">
      @if($amostra && $amostra->main_photo_url)
        <div class="mt-3 w-full max-w-sm border rounded overflow-hidden">
          <img class="w-full h-56 object-cover" src="{{ $amostra->main_photo_url }}">
        </div>
      @endif
      <div class="mt-3 flex gap-2">
        <a class="px-3 py-2 rounded border" href="{{ route('estudos.amostras.index', $estudo) }}">Voltar</a>
        <button class="px-3 py-2 rounded bg-slate-900 text-white" type="submit">Salvar</button>
      </div>
    </div>
  </div>
</form>
