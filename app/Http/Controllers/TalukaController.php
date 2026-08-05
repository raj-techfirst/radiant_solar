<?php

namespace App\Http\Controllers;

use App\Models\CompanyProfile;
use App\Models\District;
use App\Models\Taluka;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class TalukaController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:taluka-list|city-create|taluka-edit|taluka-delete', ['only' => ['index', 'store']]);
        $this->middleware('permission:taluka-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:taluka-edit', ['only' => ['edit', 'store']]);
        $this->middleware('permission:taluka-delete', ['only' => ['destroy']]);
    }

    public function index()
    {
        $district = District::get();
        if (request()->ajax()) {
            return DataTables::of(Taluka::with('district')->orderBy('name', 'ASC')->orderBy('id', 'DESC'))
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $html = '<td>';
                    if (Gate::check('taluka-edit')) {
                        $html .= '<a data-id="' . $row->id . '" href="javascript:void(0);" class="avatar bg-light-info p-50 m-0 edit" data-bs-toggle="tooltip" data-placement="left" title="Edit"><i class="fa fa-edit"></i></a>';
                    }
                    if (Gate::check('taluka-delete')) {
                        $html .= ' <a data-id="' . $row->id . '" href="javascript:void(0);" class="avatar bg-light-danger p-50 m-0 delete" data-bs-toggle="tooltip" data-placement="left" title="Delete"><i class="fa fa-trash"></i></a>';
                    }
                    $html .= '</td>';
                    return $html;
                })
                ->escapeColumns([])
                ->make(true);
        } else {
            return view('admin.taluka.index', compact('district'));
        }
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        if (!is_null($request->taluka_id)) {
            $name = [
                'required',
                Rule::unique('talukas')->where(function ($query) use ($request) {
                    return $query->where('district_id', $request->district_id)->where('deleted_at', null);
                })->ignore($request->taluka_id),
            ];
        } else {
            $name = [
                'required',
                Rule::unique('talukas')->where(function ($query) use ($request) {
                    return $query->where('district_id', $request->district_id)->where('deleted_at', null);
                }),
            ];
        };
        $validator = Validator::make($request->all(), [
            'district_id' => 'required',
            'name' => $name
        ], [
            'district_id.required' => 'Select District Name',
            'name.required' => 'Enter Taluka Name',
        ]);

        if ($validator->fails()) {
            $response = ['status' => false, 'message' => 'Please input proper data.', 'errors' => $validator->errors()];
            return response()->json($response);
        }
        DB::beginTransaction();
        try {
            if (!is_null($request->taluka_id)) {
                $taluka = Taluka::where('id', $request->taluka_id)->first();
                $response = ['data' => route('taluka.index'), 'status' => true, 'message' => ' Taluka updated successfully.'];
            } else {
                $taluka = new Taluka();
                $response = ['data' => route('taluka.index'), 'status' => true, 'message' => ' Taluka added successfully.'];
            }
            $taluka->district_id = $request->district_id;
            $taluka->name = $request->name;
            $result = $taluka->save();
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

    public function show(Taluka $taluka)
    {
        //
    }

    public function view(Request $request)
    {
        $district = Taluka::where('district_id', $request->district_id)->get();
        return response()->json($district);
    }

    public function edit($id)
    {
        $taluka = Taluka::where('id', $id)->first();
        if (!is_null($taluka)) {
            $res = array('msg_type' => 'success', 'msg_title' => 'Success!', 'result' => $taluka);
            header('Content-Type:application/json');
            echo json_encode($res);
        } else {
            return abort(404);
        }
    }

    public function update(Request $request, Taluka $taluka)
    {
        //
    }

    public function destroy($id)
    {
        try {
            $companyProfile = CompanyProfile::where('city_id', $id)->first();
            if (is_null($companyProfile)) {
                $taluka = Taluka::where('id', $id)->first();
                $taluka->delete();
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

    public function talukaDropdown(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'district_id' => 'required'
        ], [
            'district_id.required' => 'Select District'
        ]);

        if ($validator->fails()) {
            $response = ['status' => false, 'message' => 'Please input proper data.', 'errors' => $validator->errors()];
            return response()->json($response);
        }
        try {
            $taluka = Taluka::select('id', 'name')->where('district_id', $request->district_id)->orderBy('id', 'DESC')->get();
            $response = ['status' => true, 'message' => 'Taluka List', 'taluka' => $taluka];
            return response($response, 200);
        } catch (\Exception $e) {
            $response = ['status' => false, 'server_error' => 'Something went wrong. Please try again.'];
            return response()->json($response);
        }
    }
}
