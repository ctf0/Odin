<?php

namespace ctf0\Odin\Traits;

use OwenIt\Auditing\Auditable;
use Fico7489\Laravel\Pivot\Traits\PivotEventTrait;

trait Revisions
{
    use Auditable, PivotEventTrait;

    public static function bootRevisions()
    {
        static::pivotAttached(function ($model, $relationName, $pivotIds, $pivotIdsAttributes) {
            return $model->savePivotAudit(
                    'Attached',
                    get_class($model->$relationName()->getRelated()),
                    $pivotIds[0],
                    $model->getKey()
                );
        });

        static::pivotDetached(function ($model, $relationName, $pivotIds) {
            return $model->savePivotAudit(
                    'Detached',
                    get_class($model->$relationName()->getRelated()),
                    $pivotIds[0],
                    $model->getKey()
                );
        });
    }

    private function savePivotAudit($eventName, $relationClass, $relationId, $modelId)
    {
        return app('db')->table('audits_pivot')->insert([
            'event'          => $eventName,
            'auditable_id'   => $modelId,
            'auditable_type' => $this->getMorphClass(),
            'relation_type'  => $relationClass,
            'relation_id'    => $relationId,
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);
    }

    private function getPivotAudits($type, $id)
    {
        return app('db')->table('audits_pivot')
                        ->where('auditable_id', $id)
                        ->where('auditable_type', $type)
                        ->get()
                        ->reverse();
    }

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

    public function getRevisionsWithRelationAttribute()
    {
        return $this->audits->load('user')->map(function ($item) {
            $item['relations'] = $this->getPivotAudits($item->auditable_type, $item->auditable_id);

            return $item;
        })->reverse();
    }
}
