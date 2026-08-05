<?php

namespace App\Exports;

use App\Models\AgentSalesPerson;
use App\Models\CompanyProfile;
use App\Models\SalesMaster;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class PanelsRequired implements FromCollection, WithHeadings, WithMapping
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
        $query = SalesMaster::select('sales_masters.*', DB::raw('SUM(sales_quatations.penal_nos) as total_panel'))
            ->with('district', 'panel', 'panelwatt')
            ->leftJoin('sales_quatations', 'sales_quatations.id', '=', 'sales_masters.sales_quatation_id')
            ->groupBy(
                'sales_masters.district_id',
                'sales_masters.penal_company_id',
                'sales_masters.penal_watt_id'
            )
            ->orderBy('sales_masters.district_id')
            ->orderBy('sales_masters.penal_company_id')
            ->orderBy('sales_masters.penal_watt_id');

        $query->where('sales_masters.file_cancel_order', '0');
        $query->where('sales_masters.installation_done', '0');

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
                $query->whereIn('sales_masters.agent_sales_person_id', $agentIds);
            }
        }
        if ($company->user_type == 'S') {
            $agent = AgentSalesPerson::where('user_id', Auth::id())->first();
            $id = $agent->id;
            $query->where('sales_masters.agent_sales_person_id', $id);
        }

        if ($this->request->input('agent_sales_person_id') != "") {
            $query->where('sales_masters.agent_sales_person_id', $this->request->input('agent_sales_person_id'));
        }

        if ($this->request->input('district_id') != "") {
            $query->where('sales_masters.district_id', $this->request->input('district_id'));
        }

        if ($this->request->input('panel_company_id') != "") {
            $query->where('sales_masters.penal_company_id', $this->request->input('panel_company_id'));
        }

        if ($this->request->input('panel_watt_id') != "") {
            $query->where('sales_masters.penal_watt_id', $this->request->input('panel_watt_id'));
        }
        $data = $query->get();

        foreach ($data as $key => $value) {
            $value->district = $value->district->name;
            $value->panel = $value->panel->name;
            $value->panel_watt = $value->panelwatt->name;
        }
        return $data;
    }

    public function headings(): array
    {
        return [
            'District',
            'Panel',
            'Panel Watt',
            'Total Panel'
        ];
    }

    public function map($row): array
    {
        return [
            $row->district,
            $row->panel,
            $row->panel_watt,
            $row->total_panel
        ];
    }
}
