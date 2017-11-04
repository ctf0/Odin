<?php

namespace ctf0\Odin;

use Illuminate\Support\Facades\Route;

trait Routes
{
    public static function routes()
    {
        Route::group([
            'prefix' => 'odin',
            'as'     => 'odin.',
        ], function () {
            Route::post('restore/{id}', '\ctf0\Odin\Controllers\OdinController@restore')->name('restore');
            Route::delete('remove/{id}', '\ctf0\Odin\Controllers\OdinController@remove')->name('remove');
        });
    }
}
