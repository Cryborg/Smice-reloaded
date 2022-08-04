<?php

use App\Http\User\Controllers\UserController;

Route::controller(UserController::class)
    ->as('users.')
    ->prefix('users')
    ->group(function () {
        Route::get('list', 'list')->name('list');
        Route::get('smicers', 'getSmicers')->name('smicers');
        Route::get('readers', 'getReaders')->name('readers');
        Route::get('supervisors', 'getSupervisors')->name('supervisors');

        Route::get('{user}/skills', 'getSkills')->name('skills.list');
        Route::post('{user}/skills', 'addSkills')->name('skills.add');
    });
