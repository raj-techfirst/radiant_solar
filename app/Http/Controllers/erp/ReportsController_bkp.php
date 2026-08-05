<?php

namespace App\Http\Controllers\erp;

use App\Exports\B2BDispachExport;
use App\Exports\ProjectWiseDispachExport;
use App\Http\Controllers\Controller;
use App\Models\AgentSalesPerson;
use App\Models\CompanyProfile;
use App\Models\erp\SerialNumber;
use App\Models\erp\Warehouse;
use App\Models\ItemGroup;
use App\Models\SalesMaster;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class ReportsController_bkp extends Controller
{
    function __construct()
    {
        $this->middleware('permission:get-serial-numbers', ['only' => ['getSerialNumbers']]);
        $this->middleware('permission:project-wise-stock-report', ['only' => ['projectWiseStockReport']]);
        $this->middleware('permission:project-wise-dispach', ['only' => ['projectWiseDispach']]);
        $this->middleware('permission:required-stock-report', ['only' => ['requiredStock', 'getRequiredStock']]);
        $this->middleware('permission:stock-report', ['only' => ['requiredStock', 'stockReport']]);
        $this->middleware('permission:b2b-dispach', ['only' => ['bbDispach']]);
    }
    public function index()
    {
        return view('erp.reports.index');
    }
    public function getSerialNumbers()
    {
        if (request()->ajax()) {
            return DataTables::of(SerialNumber::with('warehouse', 'itemGroup'))
                ->addIndexColumn()
                ->filter(function ($query) {
                    if (request()->input('from_date') != "" && request()->input('to_date') == '') {
                        $query->where('purchase_date', '>=', date('Y-m-d 00:00:00', strtotime(request()->input('from_date'))));
                        $query->where('purchase_date', '<=', date('Y-m-d 23:59:59'));
                    }
                    if (request()->input('from_date') != "" && request()->input('to_date') != '') {
                        $query->where('purchase_date', '>=', date('Y-m-d 00:00:00', strtotime(request()->input('from_date'))));
                        $query->where('purchase_date', '<=', date('Y-m-d 23:59:59', strtotime(request()->input('to_date'))));
                    }
                    if (request()->input('from_date') == "" && request()->input('to_date') != '') {
                        $query->where('purchase_date', '<=', date('Y-m-d 23:59:59', strtotime(request()->input('to_date'))));
                    }
                    if (request()->input('item_group_id') != "") {
                        $query->where('item_group_id', request()->input('item_group_id'));
                    }
                    if (request()->input('warehouse_id') != "") {
                        $query->where('location_id', request()->input('warehouse_id'));
                    }
                    if (request()->input('serial_number') != "") {
                        $query->where('serial_number', request()->input('serial_number'));
                    }
                })

                ->editColumn('status', function ($row) {
                    switch ($row->status) {
                        case 'available':
                            $status = '<span class="badge  bg-light-success">Available</span>';
                            break;
                        case 'dispatched':
                            $status = '<span class="badge  bg-light-secondary">Dispatched</span>';
                            break;
                        case 'sold':
                            $status = '<span class="badge  bg-light-primary">Sold</span>';
                            break;
                        case 'damaged':
                            $status = '<span class="badge  bg-light-warning">Damaged</span>';
                            break;
                        case 'returned':
                            $status = '<span class="badge  bg-light-danger">Returned</span>';
                            break;
                        default:
                            $status = '<span class="badge  bg-light-warning">' . $row->status . '</span>';
                    }
                    return $status;
                })

                ->addColumn('item_group', function ($row) {
                    return getItemGropName($row->itemGroup);
                })
                ->editColumn('serial_number', function ($row) {
                    return '<a href="javascript:trackNo(' . $row->id . ')">' . $row->serial_number . '</a>';
                })
                ->editColumn('created_at', function ($row) {
                    return date("d-m-Y", strtotime($row->created_at));
                })
                ->editColumn('warranty', function ($row) {
                    if ($row->warranty_start_date != "0000-00-00" && $row->warranty_end_date != "0000-00-00") {
                        return date("d-m-Y", strtotime($row->warranty_start_date)) . ' To ' . date("d-m-Y", strtotime($row->warranty_end_date));
                    }
                    return '-';
                })
                ->editColumn('guarantee', function ($row) {
                    if ($row->guarantee_start_date != "0000-00-00" && $row->guarantee_end_date != "0000-00-00") {
                        return date("d-m-Y", strtotime($row->guarantee_start_date)) . ' To ' . date("d-m-Y", strtotime($row->guarantee_end_date));
                    }
                    return '-';
                })
                ->escapeColumns([])
                ->make(true);
        } else {
            $warehouse = Warehouse::select('id', 'name')->get();
            $itemGroup = ItemGroup::with('panel_company', 'panel_type', 'panel_watt', 'inveter_company', 'unit')->get();
            return view('erp.reports.serial_numbers', compact('warehouse', 'itemGroup'));
        }
    }
    public function projectWiseDispach()
    {
        if (request()->ajax()) {
            $where = "pws.quantity != 0 AND sm.file_cancel_order = '0'";
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
                    $where .= " AND sm.agent_sales_person_id IN (" . $agentIds . ")";
                }
            }
            if ($company->user_type == 'S') {
                $agent = AgentSalesPerson::where('user_id', Auth::id())->first();
                $id = $agent->id;
                $where .= " AND sm.agent_sales_person_id = " . $id;
            }
            if (request()->input('consumer') != "") {
                $consumer = request()->input('consumer');
                $where .= " AND (sm.consumer_name like '%" . $consumer . "%' OR sm.consumer_number like '%" . $consumer . "%' OR sm.contact_number like '%" . $consumer . "%')";
            }
            if (request()->input('agent_sales_person_id') != "") {
                $where .= "AND sm.agent_sales_person_id = " . request()->input('agent_sales_person_id');
            }
            if (request()->input('status') != "") {
                $where .= "AND sm." . request()->input('status') . " = '1'";
            }
            if (request()->input('not_status') != "") {
                $where .= "AND sm." . request()->input('not_status') . " = '0'";
            }
            $query = "SELECT dc.id AS challan_id, sm.consumer_number,sm.consumer_name, sm.contact_number as consumer_mobile, sm.total_amount as system_cost, dc.challan_number AS goods_issue_no,DATE_FORMAT(dc.created_at, '%d-%m-%Y') AS goods_issue_date,
                        SUM(dcm.rate * pws.quantity) AS taxabale_amount,
                        SUM(CASE 
                            WHEN pws.item_id != 0 THEN (dcm.rate * pws.quantity * i.gst_rate) / 100
                            WHEN pws.item_id = 0 THEN (dcm.rate * pws.quantity * ig.gst_rate) / 100
                            ELSE 0
                        END) AS gst_amount,
                        SUM((dcm.rate * pws.quantity) + 
                            (CASE 
                                WHEN pws.item_id != 0 THEN (dcm.rate * pws.quantity * i.gst_rate) / 100
                                WHEN pws.item_id = 0 THEN (dcm.rate * pws.quantity * ig.gst_rate) / 100
                                ELSE 0
                            END)) AS total_amount
                    FROM  project_wise_stocks AS pws
                    JOIN  sales_masters AS sm ON sm.id = pws.sales_master_id
                    JOIN delivery_challans AS dc ON dc.id = pws.delivery_challan_id
                    LEFT JOIN delivery_challan_metas AS dcm 
                        ON (
                            (pws.item_id != 0 AND dcm.delivery_challan_id = dc.id AND dcm.item_id = pws.item_id) OR
                            (pws.item_id = 0 AND dcm.delivery_challan_id = dc.id AND dcm.item_group_id = pws.item_group_id)
                        )
                    LEFT JOIN products AS i ON pws.item_id != 0 AND i.id = pws.item_id 
                    LEFT JOIN item_groups AS ig ON pws.item_group_id != 0 AND ig.id = pws.item_group_id 
                    WHERE " . $where . " GROUP BY dc.id";
            if (request()->input('type') == "pdf") {
                $data = DB::select(DB::raw($query));
                if (request()->input('download') == "pdf") {
                    $pdf = Pdf::loadView('erp.reports.project-wise-dispach-rander', compact('data'))
                        ->setPaper('A4', 'landscape');
                    return $pdf->download('project-wise-dispach-report.pdf');
                } else {
                    return Excel::download(new ProjectWiseDispachExport($query), 'project_wise_dispach.xlsx');
                }
            } else {
                return DataTables::of(DB::select(DB::raw($query)))
                    ->addIndexColumn()
                    ->escapeColumns([])
                    ->make(true);
            }
        } else {
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
            $warehouse = Warehouse::select('id', 'name')->get();
            return view('erp.reports.project-wise-dispach', compact('agentSalesPerson', 'warehouse'));
        }
    }

    public function projectWiseStockReport()
    {
        if (request()->ajax()) {
            $where = "pws.quantity != 0 AND sm.file_cancel_order = '0'";
            if (request()->input('consumer') != "") {
                $where .= " AND sm.id = " . request()->input('consumer');
            }
            $query = "SELECT 
                dc.id AS challan_id, sm.consumer_number, sm.consumer_name,
                sm.contact_number AS consumer_mobile, sm.register_kw,
                pws.type, pws.item_id, i.item_code, i.name, pws.item_group_id,
                pws.unit_id, pws.issue_type, pws.quantity,  ign.group_type,
                ign.panel_company_id, pc.name as penal_name, ign.panel_type_id, pt.name as penal_type_name, ign.panel_watt_id,
                pw.name as panel_watt_name, ic.name as inverter_name, ign.inveter_kw, ign.inverter_type,
                (dcm.rate * pws.quantity) AS taxable_amount, 
                (CASE 
                    WHEN pws.item_id != 0 THEN (dcm.rate * pws.quantity * i.gst_rate) / 100
                    WHEN pws.item_id = 0 THEN (dcm.rate * pws.quantity * ig.gst_rate) / 100
                    ELSE 0
                END) AS gst_amount,
                ((dcm.rate * pws.quantity) + 
                    (CASE 
                        WHEN pws.item_id != 0 THEN (dcm.rate * pws.quantity * i.gst_rate) / 100
                        WHEN pws.item_id = 0 THEN (dcm.rate * pws.quantity * ig.gst_rate) / 100
                        ELSE 0
                    END)) AS total_amount 
                FROM project_wise_stocks AS pws
                JOIN sales_masters AS sm ON sm.id = pws.sales_master_id
                JOIN delivery_challans AS dc ON dc.id = pws.delivery_challan_id
                LEFT JOIN delivery_challan_metas AS dcm 
                    ON (
                        (pws.item_id != 0 AND dcm.delivery_challan_id = dc.id AND dcm.item_id = pws.item_id) OR
                        (pws.item_id = 0 AND dcm.delivery_challan_id = dc.id AND dcm.item_group_id = pws.item_group_id)
                    )
                LEFT JOIN products AS i ON pws.item_id != 0 AND i.id = pws.item_id 
                LEFT JOIN item_groups AS ign ON pws.item_id = 0 AND ign.id = pws.item_group_id 
                LEFT JOIN inveter_companies AS ic ON ic.id = ign.inveter_company_id
                LEFT JOIN penal_companies AS pc ON pc.id = ign.panel_company_id
                LEFT JOIN penal_watts AS pw ON pw.id = ign.panel_watt_id
                LEFT JOIN penal_types AS pt ON pt.id = ign.panel_type_id
                LEFT JOIN item_groups AS ig ON pws.item_group_id != 0 AND ig.id = pws.item_group_id 
                WHERE " . $where;


$where1 = " pwsh.installation_id > 0 AND pwsh.type = 'Debit' AND sm.file_cancel_order = '0'";
if (request()->input('consumer') != "") {
    $where1 .= " AND pwsh.sales_master_id = " . request()->input('consumer');
}

            $query1 = "SELECT 
                dc.id AS challan_id, sm.consumer_number, sm.consumer_name,
                sm.contact_number AS consumer_mobile, sm.register_kw,
                pws.type, pws.item_id, i.item_code, i.name, pws.item_group_id,
                pws.unit_id, pws.issue_type, SUM(pwsh.quantity) as quantity,
                ign.group_type,
                ign.panel_company_id, pc.name as penal_name, ign.panel_type_id, pt.name as penal_type_name, ign.panel_watt_id,
                pw.name as panel_watt_name, ic.name as inverter_name, ign.inveter_kw, ign.inverter_type,
                (dcm.rate * pwsh.quantity) AS taxable_amount, 
 				SUM((dcm.rate * pwsh.quantity)) AS taxable_amount_sum, 
                (CASE 
                    WHEN pws.item_id != 0 THEN (dcm.rate * pwsh.quantity * i.gst_rate) / 100
                    WHEN pws.item_id = 0 THEN (dcm.rate * pwsh.quantity * ig.gst_rate) / 100
                    ELSE 0
                END) AS gst_amount,

SUM(CASE 
                    WHEN pws.item_id != 0 THEN (dcm.rate * pwsh.quantity * i.gst_rate) / 100
                    WHEN pws.item_id = 0 THEN (dcm.rate * pwsh.quantity * ig.gst_rate) / 100
                    ELSE 0
                END) AS gst_amount_sum,

                ((dcm.rate * pwsh.quantity) + 
                    (CASE 
                        WHEN pws.item_id != 0 THEN (dcm.rate * pwsh.quantity * i.gst_rate) / 100
                        WHEN pws.item_id = 0 THEN (dcm.rate * pwsh.quantity * ig.gst_rate) / 100
                        ELSE 0
                    END)) AS total_amount,

 SUM(((dcm.rate * pwsh.quantity) + 
                    (CASE 
                        WHEN pws.item_id != 0 THEN (dcm.rate * pwsh.quantity * i.gst_rate) / 100
                        WHEN pws.item_id = 0 THEN (dcm.rate * pwsh.quantity * ig.gst_rate) / 100
                        ELSE 0
                    END))) AS total_amount  
                FROM project_wise_stock_histories AS pwsh
                JOIN project_wise_stocks AS pws on pwsh.project_wise_stock_id = pws.id
                JOIN sales_masters AS sm ON sm.id = pwsh.sales_master_id
                JOIN delivery_challans AS dc ON dc.id = pws.delivery_challan_id
                LEFT JOIN delivery_challan_metas AS dcm 
                    ON (
                        (pws.item_id != 0 AND dcm.delivery_challan_id = dc.id AND dcm.item_id = pws.item_id) OR
                        (pws.item_id = 0 AND dcm.delivery_challan_id = dc.id AND dcm.item_group_id = pws.item_group_id)
                    )
                LEFT JOIN products AS i ON pws.item_id != 0 AND i.id = pws.item_id 
                LEFT JOIN item_groups AS ign ON pws.item_id = 0 AND ign.id = pws.item_group_id 
                LEFT JOIN inveter_companies AS ic ON ic.id = ign.inveter_company_id
                LEFT JOIN penal_companies AS pc ON pc.id = ign.panel_company_id
                LEFT JOIN penal_watts AS pw ON pw.id = ign.panel_watt_id
                LEFT JOIN penal_types AS pt ON pt.id = ign.panel_type_id
                LEFT JOIN item_groups AS ig ON pws.item_group_id != 0 AND ig.id = pws.item_group_id 
                WHERE ".$where1." 
				GROUP BY pws.item_id, pws.item_group_id;";



            if (request()->input('type') == "pdf") {
                $data = DB::select(DB::raw($query));

                $pdf = Pdf::loadView('erp.reports.project-wise-stock-rander', compact('data'));
                return $pdf->download('project-wise-stock-report.pdf');
            } else {

                $dataOne = DB::select(DB::raw($query));
                $dataTwo = DB::select(DB::raw($query1));

                echo count($dataTwo);
                dd(count($dataOne));

                if(count($dataOne) > 0){
                foreach($dataOne as $oneKey => $oneValue):

                endforeach;
                }

                



                // $allData = []; //array_merge($dataOne, $dataTwo);
                // return DataTables::of($allData)
                //     ->addIndexColumn()
                //     ->addColumn('item_dis_name', function ($row) {
                //         if ($row->item_id != 0) {
                //             return $row->item_code . ' ' . $row->name;
                //         } else {
                //             if ($row->group_type == "panel") {
                //                 return $row->panel_watt_name . 'W Solar Module (' . $row->penal_name . ' - ' . $row->penal_type_name . ')';
                //             } else {
                //                 return $row->inveter_kw . ' KW Inverter (' . $row->inverter_name  . ' | ' . $row->inverter_type . ')';
                //             }
                //         }
                //     })
                //     ->escapeColumns([])
                //     ->make(true);
            }
        } else {
            $query = SalesMaster::where('dispach_pending_list', '1');
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
            $salesMaster = $query->orderBy('id', 'DESC')->get();
            return view('erp.reports.project-wise-stock', compact('salesMaster'));
        }
    }
    public function requiredStock()
    {
        $query = SalesMaster::where('dispach_pending_list', '1');
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
        $salesMaster = $query->orderBy('id', 'DESC')->get();
        return view('erp.reports.requisition-report', compact('salesMaster'));
    }
    public function getRequiredStock()
    {
        $query = SalesMaster::select('*');
        $query->where('file_cancel_order', '0');

        if (request()->input('consumer') != "") {
            $query->where(function ($q) {
                $consumer = request()->input('consumer');
                $q->where('consumer_name', 'like', '%' . $consumer . '%')
                    ->orWhere('consumer_number', 'like', '%' . $consumer . '%')
                    ->orWhere('contact_number', 'like', '%' . $consumer . '%');
            });
        }
        if (request()->input('status') != "") {
            $query->where(request()->input('status'), "1");
        }
        if (request()->input('not_status') != "") {
            $query->where(request()->input('not_status'), "0");
        }
        $query->whereNotNull('bom_id');
        $query->where('bom_id', '!=', 0);
        $data = $query->orderBy('id', 'DESC')->get();
        if ($data->count() > 0) {
            foreach ($data as $key => $value) {
                $union[] =  " SELECT bm.type,bm.item_id,bm.item_group_id,bm.quantity,bm.unit_id FROM sales_masters as s LEFT JOIN bom_metas AS bm ON bm.boms_id = s.bom_id WHERE s.id = " . $value->id;
            }
            $final = implode(' UNION ALL ', $union);
            $query = "SELECT
                    cr.type,c.category_name,p.item_code,p.name as item_name,ig.group_type,pc.name AS penal_company,pw.name AS penal_watt,
                    pt.name AS penal_type,ic.name AS invarter_name,ig.inveter_kw,ig.inverter_type,
                    SUM(cr.quantity) AS require_qty,
                    COALESCE(ws.warehouse_stock_qty, 0) AS current_stock,
                    (SUM(cr.quantity) - COALESCE(ws.warehouse_stock_qty, 0)) AS sort_stock,
                    u.unit_name
                FROM (
                " . $final . "
                ) AS cr
                LEFT JOIN units AS u ON u.id = cr.unit_id
                LEFT JOIN products AS p ON p.id = cr.item_id
                LEFT JOIN item_groups AS ig ON ig.id = cr.item_group_id
                LEFT JOIN penal_companies AS pc ON pc.id = ig.panel_company_id
                LEFT JOIN penal_watts AS pw ON pw.id = ig.panel_watt_id
                LEFT JOIN penal_types AS pt ON pt.id = ig.panel_type_id
                LEFT JOIN inveter_companies AS ic ON ic.id = ig.inveter_company_id
                LEFT JOIN categories as c ON c.id = p.category_id

                LEFT JOIN (
                    SELECT 
                        ws.item_id,
                        ws.item_group_id,
                        SUM(ws.quantity) AS warehouse_stock_qty
                    FROM warehouse_stocks AS ws
                    GROUP BY ws.item_id, ws.item_group_id
                ) AS ws ON (
                    (cr.item_id != 0 AND ws.item_id = cr.item_id) OR
                    (cr.item_group_id != 0 AND ws.item_group_id = cr.item_group_id)
                )
                GROUP BY cr.type,cr.item_id,cr.item_group_id
                ORDER BY c.id ASC, ig.group_type ASC";

            $data = DB::select(DB::raw($query));

            $type = '';
            if (request()->input('type') == "pdf") {
                $type = 'pdf';
                $pdf = Pdf::loadView('erp.reports.requisition-rander', compact('data', 'type'));
                return $pdf->download('requisition-report.pdf');
            } else {
                $res['html'] = view('erp.reports.requisition-rander', compact('data', 'type'))->render();
            }
        } else {

            $res['html'] = 'No Data!';
        }

        return response()->json($res);
    }
    public function stockReport()
    {
        if (request()->ajax()) {
            $query = "SELECT w.id as w_id,w.name as warehouse_name,ws.type,ws.item_id, ws.item_group_id,c.category_name,p.item_code,p.name,ig.group_type,pc.name AS penal_company,pw.name AS penal_watt,pt.name AS penal_type,ic.name AS invarter_name,ig.inveter_kw,ig.inverter_type,ws.warehous_id,ws.type,ws.quantity,u.unit_name,st.price,st.gst_tax,
                (ws.quantity * st.price) AS total_value,(((ws.quantity * st.price) * st.gst_tax) /100) AS gst_amount,((ws.quantity * st.price) + (((ws.quantity * st.price) * st.gst_tax) /100)) AS total_amount
            FROM warehouse_stocks ws
            JOIN(
                SELECT w1.* FROM warehouse_stock_histories w1
                INNER JOIN(
                    SELECT warehous_stock_id, MAX(created_at) AS latest_entry
                    FROM warehouse_stock_histories
                    WHERE `type` = 'Credit'
                    GROUP BY warehous_stock_id
                ) w2
            ON w1.warehous_stock_id = w2.warehous_stock_id AND w1.created_at = w2.latest_entry
            WHERE w1.type = 'Credit') st ON ws.id = st.warehous_stock_id
            LEFT JOIN units AS u ON u.id = ws.unit_id
            LEFT JOIN products AS p ON p.id = ws.item_id
            LEFT JOIN item_groups AS ig ON ig.id = ws.item_group_id
            LEFT JOIN penal_companies AS pc ON pc.id = ig.panel_company_id
            LEFT JOIN penal_watts AS pw ON pw.id = ig.panel_watt_id
            LEFT JOIN penal_types AS pt ON pt.id = ig.panel_type_id
            LEFT JOIN inveter_companies AS ic ON ic.id = ig.inveter_company_id
            LEFT JOIN categories AS c ON c.id = p.category_id
            LEFT JOIN warehouses AS w ON w.id = ws.warehous_id";

            if (request()->input('warehouse_id') != "0") {
                $query .= " WHERE ws.warehous_id = " . request()->input('warehouse_id');
            }

            $query .= "  ORDER BY c.id ASC;";

            if (request()->input('type') == "pdf") {
                $data = DB::select(DB::raw($query));

                $ndata = [];
                if (count($data) > 0) {
                    foreach ($data as $key => $value) {
                        $ndata[$value->w_id]['name'] = $value->warehouse_name;
                        $ndata[$value->w_id]['data'][] = $value;
                    }
                }

                $pdf = Pdf::loadView('erp.reports.stock-report-rander', compact('ndata'));
                return $pdf->download('stock-report.pdf');
            } else {
                return DataTables::of(DB::select(DB::raw($query)))
                    ->addIndexColumn()
                    ->editColumn('category_name', function ($row) {
                        if ($row->item_id != 0) {
                            return $row->category_name;
                        } else {
                            return ucfirst($row->group_type);
                        }
                    })
                    ->editColumn('total_value', function ($row) {
                        return number_format($row->total_value, 2);
                    })
                    ->editColumn('gst_amount', function ($row) {
                        return number_format($row->gst_amount, 2);
                    })
                    ->editColumn('total_amount', function ($row) {
                        return number_format($row->total_amount, 2);
                    })
                    ->addColumn('item_dis_name', function ($row) {
                        if ($row->item_id != 0) {
                            return $row->item_code . ' ' . $row->name;
                        } else {
                            if ($row->group_type == "panel") {
                                return $row->penal_watt . 'W Solar Module (' . $row->penal_company . ' - ' . $row->penal_type . ')';
                            } else {
                                return $row->inveter_kw . ' KW Inverter (' . $row->invarter_name  . ' | ' . $row->inverter_type . ')';
                            }
                        }
                    })
                    ->escapeColumns([])
                    ->make(true);
            }
        } else {
            $warehouse = Warehouse::select('id', 'name')->get();
            return view('erp.reports.stock-report', compact('warehouse'));
        }
    }
    public function trackSerialNumber(Request $request)
    {
        $data = SerialNumber::with('itemGroup', 'itemGroup', 'purchase', 'purchase.supplier')->where('id', $request->id)->first();
        $res['html'] = view('erp.reports.track-serial-number', compact('data'))->render();
        return response()->json($res);
    }


    public function bbDispach()
    {
        if (request()->ajax()) {

            $where = "dc.issue_type = 'trading'";
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
                    $where .= " AND sq.agent_sales_person_id IN (" . $agentIds . ")";
                }
            }
            if ($company->user_type == 'S') {
                $agent = AgentSalesPerson::where('user_id', Auth::id())->first();
                $id = $agent->id;
                $where .= " AND sq.agent_sales_person_id = " . $id;
            }
            if (request()->input('consumer') != "") {
                $consumer = request()->input('consumer');
                $where .= " AND (sq.name like '%" . $consumer . "%' OR sq.mobile like '%" . $consumer . "%')";
            }
            if (request()->input('agent_sales_person_id') != "") {
                $where .= "AND sq.agent_sales_person_id = " . request()->input('agent_sales_person_id');
            }
            if (request()->input('warehouse_id') != "") {
                $where .= "AND dc.warehouse_id = " . request()->input('warehouse_id');
            }

            $query = "SELECT sq.name,sq.mobile,dc.id AS challan_id, dc.challan_number AS goods_issue_no,DATE_FORMAT(dc.created_at, '%d-%m-%Y') AS goods_issue_date,
                        SUM(dcm.rate * dcm.quantity) AS taxabale_amount,
                        SUM(CASE 
                            WHEN dcm.item_id != 0 THEN (dcm.rate * dcm.quantity * i.gst_rate) / 100
                            WHEN dcm.item_id = 0 THEN (dcm.rate * dcm.quantity * ig.gst_rate) / 100
                            ELSE 0
                        END) AS gst_amount,
                        SUM((dcm.rate * dcm.quantity) + 
                            (CASE 
                                WHEN dcm.item_id != 0 THEN (dcm.rate * dcm.quantity * i.gst_rate) / 100
                                WHEN dcm.item_id = 0 THEN (dcm.rate * dcm.quantity * ig.gst_rate) / 100
                                ELSE 0
                            END)) AS total_amount
                    FROM  delivery_challans AS dc
                    LEFT JOIN delivery_challan_metas AS dcm 
                        ON dcm.delivery_challan_id = dc.id
                         LEFT JOIN sales_quatations AS sq 
                        ON dc.quotations_id = sq.id
                    LEFT JOIN products AS i ON dcm.item_id != 0 AND i.id = dcm.item_id 
                    LEFT JOIN item_groups AS ig ON dcm.item_group_id != 0 AND ig.id = dcm.item_group_id 
                    WHERE " . $where . " GROUP BY dc.id;";

            if (request()->input('type') == "pdf") {
                $data = DB::select(DB::raw($query));
                if (request()->input('download') == "pdf") {
                    $pdf = Pdf::loadView('erp.reports.b2b-dispach-rander', compact('data'))
                        ->setPaper('A4', 'landscape');
                    return $pdf->download('b2b-dispach-report.pdf');
                } else {
                    return Excel::download(new B2BDispachExport($query), 'project_wise_dispach.xlsx');
                }
            } else {
                return DataTables::of(DB::select(DB::raw($query)))
                    ->addIndexColumn()
                    ->escapeColumns([])
                    ->make(true);
            }
        } else {
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
            $warehouse = Warehouse::select('id', 'name')->get();
            return view('erp.reports.b2b-dispach', compact('agentSalesPerson', 'warehouse'));
        }
    }
}
