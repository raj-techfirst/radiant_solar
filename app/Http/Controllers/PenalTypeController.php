<?php

namespace App\Http\Controllers;

use App\Models\Installation;
use App\Models\PenalCompany;
use App\Models\PenalType;
use App\Models\SalesQuatation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class PenalTypeController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:penal-type-list|penal-type-create|penal-type-edit|penal-type-delete', ['only' => ['index', 'store']]);
        $this->middleware('permission:penal-type-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:penal-type-edit', ['only' => ['edit', 'store']]);
        $this->middleware('permission:penal-type-delete', ['only' => ['destroy']]);
    }

    public function index()
    {
        if (request()->ajax()) {
            return DataTables::of(PenalType::orderBy('name', 'ASC')->orderBy('id', 'DESC'))
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $html = '<td>';
                    if (Gate::check('penal-type-edit')) {
                        $html .= '<a data-id="' . $row->id . '" href="javascript:void(0);" class="avatar bg-light-info p-50 m-0 edit" data-bs-toggle="tooltip" data-placement="left" title="Edit"><i class="fa fa-edit"></i></a>';
                    }
                    if (Gate::check('penal-type-delete')) {
                        $html .= ' <a data-id="' . $row->id . '" href="javascript:void(0);" class="avatar bg-light-danger p-50 m-0 delete" data-bs-toggle="tooltip" data-placement="left" title="Delete"><i class="fa fa-trash"></i></a>';
                    }
                    $html .= '</td>';
                    return $html;
                })
                ->escapeColumns([])
                ->make(true);
        } else {
            return view('admin.penal_type.index');
        }
    }
    public function create()
    {
        return view('admin.penal_type.index');
    }

    public function store(Request $request)
    {
        if (!is_null($request->penal_type_id)) {
            $name = [
                'required',
                Rule::unique('penal_types')->where(function ($query) use ($request) {
                    return $query->where('deleted_at', null);
                })->ignore($request->penal_type_id),
            ];
        } else {
            $name = [
                'required',
                Rule::unique('penal_types')->where(function ($query) use ($request) {
                    return $query->where('deleted_at', null);
                }),
            ];
        }
        $validator = Validator::make($request->all(), [
            'name' => $name
        ], [
            'name.required' => 'Enter Panel type',
            'name.unique' => 'The name has already been taken'
        ]);

        if ($validator->fails()) {
            $response = ['status' => false, 'message' => 'Please input proper data.', 'errors' => $validator->errors()];
            return response()->json($response);
        }
        DB::beginTransaction();
        try {
            if (!is_null($request->penal_type_id)) {
                $penalType = PenalType::where('id', $request->penal_type_id)->first();
                $response = ['data' => route('penal-type.index'), 'status' => true, 'message' => ' Panel Type updated successfully.'];
            } else {
                $penalType = new PenalType();
                $response = ['data' => route('penal-type.index'), 'status' => true, 'message' => ' Panel Type added successfully.'];
            }
            $penalType->user_id = Auth::id();
            $penalType->name = $request->name;
            $result = $penalType->save();
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

    public function show(PenalType $penalType)
    {
        //
    }

    public function edit($id)
    {
        $penalType = PenalType::where('id', $id)->first();
        if (!is_null($penalType)) {
            $res = array('msg_type' => 'success', 'msg_title' => 'Success!', 'result' => $penalType);
            header('Content-Type:application/json');
            echo json_encode($res);
        } else {
            return abort(404);
        }
    }

    public function update(Request $request, PenalType $penalType)
    {
        //
    }

    public function destroy($id)
    {
        try {
            $salesQuatation = SalesQuatation::where('penal_type_id', $id)->first();
            $installation = Installation::where('penal_type_id', $id)->first();
            if (is_null($salesQuatation) && is_null($installation)) {
                $penalType = PenalType::where('id', $id)->first();
                $penalType->delete();
                $response = ['status' => true, 'message' => 'Deleted successfully.'];
            } else {
                $response = ['status' => false, 'message' => 'You cannot delete this because it is being utilized elsewhere.'];
            }
            return response()->json($response);
        } catch (\Exception $e) {
            $response = ['status' => false, 'server_error' => 'Something went wrong. Please try again.'];
            return response()->json($response);
        }
    }
}
