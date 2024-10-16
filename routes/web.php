<?php

use App\Http\Middleware\CheckRole;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Backend\ChefController;
use App\Http\Controllers\Backend\MenuController;
use App\Http\Controllers\Backend\EventController;
use App\Http\Controllers\Backend\ImageController;
use App\Http\Controllers\Backend\VideoController;
use App\Http\Controllers\Frontend\MainController;
use App\Http\Controllers\Backend\ReviewController;
use App\Http\Controllers\Frontend\BookingController;
use App\Http\Controllers\Backend\TransactionController;
use App\Http\Controllers\Frontend\ReviewController as FrontReviewController;

Route::get('/', MainController::class);

Route::post('booking', [BookingController::class, 'store'])->name('book.attempt');
Route::post('review', [FrontReviewController::class, 'store'])->name('review.attempt');

Route::prefix('panel')->middleware('auth')->group(function () {
    Route::get('/dashboard', [TransactionController::class, 'dashboard'])->name('panel.dashboard');

    // Routes untuk kedua role yaitu owner and operator
    Route::get('transaction', [TransactionController::class, 'index'])->name('panel.transaction.index');
    Route::get('transaction/{transaction}', [TransactionController::class, 'show'])->name('panel.transaction.show');
    Route::post('transaction/download', [TransactionController::class, 'download'])->name('panel.transaction.download');

    // Routes hanya untuk operator
    Route::middleware(CheckRole::class . ':operator')->group(function () {
        Route::resource('image', ImageController::class)->names('panel.image');
        Route::resource('video', VideoController::class)->names('panel.video');
        Route::resource('menu', MenuController::class)->names('panel.menu');
        Route::resource('chef', ChefController::class)->except(['show'])->names('panel.chef');
        Route::resource('event', EventController::class)->names('panel.event');
        Route::resource('review', ReviewController::class)->names('panel.review');

        Route::post('transaction', [TransactionController::class, 'store'])->name('panel.transaction.store');
        Route::put('transaction/{transaction}', [TransactionController::class, 'update'])->name('panel.transaction.update');
        Route::delete('transaction/{transaction}', [TransactionController::class, 'destroy'])->name('panel.transaction.destroy');
    })->middleware('role:operator');

});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
