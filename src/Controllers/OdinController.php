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
        $r     = $this->getId($id);
        $model = app($r->auditable_type)->find($r->auditable_id);

        if ('created' == $r->event) {
            foreach ($r->getModified() as $col => $data) {
                $model->$col = $data['new'];
            }
        } else {
            foreach ($r->getModified() as $col => $data) {
                $model->$col = $data['old'];
            }
        }

        $model->save()
            ? session()->flash('status', 'Model Updated!')
            : session()->flash('status', 'Something Went Wrong!');

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
                'message'=> 'Revision Removed',
            ]);
        }

        return response()->json([
            'success'=> false,
            'message'=> 'Something Went Wrong!',
        ]);
    }

    protected function getId($id)
    {
        return Audit::find($id);
    }
}
