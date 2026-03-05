<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\SearchController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return Auth::check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

Route::middleware(['guest', 'no.cache'])->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');
});

Route::middleware(['auth', 'no.cache'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::livewire('/dashboard', 'pages::dashboard')->name('dashboard');
    Route::livewire('/notdin-kpa', 'pages::notdin-kpa')->name('notdin-kpa');
    Route::livewire('/notdin-ppkom', 'pages::notdin-ppkom')->name('notdin-ppkom');
    Route::livewire('/dokumen-pengadaan', 'pages::dokumen-pengadaan')->name('dokumen-pengadaan');
    Route::livewire('/tanggal-libur', 'pages::tanggal-libur')->name('tanggal-libur');
    Route::livewire('/space-nomor', 'pages::space-nomor')->name('space-nomor');
    Route::get('/search/rencana-kegiatan', [SearchController::class, 'rencanaKegiatan'])->name('search.rencana-kegiatan');
});