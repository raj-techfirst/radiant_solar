<?php

namespace App\Http\Controllers;

use App\Models\District;
use App\Models\Taluka;
use App\Models\Village;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class VillageController extends Controller
{

    function __construct()
    {
        $this->middleware('permission:village-list|city-create|village-edit|village-delete', ['only' => ['index', 'store']]);
        $this->middleware('permission:village-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:village-edit', ['only' => ['edit', 'store']]);
        $this->middleware('permission:village-delete', ['only' => ['destroy']]);
    }

    public function index()
    {
        $district = District::get();
        $taluka = Taluka::get();
        if (request()->ajax()) {
            return DataTables::of(Village::with('district','taluka')->orderBy('name', 'ASC')->orderBy('id', 'DESC'))
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $html = '<td>'; 
                    if (Gate::check('village-edit')) {
                        $html .= '<a data-id="' . $row->id . '" href="javascript:void(0);" class="avatar bg-light-info p-50 m-0 edit" data-bs-toggle="tooltip" data-placement="left" title="Edit"><i class="fa fa-edit"></i></a>';
                    }
                    if (Gate::check('village-delete')) {
                        $html .= ' <a data-id="' . $row->id . '" href="javascript:void(0);" class="avatar bg-light-danger p-50 m-0 delete" data-bs-toggle="tooltip" data-placement="left" title="Delete"><i class="fa fa-trash"></i></a>';
                    }
                    $html .= '</td>';
                    return $html;
                })
                ->escapeColumns([])
                ->make(true);
        } else {
            return view('admin.village.index', compact('district','taluka'));
        }
    }

    public function create()
    {
        //
    }


    public function store(Request $request)
    {
        //  dd($request->name);
        $validator = Validator::make($request->all(), [
            'district_id' => 'required',
            'taluka_id' => 'required',
            'name' => 'required'
        ], [
            'district_id.required' => 'Select District Name',
            'taluka_id.required' => 'Select Taluka Name',
            'name.required' => 'Enter Village Name',
        ]);

        if ($validator->fails()) {
            $response = ['status' => false, 'message' => 'Please input proper data.', 'errors' => $validator->errors()];
            return response()->json($response);
        }
        DB::beginTransaction();
        try {
            if (!is_null($request->village_id)) {
                $village = Village::where('id', $request->village_id)->first();
                $response = ['data' => route('village.index'), 'status' => true, 'message' => ' Village updated successfully.'];
            } else {
                $village = new Village();
                $response = ['data' => route('village.index'), 'status' => true, 'message' => ' Village added successfully.'];
            }
            $village->district_id = $request->district_id;
            $village->taluka_id = $request->taluka_id;
            $village->name = $request->name;
            $result = $village->save();
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

    public function show($id)
    {
        // dd($id);
        // $district = Taluka::where('district_id', $request->district_id)->get();
        // return response()->json($district);
    }

    public function view(Request $request)
    {
        $taluka = Village::where('taluka_id', $request->taluka_id)->get();
        return response()->json($taluka);
    }
 
    public function edit($id)
    {
        $village = Village::where('id', $id)->first();
        if (!is_null($village)) {
            $res = array('msg_type' => 'success', 'msg_title' => 'Success!', 'result' => $village);
            header('Content-Type:application/json');
            echo json_encode($res);
        } else {
            return abort(404);
        }
    }

    public function update(Request $request, Village $village)
    {
        //
    }

    public function destroy($id)
    {
        try {
            // if (!is_null($village)) {
            //     $taluka = Taluka::where('village_id', $village->id)->count();
            //     if ($taluka > 0) {
            //         $response = ['status_code' => 403, 'message' => 'Area also exists.'];
            //     } else {
            //         $response = ['status_code' => 200, 'message' => 'Deleted successfully.'];
            //     }
            // } else {
            //     $response = ['status_code' => 403, 'message' => 'Area not found.'];
            // }
            // // $taluka = Taluka::where('id', $id)->first();
            // $taluka->delete();
            // $response = ['status' => true, 'message' => ' Deleted successfully.'];
            $village = Village::where('id', $id)->first();
            $village->delete();
            $response = ['status' => true, 'message' => ' Deleted successfully.'];
            return response()->json($response);
        } catch (\Exception $e) {
            $response = ['status' => false, 'server_error' => 'Something went wrong. Please try again.'];
            return response()->json($response);
        }
    }
}
