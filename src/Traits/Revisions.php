<?php

namespace ctf0\Odin\Traits;

use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Models\Audit;

trait Revisions
{
    use Auditable;

    protected $auditStrict  = true;
    protected $auditExclude = ['user_id', 'id'];

    // Accessor for Revisions
    public function getRevisionsAttribute()
    {
        return $this->audits()->with('user')->get()->filter(function ($e) {
            if (empty($e->old_values) && empty($e->new_values)) {
                return Audit::find($e->id)->delete();
            }

            return $e;
        })->reverse();
    }
}
