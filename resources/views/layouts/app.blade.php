<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>{{ config('app.name', 'Estudo de Mercado Avançado') }}</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
</head>
<body class="bg-slate-50">
  <div class="min-h-screen">
    <nav class="bg-white border-b">
      <div class="max-w-6xl mx-auto px-4 py-3 flex items-center justify-between">
        <div class="font-semibold">{{ config('app.name') }}</div>
        <div class="flex gap-3 text-sm">
          <a class="hover:underline" href="{{ route('dashboard') }}">Dashboard</a>
          <a class="hover:underline" href="{{ route('estudos.index') }}">Estudos</a>
          <a class="hover:underline" href="{{ route('brand.edit') }}">Minha Marca</a>
          <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="hover:underline text-red-600" type="submit">Sair</button>
          </form>
        </div>
      </div>
    </nav>

    <main class="max-w-6xl mx-auto px-4 py-6">
      @if (session('success'))
        <div class="mb-4 rounded bg-green-50 border border-green-200 p-3 text-green-800">
          {{ session('success') }}
        </div>
      @endif
      @if ($errors->any())
        <div class="mb-4 rounded bg-red-50 border border-red-200 p-3 text-red-800">
          <div class="font-semibold mb-1">Ajuste os campos:</div>
          <ul class="list-disc pl-5">
            @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      @yield('content')
    </main>
  </div>
</body>
</html>
