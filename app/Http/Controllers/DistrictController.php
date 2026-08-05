<?php

namespace App\Http\Controllers;

use App\Models\CompanyProfile;
use App\Models\District;
use App\Models\State;
use App\Models\Taluka;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class DistrictController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:district-list|city-create|district-edit|district-delete', ['only' => ['index', 'store']]);
        $this->middleware('permission:district-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:district-edit', ['only' => ['edit', 'store']]);
        $this->middleware('permission:district-delete', ['only' => ['destroy']]);
    }

    public function index()
    {
        if (request()->ajax()) {
            return DataTables::of(District::with('state')->orderBy('name', 'ASC')->orderBy('id', 'DESC'))
                ->addIndexColumn()
                ->addColumn('state_name',function($row){
                    return ($row->state != null) ? $row->state->state_name :'';
                })
                ->addColumn('action', function ($row) {
                    $html = '<td>';
                    if (Gate::check('district-edit')) {
                        $html .= '<a data-id="' . $row->id . '" href="javascript:void(0);" class="avatar bg-light-info p-50 m-0 edit" data-bs-toggle="tooltip" data-placement="left" title="Edit"><i class="fa fa-edit"></i></a>';
                    }
                    if (Gate::check('district-delete')) {
                        $html .= ' <a data-id="' . $row->id . '" href="javascript:void(0);" class="avatar bg-light-danger p-50 m-0 delete" data-bs-toggle="tooltip" data-placement="left" title="Delete"><i class="fa fa-trash"></i></a>';
                    }
                    $html .= '</td>';
                    return $html;
                })
                ->escapeColumns([])
                ->make(true);
        } else {
            $state = State::get();
            return view('admin.district.index', compact('state'));
        }
    }

    public function create()
    {
        return view('admin.district.index');
    }

    public function store(Request $request)
    {
        if (!is_null($request->district_id)) {
            $name = [
                'required',
                Rule::unique('districts')->where(function ($query) use ($request) {
                    return $query->where('deleted_at', null);
                })->ignore($request->district_id),
            ];
        } else {
            $name = [
                'required',
                Rule::unique('districts')->where(function ($query) use ($request) {
                    return $query->where('deleted_at', null);
                }),
            ];
        }
        $validator = Validator::make($request->all(), [
            'name' => $name,
            'state_id' => 'required'
        ], [
            'name.required' => 'Enter district Name',
            'state_id.required' => 'Select State',
        ]);

        if ($validator->fails()) {
            $response = ['status' => false, 'message' => 'Please input proper data.', 'errors' => $validator->errors()];
            return response()->json($response);
        }
        DB::beginTransaction();
        try {
            if (!is_null($request->district_id)) {
                $district = District::where('id', $request->district_id)->first();
                $response = ['data' => route('district.index'), 'status' => true, 'message' => ' District updated successfully.'];
            } else {
                $district = new District();
                $response = ['data' => route('district.index'), 'status' => true, 'message' => ' District added successfully.'];
            }
            $district->state_id = $request->state_id;
            $district->name = $request->name;
            $result = $district->save();
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
    public function show(District $district)
    {
        // $city = District::where('state_id', $request->state_id)->get();
        // return response()->json($city);
    }

    public function edit($id)
    {
        $district = District::where('id', $id)->first();
        if (!is_null($district)) {
            $res = array('msg_type' => 'success', 'msg_title' => 'Success!', 'result' => $district);
            header('Content-Type:application/json');
            echo json_encode($res);
        } else {
            return abort(404);
        }
    }


    public function update(Request $request, District $district)
    {
        //
    }

    public function destroy($id)
    {
        try {
            $companyProfile = CompanyProfile::where('state_id', $id)->first();
            $taluka = Taluka::where('district_id', $id)->first();
            if (is_null($taluka) && is_null($companyProfile)) {
                $district = District::where('id', $id)->first();
                $district->delete();
                $response = ['status' => true, 'message' => ' Deleted successfully.'];
            } else {
                $response = ['status' => false, 'message' => "You cannot delete this because it is being utilized elsewhere."];
            }
            return response()->json($response);
        } catch (\Exception $e) {
            $response = ['status' => false, 'server_error' => 'Something went wrong. Please try again.'];
            return response()->json($response);
        }
    }


    public function districtListDropdown()
    {
        $district = District::select('id', 'name')->orderBy('id', 'DESC')->get();
        $response = ['status' => true, 'message' => 'District List', 'district' => $district];
        return response($response, 200);
    }
}
