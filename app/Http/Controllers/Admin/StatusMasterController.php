<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CompanyProfile;
use App\Models\StatusMaster;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Validation\Rule;

class StatusMasterController extends Controller
{
    public function index()
    {
        if (request()->ajax()) {
            return DataTables::of(StatusMaster::where('user_id', Auth::id())->orderBy('id', 'ASC'))
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $html = '<td>';
                    $html .= '<a href="' . route('status-master.edit', $row->id) . '" class="avatar bg-light-info p-50 m-0" data-bs-toggle="tooltip" data-placement="left" title="Edit"><i class="fa fa-edit"></i></a>';
                    // $html .= ' <a data-id="' . $row->id . '" href="javascript:void(0);" class="delete avatar bg-light-danger p-50 m-0 text-danger" data-bs-toggle="tooltip" data-placement="left" title="Delete"><i class="fa fa-trash"></i></a>';
                    $html .= '</td>';
                    return $html;
                })
                ->escapeColumns([])
                ->make(true);
        } else {
            return view('admin.status_master.view_status_master');
        }
    }

    public function create()
    {
        return view('admin.status_master.add_status_master');
    }

    public function store(Request $request)
    {
        if (!is_null($request->status_id)) {
            $name = [
                'required',
                Rule::unique('status_masters')->where(function ($query) use ($request) {
                    return $query->where('user_id', Auth::id());
                })->ignore($request->status_id),
            ];
        } else {
            $name = [
                'required',
                Rule::unique('status_masters')->where(function ($query) use ($request) {
                    return $query->where('user_id', Auth::id());
                })->ignore(Auth::id()),
            ];
        }

        $validator = Validator::make($request->all(), [
            'status_name' => $name,
        ], [
            'status_name.required' => 'Enter status',
        ]);
        if ($validator->fails()) {
            $response = ['status' => false, 'message' => 'Please input proper data,', 'errors' => $validator->errors()];
            return response()->json($response);
        }
        DB::beginTransaction();
        try {
            if (!is_null($request->status_id)) {
                $statusMaster = StatusMaster::where('id', $request->status_id)->first();
                $response = ['data' => route('status-master.index'), 'status' => true, 'message' => ' Status updated successfully.'];
            } else {
                $statusMaster = new StatusMaster();
                $response = ['data' => route('status-master.index'), 'status' => true, 'message' => ' Status added successfully.'];
            }
            $companyProfile = CompanyProfile::where('user_id', Auth::id())->first();
            $statusMaster->user_id = Auth::id();
            $statusMaster->company_profile_id = $companyProfile->id;
            $statusMaster->status_name = $request->status_name;
            $result = $statusMaster->save();
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

    public function show(StatusMaster $statusMaster)
    {
        //
    }

    public function edit($id)
    {
        $statusMaster = StatusMaster::where('id', $id)->first();
        return view('admin.status_master.add_status_master', compact('statusMaster'));
    }

    public function update(Request $request, StatusMaster $statusMaster)
    {
        //
    }

    public function destroy($id)
    {
        try {
            $statusMaster = StatusMaster::find($id)->delete();
            $response = ['status' => true, 'message' => 'Status deleted successfully.'];
            return response()->json($response);
        } catch (\Exception $e) {
            $response = ['status' => false, 'server_error' => 'Something went wrong. Please try again.'];
            return response()->json($response);
        }
    }
}
