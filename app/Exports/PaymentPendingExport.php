<?php

namespace App\Exports;

use App\Models\AgentSalesPerson;
use App\Models\CompanyProfile;
use App\Models\SalesMaster;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMapping;

class PaymentPendingExport implements FromCollection, WithHeadings, WithMapping
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
        $query = SalesMaster::select('*','id as sid')->with('district.state', 'taluka', 'village', 'subDivision', 'salesquatationfull','paymetCollection');
        $query->where('file_cancel_order', '0');
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
            if($this->request->input('agent_sales_person_id') == "") {
                $query->whereIn('agent_sales_person_id', $agentIds);
            }
        }
        if ($company->user_type == 'S') {
            $agent = AgentSalesPerson::where('user_id', Auth::id())->first();
            $id = $agent->id;
            $query->where('agent_sales_person_id', $id);
        }
        $query->where('installation_pending', "1");
        $query->where('payment_receveid', "0");
        if ($this->request->input('from_date') != "" && $this->request->input('to_date') == '') {
            $query->where('master_create_date', '>=', date('Y-m-d 00:00:00', strtotime($this->request->input('from_date'))));
            $query->where('master_create_date', '<=', date('Y-m-d 23:59:59'));
        }
        if ($this->request->input('from_date') != "" && $this->request->input('to_date') != '') {
            $query->where('master_create_date', '>=', date('Y-m-d 00:00:00', strtotime($this->request->input('from_date'))));
            $query->where('master_create_date', '<=', date('Y-m-d 23:59:59', strtotime($this->request->input('to_date'))));
        }
        if ($this->request->input('from_date') == "" && $this->request->input('to_date') != '') {
            $query->where('master_create_date', '<=', date('Y-m-d 23:59:59', strtotime($this->request->input('to_date'))));
        }
        if ($this->request->input('consumer') != "") {
            $query->where(function ($q) {
                $consumer = $this->request->input('consumer');
                $q->where('consumer_name', 'like', '%' . $consumer . '%')
                  ->orWhere('consumer_number', 'like', '%' . $consumer . '%')
                  ->orWhere('contact_number', 'like', '%' . $consumer . '%');
            });
        }
        if ($this->request->input('agent_sales_person_id') != "") {
            $query->where('agent_sales_person_id', $this->request->input('agent_sales_person_id'));
        }
        if ($this->request->input('status') != "") {
            $query->where($this->request->input('status'), "1");
        }
        if ($this->request->input('not_status') != "") {
            $query->where($this->request->input('not_status'), "0");
        }
        $data = $query->orderBy('id', 'DESC')->get();
        foreach ($data as $key => $value) {
            $value->id = $key + 1;
            $value->agentsalesperson_name = $value->agentsalesperson->name;
            $value->state_name = $value->district->state->state_name ?? null;
            $value->district_name = $value->district->name ?? null;
            $value->taluka_name = $value->taluka->name ?? null;
            $value->status = toGetSalesMasterLastStatus($value->sid);
            $totalamount = ($value->salesquatationfull->total_amount != null) ? $value->salesquatationfull->total_amount : 0;
            $meter_charges = ($value->salesquatationfull->meter_charges != null) ? $value->salesquatationfull->meter_charges : 0;
            $registration_fee = ($value->salesquatationfull->registration_fee != null) ? $value->salesquatationfull->registration_fee : 0;
            $other_charge_amount = ($value->salesquatationfull->other_charge_amount != null) ? $value->salesquatationfull->other_charge_amount : 0;
            if($value->salesquatationfull->form_type == 'resident') {
                $value->system_cost =  $value->salesquatationfull->total_system_cost;
            }
            else 
            {
                $value->system_cost =  $value->salesquatationfull->total_amount - $meter_charges - $registration_fee - $other_charge_amount;
            }
            $value->meter_charges = number_format($meter_charges, 2);
            $value->registration_fee = number_format($registration_fee, 2);
            $other_charge_amount = ($value->salesquatationfull->other_charge_amount != null) ? $value->salesquatationfull->other_charge_amount : 0;
            $value->other_charge_amount =  number_format($other_charge_amount, 2);
            $total_amount = ($value->total_amount != null) ? $value->total_amount : 0;
            $pending_amonut =  ($value->pending_amonut != null) ? $value->pending_amonut : 0;
            $value->pending_amonut = $pending_amonut;
            $total_amount = is_numeric($value->total_amount) ? (double) $value->total_amount : 0;
            $pending_amonut = is_numeric($value->pending_amonut) ? (double) $value->pending_amonut : 0;
            $value->received_amonut =  number_format(($total_amount - $pending_amonut), 2);
            $totalamount = is_numeric($totalamount) ? (double) $totalamount : 0;
            $meter_charges = is_numeric($meter_charges) ? (double) $meter_charges : 0;
            $registration_fee = is_numeric($registration_fee) ? (double) $registration_fee : 0;
            $other_charge_amount = is_numeric($other_charge_amount) ? (double) $other_charge_amount : 0;
            $value->totalamt = number_format(($totalamount), 2);
            $subsidy =  ($value->salesquatationfull->subsidy != null) ? $value->salesquatationfull->subsidy : 0;
            $value->subsidy = number_format($subsidy, 2);
            $subsidy = is_numeric($subsidy) ? (double) $subsidy : 0;
            $value->net_customer_price = number_format(($totalamount - $subsidy), 2);

            $value->discount = getDiscount($value->sid);
            $pending_amt = 0;
            if (!empty($value->paymetCollection)) {
                foreach ($value->paymetCollection as $item) {
                    if ($item->status == 0) {
                        $pending_amt += (float) $item->amount;
                    } else {
                        $pending_amt = 0;
                    }
                }
            }
            $value['pending_amt'] = $pending_amt;

        }
        return $data;
    }
    public function headings(): array
    {
        return [
            'Consumer Number',
            'Consumer Name',
            'Consumer Type',
            'Registation Kw',
            'System Cost',
            'Meter Charges',
            'Registration Fee',
            'Other Charges',
            'Total Amount',
            'Total Payment Received',
            'Customer Payabale',
            'Subsidy',
            'Net Customer Price',
            'Pending Amount',
            'Discount',
            'Agent Sales Person',
            'Application Status',
            'Invoice No',
            'Date'
        ];
    }
    public function map($row): array
    {
        return [
            $row->consumer_number,
            $row->consumer_name,
            $row->consumer_type,
            $row->register_kw,
            number_format($row->system_cost,2),
            $row->meter_charges,
            $row->registration_fee,
            $row->other_charge_amount,
            $row->totalamt,
            $row->received_amonut,
            number_format($row->pending_amonut,2),
            $row->subsidy,
            $row->net_customer_price,
            $row->pending_amt,
            $row->discount,
            $row->agentsalesperson_name,
            $row->status,
            $row->invoice_no,
            $row->installation_date
        ];
    }
}
