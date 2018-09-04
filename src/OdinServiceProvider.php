<?php

namespace ctf0\Odin;

use Illuminate\Support\ServiceProvider;
use ctf0\Odin\Commands\GarbageCollector;

class OdinServiceProvider extends ServiceProvider
{
    protected $file;

    /**
     * Perform post-registration booting of services.
     */
    public function boot()
    {
        $this->file = $this->app['files'];

        $this->packagePublish();
        $this->registerMacro();
        $this->command();

        // append extra data
        if (!$this->app['cache']->store('file')->has('ct-odin')) {
            $this->autoReg();
        }
    }

    protected function registerMacro()
    {
        $this->app['router']->macro('setGroupNamespace', function ($namesapce = null) {
            $lastGroupStack = array_pop($this->groupStack);
            if ($lastGroupStack !== null) {
                array_set($lastGroupStack, 'namespace', $namesapce);
                $this->groupStack[] = $lastGroupStack;
            }

            return $this;
        });
    }

    /**
     * [packagePublish description].
     *
     * @return [type] [description]
     */
    protected function packagePublish()
    {
        // migrations
        $this->publishes([
            __DIR__ . '/database' => database_path('migrations'),
        ], 'migrations');

        // resources
        $this->publishes([
            __DIR__ . '/resources/assets' => resource_path('assets/vendor/Odin'),
        ], 'assets');

        // trans
        $this->loadTranslationsFrom(__DIR__ . '/resources/lang', 'Odin');
        $this->publishes([
            __DIR__ . '/resources/lang' => resource_path('lang/vendor/Odin'),
        ], 'trans');

        // views
        $this->loadViewsFrom(__DIR__ . '/resources/views', 'Odin');
        $this->publishes([
            __DIR__ . '/resources/views' => resource_path('views/vendor/Odin'),
        ], 'views');
    }

    /**
     * clear audits table of permanently deleted auditable models.
     *
     * @return [type] [description]
     */
    protected function command()
    {
        $this->commands([
            GarbageCollector::class,
        ]);
    }

    /**
     * [autoReg description].
     *
     * @return [type] [description]
     */
    protected function autoReg()
    {
        // routes
        $route_file = base_path('routes/web.php');
        $search     = 'Odin';

        if ($this->checkExist($route_file, $search)) {
            $data = "\n// Odin\nctf0\Odin\OdinRoutes::routes();";

            $this->file->append($route_file, $data);
        }

        // mix
        $mix_file = base_path('webpack.mix.js');
        $search   = 'Odin';

        if ($this->checkExist($mix_file, $search)) {
            $data = "\n// Odin\nmix.sass('resources/assets/vendor/Odin/sass/style.scss', 'public/assets/vendor/Odin/style.css')";

            $this->file->append($mix_file, $data);
        }

        // run check once
        $this->app['cache']->store('file')->rememberForever('ct-odin', function () {
            return 'added';
        });
    }

    /**
     * [checkExist description].
     *
     * @param [type] $file   [description]
     * @param [type] $search [description]
     *
     * @return [type] [description]
     */
    protected function checkExist($file, $search)
    {
        return $this->file->exists($file) && !str_contains($this->file->get($file), $search);
    }

    /**
     * Register any package services.
     *
     * @return [type] [description]
     */
    public function register()
    {
        $this->app->singleton('odin', function () {
            return new Odin();
        });

        $this->app->register(\ctf0\PackageChangeLog\PackageChangeLogServiceProvider::class);
        $this->app->register(\OwenIt\Auditing\AuditingServiceProvider::class);
    }
}
