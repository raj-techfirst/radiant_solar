<?php

namespace App\Http\Controllers;

use App\Exports\DispachListExport;
use App\Exports\FinalOrdersExport;
use App\Exports\InstallationListExport;
use App\Exports\InverterRequired;
use App\Exports\InvoiceExport;
use App\Exports\MeterApplicationExport;
use App\Exports\MeterChargesExport;
use App\Exports\PanelsRequired;
use App\Exports\PaymentPendingExport;
use App\Exports\SubsidyClaimExport;
use App\Exports\TotalcollectionExport;
use App\Models\AgentSalesPerson;
use App\Models\CompanyProfile;
use App\Models\District;
use App\Models\InveterCompany;
use App\Models\PenalCompany;
use App\Models\PenalWatt;
use App\Models\LeadMaster;
use App\Models\SalesMaster;
use App\Models\SalesQuatation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class Reports extends Controller
{
    function __construct()
    {
        $this->middleware('permission:reports-total-collection|reports-payment-pending|reports-meter-charges|reports-dispach|reports-installation|reports-meter-application|reports-final|reports-invoice|panels-required-reports|inverters-required-reports|b2b-accept|b2b-dispatch|b2b-rate', ['only' => ['index']]);
        $this->middleware('permission:reports-total-collection', ['only' => ['totalcollection']]);
        $this->middleware('permission:reports-payment-pending', ['only' => ['paymentPending']]);
        $this->middleware('permission:reports-meter-charges', ['only' => ['meterCharges']]);
        $this->middleware('permission:reports-dispach', ['only' => ['dispach']]);
        $this->middleware('permission:reports-installation', ['only' => ['installation', 'installationNew']]);
        $this->middleware('permission:reports-meter-application', ['only' => ['meterApplication']]);
        $this->middleware('permission:reports-final', ['only' => ['finalReport']]);
    }
    public function index()
    {
        $company = CompanyProfile::where('user_id', Auth::id())->first();
        
        $subClaim = SalesMaster::selectRaw('sum(register_kw) as kw,count(id) as total')->where('meter_installation', '1');

        $invoicequery = SalesMaster::selectRaw('sum(register_kw) as kw,count(id) as total');
        $invoicequery->where('dispach_pending_list', '1');
        $totalcollectionquery = SalesMaster::selectRaw('sum(register_kw) as kw,count(id) as total');
        $totalcollectionquery->where(function ($q) {
            $q->where('installation_done',  "0")
                ->orWhere('pending_amonut', '>', 0);
        });
        $paymentpendingquery = SalesMaster::selectRaw('sum(register_kw) as kw,count(id) as total')
            ->where('payment_receveid', '0')->where('installation_pending', '1');
        $meterChargesquery = SalesMaster::selectRaw('sum(register_kw) as kw,count(id) as total')->where('feasibility_discom_sr_number', "!=", "")->where('feasibility_amount', "!=", "");
        $dispachquery = SalesMaster::selectRaw('sum(register_kw) as kw,count(id) as total')->where('dispach_pending_list', "1");
        $dispachquery->where(function ($q) {
            $q->where('installation_done',  "0");
        });
        $query = SalesMaster::selectRaw('sum(register_kw) as kw,count(id) as total')->where('installation_asian_person', "!=", "");
        $query->where(function ($q) {
            $q->where('installation_pending',  "1")
                ->orWhere('installation_done', "1");
        });
        $installationNewquery = SalesMaster::selectRaw('sum(register_kw) as kw,count(id) as total')->where('installation_asian_person', "!=", "");
        $installationNewquery->where(function ($q) {
            $q->where('installation_pending',  "1")
                ->orWhere('installation_done', "1");
        });
        $installationNewquery->whereHas('installation', function ($q) {
            $q->where('form_type', 'new');
        });
        $meterApplicationquery = SalesMaster::selectRaw('sum(register_kw) as kw,count(id) as total')->where('meter_application_done', "1")->where('meter_installation', "0");
        $finalReportquery = SalesMaster::selectRaw('sum(register_kw) as kw,count(id) as total')->where('project_completion', "1");
        $b2bAcceptCount = SalesQuatation::where('form_type', 'trading')->where('current_status', 'accepted');
        $b2bDispatchCount = SalesQuatation::where('form_type', 'trading')->where('current_status', 'dispatch');
        $b2bRateCount = LeadMaster::where('is_trading', '1')
            ->whereNotExists(function ($q) {
                $q->select(\Illuminate\Support\Facades\DB::raw(1))
                    ->from('sales_quatations')
                    ->whereColumn('sales_quatations.lead_master_id', 'lead_masters.id')
                    ->whereNull('sales_quatations.deleted_at')
                    ->where('sales_quatations.current_status', 'accepted');
            });
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
            if (request()->input('agent_sales_person_id') == "") {
                $invoicequery->whereIn('agent_sales_person_id', $agentIds);
                $totalcollectionquery->whereIn('agent_sales_person_id', $agentIds);
                $paymentpendingquery->whereIn('agent_sales_person_id', $agentIds);
                $meterChargesquery->whereIn('agent_sales_person_id', $agentIds);
                $dispachquery->whereIn('agent_sales_person_id', $agentIds);
                $query->whereIn('agent_sales_person_id', $agentIds);
                $installationNewquery->whereIn('agent_sales_person_id', $agentIds);
                $meterApplicationquery->whereIn('agent_sales_person_id', $agentIds);
                $finalReportquery->whereIn('agent_sales_person_id', $agentIds);
                $subClaim->whereIn('agent_sales_person_id', $agentIds);
                $b2bAcceptCount->whereIn('agent_sales_person_id', $agentIds);
                $b2bDispatchCount->whereIn('agent_sales_person_id', $agentIds);
                $b2bRateCount->whereIn('agent_sales_person_id', $agentIds);
            }
        }
        if ($company->user_type == 'S') {
            $agent = AgentSalesPerson::where('user_id', Auth::id())->first();
            $id = $agent->id;
            $invoicequery->where('agent_sales_person_id', $id);
            $totalcollectionquery->where('agent_sales_person_id', $id);
            $paymentpendingquery->where('agent_sales_person_id', $id);
            $meterChargesquery->where('agent_sales_person_id', $id);
            $dispachquery->where('agent_sales_person_id', $id);
            $query->where('agent_sales_person_id', $id);
            $installationNewquery->where('agent_sales_person_id', $id);
            $meterApplicationquery->where('agent_sales_person_id', $id);
            $finalReportquery->where('agent_sales_person_id', $id);
            $subClaim->where('agent_sales_person_id', $id);
            $b2bAcceptCount->where('agent_sales_person_id', $id);
            $b2bDispatchCount->where('agent_sales_person_id', $id);
            $b2bRateCount->where('agent_sales_person_id', $id);
        }
        $invoicequery->where('file_cancel_order', '0');
        $totalcollectionquery->where('file_cancel_order', '0');
        $paymentpendingquery->where('file_cancel_order', '0');
        $meterChargesquery->where('file_cancel_order', '0');
        $dispachquery->where('file_cancel_order', '0');
        $query->where('file_cancel_order', '0');
        $installationNewquery->where('file_cancel_order', '0');
        $meterApplicationquery->where('file_cancel_order', '0');
        $finalReportquery->where('file_cancel_order', '0');
        $invoice = $invoicequery->get();
        $totalcollection = $totalcollectionquery->get();
        $paymentpending = $paymentpendingquery->get();
        $meterCharges = $meterChargesquery->get();
        $dispach = $dispachquery->get();
        $installation = $query->get();
        $installationNew = $installationNewquery->get();
        $meterApplication = $meterApplicationquery->get();
        $finalReport = $finalReportquery->get();
        $subClaim = $subClaim->get();
        $b2bAccept = $b2bAcceptCount->count();
        $b2bDispatch = $b2bDispatchCount->count();
        $b2bRate = $b2bRateCount->count();

        return view('admin.reports.index', compact('totalcollection', 'paymentpending', 'meterCharges', 'dispach', 'installation', 'installationNew', 'meterApplication', 'finalReport', 'invoice', 'subClaim', 'b2bAccept', 'b2bDispatch', 'b2bRate'));
    }
    public function totalcollection()
    {
        $companyFind = CompanyProfile::where('user_id', Auth::id())->first();
        $agentWhere = "";
        if ($companyFind->user_type == 'M') {
            $id = $companyFind->id;
            $agentWhere .= '(company_profiles.user_id = ' . Auth::id() . ' OR  company_profiles.manager_id = ' . $id . ')';
        }
        if ($companyFind->user_type == 'S') {
            $id = $companyFind->id;
            $manager_id = $companyFind->manager_id;
            $agentWhere .= '(company_profiles.id = ' . $id . ' OR  company_profiles.id = ' . $manager_id . ')';
        }
        $q = CompanyProfile::select('agent_sales_people.*')->leftJoin('agent_sales_people', 'agent_sales_people.user_id', 'company_profiles.user_id');
        if ($agentWhere != "") {
            $q->whereRaw($agentWhere);
        }
        $agentSalesPerson = $q->get();
        if (request()->ajax()) {
            return DataTables::of(SalesMaster::with('district', 'taluka', 'village', 'subDivision', 'salesquatationfull', 'agentsalesperson')->orderBy('id', 'DESC'))
                ->addIndexColumn()
                ->filter(function ($query) {
                    $query->where('file_cancel_order', '0');
                    $query->where(function ($q) {
                        $q->where('installation_done',  "0")
                            ->orWhere('pending_amonut', '>', 0);
                    });
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
                        if (request()->input('agent_sales_person_id') == "") {
                            $query->whereIn('agent_sales_person_id', $agentIds);
                        }
                    }
                    if ($company->user_type == 'S') {
                        $agent = AgentSalesPerson::where('user_id', Auth::id())->first();
                        $id = $agent->id;
                        $query->where('agent_sales_person_id', $id);
                    }
                    if (request()->input('from_date') != "" && request()->input('to_date') == '') {
                        $query->where('master_create_date', '>=', date('Y-m-d 00:00:00', strtotime(request()->input('from_date'))));
                        $query->where('master_create_date', '<=', date('Y-m-d 23:59:59'));
                    }
                    if (request()->input('from_date') != "" && request()->input('to_date') != '') {
                        $query->where('master_create_date', '>=', date('Y-m-d 00:00:00', strtotime(request()->input('from_date'))));
                        $query->where('master_create_date', '<=', date('Y-m-d 23:59:59', strtotime(request()->input('to_date'))));
                    }
                    if (request()->input('from_date') == "" && request()->input('to_date') != '') {
                        $query->where('master_create_date', '<=', date('Y-m-d 23:59:59', strtotime(request()->input('to_date'))));
                    }
                    if (request()->input('consumer') != "") {
                        $query->where(function ($q) {
                            $consumer = request()->input('consumer');
                            $q->where('consumer_name', 'like', '%' . $consumer . '%')
                                ->orWhere('consumer_number', 'like', '%' . $consumer . '%')
                                ->orWhere('contact_number', 'like', '%' . $consumer . '%');
                        });
                    }
                    if (request()->input('agent_sales_person_id') != "") {
                        $query->where('agent_sales_person_id', request()->input('agent_sales_person_id'));
                    }
                    if (request()->input('status') != "") {
                        $query->where(request()->input('status'), "1");
                    }
                    if (request()->input('not_status') != "") {
                        $query->where(request()->input('not_status'), "0");
                    }
                })
                ->editColumn('consumer', function ($row) {
                    return $row->consumer_name . '<br/>' . $row->contact_number;
                })
                ->editColumn('salesquatationfull.meter_charges', function ($row) {
                    return number_format((($row->salesquatationfull->meter_charges != null) ? $row->salesquatationfull->meter_charges : 0), 2);
                })
                ->editColumn('salesquatationfull.registration_fee', function ($row) {
                    return number_format((($row->salesquatationfull->registration_fee != null) ? $row->salesquatationfull->registration_fee : 0), 2);
                })
                ->editColumn('salesquatationfull.other_charge_amount', function ($row) {
                    return number_format((($row->salesquatationfull->other_charge_amount != null) ? $row->salesquatationfull->other_charge_amount : 0), 2);
                })
                ->editColumn('salesquatationfull.subsidy', function ($row) {
                    return number_format((($row->salesquatationfull->subsidy != null) ? $row->salesquatationfull->subsidy : 0), 2);
                })
                ->editColumn('pending_amonut', function ($row) {
                    return number_format((($row->pending_amonut != null) ? $row->pending_amonut : 0), 2);
                })
                ->editColumn('totalamt', function ($row) {
                    $totalamount = ($row->salesquatationfull->total_amount != null) ? $row->salesquatationfull->total_amount : 0;
                    return number_format(($totalamount), 2);
                })
                ->addColumn('total_system', function ($row) {
                    $meter_charges = ($row->salesquatationfull->meter_charges != null) ? $row->salesquatationfull->meter_charges : 0;
                    $registration_fee = ($row->salesquatationfull->registration_fee != null) ? $row->salesquatationfull->registration_fee : 0;
                    $other_charge_amount = ($row->salesquatationfull->other_charge_amount != null) ? $row->salesquatationfull->other_charge_amount : 0;
                    if ($row->salesquatationfull->form_type == 'resident') {
                        $system_cost =  $row->salesquatationfull->total_system_cost;
                    } else {
                        $system_cost =  $row->salesquatationfull->total_amount - $meter_charges - $registration_fee - $other_charge_amount;
                    }
                    return number_format(($system_cost), 2);
                })
                ->editColumn('received_amonut', function ($row) {
                    $total_amount = ($row->total_amount != null) ? $row->total_amount : 0;
                    $pending_amonut = ($row->pending_amonut != null) ? $row->pending_amonut : 0;
                    return number_format(($total_amount - $pending_amonut), 2);
                })
                ->editColumn('net_customer_price', function ($row) {
                    $totalamount = ($row->salesquatationfull->total_amount != null) ? $row->salesquatationfull->total_amount : 0;
                    $subsidy = ($row->salesquatationfull->subsidy != null) ? $row->salesquatationfull->subsidy : 0;
                    return number_format(($totalamount - $subsidy), 2);
                })
                ->addColumn('application_pending', function ($row) {
                    return '<span class="badge badge-light-secondary">' . toGetSalesMasterLastStatus($row->id) . '</span>
                     <a data-id="' . $row->id . '" href="javascript:void(0)" class="status-view avatar bg-light-warning p-50 m-0" data-bs-toggle="tooltip" data-placement="left" title="View"><i class="fa fa-stack-exchange"></i></a>';
                })
                ->escapeColumns([])
                ->make(true);
        } else {
            return view('admin.reports.totalcollection', compact('agentSalesPerson'));
        }
    }
    public function totalcollectionDownload(Request $request)
    {
        return Excel::download(new TotalcollectionExport($request), 'Sales_Orders.xlsx');
    }
    public function paymentpending()
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
            return DataTables::of(SalesMaster::with('district', 'taluka', 'village', 'subDivision', 'salesquatationfull', 'agentsalesperson')->orderBy('id', 'DESC'))
                ->addIndexColumn()
                ->filter(function ($query) {
                    $query->where('file_cancel_order', '0');
                    $query->where('installation_pending', "1");
                    $query->where('payment_receveid', "0");
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
                        if (request()->input('agent_sales_person_id') == "") {
                            $query->whereIn('agent_sales_person_id', $agentIds);
                        }
                    }
                    if ($company->user_type == 'S') {
                        $agent = AgentSalesPerson::where('user_id', Auth::id())->first();
                        $id = $agent->id;
                        $query->where('agent_sales_person_id', $id);
                    }
                    if (request()->input('from_date') != "" && request()->input('to_date') == '') {
                        $query->where('master_create_date', '>=', date('Y-m-d 00:00:00', strtotime(request()->input('from_date'))));
                        $query->where('master_create_date', '<=', date('Y-m-d 23:59:59'));
                    }
                    if (request()->input('from_date') != "" && request()->input('to_date') != '') {
                        $query->where('master_create_date', '>=', date('Y-m-d 00:00:00', strtotime(request()->input('from_date'))));
                        $query->where('master_create_date', '<=', date('Y-m-d 23:59:59', strtotime(request()->input('to_date'))));
                    }
                    if (request()->input('from_date') == "" && request()->input('to_date') != '') {
                        $query->where('master_create_date', '<=', date('Y-m-d 23:59:59', strtotime(request()->input('to_date'))));
                    }
                    if (request()->input('consumer') != "") {
                        $query->where(function ($q) {
                            $consumer = request()->input('consumer');
                            $q->where('consumer_name', 'like', '%' . $consumer . '%')
                                ->orWhere('consumer_number', 'like', '%' . $consumer . '%')
                                ->orWhere('contact_number', 'like', '%' . $consumer . '%');
                        });
                    }
                    if (request()->input('agent_sales_person_id') != "") {
                        $query->where('agent_sales_person_id', request()->input('agent_sales_person_id'));
                    }
                    if (request()->input('status') != "") {
                        $query->where(request()->input('status'), "1");
                    }

                    if (request()->input('not_status') != "") {
                        $query->where(request()->input('not_status'), "0");
                    }
                })
                ->editColumn('consumer', function ($row) {
                    return $row->consumer_name . '<br/>' . $row->contact_number;
                })
                ->editColumn('salesquatationfull.meter_charges', function ($row) {
                    return number_format((($row->salesquatationfull->meter_charges != null) ? $row->salesquatationfull->meter_charges : 0), 2);
                })
                ->editColumn('salesquatationfull.registration_fee', function ($row) {
                    return number_format((($row->salesquatationfull->registration_fee != null) ? $row->salesquatationfull->registration_fee : 0), 2);
                })
                ->editColumn('salesquatationfull.other_charge_amount', function ($row) {
                    return number_format((($row->salesquatationfull->other_charge_amount != null) ? $row->salesquatationfull->other_charge_amount : 0), 2);
                })
                ->editColumn('salesquatationfull.subsidy', function ($row) {
                    return number_format((($row->salesquatationfull->subsidy != null) ? $row->salesquatationfull->subsidy : 0), 2);
                })
                ->editColumn('pending_amonut', function ($row) {
                    return number_format((($row->pending_amonut != null) ? $row->pending_amonut : 0), 2);
                })
                ->addColumn('total_system', function ($row) {
                    $meter_charges = ($row->salesquatationfull->meter_charges != null) ? $row->salesquatationfull->meter_charges : 0;
                    $registration_fee = ($row->salesquatationfull->registration_fee != null) ? $row->salesquatationfull->registration_fee : 0;
                    $other_charge_amount = ($row->salesquatationfull->other_charge_amount != null) ? $row->salesquatationfull->other_charge_amount : 0;
                    if ($row->salesquatationfull->form_type == 'resident') {
                        $system_cost =  $row->salesquatationfull->total_system_cost;
                    } else {
                        $system_cost =  $row->salesquatationfull->total_amount - $meter_charges - $registration_fee - $other_charge_amount;
                    }
                    return number_format(($system_cost), 2);
                })
                ->editColumn('totalamt', function ($row) {
                    $totalamount = ($row->salesquatationfull->total_amount != null) ? $row->salesquatationfull->total_amount : 0;
                    return number_format(($totalamount), 2);
                })
                ->editColumn('received_amonut', function ($row) {
                    $total_amount = ($row->total_amount != null) ? $row->total_amount : 0;
                    $pending_amonut = ($row->pending_amonut != null) ? $row->pending_amonut : 0;
                    return number_format(($total_amount - $pending_amonut), 2);
                })
                ->editColumn('net_customer_price', function ($row) {
                    $totalamount = ($row->salesquatationfull->total_amount != null) ? $row->salesquatationfull->total_amount : 0;
                    $subsidy = ($row->salesquatationfull->subsidy != null) ? $row->salesquatationfull->subsidy : 0;
                    return number_format(($totalamount - $subsidy), 2);
                })
                ->addColumn('application_pending', function ($row) {
                    return '<span class="badge badge-light-secondary">' . toGetSalesMasterLastStatus($row->id) . '</span>
                    <a data-id="' . $row->id . '" href="javascript:void(0)" class="status-view avatar bg-light-warning p-50 m-0" data-bs-toggle="tooltip" data-placement="left" title="View"><i class="fa fa-stack-exchange"></i></a>';
                })
                ->escapeColumns([])
                ->make(true);
        } else {
            return view('admin.reports.payment-pending', compact('agentSalesPerson'));
        }
    }
    public function paymentpendingDownload(Request $request)
    {
        return Excel::download(new PaymentPendingExport($request), 'Payment_Pending.xlsx');
    }
    public function meterCharges()
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
            return DataTables::of(SalesMaster::with('district', 'taluka', 'village', 'subDivision', 'salesquatationfull', 'agentsalesperson')->orderBy('id', 'DESC'))
                ->addIndexColumn()
                ->filter(function ($query) {
                    $query->where('file_cancel_order', '0');
                    $query->where('feasibility_discom_sr_number', "!=", "");
                    $query->where('feasibility_amount', "!=", "");
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
                        if (request()->input('agent_sales_person_id') == "") {
                            $query->whereIn('agent_sales_person_id', $agentIds);
                        }
                    }
                    if ($company->user_type == 'S') {
                        $agent = AgentSalesPerson::where('user_id', Auth::id())->first();
                        $id = $agent->id;
                        $query->where('agent_sales_person_id', $id);
                    }
                    if (request()->input('from_date') != "" && request()->input('to_date') == '') {
                        $query->where('master_create_date', '>=', date('Y-m-d 00:00:00', strtotime(request()->input('from_date'))));
                        $query->where('master_create_date', '<=', date('Y-m-d 23:59:59'));
                    }
                    if (request()->input('from_date') != "" && request()->input('to_date') != '') {
                        $query->where('master_create_date', '>=', date('Y-m-d 00:00:00', strtotime(request()->input('from_date'))));
                        $query->where('master_create_date', '<=', date('Y-m-d 23:59:59', strtotime(request()->input('to_date'))));
                    }
                    if (request()->input('from_date') == "" && request()->input('to_date') != '') {
                        $query->where('master_create_date', '<=', date('Y-m-d 23:59:59', strtotime(request()->input('to_date'))));
                    }
                    if (request()->input('consumer') != "") {
                        $query->where(function ($q) {
                            $consumer = request()->input('consumer');
                            $q->where('consumer_name', 'like', '%' . $consumer . '%')
                                ->orWhere('consumer_number', 'like', '%' . $consumer . '%')
                                ->orWhere('contact_number', 'like', '%' . $consumer . '%');
                        });
                    }
                    if (request()->input('agent_sales_person_id') != "") {
                        $query->where('agent_sales_person_id', request()->input('agent_sales_person_id'));
                    }
                    if (request()->input('status') != "") {
                        $query->where(request()->input('status'), "1");
                    }
                    if (request()->input('not_status') != "") {
                        $query->where(request()->input('not_status'), "0");
                    }
                    if (request()->input('payment_ref_no') == "YES") {
                        $query->where('payment_ref_number', '!=', '');
                    }
                    if (request()->input('payment_ref_no') == "NO") {
                        $query->where('payment_ref_number', '=', null);
                    }
                })
                ->editColumn('consumer', function ($row) {
                    return $row->consumer_name . '<br/>' . $row->contact_number;
                })
                ->editColumn('subDivisionName', function ($row) {
                    return (isset($row->subDivision) && $row->subDivision != null && isset($row->subDivision[0])) ? $row->subDivision[0]->name : '';
                })
                ->editColumn('salesquatationfull.meter_charges', function ($row) {
                    return number_format((($row->salesquatationfull->meter_charges != null) ? $row->salesquatationfull->meter_charges : 0), 2);
                })
                ->editColumn('salesquatationfull.registration_fee', function ($row) {
                    return number_format((($row->salesquatationfull->registration_fee != null) ? $row->salesquatationfull->registration_fee : 0), 2);
                })
                ->editColumn('salesquatationfull.other_charge_amount', function ($row) {
                    return number_format((($row->salesquatationfull->other_charge_amount != null) ? $row->salesquatationfull->other_charge_amount : 0), 2);
                })
                ->editColumn('salesquatationfull.subsidy', function ($row) {
                    return number_format((($row->salesquatationfull->subsidy != null) ? $row->salesquatationfull->subsidy : 0), 2);
                })
                ->editColumn('pending_amonut', function ($row) {
                    return number_format((($row->pending_amonut != null) ? $row->pending_amonut : 0), 2);
                })
                ->editColumn('totalamt', function ($row) {
                    $totalamount = ($row->salesquatationfull->total_amount != null) ? $row->salesquatationfull->total_amount : 0;
                    $meter_charges = ($row->salesquatationfull->meter_charges != null) ? $row->salesquatationfull->meter_charges : 0;
                    $registration_fee = ($row->salesquatationfull->registration_fee != null) ? $row->salesquatationfull->registration_fee : 0;
                    $other_charge_amount = ($row->salesquatationfull->other_charge_amount != null) ? $row->salesquatationfull->other_charge_amount : 0;
                    return number_format(($totalamount + $meter_charges + $registration_fee  + $other_charge_amount), 2);
                })
                ->editColumn('received_amonut', function ($row) {
                    $total_amount = ($row->total_amount != null) ? $row->total_amount : 0;
                    $pending_amonut = ($row->pending_amonut != null) ? $row->pending_amonut : 0;
                    return number_format(($total_amount - $pending_amonut), 2);
                })
                ->editColumn('net_customer_price', function ($row) {
                    $totalamount = ($row->salesquatationfull->total_amount != null) ? $row->salesquatationfull->total_amount : 0;
                    $subsidy = ($row->salesquatationfull->subsidy != null) ? $row->salesquatationfull->subsidy : 0;
                    return number_format(($totalamount - $subsidy), 2);
                })
                ->addColumn('application_pending', function ($row) {
                    return '<span class="badge badge-light-secondary">' . toGetSalesMasterLastStatus($row->id) . '</span>
                    <a data-id="' . $row->id . '" href="javascript:void(0)" class="status-view avatar bg-light-warning p-50 m-0" data-bs-toggle="tooltip" data-placement="left" title="View"><i class="fa fa-stack-exchange"></i></a>';
                })
                ->escapeColumns([])
                ->make(true);
        } else {
            return view('admin.reports.meter-charges', compact('agentSalesPerson'));
        }
    }
    public function meterChargesDownload(Request $request)
    {
        return Excel::download(new MeterChargesExport($request), 'Meter_Charges.xlsx');
    }
    public function dispach()
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
            return DataTables::of(SalesMaster::with('district', 'taluka', 'village', 'subDivision', 'salesquatationfull', 'agentsalesperson')->orderBy('id', 'DESC'))
                ->addIndexColumn()
                ->filter(function ($query) {
                    $query->where('file_cancel_order', '0');
                    $query->where(function ($q) {
                        $q->where('installation_done',  "0");
                    });
                    $query->where('dispach_pending_list', "1");
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
                        if (request()->input('agent_sales_person_id') == "") {
                            $query->whereIn('agent_sales_person_id', $agentIds);
                        }
                    }
                    if ($company->user_type == 'S') {
                        $agent = AgentSalesPerson::where('user_id', Auth::id())->first();
                        $id = $agent->id;
                        $query->where('agent_sales_person_id', $id);
                    }
                    if (request()->input('from_date') != "" && request()->input('to_date') == '') {
                        $query->where('master_create_date', '>=', date('Y-m-d 00:00:00', strtotime(request()->input('from_date'))));
                        $query->where('master_create_date', '<=', date('Y-m-d 23:59:59'));
                    }
                    if (request()->input('from_date') != "" && request()->input('to_date') != '') {
                        $query->where('master_create_date', '>=', date('Y-m-d 00:00:00', strtotime(request()->input('from_date'))));
                        $query->where('master_create_date', '<=', date('Y-m-d 23:59:59', strtotime(request()->input('to_date'))));
                    }
                    if (request()->input('from_date') == "" && request()->input('to_date') != '') {
                        $query->where('master_create_date', '<=', date('Y-m-d 23:59:59', strtotime(request()->input('to_date'))));
                    }
                    if (request()->input('consumer') != "") {
                        $query->where(function ($q) {
                            $consumer = request()->input('consumer');
                            $q->where('consumer_name', 'like', '%' . $consumer . '%')
                                ->orWhere('consumer_number', 'like', '%' . $consumer . '%')
                                ->orWhere('contact_number', 'like', '%' . $consumer . '%');
                        });
                    }
                    if (request()->input('agent_sales_person_id') != "") {
                        $query->where('agent_sales_person_id', request()->input('agent_sales_person_id'));
                    }
                    if (request()->input('status') != "") {
                        $query->where(request()->input('status'), "1");
                    }
                    if (request()->input('not_status') != "") {
                        $query->where(request()->input('not_status'), "0");
                    }
                })
                ->editColumn('consumer', function ($row) {
                    return $row->consumer_name . '<br/>' . $row->contact_number;
                })
                ->editColumn('salesquatationfull.meter_charges', function ($row) {
                    return number_format((($row->salesquatationfull->meter_charges != null) ? $row->salesquatationfull->meter_charges : 0), 2);
                })
                ->editColumn('salesquatationfull.registration_fee', function ($row) {
                    return number_format((($row->salesquatationfull->registration_fee != null) ? $row->salesquatationfull->registration_fee : 0), 2);
                })
                ->editColumn('salesquatationfull.other_charge_amount', function ($row) {
                    return number_format((($row->salesquatationfull->other_charge_amount != null) ? $row->salesquatationfull->other_charge_amount : 0), 2);
                })
                ->editColumn('salesquatationfull.subsidy', function ($row) {
                    return number_format((($row->salesquatationfull->subsidy != null) ? $row->salesquatationfull->subsidy : 0), 2);
                })
                ->editColumn('pending_amonut', function ($row) {
                    return number_format((($row->pending_amonut != null) ? $row->pending_amonut : 0), 2);
                })
                ->editColumn('totalamt', function ($row) {
                    $totalamount = ($row->salesquatationfull->total_amount != null) ? $row->salesquatationfull->total_amount : 0;
                    $meter_charges = ($row->salesquatationfull->meter_charges != null) ? $row->salesquatationfull->meter_charges : 0;
                    $registration_fee = ($row->salesquatationfull->registration_fee != null) ? $row->salesquatationfull->registration_fee : 0;
                    $other_charge_amount = ($row->salesquatationfull->other_charge_amount != null) ? $row->salesquatationfull->other_charge_amount : 0;
                    return number_format(($totalamount + $meter_charges + $registration_fee  + $other_charge_amount), 2);
                })
                ->editColumn('received_amonut', function ($row) {
                    $total_amount = ($row->total_amount != null) ? $row->total_amount : 0;
                    $pending_amonut = ($row->pending_amonut != null) ? $row->pending_amonut : 0;
                    return number_format(($total_amount - $pending_amonut), 2);
                })
                ->editColumn('net_customer_price', function ($row) {
                    $totalamount = ($row->salesquatationfull->total_amount != null) ? $row->salesquatationfull->total_amount : 0;
                    $subsidy = ($row->salesquatationfull->subsidy != null) ? $row->salesquatationfull->subsidy : 0;
                    return number_format(($totalamount - $subsidy), 2);
                })
                ->addColumn('application_pending', function ($row) {
                    return '<span class="badge badge-light-secondary">' . toGetSalesMasterLastStatus($row->id) . '</span>
                    <a data-id="' . $row->id . '" href="javascript:void(0)" class="status-view avatar bg-light-warning p-50 m-0" data-bs-toggle="tooltip" data-placement="left" title="View"><i class="fa fa-stack-exchange"></i></a>';
                })
                ->addColumn('installer', function ($row) {
                    return installationAsignPerson($row->installation_asian_person);
                })
                ->escapeColumns([])
                ->make(true);
        } else {
            return view('admin.reports.dispach-list', compact('agentSalesPerson'));
        }
    }
    public function dispachDownload(Request $request)
    {
        return Excel::download(new DispachListExport($request), 'dispach_list.xlsx');
    }
    public function installation()
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
            return DataTables::of(SalesMaster::with('district', 'taluka', 'village', 'subDivision', 'salesquatationfull', 'agentsalesperson')->orderBy('id', 'DESC'))
                ->addIndexColumn()
                ->filter(function ($query) {
                    $query->where('file_cancel_order', '0');
                    $query->where('installation_asian_person', "!=", "");
                    $query->where(function ($q) {
                        $q->where('installation_pending',  "1")
                            ->orWhere('installation_done', "1");
                    });
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
                        if (request()->input('agent_sales_person_id') == "") {
                            $query->whereIn('agent_sales_person_id', $agentIds);
                        }
                    }
                    if ($company->user_type == 'S') {
                        $agent = AgentSalesPerson::where('user_id', Auth::id())->first();
                        $id = $agent->id;
                        $query->where('agent_sales_person_id', $id);
                    }
                    if (request()->input('from_date') != "" && request()->input('to_date') == '') {
                        $query->where('master_create_date', '>=', date('Y-m-d 00:00:00', strtotime(request()->input('from_date'))));
                        $query->where('master_create_date', '<=', date('Y-m-d 23:59:59'));
                    }
                    if (request()->input('from_date') != "" && request()->input('to_date') != '') {
                        $query->where('master_create_date', '>=', date('Y-m-d 00:00:00', strtotime(request()->input('from_date'))));
                        $query->where('master_create_date', '<=', date('Y-m-d 23:59:59', strtotime(request()->input('to_date'))));
                    }
                    if (request()->input('from_date') == "" && request()->input('to_date') != '') {
                        $query->where('master_create_date', '<=', date('Y-m-d 23:59:59', strtotime(request()->input('to_date'))));
                    }
                    if (request()->input('consumer') != "") {
                        $query->where(function ($q) {
                            $consumer = request()->input('consumer');
                            $q->where('consumer_name', 'like', '%' . $consumer . '%')
                                ->orWhere('consumer_number', 'like', '%' . $consumer . '%')
                                ->orWhere('contact_number', 'like', '%' . $consumer . '%');
                        });
                    }
                    if (request()->input('agent_sales_person_id') != "") {
                        $query->where('agent_sales_person_id', request()->input('agent_sales_person_id'));
                    }
                    if (request()->input('status') != "") {
                        $query->where(request()->input('status'), "1");
                    }
                    if (request()->input('not_status') != "") {
                        $query->where(request()->input('not_status'), "0");
                    }
                })
                ->editColumn('consumer', function ($row) {
                    return $row->consumer_name . '<br/>' . $row->contact_number;
                })
                ->editColumn('salesquatationfull.meter_charges', function ($row) {
                    return number_format((($row->salesquatationfull->meter_charges != null) ? $row->salesquatationfull->meter_charges : 0), 2);
                })
                ->editColumn('salesquatationfull.registration_fee', function ($row) {
                    return number_format((($row->salesquatationfull->registration_fee != null) ? $row->salesquatationfull->registration_fee : 0), 2);
                })
                ->editColumn('salesquatationfull.other_charge_amount', function ($row) {
                    return number_format((($row->salesquatationfull->other_charge_amount != null) ? $row->salesquatationfull->other_charge_amount : 0), 2);
                })
                ->editColumn('salesquatationfull.subsidy', function ($row) {
                    return number_format((($row->salesquatationfull->subsidy != null) ? $row->salesquatationfull->subsidy : 0), 2);
                })
                ->editColumn('pending_amonut', function ($row) {
                    return number_format((($row->pending_amonut != null) ? $row->pending_amonut : 0), 2);
                })
                ->editColumn('totalamt', function ($row) {
                    $totalamount = ($row->salesquatationfull->total_amount != null) ? $row->salesquatationfull->total_amount : 0;
                    $meter_charges = ($row->salesquatationfull->meter_charges != null) ? $row->salesquatationfull->meter_charges : 0;
                    $registration_fee = ($row->salesquatationfull->registration_fee != null) ? $row->salesquatationfull->registration_fee : 0;
                    $other_charge_amount = ($row->salesquatationfull->other_charge_amount != null) ? $row->salesquatationfull->other_charge_amount : 0;
                    return number_format(($totalamount + $meter_charges + $registration_fee  + $other_charge_amount), 2);
                })
                ->editColumn('received_amonut', function ($row) {
                    $total_amount = ($row->total_amount != null) ? $row->total_amount : 0;
                    $pending_amonut = ($row->pending_amonut != null) ? $row->pending_amonut : 0;
                    return number_format(($total_amount - $pending_amonut), 2);
                })
                ->editColumn('net_customer_price', function ($row) {
                    $totalamount = ($row->salesquatationfull->total_amount != null) ? $row->salesquatationfull->total_amount : 0;
                    $subsidy = ($row->salesquatationfull->subsidy != null) ? $row->salesquatationfull->subsidy : 0;
                    return number_format(($totalamount - $subsidy), 2);
                })
                ->addColumn('application_pending', function ($row) {
                    return '<span class="badge badge-light-secondary">' . toGetSalesMasterLastStatus($row->id) . '</span>
                    <a data-id="' . $row->id . '" href="javascript:void(0)" class="status-view avatar bg-light-warning p-50 m-0" data-bs-toggle="tooltip" data-placement="left" title="View"><i class="fa fa-stack-exchange"></i></a>';
                })
                ->addColumn('installer', function ($row) {
                    return installationAsignPerson($row->installation_asian_person);
                })
                ->escapeColumns([])
                ->make(true);
        } else {
            return view('admin.reports.installation-list', compact('agentSalesPerson'));
        }
    }
    public function installationNew()
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
            return DataTables::of(SalesMaster::with('district', 'taluka', 'village', 'subDivision', 'salesquatationfull', 'agentsalesperson')->orderBy('id', 'DESC'))
                ->addIndexColumn()
                ->filter(function ($query) {
                    $query->where('file_cancel_order', '0');
                    $query->where('installation_asian_person', "!=", "");
                    $query->where(function ($q) {
                        $q->where('installation_pending',  "1")
                            ->orWhere('installation_done', "1");
                    });
                    $query->whereHas('installation', function ($q) {
                        $q->where('form_type', 'new');
                    });
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
                        if (request()->input('agent_sales_person_id') == "") {
                            $query->whereIn('agent_sales_person_id', $agentIds);
                        }
                    }
                    if ($company->user_type == 'S') {
                        $agent = AgentSalesPerson::where('user_id', Auth::id())->first();
                        $id = $agent->id;
                        $query->where('agent_sales_person_id', $id);
                    }
                    if (request()->input('from_date') != "" && request()->input('to_date') == '') {
                        $query->where('master_create_date', '>=', date('Y-m-d 00:00:00', strtotime(request()->input('from_date'))));
                        $query->where('master_create_date', '<=', date('Y-m-d 23:59:59'));
                    }
                    if (request()->input('from_date') != "" && request()->input('to_date') != '') {
                        $query->where('master_create_date', '>=', date('Y-m-d 00:00:00', strtotime(request()->input('from_date'))));
                        $query->where('master_create_date', '<=', date('Y-m-d 23:59:59', strtotime(request()->input('to_date'))));
                    }
                    if (request()->input('from_date') == "" && request()->input('to_date') != '') {
                        $query->where('master_create_date', '<=', date('Y-m-d 23:59:59', strtotime(request()->input('to_date'))));
                    }
                    if (request()->input('consumer') != "") {
                        $query->where(function ($q) {
                            $consumer = request()->input('consumer');
                            $q->where('consumer_name', 'like', '%' . $consumer . '%')
                                ->orWhere('consumer_number', 'like', '%' . $consumer . '%')
                                ->orWhere('contact_number', 'like', '%' . $consumer . '%');
                        });
                    }
                    if (request()->input('agent_sales_person_id') != "") {
                        $query->where('agent_sales_person_id', request()->input('agent_sales_person_id'));
                    }
                    if (request()->input('status') != "") {
                        $query->where(request()->input('status'), "1");
                    }
                    if (request()->input('not_status') != "") {
                        $query->where(request()->input('not_status'), "0");
                    }
                })
                ->editColumn('consumer', function ($row) {
                    return $row->consumer_name . '<br/>' . $row->contact_number;
                })
                ->editColumn('salesquatationfull.meter_charges', function ($row) {
                    return number_format((($row->salesquatationfull->meter_charges != null) ? $row->salesquatationfull->meter_charges : 0), 2);
                })
                ->editColumn('salesquatationfull.registration_fee', function ($row) {
                    return number_format((($row->salesquatationfull->registration_fee != null) ? $row->salesquatationfull->registration_fee : 0), 2);
                })
                ->editColumn('salesquatationfull.other_charge_amount', function ($row) {
                    return number_format((($row->salesquatationfull->other_charge_amount != null) ? $row->salesquatationfull->other_charge_amount : 0), 2);
                })
                ->editColumn('salesquatationfull.subsidy', function ($row) {
                    return number_format((($row->salesquatationfull->subsidy != null) ? $row->salesquatationfull->subsidy : 0), 2);
                })
                ->editColumn('pending_amonut', function ($row) {
                    return number_format((($row->pending_amonut != null) ? $row->pending_amonut : 0), 2);
                })
                ->editColumn('totalamt', function ($row) {
                    $totalamount = ($row->salesquatationfull->total_amount != null) ? $row->salesquatationfull->total_amount : 0;
                    $meter_charges = ($row->salesquatationfull->meter_charges != null) ? $row->salesquatationfull->meter_charges : 0;
                    $registration_fee = ($row->salesquatationfull->registration_fee != null) ? $row->salesquatationfull->registration_fee : 0;
                    $other_charge_amount = ($row->salesquatationfull->other_charge_amount != null) ? $row->salesquatationfull->other_charge_amount : 0;
                    return number_format(($totalamount + $meter_charges + $registration_fee  + $other_charge_amount), 2);
                })
                ->editColumn('received_amonut', function ($row) {
                    $total_amount = ($row->total_amount != null) ? $row->total_amount : 0;
                    $pending_amonut = ($row->pending_amonut != null) ? $row->pending_amonut : 0;
                    return number_format(($total_amount - $pending_amonut), 2);
                })
                ->editColumn('net_customer_price', function ($row) {
                    $totalamount = ($row->salesquatationfull->total_amount != null) ? $row->salesquatationfull->total_amount : 0;
                    $subsidy = ($row->salesquatationfull->subsidy != null) ? $row->salesquatationfull->subsidy : 0;
                    return number_format(($totalamount - $subsidy), 2);
                })
                ->addColumn('application_pending', function ($row) {
                    return '<span class="badge badge-light-secondary">' . toGetSalesMasterLastStatus($row->id) . '</span>
                    <a data-id="' . $row->id . '" href="javascript:void(0)" class="status-view avatar bg-light-warning p-50 m-0" data-bs-toggle="tooltip" data-placement="left" title="View"><i class="fa fa-stack-exchange"></i></a>';
                })
                ->addColumn('installer', function ($row) {
                    return installationAsignPerson($row->installation_asian_person);
                })
                ->escapeColumns([])
                ->make(true);
        } else {
            return view('admin.reports.installation-new-list', compact('agentSalesPerson'));
        }
    }
    public function installationDownload(Request $request)
    {
        return Excel::download(new InstallationListExport($request, 'old'), 'installation_list.xlsx');
    }
    public function installationNewDownload(Request $request)
    {
        return Excel::download(new InstallationListExport($request, 'new'), 'installation_new_list.xlsx');
    }
    public function meterApplication()
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
            return DataTables::of(SalesMaster::with('district', 'taluka', 'village', 'subDivision', 'salesquatationfull', 'agentsalesperson')->orderBy('id', 'DESC'))
                ->addIndexColumn()
                ->filter(function ($query) {
                    $query->where('file_cancel_order', '0');
                    $query->where('meter_application_done', "1");
                    $query->where('meter_installation', "0");

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
                        if (request()->input('agent_sales_person_id') == "") {
                            $query->whereIn('agent_sales_person_id', $agentIds);
                        }
                    }
                    if ($company->user_type == 'S') {
                        $agent = AgentSalesPerson::where('user_id', Auth::id())->first();
                        $id = $agent->id;
                        $query->where('agent_sales_person_id', $id);
                    }
                    if (request()->input('from_date') != "" && request()->input('to_date') == '') {
                        $query->where('master_create_date', '>=', date('Y-m-d 00:00:00', strtotime(request()->input('from_date'))));
                        $query->where('master_create_date', '<=', date('Y-m-d 23:59:59'));
                    }
                    if (request()->input('from_date') != "" && request()->input('to_date') != '') {
                        $query->where('master_create_date', '>=', date('Y-m-d 00:00:00', strtotime(request()->input('from_date'))));
                        $query->where('master_create_date', '<=', date('Y-m-d 23:59:59', strtotime(request()->input('to_date'))));
                    }
                    if (request()->input('from_date') == "" && request()->input('to_date') != '') {
                        $query->where('master_create_date', '<=', date('Y-m-d 23:59:59', strtotime(request()->input('to_date'))));
                    }
                    if (request()->input('consumer') != "") {
                        $query->where(function ($q) {
                            $consumer = request()->input('consumer');
                            $q->where('consumer_name', 'like', '%' . $consumer . '%')
                                ->orWhere('consumer_number', 'like', '%' . $consumer . '%')
                                ->orWhere('contact_number', 'like', '%' . $consumer . '%');
                        });
                    }
                    if (request()->input('agent_sales_person_id') != "") {
                        $query->where('agent_sales_person_id', request()->input('agent_sales_person_id'));
                    }
                    if (request()->input('status') != "") {
                        $query->where(request()->input('status'), "1");
                    }
                    if (request()->input('not_status') != "") {
                        $query->where(request()->input('not_status'), "0");
                    }
                })
                ->editColumn('consumer', function ($row) {
                    return $row->consumer_name . '<br/>' . $row->contact_number;
                })
                ->editColumn('salesquatationfull.meter_charges', function ($row) {
                    return number_format((($row->salesquatationfull->meter_charges != null) ? $row->salesquatationfull->meter_charges : 0), 2);
                })
                ->editColumn('salesquatationfull.registration_fee', function ($row) {
                    return number_format((($row->salesquatationfull->registration_fee != null) ? $row->salesquatationfull->registration_fee : 0), 2);
                })
                ->editColumn('salesquatationfull.other_charge_amount', function ($row) {
                    return number_format((($row->salesquatationfull->other_charge_amount != null) ? $row->salesquatationfull->other_charge_amount : 0), 2);
                })
                ->editColumn('salesquatationfull.subsidy', function ($row) {
                    return number_format((($row->salesquatationfull->subsidy != null) ? $row->salesquatationfull->subsidy : 0), 2);
                })
                ->editColumn('pending_amonut', function ($row) {
                    return number_format((($row->pending_amonut != null) ? $row->pending_amonut : 0), 2);
                })
                ->editColumn('totalamt', function ($row) {
                    $totalamount = ($row->salesquatationfull->total_amount != null) ? $row->salesquatationfull->total_amount : 0;
                    $meter_charges = ($row->salesquatationfull->meter_charges != null) ? $row->salesquatationfull->meter_charges : 0;
                    $registration_fee = ($row->salesquatationfull->registration_fee != null) ? $row->salesquatationfull->registration_fee : 0;
                    $other_charge_amount = ($row->salesquatationfull->other_charge_amount != null) ? $row->salesquatationfull->other_charge_amount : 0;
                    return number_format(($totalamount + $meter_charges + $registration_fee  + $other_charge_amount), 2);
                })
                ->editColumn('received_amonut', function ($row) {
                    $total_amount = ($row->total_amount != null) ? $row->total_amount : 0;
                    $pending_amonut = ($row->pending_amonut != null) ? $row->pending_amonut : 0;
                    return number_format(($total_amount - $pending_amonut), 2);
                })
                ->editColumn('net_customer_price', function ($row) {
                    $totalamount = ($row->salesquatationfull->total_amount != null) ? $row->salesquatationfull->total_amount : 0;
                    $subsidy = ($row->salesquatationfull->subsidy != null) ? $row->salesquatationfull->subsidy : 0;
                    return number_format(($totalamount - $subsidy), 2);
                })
                ->addColumn('application_pending', function ($row) {
                    return '<span class="badge badge-light-secondary">' . toGetSalesMasterLastStatus($row->id) . '</span>
                    <a data-id="' . $row->id . '" href="javascript:void(0)" class="status-view avatar bg-light-warning p-50 m-0" data-bs-toggle="tooltip" data-placement="left" title="View"><i class="fa fa-stack-exchange"></i></a>';
                })
                ->addColumn('installer', function ($row) {
                    return installationAsignPerson($row->installation_asian_person);
                })
                ->escapeColumns([])
                ->make(true);
        } else {
            return view('admin.reports.meter-application', compact('agentSalesPerson'));
        }
    }
    public function meterApplicationDownload(Request $request)
    {
        return Excel::download(new MeterApplicationExport($request), 'meter_application.xlsx');
    }
    public function finalReport()
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
            return DataTables::of(SalesMaster::with('district', 'taluka', 'village', 'subDivision', 'salesquatationfull', 'agentsalesperson')->orderBy('id', 'DESC'))
                ->addIndexColumn()
                ->filter(function ($query) {
                    $query->where('file_cancel_order', '0');
                    $query->where('project_completion', "1");
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
                        if (request()->input('agent_sales_person_id') == "") {
                            $query->whereIn('agent_sales_person_id', $agentIds);
                        }
                    }
                    if ($company->user_type == 'S') {
                        $agent = AgentSalesPerson::where('user_id', Auth::id())->first();
                        $id = $agent->id;
                        $query->where('agent_sales_person_id', $id);
                    }
                    if (request()->input('from_date') != "" && request()->input('to_date') == '') {
                        $query->where('master_create_date', '>=', date('Y-m-d 00:00:00', strtotime(request()->input('from_date'))));
                        $query->where('master_create_date', '<=', date('Y-m-d 23:59:59'));
                    }
                    if (request()->input('from_date') != "" && request()->input('to_date') != '') {
                        $query->where('master_create_date', '>=', date('Y-m-d 00:00:00', strtotime(request()->input('from_date'))));
                        $query->where('master_create_date', '<=', date('Y-m-d 23:59:59', strtotime(request()->input('to_date'))));
                    }
                    if (request()->input('from_date') == "" && request()->input('to_date') != '') {
                        $query->where('master_create_date', '<=', date('Y-m-d 23:59:59', strtotime(request()->input('to_date'))));
                    }
                    if (request()->input('consumer') != "") {
                        $query->where(function ($q) {
                            $consumer = request()->input('consumer');
                            $q->where('consumer_name', 'like', '%' . $consumer . '%')
                                ->orWhere('consumer_number', 'like', '%' . $consumer . '%')
                                ->orWhere('contact_number', 'like', '%' . $consumer . '%');
                        });
                    }
                    if (request()->input('agent_sales_person_id') != "") {
                        $query->where('agent_sales_person_id', request()->input('agent_sales_person_id'));
                    }
                    if (request()->input('status') != "") {
                        $query->where(request()->input('status'), "1");
                    }
                    if (request()->input('not_status') != "") {
                        $query->where(request()->input('not_status'), "0");
                    }
                })
                ->editColumn('consumer', function ($row) {
                    return $row->consumer_name . '<br/>' . $row->contact_number;
                })
                ->editColumn('salesquatationfull.meter_charges', function ($row) {
                    return number_format((($row->salesquatationfull->meter_charges != null) ? $row->salesquatationfull->meter_charges : 0), 2);
                })
                ->editColumn('salesquatationfull.registration_fee', function ($row) {
                    return number_format((($row->salesquatationfull->registration_fee != null) ? $row->salesquatationfull->registration_fee : 0), 2);
                })
                ->editColumn('salesquatationfull.other_charge_amount', function ($row) {
                    return number_format((($row->salesquatationfull->other_charge_amount != null) ? $row->salesquatationfull->other_charge_amount : 0), 2);
                })
                ->editColumn('salesquatationfull.subsidy', function ($row) {
                    return number_format((($row->salesquatationfull->subsidy != null) ? $row->salesquatationfull->subsidy : 0), 2);
                })
                ->editColumn('pending_amonut', function ($row) {
                    return number_format((($row->pending_amonut != null) ? $row->pending_amonut : 0), 2);
                })
                ->editColumn('totalamt', function ($row) {
                    $totalamount = ($row->salesquatationfull->total_amount != null) ? $row->salesquatationfull->total_amount : 0;
                    $meter_charges = ($row->salesquatationfull->meter_charges != null) ? $row->salesquatationfull->meter_charges : 0;
                    $registration_fee = ($row->salesquatationfull->registration_fee != null) ? $row->salesquatationfull->registration_fee : 0;
                    $other_charge_amount = ($row->salesquatationfull->other_charge_amount != null) ? $row->salesquatationfull->other_charge_amount : 0;
                    return number_format(($totalamount + $meter_charges + $registration_fee  + $other_charge_amount), 2);
                })
                ->editColumn('received_amonut', function ($row) {
                    $total_amount = ($row->total_amount != null) ? $row->total_amount : 0;
                    $pending_amonut = ($row->pending_amonut != null) ? $row->pending_amonut : 0;
                    return number_format(($total_amount - $pending_amonut), 2);
                })
                ->editColumn('net_customer_price', function ($row) {
                    $totalamount = ($row->salesquatationfull->total_amount != null) ? $row->salesquatationfull->total_amount : 0;
                    $subsidy = ($row->salesquatationfull->subsidy != null) ? $row->salesquatationfull->subsidy : 0;
                    return number_format(($totalamount - $subsidy), 2);
                })
                ->addColumn('application_pending', function ($row) {
                    return '<span class="badge badge-light-secondary">' . toGetSalesMasterLastStatus($row->id) . '</span>
                    <a data-id="' . $row->id . '" href="javascript:void(0)" class="status-view avatar bg-light-warning p-50 m-0" data-bs-toggle="tooltip" data-placement="left" title="View"><i class="fa fa-stack-exchange"></i></a>';
                })
                ->addColumn('installer', function ($row) {
                    return installationAsignPerson($row->meter_asian_person);
                })
                ->escapeColumns([])
                ->make(true);
        } else {
            return view('admin.reports.final-orders', compact('agentSalesPerson'));
        }
    }
    public function finalReportDownload(Request $request)
    {
        return Excel::download(new FinalOrdersExport($request), 'final_orders.xlsx');
    }
    public function invoice()
    {
        $companyFind = CompanyProfile::where('user_id', Auth::id())->first();
        $agentWhere = "";
        if ($companyFind->user_type == 'M') {
            $id = $companyFind->id;
            $agentWhere .= '(company_profiles.user_id = ' . Auth::id() . ' OR  company_profiles.manager_id = ' . $id . ')';
        }
        if ($companyFind->user_type == 'S') {
            $id = $companyFind->id;
            $manager_id = $companyFind->manager_id;
            $agentWhere .= '(company_profiles.id = ' . $id . ' OR  company_profiles.id = ' . $manager_id . ')';
        }
        $q = CompanyProfile::select('agent_sales_people.*')->leftJoin('agent_sales_people', 'agent_sales_people.user_id', 'company_profiles.user_id');
        if ($agentWhere != "") {
            $q->whereRaw($agentWhere);
        }
        $agentSalesPerson = $q->get();
        if (request()->ajax()) {
            return DataTables::of(SalesMaster::with('district', 'taluka', 'village', 'subDivision', 'salesquatationfull', 'agentsalesperson')->orderBy('invoice_date', 'DESC')->orderBy('invoice_no', 'DESC'))
                ->addIndexColumn()
                ->filter(function ($query) {
                    $query->where('file_cancel_order', '0');
                    $query->where('dispach_pending_list', '1');
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
                        if (request()->input('agent_sales_person_id') == "") {
                            $query->whereIn('agent_sales_person_id', $agentIds);
                        }
                    }
                    if ($company->user_type == 'S') {
                        $agent = AgentSalesPerson::where('user_id', Auth::id())->first();
                        $id = $agent->id;
                        $query->where('agent_sales_person_id', $id);
                    }
                    if (request()->input('from_date') != "" && request()->input('to_date') == '') {
                        $query->where('master_create_date', '>=', date('Y-m-d 00:00:00', strtotime(request()->input('from_date'))));
                        $query->where('master_create_date', '<=', date('Y-m-d 23:59:59'));
                    }
                    if (request()->input('from_date') != "" && request()->input('to_date') != '') {
                        $query->where('master_create_date', '>=', date('Y-m-d 00:00:00', strtotime(request()->input('from_date'))));
                        $query->where('master_create_date', '<=', date('Y-m-d 23:59:59', strtotime(request()->input('to_date'))));
                    }
                    if (request()->input('from_date') == "" && request()->input('to_date') != '') {
                        $query->where('master_create_date', '<=', date('Y-m-d 23:59:59', strtotime(request()->input('to_date'))));
                    }
                    if (request()->input('consumer') != "") {
                        $query->where(function ($q) {
                            $consumer = request()->input('consumer');
                            $q->where('consumer_name', 'like', '%' . $consumer . '%')
                                ->orWhere('consumer_number', 'like', '%' . $consumer . '%')
                                ->orWhere('contact_number', 'like', '%' . $consumer . '%');
                        });
                    }
                    if (request()->input('agent_sales_person_id') != "") {
                        $query->where('agent_sales_person_id', request()->input('agent_sales_person_id'));
                    }
                    if (request()->input('status') != "") {
                        $query->where(request()->input('status'), "1");
                    }
                    if (request()->input('not_status') != "") {
                        $query->where(request()->input('not_status'), "0");
                    }
                    if (request()->input('invoice') == "YES") {
                        $query->where(function ($q) {
                            $q->where('invoice_date', '!=', '')
                                ->where('invoice_date', '!=', '0000-00-00');
                        });
                    }
                    if (request()->input('invoice') == "NO") {
                        $query->where(function ($q) {
                            $q->where('invoice_date', '==', '')
                                ->orWhere('invoice_date', '==', '0000-00-00');
                        });
                    }
                })
                ->editColumn('consumer', function ($row) {
                    return $row->consumer_name . '<br/>' . $row->contact_number;
                })
                ->editColumn('salesquatationfull.meter_charges', function ($row) {
                    return number_format((($row->salesquatationfull->meter_charges != null) ? $row->salesquatationfull->meter_charges : 0), 2);
                })
                ->editColumn('salesquatationfull.registration_fee', function ($row) {
                    return number_format((($row->salesquatationfull->registration_fee != null) ? $row->salesquatationfull->registration_fee : 0), 2);
                })
                ->editColumn('salesquatationfull.other_charge_amount', function ($row) {
                    return number_format((($row->salesquatationfull->other_charge_amount != null) ? $row->salesquatationfull->other_charge_amount : 0), 2);
                })
                ->editColumn('salesquatationfull.subsidy', function ($row) {
                    return number_format((($row->salesquatationfull->subsidy != null) ? $row->salesquatationfull->subsidy : 0), 2);
                })
                ->editColumn('pending_amonut', function ($row) {
                    return number_format((($row->pending_amonut != null) ? $row->pending_amonut : 0), 2);
                })
                ->editColumn('totalamt', function ($row) {
                    $totalamount = ($row->salesquatationfull->total_amount != null) ? $row->salesquatationfull->total_amount : 0;
                    return number_format(($totalamount), 2);
                })
                ->addColumn('installer', function ($row) {
                    return installationAsignPerson($row->installation_asian_person);
                })
                ->addColumn('invoice_date', function ($row) {
                    return ($row->invoice_date != "0000-00-00" && $row->invoice_date != "") ? $row->invoice_date : '';
                })
                ->addColumn('total_system', function ($row) {
                    $meter_charges = ($row->salesquatationfull->meter_charges != null) ? $row->salesquatationfull->meter_charges : 0;
                    $registration_fee = ($row->salesquatationfull->registration_fee != null) ? $row->salesquatationfull->registration_fee : 0;
                    $other_charge_amount = ($row->salesquatationfull->other_charge_amount != null) ? $row->salesquatationfull->other_charge_amount : 0;
                    if ($row->salesquatationfull->form_type == 'resident') {
                        $system_cost =  $row->salesquatationfull->total_system_cost;
                    } else {
                        $system_cost =  $row->salesquatationfull->total_amount - $meter_charges - $registration_fee - $other_charge_amount;
                    }
                    return number_format(($system_cost), 2);
                })
                ->editColumn('received_amonut', function ($row) {
                    $total_amount = ($row->total_amount != null) ? $row->total_amount : 0;
                    $pending_amonut = ($row->pending_amonut != null) ? $row->pending_amonut : 0;
                    return number_format(($total_amount - $pending_amonut), 2);
                })

                ->editColumn('salesquatationfull.total_amount', function ($row) {
                    return number_format(($row->salesquatationfull->total_amount), 2);
                })

                ->editColumn('net_customer_price', function ($row) {
                    $totalamount = ($row->salesquatationfull->total_amount != null) ? $row->salesquatationfull->total_amount : 0;
                    $subsidy = ($row->salesquatationfull->subsidy != null) ? $row->salesquatationfull->subsidy : 0;
                    return number_format(($totalamount - $subsidy), 2);
                })
                ->addColumn('application_pending', function ($row) {
                    return '<span class="badge badge-light-secondary">' . toGetSalesMasterLastStatus($row->id) . '</span>
                     <a data-id="' . $row->id . '" href="javascript:void(0)" class="status-view avatar bg-light-warning p-50 m-0" data-bs-toggle="tooltip" data-placement="left" title="View"><i class="fa fa-stack-exchange"></i></a>';
                })
                ->escapeColumns([])
                ->make(true);
        } else {
            return view('admin.reports.invoice', compact('agentSalesPerson'));
        }
    }
    public function invoiceDownload(Request $request)
    {
        return Excel::download(new InvoiceExport($request), 'Invoice_Report.xlsx');
    }

    public function panelsRequired()
    {
        $companyFind = CompanyProfile::where('user_id', Auth::id())->first();
        $agentWhere = "";
        if ($companyFind->user_type == 'M') {
            $id = $companyFind->id;
            $agentWhere .= '(company_profiles.user_id = ' . Auth::id() . ' OR  company_profiles.manager_id = ' . $id . ')';
        }
        if ($companyFind->user_type == 'S') {
            $id = $companyFind->id;
            $manager_id = $companyFind->manager_id;
            $agentWhere .= '(company_profiles.id = ' . $id . ' OR  company_profiles.id = ' . $manager_id . ')';
        }
        $q = CompanyProfile::select('agent_sales_people.*')->leftJoin('agent_sales_people', 'agent_sales_people.user_id', 'company_profiles.user_id');
        if ($agentWhere != "") {
            $q->whereRaw($agentWhere);
        }
        $agentSalesPerson = $q->get();

        $districts = District::get();
        $panelCompany = PenalCompany::get();
        $panelwatts = PenalWatt::get();

        if (request()->ajax()) {
            return DataTables::of(
                SalesMaster::select('sales_masters.*', DB::raw('SUM(sales_quatations.penal_nos) as total_panel'))
                    ->with('district', 'panel', 'panelwatt')
                    ->leftJoin('sales_quatations', 'sales_quatations.id', '=', 'sales_masters.sales_quatation_id')
                    ->groupBy(
                        'sales_masters.district_id',
                        'sales_masters.penal_company_id',
                        'sales_masters.penal_watt_id'
                    )
                    ->orderBy('sales_masters.district_id')
                    ->orderBy('sales_masters.penal_company_id')
                    ->orderBy('sales_masters.penal_watt_id')
            )
                ->addIndexColumn()
                ->filter(function ($query) {
                    $query->where('sales_masters.file_cancel_order', '0');
                    $query->where('sales_masters.installation_done', '0');
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
                        if (request()->input('agent_sales_person_id') == "") {
                            $query->whereIn('sales_masters.agent_sales_person_id', $agentIds);
                        }
                    }
                    if ($company->user_type == 'S') {
                        $agent = AgentSalesPerson::where('user_id', Auth::id())->first();
                        $id = $agent->id;
                        $query->where('sales_masters.agent_sales_person_id', $id);
                    }

                    if (request()->input('agent_sales_person_id') != "") {
                        $query->where('sales_masters.agent_sales_person_id', request()->input('agent_sales_person_id'));
                    }

                    if (request()->input('district_id') != "") {
                        $query->where('sales_masters.district_id', request()->input('district_id'));
                    }

                    if (request()->input('panel_company_id') != "") {
                        $query->where('sales_masters.penal_company_id', request()->input('panel_company_id'));
                    }

                    if (request()->input('panel_watt_id') != "") {
                        $query->where('sales_masters.penal_watt_id', request()->input('panel_watt_id'));
                    }
                })
                ->escapeColumns([])
                ->make(true);
        } else {
            return view('admin.reports.panels-required', compact('agentSalesPerson', 'districts', 'panelCompany', 'panelwatts'));
        }
    }
    public function panelsRequiredDownload(Request $request)
    {
        return Excel::download(new PanelsRequired($request), 'Panels_Required_Report.xlsx');
    }

    public function invertersRequired()
    {
        $companyFind = CompanyProfile::where('user_id', Auth::id())->first();
        $agentWhere = "";
        if ($companyFind->user_type == 'M') {
            $id = $companyFind->id;
            $agentWhere .= '(company_profiles.user_id = ' . Auth::id() . ' OR  company_profiles.manager_id = ' . $id . ')';
        }
        if ($companyFind->user_type == 'S') {
            $id = $companyFind->id;
            $manager_id = $companyFind->manager_id;
            $agentWhere .= '(company_profiles.id = ' . $id . ' OR  company_profiles.id = ' . $manager_id . ')';
        }
        $q = CompanyProfile::select('agent_sales_people.*')->leftJoin('agent_sales_people', 'agent_sales_people.user_id', 'company_profiles.user_id');
        if ($agentWhere != "") {
            $q->whereRaw($agentWhere);
        }
        $agentSalesPerson = $q->get();

        $districts = District::get();
        $inveterCompany = InveterCompany::get();

        if (request()->ajax()) {
            return DataTables::of(
                SalesMaster::select('sales_masters.*', DB::raw('SUM(sales_quatations.no_of_inveter) as total_inveter'))
                    ->with('district', 'inveter')
                    ->leftJoin('sales_quatations', 'sales_quatations.id', '=', 'sales_masters.sales_quatation_id')
                    ->groupBy(
                        'sales_masters.district_id',
                        'sales_masters.inveter_company_id'
                    )
                    ->orderBy('sales_masters.district_id')
                    ->orderBy('sales_masters.inveter_company_id')
            )
                ->addIndexColumn()
                ->filter(function ($query) {
                    $query->where('sales_masters.file_cancel_order', '0');
                    $query->where('sales_masters.installation_done', '0');
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
                        if (request()->input('agent_sales_person_id') == "") {
                            $query->whereIn('sales_masters.agent_sales_person_id', $agentIds);
                        }
                    }
                    if ($company->user_type == 'S') {
                        $agent = AgentSalesPerson::where('user_id', Auth::id())->first();
                        $id = $agent->id;
                        $query->where('sales_masters.agent_sales_person_id', $id);
                    }
                    if (request()->input('agent_sales_person_id') != "") {
                        $query->where('sales_masters.agent_sales_person_id', request()->input('agent_sales_person_id'));
                    }
                    if (request()->input('district_id') != "") {
                        $query->where('sales_masters.district_id', request()->input('district_id'));
                    }
                    if (request()->input('inveter_company_id') != "") {
                        $query->where('sales_masters.inveter_company_id', request()->input('inveter_company_id'));
                    }
                })
                ->escapeColumns([])
                ->make(true);
        } else {
            return view('admin.reports.inverter-required', compact('agentSalesPerson', 'districts', 'inveterCompany'));
        }
    }
    public function inverterRequiredDownload(Request $request)
    {
        return Excel::download(new InverterRequired($request), 'Inverter_Required_Report.xlsx');
    }

    public function subsidyClaimReports()
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
            return DataTables::of(SalesMaster::with('district', 'taluka', 'village', 'subDivision', 'salesquatationfull', 'agentsalesperson')->orderBy('id', 'DESC'))
                ->addIndexColumn()
                ->filter(function ($query) {
                    $query->where('file_cancel_order', '0');
                    $query->where('meter_installation', "1");

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
                        if (request()->input('agent_sales_person_id') == "") {
                            $query->whereIn('agent_sales_person_id', $agentIds);
                        }
                    }
                    if ($company->user_type == 'S') {
                        $agent = AgentSalesPerson::where('user_id', Auth::id())->first();
                        $id = $agent->id;
                        $query->where('agent_sales_person_id', $id);
                    }
                    if (request()->input('from_date') != "" && request()->input('to_date') == '') {
                        $query->where('master_create_date', '>=', date('Y-m-d 00:00:00', strtotime(request()->input('from_date'))));
                        $query->where('master_create_date', '<=', date('Y-m-d 23:59:59'));
                    }
                    if (request()->input('from_date') != "" && request()->input('to_date') != '') {
                        $query->where('master_create_date', '>=', date('Y-m-d 00:00:00', strtotime(request()->input('from_date'))));
                        $query->where('master_create_date', '<=', date('Y-m-d 23:59:59', strtotime(request()->input('to_date'))));
                    }
                    if (request()->input('from_date') == "" && request()->input('to_date') != '') {
                        $query->where('master_create_date', '<=', date('Y-m-d 23:59:59', strtotime(request()->input('to_date'))));
                    }
                    if (request()->input('consumer') != "") {
                        $query->where(function ($q) {
                            $consumer = request()->input('consumer');
                            $q->where('consumer_name', 'like', '%' . $consumer . '%')
                                ->orWhere('consumer_number', 'like', '%' . $consumer . '%')
                                ->orWhere('contact_number', 'like', '%' . $consumer . '%');
                        });
                    }
                    if (request()->input('agent_sales_person_id') != "") {
                        $query->where('agent_sales_person_id', request()->input('agent_sales_person_id'));
                    }
                    if (request()->input('status') != "") {
                        $query->where(request()->input('status'), "1");
                    }
                    if (request()->input('not_status') != "") {
                        $query->where(request()->input('not_status'), "0");
                    }
                })
                ->editColumn('consumer', function ($row) {
                    return $row->consumer_name . '<br/>' . $row->contact_number;
                })
                ->editColumn('meter_installation_date', function ($row) {
                    return (!is_null($row->meter_installation_date) && $row->meter_installation_date != '1970-01-01') ? date('d-m-Y', strtotime($row->meter_installation_date)) : '-';
                })
                ->editColumn('subsidy_request_date', function ($row) {
                    return (!is_null($row->subsidy_request_date) && $row->subsidy_request_date != '1970-01-01') ? date('d-m-Y', strtotime($row->subsidy_request_date)) : '-';
                })
                ->editColumn('subsidy_disbursement_verify_date', function ($row) {
                    return (!is_null($row->subsidy_disbursement_verify_date) && $row->subsidy_disbursement_verify_date != '1970-01-01') ? date('d-m-Y', strtotime($row->subsidy_disbursement_verify_date)) : '-';
                })
                ->editColumn('subsidy_disbursement_date', function ($row) {
                    return (!is_null($row->subsidy_disbursement_date) && $row->subsidy_disbursement_date != '1970-01-01') ? date('d-m-Y', strtotime($row->subsidy_disbursement_date)) : '-';
                })
                ->editColumn('salesquatationfull.meter_charges', function ($row) {
                    return number_format((($row->salesquatationfull->meter_charges != null) ? $row->salesquatationfull->meter_charges : 0), 2);
                })
                ->editColumn('salesquatationfull.registration_fee', function ($row) {
                    return number_format((($row->salesquatationfull->registration_fee != null) ? $row->salesquatationfull->registration_fee : 0), 2);
                })
                ->editColumn('salesquatationfull.other_charge_amount', function ($row) {
                    return number_format((($row->salesquatationfull->other_charge_amount != null) ? $row->salesquatationfull->other_charge_amount : 0), 2);
                })
                ->editColumn('salesquatationfull.subsidy', function ($row) {
                    return number_format((($row->salesquatationfull->subsidy != null) ? $row->salesquatationfull->subsidy : 0), 2);
                })
                ->editColumn('pending_amonut', function ($row) {
                    return number_format((($row->pending_amonut != null) ? $row->pending_amonut : 0), 2);
                })
                ->editColumn('totalamt', function ($row) {
                    $totalamount = ($row->salesquatationfull->total_amount != null) ? $row->salesquatationfull->total_amount : 0;
                    $meter_charges = ($row->salesquatationfull->meter_charges != null) ? $row->salesquatationfull->meter_charges : 0;
                    $registration_fee = ($row->salesquatationfull->registration_fee != null) ? $row->salesquatationfull->registration_fee : 0;
                    $other_charge_amount = ($row->salesquatationfull->other_charge_amount != null) ? $row->salesquatationfull->other_charge_amount : 0;
                    return number_format(($totalamount + $meter_charges + $registration_fee  + $other_charge_amount), 2);
                })
                ->editColumn('received_amonut', function ($row) {
                    $total_amount = ($row->total_amount != null) ? $row->total_amount : 0;
                    $pending_amonut = ($row->pending_amonut != null) ? $row->pending_amonut : 0;
                    return number_format(($total_amount - $pending_amonut), 2);
                })
                ->editColumn('net_customer_price', function ($row) {
                    $totalamount = ($row->salesquatationfull->total_amount != null) ? $row->salesquatationfull->total_amount : 0;
                    $subsidy = ($row->salesquatationfull->subsidy != null) ? $row->salesquatationfull->subsidy : 0;
                    return number_format(($totalamount - $subsidy), 2);
                })
                ->addColumn('application_pending', function ($row) {
                    return '<span class="badge badge-light-secondary">' . toGetSalesMasterLastStatus($row->id) . '</span>
                    <a data-id="' . $row->id . '" href="javascript:void(0)" class="status-view avatar bg-light-warning p-50 m-0" data-bs-toggle="tooltip" data-placement="left" title="View"><i class="fa fa-stack-exchange"></i></a>';
                })
                ->addColumn('installer', function ($row) {
                    return installationAsignPerson($row->installation_asian_person);
                })
                ->escapeColumns([])
                ->make(true);
        } else {
            return view('admin.reports.subsidy-claim', compact('agentSalesPerson'));
        }
    }

    public function subsidyClaimDownload(Request $request)
    {
        return Excel::download(new SubsidyClaimExport($request), 'subsidy_claim.xlsx');
    }
}
