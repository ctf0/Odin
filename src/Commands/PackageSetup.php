<?php

namespace ctf0\Odin\Commands;

use Illuminate\Console\Command;

class PackageSetup extends Command
{
    protected $signature   = 'odin:setup';
    protected $description = 'setup package routes & assets compiling';

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
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

        $this->info('All Done');
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
        $file = $this->app['files'];

        return $file->exists($file) && !str_contains($file->get($file), $search);
    }
}
