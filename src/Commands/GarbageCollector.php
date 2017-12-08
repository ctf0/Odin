<?php

namespace ctf0\Odin\Commands;

use Illuminate\Console\Command;
use OwenIt\Auditing\Models\Audit;

class GarbageCollector extends Command
{
    protected $signature   = 'odin:gc';
    protected $description = 'Clear audits for permanently deleted models';

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
        $audit   = app(Audit::class);
        $models  = $audit->groupBy('auditable_type')->pluck('auditable_type');
        $cleared = false;

        foreach ($models as $m) {
            $audit_ids = $audit->where('auditable_type', $m)->groupBy('auditable_id')->pluck('auditable_id')->all();
            $model_ids = app($m)->withoutGlobalScopes()->pluck('id')->all();
            $diff      = array_diff($audit_ids, $model_ids);

            if (count($diff) > 0) {
                $cleared = true;

                $audit->where('auditable_type', $m)
                    ->whereIn('auditable_id', $diff)
                    ->delete();

                $this->line("'$m' audits are cleared");
            }
        }

        $cleared
            ? $this->info('All Done')
            : $this->info('Nothing To Clear');
    }
}
