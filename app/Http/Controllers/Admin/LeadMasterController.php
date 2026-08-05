<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Imports\LeadImport;
use App\Models\CompanyProfile;
use App\Models\LeadMaster;
use App\Models\LeadStatus;
use App\Models\LeadTransection;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\LeadExport;
use App\Models\AgentSalesPerson;
use App\Models\FollowUp;
use App\Models\FollowUpImage;
use App\Models\LeadSource;
use App\Models\SalesQuatation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Gate;
use Yajra\DataTables\Facades\DataTables;

class LeadMasterController extends Controller
{

    function __construct()
    {
        $this->middleware('permission:lead-list|lead-create|lead-edit|lead-delete', ['only' => ['index', 'store']]);
        $this->middleware('permission:lead-create|lead-edit', ['only' => ['create', 'edit', 'store']]);
        $this->middleware('permission:lead-delete', ['only' => ['destroy']]);
    }

    public function index(Request $request)
    {

        $users = User::where('id', Auth::id())->first();
        $where = "1 = 1";

        $company = CompanyProfile::where('user_id', Auth::id())->first();

        if ($company->user_type == 'M') {

            $agent = AgentSalesPerson::where('user_id', Auth::id())->first();
            $agentIds = [$agent->id];
            $assignIds = [$agent->id];

            $sales = CompanyProfile::select('id', 'user_id')->where('manager_id', $company->id)->get();
            if ($sales->count() > 0) {
                foreach ($sales as $k => $v):
                    array_push($agentIds, $v->user_id);
                    array_push($assignIds, $v->id);
                endforeach;
            }
            $where .= ' AND (agent_sales_person_id IN(' . implode(',', $agentIds) . ') OR assign_id IN(' . implode(',', $assignIds) . '))';
        }

        if ($company->user_type == 'S') {
            $agent = AgentSalesPerson::where('user_id', Auth::id())->first();
            $id = $agent->id;
            $where .= ' AND ((agent_sales_person_id = ' . $id . ' OR assign_id = ' . $id . ') OR (assign_id = ' . $company->id . '))';
        }

        $companyFind = CompanyProfile::where('user_id', Auth::id())->first();
        $agentWhere = "";
        if ($companyFind->user_type == 'M') {
            $id = $companyFind->id;
            $agentWhere .= 'company_profiles.user_id = ' . Auth::id() . ' OR  company_profiles.manager_id = ' . $id;
        }
        if ($companyFind->user_type == 'S') {
            $id = $companyFind->id;
            $manager_id = $companyFind->manager_id;
            $agentWhere .= 'company_profiles.id = ' . $id . ' OR  company_profiles.id = ' . $manager_id;
        }

        $q = CompanyProfile::select('agent_sales_people.*')->leftJoin('agent_sales_people', 'agent_sales_people.user_id', 'company_profiles.user_id');
        if ($agentWhere != "") {
            $q->whereRaw($agentWhere);
        }



        $agentSalesPerson = $q->get();

        if (request()->ajax()) {
            return DataTables::of(LeadMaster::with('agentSalesPerson', 'leadStatus')->whereRaw($where)->orderBy('id', 'DESC'))
                ->addIndexColumn()
                ->filter(function ($query) {

                    if (request()->input('todayFollowUp') != "") {
                        $query->whereDate('reminder_date', '<=', Carbon::today());
						 $query->where('lead_status_id', '!=','8')->where('lead_status_id', '!=','9');
                    }
                    if (request()->input('sitevisit') != "") {
                        $query->where('lead_status_id', '4');
                    }

                    if (request()->input('from_date') != "" && request()->input('to_date') == '') {
                        $query->where('created_at', '>=', date('Y-m-d 00:00:00', strtotime(request()->input('from_date'))));
                        $query->where('created_at', '<=', date('Y-m-d 23:59:59'));
                    }
                    if (request()->input('from_date') != "" && request()->input('to_date') != '') {
                        $query->where('created_at', '>=', date('Y-m-d 00:00:00', strtotime(request()->input('from_date'))));
                        $query->where('created_at', '<=', date('Y-m-d 23:59:59', strtotime(request()->input('to_date'))));
                    }
                    if (request()->input('from_date') == "" && request()->input('to_date') != '') {
                        $query->where('created_at', '<=', date('Y-m-d 23:59:59', strtotime(request()->input('to_date'))));
                    }

                    if (request()->input('status') != "" && request()->input('status') != '0') {
                        $query->where('lead_status_id', request()->input('status'));
                    }
                    if (request()->input('assign') != "") {
                        $query->where('agent_sales_person_id', request()->input('assign'));
                    }

                    if (request()->input('consumer') != "") {
                        $query->where(function ($q) {
                            $consumer = request()->input('consumer');
                            $q->where('name', 'like', '%' . $consumer . '%')
                                ->orWhere('mobile', 'like', '%' . $consumer . '%');
                        });
                    }

                })
                ->addColumn('action', function ($row) {
                    $html = '<div>';
                    if (Gate::allows('follow-up-list')) {
                        $html .= '<a href="' . route('follow-up.edit', $row->id) . '" class="avatar bg-light-success p-50 m-0" data-bs-toggle="tooltip" data-placement="left" title="Follow Up"><i class="fa fa-eye"></i></a>';
                    }
                    if ($row->status != '1') {
                        if (Gate::allows('lead-edit')) {
                            $html .= ' <a href="' . route('lead.edit', $row->id) . '" class="avatar bg-light-info p-50 m-0" data-bs-toggle="tooltip" data-placement="left" title="Edit"><i class="fa fa-edit"></i></a>';
                        }
                        if ($row->lead_status_id == 1 && Gate::allows('lead-delete')) {
                            $html .= ' <a data-id="' . $row->id . '" href="javascript:void(0);" class="delete avatar bg-light-danger p-50 m-0" data-bs-toggle="tooltip" data-placement="left" title="Delete"><i class="fa fa-trash"></i></a>';
                        }
                    }
                    $html .= ' <a data-id="' . $row->id . '" data-mobile="' . $row->mobile . '" data-name="' . $row->name . ' ' . $row->last_name . '" href="javascript:void(0)" class="referral_share avatar bg-light-success p-50 m-0" data-bs-toggle="tooltip" data-bs-placement="left" title="Share"><i class="fa fa-whatsapp"></i></a>';
                    $html .= '</div>';
                    return $html;
                })
                ->addColumn('status', function ($row) {

                    //    return  $row->leadStatus->name;
                    $class = $row->leadStatus->class_name ?? '';
                    $name = $row->leadStatus->name ?? '';
                    return '<span class="badge bg-' . $class . ' w-100">' . $name . '</span>';

                })
                ->editColumn('reminder_date', function ($row) {
                    if (!is_null($row->reminder_date)) {
                        return date('d-m-Y h:i A', strtotime($row->reminder_date));
                    } else {
                        return '';
                    }
                })
                ->editColumn('agent_sales_person_id', function ($row) {
                    if ($row->agentSalesPerson) {
                        return $row->agentSalesPerson->name;
                    } else {
                        return '';
                    }
                })
                ->addColumn('name', function ($row) {
                    return $row->name . ' ' . $row->last_name;
                })

                ->addColumn('source', function ($row) {
                    return $row->source->source_name ?? '';
                })
                ->rawColumns(['action', 'status', 'reminder_date', 'name'])
                ->make(true);
        } else {
            $leadStatus = LeadStatus::select('id', 'name')->where('is_for_system', '0')->orderBy('id', 'asc')->get();
            return view('admin.lead.view_lead', compact('agentSalesPerson', 'company', 'leadStatus'));
        }
    }

    public function export(Request $request)
    {
        return Excel::download(new LeadExport($request), 'Lead_Report.xlsx');
    }

    public function create()
    {
        $where = "1 = 1";

        $companyFind = CompanyProfile::where('user_id', Auth::id())->first();
        if ($companyFind->user_type == 'O') {
            $id = $companyFind->id;
            $where .= ' AND company_profiles.user_id =' . Auth::id() . ' OR company_profiles.parent_id =' . $companyFind->id;
        } else {
            $id = $companyFind->parent_id;
            $where .= ' AND (company_profiles.parent_id = ' . $id . ' AND company_profiles.id = ' . $id . ' OR (company_profiles.manager_id = ' . $companyFind->id . ' OR company_profiles.user_id = ' . $companyFind->user_id . ')) OR company_profiles.user_type = "O"';
        }
        $companyProfile = CompanyProfile::with('user')->whereRaw($where)->get();

        $agentWhere = "";
        if ($companyFind->user_type == 'M') {
            $id = $companyFind->id;
            $agentWhere .= 'company_profiles.user_id = ' . Auth::id() . ' OR  company_profiles.manager_id = ' . $id;
        }
        if ($companyFind->user_type == 'S') {
            $id = $companyFind->id;
            $manager_id = $companyFind->manager_id;
            $agentWhere .= 'company_profiles.id = ' . $id . ' OR  company_profiles.id = ' . $manager_id;
        }

        $q = CompanyProfile::select('agent_sales_people.*')->leftJoin('agent_sales_people', 'agent_sales_people.user_id', 'company_profiles.user_id');
        if ($agentWhere != "") {
            $q->whereRaw($agentWhere);
        }

        $agentSalesPerson = $q->get();


        $source = LeadSource::select('id', 'source_name')->orderBy('id', 'DESC')->get();
        $leadStatus = LeadStatus::select('id', 'name')->where('is_for_system', '0')->orderBy('id', 'asc')->get();

        return view('admin.lead.add_lead', compact('agentSalesPerson', 'companyProfile', 'source', 'leadStatus'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'mobile' => 'required',
        ], [
            'mobile.required' => 'Enter mobile'
        ]);
        if ($validator->fails()) {
            $response = ['status' => false, 'message' => 'Please input proper data.', 'errors' => $validator->errors()];
            return response()->json($response);
        }
        DB::beginTransaction();
        try {
            $companyFind = CompanyProfile::where('user_id', Auth::id())->first();

            if ($companyFind->user_type == 'O') {
                $company_profile_id = $companyFind->id;
                $assign_id = $request->assign_id;
            } elseif ($companyFind->user_type == 'M') {
                $company_profile_id = $companyFind->parent_id;
                $assign_id = $request->assign_id;
            } else {
                $company_profile_id = $companyFind->parent_id;
                $assign_id = $companyFind->id;
            }

            $manager = CompanyProfile::where('id', $assign_id)->first();

            if ($manager->user_type == 'O') {
                $manager_id = 0;
            } elseif ($manager->user_type == 'M') {
                $manager_id = $manager->id;
            } else {
                $manager_id = $manager->manager_id;
            }

            if (!is_null($request->lead_id)) {
                $leadMaster = LeadMaster::where('id', $request->lead_id)->first();
                $old_status = $leadMaster->lead_status_id;
                $response = ['data' => route('lead.index'), 'status' => true, 'message' => ' Lead updated successfully.'];
            } else {
                $leadMaster = new LeadMaster();
                $leadMaster->reminder_date = null;
                $response = ['data' => route('lead.index'), 'status' => true, 'message' => ' Lead added successfully.'];
            }

            $leadMaster->user_id = Auth::id();
            $leadMaster->company_profile_id = $company_profile_id;
            $leadMaster->assign_id = $assign_id;
            $leadMaster->manager_id = $manager_id;
            $leadMaster->name = strtoupper($request->name);
            $leadMaster->mobile = $request->mobile;
            $leadMaster->source_id = $request->source_id;
            $leadMaster->address = $request->address;
            $leadMaster->kw = $request->kw;
            $leadMaster->reference = strtoupper($request->reference);
            $leadMaster->agent_sales_person_id = $request->agent_sales_person_id;

            if ($request->lead_status != '') {
                $leadMaster->lead_status_id = $request->lead_status;
            } else {
                if (!$request->lead_id) {
                    $leadMaster->lead_status_id = '0';
                }
            }

            $leadMaster->remark = $request->remark;
            $result = $leadMaster->save();
            DB::commit();
            if (!is_null($result)) {

                if (!is_null($request->lead_id)) {
                    $remark = 'Lead updated.';
                    $status_id = $old_status;
                } else {
                    $remark = 'Lead added.';
                    $status_id = 1;
                }

                $followUp = new FollowUp();
                $followUp->lead_master_id = $leadMaster->id;
                $followUp->call_detail = '';
                $followUp->remark = $remark;
                $company = CompanyProfile::with('user')->where('user_id', Auth::id())->first();
                $followUp->follow_up_by = $company->id;
                $followUp->status_id = $status_id;
                $result = $followUp->save();

                return response()->json($response);
            } else {
                $response = ['status' => false, 'server_error' => 'Something went wrong. Please try again.'];
                return response()->json($response);
            }
        } catch (\Exception $e) {
            DB::rollback();
            dd($e);
            $response = ['status' => false, 'server_error' => 'Something went wrong. Please try again.'];
            return response()->json($response);
        }
    }

    public function show(Request $request, $id)
    {
        $leadMaster = LeadMaster::where('id', $id)->first();
        if (!is_null($leadMaster)) {
            $leadMaster->status_master_id = $request->status;
            $leadMaster->save();
            $response = ['status' => true, 'message' => ' Status updated successfully.'];
            return response()->json($response);
        } else {
            return abort(404);
        }
    }

    public function edit($id)
    {
        $lead = LeadMaster::where('id', $id)->first();
        if (!is_null($lead)) {


            $where = "1 = 1";
            $companyFind = CompanyProfile::where('user_id', Auth::id())->first();
            if ($companyFind->user_type == 'O') {
                $id = $companyFind->id;
                $where .= ' AND company_profiles.user_id =' . Auth::id() . ' OR company_profiles.parent_id =' . $companyFind->id;
            } else {
                $id = $companyFind->parent_id;
                $where .= ' AND company_profiles.parent_id = ' . $id . ' AND company_profiles.id = ' . $id . ' OR (company_profiles.manager_id = ' . $companyFind->id . ' OR company_profiles.user_id = ' . $companyFind->user_id . ')';
            }

            $companyProfile = CompanyProfile::with('user')->whereRaw($where)->get();

            $companyFind = CompanyProfile::where('user_id', Auth::id())->first();
            $agentWhere = "";
            if ($companyFind->user_type == 'M') {
                $id = $companyFind->id;
                $agentWhere .= 'company_profiles.user_id = ' . Auth::id() . ' OR  company_profiles.manager_id = ' . $id;
            }
            if ($companyFind->user_type == 'S') {
                $id = $companyFind->id;
                $manager_id = $companyFind->manager_id;
                $agentWhere .= 'company_profiles.id = ' . $id . ' OR  company_profiles.id = ' . $manager_id;
            }

            $q = CompanyProfile::select('agent_sales_people.*')->leftJoin('agent_sales_people', 'agent_sales_people.user_id', 'company_profiles.user_id');
            if ($agentWhere != "") {
                $q->whereRaw($agentWhere);
            }

            $agentSalesPerson = $q->get();


            $source = LeadSource::select('id', 'source_name')->orderBy('id', 'DESC')->get();
            $leadStatus = LeadStatus::select('id', 'name')->where('is_for_system', '0')->orderBy('id', 'asc')->get();

            return view('admin.lead.add_lead', compact('lead', 'companyProfile', 'agentSalesPerson','source', 'leadStatus'));
        } else {
            return abort(404);
        }
    }

    public function import(Request $request)
    {
        try {
            $file = $request->file('excel_file');
            Excel::import(new LeadImport, $file);
            $response = ['data' => route('lead.index'), 'status' => true, 'message' => ' Lead added successfully.'];
            return response()->json($response);
        } catch (\Exception $e) {
            dd($e);
            $response = ['status' => false, 'server_error' => 'Something went wrong. Please try again.'];
            return response()->json($response);
        }
    }

    public function sampleDownload()
    {
        return Excel::download(new \App\Exports\LeadSampleExport, 'lead_import_sample.xlsx');
    }

    public function update(Request $request)
    {
        $users = User::where('id', Auth::id())->first();
        $where = "1 = 1";
        $company = CompanyProfile::where('user_id', Auth::id())->first();

        if ($company->user_type == 'M') {

            $agent = AgentSalesPerson::where('user_id', Auth::id())->first();
            $agentIds = [$agent->id];
            $assignIds = [$agent->id];

            $sales = CompanyProfile::select('id', 'user_id')->where('manager_id', $company->id)->get();
            if ($sales->count() > 0) {
                foreach ($sales as $k => $v):
                    array_push($agentIds, $v->user_id);
                    array_push($assignIds, $v->id);
                endforeach;
            }
            $where .= ' AND (agent_sales_person_id IN(' . implode(',', $agentIds) . ') OR assign_id IN(' . implode(',', $assignIds) . '))';
        }

        if ($company->user_type == 'S') {
            $agent = AgentSalesPerson::where('user_id', Auth::id())->first();
            $id = $agent->id;
            $where .= ' AND ((agent_sales_person_id = ' . $id . ' OR assign_id = ' . $id . ') OR (assign_id = ' . $company->id . '))';
        }

        $companyFind = CompanyProfile::where('user_id', Auth::id())->first();
        $agentWhere = "";
        if ($companyFind->user_type == 'M') {
            $id = $companyFind->id;
            $agentWhere .= 'company_profiles.user_id = ' . Auth::id() . ' OR  company_profiles.manager_id = ' . $id;
        }
        if ($companyFind->user_type == 'S') {
            $id = $companyFind->id;
            $manager_id = $companyFind->manager_id;
            $agentWhere .= 'company_profiles.id = ' . $id . ' OR  company_profiles.id = ' . $manager_id;
        }

        $q = CompanyProfile::select('agent_sales_people.*')->leftJoin('agent_sales_people', 'agent_sales_people.user_id', 'company_profiles.user_id');
        if ($agentWhere != "") {
            $q->whereRaw($agentWhere);
        }

        $agentSalesPerson = $q->get();

        if (request()->ajax()) {
            return DataTables::of(LeadMaster::with('agentSalesPerson')->whereRaw($where)->orderBy('id', 'DESC'))
                ->addIndexColumn()
                ->filter(function ($query) {

                    if (request()->input('todayFollowUp') != "") {
                        $query->whereDate('reminder_date', '<=', Carbon::today());
                    }
                    if (request()->input('sitevisit') != "") {
                        $query->where('lead_status_id', '4');
                    }

                    if (request()->input('from_date') != "" && request()->input('to_date') == '') {
                        $query->where('created_at', '>=', date('Y-m-d 00:00:00', strtotime(request()->input('from_date'))));
                        $query->where('created_at', '<=', date('Y-m-d 23:59:59'));
                    }
                    if (request()->input('from_date') != "" && request()->input('to_date') != '') {
                        $query->where('created_at', '>=', date('Y-m-d 00:00:00', strtotime(request()->input('from_date'))));
                        $query->where('created_at', '<=', date('Y-m-d 23:59:59', strtotime(request()->input('to_date'))));
                    }
                    if (request()->input('from_date') == "" && request()->input('to_date') != '') {
                        $query->where('created_at', '<=', date('Y-m-d 23:59:59', strtotime(request()->input('to_date'))));
                    }


                    $query->where('lead_status_id', 8);

                    if (request()->input('assign') != "") {
                        $query->where('agent_sales_person_id', request()->input('assign'));
                    }

                    if (request()->input('consumer') != "") {
                        $query->where(function ($q) {
                            $consumer = request()->input('consumer');
                            $q->where('name', 'like', '%' . $consumer . '%')
                                ->orWhere('mobile', 'like', '%' . $consumer . '%');
                        });
                    }
                })
                ->addColumn('action', function ($row) {
                    $html = '<div>';
                    if (Gate::allows('follow-up-list')) {
                        $html .= '<a href="' . route('follow-up.edit', $row->id) . '" class="avatar bg-light-success p-50 m-0" data-bs-toggle="tooltip" data-placement="left" title="Follow Up"><i class="fa fa-eye"></i></a>';
                    }
                    $html .= '</div>';
                    return $html;
                })
                ->editColumn('status', function ($row) {
                    return '<span class="badge bg-' . $row->leadStatus->class_name . ' w-100">' . $row->leadStatus->name . '</span>';
                })
                ->editColumn('reminder_date', function ($row) {
                    if (!is_null($row->reminder_date)) {
                        return date('d-m-Y h:i A', strtotime($row->reminder_date));
                    } else {
                        return '';
                    }
                })
                ->editColumn('agent_sales_person_id', function ($row) {
                    if ($row->agentSalesPerson) {
                        return $row->agentSalesPerson->name;
                    } else {
                        return '';
                    }
                })
                ->addColumn('name', function ($row) {
                    return $row->name . ' ' . $row->last_name;
                })

                ->addColumn('source', function ($row) {
                    return $row->source->source_name ?? '';
                })
                ->rawColumns(['action', 'status', 'reminder_date', 'name'])
                ->make(true);

        } else {
            return view('admin.lead.completed_lead', compact('agentSalesPerson', 'company'));
        }
    }

    public function destroy($id)
    {
        try {
            $salesQuatation = SalesQuatation::where('lead_master_id', $id)->first();
            if (is_null($salesQuatation)) {
                $leadMaster = LeadMaster::where('id', $id)->first();
                LeadTransection::where('lead_master_id', $leadMaster->id)->delete();
                FollowUp::where('lead_master_id', $leadMaster->id)->delete();
                FollowUpImage::where('lead_master_id', $leadMaster->id)->delete();
                $leadMaster->delete();
                $response = ['status' => true, 'message' => 'Deleted successfully.'];
            } else {
                $response = ['status' => false, 'message' => "You cannot delete this lead because it is referenced in a sales quotation."];
            }
            return response()->json($response);
        } catch (\Exception $e) {
            $response = ['status' => false, 'server_error' => 'Something went wrong. Please try again.'];
            return response()->json($response);
        }
    }
    public function searchlead(Request $request)
    {
        $lead = LeadMaster::with('agentSalesPerson')->where('mobile', $request->mobileNumber)->orderBy('id', 'DESC')->get();
        if (isset($request->type) && $request->type == "rateCalc") {
            $name = ($lead->count() > 0) ? $lead[0]->name : '';
            return response()->json($name);
        }
        return response()->json($lead);
    }
}
