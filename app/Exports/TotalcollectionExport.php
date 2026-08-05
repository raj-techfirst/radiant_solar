<?php

namespace App\Exports;

use App\Models\AgentSalesPerson;
use App\Models\CompanyProfile;
use App\Models\InveterCompany;
use App\Models\PenalCompany;
use App\Models\SalesMaster;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMapping;

class TotalcollectionExport implements FromCollection, WithHeadings, WithMapping
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
        $query = SalesMaster::select('*', 'id as sid')->with('district', 'taluka', 'village', 'subDivision', 'salesquatationfull', 'paymetCollection');
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
            if ($this->request->input('agent_sales_person_id') == "") {
                $query->whereIn('agent_sales_person_id', $agentIds);
            }
        }
        if ($company->user_type == 'S') {
            $agent = AgentSalesPerson::where('user_id', Auth::id())->first();
            $id = $agent->id;
            $query->where('agent_sales_person_id', $id);
        }
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
            $value['state_name'] = !is_null($value->district) ? $value->district->state->state_name : '';
            $value['district_id'] = !is_null($value->district) ? $value->district->name : '';
            $value['taluka_id'] = !is_null($value->taluka) ? $value->taluka->name : '';
            $value['installation_date'] = (!is_null($value->installation_date) && $value->installation_date != '1970-01-01' && $value->installation_date != '0000-00-00') ? date('d-m-Y', strtotime($value->installation_date)) : '';
            $value['meter_installation_date'] = (!is_null($value->meter_installation_date) && $value->meter_installation_date != '1970-01-01' && $value->meter_installation_date != '0000-00-00') ? date('d-m-Y', strtotime($value->meter_installation_date)) : '';
            $value['subsidy_disbursement_date'] = (!is_null($value->subsidy_disbursement_date) && $value->subsidy_disbursement_date != '1970-01-01' && $value->subsidy_disbursement_date != '0000-00-00') ? date('d-m-Y', strtotime($value->subsidy_disbursement_date)) : '';
            $value['subsidy_request_date'] = (!is_null($value->subsidy_request_date) && $value->subsidy_request_date != '1970-01-01' && $value->subsidy_request_date != '0000-00-00') ? date('d-m-Y', strtotime($value->subsidy_request_date)) : '';
            $value->id = $key + 1;
            $totalamount = ($value->salesquatationfull->total_amount != null) ? $value->salesquatationfull->total_amount : 0;
            $meter_charges = ($value->salesquatationfull->meter_charges != null) ? $value->salesquatationfull->meter_charges : 0;
            $registration_fee = ($value->salesquatationfull->registration_fee != null) ? $value->salesquatationfull->registration_fee : 0;
            $other_charge_amount = ($value->salesquatationfull->other_charge_amount != null) ? $value->salesquatationfull->other_charge_amount : 0;

            if ($value->salesquatationfull->form_type == 'resident') {
                $value->system_cost =  $value->salesquatationfull->total_system_cost;
            } else {
                $value->system_cost =  $value->salesquatationfull->total_amount - $meter_charges - $registration_fee - $other_charge_amount;
            }

            $value->meter_charges = number_format($meter_charges, 2);
            $value->registration_fee = number_format($registration_fee, 2);
            $other_charge_amount = ($value->salesquatationfull->other_charge_amount != null) ? $value->salesquatationfull->other_charge_amount : 0;
            $value->other_charge_amount =  number_format($other_charge_amount, 2);
            $total_amount = ($value->total_amount != null) ? $value->total_amount : 0;
            $pending_amonut =  ($value->pending_amonut != null) ? $value->pending_amonut : 0;
            $value->pending_amonut = $pending_amonut;
            $total_amount = is_numeric($value->total_amount) ? (float) $value->total_amount : 0;
            $pending_amonut = is_numeric($value->pending_amonut) ? (float) $value->pending_amonut : 0;
            $value->received_amonut =  number_format(($total_amount - $pending_amonut), 2);
            $totalamount = is_numeric($totalamount) ? (float) $totalamount : 0;
            $meter_charges = is_numeric($meter_charges) ? (float) $meter_charges : 0;
            $registration_fee = is_numeric($registration_fee) ? (float) $registration_fee : 0;
            $other_charge_amount = is_numeric($other_charge_amount) ? (float) $other_charge_amount : 0;
            $value->totalamt = number_format(($totalamount), 2);
            $subsidy =  ($value->salesquatationfull->subsidy != null) ? $value->salesquatationfull->subsidy : 0;
            $value->subsidy = number_format($subsidy, 2);
            $subsidy = is_numeric($subsidy) ? (float) $subsidy : 0;
            $value->net_customer_price = number_format(($totalamount - $subsidy), 2);
            $value->agentsalesperson_name = $value->agentsalesperson->name;
            $value->penal_company = '';
            $value->inveter_company = '';
            $value->penal_watt = (isset($value->salesquatationfull->penalWatt) && isset($value->salesquatationfull->penalWatt->name)) ? $value->salesquatationfull->penalWatt->name : '';
            $value->status = toGetSalesMasterLastStatus($value->sid);
            $value->discount = getDiscount($value->sid);
            // company name
            if ($value->salesquatationfull->penal_company_id != null) {
                $hist = PenalCompany::whereIn('id', explode(',', $value->salesquatationfull->penal_company_id))->get();
                if ($hist != '') {
                    $penel_companies_name =  "";
                    foreach ($hist as $ri) {
                        $penel_companies_name != "" && $penel_companies_name .= " / ";
                        $penel_companies_name .= $ri->name;
                    }
                    $value->penal_company = $penel_companies_name;
                }
            }
            // company name
            // Inveter company name
            if ($value->salesquatationfull->inveter_company_id != null) {
                $hist = InveterCompany::whereIn('id', explode(',', $value->salesquatationfull->inveter_company_id))->get();
                if ($hist != '') {
                    $inveter_companies_name =  "";
                    foreach ($hist as $ri) {
                        $inveter_companies_name != "" && $inveter_companies_name .= " / ";
                        $inveter_companies_name .= $ri->name;
                    }
                    $value->inveter_company = $inveter_companies_name;
                }
            }
            // Inveter company name

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
            'Contact Number',
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
            'Date',
            'Panel Company Name',
            'Panel Watt',
            'Panel Nos',
            'Inveter Company Name',
            'State',
            'District',
            'Taluko',
            'Meter Installation Date',
            'Subsidy Disbursement Date',
            'SubsidyRequest Date',
        ];
    }

    public function map($row): array
    {
        return [
            $row->consumer_number,
            $row->consumer_name,
            $row->contact_number,
            $row->consumer_type,
            $row->register_kw,
            $row->system_cost,
            $row->meter_charges,
            $row->registration_fee,
            $row->other_charge_amount,
            $row->totalamt,
            $row->received_amonut,
            $row->pending_amonut,
            $row->subsidy,
            $row->net_customer_price,
            $row->pending_amt,
            $row->discount,
            $row->agentsalesperson_name,
            $row->status,
            $row->invoice_no,
            $row->installation_date,
            $row->penal_company,
            $row->penal_watt,
            $row->salesquatationfull->penal_nos,
            $row->inveter_company,
            $row->state_name,
            $row->district_id,
            $row->taluka_id,
            $row->meter_installation_date,
            $row->subsidy_disbursement_date,
            $row->subsidy_request_date,
        ];
    }
}
