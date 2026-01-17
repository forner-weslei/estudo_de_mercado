<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StudyController;
use App\Http\Controllers\ComparableController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\PresentationController;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

// IMPORTANT (LGPD / multi-user): enable scoped route-model bindings for nested resources
// so that /estudos/{estudo}/amostras/{amostra} only resolves amostras that belong to {estudo}.
Route::middleware(['auth'])->scopeBindings()->group(function () {
    Route::view('/dashboard', 'dashboard')->name('dashboard');

    Route::get('/minha-marca', [BrandController::class, 'edit'])->name('brand.edit');
    Route::post('/minha-marca', [BrandController::class, 'update'])->name('brand.update');

    Route::resource('estudos', StudyController::class);
    Route::resource('estudos.amostras', ComparableController::class);

    Route::get('/estudos/{study}/apresentacao', [PresentationController::class, 'show'])->name('studies.presentation');
    Route::get('/estudos/{study}/exportar-pdf', [PresentationController::class, 'pdf'])->name('studies.pdf');
});

require __DIR__.'/auth.php';
