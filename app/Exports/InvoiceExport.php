<?php

namespace App\Exports;

use App\Models\AgentSalesPerson;
use App\Models\CompanyProfile;
use App\Models\SalesMaster;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class InvoiceExport implements FromCollection, WithHeadings, WithMapping
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
        $query = SalesMaster::select('*', 'id as sid')->with('district','district.state', 'taluka', 'village', 'subDivision', 'salesquatationfull', 'installation');
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

        if ($this->request->input('invoice') == "YES") {
            $query->where(function ($q) {
                $q->where('invoice_date', '!=', '')
                    ->where('invoice_date', '!=', '0000-00-00');
            });
        }
        if ($this->request->input('invoice') == "NO") {
            $query->where(function ($q) {
                $q->where('invoice_date', '==', '')
                    ->orWhere('invoice_date', '==', '0000-00-00');
            });
        }

        $data = $query->orderBy('invoice_date', 'DESC')->orderBy('invoice_no', 'DESC')->get();
        foreach ($data as $key => $value) {
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
            $total_amount = is_numeric($value->total_amount) ? (float) $value->total_amount : 0;
            $pending_amonut = is_numeric($value->pending_amonut) ? (float) $value->pending_amonut : 0;
            $value->received_amonut =  number_format(($total_amount - $pending_amonut), 2);
            $totalamount = is_numeric($totalamount) ? (float) $totalamount : 0;
            $value->pending_amonut = number_format($pending_amonut,2);
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
            $value->installation_asian_person = installationAsignPerson($value->installation_asian_person);
            $value->ragistration_date = ($value->ragistration_date != "" && $value->ragistration_date != "0000-00-00") ? date('d-m-Y', strtotime($value->ragistration_date)) : '';
            $value->invoice_date = ($value->invoice_date != "" && $value->invoice_date != "0000-00-00") ? date('d-m-Y', strtotime($value->invoice_date)) : '';
            $value->master_create_date = ($value->master_create_date != "" && $value->master_create_date != "0000-00-00") ? date('d-m-Y', strtotime($value->master_create_date)) : '';
            $value->feasibility_date = ($value->feasibility_date != "" && $value->feasibility_date != "0000-00-00") ? date('d-m-Y', strtotime($value->feasibility_date)) : '';
            $value->meter_application_date = ($value->meter_application_date != "" && $value->meter_application_date != "0000-00-00") ? date('d-m-Y', strtotime($value->meter_application_date)) : '';
            if (!is_null($value->installation)) {
                $value->installation_date = ($value->installation->date != "" && $value->installation->date != "0000-00-00") ? date('d-m-Y', strtotime($value->installation->date)) : '';
            } else {
                $value->installation_date = '';
            }
            $value->state_name = $value->district->state->state_name;

        }
        return $data;
    }

    public function headings(): array
    {
        return [
            'Consumer Number',
            'Invoice No.',
            'Invoice Date',
            'Consumer Name',
            'Contact Number',
            'Registation KW',
            'Agent Sales Person',
            'Installation Assign Person',
            'Consumer Type',
            'Application Status',
            'Address',
            'State',
            'District',
            'Taluka',
            'Pincode',
            'Master date',
            'Ragistration Numbar',
            'Ragistration Date',
            'Feasibility Date',
            'Installation Date',
            'Meter Application Date',

            'System Cost',
            'Meter Charge ',
            'Registration Fee',
            'Other Charge',
            'Total Cost ',
            'Payment Received ',
            'Payment Pending',

        ];
    }

    public function map($row): array
    {
        return [
            $row->consumer_number,
            $row->invoice_no,
            $row->invoice_date,
            $row->consumer_name,
            $row->contact_number,
            $row->register_kw,
            $row->agentsalesperson_name,
            $row->installation_asian_person,
            $row->consumer_type,
            $row->status,
            $row->address,
            $row->state_name,
            $row->district->name,
            $row->taluka->name,
            $row->pin_code,
            $row->master_create_date,
            $row->ragistration_number,
            $row->ragistration_date,
            $row->feasibility_date,
            $row->installation_date,
            $row->meter_application_date,

            $row->system_cost,
            $row->meter_charges,
            $row->registration_fee,
            $row->other_charge_amount,
            $row->totalamt,
            $row->received_amonut,
            $row->pending_amonut,
        ];
    }
}
