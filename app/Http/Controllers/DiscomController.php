<?php

namespace App\Http\Controllers;

use App\Models\Discom;
use App\Models\SubDivision;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class DiscomController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:discom-list|discom-create|discom-edit|discom-delete', ['only' => ['index', 'store']]);
        $this->middleware('permission:discom-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:discom-edit', ['only' => ['edit', 'store']]);
        $this->middleware('permission:discom-delete', ['only' => ['destroy']]);
    }

    public function index()
    {
        if (request()->ajax()) {
            return DataTables::of(Discom::orderBy('id', 'DESC'))
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $html = '<td>';
                    if (Gate::check('discom-edit')) {
                        $html .= '<a data-id="' . $row->id . '" href="javascript:void(0);" class="avatar bg-light-info p-50 m-0 edit" data-bs-toggle="tooltip" data-placement="left" title="Edit"><i class="fa fa-edit"></i></a>';
                    }
                    if (Gate::check('discom-delete')) {
                        $html .= ' <a data-id="' . $row->id . '" href="javascript:void(0);" class="avatar bg-light-danger p-50 m-0 delete" data-bs-toggle="tooltip" data-placement="left" title="Delete"><i class="fa fa-trash"></i></a>';
                    }
                    $html .= '</td>';
                    return $html;
                })
                ->escapeColumns([])
                ->make(true);
        } else {
            return view('admin.discom.index');
        }
    }

    public function create()
    {
        return view('admin.discom.index');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'discom_name' => 'required',
            'address' => 'required'
        ], [
            'discom_name.required' => 'Enter DISCOM Name',
            'address.required' => 'Enter Address'
        ]);

        if ($validator->fails()) {
            $response = ['status' => false, 'message' => 'Please input proper data.', 'errors' => $validator->errors()];
            return response()->json($response);
        }
        DB::beginTransaction();
        try {
            if (!is_null($request->discom_id)) {
                $discom = Discom::where('id', $request->discom_id)->first();
                $response = ['data' => route('discom.index'), 'status' => true, 'message' => ' DISCOM updated successfully.'];
            } else {
                $discom = new Discom();
                $response = ['data' => route('discom.index'), 'status' => true, 'message' => ' DISCOM added successfully.'];
            }
            
            $discom->discom_name = $request->discom_name;
            $discom->address = $request->address;

            $allPDFs = salespdfs();
            $documentIds = array_column($allPDFs, 'id');
            $matchedIndexes = array_intersect($request->pdfs, $documentIds);

            $matchedDocuments = [];
            foreach ($matchedIndexes as $index) {
                $matchedDocuments[] = $allPDFs[array_search($index, $documentIds)];
            }

            $discom->selected_pdfs = json_encode($matchedDocuments);

            $result = $discom->save();
            DB::commit();
            if (!is_null($result)) {
                return response()->json($response);
            } else {
                $response = ['status' => false, 'server_error' => 'Something went wrong. Please try again.'];
                return response()->json($response);
            }
        } catch (\Exception $e) {
            DB::rollback();
            $response = ['status' => false, 'server_error' => 'Something went wrong. Please try again.'];
            return response()->json($response);
        }
    }


    public function edit($id)
    {
        $discom = Discom::where('id', $id)->first();
        if (!is_null($discom)) {
            $res = array('msg_type' => 'success', 'msg_title' => 'Success!', 'result' => $discom);
            header('Content-Type:application/json');
            echo json_encode($res);
        } else {
            return abort(404);
        }
    }
    public function destroy($id)
    {
        try {
            $discom = Discom::where('id', $id)->first();
            $discom->delete();
            $response = ['status' => true, 'message' => ' Deleted successfully.'];
            return response()->json($response);
        } catch (\Exception $e) {
            $response = ['status' => false, 'server_error' => 'Something went wrong. Please try again.'];
            return response()->json($response);
        }
    }
}
