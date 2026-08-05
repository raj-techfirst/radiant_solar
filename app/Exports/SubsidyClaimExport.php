<?php

namespace App\Exports;

use App\Models\AgentSalesPerson;
use App\Models\CompanyProfile;
use App\Models\SalesMaster;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMapping;


class SubsidyClaimExport implements FromCollection, WithHeadings, WithMapping
{
    private $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function collection()
    {
        $query = SalesMaster::select('*', 'id as sid')->with('district', 'taluka', 'village', 'subDivision', 'salesquatationfull');
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
            if ($this->request->input('agent_sales_person_id') == "") {
                $query->whereIn('agent_sales_person_id', $agentIds);
            }
        }
        if ($company->user_type == 'S') {
            $agent = AgentSalesPerson::where('user_id', Auth::id())->first();
            $id = $agent->id;
            $query->where('agent_sales_person_id', $id);
        }
        $query->where('meter_installation', "1");
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
            $value['invoice_date'] = (!is_null($value->invoice_date) && $value->invoice_date != '1970-01-01' && $value->invoice_date != '0000-00-00') ? date('d-m-Y', strtotime($value->invoice_date)) : '';
            $value['meter_installation_date'] = (!is_null($value->meter_installation_date) && $value->meter_installation_date != '1970-01-01' && $value->meter_installation_date != '0000-00-00') ? date('d-m-Y', strtotime($value->meter_installation_date)) : '';
            $value['subsidy_request_date'] = (!is_null($value->subsidy_request_date) && $value->subsidy_request_date != '1970-01-01' && $value->subsidy_request_date != '0000-00-00') ? date('d-m-Y', strtotime($value->subsidy_request_date)) : '';
            $value['subsidy_disbursement_verify_date'] = (!is_null($value->subsidy_disbursement_verify_date) && $value->subsidy_disbursement_verify_date != '1970-01-01' && $value->subsidy_disbursement_verify_date != '0000-00-00') ? date('d-m-Y', strtotime($value->subsidy_disbursement_verify_date)) : '';
            $value->id = $key + 1;
            $value->agentsalesperson_name = $value->agentsalesperson->name;
            $value->status = toGetSalesMasterLastStatus($value->sid);
            $value->sub_division_name = (isset($value->subDivision) && (isset($value->subDivision[0]))) ? $value->subDivision[0]->name : '';
        }
        return $data;
    }


    public function headings(): array
    {
        return [
            'Consumer Number',
            'Ragistration Number',
            'Ragistration Portal',
            'Consumer Type',
            'Consumer Name',
            'Contact Number',
            'Sub Division',
            'Division',
            'DISCOM',
            'Agent Sales Person',
            'Registation Kw',
            'Invoice Date',
            'Meter Installation Date',
            'Subsidy Claim Date',
            'Subsidy Claim Verify Date',
            'Subsidy Remarks',
            'Application Status'
        ];
    }

    public function map($row): array
    {
        return [
            $row->consumer_number,
            $row->ragistration_number,
            $row->ragistration_portal,
            $row->consumer_type,
            $row->consumer_name,
            $row->contact_number,
            $row->sub_division_name,
            $row->division,
            $row->discom,
            $row->agentsalesperson_name,
            $row->register_kw,
            $row->invoice_date,
            $row->meter_installation_date,
            $row->subsidy_request_date,
            $row->subsidy_disbursement_verify_date,
            $row->subsidy_disbursal_remark,
            $row->status,
        ];
    }
}
