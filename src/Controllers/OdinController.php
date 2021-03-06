<?php

namespace ctf0\Odin\Controllers;

use OwenIt\Auditing\Models\Audit;
use App\Http\Controllers\Controller;

class OdinController extends Controller
{
    /**
     * preview old model data.
     *
     * @param [type] $id [description]
     *
     * @return [type] [description]
     */
    public function preview($id)
    {
        $revision = $this->getId($id);

        if (in_array($revision->event, ['created', 'restored'])) {
            return back()->with([
                'title'  => 'Error',
                'status' => trans('Odin::messages.cant_preview'),
                'type'   => 'danger',
            ]);
        }

        $model = app($revision->auditable_type);
        $data  = method_exists($model, 'isForceDeleting')
            ? $model->withTrashed()->findOrFail($revision->auditable_id)->transitionTo($revision, true)
            : $model->findOrFail($revision->auditable_id)->transitionTo($revision, true);

        return view(request('template'), compact('data'));
    }

    /**
     * restore a revision.
     *
     * @param mixed $id
     *
     * @return [type] [description]
     */
    public function restore($id)
    {
        $revision = $this->getId($id);

        if (in_array($revision->event, ['deleted', 'restored'])) {
            return back()->with([
                'title'  => 'Error',
                'status' => trans('Odin::messages.cant_restore'),
                'type'   => 'danger',
            ]);
        }

        $model = app($revision->auditable_type)->findOrFail($revision->auditable_id);
        $revision->event == 'created'
            ? $model->transitionTo($revision) // use new
            : $model->transitionTo($revision, true); // use old

        $model->save()
            ? session()->flash('status', trans('Odin::messages.res_success'))
            : session()->flash([
                'title'  => 'Error',
                'status' => trans('Odin::messages.went_bad'),
                'type'   => 'danger',
            ]);

        return back();
    }

    /**
     * restore soft deleted model.
     *
     * @param [type] $id [description]
     *
     * @return [type] [description]
     */
    public function restoreSoft($id)
    {
        $revision = $this->getId($id);

        if (!in_array($revision->event, ['deleted'])) {
            return back()->with([
                'title'  => 'Error',
                'status' => trans('Odin::messages.cant_soft_restore'),
                'type'   => 'danger',
            ]);
        }

        $model = app($revision->auditable_type)->withTrashed()->findOrFail($revision->auditable_id);

        $model->restore()
            ? session()->flash('status', trans('Odin::messages.res_model_success'))
            : session()->flash([
                'title'  => 'Error',
                'status' => trans('Odin::messages.went_bad'),
                'type'   => 'danger',
            ]);

        return back();
    }

    /**
     * delete a revision.
     *
     * @param [type] $id [description]
     *
     * @return [type] [description]
     */
    public function remove($id)
    {
        if ($this->getId($id)->delete()) {
            return response()->json([
                'success'=> true,
                'message'=> trans('Odin::messages.del_success'),
            ]);
        }

        return response()->json([
            'success'=> false,
            'message'=> trans('Odin::messages.went_bad'),
        ]);
    }

    /**
     * helper.
     *
     * @param [type] $id [description]
     *
     * @return [type] [description]
     */
    protected function getId($id)
    {
        return Audit::findOrFail($id);
    }
}
