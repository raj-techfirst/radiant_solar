<?php

namespace App\Http\Controllers;

use App\Models\Installation;
use App\Models\PenalCompany;
use App\Models\PenalWatt;
use App\Models\SalesQuatation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class PenalWattController extends Controller
{

    function __construct()
    {
        $this->middleware('permission:penal-watt-list|penal-watt-create|penal-watt-edit|penal-watt-delete', ['only' => ['index', 'store']]);
        $this->middleware('permission:penal-watt-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:penal-watt-edit', ['only' => ['edit', 'store']]);
        $this->middleware('permission:penal-watt-delete', ['only' => ['destroy']]);
    }

    public function index()
    {
        if (request()->ajax()) {
            return DataTables::of(PenalWatt::orderBy('name', 'ASC')->orderBy('id', 'DESC'))
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $html = '<td>';
                    if (Gate::check('penal-watt-edit')) {
                        $html .= '<a data-id="' . $row->id . '" href="javascript:void(0);" class="avatar bg-light-info p-50 m-0 edit" data-bs-toggle="tooltip" data-placement="left" title="Edit"><i class="fa fa-edit"></i></a>';
                    }
                    if (Gate::check('penal-watt-delete')) {
                        $html .= ' <a data-id="' . $row->id . '" href="javascript:void(0);" class="avatar bg-light-danger p-50 m-0 delete" data-bs-toggle="tooltip" data-placement="left" title="Delete"><i class="fa fa-trash"></i></a>';
                    }
                    $html .= '</td>';
                    return $html;
                })
                ->escapeColumns([])
                ->make(true);
        } else {
            return view('admin.penal_watt.index');
        }
    }

    public function create()
    {
        // return view('admin.penal_watt.index');
    }
    public function store(Request $request)
    {
        if (!is_null($request->penal_watt_id)) {
            $name = [
                'required',
                Rule::unique('penal_watts')->where(function ($query) use ($request) {
                    return $query->where('deleted_at', null);
                })->ignore($request->penal_watt_id),
            ];
        } else {
            $name = [
                'required',
                Rule::unique('penal_watts')->where(function ($query) use ($request) {
                    return $query->where('deleted_at', null);
                }),
            ];
        }
        $validator = Validator::make($request->all(), [
            'name' => $name
        ], [
            'name.required' => 'Enter Panel watt',
            'name.unique' => 'The name has already been taken'
        ]);

        if ($validator->fails()) {
            $response = ['status' => false, 'message' => 'Please input proper data.', 'errors' => $validator->errors()];
            return response()->json($response);
        }
        DB::beginTransaction();
        try {
            if (!is_null($request->penal_watt_id)) {
                $penalWatt = PenalWatt::where('id', $request->penal_watt_id)->first();
                $response = ['data' => route('penal-watt.index'), 'status' => true, 'message' => 'Panel Watt updated successfully.'];
            } else {
                $penalWatt = new PenalWatt();
                $response = ['data' => route('penal-watt.index'), 'status' => true, 'message' => 'Panel Watt added successfully.'];
            }
            $penalWatt->name = $request->name;
            $result = $penalWatt->save();
            DB::commit();
            if (!is_null($result)) {
                return response()->json($response);
            } else {
                $response = ['status' => false, 'server_error' => 'Something went wrong. Please try again.'];
                return response()->json($response);
            }
        } catch (\Exception $e) {
            dd($e);
            DB::rollback();
            $response = ['status' => false, 'server_error' => 'Something went wrong. Please try again.'];
            return response()->json($response);
        }
    }

    public function show(PenalWatt $penalWatt)
    {
        //
    }

    public function edit($id)
    {
        $penalWatt = PenalWatt::where('id', $id)->first();
        if (!is_null($penalWatt)) {
            $res = array('msg_type' => 'success', 'msg_title' => 'Success!', 'result' => $penalWatt);
            header('Content-Type:application/json');
            echo json_encode($res);
        } else {
            return abort(404);
        }
    }


    public function update(Request $request, PenalWatt $penalWatt)
    {
        //
    }

    public function destroy($id)
    {
        try {
            $salesQuatation = SalesQuatation::where('penal_watt_id', $id)->first();
            $installation = Installation::where('penal_watt_id', $id)->first();
            if (is_null($salesQuatation) && is_null($installation)) {
                $penalWatt = PenalWatt::where('id', $id)->first();
                $penalWatt->delete();
                $response = ['status' => true, 'message' => ' Deleted successfully.'];
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
