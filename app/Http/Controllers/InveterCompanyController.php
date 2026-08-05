<?php

namespace App\Http\Controllers;

use App\Models\Installation;
use App\Models\InstallationInvater;
use App\Models\InveterCompany;
use App\Models\SalesQuatation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class InveterCompanyController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:inveter-company-list|inveter-company-create|inveter-company-edit|inveter-company-delete', ['only' => ['index', 'store']]);
        $this->middleware('permission:inveter-company-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:inveter-company-edit', ['only' => ['edit', 'store']]);
        $this->middleware('permission:inveter-company-delete', ['only' => ['destroy']]);
    }

    public function index()
    {
        if (request()->ajax()) {
            return DataTables::of(InveterCompany::orderBy('name', 'ASC')->orderBy('id', 'DESC'))
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $html = '<td>';
                    if (Gate::check('inveter-company-edit')) {
                        $html .= '<a data-id="' . $row->id . '" href="javascript:void(0);" class="avatar bg-light-info p-50 m-0 edit" data-bs-toggle="tooltip" data-placement="left" title="Edit"><i class="fa fa-edit"></i></a>';
                    }
                    if (Gate::check('inveter-company-delete')) {
                        $html .= ' <a data-id="' . $row->id . '" href="javascript:void(0);" class="avatar bg-light-danger p-50 m-0 delete" data-bs-toggle="tooltip" data-placement="left" title="Delete"><i class="fa fa-trash"></i></a>';
                    }
                    $html .= '</td>';
                    return $html;
                })
                ->escapeColumns([])
                ->make(true);
        } else {
            return view('admin.inveter_company.index');
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.inveter_company.index');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', Rule::unique('inveter_companies')->where(function ($query) use ($request) {
                if (!is_null($request->inveter_company_id)) {
                    return $query->where('deleted_at', null)->where('id', '!=', $request->inveter_company_id);
                } else {
                    return $query->where('deleted_at', null);
                }
            })],
        ], [
            'name.required' => 'Enter Name',
            'name.unique' => 'The name has already been taken'
        ]);
        if ($validator->fails()) {
            $response = ['status' => false, 'message' => 'The name has already been taken.', 'errors' => $validator->errors()];
            return response()->json($response);
        }
        DB::beginTransaction();
        try {
            if (!is_null($request->inveter_company_id)) {
                $inveterCompany = InveterCompany::where('id', $request->inveter_company_id)->first();
                $response = ['data' => route('inveter-company.index'), 'status' => true, 'message' => ' Inveter Company updated successfully.'];
            } else {
                $inveterCompany = new InveterCompany();
                $response = ['data' => route('inveter-company.index'), 'status' => true, 'message' => ' Inveter Company added successfully.'];
            }
            $inveterCompany->name = $request->name;
            $result = $inveterCompany->save();
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

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\InveterCompany  $inveterCompany
     * @return \Illuminate\Http\Response
     */
    public function show(InveterCompany $inveterCompany)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\InveterCompany  $inveterCompany
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $inveterCompany = InveterCompany::where('id', $id)->first();
        if (!is_null($inveterCompany)) {
            $res = array('msg_type' => 'success', 'msg_title' => 'Success!', 'result' => $inveterCompany);
            header('Content-Type:application/json');
            echo json_encode($res);
        } else {
            return abort(404);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\InveterCompany  $inveterCompany
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, InveterCompany $inveterCompany)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\InveterCompany  $inveterCompany
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $salesQuatation = SalesQuatation::whereRaw("FIND_IN_SET(?, inveter_company_id)", [$id])->first();
            $installation = InstallationInvater::where('invater_id', $id)->first();
            if (is_null($salesQuatation) && is_null($installation)) {
                $inveterCompany = InveterCompany::where('id', $id)->first();
                $inveterCompany->delete();
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
