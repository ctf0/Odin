<?php

namespace ctf0\Odin;

use Illuminate\Support\Arr;
use ctf0\Odin\Commands\PackageSetup;
use Illuminate\Support\ServiceProvider;
use ctf0\Odin\Commands\GarbageCollector;

class OdinServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     */
    public function boot()
    {
        $this->packagePublish();
        $this->registerMacro();
        $this->command();
    }

    protected function registerMacro()
    {
        $this->app['router']->macro('setGroupNamespace', function ($namesapce = null) {
            $lastGroupStack = array_pop($this->groupStack);

            if ($lastGroupStack !== null) {
                Arr::set($lastGroupStack, 'namespace', $namesapce);
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
     * package commands.
     *
     * @return [type] [description]
     */
    protected function command()
    {
        $this->commands([
            GarbageCollector::class,
            PackageSetup::class,
        ]);
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
    }
}
