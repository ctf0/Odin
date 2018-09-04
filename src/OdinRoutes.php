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
            app('router')->setGroupNamespace('\ctf0\Odin\Controllers');

            app('router')->post('revision/{id}/preview', 'OdinController@preview')->name('preview');
            app('router')->post('restore/{id}', 'OdinController@restore')->name('restore');
            app('router')->put('restore-soft/{id}', 'OdinController@restoreSoft')->name('restore.soft');
            app('router')->delete('remove/{id}', 'OdinController@remove')->name('remove');
        });
    }
}
