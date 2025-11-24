<?php
 
 use Illuminate\Http\Request;
 use Illuminate\Support\Facades\Route;
 use App\Http\Controllers\AuthController;
 use App\Http\Controllers\MessageController;
 use App\Http\Controllers\ImageMessageController;
 use App\Http\Controllers\FileEncryptController;

 Route::post('/register', [AuthController::class, 'register']);
 Route::post('/login', [AuthController::class, 'login']);

 Route::post('/send', [MessageController::class, 'send']);
 Route::get('/inbox/{receiver_id}', [MessageController::class, 'inbox']);

 Route::post('/stego/embed', [ImageMessageController::class, 'embed']);
 Route::post('/stego/extract', [ImageMessageController::class, 'extract']);

 Route::post('/file/encrypt', [FileEncryptController::class, 'encrypt']);
 Route::post('/file/decrypt', [FileEncryptController::class, 'decrypt']);
