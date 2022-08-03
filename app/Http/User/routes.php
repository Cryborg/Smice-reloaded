<?php


use App\Http\User\Controllers\UserController;

Route::controller(UserController::class)
    ->as('users.')
    ->prefix('users')
    ->group(function () {
        Route::get('list', 'list')->name('list');
        Route::get('smicers', 'getSmicers')->name('smicers');
    });
