<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\SecretMailController;

// === AUTH ===
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'processLogin'])->name('login.process');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'processRegister'])->name('register.process');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/', [MessageController::class, 'inbox'])->name('dashboard.inbox');
    
    // pesan keluar (sent)
    Route::get('/sent', [MessageController::class, 'showSent'])->name('sent');
    
    // create message
    Route::get('/create', [MessageController::class, 'create'])->name('create');
    Route::post('/store', [MessageController::class, 'store'])->name('messages.store');

    // Inbox Secret Messages
    Route::get('/secret', [SecretMailController::class, 'index'])->name('secret.index');
    
    // Send Secret Message
    Route::post('/secret/send', [SecretMailController::class, 'store'])->name('secret.send');
    
    // Sent Secret Messages
    Route::get('/secret/sent', [SecretMailController::class, 'sent'])->name('secret.sent');
    
    // Delete Secret Message
    Route::delete('/secret/{id}', [SecretMailController::class, 'destroy'])->name('secret.delete');
    
    // Download Stego Image
    Route::get('/secret/{id}/download', [SecretMailController::class, 'downloadImage'])->name('secret.download');
    
    // Preview Message (AJAX)
    Route::get('/secret/{id}/preview', [SecretMailController::class, 'preview'])->name('secret.preview');
});