<?php

namespace ctf0\Odin\Traits;

use OwenIt\Auditing\Auditable;

trait Revisions
{
    use Auditable;

    public function getAuditExclude(): array
    {
        $main  = $this->auditExclude ?? [];
        $extra = [config('audit.user.foreign_key'), 'id'];
        $dates = ['created_at', 'updated_at', 'deleted_at'];

        return array_merge($main, $extra, $dates);
    }

    // Accessor for Revisions
    public function getRevisionsAttribute()
    {
        return $this->audits()->get()->reverse();
    }
}
