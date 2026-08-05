<?php

namespace App\Http\Controllers;

use App\Models\Installation;
use App\Models\PenalCompany;
use App\Models\SalesQuatation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class PenalCompanyController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:penal-company-list|penal-company-create|penal-company-edit|penal-company-delete', ['only' => ['index', 'store']]);
        $this->middleware('permission:penal-company-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:penal-company-edit', ['only' => ['edit', 'store']]);
        $this->middleware('permission:penal-company-delete', ['only' => ['destroy']]);
    }

    public function index()
    {
        if (request()->ajax()) {
            return DataTables::of(PenalCompany::orderBy('name', 'ASC')->orderBy('id', 'DESC'))
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $html = '<td>';
                    if (Gate::check('penal-company-edit')) {
                        $html .= '<a data-id="' . $row->id . '" href="javascript:void(0);" class="avatar bg-light-info p-50 m-0 edit" data-bs-toggle="tooltip" data-placement="left" title="Edit"><i class="fa fa-edit"></i></a>';
                    }
                    if (Gate::check('penal-company-delete')) {
                        $html .= ' <a data-id="' . $row->id . '" href="javascript:void(0);" class="avatar bg-light-danger p-50 m-0 delete" data-bs-toggle="tooltip" data-placement="left" title="Delete"><i class="fa fa-trash"></i></a>';
                    }
                    $html .= '</td>';
                    return $html;
                })
                ->editColumn('logo', function ($row) {
                    $logo = '';
                    if (!is_null($row->logo)) {
                        $logo .= '<img height="35" width="35" src="' . asset('upload/company/' . $row->logo) . '" class="img-fluid">';
                    }
                    return $logo;
                })
                ->escapeColumns([])
                ->make(true);
        } else {
            return view('admin.penal_company.index');
        }
    }

    public function create()
    {
        return view('admin.penal_company.index');
    }

    public function store(Request $request)
    {
        if (!is_null($request->penal_company_id)) {
            $name = [
                'required',
                Rule::unique('penal_companies')->where(function ($query) use ($request) {
                    return $query->where('deleted_at', null);
                })->ignore($request->penal_company_id),
            ];
        } else {
            $name = [
                'required',
                Rule::unique('penal_companies')->where(function ($query) use ($request) {
                    return $query->where('deleted_at', null);
                }),
            ];
        }
        $validator = Validator::make($request->all(), [
            // 'name' => ['required', Rule::unique('penal_companies')->where(function ($query) use ($request) {
            //     if (!is_null($request->penal_company_id)) {
            //         return $query->where('deleted_at', null)->where('id', '!=', $request->penal_company_id);
            //     } else {
            //         return $query->where('deleted_at', null);
            //     }
            // })],
            'name' => $name
        ], [
            'name.required' => 'Enter Panel company Name',
            'name.unique' => 'The name has already been taken'
        ]);

        if ($validator->fails()) {
            $response = ['status' => false, 'message' => 'Please input proper data.', 'errors' => $validator->errors()];
            return response()->json($response);
        }
        DB::beginTransaction();
        try {
            if (!is_null($request->penal_company_id)) {
                $penalCompany = PenalCompany::where('id', $request->penal_company_id)->first();
                $response = ['data' => route('penal-company.index'), 'status' => true, 'message' => ' Panel  Company updated successfully.'];
            } else {
                $penalCompany = new PenalCompany();
                $response = ['data' => route('penal-company.index'), 'status' => true, 'message' => ' Panel Company added successfully.'];
            }
            $penalCompany->name = $request->name;
            if (!empty($request->logo)) {
                $PhotosDir = 'upload/company/';
                if (!file_exists($PhotosDir)) {
                    mkdir($PhotosDir, 0777, true);
                }
                $file = $request->logo;
                $extension = $file->getClientOriginalExtension();
                $filename = 'logo-' . time() . '-' . uniqid() . '.' . $extension;
                $file->move('upload/company/', $filename);
                $penalCompany->logo = $filename;
            }
            $result = $penalCompany->save();
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
    public function show(PenalCompany $penalCompany)
    {
        //
    }

    public function edit($id)
    {
        $penalCompany = PenalCompany::where('id', $id)->first();
        if (!is_null($penalCompany)) {
            $res = array('msg_type' => 'success', 'msg_title' => 'Success!', 'result' => $penalCompany);
            header('Content-Type:application/json');
            echo json_encode($res);
        } else {
            return abort(404);
        }
    }

    public function update(Request $request, PenalCompany $penalCompany)
    {
        //
    }

    public function destroy($id)
    {
        try {
            $salesQuatation = SalesQuatation::whereRaw("FIND_IN_SET(?, penal_company_id)", [$id])->first();
            $installation = Installation::where('penal_company_id', $id)->first();
            if (is_null($salesQuatation) && is_null($installation)) {
                $penalCompany = PenalCompany::where('id', $id)->first();
                $path = 'upload/company/' . $penalCompany->log;
                if ($penalCompany->log) {
                    if (File::exists($path)) {
                        unlink($path);
                    }
                }
                $penalCompany->delete();
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
