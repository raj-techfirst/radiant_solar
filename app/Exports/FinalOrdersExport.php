<?php

namespace App\Exports;

use App\Models\AgentSalesPerson;
use App\Models\CompanyProfile;
use App\Models\SalesMaster;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMapping;

class FinalOrdersExport implements FromCollection, WithHeadings, WithMapping
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
        $query = SalesMaster::select('*','id as sid')->with('district','district.state', 'taluka', 'village', 'subDivision', 'salesquatationfull');
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
        $query->where('project_completion', "1");
        
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
            $totalamount = ($value->salesquatationfull->total_amount != null) ? $value->salesquatationfull->total_amount : 0;
            $value->system_cost =  $totalamount;
            $meter_charges = ($value->salesquatationfull->meter_charges != null) ? $value->salesquatationfull->meter_charges : 0;
            $value->meter_charges = number_format($meter_charges, 2);
            $registration_fee = ($value->salesquatationfull->registration_fee != null) ? $value->salesquatationfull->registration_fee : 0;
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
            $value->totalamt = number_format(($totalamount + $meter_charges + $registration_fee  + $other_charge_amount), 2);
            $subsidy =  ($value->salesquatationfull->subsidy != null) ? $value->salesquatationfull->subsidy : 0;
            $value->subsidy = number_format($subsidy, 2);
            $subsidy = is_numeric($subsidy) ? (double) $subsidy : 0;
            $value->net_customer_price = number_format(($totalamount - $subsidy), 2);
            $value->agentsalesperson_name = $value->agentsalesperson->name;
            $value->status = toGetSalesMasterLastStatus($value->sid);
            $value->installer = installationAsignPerson($value->installation_asian_person);
            $value->meter_application_person = installationAsignPerson($value->meter_asian_person);
            $value->sub_division_name = (isset($value->subDivision) && (isset($value->subDivision[0]))) ? $value->subDivision[0]->name : '';
            $value->state_name = $value->district->state->state_name;
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
            'Address',
            'State',
            'District',
            'Taluka',
            'Pincode',
            'Sub Division',
            'Division',
            'Agent Sales Person',
            'Installation Asian Person',
            'Application Status'
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
            $row->address,
            $row->state_name,
            $row->district->name,
            $row->taluka->name,
            $row->pin_code,
            $row->sub_division_name,
            $row->division,
            $row->agentsalesperson_name,
            $row->installer,
            $row->status
        ];
    }
}
