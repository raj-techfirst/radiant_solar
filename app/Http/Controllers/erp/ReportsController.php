<?php

namespace App\Http\Controllers\erp;

use App\Exports\B2BAcceptExport;
use App\Exports\B2BDispatchExport;
use App\Exports\B2BDispachExport;
use App\Exports\B2BRateExport;
use App\Exports\ProjectWiseDispachExport;
use App\Exports\ProjectWiseStockExport;
use App\Exports\RequisitionExport;
use App\Exports\SalesAgentWiseExport;
use App\Exports\StockExport;
use App\Http\Controllers\Controller;
use App\Models\AgentSalesPerson;
use App\Models\Category;
use App\Models\CompanyProfile;
use App\Models\erp\SerialNumber;
use App\Models\erp\Warehouse;
use App\Models\InstallationInvater;
use App\Models\InstallationPenal;
use App\Models\ItemGroup;
use App\Models\LeadMaster;
use App\Models\SalesMaster;
use App\Models\SalesQuatation;
use App\Models\SerialNumberLog;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class ReportsController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:get-serial-numbers', ['only' => ['getSerialNumbers']]);
        $this->middleware('permission:project-wise-stock-report', ['only' => ['projectWiseStockReport']]);
        $this->middleware('permission:project-wise-dispach', ['only' => ['projectWiseDispach']]);
        $this->middleware('permission:required-stock-report', ['only' => ['requiredStock', 'getRequiredStock']]);
        $this->middleware('permission:stock-report', ['only' => ['requiredStock', 'stockReport']]);
        $this->middleware('permission:b2b-dispach', ['only' => ['bbDispach']]);
        $this->middleware('permission:b2b-accept', ['only' => ['bbAccept']]);
        $this->middleware('permission:b2b-dispatch', ['only' => ['bbDispatch']]);
        $this->middleware('permission:b2b-rate', ['only' => ['bbRate']]);
        $this->middleware('permission:sales-agent-wise-report', ['only' => ['salesAgentWise']]);
    }
    public function index()
    {
        return view('erp.reports.index');
    }
    public function getSerialNumbers()
    {
        if (request()->ajax()) {
            return DataTables::of(SerialNumber::with('warehouse', 'itemGroup')->orderBy('id', 'DESC'))
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
                        case 'transfer':
                            $status = '<span class="badge  bg-light-info">Transfer</span>';
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

    public function projectWiseDispachOld()
    {
        if (request()->ajax()) {
            $where = "pws.quantity != 0 AND sm.file_cancel_order = '0' AND sm.deleted_at IS NULL AND dc.deleted_at IS NULL AND pws.deleted_at IS NULL";
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

    public function projectWiseDispach()
    {

        if (request()->ajax()) {
            $where = "pws.quantity != 0 AND sm.file_cancel_order = '0' AND sm.deleted_at IS NULL AND dc.deleted_at IS NULL AND pws.deleted_at IS NULL";

             if (request()->input('consumer') != "") {
                $consumer = request()->input('consumer');
                $where .= " AND (sm.consumer_name like '%" . $consumer . "%' OR sm.consumer_number like '%" . $consumer . "%' OR sm.contact_number like '%" . $consumer . "%')";
            }
            if (request()->input('agent_sales_person_id') != "") {
                $where .= " AND sm.agent_sales_person_id = " . request()->input('agent_sales_person_id');
            }
            if (request()->input('status') != "") {
                $where .= " AND sm." . request()->input('status') . " = '1'";
            }
            if (request()->input('not_status') != "") {
                $where .= " AND sm." . request()->input('not_status') . " = '0'";
            }

            $query = "SELECT
                dc.id AS challan_id, sm.consumer_number, sm.consumer_name,sm.total_amount as system_cost,sm.id as new_sales_master_id,
                sm.contact_number AS consumer_mobile, sm.register_kw,
                pws.type, pws.item_id, i.item_code, i.name, pws.item_group_id,
                pws.unit_id, pws.issue_type, pws.quantity,  ign.group_type,
                ign.panel_company_id, pc.name as penal_name, ign.panel_type_id, pt.name as penal_type_name,ign.p_type, ign.panel_watt_id,
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
                WHERE " . $where.' GROUP BY sm.id';


            $where1 = " pwsh.installation_id > 0 AND pwsh.type = 'Debit' AND sm.file_cancel_order = '0' AND sm.deleted_at IS NULL AND dc.deleted_at IS NULL AND pws.deleted_at IS NULL AND pwsh.deleted_at IS NULL";

             if (request()->input('consumer') != "") {
                $consumer = request()->input('consumer');
                $where1 .= " AND (sm.consumer_name like '%" . $consumer . "%' OR sm.consumer_number like '%" . $consumer . "%' OR sm.contact_number like '%" . $consumer . "%')";
            }
            if (request()->input('agent_sales_person_id') != "") {
                $where1 .= " AND sm.agent_sales_person_id = " . request()->input('agent_sales_person_id');
            }
            if (request()->input('status') != "") {
                $where1 .= " AND sm." . request()->input('status') . " = '1'";
            }
            if (request()->input('not_status') != "") {
                $where1 .= " AND sm." . request()->input('not_status') . " = '0'";
            }


            $query1 = "SELECT
                dc.id AS challan_id, sm.consumer_number, sm.consumer_name,sm.total_amount as system_cost,sm.id as new_sales_master_id,
                sm.contact_number AS consumer_mobile, sm.register_kw,
                pws.type, pws.item_id, i.item_code, i.name, pws.item_group_id,
                pws.unit_id, pws.issue_type, pwsh.quantity as use_quantity,
                ign.group_type,
                ign.panel_company_id, pc.name as penal_name, ign.panel_type_id, pt.name as penal_type_name,ign.p_type, ign.panel_watt_id,
                pw.name as panel_watt_name, ic.name as inverter_name, ign.inveter_kw, ign.inverter_type,
                (dcm.rate * pwsh.quantity) AS taxable_amount,
                CASE
                    WHEN pws.item_id != 0 THEN (dcm.rate * pwsh.quantity * i.gst_rate) / 100
                    WHEN pws.item_id = 0 THEN (dcm.rate * pwsh.quantity * ig.gst_rate) / 100
                    ELSE 0
                END AS gst_amount,
                ((dcm.rate * pwsh.quantity) +
                    (CASE
                        WHEN pws.item_id != 0 THEN (dcm.rate * pwsh.quantity * i.gst_rate) / 100
                        WHEN pws.item_id = 0 THEN (dcm.rate * pwsh.quantity * ig.gst_rate) / 100
                        ELSE 0
                    END)) AS total_amount
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
                WHERE " . $where1 . "
				GROUP BY pws.item_id, pws.item_group_id,sm.id;";


            $dataOne = DB::select(DB::raw($query));
            $dataTwo = DB::select(DB::raw($query1));

            $allData = [];
            if (count($dataOne) > 0) {
                foreach ($dataOne as $oneKey => $oneValue):


                    if (isset($allData[$oneValue->new_sales_master_id])) {
                        $allData[$oneValue->new_sales_master_id]->taxable_amount += $oneValue->taxable_amount;
                        $allData[$oneValue->new_sales_master_id]->gst_amount += $oneValue->gst_amount;
                        $allData[$oneValue->new_sales_master_id]->total_amount += $oneValue->total_amount;
                    } else {
                        $oneValue->taxable_amount = $oneValue->taxable_amount;
                        $oneValue->gst_amount = $oneValue->gst_amount;
                        $oneValue->total_amount = $oneValue->total_amount;

                        $allData[$oneValue->new_sales_master_id] = $oneValue;
                    }

                endforeach;
            }

            if (count($dataTwo) > 0) {
                foreach ($dataTwo as $twoKey => $twoValue):

                    if (isset($allData[$twoValue->new_sales_master_id])) {
                        $allData[$twoValue->new_sales_master_id]->taxable_amount += $twoValue->taxable_amount;
                        $allData[$twoValue->new_sales_master_id]->gst_amount += $twoValue->gst_amount;
                        $allData[$twoValue->new_sales_master_id]->total_amount += $twoValue->total_amount;
                    } else {
                    $twoValue->taxable_amount = $twoValue->taxable_amount;
                    $twoValue->gst_amount = $twoValue->gst_amount;
                    $twoValue->total_amount = $twoValue->total_amount;
                    $allData[$twoValue->new_sales_master_id] = $twoValue;
                }

                endforeach;
            }


            if (request()->input('download') == "pdf") {
                $data = $allData;
                $pdf = Pdf::loadView('erp.reports.project-wise-dispach-rander', compact('data'))->setPaper('A4', 'landscape');
                return $pdf->download('project-wise-dispach-report.pdf');
            } else if (request()->input('download') == "excel") {
                $data = $allData;
                return Excel::download(new ProjectWiseDispachExport($data), 'dispach-report.xlsx');
            } else {

                return DataTables::of($allData)
                    ->addIndexColumn()
                    ->editColumn('taxable_amount', function ($row) {
                        return number_format($row->taxable_amount, 2);
                    })
                    ->editColumn('gst_amount', function ($row) {
                        return number_format($row->gst_amount, 2);
                    })
                    ->editColumn('total_amount', function ($row) {
                        return number_format($row->total_amount, 2);
                    })
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
            $where = "pws.quantity != 0 AND sm.file_cancel_order = '0' AND sm.deleted_at IS NULL AND dc.deleted_at IS NULL AND pws.deleted_at IS NULL";
            if (request()->input('consumer') != "") {
                $where .= " AND sm.id = " . request()->input('consumer');
            }
            $query = "SELECT
                dc.id AS challan_id, sm.consumer_number, sm.consumer_name,
                sm.contact_number AS consumer_mobile, sm.register_kw,
                pws.type, pws.item_id, i.item_code, i.name, pws.item_group_id,
                pws.unit_id, pws.issue_type, pws.quantity,  ign.group_type,
                ign.panel_company_id, pc.name as penal_name, ign.panel_type_id, pt.name as penal_type_name,ign.p_type, ign.panel_watt_id,
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


            $where1 = " pwsh.installation_id > 0 AND pwsh.type = 'Debit' AND sm.file_cancel_order = '0' AND sm.deleted_at IS NULL AND dc.deleted_at IS NULL AND pws.deleted_at IS NULL AND pwsh.deleted_at IS NULL";
            if (request()->input('consumer') != "") {
                $where1 .= " AND pwsh.sales_master_id = " . request()->input('consumer');
            }

            $query1 = "SELECT
                dc.id AS challan_id, sm.consumer_number, sm.consumer_name,
                sm.contact_number AS consumer_mobile, sm.register_kw,
                pws.type, pws.item_id, i.item_code, i.name, pws.item_group_id,
                pws.unit_id, pws.issue_type, pwsh.quantity as use_quantity,
                ign.group_type,
                ign.panel_company_id, pc.name as penal_name, ign.panel_type_id, pt.name as penal_type_name,ign.p_type, ign.panel_watt_id,
                pw.name as panel_watt_name, ic.name as inverter_name, ign.inveter_kw, ign.inverter_type,
                (dcm.rate * pwsh.quantity) AS taxable_amount,
                CASE
                    WHEN pws.item_id != 0 THEN (dcm.rate * pwsh.quantity * i.gst_rate) / 100
                    WHEN pws.item_id = 0 THEN (dcm.rate * pwsh.quantity * ig.gst_rate) / 100
                    ELSE 0
                END AS gst_amount,
                ((dcm.rate * pwsh.quantity) +
                    (CASE
                        WHEN pws.item_id != 0 THEN (dcm.rate * pwsh.quantity * i.gst_rate) / 100
                        WHEN pws.item_id = 0 THEN (dcm.rate * pwsh.quantity * ig.gst_rate) / 100
                        ELSE 0
                    END)) AS total_amount
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
                WHERE " . $where1 . "
				GROUP BY pws.item_id, pws.item_group_id;";


            $dataOne = DB::select(DB::raw($query));
            $dataTwo = DB::select(DB::raw($query1));

            $allData = [];
            if (count($dataOne) > 0) {
                foreach ($dataOne as $oneKey => $oneValue):

                    $oneValue->use_quantity = 0;
                    $oneValue->total_qty = $oneValue->quantity;
                    $allData[$oneValue->item_id . '_' . $oneValue->item_group_id] = $oneValue;

                    if ($oneValue->item_id != 0) {
                        $allData[$oneValue->item_id . '_' . $oneValue->item_group_id]->item_dis_name = $oneValue->item_code . ' ' . $oneValue->name;
                    } else {
                        if ($oneValue->group_type == "panel") {
                            $allData[$oneValue->item_id . '_' . $oneValue->item_group_id]->item_dis_name =  $oneValue->panel_watt_name . 'W Solar Module (' . $oneValue->penal_name . ' - ' . $oneValue->penal_type_name . ' | ' . $oneValue->p_type . ')';
                        } else {
                            $allData[$oneValue->item_id . '_' . $oneValue->item_group_id]->item_dis_name = $oneValue->inveter_kw . ' KW Inverter (' . $oneValue->inverter_name  . ' | ' . $oneValue->inverter_type . ')';
                        }
                    }
                endforeach;
            }

            if (count($dataTwo) > 0) {
                foreach ($dataTwo as $twoKey => $twoValue):



                    if (isset($allData[$twoValue->item_id . '_' . $twoValue->item_group_id])) {
                        $allData[$twoValue->item_id . '_' . $twoValue->item_group_id]->total_qty = $allData[$twoValue->item_id . '_' . $twoValue->item_group_id]->quantity + $twoValue->use_quantity;
                        $allData[$twoValue->item_id . '_' . $twoValue->item_group_id]->use_quantity = $twoValue->use_quantity;

                        $allData[$twoValue->item_id . '_' . $twoValue->item_group_id]->taxable_amount += $twoValue->taxable_amount;
                        $allData[$twoValue->item_id . '_' . $twoValue->item_group_id]->gst_amount += $twoValue->gst_amount;
                        $allData[$twoValue->item_id . '_' . $twoValue->item_group_id]->total_amount += $twoValue->total_amount;


                        if ($twoValue->item_id != 0) {
                            $allData[$twoValue->item_id . '_' . $twoValue->item_group_id]->item_dis_name = $twoValue->item_code . ' ' . $twoValue->name;
                        } else {
                            if ($twoValue->group_type == "panel") {
                                $allData[$twoValue->item_id . '_' . $twoValue->item_group_id]->item_dis_name =  $twoValue->panel_watt_name . 'W Solar Module (' . $twoValue->penal_name . ' - ' . $twoValue->penal_type_name . ' | ' . $twoValue->p_type . ')';
                            } else {
                                $allData[$twoValue->item_id . '_' . $twoValue->item_group_id]->item_dis_name = $twoValue->inveter_kw . ' KW Inverter (' . $twoValue->inverter_name  . ' | ' . $twoValue->inverter_type . ')';
                            }
                        }
                    } else {
                        $twoValue->quantity = 0;
                        $twoValue->total_qty = $twoValue->use_quantity;
                        $allData[$twoValue->item_id . '_' . $twoValue->item_group_id] = $twoValue;

                        if ($twoValue->item_id != 0) {
                            $allData[$twoValue->item_id . '_' . $twoValue->item_group_id]->item_dis_name = $twoValue->item_code . ' ' . $twoValue->name;
                        } else {
                            if ($twoValue->group_type == "panel") {
                                $allData[$twoValue->item_id . '_' . $twoValue->item_group_id]->item_dis_name =  $twoValue->panel_watt_name . 'W Solar Module (' . $twoValue->penal_name . ' - ' . $twoValue->penal_type_name . ' | ' . $twoValue->p_type . ')';
                            } else {
                                $allData[$twoValue->item_id . '_' . $twoValue->item_group_id]->item_dis_name = $twoValue->inveter_kw . ' KW Inverter (' . $twoValue->inverter_name  . ' | ' . $twoValue->inverter_type . ')';
                            }
                        }
                    }
                endforeach;
            }

            if (request()->input('type') == "pdf") {
                $data = $allData;
                $pdf = Pdf::loadView('erp.reports.project-wise-stock-rander', compact('data'))->setPaper('A4', 'landscape');
                return $pdf->download('project-wise-stock-report.pdf');
            } else if (request()->input('type') == "excel") {
                $data = $allData;
                return Excel::download(new ProjectWiseStockExport($data), 'project_wise_stock.xlsx');
            } else {

                return DataTables::of($allData)
                    ->addIndexColumn()
                    ->editColumn('taxable_amount', function ($row) {
                        return number_format($row->taxable_amount, 2);
                    })
                    ->editColumn('gst_amount', function ($row) {
                        return number_format($row->gst_amount, 2);
                    })
                    ->editColumn('total_amount', function ($row) {
                        return number_format($row->total_amount, 2);
                    })
                    ->addColumn('item_dis_name', function ($row) {
                        return $row->item_dis_name;
                    })
                    ->escapeColumns([])
                    ->make(true);
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
                $union[] =  " SELECT bm.type,bm.item_id,bm.item_group_id,bm.quantity,bm.unit_id FROM sales_masters as s LEFT JOIN bom_metas AS bm ON bm.boms_id = s.bom_id WHERE s.id = " . $value->id . " AND s.deleted_at IS NULL AND bm.deleted_at IS NULL";
            }
            $final = implode(' UNION ALL ', $union);

            $query = "SELECT
                   cr.item_id,cr.type,c.category_name,p.item_code,p.name AS item_name,ig.group_type,ig.p_type,pc.name AS penal_company,pw.name AS penal_watt,pt.name AS penal_type,
                ic.name AS invarter_name,ig.inveter_kw,ig.inverter_type,SUM(cr.quantity) AS require_qty,COALESCE(ws.warehouse_stock_qty, 0) AS current_stock,
                COALESCE(pwss.installer_stock_qty,0) AS installer_stock,
                (COALESCE(ws.warehouse_stock_qty, 0) + COALESCE(pwss.installer_stock_qty,0)) as total_current_stock,
                CASE WHEN(SUM(cr.quantity) - COALESCE(ws.warehouse_stock_qty, 0) - COALESCE(pwss.installer_stock_qty,0)) < 1 THEN '0' ELSE CAST((SUM(cr.quantity) - COALESCE(ws.warehouse_stock_qty, 0) - COALESCE(pwss.installer_stock_qty,0)) AS CHAR) END AS sort_stock,u.unit_name
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

                LEFT JOIN(SELECT pws.item_id,pws.item_group_id,SUM(pws.quantity) AS installer_stock_qty FROM project_wise_stocks AS pws WHERE pws.issue_type = 'installer'
                GROUP BY pws.item_id,pws.item_group_id) AS pwss ON
                ((cr.item_id != 0 AND pwss.item_id = cr.item_id) OR (cr.item_group_id != 0 AND pwss.item_group_id = cr.item_group_id))

                GROUP BY cr.type,cr.item_id,cr.item_group_id
                ORDER BY c.id ASC, ig.group_type ASC";

            $data = DB::select(DB::raw($query));

            $type = '';
            if (request()->input('type') == "pdf") {
                $type = 'pdf';
                $pdf = Pdf::loadView('erp.reports.requisition-rander', compact('data', 'type'));
                return $pdf->download('requisition-report.pdf');
            } else if (request()->input('type') == "excel") {

                $data = DB::select(DB::raw($query));

                if (count($data) > 0) {
                    foreach ($data as $key => $value) {
                        if ($value->type == "Item") {
                            $value->item_dis_name = $value->item_code . ' ' . $value->item_name;
                        } else if ($value->group_type == "panel") {
                            $value->item_dis_name =  $value->penal_watt . 'W Solar Module (' . $value->penal_company . ' - ' . $value->penal_type . ' | ' . $value->p_type . ')';
                        } else {
                            $value->item_dis_name =  $value->inveter_kw . ' KW Inverter (' . $value->invarter_name  . ' | ' . $value->inverter_type . ')';
                        }
                        $data[$key] = $value;
                    }
                }
                return Excel::download(new RequisitionExport($data), 'requisition-report.xlsx');
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
            $query = "SELECT ws.id as ws_ids,p.category_id,w.id as w_id,w.name as warehouse_name,ws.type,ws.item_id, ws.item_group_id,c.category_name,p.item_code,p.name,ig.group_type,pc.name AS penal_company,pw.name AS penal_watt,pt.name AS penal_type,ic.name AS invarter_name,ig.inveter_kw,ig.inverter_type,ws.warehous_id,ws.type,ws.quantity,u.unit_name,st.price,st.gst_tax,ig.p_type,
                (ws.quantity * st.price) AS total_value,(((ws.quantity * st.price) * st.gst_tax) /100) AS gst_amount,((ws.quantity * st.price) + (((ws.quantity * st.price) * st.gst_tax) /100)) AS total_amount
            FROM warehouse_stocks ws
            JOIN(
                SELECT w1.* FROM warehouse_stock_histories w1
                INNER JOIN(
                    SELECT warehous_stock_id, MAX(created_at) AS latest_entry
                    FROM warehouse_stock_histories
                    WHERE `type` = 'Credit' and price != 0 AND deleted_at IS NULL
                    GROUP BY warehous_stock_id
                ) w2
            ON w1.warehous_stock_id = w2.warehous_stock_id AND w1.created_at = w2.latest_entry
            WHERE w1.type = 'Credit' AND w1.deleted_at IS NULL) st ON ws.id = st.warehous_stock_id
            LEFT JOIN units AS u ON u.id = ws.unit_id
            LEFT JOIN products AS p ON p.id = ws.item_id
            LEFT JOIN item_groups AS ig ON ig.id = ws.item_group_id
            LEFT JOIN penal_companies AS pc ON pc.id = ig.panel_company_id
            LEFT JOIN penal_watts AS pw ON pw.id = ig.panel_watt_id
            LEFT JOIN penal_types AS pt ON pt.id = ig.panel_type_id
            LEFT JOIN inveter_companies AS ic ON ic.id = ig.inveter_company_id
            LEFT JOIN categories AS c ON c.id = p.category_id
            LEFT JOIN warehouses AS w ON w.id = ws.warehous_id";

            $query .= " WHERE ws.quantity > 0 AND ws.deleted_at IS NULL AND u.deleted_at IS NULL AND p.deleted_at IS NULL AND ig.deleted_at IS NULL AND w.deleted_at IS NULL";
            if (request()->input('warehouse_id') != "" && request()->input('warehouse_id') != "0") {
                $query .= " AND  ws.warehous_id = " . request()->input('warehouse_id');
            }
            if (request()->input('category_id') != "") {
                $query .= " AND  c.id = " . request()->input('category_id');
            }
            if (request()->input('type') != "") {
                $query .= " AND  ig.group_type = '" . request()->input('type') . "'";
            }

            $query .= " ORDER BY c.id ASC;";

            if (request()->input('downloadtype') == "pdf") {
                $data = DB::select(DB::raw($query));

                $ndata = [];
                if (count($data) > 0) {
                    foreach ($data as $key => $value) {

                        $cat = ($value->type == "item") ? $value->category_name : $value->group_type;
                        $ndata[$value->w_id][$cat]['name'] = $value->warehouse_name;
                        $ndata[$value->w_id][$cat]['data'][] = $value;
                    }
                }

                $pdf = Pdf::loadView('erp.reports.stock-report-rander', compact('ndata'));
                return $pdf->download('stock-report.pdf');
            } else if (request()->input('downloadtype') == "excel") {
                $data = DB::select(DB::raw($query));


                if (count($data) > 0) {
                    foreach ($data as $key => $row) {

                        if ($row->item_id != 0) {
                            $row->item_dis_name = $row->item_code . ' ' . $row->name;
                        } else {
                            if ($row->group_type == "panel") {
                                $row->item_dis_name = $row->penal_watt . 'W Solar Module (' . $row->penal_company . ' - ' . $row->penal_type . ' | ' . $row->p_type . ')';
                            } else {
                                $row->item_dis_name = $row->inveter_kw . ' KW Inverter (' . $row->invarter_name  . ' | ' . $row->inverter_type . ')';
                            }
                        }
                        $data[$key] = $row;
                    }
                }
                return Excel::download(new StockExport($data), 'stock-report.xlsx');
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
                            return '<a data-id="' . $row->ws_ids . '" href="javascript:void(0);" class="view" data-bs-toggle="tooltip" data-placement="left" title="View">' . $row->item_code . ' ' . $row->name . '</a>';
                        } else {
                            if ($row->group_type == "panel") {
                                return '<a data-id="' . $row->ws_ids . '" href="javascript:void(0);" class="view" data-bs-toggle="tooltip" data-placement="left" title="View">' . $row->penal_watt . 'W Solar Module (' . $row->penal_company . ' - ' . $row->penal_type . ' | ' . $row->p_type . ')</a>';
                            } else {
                                return '<a data-id="' . $row->ws_ids . '" href="javascript:void(0);" class="view" data-bs-toggle="tooltip" data-placement="left" title="View">' . $row->inveter_kw . ' KW Inverter (' . $row->invarter_name  . ' | ' . $row->inverter_type . ')</a>';
                            }
                        }
                    })
                    ->escapeColumns([])
                    ->make(true);
            }
        } else {
            $warehouse = Warehouse::select('id', 'name')->get();
            $categories = Category::get();
            return view('erp.reports.stock-report', compact('warehouse', 'categories'));
        }
    }
    public function trackSerialNumber(Request $request)
    {
        $data = SerialNumber::with('itemGroup', 'itemGroup', 'purchase', 'purchase.supplier')->where('id', $request->id)->first();
        $data2 = SerialNumberLog::with('delivery_challan', 'delivery_challan.salesQuatation')->where('serial_number_id', $request->id)->first();
        $data3 = InstallationPenal::with('salesMaster')->where('serial_no', $data->serial_number)->first();
        $data4 = InstallationInvater::with('salesMaster')->where('serial_no_of_inverter', $data->serial_number)->first();

        $res['html'] = view('erp.reports.track-serial-number', compact('data', 'data2', 'data3', 'data4'))->render();
        return response()->json($res);
    }
    public function bbDispach()
    {
        if (request()->ajax()) {

            $where = "dc.issue_type = 'trading' AND dc.deleted_at IS NULL AND sq.deleted_at IS NULL";
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

    public function bbAccept()
    {
        if (request()->ajax()) {

            $where = "sq.form_type = 'trading' AND sq.current_status = 'accepted' AND sq.deleted_at IS NULL";
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
                    $where .= " AND sq.agent_sales_person_id IN (" . implode(',', $agentIds) . ")";
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
                $where .= " AND sq.agent_sales_person_id = " . request()->input('agent_sales_person_id');
            }

            $query = "SELECT sq.id, sq.name, sq.mobile,
                        DATE_FORMAT(sq.created_at, '%d-%m-%Y') AS quotation_date,
                        asp.name AS agent_name,
                        sq.gst,
                        sq.total_amount AS total_taxable,
                        sqm.nos,
                        sqm.rate,
                        CASE
                            WHEN sqm.type = 'Item' THEN p.name
                            WHEN sqm.type = 'ItemGroup' AND ig.group_type = 'inverter'
                                THEN CONCAT(ig.inveter_kw, ' KW Inverter (', IFNULL(ic.name, 'N/A'), ' | ', IFNULL(ig.inverter_type, 'N/A'), ')')
                            WHEN sqm.type = 'ItemGroup'
                                THEN CONCAT(IFNULL(pw.name, 'N/A'), 'W Solar Module (', IFNULL(pc.name, 'N/A'), ' - ', IFNULL(pt.name, 'N/A'), ' | ', IFNULL(ig.p_type, 'N/A'), ')')
                            ELSE ''
                        END AS item_detail
                    FROM  sales_quatations AS sq
                    LEFT JOIN sales_quatation_metas AS sqm ON sqm.sales_quatation_id = sq.id AND sqm.deleted_at IS NULL
                    LEFT JOIN products AS p ON p.id = sqm.item_id AND sqm.type = 'Item'
                    LEFT JOIN item_groups AS ig ON ig.id = sqm.item_group_id AND sqm.type = 'ItemGroup'
                    LEFT JOIN penal_companies AS pc ON pc.id = ig.panel_company_id
                    LEFT JOIN penal_types AS pt ON pt.id = ig.panel_type_id
                    LEFT JOIN penal_watts AS pw ON pw.id = ig.panel_watt_id
                    LEFT JOIN inveter_companies AS ic ON ic.id = ig.inveter_company_id
                    LEFT JOIN agent_sales_people AS asp ON asp.id = sq.agent_sales_person_id
                    WHERE " . $where . " ORDER BY sq.created_at DESC, sq.id DESC, sqm.id ASC;";

            if (request()->input('download') == "excel") {
                return Excel::download(new B2BAcceptExport($query), 'b2b-accept.xlsx');
            } else {
                $rows = DB::select(DB::raw($query));
                $srNo = 0;
                $prevQuoteId = null;
                foreach ($rows as $row) {
                    if ($row->id !== $prevQuoteId) {
                        $srNo++;
                        $prevQuoteId = $row->id;
                    }
                    $row->sr_no = $srNo;
                }
                return DataTables::of($rows)
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
            return view('erp.reports.b2b-accept', compact('agentSalesPerson'));
        }
    }

    public function bbDispatch()
    {
        if (request()->ajax()) {

            $where = "sq.form_type = 'trading' AND sq.current_status = 'dispatch' AND sq.deleted_at IS NULL";
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
                    $where .= " AND sq.agent_sales_person_id IN (" . implode(',', $agentIds) . ")";
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
                $where .= " AND sq.agent_sales_person_id = " . request()->input('agent_sales_person_id');
            }

            $query = "SELECT sq.id, sq.name, sq.mobile,
                        DATE_FORMAT(sq.created_at, '%d-%m-%Y') AS quotation_date,
                        asp.name AS agent_name,
                        sq.address AS bill_to_address,
                        sq.ship_to,
                        sq.gst,
                        sq.total_amount AS total_taxable,
                        sqm.nos,
                        sqm.rate,
                        CASE
                            WHEN sqm.type = 'Item' THEN p.name
                            WHEN sqm.type = 'ItemGroup' AND ig.group_type = 'inverter'
                                THEN CONCAT(ig.inveter_kw, ' KW Inverter (', IFNULL(ic.name, 'N/A'), ' | ', IFNULL(ig.inverter_type, 'N/A'), ')')
                            WHEN sqm.type = 'ItemGroup'
                                THEN CONCAT(IFNULL(pw.name, 'N/A'), 'W Solar Module (', IFNULL(pc.name, 'N/A'), ' - ', IFNULL(pt.name, 'N/A'), ' | ', IFNULL(ig.p_type, 'N/A'), ')')
                            ELSE ''
                        END AS item_detail
                    FROM  sales_quatations AS sq
                    LEFT JOIN sales_quatation_metas AS sqm ON sqm.sales_quatation_id = sq.id AND sqm.deleted_at IS NULL
                    LEFT JOIN products AS p ON p.id = sqm.item_id AND sqm.type = 'Item'
                    LEFT JOIN item_groups AS ig ON ig.id = sqm.item_group_id AND sqm.type = 'ItemGroup'
                    LEFT JOIN penal_companies AS pc ON pc.id = ig.panel_company_id
                    LEFT JOIN penal_types AS pt ON pt.id = ig.panel_type_id
                    LEFT JOIN penal_watts AS pw ON pw.id = ig.panel_watt_id
                    LEFT JOIN inveter_companies AS ic ON ic.id = ig.inveter_company_id
                    LEFT JOIN agent_sales_people AS asp ON asp.id = sq.agent_sales_person_id
                    WHERE " . $where . " ORDER BY sq.created_at DESC, sq.id DESC, sqm.id ASC;";

            if (request()->input('download') == "excel") {
                return Excel::download(new B2BDispatchExport($query), 'b2b-dispatch.xlsx');
            } else {
                $rows = DB::select(DB::raw($query));
                $srNo = 0;
                $prevQuoteId = null;
                foreach ($rows as $row) {
                    if ($row->id !== $prevQuoteId) {
                        $srNo++;
                        $prevQuoteId = $row->id;
                    }
                    $row->sr_no = $srNo;
                }
                return DataTables::of($rows)
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
            return view('erp.reports.b2b-dispatch', compact('agentSalesPerson'));
        }
    }

    public function bbRate()
    {
        if (request()->ajax()) {

            $where = "lm.is_trading = '1' AND lm.deleted_at IS NULL
                    AND (
                        EXISTS (SELECT 1 FROM sales_quatations AS sq WHERE sq.lead_master_id = lm.id AND sq.deleted_at IS NULL AND sq.current_status = 'active')
                        OR
                        EXISTS (SELECT 1 FROM rate_given_table AS rg2 WHERE rg2.lead_master_id = lm.id AND rg2.deleted_at IS NULL AND rg2.is_hide = 0)
                    )";
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
                    $where .= " AND lm.agent_sales_person_id IN (" . implode(',', $agentIds) . ")";
                }
            }
            if ($company->user_type == 'S') {
                $agent = AgentSalesPerson::where('user_id', Auth::id())->first();
                $id = $agent->id;
                $where .= " AND lm.agent_sales_person_id = " . $id;
            }
            if (request()->input('consumer') != "") {
                $consumer = request()->input('consumer');
                $where .= " AND (lm.name like '%" . $consumer . "%' OR lm.mobile like '%" . $consumer . "%')";
            }
            if (request()->input('agent_sales_person_id') != "") {
                $where .= " AND lm.agent_sales_person_id = " . request()->input('agent_sales_person_id');
            }

            $query = "(SELECT lm.id, lm.name, lm.mobile, lm.kw,
                        DATE_FORMAT(lm.created_at, '%d-%m-%Y') AS lead_date,
                        asp.name AS agent_name,
                        rg.type, rg.nos, rg.rate, rg.item_gst, rg.total_taxable,
                        pw.name AS panel_watt,
                        CASE
                            WHEN rg.type = 'Item' THEN p.name
                            WHEN rg.type = 'ItemGroup' AND ig.group_type = 'inverter'
                                THEN CONCAT(ig.inveter_kw, ' KW Inverter (', IFNULL(ic.name, 'N/A'), ' | ', IFNULL(ig.inverter_type, 'N/A'), ')')
                            WHEN rg.type = 'ItemGroup'
                                THEN CONCAT(IFNULL(pw.name, 'N/A'), 'W Solar Module (', IFNULL(pc.name, 'N/A'), ' - ', IFNULL(pt.name, 'N/A'), ' | ', IFNULL(ig.p_type, 'N/A'), ')')
                            ELSE ''
                        END AS item_detail
                    FROM lead_masters AS lm
                    LEFT JOIN rate_given_table AS rg ON rg.lead_master_id = lm.id AND rg.deleted_at IS NULL AND rg.is_hide = 0
                    LEFT JOIN products AS p ON p.id = rg.item_id AND rg.type = 'Item'
                    LEFT JOIN item_groups AS ig ON ig.id = rg.item_group_id AND rg.type = 'ItemGroup'
                    LEFT JOIN penal_companies AS pc ON pc.id = ig.panel_company_id
                    LEFT JOIN penal_types AS pt ON pt.id = ig.panel_type_id
                    LEFT JOIN penal_watts AS pw ON pw.id = ig.panel_watt_id
                    LEFT JOIN inveter_companies AS ic ON ic.id = ig.inveter_company_id
                    LEFT JOIN agent_sales_people AS asp ON asp.id = lm.agent_sales_person_id
                    WHERE " . $where . "
                    AND rg.id IS NOT NULL
                    AND NOT EXISTS (SELECT 1 FROM sales_quatations AS sq WHERE sq.lead_master_id = lm.id AND sq.deleted_at IS NULL AND sq.current_status = 'active')
                    AND rg.id IN (
                        SELECT MAX(rg3.id) FROM rate_given_table AS rg3
                        WHERE rg3.lead_master_id = lm.id AND rg3.deleted_at IS NULL AND rg3.is_hide = 0
                        GROUP BY COALESCE(rg3.item_id, 0), COALESCE(rg3.item_group_id, 0)
                    ))
                    UNION ALL
                    (SELECT lm.id, lm.name, lm.mobile, lm.kw,
                        DATE_FORMAT(lm.created_at, '%d-%m-%Y') AS lead_date,
                        asp.name AS agent_name,
                        sqm.type, sqm.nos, sqm.rate, sqm.item_gst, (CAST(sqm.nos AS DECIMAL(10,2)) * CAST(sqm.rate AS DECIMAL(10,2))) AS total_taxable,
                        pw.name AS panel_watt,
                        CASE
                            WHEN sqm.type = 'Item' THEN p.name
                            WHEN sqm.type = 'ItemGroup' AND ig.group_type = 'inverter'
                                THEN CONCAT(ig.inveter_kw, ' KW Inverter (', IFNULL(ic.name, 'N/A'), ' | ', IFNULL(ig.inverter_type, 'N/A'), ')')
                            WHEN sqm.type = 'ItemGroup'
                                THEN CONCAT(IFNULL(pw.name, 'N/A'), 'W Solar Module (', IFNULL(pc.name, 'N/A'), ' - ', IFNULL(pt.name, 'N/A'), ' | ', IFNULL(ig.p_type, 'N/A'), ')')
                            ELSE ''
                        END AS item_detail
                    FROM lead_masters AS lm
                    INNER JOIN sales_quatations AS sq ON sq.lead_master_id = lm.id AND sq.deleted_at IS NULL AND sq.current_status = 'active'
                    INNER JOIN sales_quatation_metas AS sqm ON sqm.sales_quatation_id = sq.id AND sqm.deleted_at IS NULL
                    LEFT JOIN products AS p ON p.id = sqm.item_id AND sqm.type = 'Item'
                    LEFT JOIN item_groups AS ig ON ig.id = sqm.item_group_id AND sqm.type = 'ItemGroup'
                    LEFT JOIN penal_companies AS pc ON pc.id = ig.panel_company_id
                    LEFT JOIN penal_types AS pt ON pt.id = ig.panel_type_id
                    LEFT JOIN penal_watts AS pw ON pw.id = ig.panel_watt_id
                    LEFT JOIN inveter_companies AS ic ON ic.id = ig.inveter_company_id
                    LEFT JOIN agent_sales_people AS asp ON asp.id = lm.agent_sales_person_id
                    WHERE " . $where . ")
                    ORDER BY id DESC, lead_date DESC;";

            if (request()->input('download') == "excel") {
                return Excel::download(new B2BRateExport($query), 'b2b-rate.xlsx');
            } else {
                $rows = DB::select(DB::raw($query));
                $srNo = 0;
                $prevLeadId = null;
                foreach ($rows as $row) {
                    if ($row->id !== $prevLeadId) {
                        $srNo++;
                        $prevLeadId = $row->id;
                    }
                    $row->sr_no = $srNo;
                }
                return DataTables::of($rows)
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
            return view('erp.reports.b2b-rate', compact('agentSalesPerson'));
        }
    }

    public function bbReport()
    {
        $company = CompanyProfile::where('user_id', Auth::id())->first();

        $b2bAcceptCount = SalesQuatation::where('form_type', 'trading')->where('current_status', 'accepted')->where('deleted_at', null);
        $b2bDispatchCount = SalesQuatation::where('form_type', 'trading')->where('current_status', 'dispatch')->where('deleted_at', null);
        $b2bRateCount = LeadMaster::where('is_trading', '1')
            ->whereNotExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('sales_quatations')
                    ->whereColumn('sales_quatations.lead_master_id', 'lead_masters.id')
                    ->whereNull('sales_quatations.deleted_at')
                    ->where('sales_quatations.current_status', 'accepted');
            });

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
            $b2bAcceptCount->whereIn('agent_sales_person_id', $agentIds);
            $b2bDispatchCount->whereIn('agent_sales_person_id', $agentIds);
            $b2bRateCount->whereIn('agent_sales_person_id', $agentIds);
        }
        if ($company->user_type == 'S') {
            $agent = AgentSalesPerson::where('user_id', Auth::id())->first();
            $id = $agent->id;
            $b2bAcceptCount->where('agent_sales_person_id', $id);
            $b2bDispatchCount->where('agent_sales_person_id', $id);
            $b2bRateCount->where('agent_sales_person_id', $id);
        }

        $b2bAccept = $b2bAcceptCount->count();
        $b2bDispatch = $b2bDispatchCount->count();
        $b2bRate = $b2bRateCount->count();

        $q = CompanyProfile::select('agent_sales_people.*')->leftJoin('agent_sales_people', 'agent_sales_people.user_id', 'company_profiles.user_id');
        if ($agentWhere != "") {
            $q->whereRaw($agentWhere);
        }
        $agentSalesPerson = $q->get();

        return view('erp.reports.b2b-report', compact('agentSalesPerson', 'b2bAccept', 'b2bDispatch', 'b2bRate'));
    }

    public function salesAgentWise()
    {
        $from = request()->input('from', date('Y-m-01', strtotime('-11 months')));
        $to = request()->input('to', date('Y-m-t'));
        if ($to < $from) {
            $to = $from;
        }

        $company = CompanyProfile::where('user_id', Auth::id())->first();
        $agentIds = [];
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
        }
        if ($company->user_type == 'S') {
            $agent = AgentSalesPerson::where('user_id', Auth::id())->first();
            $agentIds = [$agent->id];
        }

        $months = [];
        $labels = [];
        $start = Carbon::parse($from)->startOfMonth();
        $end = Carbon::parse($to)->startOfMonth();
        while ($start <= $end) {
            $months[] = $start->format('M-y');
            $labels[$start->format('M-y')] = $start->format('Y-m');
            $start->addMonth();
        }

        $where = "dc.issue_type = 'trading' AND dc.deleted_at IS NULL
                    AND sq.current_status = 'dispatch' AND sq.deleted_at IS NULL
                    AND DATE_FORMAT(sq.created_at, '%Y-%m') BETWEEN '" . date('Y-m', strtotime($from)) . "' AND '" . date('Y-m', strtotime($to)) . "'";
        if (count($agentIds) > 0) {
            $where .= " AND sq.agent_sales_person_id IN (" . implode(',', $agentIds) . ")";
        }

        $query = "SELECT sq.agent_sales_person_id, asp.name AS agent_name,
                    DATE_FORMAT(sq.created_at, '%b-%y') AS month_label,
                    DATE_FORMAT(sq.created_at, '%Y-%m') AS month_key,
            ROUND(SUM(
                CASE
                    WHEN dcm.type = 'ItemGroup' AND ig.group_type = 'panel'
                        THEN (CAST(pw.name AS DECIMAL(10,2)) * dcm.quantity) / 1000
                    ELSE 0
                END
            ), 3) AS total_kw
                FROM delivery_challans AS dc
                LEFT JOIN delivery_challan_metas AS dcm ON dcm.delivery_challan_id = dc.id AND dcm.deleted_at IS NULL
                LEFT JOIN item_groups AS ig ON ig.id = dcm.item_group_id AND dcm.type = 'ItemGroup'
                LEFT JOIN penal_watts AS pw ON pw.id = ig.panel_watt_id
                LEFT JOIN sales_quatations AS sq ON sq.id = dc.quotations_id AND sq.deleted_at IS NULL
                LEFT JOIN agent_sales_people AS asp ON asp.id = sq.agent_sales_person_id
                WHERE " . $where . "
                GROUP BY sq.agent_sales_person_id, month_key
                ORDER BY asp.name;";

        $rows = DB::select(DB::raw($query));

        $agents = [];
        $data = [];
        foreach ($rows as $row) {
            if (!isset($agents[$row->agent_sales_person_id])) {
                $agents[$row->agent_sales_person_id] = [
                    'id' => $row->agent_sales_person_id,
                    'name' => $row->agent_name ?: 'Unknown',
                ];
            }
            $data[$row->month_label . '|' . $row->agent_sales_person_id] = (float) $row->total_kw;
        }

        foreach ($agents as $agentId => $agent) {
            $hasAmount = false;
            foreach ($months as $month) {
                if (isset($data[$month . '|' . $agentId]) && $data[$month . '|' . $agentId] > 0) {
                    $hasAmount = true;
                    break;
                }
            }
            if (!$hasAmount) {
                unset($agents[$agentId]);
            }
        }

        $agents = array_values($agents);

        if (request()->input('download') == 'excel') {
            $fileName = 'DCR-Sales-AgentWise-' . date('Y-m', strtotime($from)) . '-to-' . date('Y-m', strtotime($to)) . '.xlsx';
            return Excel::download(new SalesAgentWiseExport($months, $agents, $data, $from, $to), $fileName);
        }

        return view('erp.reports.sales-agent-wise', compact('months', 'agents', 'data', 'labels', 'from', 'to'));
    }
}
