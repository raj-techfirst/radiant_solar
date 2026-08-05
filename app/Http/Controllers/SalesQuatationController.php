<?php

namespace App\Http\Controllers;

use App\Models\AgentSalesPerson;
use App\Models\Bank;
use App\Models\CompanyProfile;
use App\Models\FollowUp;
use App\Models\InveterCompany;
use App\Models\ItemGroup;
use App\Models\LeadMaster;
use App\Models\PenalCompany;
use App\Models\PenalType;
use App\Models\PenalWatt;
use App\Models\Policy;
use App\Models\Product;
use App\Models\SalesMaster;
use App\Models\SalesQuatation;
use App\Models\SalesQuatationMeta;
use App\Models\SalesQuatationTechnicalSpecification;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;


class SalesQuatationController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:sales-quatation-list', ['only' => ['index']]);
       /* $this->middleware('permission:sales-quatation-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:sales-quatation-edit', ['only' => ['edit', 'store']]); */
        $this->middleware('permission:sales-quatation-delete', ['only' => ['destroy']]);
    }

    public function index()
    {

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
            return DataTables::of(SalesQuatation::with('agentSalesPerson')->orderBy('id', 'DESC'))
                ->addIndexColumn()
                ->filter(function ($query) {
                    $company = CompanyProfile::where('user_id', Auth::id())->first();
                    if ($company->user_type == 'M') {
                        $agent = AgentSalesPerson::where('user_id', Auth::id())->first();
                        $agentIds = [$agent->id];
                        $sales = CompanyProfile::select('company_profiles.id', 'company_profiles.user_id', 'agent_sales_people.id as agent_id')
                            ->leftJoin('agent_sales_people', 'agent_sales_people.user_id', 'company_profiles.user_id')
                            ->where('company_profiles.manager_id', $company->id)->get();
                        if ($sales->count() > 0) {
                            foreach ($sales as $k => $v) :
                                array_push($agentIds, $v->agent_id);
                            endforeach;
                        }
                        $query->whereIn('agent_sales_person_id', $agentIds);
                    }
                    if ($company->user_type == 'S') {
                        $agent = AgentSalesPerson::where('user_id', Auth::id())->first();
                        $id = $agent->id;
                        $query->where('agent_sales_person_id', $id);
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

                    if (request()->input('form_type') != "") {
                        $query->where('form_type', request()->input('form_type'));
                    }
                     if (request()->input('current_status') != "") {
                        $query->where('current_status', request()->input('current_status'));
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
                ->addColumn('form_type', function ($row) {
                    if ($row->form_type == 'trading') {
                        return $row->form_type = 'Trading';
                    } elseif ($row->form_type == 'resident') {
                        return $row->form_type = 'Resident With Subsidy';
                    } elseif ($row->form_type == 'roof') {
                        return $row->form_type = 'Solar RoofTop';
                    }
                })
                 ->editColumn('current_status', function ($row) {
                    if (Gate::check('sales-quatation-edit')) {
						
                        $payStatus = getSalesQuotationStatusClass($row->current_status);

                        $btn = "btn-outline-" . $payStatus['class'] ?? 'warning'; 
                        $title = $payStatus['status'] ?? 'active';

                        $html = '<div class="">
                        <div class="btn-group p-0">
                            <button type="button" class="btn-sm btn ' . $btn . '">' . $title . '</button>
                            <button type="button" class="btn-sm btn ' . $btn . ' dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false">
                                <span class="visually-hidden">Toggle Dropdown</span>
                            </button>
                        <div class="dropdown-menu" container="body">';

                        $salesQuatationStatus = salesQuotationStatus();
                        foreach ($salesQuatationStatus as $k => $v):
                            $html .= '<a class="dropdown-item change-status" href="javascript:void(0);" data-id="' . $row->id . '" data-status="' . $v['id'] . '">' . $v['name'] . '</a>';
                        endforeach;
                        $html .= '</div>
                        </div>';
                        return $html;

                    } else {
                        $payStatus = getSalesQuotationStatusClass($row->current_status);
                        return '<span class="badge bg-light-' . $payStatus['class'] . ' w-100">' . $payStatus['status'] . '</span>';
                    }
                })
                ->editColumn('created_at', function ($row) {
                    if (!is_null($row->created_at)) {
                        return date('d-m-Y', strtotime($row->created_at));
                    } else {
                        return '';
                    }
                })
                ->addColumn('action', function ($row) {
                    $html = '<div>';
                    $html = '<a data-id="' . $row->id . '" href="javascript:void(0)" class="view avatar bg-light-success p-50 m-0" data-bs-toggle="tooltip" data-placement="left" title="View"><i class="fa fa-eye"></i></a>';
                    $html .= '<a href="' . route('sales-quatation-pdf', $row->id) . '" target="_blank" class="avatar bg-light-warning p-50 m-0" data-bs-toggle="tooltip" data-placement="left" title="Download PDF"><i class="fa fa-file"></i></a>';
                    if (Gate::allows('sales-quatation-edit')) {
                        $html .= ' <a href="' . route('sales-quatation.edit', $row->id) . '" class="avatar bg-light-info p-50 m-0" data-bs-toggle="tooltip" data-placement="left" title="Edit"><i class="fa fa-edit"></i></a>';
                    }
                    if (Gate::allows('sales-quatation-delete')) {
                        $html .= ' <a data-id="' . $row->id . '" href="javascript:void(0);" class="delete avatar bg-light-danger p-50 m-0" data-bs-toggle="tooltip" data-placement="left" title="Delete"><i class="fa fa-trash"></i></a>';
                    }
                    $html .= '</div>';
                    return $html;
                })
                ->escapeColumns([])
                ->make(true);
        } else {
            return view('admin.sales-quatation.index', compact('agentSalesPerson'));
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $product = Product::get();
        $itemGroup = ItemGroup::with('panel_company', 'panel_type', 'panel_watt', 'inveter_company')->get();
        $penal_company = PenalCompany::get();
        $penal_type = PenalType::get();
        $penal_watt = PenalWatt::get();

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
        $lead_complete = LeadMaster::whereRaw($where)->orderBy('id', 'DESC')->get();

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

        $inveter_company = InveterCompany::get();
        $bank = Bank::get();
        $technicalSpecification = [];
        return view('admin.sales-quatation.add_sales', compact('product', 'agentSalesPerson', 'penal_company', 'lead_complete', 'inveter_company', 'penal_type', 'penal_watt', 'bank', 'itemGroup','technicalSpecification'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            if (!is_null($request->sales_quatation_id)) {
                $SalesQuatation = SalesQuatation::where('id', $request->sales_quatation_id)->first();
                $response = ['data' => route('sales-quatation.index'), 'status' => true, 'message' => ' Sales Quatation updated successfully.'];
            } else {
                $SalesQuatation = new SalesQuatation();
                $response = ['data' => route('sales-quatation.index'), 'status' => true, 'message' => ' Sales Quatation added successfully.'];
            }

            $SalesQuatation->user_id = Auth::id();
            $SalesQuatation->form_type = $request->form_type;

            if ($request->form_type == 'trading') {
                $SalesQuatation->lead_master_id = $request->lead_master_id;
                $SalesQuatation->name = strtoupper($request->name);
                $SalesQuatation->mobile = $request->mobile;
                $SalesQuatation->address = $request->address;
                $SalesQuatation->ship_to = $request->ship_to;
                $SalesQuatation->gst_no = $request->gst_no;
                $SalesQuatation->gst = $request->trading_gst;
                $SalesQuatation->reference = strtoupper($request->reference);
                $SalesQuatation->agent_sales_person_id = $request->agent_sales_person_id;
                $SalesQuatation->total_amount = $request->trading_total_amount;
                $SalesQuatation->bank_id = $request->trading_bank_id;
                 $SalesQuatation->current_status = 'active';
                $result = $SalesQuatation->save();
                if ($result) {
                    if (isset($request->invoice) && count($request->invoice) > 0) {
                        $old_quatation_details = SalesQuatationMeta::where('sales_quatation_id', $request->sales_quatation_id)->get();
                        $new_quatation_id = array();
                        foreach ($request->invoice as $key => $value) {
                            if (isset($value['sales_quotation_meta_id']) && !is_null($value['sales_quatation_meta_id'])) {
                                $new_quatation_id[] = $value['sales_quatation_meta_id'];
                                SalesQuatationMeta::where('id', $value['sales_quatation_meta_id'])->delete();
                                if ($value['type'] == "Item") {
                                    $salesQuatationMetaData = [
                                        'sales_quatation_id' => $request->sales_quatation_id,
                                        'type' => "Item",
                                        'item_id' => $value['item_id'],
                                        'item_group_id' => 0,
                                        'nos' => $value['nos'],
                                        'rate' => $value['rate'],
                                        'item_gst' => $value['item_gst']
                                    ];
                                }
                                if ($value['type'] == "ItemGroup") {
                                    $salesQuatationMetaData = [
                                        'sales_quatation_id' => $request->sales_quatation_id,
                                        'type' => "ItemGroup",
                                        'item_id' => 0,
                                        'item_group_id' => $value['item_group_id'],
                                        'nos' => $value['nos'],
                                        'rate' => $value['rate'],
                                        'item_gst' => $value['item_gst']
                                    ];
                                }
                                SalesQuatationMeta::create($salesQuatationMetaData);
                                // SalesQuatationMeta::where('id', $value['sales_quatation_meta_id'])->update($salesQuatationMetaData);
                            } else {
                                if ($value['type'] == "Item") {
                                    $salesQuatationMetaData = [
                                        'sales_quatation_id' => $SalesQuatation->id,
                                        'type' => "Item",
                                        'item_id' => $value['item_id'],
                                        'item_group_id' => 0,
                                        'nos' => $value['nos'],
                                        'rate' => $value['rate'],
                                        'item_gst' => $value['item_gst']
                                    ];
                                }
                                if ($value['type'] == "ItemGroup") {
                                    $salesQuatationMetaData = [
                                        'sales_quatation_id' => $SalesQuatation->id,
                                        'type' => "ItemGroup",
                                        'item_id' => 0,
                                        'item_group_id' => $value['item_group_id'],
                                        'nos' => $value['nos'],
                                        'rate' => $value['rate'],
                                        'item_gst' => $value['item_gst']
                                    ];
                                }
                                SalesQuatationMeta::create($salesQuatationMetaData);
                            }
                        }
                        // delete
                        $quatation = [];
                        foreach ($old_quatation_details as $key => $value) {
                            $quatation[] = $value->id;
                            if (!in_array($quatation[$key], $new_quatation_id)) {
                                $data = SalesQuatationMeta::findOrFail($value->id);
                                $data->delete();
                            }
                        }
                        // delete
                    }
                }
            }
            if ($request->form_type == 'resident') {
                $SalesQuatation->lead_master_id = $request->res_lead_master_id;
                $SalesQuatation->name = strtoupper($request->res_name);
                $SalesQuatation->mobile = $request->res_mobile;
                $SalesQuatation->address = $request->res_address;
                $SalesQuatation->penal_company_id = implode(',', $request->res_penal_company_id);
                $SalesQuatation->penal_type_id = $request->res_penal_type_id;
                $SalesQuatation->penal_watt_id = $request->res_penal_watt_id;
                $SalesQuatation->penal_nos = $request->res_penal_nos;
                $SalesQuatation->pv_capacity_kw = $request->res_pv_capacity_kw;
                $SalesQuatation->inveter_company_id = implode(',', $request->res_inveter_company_id);
                $SalesQuatation->inveter_capacity = $request->res_inveter_capacity;
                $SalesQuatation->no_of_inveter = $request->res_no_of_inveter;
                $SalesQuatation->structure = $request->res_structure;
                $SalesQuatation->common_meter = $request->res_common_meter;
                $SalesQuatation->total_system_cost = $request->res_total_system_cost;
                $SalesQuatation->meter_charges_extra = $request->res_meter_charge_extra;
                $SalesQuatation->meter_charges = $request->res_meter_charges;
                $SalesQuatation->registration_fee = $request->res_registration_fee;
                $SalesQuatation->total_amount = $request->resident_total_amount;
                 $SalesQuatation->current_status = 'active';
                if (!is_null($request->sales_quatation_id)) {
                    $salesMaster = SalesMaster::where('sales_quatation_id', $request->sales_quatation_id)->first();
                    if (!is_null($salesMaster)) {
                        $findPayment = $salesMaster->total_amount - $salesMaster->pending_amonut;
                        $salesMaster->total_amount =  $request->resident_total_amount;
                        $salesMaster->pending_amonut =  $request->resident_total_amount - $findPayment;
                        $salesMaster->save();
                    }
                }

                $SalesQuatation->reference = strtoupper($request->reference);
                $SalesQuatation->agent_sales_person_id = $request->res_agent_sales_person_id;
                $SalesQuatation->other_charge_name = $request->res_other_charge_name;
                $SalesQuatation->other_charge_amount = $request->res_other_charge_amount;
                $SalesQuatation->subsidy = $request->span_subsidy;
                $SalesQuatation->bank_id = $request->res_bank_id;
                $result = $SalesQuatation->save();

                /* Technical Specification & BOM */
                SalesQuatationTechnicalSpecification::where('sales_quatation_id',$SalesQuatation->id)->delete();
                if (!empty($request->res_itemDescription) && count($request->res_itemDescription) > 0) {
                    foreach ($request->res_itemDescription as $techKey => $techValue):
                        $technicalData = [
                            'sales_quatation_id' => $SalesQuatation->id,
                            'itemDescription' => $techValue,
                            'qty' => $request->res_qty[$techKey] ?? '',
                            'size' => $request->res_size[$techKey] ?? '',
                            'make' => $request->res_make[$techKey] ?? '',
                            'type' => $request->res_type[$techKey] ?? '',
                        ];
                        SalesQuatationTechnicalSpecification::create($technicalData);
                    endforeach;
                }
                /* / Technical Specification & BOM */
            }
            if ($request->form_type == 'roof') {
                $SalesQuatation->lead_master_id = $request->roof_lead_master_id;
                $SalesQuatation->name = strtoupper($request->roof_name);
                $SalesQuatation->mobile = $request->roof_mobile;
                $SalesQuatation->address = $request->roof_address;
                $SalesQuatation->penal_company_id = implode(',', $request->penal_company_id);
                $SalesQuatation->penal_type_id = $request->penal_type_id;
                $SalesQuatation->penal_watt_id = $request->penal_watt_id;
                $SalesQuatation->penal_nos = $request->penal_nos;
                $SalesQuatation->pv_capacity_kw = $request->pv_capacity_kw;
                $SalesQuatation->inveter_company_id = implode(',', $request->inveter_company_id);
                $SalesQuatation->inveter_capacity = $request->inveter_capacity;
                $SalesQuatation->no_of_inveter = $request->no_of_inveter;
                $SalesQuatation->structure = $request->structure;
                $SalesQuatation->meter_charges_extra = $request->meter_charge_extra;
                $SalesQuatation->meter_charges = $request->meter_charges;
                $SalesQuatation->registration_fee = $request->registration_fee;
                $SalesQuatation->rate_per_kw = $request->rate_per_kw;
                $SalesQuatation->gst = $request->gst;
                $SalesQuatation->quatation_type = $request->quatation_type;
                $SalesQuatation->other_charge_name = $request->roof_other_charge_name;
                $SalesQuatation->other_charge_amount = $request->roof_other_charge_amount;
                $SalesQuatation->bank_id = $request->roof_bank_id;
                $SalesQuatation->total_amount = $request->roof_span_total_project_cost_hidden;
                 $SalesQuatation->current_status = 'active';
                if (!is_null($request->sales_quatation_id)) {
                    $salesMaster = SalesMaster::where('sales_quatation_id', $request->sales_quatation_id)->first();
                    if (!is_null($salesMaster)) {
                        $findPayment = $salesMaster->total_amount - $salesMaster->pending_amonut;
                        $salesMaster->total_amount =  $request->roof_span_total_project_cost_hidden;
                        $salesMaster->pending_amonut =  $request->roof_span_total_project_cost_hidden - $findPayment;
                        $salesMaster->save();
                    }
                }
                $SalesQuatation->reference = strtoupper($request->roof_reference);
                $SalesQuatation->agent_sales_person_id = $request->roof_agent_sales_person_id;
                $result = $SalesQuatation->save();

                /* Technical Specification & BOM */
                SalesQuatationTechnicalSpecification::where('sales_quatation_id',$SalesQuatation->id)->delete();
                if (!empty($request->roof_itemDescription) && count($request->roof_itemDescription) > 0) {
                    foreach ($request->roof_itemDescription as $techKey => $techValue):
                        $technicalData = [
                            'sales_quatation_id' => $SalesQuatation->id,
                            'itemDescription' => $techValue,
                            'qty' => $request->roof_qty[$techKey] ?? '',
                            'size' => $request->roof_size[$techKey] ?? '',
                            'make' => $request->roof_make[$techKey] ?? '',
                            'type' => $request->roof_type[$techKey] ?? '',
                        ];
                        SalesQuatationTechnicalSpecification::create($technicalData);
                    endforeach;
                }
                /* / Technical Specification & BOM */
            }
            DB::commit();
            if (!is_null($result)) {
                return response()->json($response);
            } else {
                $response = ['status' => false, 'server_error' => 'Something went wrong. Please try again.'];
                return response()->json($response);
            }
        } catch (\Exception $e) {
            DB::rollback();
            $response = ['status' => false, 'server_error' => 'Something went wrong. Please try again. 2'];
            return response()->json($response);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\SalesQuatation  $salesQuatation
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $salesQuatation = SalesQuatation::select('sales_quatations.*', 'penal_types.name as penal_types_name', 'penal_watts.name as penal_watts_name', 'agent_sales_people.name as agent_sales_people_name', 'inveter_companies.name as inveter_companies_name')
            // ->leftJoin('penel_companies', 'sales_quatations.penal_company_id', '=', 'penel_companies.id')
            ->leftJoin('penal_types', 'sales_quatations.penal_type_id', '=', 'penal_types.id')
            ->leftJoin('penal_watts', 'sales_quatations.penal_watt_id', '=', 'penal_watts.id')
            ->leftJoin('agent_sales_people', 'sales_quatations.agent_sales_person_id', '=', 'agent_sales_people.id')
            ->leftJoin('inveter_companies', 'sales_quatations.inveter_company_id', '=', 'inveter_companies.id')
            ->where('sales_quatations.id', $id)->first();
        // company name
        if ($salesQuatation->penal_company_id != null) {
            $hist = PenalCompany::whereIn('id', explode(',', $salesQuatation->penal_company_id))->get();
            if ($hist != '') {
                $company = [];
                $penel_companies_name =  "";
                foreach ($hist as $key => $ri) {
                    $penel_companies_name != "" && $penel_companies_name .= " / ";
                    $penel_companies_name .= $ri->name;
                    $company[] = [
                        'name' => $ri->name,
                        'logo' => $ri->logo,
                    ];
                }
                $salesQuatation->penal_company_id = $penel_companies_name;
                $salesQuatation['company'] = array_values($company);
            }
        }
        // company name
        // Inveter company name
        if ($salesQuatation->inveter_company_id != null) {
            $hist = InveterCompany::whereIn('id', explode(',', $salesQuatation->inveter_company_id))->get();
            if ($hist != '') {
                $inveter_companies_name =  "";
                foreach ($hist as $ri) {
                    $inveter_companies_name != "" && $inveter_companies_name .= " / ";
                    $inveter_companies_name .= $ri->name;
                }
                $salesQuatation->inveter_company_id = $inveter_companies_name;
            }
        }
        // Inveter company name
        if ($salesQuatation->form_type == 'trading') {
            $meta = SalesQuatationMeta::with('item')->where('sales_quatation_id', $id)->get();
        } else {
            $meta = '';
        }
        if (!is_null($salesQuatation)) {
            $data['html'] = view('admin.sales-quatation.model', compact('salesQuatation', 'meta'))->render();
            return response()->json($data);
        } else {
            return abort(404);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\SalesQuatation  $salesQuatation
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $sales_quatation = SalesQuatation::where('id', $id)->first();

        if (!is_null($sales_quatation)) {
            if ($sales_quatation->form_type == 'trading') {
                $meta = SalesQuatationMeta::with('item')->where('sales_quatation_id', $id)->get();
            } else {
                $meta = '';
            }
            $product = Product::get();
            $penal_company = PenalCompany::get();
            $penal_type = PenalType::get();
            $penal_watt = PenalWatt::get();

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
            $lead_complete = LeadMaster::whereRaw($where)->orderBy('id', 'DESC')->get();

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

            $inveter_company = InveterCompany::get();

            $bank = Bank::get();
            $itemGroup = ItemGroup::with('panel_company', 'panel_type', 'panel_watt', 'inveter_company')->get();
            $technicalSpecification = SalesQuatationTechnicalSpecification::where('sales_quatation_id', $id)->get()->toArray();

            return view('admin.sales-quatation.add_sales', compact('product', 'sales_quatation', 'penal_company', 'penal_type', 'penal_watt', 'lead_complete', 'agentSalesPerson', 'inveter_company', 'meta', 'bank', 'itemGroup','technicalSpecification'));
        } else {
            return abort(404);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\SalesQuatation  $salesQuatation
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, SalesQuatation $salesQuatation)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\SalesQuatation  $salesQuatation
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $product = SalesQuatation::where('id', $id)->first();
            $salesMaster = SalesMaster::where('sales_quatation_id', $id)->get();
            if ($salesMaster->count() == 0) {
                $product->delete();
                $response = ['status' => true, 'message' => ' Deleted successfully.'];
            } else {
                $response = ['status' => false, 'server_error' => 'Sorry, you cannot delete this quotation because you have created an order based on it.'];
            }
            return response()->json($response);
        } catch (\Exception $e) {
            $response = ['status' => false, 'server_error' => 'Something went wrong. Please try again.'];
            return response()->json($response);
        }
    }

    public function getPanelsAndWatts(Request $request)
    {
        $companyId = $request->input('companyId');
        $panel_type = PenalType::where('penal_company_id', $companyId)->get();
        $watts = PenalWatt::where('penal_company_id', $companyId)->get();

        return response()->json([
            'panel_type' => $panel_type,
            'watts' => $watts,
        ]);
    }

    public function salesQuatationPdf($id)
    {
        $sales_data = SalesQuatation::select('sales_quatations.*', 'penal_types.name as penal_type_name', 'penal_watts.name as panel_watt', 'agent_sales_people.name as agent_name', 'agent_sales_people.number as agent_mobile', 'banks.name as bank_name', 'banks.account_number', 'banks.ifsc_number', 'banks.branch', 'banks.holder_name')
            ->leftJoin('penal_watts', 'sales_quatations.penal_watt_id', '=', 'penal_watts.id')
            ->leftJoin('penal_types', 'sales_quatations.penal_type_id', '=', 'penal_types.id')
            ->leftJoin('agent_sales_people', 'sales_quatations.agent_sales_person_id', '=', 'agent_sales_people.id')
            ->leftJoin('banks', 'sales_quatations.bank_id', '=', 'banks.id')
            ->orderBy('sales_quatations.id', 'DESC')->where('sales_quatations.id', $id)->first();
        // company name
        if ($sales_data->penal_company_id != null) {
            $hist = PenalCompany::whereIn('id', explode(',', $sales_data->penal_company_id))->get();
            if ($hist != '') {
                $company = [];
                $penel_companies_name =  "";
                foreach ($hist as $ri) {
                    $penel_companies_name != "" && $penel_companies_name .= " / ";
                    $penel_companies_name .= $ri->name;
                    $company[] = [
                        'name' => $ri->name,
                        'logo' => $ri->logo,
                    ];
                }
                $sales_data->penal_company_id = $penel_companies_name;
                $sales_data['company'] = array_values($company);
            }
        }
        // company name
        // Inveter company name
        if ($sales_data->inveter_company_id != null) {
            $hist = InveterCompany::whereIn('id', explode(',', $sales_data->inveter_company_id))->get();
            if ($hist != '') {
                $inveter_companies_name =  "";
                foreach ($hist as $ri) {
                    $inveter_companies_name != "" && $inveter_companies_name .= " / ";
                    $inveter_companies_name .= $ri->name;
                }
                $sales_data->inveter_company_id = $inveter_companies_name;
            }
        }
        // Inveter company name
        if (!is_null($sales_data)) {
            if ($sales_data->form_type == 'resident') {
                $policy_data = Policy::where('form_type', 'resident')->first();
                $technicalSpecification = SalesQuatationTechnicalSpecification::where('sales_quatation_id', $sales_data->id)->get();
                $data = [
                    'title' => 'Quatation',
                    'sales_data' => $sales_data,
                    'policy_data' => $policy_data,
                    'technicalSpecification' => $technicalSpecification
                ];
                $pdf = Pdf::loadView('admin.sales-quatation.rooftop_pdf', $data);
                // return $pdf->stream('quatation.pdf');

                return $pdf->download(Str::slug($sales_data->name) . '-quot.pdf');
            } else if ($sales_data->form_type == 'trading') {
                $meta_data = SalesQuatationMeta::with('item', 'itemGroup')->where('sales_quatation_id', $sales_data->id)->get();
                $policy_data = Policy::where('form_type', 'trading')->first();
                $data = [
                    'title' => 'Trading',
                    'sales_data' => $sales_data,
                    'meta_data' => $meta_data,
                    'policy_data' => $policy_data,
                ];
                $pdf = Pdf::loadView('admin.sales-quatation.trading_pdf', $data);
                // return $pdf->stream('trading.pdf');

                return $pdf->download(Str::slug($sales_data->name) . '-trading.pdf');
            } else if ($sales_data->form_type == 'roof') {

                $policy_data = Policy::where('form_type', 'roof')->first();
                $technicalSpecification = SalesQuatationTechnicalSpecification::where('sales_quatation_id', $sales_data->id)->get();

                $rate_per_kw = $sales_data->rate_per_kw;
                $rate_per_kw_value = $sales_data->pv_capacity_kw * $sales_data->rate_per_kw;

                $rate_per_kw_gst = 0;
                $rate_per_kw_value_gst = 0;
                $gst = $sales_data->gst;
                $val4 = $sales_data->rate_per_kw;

                if ($gst == 'Extra') {
                    $rate_per_kw_gst = (($rate_per_kw * env("PER")) / 100);
                    $value1 = $rate_per_kw_value;
                    $rate_per_kw_value_gst = (($value1 * env("PER")) / 100);
                    $rate_per_kw_value = $sales_data->pv_capacity_kw * $val4;
                } else {
                    $a = $rate_per_kw_value * 100;
                    $b = env("PER") + 100;
                    $value1 = ($a / $b);
                    $rate_per_kw_value_gst = (($value1 * env("PER")) / 100);

                    $c = ((($rate_per_kw * 100) * env("PER")) / 100);
                    $rate_per_kw_gst = ($c / $b);
                    $val4 = ($rate_per_kw - $rate_per_kw_gst);

                    $rate_per_kw_value = $sales_data->pv_capacity_kw * $val4;
                }

                $calc['val4'] = $val4;
                $calc['rate_per_kw_value'] = $rate_per_kw_value;
                $calc['rate_per_kw_gst'] = $rate_per_kw_gst;
                $calc['rate_per_kw_value_gst'] = $rate_per_kw_value_gst;

                $data = [
                    'title' => 'Solar RoofTop',
                    'sales_data' => $sales_data,
                    'policy_data' => $policy_data,
                    'calc' => $calc,
                    'technicalSpecification' => $technicalSpecification
                ];
                $pdf = Pdf::loadView('admin.sales-quatation.rooftop_industry_pdf', $data);
                // return $pdf->stream('solar-rooftop.pdf');
                return $pdf->download(Str::slug($sales_data->name) . '-solar-rooftop.pdf');
            }
        } else {
            return abort(404);
        }
    }

    public function getDetails(Request $request)
    {
        try {
            $salesQuatation = SalesQuatation::select('name', 'address', 'ship_to', 'gst_no')->where('mobile', $request->mobile)->first();
            if (!is_null($salesQuatation)) {
                $response = ['status' => true, 'salesQuatation' => $salesQuatation];
            } else {
                $response = ['status' => false];
            }
            return response()->json($response);
        } catch (\Exception $e) {
            $response = ['status' => false, 'server_error' => 'Something went wrong. Please try again.'];
            return response()->json($response);
        }
    }

    public function changeStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required',
            'id' => 'required'
        ], [
            'status.required' => 'Select Status',
            'id.required' => 'Enter Payment id',
        ]);
        if ($validator->fails()) {
            $response = ['status' => false, 'message' => 'Please input proper data.', 'errors' => $validator->errors()];
            return response()->json($response);
        }
        DB::beginTransaction();
        try {
            $response = ['status' => false, 'server_error' => 'Not Found.'];
            $salesQuatation = SalesQuatation::where('id', $request->id)->first();
            if (!is_null($salesQuatation)) {

                $oldStatus = $salesQuatation->current_status;
                $salesQuatation->current_status = $request->status;
                $res = $salesQuatation->save();
                $newStatus = $salesQuatation->current_status;

                $sqId = env('APP_SORT').'/EPC/'.str_pad($salesQuatation->id, 2, '0', STR_PAD_LEFT);

                if (!is_null($request->id)) {
                    $remark = $sqId.' status change '. $oldStatus .' To '. $newStatus;
                    $status_id = 12;
                }

            $followUp = new FollowUp();
            $followUp->lead_master_id = $salesQuatation->lead_master_id;
            $followUp->call_detail = '';
            $followUp->remark = $remark;
            $company = CompanyProfile::with('user')->where('user_id', Auth::id())->first();
            $followUp->follow_up_by = $company->id;
            $followUp->status_id = $status_id;
            $followUp->save();

                if ($res) {
                    DB::commit();
                    $response = ['status' => true, 'message' => 'Status changed successfully.'];
                } else {
                    DB::rollback();
                    $response = ['status' => false, 'server_error' => 'Something went wrong. Please try again.'];
                }
            }
            return response()->json($response);
        } catch (\Exception $e) {
            DB::rollback();
            $response = ['status' => false, 'server_error' => 'Something went wrong. Please try again.'];
            return response()->json($response);
        }
    }

}
