<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/
use App\Http\Controllers\ChatGptController;
use App\Http\Controllers\WhisperController;

Route::get('/chat', [ChatGptController::class, 'index'])->name('chat_gpt-index');
Route::post('/vision_api', [ChatGptController::class, 'chat'])->name('vision_api');

Route::get('/', function () {
    return view('welcome');
});

Route::get('whisper', [WhisperController::class, 'whisper'])->name('whisper');

Route::get('/users', [ChatGptController::class, 'users'])->name('users');
