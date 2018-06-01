<?php

namespace ctf0\Odin\Traits;

use OwenIt\Auditing\Auditable;

trait Revisions
{
    use Auditable;

    /**
     * override default.
     *
     * @return [type] [description]
     */
    public function getAuditExclude(): array
    {
        $main  = $this->auditExclude ?: [];
        $extra = ['user_id', 'id'];

        return array_merge($main, $extra);
    }

    // Accessor for Revisions
    public function getRevisionsAttribute()
    {
        return $this->audits->load('user')->reverse();
    }
}
