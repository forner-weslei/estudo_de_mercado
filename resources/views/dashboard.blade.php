<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Estudos') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold mb-2">Estudos</h3>
                    <p class="text-sm text-gray-600 mb-4">
                        Crie uma nova análise ou gerencie as existentes.
                    </p>

                    <div class="flex flex-wrap gap-3">
                        <a href="{{ route('estudos.create') }}"
                           class="inline-flex items-center px-4 py-2 bg-gray-900 text-white rounded-md hover:bg-gray-800">
                            Nova análise
                        </a>

                        <a href="{{ route('estudos.index') }}"
                           class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                            Estudos
                        </a>

                        <a href="{{ route('brand.edit') }}"
                           class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                            Minha marca
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
