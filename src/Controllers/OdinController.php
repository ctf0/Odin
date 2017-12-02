<?php

namespace ctf0\Odin\Controllers;

use OwenIt\Auditing\Models\Audit;
use App\Http\Controllers\Controller;

class OdinController extends Controller
{
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

        if ('created' == $revision->event) {
            // use new
            $model = app($revision->auditable_type)->find($revision->auditable_id)->transitionTo($revision);
        } else {
            // use old
            $model = app($revision->auditable_type)->find($revision->auditable_id)->transitionTo($revision, true);
        }

        $model->save()
            ? session()->flash('status', trans('Odin::messages.res_success'))
            : session()->flash('status', trans('Odin::messages.went_bad'));

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
        $model    = app($revision->auditable_type)->withTrashed()->find($revision->auditable_id);

        $model->restore()
            ? session()->flash('status', trans('Odin::messages.res_model_success'))
            : session()->flash('status', trans('Odin::messages.went_bad'));

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

    protected function getId($id)
    {
        return Audit::find($id);
    }
}
