<?php

namespace App\Exports;

use App\Models\AgentSalesPerson;
use App\Models\CompanyProfile;
use App\Models\SalesMaster;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class BankLoanExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    /** Loan status columns on sales_masters that may be used in the status filters. */
    public const STATUS_COLUMNS = [
        'apply_for_loan' => 'Applied For Loan',
        'loan_sension' => 'Loan Sanction',
        'disbursement' => 'Disbursement',
    ];

    private $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    /**
     * Base query shared by the Bank Loan report screen and this Excel export.
     */
    public static function query($request)
    {
        $query = SalesMaster::query()
            ->with('district.state', 'taluka', 'loandata', 'agentsalesperson')
            ->withSum('loandisbursements as disbursed_amount', 'amount')
            ->where('file_type', 'L')
            ->where('file_cancel_order', '0');

        $company = CompanyProfile::where('user_id', Auth::id())->first();
        if ($company->user_type == 'M') {
            $agent = AgentSalesPerson::where('user_id', Auth::id())->first();
            $agentIds = [$agent->id];
            $sales = CompanyProfile::select('company_profiles.id', 'company_profiles.user_id', 'agent_sales_people.id as agent_id')
                ->leftJoin('agent_sales_people', 'agent_sales_people.user_id', 'company_profiles.user_id')
                ->where('company_profiles.manager_id', $company->id)->get();
            foreach ($sales as $v) {
                $agentIds[] = $v->agent_id;
            }
            if ($request->input('agent_sales_person_id') == '') {
                $query->whereIn('agent_sales_person_id', $agentIds);
            }
        }
        if ($company->user_type == 'S') {
            $agent = AgentSalesPerson::where('user_id', Auth::id())->first();
            $query->where('agent_sales_person_id', $agent->id);
        }

        // Date filter on the loan application submitted date
        $from = $request->input('from_date');
        $to = $request->input('to_date');
        if ($from != '' || $to != '') {
            $query->whereHas('loandata', function ($q) use ($from, $to) {
                if ($from != '') {
                    $q->where('submitted_at', '>=', date('Y-m-d', strtotime($from)));
                }
                $q->where('submitted_at', '<=', date('Y-m-d', $to != '' ? strtotime($to) : time()));
            });
        }

        if ($request->input('consumer') != '') {
            $consumer = $request->input('consumer');
            $query->where(function ($q) use ($consumer) {
                $q->where('consumer_name', 'like', '%' . $consumer . '%')
                    ->orWhere('consumer_number', 'like', '%' . $consumer . '%')
                    ->orWhere('contact_number', 'like', '%' . $consumer . '%');
            });
        }
        if ($request->input('agent_sales_person_id') != '') {
            $query->where('agent_sales_person_id', $request->input('agent_sales_person_id'));
        }
        if (array_key_exists((string) $request->input('status'), self::STATUS_COLUMNS)) {
            $query->where($request->input('status'), '1');
        }
        if (array_key_exists((string) $request->input('not_status'), self::STATUS_COLUMNS)) {
            $query->where($request->input('not_status'), '0');
        }

        return $query->orderBy('id', 'DESC');
    }

    public static function formatDate($date)
    {
        return (!empty($date) && !in_array($date, ['1970-01-01', '0000-00-00'])) ? date('d-m-Y', strtotime($date)) : '';
    }

    public static function approvedAmount($row)
    {
        return (float) ($row->loandata->approved_amount ?? 0);
    }

    public static function pendingAmount($row)
    {
        return self::approvedAmount($row) - (float) ($row->disbursed_amount ?? 0);
    }

    public function collection()
    {
        return self::query($this->request)->get();
    }

    public function headings(): array
    {
        return [
            'Consumer No',
            'Consumer Name',
            'Register KW',
            'Consumer Type',
            'Contact No.',
            'State',
            'District',
            'Taluka',
            'Loan Portal',
            'Application No',
            'Application Submitted At',
            'Approved Amount',
            'Loan Sanction Date',
            'Disbursement Amount',
            'Pending Amount',
        ];
    }

    public function map($row): array
    {
        return [
            $row->consumer_number,
            $row->consumer_name,
            $row->register_kw,
            $row->consumer_type,
            $row->contact_number,
            $row->district->state->state_name ?? '',
            $row->district->name ?? '',
            $row->taluka->name ?? '',
            $row->loandata->loan_portal ?? '',
            $row->loandata->application_no ?? '',
            self::formatDate($row->loandata->submitted_at ?? null),
            self::approvedAmount($row),
            self::formatDate($row->loandata->loan_sanction_date ?? null),
            (float) ($row->disbursed_amount ?? 0),
            self::pendingAmount($row),
        ];
    }
}
