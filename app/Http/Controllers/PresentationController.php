<?php

namespace App\Http\Controllers;

use App\Models\Study;
use Barryvdh\DomPDF\Facade\Pdf;

class PresentationController extends Controller
{
    public function show(Study $study)
    {
        $this->authorize('view', $study);
        $study->load('comparables');
        $computed = $study->computePricing();
        $brand = $study->effectiveBrand();
        return view('studies.presentation', compact('study', 'computed', 'brand'));
    }

    public function pdf(Study $study)
    {
        $this->authorize('view', $study);
        $study->load('comparables');
        $computed = $study->computePricing();
        $brand = $study->effectiveBrand();

        $pdf = Pdf::loadView('studies.presentation_pdf', compact('study', 'computed', 'brand'))
            ->setPaper('a4', 'portrait');

        return $pdf->download('estudo-mercado-'.$study->id.'.pdf');
    }
}
