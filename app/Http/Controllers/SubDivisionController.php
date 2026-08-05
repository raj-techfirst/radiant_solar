<?php

namespace App\Http\Controllers;

use App\Models\Discom;
use App\Models\SalesMaster;
use App\Models\SubDivision;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class SubDivisionController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:sub-division-list|sub-division-create|sub-division-edit|sub-division-delete', ['only' => ['index', 'store']]);
        $this->middleware('permission:sub-division-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:sub-division-edit', ['only' => ['edit', 'store']]);
        $this->middleware('permission:sub-division-delete', ['only' => ['destroy']]);
    }

    public function index()
    {
        $discoms = Discom::get();
        if (request()->ajax()) {
            return DataTables::of(SubDivision::orderBy('name', 'ASC')->orderBy('id', 'DESC'))
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $html = '<td>';
                    if (Gate::check('sub-division-edit')) {
                        $html .= '<a data-id="' . $row->id . '" href="javascript:void(0);" class="avatar bg-light-info p-50 m-0 edit" data-bs-toggle="tooltip" data-placement="left" title="Edit"><i class="fa fa-edit"></i></a>';
                    }
                    if (Gate::check('sub-division-delete')) {
                        $html .= ' <a data-id="' . $row->id . '" href="javascript:void(0);" class="avatar bg-light-danger p-50 m-0 delete" data-bs-toggle="tooltip" data-placement="left" title="Delete"><i class="fa fa-trash"></i></a>';
                    }
                    $html .= '</td>';
                    return $html;
                })
                ->escapeColumns([])
                ->make(true);
        } else {
            return view('admin.sub-division.index',compact('discoms'));
        }
    }

    public function create()
    {
        $discoms = Discom::get();
        return view('admin.sub-division.index',compact('discoms'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'division_name' => 'required',
            'circle_name' => 'required',
            'discom' => 'required',
        ], [
            'name.required' => 'Enter Name',
            'division_name.required' => 'Enter Division Name',
            'circle_name.required' => 'Enter Circle',
            'discom.required' => 'Enter DISCOM',
        ]);

        if ($validator->fails()) {
            $response = ['status' => false, 'message' => 'Please input proper data.', 'errors' => $validator->errors()];
            return response()->json($response);
        }
        DB::beginTransaction();
        try {
            if (!is_null($request->sub_division_id)) {
                $sub_division = SubDivision::where('id', $request->sub_division_id)->first();
                $response = ['data' => route('sub-division.index'), 'status' => true, 'message' => ' Sub Division updated successfully.'];
            } else {
                $sub_division = new SubDivision();
                $response = ['data' => route('sub-division.index'), 'status' => true, 'message' => ' Sub Division added successfully.'];
            }
            // $sub_division->user_id = Auth::id();
            $sub_division->name = $request->name;
            $sub_division->division_name = $request->division_name;
            $sub_division->circle_name = $request->circle_name;
            $sub_division->discom = $request->discom;
            $result = $sub_division->save();
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

    public function show(SubDivision $subDivision)
    {
        //
    }

    public function view(Request $request)
    {
        $subDivision = SubDivision::where('id', $request->sub_division_id)->first();
        // dd($subDivision);
        return response()->json($subDivision);
    }

    public function edit($id)
    {
        $sub_division = SubDivision::where('id', $id)->first();
        if (!is_null($sub_division)) {
            $res = array('msg_type' => 'success', 'msg_title' => 'Success!', 'result' => $sub_division);
            header('Content-Type:application/json');
            echo json_encode($res);
        } else {
            return abort(404);
        }
    }

    public function update(Request $request, SubDivision $subDivision)
    {
        //
    }

    public function destroy($id)
    {
        try {
            $sub_division = SubDivision::where('id', $id)->first();
            $sub_division->delete();
            $response = ['status' => true, 'message' => ' Deleted successfully.'];
            return response()->json($response);
        } catch (\Exception $e) {
            $response = ['status' => false, 'server_error' => 'Something went wrong. Please try again.'];
            return response()->json($response);
        }
    }
}
