<?php

use App\Http\Controllers\MessageController;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/chats', [MessageController::class, 'index'])->name('chat.index');
    Route::post('/chats/{user:uuid}', [MessageController::class, 'store'])->name('chat.store');
    Route::get('/chats/{user:uuid}', [MessageController::class, 'show'])->name('chat.show');
    Route::delete('/chats/{message}', [MessageController::class, 'destroy'])->name('chat.destroy');
});

require __DIR__.'/auth.php';
