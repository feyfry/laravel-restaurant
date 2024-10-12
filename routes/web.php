<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Backend\ImageController;

Route::get('/', function () {
    return view('welcome');
});

Route::prefix('panel')->middleware('auth')->group(function () {
        Route::get('/dashboard', function () {
            return view('backend.dashboard.index');
        })->name('panel.dashboard');

        Route::resource('image', ImageController::class)->names('panel.image');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
