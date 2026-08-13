<?php

namespace App\Exports;

use App\Models\AgentSalesPerson;
use App\Models\CompanyProfile;
use App\Models\SalesQuatation;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMapping;


class SalesQuatationExport implements FromCollection, WithHeadings, WithMapping
{
    private $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function collection()
    {
        $query = SalesQuatation::with('agentSalesPerson')->orderBy('id', 'DESC');
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
        if ($this->request->input('from_date') != "" && $this->request->input('to_date') == '') {
            $query->where('created_at', '>=', date('Y-m-d 00:00:00', strtotime($this->request->input('from_date'))));
            $query->where('created_at', '<=', date('Y-m-d 23:59:59'));
        }
        if ($this->request->input('from_date') != "" && $this->request->input('to_date') != '') {
            $query->where('created_at', '>=', date('Y-m-d 00:00:00', strtotime($this->request->input('from_date'))));
            $query->where('created_at', '<=', date('Y-m-d 23:59:59', strtotime($this->request->input('to_date'))));
        }
        if ($this->request->input('from_date') == "" && $this->request->input('to_date') != '') {
            $query->where('created_at', '<=', date('Y-m-d 23:59:59', strtotime($this->request->input('to_date'))));
        }
        if ($this->request->input('consumer') != "") {
            $query->where(function ($q) {
                $consumer = $this->request->input('consumer');
                $q->where('name', 'like', '%' . $consumer . '%')
                    ->orWhere('mobile', 'like', '%' . $consumer . '%');
            });
        }
        if ($this->request->input('form_type') != "") {
            $query->where('form_type', $this->request->input('form_type'));
        }
        if ($this->request->input('assign') != "") {
            $query->where('agent_sales_person_id', $this->request->input('assign'));
        }
        if ($this->request->input('current_status') != "") {
            $query->where('current_status', $this->request->input('current_status'));
        }
        $data = $query->get();
        foreach ($data as $key => $value) {
            $value->id = $key + 1;
            $value->agentsalesperson_name = (isset($value->agentSalesPerson)) ? $value->agentSalesPerson->name : '';
            $value->quatation_type = ($value->form_type == 'trading') ? 'Trading' : (($value->form_type == 'resident') ? 'Resident With Subsidy' : 'Solar RoofTop');
            $value->quatation_status = getSalesQuotationStatusClass($value->current_status);
        }
        return $data;
    }


    public function headings(): array
    {
        return [
            'Sr No',
            'Status',
            'Type',
            'Name',
            'Mobile',
            'Address',
            'Date',
            'Agent'
        ];
    }

    public function map($row): array
    {
        return [
            $row->id,
            $row->quatation_status['status'],
            $row->quatation_type,
            $row->name,
            $row->mobile,
            $row->address,
            date('d-m-Y', strtotime($row->created_at)),
            $row->agentsalesperson_name,
        ];
    }
}