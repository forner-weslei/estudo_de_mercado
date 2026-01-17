<?php

namespace App\Http\Controllers;

use App\Models\Study;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StudyController extends Controller
{
    public function index()
    {
        $studies = Study::where('user_id', Auth::id())->latest()->paginate(20);
        return view('studies.index', compact('studies'));
    }

    public function create()
    {
        return view('studies.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'owner_name' => 'required|string|max:120',
            'owner_contact' => 'nullable|string|max:120',
            'subject_address' => 'required|string|max:255',
            'subject_neighborhood' => 'nullable|string|max:120',
            'subject_city' => 'required|string|max:120',
            'subject_state' => 'required|string|max:2',
            'subject_type' => 'required|in:house,apartment,commercial,land',
            'subject_area_m2' => 'required|numeric|min:1',
            'subject_bedrooms' => 'nullable|integer|min:0',
            'subject_suites' => 'nullable|integer|min:0',
            'subject_parking' => 'nullable|integer|min:0',
            'notes' => 'nullable|string|max:5000',

            'scenario_base' => 'required|in:avg_total,avg_m2,lowest_total,manual_total',
            'manual_total_price' => 'nullable|numeric|min:0',
            'pct_optimistic' => 'required|numeric',
            'pct_market' => 'required|numeric',
            'pct_competitive' => 'required|numeric',

            'override_branding' => 'nullable|boolean',
        ]);

        $data['user_id'] = Auth::id();

        $study = Study::create($data);

        return redirect()->route('estudos.edit', $study)->with('success', 'Estudo criado.');
    }

    public function edit(Study $estudo)
    {
        $this->authorize('view', $estudo);
        return view('studies.edit', ['study' => $estudo]);
    }

    public function update(Request $request, Study $estudo)
    {
        $this->authorize('update', $estudo);

        $data = $request->validate([
            'owner_name' => 'required|string|max:120',
            'owner_contact' => 'nullable|string|max:120',
            'subject_address' => 'required|string|max:255',
            'subject_neighborhood' => 'nullable|string|max:120',
            'subject_city' => 'required|string|max:120',
            'subject_state' => 'required|string|max:2',
            'subject_type' => 'required|in:house,apartment,commercial,land',
            'subject_area_m2' => 'required|numeric|min:1',
            'subject_bedrooms' => 'nullable|integer|min:0',
            'subject_suites' => 'nullable|integer|min:0',
            'subject_parking' => 'nullable|integer|min:0',
            'notes' => 'nullable|string|max:5000',

            'scenario_base' => 'required|in:avg_total,avg_m2,lowest_total,manual_total',
            'manual_total_price' => 'nullable|numeric|min:0',
            'pct_optimistic' => 'required|numeric',
            'pct_market' => 'required|numeric',
            'pct_competitive' => 'required|numeric',
        ]);

        $estudo->update($data);

        return back()->with('success', 'Estudo atualizado.');
    }

    public function destroy(Study $estudo)
    {
        $this->authorize('delete', $estudo);
        $estudo->delete();
        return redirect()->route('estudos.index')->with('success', 'Estudo removido.');
    }

    public function show(Study $estudo)
    {
        return redirect()->route('studies.presentation', $estudo);
    }
}
