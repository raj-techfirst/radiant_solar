<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SalesMaster;
use App\Models\CommissionPayment;
use App\Models\UserCommission;
use App\Models\AgentSalesPerson;
use App\Models\User;
use App\Models\CompanyProfile;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\CommissionListExport;
use App\Exports\CommissionDetailsExport;
use App\Exports\CommissionFilesExport;

class CommissionListController extends Controller
{

 function __construct()
    {
        $this->middleware('permission:commission-list', ['only' => ['index', 'store']]);
        $this->middleware('permission:commission-list', ['only' => ['create', 'store']]);
        $this->middleware('permission:commission-list', ['only' => ['edit', 'store']]);
        $this->middleware('permission:commission-list', ['only' => ['destroy']]);
    }


    public function index()
    {
        if (request()->ajax()) {
            $from = request('from_date') ?? null;
            $to = request('to_date') ?? null;
            $agentFilter = request('agent_sales_person_id') ?? null;
            $fromDate = $from ? date('Y-m-d', strtotime($from)) : null;
            $toDate = $to ? date('Y-m-d', strtotime($to)) : null;
            $companyRows = User::select('users.*','agent_sales_people.id as agent_id')
                ->join('agent_sales_people', 'agent_sales_people.user_id', '=', 'users.id');

            if (!empty($agentFilter)) {
                $companyRows->where('agent_sales_people.id', $agentFilter);
            }

            return DataTables::of($companyRows)
                ->addIndexColumn()
                ->addColumn('agent_name', function ($row) use($from,$to,$fromDate,$toDate){
                    $row->stats = getCommissionData($row->agent_id,$row->id,$fromDate,$toDate);
                    $name = $row->name.' '.$row->last_name;
                    $url = route('commission-list.show', $row->agent_id);
                    if (!empty($from) || !empty($to)) {
                        $qs = [];
                        if (!empty($from)) { $qs[] = 'from_date='.urlencode($from); }
                        if (!empty($to)) { $qs[] = 'to_date='.urlencode($to); }
                        $url .= '?'.implode('&', $qs);
                    }
                    if ($row->stats['no_of_file'] > 0) {
                        return '<a href="'.$url.'" class="text-warning fw-bold">'.$name.'</a>';
                    } else {
                        return $name;
                    }
                })
                ->addColumn('no_of_file', function ($row) {
                    return $row->stats['no_of_file'] ?? 0;
                })
                ->addColumn('kw', function ($row) {
                    return $row->stats['kw'] ?? 0;
                })
                ->addColumn('commission', function ($row) {
                    return $row->stats['commission'] ?? 0;
                })
                ->addColumn('sub_commission', function ($row) {
                    return $row->stats['sub_commission'] ?? 0;
                })
                ->addColumn('installation', function ($row) {
                    return $row->stats['installation'] ?? 0;
                })
                ->addColumn('total_payable', function ($row) {
                    return $row->stats['total_payable'] ?? 0;
                })
                ->addColumn('total_paid', function ($row) {
                    return $row->stats['total_paid'] ?? 0;
                })
                ->addColumn('pending_payout', function ($row) {
                    return $row->stats['pending_payout'] ?? 0;
                })
                ->addColumn('customer_payment_pending', function ($row) {
                    return $row->stats['customer_payment_pending'] ?? 0;
                })
                ->addColumn('action', function ($row) {
                    $html = '';
                    return $html;
                })
                ->escapeColumns([])
                ->make(true);
        } else {
            $agents = AgentSalesPerson::select(
                'agent_sales_people.id',
                'agent_sales_people.user_id',
                'users.name',
                'users.last_name'
            )
            ->join('users', 'users.id', '=', 'agent_sales_people.user_id')
            ->where('users.deleted_at', null)
           ->orderBy('users.name')

            ->get();
            return view('admin.commission-list.index', compact('agents'));
        }
    }

    public function show($agentId)
    {
        $from = request('from_date') ?? null;
        $to = request('to_date') ?? null;
        $fromDate = $from ? date('Y-m-d', strtotime($from)) : null;
        $toDate = $to ? date('Y-m-d', strtotime($to)) : null;

        $agent = AgentSalesPerson::with('user')->findOrFail($agentId);
        $stats = getCommissionData($agent->id, $agent->user_id, $fromDate, $toDate, true);

        return view('admin.commission-list.show', compact('agent','stats','from','to'));
    }

    public function files($agentId)
    {
        $from = request('from_date') ?? null;
        $to = request('to_date') ?? null;
        $fromDate = $from ? date('Y-m-d', strtotime($from)) : null;
        $toDate = $to ? date('Y-m-d', strtotime($to)) : null;

        $agent = AgentSalesPerson::with('user')->findOrFail($agentId);
        $stats = getCommissionData($agent->id, $agent->user_id, $fromDate, $toDate, true);
        $saleIds = $stats['counted_sale_ids'] ?? [];

        $selectedCompanyId = CompanyProfile::where('user_id', $agent->user_id)->value('id');

        $sales = SalesMaster::with(['agentsalesperson:id,name'])
            ->when(!empty($saleIds), function($q) use ($saleIds){ $q->whereIn('sales_masters.id', $saleIds); })
            ->leftJoin('agent_sales_people', 'agent_sales_people.id', '=', 'sales_masters.agent_sales_person_id')
            ->select(['sales_masters.id','sales_masters.agent_sales_person_id','sales_masters.invoice_date','sales_masters.register_kw','sales_masters.consumer_name','sales_masters.consumer_number','sales_masters.pending_amonut','sales_masters.commission_amount','sales_masters.sub_commission_amount','sales_masters.installation_amount','sales_masters.installation_asian_person','sales_masters.installation_done','agent_sales_people.user_id as agent_user_id'])
            ->orderBy('sales_masters.invoice_date')
            ->get();

        return view('admin.commission-list.files', compact('agent','sales','from','to','selectedCompanyId'));
    }

    public function downloadPdf($agentId)
    {
        $type = request('type') ?? 'summary';
        $from = request('from_date') ?? null;
        $to = request('to_date') ?? null;
        $fromDate = $from ? date('Y-m-d 00:00:00', strtotime($from)) : null;
        $toDate = $to ? date('Y-m-d 23:59:59', strtotime($to)) : null;

        $agent = AgentSalesPerson::with('user')->findOrFail($agentId);
        $stats = getCommissionData($agent->id, $agent->user_id, $fromDate, $toDate, true);
        $saleIds = $stats['counted_sale_ids'] ?? [];

        $selectedCompanyId = CompanyProfile::where('user_id', $agent->user_id)->value('id');

        $sales = SalesMaster::with(['agentsalesperson:id,name'])
            ->when(!empty($saleIds), function($q) use ($saleIds){ $q->whereIn('sales_masters.id', $saleIds); })
            ->leftJoin('agent_sales_people', 'agent_sales_people.id', '=', 'sales_masters.agent_sales_person_id')
            ->select(['sales_masters.id','sales_masters.agent_sales_person_id','sales_masters.invoice_date','sales_masters.register_kw','sales_masters.consumer_name','sales_masters.consumer_number','sales_masters.pending_amonut','sales_masters.commission_amount','sales_masters.sub_commission_amount','sales_masters.installation_amount','sales_masters.installation_asian_person','sales_masters.installation_done','agent_sales_people.user_id as agent_user_id'])
            ->orderBy('sales_masters.invoice_date')
            ->get();

        $agentName = $agent->user->name . ' ' . $agent->user->last_name;
        $agentName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $agentName);

        $filename = 'Commission_Report_' . $agentName;
        if ($from && $to) {
            $filename .= '_' . date('Y-m-d', strtotime($from)) . '_to_' . date('Y-m-d', strtotime($to));
        } elseif ($from) {
            $filename .= '_from_' . date('Y-m-d', strtotime($from));
        } elseif ($to) {
            $filename .= '_till_' . date('Y-m-d', strtotime($to));
        }
        $filename .= '.pdf';

        $pdf = Pdf::loadView('admin.commission-list.pdf', compact('agent', 'stats', 'from', 'to', 'sales', 'selectedCompanyId','type'));
        if($type == 'files'){
            $pdf->setPaper('A4', 'landscape');
        }

        return $pdf->download($filename);
    }

    public function downloadFilesExcel($agentId)
    {
        $from = request('from_date') ?? null;
        $to = request('to_date') ?? null;
        $fromDate = $from ? date('Y-m-d', strtotime($from)) : null;
        $toDate = $to ? date('Y-m-d', strtotime($to)) : null;

        $agent = AgentSalesPerson::with('user')->findOrFail($agentId);
        $stats = getCommissionData($agent->id, $agent->user_id, $fromDate, $toDate, true);
        $saleIds = $stats['counted_sale_ids'] ?? [];

        $selectedCompanyId = CompanyProfile::where('user_id', $agent->user_id)->value('id');

        $sales = SalesMaster::with(['agentsalesperson:id,name'])
            ->when(!empty($saleIds), function($q) use ($saleIds){ $q->whereIn('sales_masters.id', $saleIds); })
            ->leftJoin('agent_sales_people', 'agent_sales_people.id', '=', 'sales_masters.agent_sales_person_id')
            ->select(['sales_masters.id','sales_masters.agent_sales_person_id','sales_masters.invoice_date','sales_masters.register_kw','sales_masters.consumer_name','sales_masters.consumer_number','sales_masters.pending_amonut','sales_masters.commission_amount','sales_masters.sub_commission_amount','sales_masters.installation_amount','sales_masters.installation_asian_person','sales_masters.installation_done','agent_sales_people.user_id as agent_user_id'])
            ->orderBy('sales_masters.invoice_date')
            ->get();

        $agentName = $agent->user->name . ' ' . $agent->user->last_name;
        $agentName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $agentName);

        $filename = 'Commission_Files_' . $agentName;
        if ($from && $to) {
            $filename .= '_' . date('Y-m-d', strtotime($from)) . '_to_' . date('Y-m-d', strtotime($to));
        } elseif ($from) {
            $filename .= '_from_' . date('Y-m-d', strtotime($from));
        } elseif ($to) {
            $filename .= '_till_' . date('Y-m-d', strtotime($to));
        }
        $filename .= '.xlsx';

        return Excel::download(new CommissionFilesExport($agent, $sales, $selectedCompanyId, $from, $to), $filename);
    }

    public function downloadExcel()
    {
        $from = request('from_date') ?? null;
        $to = request('to_date') ?? null;
        $agentFilter = request('agent_sales_person_id') ?? null;

        $filename = 'Commission_List';
        if ($from && $to) {
            $filename .= '_' . date('Y-m-d', strtotime($from)) . '_to_' . date('Y-m-d', strtotime($to));
        } elseif ($from) {
            $filename .= '_from_' . date('Y-m-d', strtotime($from));
        } elseif ($to) {
            $filename .= '_till_' . date('Y-m-d', strtotime($to));
        }
        $filename .= '.xlsx';

        return Excel::download(new CommissionListExport($from, $to, $agentFilter), $filename);
    }

    public function downloadAgentExcel($agentId)
    {
        $from = request('from_date') ?? null;
        $to = request('to_date') ?? null;
        $fromDate = $from ? date('Y-m-d', strtotime($from)) : null;
        $toDate = $to ? date('Y-m-d', strtotime($to)) : null;

        $agent = AgentSalesPerson::with('user')->findOrFail($agentId);
        $stats = getCommissionData($agent->id, $agent->user_id, $fromDate, $toDate, true);

        $agentName = $agent->user->name . ' ' . $agent->user->last_name;
        $agentName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $agentName);

        $filename = 'Commission_Details_' . $agentName;
        if ($from && $to) {
            $filename .= '_' . date('Y-m-d', strtotime($from)) . '_to_' . date('Y-m-d', strtotime($to));
        } elseif ($from) {
            $filename .= '_from_' . date('Y-m-d', strtotime($from));
        } elseif ($to) {
            $filename .= '_till_' . date('Y-m-d', strtotime($to));
        }
        $filename .= '.xlsx';

        return Excel::download(new CommissionDetailsExport($agent, $stats, $from, $to), $filename);
    }
}


