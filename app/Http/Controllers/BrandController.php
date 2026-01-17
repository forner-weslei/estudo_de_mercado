<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class BrandController extends Controller
{
    public function edit()
    {
        $brand = Auth::user()->brand ?? null;
        return view('brand.edit', compact('brand'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'company_name' => 'required|string|max:120',
            'agent_name' => 'required|string|max:120',
            'creci' => 'nullable|string|max:60',
            'phone' => 'nullable|string|max:60',
            'whatsapp' => 'nullable|string|max:60',
            'email' => 'nullable|email|max:120',
            'website' => 'nullable|string|max:120',
            'footer_text' => 'nullable|string|max:255',
            'color_primary' => 'required|string|max:20',
            'color_secondary' => 'required|string|max:20',
            'logo' => 'nullable|image|max:5120'
        ]);

        $user = Auth::user();
        $brand = $user->brand()->firstOrCreate([]);

        if ($request->hasFile('logo')) {
            if ($brand->logo_path) Storage::disk('public')->delete($brand->logo_path);
            $data['logo_path'] = $request->file('logo')->store('logos', 'public');
        }

        $brand->update($data);

        return back()->with('success', 'Marca atualizada.');
    }
}
