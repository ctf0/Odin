<?php

namespace ctf0\Odin\Traits;

use OwenIt\Auditing\Auditable;

trait Revisions
{
    use Auditable;

    protected $auditStrict  = true;
    protected $auditExclude = ['user_id', 'id'];

    // Accessor for Revisions
    public function getRevisionsAttribute()
    {
        return $this->audits()->with('user')->get()->reverse();
    }
}
