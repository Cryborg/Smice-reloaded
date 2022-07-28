<?php

use App\Http\Auth\Controllers\AuthController;

Route::controller(AuthController::class)
    ->as('auth.')
    ->group(function () {
    Route::post('login', 'login')->name('login');
    Route::post('register', 'register')->name('register');
    Route::post('logout', 'logout')->name('logout');
    Route::post('refresh', 'refresh')->name('refresh');
});
