<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PerformanceController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RecommendationController;
use App\Models\User;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/performance', [PerformanceController::class, 'index'])->name('performance.index');

    Route::get('/recommendations', [RecommendationController::class, 'index'])->name('recommendations.index');

    Route::middleware('role:' . User::ROLE_CONTROLE_INTERNE)->group(function () {
        Route::get('/recommendations/create', [RecommendationController::class, 'create'])->name('recommendations.create');
        Route::post('/recommendations', [RecommendationController::class, 'store'])->name('recommendations.store');
    });

    Route::get('/recommendations/{recommendation}', [RecommendationController::class, 'show'])->name('recommendations.show');
    Route::get('/recommendations/{recommendation}/evidences/{evidence}', [RecommendationController::class, 'downloadEvidence'])
        ->name('recommendations.evidences.download');

    Route::middleware('role:' . User::ROLE_CONTROLE_INTERNE)->group(function () {
        Route::delete('/recommendations/{recommendation}', [RecommendationController::class, 'destroy'])->name('recommendations.destroy');
    });

    Route::middleware('role:' . User::ROLE_CONTROLE_INTERNE . ',' . User::ROLE_DIRECTEUR)->group(function () {
        Route::get('/recommendations/{recommendation}/edit', [RecommendationController::class, 'edit'])->name('recommendations.edit');
        Route::put('/recommendations/{recommendation}', [RecommendationController::class, 'update'])->name('recommendations.update');
        Route::patch('/recommendations/{recommendation}', [RecommendationController::class, 'update']);
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
