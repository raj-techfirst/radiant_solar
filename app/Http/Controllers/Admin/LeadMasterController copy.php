<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Imports\LeadImport;
use App\Models\Category;
use App\Models\City;
use App\Models\CompanyProfile;
use App\Models\Estimate;
use App\Models\EstimateItem;
use App\Models\LeadMaster;
use App\Models\LeadTransection;
use App\Models\Product;
use App\Models\Source;
use App\Models\State;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\LeadExport;
use App\Models\AgentSalesPerson;
use App\Models\FollowUp;
use App\Models\FollowUpImage;
use App\Models\Notification;
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
        $this->middleware('permission:lead-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:lead-edit', ['only' => ['edit', 'store']]);
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
                foreach ($sales as $k => $v) :
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

        //$companyProfile = CompanyProfile::with('user')->get();

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
                        $query->whereDate('reminder_date', '<=', Carbon::today())
                            ->where('status', '3');
                    }
                    if (request()->input('sitevisit') != "") {
                        $query->where('status', '4');
                    }
                    if (request()->input('mstatus') == "") {
                        $query->whereNotIn('status', ['1']);
                    }
                    if (request()->input('mstatus') == "1") {
                        $query->where('status', '1');
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

                    if (request()->input('status') != "") {
                        $query->where('status', request()->input('status'));
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
                        if (Gate::allows('lead-delete')) {
                            $html .= ' <a data-id="' . $row->id . '" href="javascript:void(0);" class="delete avatar bg-light-danger p-50 m-0" data-bs-toggle="tooltip" data-placement="left" title="Delete"><i class="fa fa-trash"></i></a>';
                        }
                    }
                    $html .= ' <a data-id="' . $row->id . '" data-mobile="' . $row->mobile . '" data-name="' . $row->name . ' ' . $row->last_name . '" href="javascript:void(0)" class="referral_share avatar bg-light-success p-50 m-0" data-bs-toggle="tooltip" data-bs-placement="left" title="Share"><i class="fa fa-whatsapp"></i></a>';
                    $html .= '</div>';
                    return $html;
                })
                ->editColumn('status', function ($row) {
                    if ($row->status == '0') {
                        return '<span class="badge bg-light-warning w-100">New</span>';
                    } elseif ($row->status == '1') {
                        return '<span class="badge bg-light-success w-100">Completed</span>';
                    } elseif ($row->status == '2') {
                        return '<span class="badge bg-light-danger w-100">Not Interested</span>';
                    } elseif ($row->status == '3') {
                        return '<span class="badge bg-light-primary w-100">Next Follow up</span>';
                    } elseif ($row->status == '4') {
                        return '<span class="badge bg-light-info w-100">Site Visit</span>';
                    } elseif ($row->status == '5') {
                        return '<span class="badge bg-light-success w-100">Make Quatation</span>';
                    }
                })
                ->editColumn('reminder_date', function ($row) {
                    if (!is_null($row->reminder_date)) {
                        return date('d-m-Y', strtotime($row->reminder_date));
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
                ->rawColumns(['action', 'status', 'reminder_date', 'name'])
                ->make(true);
        } else {
            return view('admin.lead.view_lead', compact('agentSalesPerson', 'company'));
        }
    }

    public function export(Request $request)
    {
        return Excel::download(new LeadExport($request), 'Lead_Report.xlsx');
    }

    public function create()
    {

        $state = State::get();

        $city = [];
        $where = "1 = 1";

        $companyFind = CompanyProfile::where('user_id', Auth::id())->first();
        if ($companyFind->user_type == 'O') {
            $id = $companyFind->id;
            $where .= ' AND company_profiles.user_id =' . Auth::id() . ' OR company_profiles.parent_id =' . $companyFind->id;
        } else {
            $id = $companyFind->parent_id;
            $where .= ' AND (company_profiles.parent_id = ' . $id . ' AND company_profiles.id = ' . $id . ' OR (company_profiles.manager_id = ' . $companyFind->id . ' OR company_profiles.user_id = ' . $companyFind->user_id . ')) OR company_profiles.user_type = "O"';
        }
        $product = Product::get();
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


        return view('admin.lead.add_lead', compact('city', 'agentSalesPerson', 'state', 'companyProfile', 'product'));
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
            $leadMaster->address = $request->address;
            $leadMaster->kw = $request->kw;
            $leadMaster->reference = strtoupper($request->reference);
            $leadMaster->agent_sales_person_id = $request->agent_sales_person_id;

            if ($request->lead_status != '') {
                $leadMaster->status = $request->lead_status;
            } else {
                if (!$request->lead_id) {
                    $leadMaster->status = '0';
                    $leadMaster->status_master_id = 0;
                }
            }

            $leadMaster->remark = $request->remark;
            $result = $leadMaster->save();
            DB::commit();
            if (!is_null($result)) {
                $leadTransection = new LeadTransection();
                $leadTransection->lead_master_id = $leadMaster->id;
                $leadTransection->assign_id = $assign_id;
                $company = CompanyProfile::with('user')->where('user_id', Auth::id())->first();
                $leadTransection->assign_by = $company->id;
                $leadTransection->save();

                if (!$request->lead_id) {
                    if ($companyFind->user_type == 'O' && $assign_id == $manager_id) {
                        $notification = new Notification();
                        // $notification->user_id = $assign_id;
                        $notification->user_id = $assign_id . ',' . $company_profile_id;
                        $notification->title = "New Lead";
                        $notification->description = $leadMaster->lead_title . " Lead has been Assigned to you.";
                        $notification->is_read = '0';
                        $notification->count = '0';
                        $notification->save();
                    } elseif ($companyFind->user_type == 'O' && $assign_id != $manager_id && $assign_id != $company_profile_id) {
                        for ($i = 1; $i <= 2; $i++) {
                            $notification = new Notification();
                            $notification->title = "New Lead";
                            if ($i == 1) {
                                $notification->user_id = $manager_id;
                            } else {
                                $notification->user_id = $assign_id;
                            }
                            // $notification->user_id = $assign_id.','.$manager_id.','.$company_profile_id;
                            $notification->description = $leadMaster->lead_title . " Lead has been Assigned to you.";
                            $notification->is_read = '0';
                            $notification->count = '0';
                            $notification->save();
                        }
                    } elseif ($companyFind->user_type == 'M' && $assign_id != $manager_id) {
                        $notification = new Notification();
                        $notification->user_id = $assign_id;
                        $notification->title = "New Lead";
                        $notification->description = $leadMaster->lead_title . " Lead has been Assigned to you.";
                        $notification->is_read = '0';
                        $notification->count = '0';
                        $notification->save();
                    } else {
                    }
                }
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

    public function leadApi(Request $request)
    {
        try {
            $source = Source::where('source_name', $request->source)->first();
            $end_date = date("d-M-Y");
            $start_date = date("d-M-Y", strtotime("-7 day"));
            $company = CompanyProfile::where('user_id', Auth::id())->first();
            if (!is_null($company->indiamart_key)) {
                $link = $source->source_link . $company->indiamart_key . '&start_time=' . $start_date . '&end_time=' . $end_date;

                $ch = curl_init();
                // Will return the response, if false it print the response
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                // Set the url
                curl_setopt($ch, CURLOPT_URL, $link);
                // Execute
                $result = curl_exec($ch);
                // Closing
                curl_close($ch);

                $data = json_decode($result, true);
                if ($data['STATUS'] == 'SUCCESS') {
                    foreach ($data['RESPONSE'] as $item) {
                        $lead = LeadMaster::where('india_mart_unique_id', $item['UNIQUE_QUERY_ID'])->first();
                        if (is_null($lead)) {
                            $leadMaster = new LeadMaster();
                            $leadMaster->user_id = Auth::id();
                            $leadMaster->company_profile_id = $company->id;
                            $leadMaster->assign_id = $company->id;
                            $leadMaster->india_mart_unique_id = $item['UNIQUE_QUERY_ID'];
                            $leadMaster->query_type = $item['QUERY_TYPE'];
                            $leadMaster->query_time = $item['QUERY_TIME'];
                            $leadMaster->name = $item['SENDER_NAME'];
                            $leadMaster->mobile = substr($item['SENDER_MOBILE'], 4);
                            $leadMaster->email = $item['SENDER_EMAIL'];
                            $leadMaster->lead_title = $item['SUBJECT'];
                            $leadMaster->company_name = $item['SENDER_COMPANY'];
                            $leadMaster->source_id = $source->id;

                            //category
                            if (!is_null($item['QUERY_MCAT_NAME'])) {
                                $category = Category::where('category_name', $item['QUERY_MCAT_NAME'])->first();
                                if (!is_null($category)) {
                                    $leadMaster->category_id = $category->id;
                                } else {
                                    $categoryAdd = new Category();
                                    $categoryAdd->category_name = $item['QUERY_MCAT_NAME'];
                                    $categoryAdd->user_id = Auth::id();
                                    $categoryAdd->company_profile_id = $company->id;
                                    $categoryAdd->save();
                                    $leadMaster->category_id = $categoryAdd->id;
                                }
                            }

                            //product
                            if (!is_null($item['QUERY_PRODUCT_NAME'])) {
                                $product = Product::where('product_name', $item['QUERY_PRODUCT_NAME'])->first();
                                if (!is_null($product)) {
                                    $leadMaster->product_id = $product->id;
                                } else {
                                    $productAdd = new Product();
                                    $productAdd->product_name = $item['QUERY_PRODUCT_NAME'];
                                    $productAdd->user_id = Auth::id();
                                    $productAdd->company_profile_id = $company->id;
                                    $productAdd->save();
                                    $leadMaster->product_id = $productAdd->id;
                                }
                            }

                            //state
                            if (!is_null($item['SENDER_STATE'])) {
                                $state = State::where('state_name', $item['SENDER_STATE'])->first();
                                if (!is_null($state)) {
                                    $stateID = $state->id;
                                } else {
                                    $stateAdd = new State();
                                    $stateAdd->state_name = $item['SENDER_STATE'];
                                    $stateAdd->save();
                                    $stateID = $stateAdd->id;
                                }
                                $leadMaster->state_id = $stateID;
                            }

                            //city
                            if (!is_null($item['SENDER_CITY'])) {
                                $city = City::where('city_name', $item['SENDER_CITY'])->first();
                                if (!is_null($city)) {
                                    $leadMaster->city_id = $city->id;
                                } else {
                                    $cityAdd = new City();
                                    $cityAdd->state_id = $stateID;
                                    $cityAdd->city_name = $item['SENDER_CITY'];
                                    $cityAdd->save();
                                    $leadMaster->city_id = $cityAdd->id;
                                }
                            }

                            $leadMaster->address = $item['SENDER_ADDRESS'];
                            $leadMaster->pincode = $item['SENDER_PINCODE'];
                            $leadMaster->notes = $item['QUERY_MESSAGE'];
                            $leadMaster->status = '0';
                            $leadMaster->status_master_id = 0;

                            $result = $leadMaster->save();
                            DB::commit();
                            if (!is_null($result)) {
                                $leadTransection = new LeadTransection();
                                $leadTransection->lead_master_id = $leadMaster->id;
                                $leadTransection->assign_id = $company->id;
                                $leadTransection->assign_by = $company->id;
                                $leadTransection->save();
                                $response = ['status' => true, 'message' => 'IndiaMart lead added successfully.'];
                                return response()->json($response);
                            } else {
                                $response = ['status' => false, 'server_error' => 'Something went wrong. Please try again.'];
                                return response()->json($response);
                            }
                        }
                    }
                } else {
                    $response = ['status' => false, 'message' => 'Please try again after 5 minutes.'];
                    return response()->json($response);
                }
            }
        } catch (\Exception $e) {
            DB::rollback();
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
            $source = Source::get();
            $state = State::get();
            $city = City::where('state_id', $lead->state_id)->get();
            $where = "1 = 1";
            $companyFind = CompanyProfile::where('user_id', Auth::id())->first();
            if ($companyFind->user_type == 'O') {
                $id = $companyFind->id;
                $where .= ' AND company_profiles.user_id =' . Auth::id() . ' OR company_profiles.parent_id =' . $companyFind->id;
            } else {
                $id = $companyFind->parent_id;
                $where .= ' AND company_profiles.parent_id = ' . $id . ' AND company_profiles.id = ' . $id . ' OR (company_profiles.manager_id = ' . $companyFind->id . ' OR company_profiles.user_id = ' . $companyFind->user_id . ')';
            }
            $product = Product::get();

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
            return view('admin.lead.add_lead', compact('lead', 'source', 'state', 'city', 'companyProfile', 'product', 'agentSalesPerson'));
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
            $response = ['status' => false, 'server_error' => 'Something went wrong. Please try again.'];
            return response()->json($response);
        }
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
                foreach ($sales as $k => $v) :
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

                    if (request()->input('mstatus') == "") {
                        $query->whereNotIn('status', ['1']);
                    }
                    if (request()->input('mstatus') == "1") {
                        $query->where('status', '1');
                    }

                    if (request()->input('from_date') != "" && request()->input('to_date') == '') {
                        $query->where('created_at', '>=', date('Y-m-d', strtotime(request()->input('from_date'))));
                        $query->where('created_at', '<=', date('Y-m-d'));
                    }
                    if (request()->input('from_date') != "" && request()->input('to_date') != '') {
                        $query->where('created_at', '>=', date('Y-m-d', strtotime(request()->input('from_date'))));
                        $query->where('created_at', '<=', date('Y-m-d', strtotime(request()->input('to_date'))));
                    }
                    if (request()->input('from_date') == "" && request()->input('to_date') != '') {
                        $query->where('created_at', '<=', date('Y-m-d', strtotime(request()->input('to_date'))));
                    }

                    if (request()->input('status') != "") {
                        $query->where('status', request()->input('status'));
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
                        if (Gate::allows('lead-delete')) {
                            $html .= ' <a data-id="' . $row->id . '" href="javascript:void(0);" class="delete avatar bg-light-danger p-50 m-0" data-bs-toggle="tooltip" data-placement="left" title="Delete"><i class="fa fa-trash"></i></a>';
                        }
                    }
                    $html .= ' <a data-id="' . $row->id . '" data-mobile="' . $row->mobile . '" data-name="' . $row->name . ' ' . $row->last_name . '" href="javascript:void(0)" class="referral_share avatar bg-light-success p-50 m-0" data-bs-toggle="tooltip" data-bs-placement="left" title="Share"><i class="fa fa-whatsapp"></i></a>';
                    $html .= '</div>';
                    return $html;
                })
                ->editColumn('status', function ($row) {
                    if ($row->status == '0') {
                        return '<span class="badge bg-light-warning w-100">New</span>';
                    } elseif ($row->status == '1') {
                        return '<span class="badge bg-light-success w-100">Completed</span>';
                    } elseif ($row->status == '2') {
                        return '<span class="badge bg-light-danger w-100">Not Interested</span>';
                    } elseif ($row->status == '3') {
                        return '<span class="badge bg-light-primary w-100">Next Follow up</span>';
                    } elseif ($row->status == '4') {
                        return '<span class="badge bg-light-info w-100">Site Visit</span>';
                    } elseif ($row->status == '5') {
                        return '<span class="badge bg-light-success w-100">Make Quatation</span>';
                    }
                })
                ->editColumn('reminder_date', function ($row) {
                    if (!is_null($row->reminder_date)) {
                        return date('d-m-Y', strtotime($row->reminder_date));
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
        if(isset($request->type) && $request->type == "rateCalc")
        {
            $name = ($lead->count() > 0) ? $lead[0]->name : '';
            return response()->json($name);
        }
        return response()->json($lead);
    }
}
