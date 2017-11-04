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
        $model    = app($revision->auditable_type)->find($revision->auditable_id);

        if ($revision->event == 'created') {
            foreach ($revision->getModified() as $col => $data) {
                $model->$col = $data['new'];
            }
        } else {
            foreach ($revision->getModified() as $col => $data) {
                $model->$col = $data['old'];
            }
        }

        $model->save()
            ? session()->flash('status', trans('Odin::messages.res_success'))
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
