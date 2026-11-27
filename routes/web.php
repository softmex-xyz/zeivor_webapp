<?php

use App\Http\Controllers\Auth\SessionController;
use App\Http\Controllers\CraftController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\ProviderController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Home', [
        'mensaje' => 'Configuración funcionando correctamente.',
    ]);
})->name('home');

Route::get('/login', [SessionController::class, 'create'])->name('login');
Route::post('/login', [SessionController::class, 'store'])->middleware('throttle:login')->name('login.store');

Route::apiResource('crafts', CraftController::class)->only(['index', 'show']);

Route::middleware('auth')->group(function (): void {
    Route::post('/logout', [SessionController::class, 'destroy'])->name('logout');

    Route::prefix('users')->group(function (): void {
        Route::get('/', [UserController::class, 'index'])->middleware('can:manage-users')->name('users.index');
        Route::get('/{id}', [UserController::class, 'show'])->name('users.show');
        Route::patch('/{id}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/{id}', [UserController::class, 'destroy'])->name('users.destroy');
    });

    Route::prefix('providers')->group(function (): void{
        Route::get('/', [ProviderController::class, 'index'])->name('providers.index');
        Route::get('/{id}', [ProviderController::class, 'show'])->name('providers.show');
        Route::post('/', [ProviderController::class, 'store'])->name('providers.store');
        Route::patch('/{id}', [ProviderController::class, 'update'])->name('providers.update');
        Route::delete('/{id}', [ProviderController::class, 'destroy'])->name('providers.destroy');
    });

    Route::prefix('services')->group(function(): void {
        Route::get('/', [ServiceController::class, 'index'])->name('services.index');
        Route::get('/{id}', [ServiceController::class, 'show'])->name('services.show');
        Route::post('/', [ServiceController::class, 'store'])->name('services.store');
        Route::patch('/{id}', [ServiceController::class, 'update'])->name('services.update');
        Route::delete('/{id}', [ServiceController::class, 'destroy'])->name('services.destroy');
    });

    Route::prefix('reviews')->group(function(): void {
        Route::get('/', [ReviewController::class, 'index'])->name('reviews.index');
        Route::get('/{id}', [ReviewController::class, 'show'])->name('reviews.show');
        Route::post('/', [ReviewController::class, 'store'])->name('reviews.store');
        Route::patch('/{id}', [ReviewController::class, 'update'])->name('reviews.update');
        Route::delete('/{id}', [ReviewController::class, 'destroy'])->name('reviews.destroy');
    });

    Route::apiResource('crafts', CraftController::class)
        ->only(['store', 'update', 'destroy'])
        ->middleware('can:manage-crafts');
});
