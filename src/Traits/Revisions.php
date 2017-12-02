<?php

namespace ctf0\Odin\Traits;

use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Models\Audit;

trait Revisions
{
    use Auditable;

    public function getAuditExclude(): array
    {
        $main  = $this->auditExclude ?? [];
        $extra = [config('audit.user.foreign_key'), 'id'];
        $dates = $this->getDates();

        return array_merge($main, $extra, $dates);
    }

    // Accessor for Revisions
    public function getRevisionsAttribute()
    {
        return $this->audits()->with('user')->get()->reverse();
    }
}
