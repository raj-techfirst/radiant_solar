<?php

namespace App\Exports;

use App\Models\AgentSalesPerson;
use App\Models\CompanyProfile;
use App\Models\PaymetCollection;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMapping;

class PaymentExport implements FromCollection, WithHeadings, WithMapping
{

    private $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $paymets =  PaymetCollection::select('id', 'sales_master_id', 'payment_type', 'amount', 'payment_date', 'cheque_number', 'bank_name', 'branch_name', 'utr_number', 'upi_id', 'remark', 'status')->with('salesMaster', 'salesMaster.agentsalesperson');
        
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
            $paymets->whereHas('salesMaster', function ($query) use ($agentIds) {
                $query->whereIn('agent_sales_person_id', $agentIds);
            });
        }
        if ($company->user_type == 'S') {
            $agent = AgentSalesPerson::where('user_id', Auth::id())->first();
            $id = $agent->id;

            $paymets->whereHas('salesMaster', function ($query) use ($id) {
                $query->where('agent_sales_person_id', $id);
            });
        }
        if ($this->request->input('from_date') != "" && $this->request->input('to_date') == '') {
            $paymets->where('payment_date', '>=', date('Y-m-d 00:00:00', strtotime($this->request->input('from_date'))));
            $paymets->where('payment_date', '<=', date('Y-m-d 23:59:59'));
        }
        if ($this->request->input('from_date') != "" && $this->request->input('to_date') != '') {
            $paymets->where('payment_date', '>=', date('Y-m-d 00:00:00', strtotime($this->request->input('from_date'))));
            $paymets->where('payment_date', '<=', date('Y-m-d 23:59:59', strtotime($this->request->input('to_date'))));
        }
        if ($this->request->input('from_date') == "" && $this->request->input('to_date') != '') {
            $paymets->where('payment_date', '<=', date('Y-m-d 23:59:59', strtotime($this->request->input('to_date'))));
        }
        if ($this->request->input('consumer') != "") {
            $paymets->where(function ($q) {
                $consumer = $this->request->input('consumer');
                $q->where('consumer_name', 'like', '%' . $consumer . '%')
                    ->orWhere('consumer_number', 'like', '%' . $consumer . '%')
                    ->orWhere('contact_number', 'like', '%' . $consumer . '%');
            });
        }
        if ($this->request->input('payment_type') != "") {
            $paymets->where('payment_type', $this->request->input('payment_type'));
        }
        if ($this->request->input('status') != "") {
            $paymets->where('status', $this->request->input('status'));
        }
        $data = $paymets->orderBy('id', 'DESC')->get();
        foreach ($data as $key => $value) {
            $value->id = $key + 1;
            $value->consumer_number = $value->salesMaster->consumer_number;
            $value->consumer_name = $value->salesMaster->consumer_name;
            $value->contact_number = $value->salesMaster->contact_number;
            $value->consumer_type = $value->salesMaster->consumer_type;
            if (!is_null($value->payment_date)) {
                $value->payment_date = date('d-m-Y', strtotime($value->payment_date));
            } else {
                $value->payment_date = "";
            }
            $value->cheque_number_utr_upi = $value->cheque_number . '' . $value->utr_number . '' . $value->upi_id;
            $value->agentsalesperson_name = $value->salesMaster->agentsalesperson->name;
            $value->status = getPaymentStatus($value->status)['status'];
            unset($value->utr_number, $value->upi_id, $value->sales_master_id, $value->salesMaster);
        }
        return $data;
    }
    public function headings(): array
    {
        return [
            'Sr no',
            'Consumer Number',
            'Consumer Name',
            'Contact Number',
            'Consumer Type',
            'Payment Type',
            'Amount',
            'Payment Date',
            'Cheque Number /UTR Number /UPI ID',
            'Bank Name',
            'Branch Name',
            'Agent Sales Person',
            'Remark',
            'Approval Status'
        ];
    }

    public function map($row): array
    {
        return [
            $row->id,
            (string) $row->consumer_number,
            $row->consumer_name,
            $row->contact_number,
            $row->consumer_type,
            $row->payment_type,
            number_format($row->amount, 2),
            $row->payment_date,
            $row->cheque_number_utr_upi,
            $row->bank_name,
            $row->branch_name,
            $row->agentsalesperson_name,
            $row->remark,
            $row->status
        ];
    }
}
