<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});



Route ::get('/home', function () { return 'Essa é a LandingPage'; });
Route::get('/auth', [AuthController::class, 'showAuthForm'])->name('auth.form');
Route::post('/register', [AuthController::class, 'register'])->name('register');
Route::post('/login', [AuthController::class, 'login'])->name('login');


Route ::get('/home', function () { return 'Essa é a LandingPage'; });
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [AuthController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [AuthController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [AuthController::class, 'destroy'])->name('profile.destroy');

});



Route ::get('/home', function () { return 'Essa é a LandingPage'; });








