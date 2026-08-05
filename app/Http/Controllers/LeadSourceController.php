<?php

namespace App\Http\Controllers;

use App\Models\LeadSource;
use App\Models\CompanyProfile;
use Illuminate\Http\Request;
use App\Models\LeadMaster;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Validation\Rule;

class LeadSourceController extends Controller
{

    function __construct()
    {
        $this->middleware('permission:lead-source-list|lead-source-create|lead-source-edit|lead-source-delete', ['only' => ['index', 'store']]);
        $this->middleware('permission:lead-source-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:lead-source-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:lead-source-delete', ['only' => ['destroy']]);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (request()->ajax()) {
            return DataTables::of(LeadSource::orderBy('id', 'DESC'))
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $html = '<td>';
                    if (Gate::check('lead-source-edit')) {
                        $html .= '<a data-id="' . $row->id . '" href="javascript:void(0);" class="avatar bg-light-info p-50 m-0 edit" data-bs-toggle="tooltip" data-placement="left" title="Edit"><i class="fa fa-edit"></i></a>';
                    }
                    if (Gate::check('lead-source-delete')) {
                        $html .= ' <a data-id="' . $row->id . '" href="javascript:void(0);" class="avatar bg-light-danger p-50 m-0 delete" data-bs-toggle="tooltip" data-placement="left" title="Delete"><i class="fa fa-trash"></i></a>';
                    }
                    $html .= '</td>';
                    return $html;
                })
                ->escapeColumns([])
                ->make(true);
        } else {
            return view('admin.leadsource.leadsource');
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (!is_null($request->source_id)) {
            $name = [
                'required',
                Rule::unique('lead_sources')->where(function ($query) use ($request) {
                    return $query->where('deleted_at', null);
                })->ignore($request->source_id),
            ];
        } else {
            $name = [
                'required',
                Rule::unique('lead_sources')->where(function ($query) use ($request) {
                    return $query->where('deleted_at', null);
                }),
            ];
        }
        $validator = Validator::make($request->all(), [
            'source_name' => $name
        ], [
            'source_name.required' => 'Enter lead source',
            'source_name.unique' => 'The lead source has already been taken'
        ]);

        if ($validator->fails()) {
            $response = ['status' => false, 'message' => 'Please input proper data.', 'errors' => $validator->errors()];
            return response()->json($response);
        }
        DB::beginTransaction();
        try {
            if (!is_null($request->source_id)) {
                $source = LeadSource::where('id', $request->source_id)->first();
                $response = ['data' => route('lead-source.index'), 'status' => true, 'message' => 'Lead source updated successfully.'];
            } else {
                $source = new LeadSource();
                $response = ['data' => route('lead-source.index'), 'status' => true, 'message' => 'Lead source added successfully.'];
            }
            $companyProfile = CompanyProfile::where('user_id', Auth::id())->first();
            $source->user_id = Auth::id();
            $source->company_profile_id = $companyProfile->id;
            $source->source_name = $request->source_name;
            $result = $source->save();
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

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\LeadSource  $leadSource
     * @return \Illuminate\Http\Response
     */
    public function show(LeadSource $leadSource)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\LeadSource  $leadSource
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
         $source = LeadSource::where('id', $id)->first();
        if (!is_null($source)) {
            $res = array('msg_type' => 'success', 'msg_title' => 'Success!', 'result' => $source);
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
     * @param  \App\Models\LeadSource  $leadSource
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, LeadSource $leadSource)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\LeadSource  $leadSource
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $source = LeadSource::where('id', $id)->first();
            $leadMaster = LeadMaster::where('source_id', $source->id)->count();
            if ($leadMaster <= 0) {
                $source->delete();
                $response = ['status' => true, 'message' => ' Deleted successfully.'];
            } else {
                $response = ['status' => false, 'message' => ' Also used this product.'];
            }
            return response()->json($response);
        } catch (\Exception $e) {
            $response = ['status' => false, 'server_error' => 'Something went wrong. Please try again.'];
            return response()->json($response);
        }
    }
}
