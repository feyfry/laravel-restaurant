<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Backend\ChefController;
use App\Http\Controllers\Backend\MenuController;
use App\Http\Controllers\Backend\EventController;
use App\Http\Controllers\Backend\ImageController;
use App\Http\Controllers\Frontend\MainController;
use App\Http\Controllers\Backend\ReviewController;
use App\Http\Controllers\Frontend\BookingController;
use App\Http\Controllers\Backend\TransactionController;
use App\Http\Controllers\Frontend\ReviewController as FrontReviewController;

Route::get('/', MainController::class);

Route::post('booking', [BookingController::class, 'store'])->name('book.attempt');
Route::post('review', [FrontReviewController::class, 'store'])->name('review.attempt');

Route::prefix('panel')->middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        return view('backend.dashboard.index');
    })->name('panel.dashboard');

    Route::resource('image', ImageController::class)->names('panel.image');

    Route::resource('menu', MenuController::class)->names('panel.menu');

    Route::resource('chef', ChefController::class)
        ->except(['show'])
        ->names('panel.chef');

    Route::resource('event', EventController::class)->names('panel.event');

    Route::resource('review', ReviewController::class)
    // ->only('index', 'show', 'destroy')
        ->names('panel.review');

    Route::post('transaction/download', [TransactionController::class, 'download'])->name('panel.transaction.download');
    Route::resource('transaction', TransactionController::class)
        ->except(['create', 'store', 'edit'])
        ->names('panel.transaction');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
