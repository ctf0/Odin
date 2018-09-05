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
            if ($pivotIds) {
                return $model->savePivotAudit(
                    'Attached',
                    $model->getKey(),
                    get_class($model->$relationName()->getRelated()),
                    $pivotIds[0],
                    $model->updated_at
                );
            }
        });

        static::pivotDetached(function ($model, $relationName, $pivotIds) {
            if ($pivotIds) {
                return $model->savePivotAudit(
                    'Detached',
                    $model->getKey(),
                    get_class($model->$relationName()->getRelated()),
                    $pivotIds[0],
                    $model->updated_at
                );
            }
        });
    }

    private function savePivotAudit($eventName, $id, $relation, $pivotId, $date)
    {
        return app('db')->table('audits_pivot')->insert([
            'event'            => $eventName,
            'auditable_id'     => $id,
            'relation_type'    => $relation,
            'relation_id'      => $pivotId,
            'auditable_type'   => $this->getMorphClass(),
            'parent_updated_at'=> $date,
        ]);
    }

    private function getPivotAudits($type, $id, $date)
    {
        return app('db')->table('audits_pivot')
                        ->where('auditable_id', $id)
                        ->where('auditable_type', $type)
                        ->where('parent_updated_at', $date)
                        ->get();
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

    /**
     * normal : $model->audits.
     */
    public function getRevisionsAttribute()
    {
        return $this->audits->load('user')->reverse();
    }

    /**
     * with relation : $model->auditsWithRelation.
     */
    public function getRevisionsWithRelationAttribute()
    {
        return $this->audits->load('user')->map(function ($item) {
            $item['odin_relations'] = $this
                ->getPivotAudits($item->auditable_type, $item->auditable_id, $item->updated_at)
                ->groupBy(['parent_updated_at', 'relation_id', 'relation_type'])
                ->flatten(2)
                ->reject(function ($item) {
                    return $item->count() == 2;
                })->flatten()->reverse()->groupBy(['relation_type']);

            return $item;
        })->reverse();
    }
}
