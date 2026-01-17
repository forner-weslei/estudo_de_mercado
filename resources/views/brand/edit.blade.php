@extends('layouts.app')
@section('content')
  <h1 class="text-2xl font-semibold">Minha Marca</h1>
  <form class="mt-4 bg-white border rounded p-4" method="POST" action="{{ route('brand.update') }}" enctype="multipart/form-data">
    @csrf
    <div class="grid md:grid-cols-2 gap-4">
      <div>
        <label class="block text-sm">Nome da empresa</label>
        <input class="w-full border rounded px-3 py-2" name="company_name" value="{{ old('company_name', $brand->company_name ?? '') }}" required>
        <label class="block text-sm mt-2">Nome do corretor</label>
        <input class="w-full border rounded px-3 py-2" name="agent_name" value="{{ old('agent_name', $brand->agent_name ?? '') }}" required>

        <div class="grid grid-cols-2 gap-2 mt-2">
          <div>
            <label class="block text-sm">CRECI</label>
            <input class="w-full border rounded px-3 py-2" name="creci" value="{{ old('creci', $brand->creci ?? '') }}">
          </div>
          <div>
            <label class="block text-sm">Telefone</label>
            <input class="w-full border rounded px-3 py-2" name="phone" value="{{ old('phone', $brand->phone ?? '') }}">
          </div>
        </div>

        <div class="grid grid-cols-2 gap-2 mt-2">
          <div>
            <label class="block text-sm">WhatsApp</label>
            <input class="w-full border rounded px-3 py-2" name="whatsapp" value="{{ old('whatsapp', $brand->whatsapp ?? '') }}">
          </div>
          <div>
            <label class="block text-sm">E-mail</label>
            <input class="w-full border rounded px-3 py-2" name="email" value="{{ old('email', $brand->email ?? '') }}">
          </div>
        </div>

        <label class="block text-sm mt-2">Site</label>
        <input class="w-full border rounded px-3 py-2" name="website" value="{{ old('website', $brand->website ?? '') }}">

        <label class="block text-sm mt-2">Texto do rodapé</label>
        <input class="w-full border rounded px-3 py-2" name="footer_text" value="{{ old('footer_text', $brand->footer_text ?? '') }}">
      </div>

      <div>
        <div class="grid grid-cols-2 gap-2">
          <div>
            <label class="block text-sm">Cor principal (hex)</label>
            <input class="w-full border rounded px-3 py-2" name="color_primary" value="{{ old('color_primary', $brand->color_primary ?? '#0B2C4A') }}" required>
          </div>
          <div>
            <label class="block text-sm">Cor secundária (hex)</label>
            <input class="w-full border rounded px-3 py-2" name="color_secondary" value="{{ old('color_secondary', $brand->color_secondary ?? '#C9A227') }}" required>
          </div>
        </div>

        <label class="block text-sm mt-2">Logo (PNG/JPG)</label>
        <input class="w-full" type="file" name="logo" accept="image/*">
        @if($brand && $brand->logo_path)
          <div class="mt-3 w-40 h-40 bg-slate-100 rounded overflow-hidden flex items-center justify-center">
            <img class="max-w-full max-h-full" src="{{ asset('storage/'.$brand->logo_path) }}">
          </div>
        @endif

        <button class="mt-4 px-4 py-2 rounded bg-slate-900 text-white" type="submit">Salvar</button>
      </div>
    </div>
  </form>
@endsection
