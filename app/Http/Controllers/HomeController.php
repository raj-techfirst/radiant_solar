<?php

namespace App\Http\Controllers;

use App\Models\AgentSalesPerson;
use App\Models\CompanyProfile;
use App\Models\erp\DeliveryChallan;
use App\Models\erp\DeliveryChallanReturn;
use App\Models\erp\PurchaseDirect;
use App\Models\erp\PurchaseOrder;
use App\Models\erp\WarehouseStock;
use App\Models\LeadMaster;
use App\Models\SalesMaster;
use App\Models\SalesQuatation;
use App\Models\User;
use App\Models\Year;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        if (Auth::user()->roles[0]->name == 'Super Admin') {
            return view('home_detail');
        } else {

            if (session()->get('soft') == 'crm') {
                $salesData = [];
                $query = SalesMaster::select('id', 'total_amount', 'pending_amonut', 'agent_sales_person_id', 'register_kw', 'ragistration_portal')->with('agentsalesperson');

                $user = User::where('id', Auth::id())->first();
                if ($user->roles[0]->name == 'Manager') {
                    $agnt = AgentSalesPerson::where('user_id', Auth::id())->first();
                    $id = $agnt->id;
                    $agentIds = [$agnt->id];
                    $company = CompanyProfile::where('user_id', Auth::id())->first();
                    $sales = CompanyProfile::select('id', 'user_id')->where('manager_id', $company->id)->get();
                    if ($sales->count() > 0) {
                        foreach ($sales as $k => $v) :
                            $a = AgentSalesPerson::where('user_id', $v->user_id)->first();
                            if (!is_null($a)) {
                                array_push($agentIds, $a->id);
                            }
                        endforeach;
                    }
                    $query->whereIn('agent_sales_person_id', $agentIds);
                }
                if ($user->roles[0]->name == 'Sales') {
                    $agnt = AgentSalesPerson::where('user_id', Auth::id())->first();
                    $id = $agnt->id;
                    $query->where('agent_sales_person_id', $id);
                }
                $query->where('file_cancel_order', '0');

                $saleOrder = $query->get();

                $agents = [];
                if ($user->roles[0]->name == 'Super Admin' || $user->roles[0]->name == 'Admin') {
                    $agents = AgentSalesPerson::all();
                } elseif ($user->roles[0]->name == 'Manager') {

                     if(isset($agentIds)){
                         $agents = AgentSalesPerson::whereIn('id', $agentIds)->get();
                     } else {
                        $agents = [];
                     }
                }


                if ($saleOrder->count() > 0) {

                    $salesData['forstatus']['total']['name'] = 'Total';
                    $salesData['forstatus']['total']['count'] = 0;
                    $salesData['forstatus']['total']['register_kw'] = 0;

                    $salesData['forstatus']['application_pending']['name'] = 'Application Pending';
                    $salesData['forstatus']['application_pending']['count'] = 0;
                    $salesData['forstatus']['application_pending']['register_kw'] = 0;

                    $salesData['forstatus']['pending_approval']['name'] = 'Pending Approval';
                    $salesData['forstatus']['pending_approval']['count'] = 0;
                    $salesData['forstatus']['pending_approval']['register_kw'] = 0;

                    $salesData['forstatus']['feasibility_approved']['name'] = 'Feasibility Approved';
                    $salesData['forstatus']['feasibility_approved']['count'] = 0;
                    $salesData['forstatus']['feasibility_approved']['register_kw'] = 0;

                    $salesData['forstatus']['meter_charge_paid']['name'] = 'Meter Charge Paid';
                    $salesData['forstatus']['meter_charge_paid']['count'] = 0;
                    $salesData['forstatus']['meter_charge_paid']['register_kw'] = 0;

                        /* Loan */
                    $salesData['forstatus']['apply_for_loan_/_login']['name'] = 'Apply for loan / Login';
                    $salesData['forstatus']['apply_for_loan_/_login']['count'] = 0;
                    $salesData['forstatus']['apply_for_loan_/_login']['register_kw'] = 0;

                    $salesData['forstatus']['loan_sension']['name'] = 'Loan Sanction';
                    $salesData['forstatus']['loan_sension']['count'] = 0;
                    $salesData['forstatus']['loan_sension']['register_kw'] = 0;

                    $salesData['forstatus']['disbursement']['name'] = 'Disbursement';
                    $salesData['forstatus']['disbursement']['count'] = 0;
                    $salesData['forstatus']['disbursement']['register_kw'] = 0;
                    /* / Loan */

                    $salesData['forstatus']['payment_received']['name'] = 'Payment Received';
                    $salesData['forstatus']['payment_received']['count'] = 0;
                    $salesData['forstatus']['payment_received']['register_kw'] = 0;

                    $salesData['forstatus']['dispatch_pending_list']['name'] = 'Dispatch Pending List';
                    $salesData['forstatus']['dispatch_pending_list']['count'] = 0;
                    $salesData['forstatus']['dispatch_pending_list']['register_kw'] = 0;

                    $salesData['forstatus']['installation_pending']['name'] = 'Installation Pending';
                    $salesData['forstatus']['installation_pending']['count'] = 0;
                    $salesData['forstatus']['installation_pending']['register_kw'] = 0;

                    $salesData['forstatus']['installation_done']['name'] = 'Installation Done';
                    $salesData['forstatus']['installation_done']['count'] = 0;
                    $salesData['forstatus']['installation_done']['register_kw'] = 0;

                    $salesData['forstatus']['meter_application_done']['name'] = 'Meter Application Done';
                    $salesData['forstatus']['meter_application_done']['count'] = 0;
                    $salesData['forstatus']['meter_application_done']['register_kw'] = 0;

                    $salesData['forstatus']['meter_installation']['name'] = 'Meter Installation';
                    $salesData['forstatus']['meter_installation']['count'] = 0;
                    $salesData['forstatus']['meter_installation']['register_kw'] = 0;

                    $salesData['forstatus']['subsidy_request']['name'] = 'Subsidy Request';
                    $salesData['forstatus']['subsidy_request']['count'] = 0;
                    $salesData['forstatus']['subsidy_request']['register_kw'] = 0;

                    $salesData['forstatus']['subsidy_disbursal']['name'] = 'Subsidy Disbursal';
                    $salesData['forstatus']['subsidy_disbursal']['count'] = 0;
                    $salesData['forstatus']['subsidy_disbursal']['register_kw'] = 0;

                    $salesData['forstatus']['hold_/_query']['name'] = 'Hold / Query';
                    $salesData['forstatus']['hold_/_query']['count'] = 0;
                    $salesData['forstatus']['hold_/_query']['register_kw'] = 0;

                     $salesData['forPortal']['National']['count'] = 0;
                     $salesData['forPortal']['National']['register_kw'] = 0;
                     $salesData['forPortal']['GEDA']['count'] = 0;
                     $salesData['forPortal']['GEDA']['register_kw'] = 0;
                     $salesData['forPortal']['Other']['count'] = 0;
                     $salesData['forPortal']['Other']['register_kw'] = 0;


                    foreach ($saleOrder as $key => $value) :


                        if ($value->ragistration_portal == "National Portal") {
                            $value->ragistration_portal =  "National";
                        }
                        if ($value->ragistration_portal == "" || $value->ragistration_portal == null) {
                            $value->ragistration_portal =  "Other";
                        }

                        if (isset($salesData['forPortal'][$value->ragistration_portal]['count'])) {
                            $salesData['forPortal'][$value->ragistration_portal]['count'] += 1;
                            $salesData['forPortal'][$value->ragistration_portal]['register_kw'] += $value->register_kw;
                        } else {
                            $salesData['forPortal'][$value->ragistration_portal]['count'] = 1;
                            $salesData['forPortal'][$value->ragistration_portal]['register_kw'] = $value->register_kw;
                        }

                        $findStatus = toGetSalesMasterStatusForDashboard($value->id);
                        $lastStatus = $findStatus['title'];
                        $statusValue = $findStatus['value'];
                        $nextStatusValue = $findStatus['next'];
                        $status = strtolower(str_replace(" ", "_", $lastStatus));

                        $salesData['forstatus']['total']['name'] = "Total";
                        if (isset($salesData['forstatus']['total']['count'])) {
                            $salesData['forstatus']['total']['count'] += 1;
                            $salesData['forstatus']['total']['register_kw'] += $value->register_kw;
                        } else {
                            $salesData['forstatus']['total']['count'] = 1;
                            $salesData['forstatus']['total']['register_kw'] = $value->register_kw;
                        }

                        $salesData['foragent']['total']['name'] = "Total";
                        if (isset($salesData['foragent']['total']['count'])) {
                            $salesData['foragent']['total']['count'] += 1;
                            $salesData['foragent']['total']['register_kw'] += $value->register_kw;
                        } else {
                            $salesData['foragent']['total']['count'] = 1;
                            $salesData['foragent']['total']['register_kw'] = $value->register_kw;
                        }
                        $salesData['forstatus'][$status]['name'] = $lastStatus;
                        $salesData['forstatus'][$status]['status'] = $statusValue;
                        $salesData['forstatus'][$status]['status_not'] = $nextStatusValue;

                        if (isset($salesData['forstatus'][$status]['count'])) {
                            $salesData['forstatus'][$status]['count'] += 1;
                            $salesData['forstatus'][$status]['register_kw'] += $value->register_kw;
                        } else {
                            $salesData['forstatus'][$status]['count'] = 1;
                            $salesData['forstatus'][$status]['register_kw'] = $value->register_kw;
                        }

                        $salesData['foragent'][$value->agentsalesperson->id]['name'] = $value->agentsalesperson->name;
                        if (isset($salesData['foragent'][$value->agentsalesperson->id]['count'])) {
                            $salesData['foragent'][$value->agentsalesperson->id]['count'] += 1;
                            $salesData['foragent'][$value->agentsalesperson->id]['register_kw'] += $value->register_kw;
                        } else {
                            $salesData['foragent'][$value->agentsalesperson->id]['id'] = $value->agentsalesperson->id;
                            $salesData['foragent'][$value->agentsalesperson->id]['count'] = 1;
                            $salesData['foragent'][$value->agentsalesperson->id]['register_kw'] = $value->register_kw;
                        }

                    endforeach;
                }

                if (isset($salesData['foragent']) && count($salesData['foragent']) > 2) {
                    $count = array_column($salesData['foragent'], 'count');
                    $register_kw = array_column($salesData['foragent'], 'register_kw');
                    array_multisort($count, SORT_DESC, $register_kw, SORT_DESC, $salesData['foragent']);
                }

                // start todayFollowUp
                $query = LeadMaster::whereDate('reminder_date', '<=', Carbon::today())
                    ->where('lead_status_id', '!=','8')->where('lead_status_id', '!=','9');

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
                    $query->whereRaw('(agent_sales_person_id IN(' . implode(',', $agentIds) . ') OR assign_id IN(' . implode(',', $assignIds) . '))');
                } elseif ($company->user_type == 'S' ) {
                    $agent = AgentSalesPerson::where('user_id', Auth::id())->first();
                    $id = $agent->id;
                    $query->whereRaw('((agent_sales_person_id = ' . $id . ' OR assign_id = ' . $id . ') OR (assign_id = ' . $company->id . '))');
                }


                $todayFollowUp = $query->count();
                // end todayFollowUp
                // start sitevisit
                $querys = LeadMaster::where('lead_status_id', '4');
                $totalLeadsQuery = LeadMaster::where('mobile', '!=','');


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
                    $querys->whereRaw('(agent_sales_person_id IN(' . implode(',', $agentIds) . ') OR assign_id IN(' . implode(',', $assignIds) . '))');
                    $totalLeadsQuery->whereRaw('(agent_sales_person_id IN(' . implode(',', $agentIds) . ') OR assign_id IN(' . implode(',', $assignIds) . '))');
                } elseif ($company->user_type == 'S' ) {
                    $agent = AgentSalesPerson::where('user_id', Auth::id())->first();
                    $id = $agent->id;
                    $querys->whereRaw('((agent_sales_person_id = ' . $id . ' OR assign_id = ' . $id . ') OR (assign_id = ' . $company->id . '))');
                    $totalLeadsQuery->whereRaw('((agent_sales_person_id = ' . $id . ' OR assign_id = ' . $id . ') OR (assign_id = ' . $company->id . '))');
                }



                $sitevisit = $querys->count();
                $totalLeads = $totalLeadsQuery->count();

                $totalwonLeads = $totalLeadsQuery->where('lead_status_id',8)->count();


                // end sitevisit

                if ($user->roles[0]->name == 'Sales') {
                    $salesData['foragent'] = [];
                }

                if (isset($salesData['forPortal']) && count($salesData['forPortal']) > 2) {
                    $count = array_column($salesData['forPortal'], 'count');
                    $register_kw = array_column($salesData['forPortal'], 'register_kw');
                    array_multisort($count, SORT_DESC, $register_kw, SORT_DESC, $salesData['forPortal']);
                }


                // Graph Data Fetching
                $months = [];
                for ($i = 11; $i >= 0; $i--) {
                    $months[] = now()->subMonths($i)->format('Y-m');
                }


               $convertedLeadsQuery =  SalesQuatation::whereNotNull('lead_master_id')->distinct('lead_master_id');

                 if ($company->user_type == 'M') {
                        $agent = AgentSalesPerson::where('user_id', Auth::id())->first();
                        $agentIds = [$agent->id];
                        $sales = CompanyProfile::select('company_profiles.id', 'company_profiles.user_id', 'agent_sales_people.id as agent_id')
                            ->leftJoin('agent_sales_people', 'agent_sales_people.user_id', 'company_profiles.user_id')
                            ->where('company_profiles.manager_id', $company->id)->get();
                        if ($sales->count() > 0) {
                            foreach ($sales as $k => $v):
                                array_push($agentIds, $v->agent_id);
                            endforeach;
                        }
                        $convertedLeadsQuery->whereIn('agent_sales_person_id', $agentIds);
                    }
                    if ($company->user_type == 'S') {
                        $agent = AgentSalesPerson::where('user_id', Auth::id())->first();
                        $id = $agent->id;
                        $convertedLeadsQuery->where('agent_sales_person_id', $id);
                    }

                $convertedLeads = $convertedLeadsQuery->count('lead_master_id');
                $conversionRate = $totalLeads > 0 ? round(($convertedLeads / $totalLeads) * 100, 2) : 0;

                $totalQuotations = $convertedLeadsQuery->count();
                $smQuery = SalesMaster::whereNotNull('sales_quatation_id')->distinct('sales_quatation_id');

                if ($company->user_type == 'M') {
                        $agent = AgentSalesPerson::where('user_id', Auth::id())->first();
                        $agentIds = [$agent->id];
                        $sales = CompanyProfile::select('company_profiles.id', 'company_profiles.user_id', 'agent_sales_people.id as agent_id')
                            ->leftJoin('agent_sales_people', 'agent_sales_people.user_id', 'company_profiles.user_id')
                            ->where('company_profiles.manager_id', $company->id)->get();
                        if ($sales->count() > 0) {
                            foreach ($sales as $k => $v):
                                array_push($agentIds, $v->agent_id);
                            endforeach;
                        }
                        $smQuery->whereIn('agent_sales_person_id', $agentIds);
                    }
                    if ($company->user_type == 'S') {
                        $agent = AgentSalesPerson::where('user_id', Auth::id())->first();
                        $id = $agent->id;
                        $smQuery->where('agent_sales_person_id', $id);
                    }


                $convertedQuotations = $smQuery->count('sales_quatation_id');
                $conversionRate2 = $totalQuotations > 0 ? round(($convertedQuotations / $totalQuotations) * 100, 2) : 0;

                $graph1Data = [
                    'series' => [
                        ['name' => 'Count', 'data' => [$totalLeads, $convertedLeads]]
                    ],
                    'categories' => ['Total Leads ('.$totalLeads.')', 'Total Sales Quotations ('.$convertedLeads.')'],
                    'conversionRate' => $conversionRate
                ];

                $graph2Data = [
                    'series' => [
                        ['name' => 'Count', 'data' => [$totalQuotations, $convertedQuotations]]
                    ],
                    'categories' => ['Total Quotations ('.$totalQuotations.')', 'Total Sales Orders ('.$convertedQuotations.')'],
                    'conversionRate' => $conversionRate2
                ];

                return view('home', compact('salesData', 'todayFollowUp', 'sitevisit', 'graph1Data', 'graph2Data', 'agents','totalQuotations','totalwonLeads'));
            } else {

                 $purchase_order = PurchaseOrder::selectRaw("count(id) as total_pr, DATE_FORMAT(purchase_date, '%d %b') as purchase_date")
				    ->where('purchase_date', '>=', now()->subDays(30))
                    ->groupBy("purchase_date")->orderBy("purchase_date")->get();

                $purchase_direct = PurchaseDirect::selectRaw("count(id) as total_pd, DATE_FORMAT(date, '%d %b') as date")
				    ->where('date', '>=', now()->subDays(30))
                    ->groupBy("date")->orderBy("date")->get();

                $delivery_challan = DeliveryChallan::selectRaw("count(id) as total_dc, DATE_FORMAT(challan_date, '%d %b') as challan_date")
				->where('challan_date', '>=', now()->subDays(30))
                    ->groupBy("challan_date")->orderBy("challan_date")->get();

                $delivery_challan_return = DeliveryChallanReturn::selectRaw("count(id) as total_dcr, DATE_FORMAT(challan_date, '%d %b') as challan_date")
				->where('challan_date', '>=', now()->subDays(30))
                    ->groupBy("challan_date")->orderBy("challan_date")->get();


                $all_dates = collect();
                $purchase_order->each(function ($po) use ($all_dates) {
                    $all_dates->push($po->purchase_date);
                });
                $purchase_direct->each(function ($pd) use ($all_dates) {
                    $all_dates->push($pd->date);
                });
                $delivery_challan->each(function ($dc) use ($all_dates) {
                    $all_dates->push($dc->challan_date);
                });
                $delivery_challan_return->each(function ($dcr) use ($all_dates) {
                    $all_dates->push($dcr->challan_date);
                });

                $unique_dates = $all_dates->unique()->sortBy(function ($date) {
                    return \Carbon\Carbon::createFromFormat('d M', $date);
                })->values();

                // Get summary statistics
                $total_purchase_orders = PurchaseOrder::count();
                $total_goods_receipt = PurchaseDirect::count();
                $total_goods_issue = DeliveryChallan::count();
                $total_challan_return = DeliveryChallanReturn::count();

                // Get warehouse stock summary
                $total_stock_items = WarehouseStock::where('quantity', '>', 0)->count();
                $low_stock_items = WarehouseStock::where('quantity', '>', 0)
                    ->where(function ($query) {
                        $query->whereHas('itemGroup', function ($q) {
                            $q->whereColumn('warehouse_stocks.quantity', '<=', 'item_groups.moq_level');
                        })
                        ->orWhereHas('item', function ($q) {
                            $q->whereColumn('warehouse_stocks.quantity', '<=', 'products.moq_level');
                        });
                    })
                    ->count();

                // Low Stock Details
                $lowStockDetails = WarehouseStock::with(['item', 'itemGroup'])
                    ->where('quantity', '>', 0)
                    ->where(function ($query) {
                        $query->whereHas('itemGroup', function ($q) {
                            $q->whereColumn('warehouse_stocks.quantity', '<=', 'item_groups.moq_level');
                        })
                        ->orWhereHas('item', function ($q) {
                            $q->whereColumn('warehouse_stocks.quantity', '<=', 'products.moq_level');
                        });
                    })
                    ->orderBy('quantity', 'asc')
                    ->get();


                $data['data_count'] = [
                    [
                        "name" => 'Purchase Order',
                        "data" => []
                    ],
                    [
                        "name" => 'Goods Receipt',
                        "data" => []
                    ],
                    [
                        "name" => 'Goods Issue',
                        "data" => []
                    ],
                    [
                        "name" => 'Challan Return',
                        "data" => []
                    ]
                ];

                foreach ($unique_dates as $date) {
                    // For Purchase Orders
                    $po_count = $purchase_order->firstWhere('purchase_date', $date);
                    $data['data_count'][0]['data'][] = $po_count ? $po_count->total_pr : 0;

                    // For Purchase Direct
                    $pd_count = $purchase_direct->firstWhere('date', $date);
                    $data['data_count'][1]['data'][] = $pd_count ? $pd_count->total_pd : 0;

                    // For Delivery Challans
                    $dc_count = $delivery_challan->firstWhere('challan_date', $date);
                    $data['data_count'][2]['data'][] = $dc_count ? $dc_count->total_dc : 0;

                    // For Delivery Challan Returns
                    $dcr_count = $delivery_challan_return->firstWhere('challan_date', $date);
                    $data['data_count'][3]['data'][] = $dcr_count ? $dcr_count->total_dcr : 0;
                }

                $data['date'] = $unique_dates->toArray();
                $data['summary'] = [
                    'total_purchase_orders' => $total_purchase_orders,
                    'total_goods_receipt' => $total_goods_receipt,
                    'total_goods_issue' => $total_goods_issue,
                    'total_challan_return' => $total_challan_return,
                    'total_stock_items' => $total_stock_items,
                    'low_stock_items' => $low_stock_items
                ];
                return view('home', compact('data', 'lowStockDetails'));
            }
        }
    }

    public function language(Request $request)
    {
        App::setLocale($request->lang);
        session()->put('locale', $request->lang);
        return redirect()->back();
    }

    public function softChange(Request $request)
    {
        session()->put('soft', $request->soft);
        if ($request->soft == "crm") {
            session()->forget('year');
        } else {
            $years = Year::select('id', 'name')->where('is_default', '1')->first();
            if (!is_null($years)) {
                session()->put('year', $years->name);
            }
        }
        return redirect('home');
    }

    public function yearChange(Request $request)
    {
        session()->put('year', $request->year);
        return redirect()->back();
    }

    public function user()
    {
        if (request()->ajax()) {
            return DataTables::of(User::where('id', '!=', Auth::id())->whereHas(
                'roles',
                function ($q) {
                    $q->where('name', 'Admin');
                }
            )->orderBy('id', 'DESC'))
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    // if (Gate::allows('user-delete')) {
                    $html = '<div class="d-flex">';
                    if ($row->status == '1') {
                        $html .= ' ';
                    } else {
                        $html .= '<a data-id="' . $row->id . '" href="javascript:void(0);"  class="delete  avatar bg-light-danger p-50 m-0 text-danger mx-1" data-bs-toggle="tooltip" data-placement="left" title="Delete"><i class="fa fa-trash"></i></a>';
                    }
                    $html .= '</div>';
                    return $html;
                    // }
                })
                ->addColumn('status', function ($row) {
                    // if (Gate::allows('user-status')) {
                    $html = '<div class="">';
                    $active1 =  $active2  = $active3 = '';
                    if ($row->status == 0) {
                        $btn = "btn-outline-danger";
                        $title = "Reject";
                        $active1 = 'active bg-danger';
                    }
                    if ($row->status == 1) {
                        $btn = "btn-outline-success";
                        $title = "Approve";
                        $active2 = 'active bg-success';
                    }
                    if ($row->status == 2) {
                        $btn = "btn-outline-warning";
                        $title = "Block";
                        $active3 = 'active bg-warning';
                    }
                    $html .= '<div class="btn-group p-0">
                                    <button type="button" class="btn-sm btn ' . $btn . '">' . $title . '</button>
                                    <button type="button" class="btn-sm btn ' . $btn . ' dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown"   aria-expanded="false">
                                    <span class="visually-hidden">Toggle Dropdown</span>
                                    </button>
                                    <div class="dropdown-menu" container="body">
                                    <a class="dropdown-item status ' . $active1 . '" href="javascript:void(0);" data-id="' . $row->id . '" data-value="0">Reject</a>
                                    <a class="dropdown-item status ' . $active2 . '" href="javascript:void(0);" data-id="' . $row->id . '" data-value="1">Approve</a>
                                    <a class="dropdown-item status ' . $active3 . '" href="javascript:void(0);" data-id="' . $row->id . '" data-value="2">Block</a>
                                    </div>
                                </div>';
                    return $html;
                    // }
                })
                ->rawColumns(['action', 'status'])
                ->make(true);
        } else {
            return view('user.view_user');
        }
    }

    public function userStatus(Request $request)
    {
        $user = User::where('id', $request->id)->update(['status' => $request->status]);
        $response = ['status' => true, 'message' => ' Status updated successfully.'];
        return response()->json($response);
    }

    public function userDelete($id)
    {
        try {
            if ($id != 113 && $id != 1 && $id != 114 && $id != 115) {
                User::find($id)->delete();
                $response = ['status' => true, 'message' => 'User deleted successfully.'];
            } else {
                $response = ['status' => false, 'message' => 'This is SELF LEAD account you can`t delete.'];
            }
            return response()->json($response);
        } catch (\Exception $e) {
            $response = ['status' => false, 'server_error' => 'Something went wrong. Please try again.'];
            return response()->json($response);
        }
    }

    public function changePassword()
    {
        return view('change_password');
    }

    public function updatePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required',
            'password' => 'required_with:confirm_password|min:8|regex:/[a-z]/|regex:/[A-Z]/|regex:/[0-9]/|regex:/[@$!%*#?&]/',
            'confirm_password' => 'same:password'
        ], [
            'password.min' => 'The password must have minimum 8 characters',
            'password.regex' => 'at least 1 uppercase letter, 1 lowercase letter, 1 special character and 1 number',
        ]);
        if ($validator->fails()) {
            $response = ['status' => false, 'message' => 'Please Input Proper Data !!', 'errors' => $validator->errors()];
            return response()->json($response);
        }

        DB::beginTransaction();
        try {
            $user = User::where('id', Auth::id())->first();
            if (!is_null($user) && Hash::check($request->current_password, $user->password)) {
                $user->password = Hash::make($request->password);
                $user->save();
                DB::commit();
                $response = ['status' => true, 'message' => ' Your password has been updated.'];
                return response()->json($response);
            } else {
                DB::rollback();
                $response = ['status' => false, 'message' => ' Current password does not match.', 'label' => 'false'];
                return response()->json($response);
            }
        } catch (\Exception $e) {
            DB::rollback();
            $response = ['status' => false, 'server_error' => 'Something went wrong. Please try again.'];
            return response()->json($response);
        }
    }
}
