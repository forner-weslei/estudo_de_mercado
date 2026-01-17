<?php

namespace App\Http\Controllers;

use App\Models\Study;
use App\Models\Comparable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ComparableController extends Controller
{
    public function index(Study $estudo)
    {
        $this->authorize('view', $estudo);
        $comparables = $estudo->comparables()->orderBy('sort_order')->get();
        return view('comparables.index', compact('estudo', 'comparables'));
    }

    public function create(Study $estudo)
    {
        $this->authorize('update', $estudo);
        return view('comparables.create', compact('estudo'));
    }

    public function store(Request $request, Study $estudo)
    {
        $this->authorize('update', $estudo);

        $data = $request->validate([
            'title' => 'nullable|string|max:200',
            'address' => 'nullable|string|max:255',
            'price' => 'required|numeric|min:0',
            'area_m2' => 'required|numeric|min:1',
            'bedrooms' => 'nullable|integer|min:0',
            'suites' => 'nullable|integer|min:0',
            'parking' => 'nullable|integer|min:0',
            'bathrooms' => 'nullable|integer|min:0',
            'include_in_calc' => 'nullable|boolean',
            'photo' => 'nullable|image|max:5120'
        ]);

        $data['include_in_calc'] = (bool)($data['include_in_calc'] ?? true);
        $data['sort_order'] = ($estudo->comparables()->max('sort_order') ?? 0) + 1;

        if ($request->hasFile('photo')) {
            $data['main_photo_path'] = $request->file('photo')->store('comparables', 'public');
        }

        $estudo->comparables()->create($data);

        return redirect()->route('estudos.amostras.index', $estudo)->with('success', 'Amostra adicionada.');
    }

    public function edit(Study $estudo, Comparable $amostra)
    {
        $this->authorize('update', $estudo);
        return view('comparables.edit', compact('estudo', 'amostra'));
    }

    public function update(Request $request, Study $estudo, Comparable $amostra)
    {
        $this->authorize('update', $estudo);

        $data = $request->validate([
            'title' => 'nullable|string|max:200',
            'address' => 'nullable|string|max:255',
            'price' => 'required|numeric|min:0',
            'area_m2' => 'required|numeric|min:1',
            'bedrooms' => 'nullable|integer|min:0',
            'suites' => 'nullable|integer|min:0',
            'parking' => 'nullable|integer|min:0',
            'bathrooms' => 'nullable|integer|min:0',
            'include_in_calc' => 'nullable|boolean',
            'photo' => 'nullable|image|max:5120'
        ]);

        $data['include_in_calc'] = (bool)($data['include_in_calc'] ?? false);

        if ($request->hasFile('photo')) {
            if ($amostra->main_photo_path) Storage::disk('public')->delete($amostra->main_photo_path);
            $data['main_photo_path'] = $request->file('photo')->store('comparables', 'public');
        }

        $amostra->update($data);

        return back()->with('success', 'Amostra atualizada.');
    }

    public function destroy(Study $estudo, Comparable $amostra)
    {
        $this->authorize('update', $estudo);
        if ($amostra->main_photo_path) Storage::disk('public')->delete($amostra->main_photo_path);
        $amostra->delete();
        return back()->with('success', 'Amostra removida.');
    }

    public function show(Study $estudo, Comparable $amostra)
    {
        return redirect()->route('estudos.amostras.edit', [$estudo, $amostra]);
    }
}
