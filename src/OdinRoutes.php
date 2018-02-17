<?php

namespace ctf0\Odin;

class OdinRoutes
{
    public static function routes()
    {
        app('router')->group([
            'prefix' => 'odin',
            'as'     => 'odin.',
        ], function () {
            app('router')->post('revision/{id}/preview', '\ctf0\Odin\Controllers\OdinController@preview')->name('preview');
            app('router')->post('restore/{id}', '\ctf0\Odin\Controllers\OdinController@restore')->name('restore');
            app('router')->put('restore-soft/{id}', '\ctf0\Odin\Controllers\OdinController@restoreSoft')->name('restore.soft');
            app('router')->delete('remove/{id}', '\ctf0\Odin\Controllers\OdinController@remove')->name('remove');
        });
    }
}
