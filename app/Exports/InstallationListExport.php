<?php

namespace App\Exports;

use App\Models\AgentSalesPerson;
use App\Models\CompanyProfile;
use App\Models\SalesMaster;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMapping;

class InstallationListExport implements FromCollection, WithHeadings, WithMapping
{
    private $request;
    private $formType;
    private $dynamicItemColumns = [];

    public function __construct($request, $formType = 'old')
    {
        $this->request = $request;
        $this->formType = $formType;
        if ($formType == 'new') {
            $this->prepareDynamicItems();
        }
    }

    private function prepareDynamicItems()
    {
        $query = \App\Models\SalesMaster::select('id')
            ->where('file_cancel_order', '0')
            ->where('installation_asian_person', '!=', '')
            ->where(function ($q) {
                $q->where('installation_pending',  "1")
                    ->orWhere('installation_done', "1");
            })
            ->whereHas('installation', function ($q) {
                $q->where('form_type', 'new');
            });
        $company = \App\Models\CompanyProfile::where('user_id', Auth::id())->first();
        if ($company->user_type == 'M') {
            $agent = \App\Models\AgentSalesPerson::where('user_id', Auth::id())->first();
            $agentIds = [$agent->id];
            $sales = \App\Models\CompanyProfile::select('company_profiles.id', 'company_profiles.user_id', 'agent_sales_people.id as agent_id')
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
            $agent = \App\Models\AgentSalesPerson::where('user_id', Auth::id())->first();
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
        $salesIds = $query->pluck('id');
        if ($salesIds->count() > 0) {
            $itemIds = \App\Models\InstallationItems::whereIn('sales_master_id', $salesIds)
                ->where('deleted_at', null)
                ->pluck('item_id')
                ->unique()
                ->filter()
                ->values();
            if ($itemIds->count() > 0) {
                $items = \App\Models\Product::whereIn('id', $itemIds)->get(['id', 'name']);
                $this->dynamicItemColumns = $items->keyBy('id');
            }
        }
    }
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $query = SalesMaster::select('*', 'id as sid')->with('district','district.state', 'taluka',  'subDivision', 'salesquatationfull', 'installation', 'installation.panelwatt', 'installation.panelcompany', 'installation.paneltype', 'installation.installationPenals', 'installation.installationPenals.itemGroup', 'installation.invater', 'installation.invater.company', 'installation.invater.itemGroup', 'installation.installationItems.product');
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
        $query->where('installation_asian_person', "!=", "");
        $query->where(function ($q) {
            $q->where('installation_pending',  "1")
                ->orWhere('installation_done', "1");
        });
        $query->whereHas('installation', function ($q) {
            $q->where('form_type', $this->formType);
        });
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
            $installation_date = (isset($value->installation) && $value->installation != null) ? $value->installation->date : '';
            $penal_company = (isset($value->installation) && $value->installation != null && isset($value->installation->panelcompany) && $value->installation->panelcompany != null) ? $value->installation->panelcompany->name : '';
            $penal_model_no = (isset($value->installation) && $value->installation != null) ? $value->installation->penal_model_no : '';
            $penal_type = (isset($value->installation) && $value->installation != null && isset($value->installation->paneltype) && $value->installation->paneltype != null) ? $value->installation->paneltype->name : '';
            $penal_watt = (isset($value->installation) && $value->installation != null && isset($value->installation->panelwatt) && $value->installation->panelwatt != null) ? $value->installation->panelwatt->name : '';
            $penal_nos = (isset($value->installation) && $value->installation != null) ? $value->installation->penal_nos : '';
            $penal_sr_no_arr = [];
            if (isset($value->installation) && $value->installation != null && isset($value->installation->installationPenals) && count($value->installation->installationPenals) > 0) {
                foreach ($value->installation->installationPenals as $pk => $pv) :
                    $penal_sr_no_arr[] = $pv->serial_no;
                endforeach;
            }
            $penal_sr_no = implode(', ', $penal_sr_no_arr);
            $p_type = '';
            if (isset($value->installation) && $value->installation != null && isset($value->installation->installationPenals) && count($value->installation->installationPenals) > 0 && isset($value->installation->installationPenals[0]->itemGroup) && $value->installation->installationPenals[0]->itemGroup != null) {
                $p_type = $value->installation->installationPenals[0]->itemGroup->p_type;
            }
            $panel_detail = $penal_watt . 'W Solar Module (' . $penal_company . ' - ' . $penal_type . ' | ' . $p_type . ')';
            $total_kw = (isset($value->installation) && $value->installation != null) ? $value->installation->total_kv : '';
            $type_of_inverter = (isset($value->installation) && $value->installation != null) ? $value->installation->type_of_inverter : '';
            if ($value->installation != null && $value->installation->form_type == 'new') {
                $type_of_inverter = ($type_of_inverter === null || $type_of_inverter === '') ? getInstallationInverterType($value->installation) : $type_of_inverter;
            }
            $no_of_inverter = (isset($value->installation) && $value->installation != null) ? $value->installation->no_of_inverter : '';
            $make_of_inverter_arr = $inverter_model_no_arr = $inverter_kw_arr = $inverter_sr_no_arr = $inverter_voltage_arr = [];
            if (isset($value->installation) && $value->installation != null && isset($value->installation->invater) && count($value->installation->invater) > 0) {
                foreach ($value->installation->invater as $ik => $iv) :
                    if (isset($iv->company) && $iv->company != null) {
                        $make_of_inverter_arr[] = $iv->company->name;
                    }
                    $inverter_model_no_arr[] = $iv->model_number;
                    $inverter_kw_arr[] = $iv->invater_kw;
                    $inverter_sr_no_arr[] = $iv->serial_no_of_inverter;
                    $inverter_voltage_arr[] = $iv->voltage;

                endforeach;
            }
            $make_of_inverter = implode(', ', $make_of_inverter_arr);
            $inverter_model_no = implode(', ', $inverter_model_no_arr);
            $inverter_sr_no = implode(', ', $inverter_sr_no_arr);
            $inverter_voltage = implode(', ', $inverter_voltage_arr);
            $inverter_kw = implode(', ', $inverter_kw_arr);
            $inverter_detail_arr = [];
            if (isset($value->installation) && $value->installation != null && isset($value->installation->invater) && count($value->installation->invater) > 0) {
                foreach ($value->installation->invater as $ik => $iv) :
                    $inv_company = (isset($iv->company) && $iv->company != null) ? $iv->company->name : 'N/A';
                    $inv_type = (isset($iv->itemGroup) && $iv->itemGroup != null && $iv->itemGroup->inverter_type != null && $iv->itemGroup->inverter_type != '') ? $iv->itemGroup->inverter_type : 'N/A';
                    $inverter_detail_arr[] = $iv->invater_kw . ' KW Inverter (' . $inv_company . ' | ' . $inv_type . ')';
                endforeach;
            }
            $inverter_detail = implode(', ', $inverter_detail_arr);
            $cable_dc = (isset($value->installation) && $value->installation != null) ? $value->installation->cable_dc : '';
            $cable_ac = (isset($value->installation) && $value->installation != null) ? $value->installation->cable_ac : '';
            $cable_la = (isset($value->installation) && $value->installation != null) ? $value->installation->cable_la : '';
            $cable_earthing = (isset($value->installation) && $value->installation != null) ? $value->installation->cable_earthing : '';
            if ($value->installation != null && $value->installation->form_type == 'new') {
                $cableTotals = getInstallationCableTotals($value->installation);
                $cable_dc = ($cable_dc === null || $cable_dc === '') ? $cableTotals['dc'] : $cable_dc;
                $cable_ac = ($cable_ac === null || $cable_ac === '') ? $cableTotals['ac'] : $cable_ac;
                $cable_la = ($cable_la === null || $cable_la === '') ? $cableTotals['la'] : $cable_la;
                $cable_earthing = ($cable_earthing === null || $cable_earthing === '') ? $cableTotals['earthing'] : $cable_earthing;
            }
            $dc_side = (isset($value->installation) && $value->installation != null) ? $value->installation->dc_side : '';
            $ac_Side = (isset($value->installation) && $value->installation != null) ? $value->installation->ac_side : '';
            $la_Earthing = (isset($value->installation) && $value->installation != null) ? $value->installation->la_earthing : '';
            $phase_to_earth = (isset($value->installation) && $value->installation != null) ? $value->installation->phase_to_earth : '';
            $phase_to_phase = (isset($value->installation) && $value->installation != null) ? $value->installation->phase_to_phase : '';
            $structure_40_40_2_mm = (isset($value->installation) && $value->installation != null) ? $value->installation->structure_40_40_2mm : '';
            $structure_60_40_2_mm = (isset($value->installation) && $value->installation != null) ? $value->installation->structure_60_40_2mm : '';
            $structure_80_40_2_mm = (isset($value->installation) && $value->installation != null) ? $value->installation->structure_80_40_2mm : '';
            $structure_others = (isset($value->installation) && $value->installation != null) ? $value->installation->structure_others : '';
            if ($value->installation != null && $value->installation->form_type == 'new') {
                $structureTotals = getInstallationStructureTotals($value->installation);
                $structure_40_40_2_mm = ($structure_40_40_2_mm === null || $structure_40_40_2_mm === '') ? $structureTotals['s40'] : $structure_40_40_2_mm;
                $structure_60_40_2_mm = ($structure_60_40_2_mm === null || $structure_60_40_2_mm === '') ? $structureTotals['s60'] : $structure_60_40_2_mm;
                $structure_80_40_2_mm = ($structure_80_40_2_mm === null || $structure_80_40_2_mm === '') ? $structureTotals['s80'] : $structure_80_40_2_mm;
                $structure_others = ($structure_others === null || $structure_others === '') ? $structureTotals['others'] : $structure_others;
            }
            $value->installation_date = $installation_date;
            $value->penal_company = $penal_company;
            $value->penal_model_no = $penal_model_no;
            $value->penal_type = $penal_type;
            $value->penal_watt = $penal_watt;
            $value->penal_nos = $penal_nos;
            $value->penal_sr_no = $penal_sr_no;
            $value->panel_detail = $panel_detail;
            $value->total_kw = $total_kw;
            $value->type_of_inverter = $type_of_inverter;
            $value->no_of_inverter = $no_of_inverter;
            $value->make_of_inverter = $make_of_inverter;
            $value->inverter_model_no = $inverter_model_no;
            $value->inverter_kw = $inverter_kw;
            $value->inverter_sr_no = $inverter_sr_no;
            $value->inverter_voltage = $inverter_voltage;
            $value->inverter_detail = $inverter_detail;
            $value->cable_dc = $cable_dc;
            $value->cable_ac = $cable_ac;
            $value->cable_la = $cable_la;
            $value->cable_earthing = $cable_earthing;
            $value->dc_side = $dc_side;
            $value->ac_Side = $ac_Side;
            $value->la_Earthing = $la_Earthing;
            $value->phase_to_earth = $phase_to_earth;
            $value->phase_to_phase = $phase_to_phase;
            $value->structure_40_40_2_mm = $structure_40_40_2_mm;
            $value->structure_60_40_2_mm = $structure_60_40_2_mm;
            $value->structure_80_40_2_mm = $structure_80_40_2_mm;
            $value->structure_others = $structure_others;
            $value->state_name = $value->district->state->state_name;
        }

        return $data;
    }

    public function headings(): array
    {
        $headings = [
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
            'Invoice No',
            'Date',
            'Agent Sales Person',
            'Installation Asign Person',
            'Application Status',
            'Installation Date',
        ];
        if ($this->formType == 'new') {
            array_push($headings, 'Panel Detail', 'Panel Model no', 'Panel Nos');
        } else {
            array_push($headings, 'Panel Company', 'Panel Model no', 'Panel Type', 'Panel Watt', 'Panel Nos');
        }
        array_push($headings, 'Total KW');
        if ($this->formType == 'new') {
            array_push($headings, 'Inverter Detail', 'No of Inverter', 'Inverter Model no', 'Inverter Sr No', 'Inverter Voltage');
        } else {
            array_push($headings, 'Type Of Inverter', 'No of Inverter', 'Make Of Inverter', 'Inverter Model no', 'Inverter KW', 'Inverter Sr No', 'Inverter Voltage');
        }
        if ($this->formType == 'new' && !empty($this->dynamicItemColumns)) {
            foreach ($this->dynamicItemColumns as $item) {
                array_push($headings, $item->name);
            }
        } else {
            array_push($headings,
                'Cable DC',
                'Cable AC',
                'Cable LA',
                'Cable Earthing',
                'DC Side',
                'AC Side',
                'LA Earthing',
                'Phase To Earth',
                'Phase To Phase',
                'Structure 40*40*2 mm',
                'Structure 60*40*2 mm',
                'Structure 80*40*2 mm',
                'Structure others'
            );
        }
        array_push($headings,
            "Commission Amount",
            "Sub Commission Amount",
            "Installation Amount"
        );
        return $headings;
    }

    public function map($row): array
    {
        $data = [
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
            $row->invoice_no,
            $row->installation_date,
            $row->agentsalesperson_name,
            $row->installer,
            $row->status,
            $row->installation_date,
        ];
        if ($this->formType == 'new') {
            array_push($data, $row->panel_detail, $row->penal_model_no, $row->penal_nos);
        } else {
            array_push($data, $row->penal_company, $row->penal_model_no, $row->penal_type, $row->penal_watt, $row->penal_nos);
        }
        array_push($data, $row->total_kw);
        if ($this->formType == 'new') {
            array_push($data, $row->inverter_detail, $row->no_of_inverter, $row->inverter_model_no, $row->inverter_sr_no, $row->inverter_voltage);
        } else {
            array_push($data, $row->type_of_inverter, $row->no_of_inverter, $row->make_of_inverter, $row->inverter_model_no, $row->inverter_kw, $row->inverter_sr_no, $row->inverter_voltage);
        }
        if ($this->formType == 'new' && !empty($this->dynamicItemColumns)) {
            $rowItems = optional($row->installation)->installationItems ?? collect();
            foreach ($this->dynamicItemColumns as $itemId => $item) {
                $itemData = $rowItems->where('item_id', $itemId)->first();
                $data[] = $itemData ? ($itemData->use_stock ?? '') : '';
            }
        } else {
            array_push($data,
                $row->cable_dc,
                $row->cable_ac,
                $row->cable_la,
                $row->cable_earthing,
                $row->dc_side,
                $row->ac_Side,
                $row->la_Earthing,
                $row->phase_to_earth,
                $row->phase_to_phase,
                $row->structure_40_40_2_mm,
                $row->structure_60_40_2_mm,
                $row->structure_80_40_2_mm,
                $row->structure_others
            );
        }
        array_push($data,
            $row->commission_amount,
            $row->sub_commission_amount,
            $row->installation_amount
        );
        return $data;
    }
}
