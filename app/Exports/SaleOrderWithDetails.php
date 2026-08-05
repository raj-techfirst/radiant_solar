<?php

namespace App\Exports;

use App\Models\InveterCompany;
use App\Models\PenalCompany;
use App\Models\SalesMaster;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class SaleOrderWithDetails implements FromCollection, WithHeadings, WithMapping
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
        $query = SalesMaster::select('*', 'id as sid')->with('district', 'taluka',  'subDivision', 'salesquatationfull', 'installation', 'installation.panelwatt', 'installation.panelcompany', 'installation.paneltype', 'installation.installationPenals', 'installation.invater', 'installation.invater.company');
        $data = $query->orderBy('id', 'DESC')->first();

        $data->id = 1;
        $totalamount = ($data->salesquatationfull->total_amount != null) ? $data->salesquatationfull->total_amount : 0;
        $data->system_cost =  $totalamount;
        $meter_charges = ($data->salesquatationfull->meter_charges != null) ? $data->salesquatationfull->meter_charges : 0;
        $data->meter_charges = number_format($meter_charges, 2);
        $registration_fee = ($data->salesquatationfull->registration_fee != null) ? $data->salesquatationfull->registration_fee : 0;
        $data->registration_fee = number_format($registration_fee, 2);
        $other_charge_amount = ($data->salesquatationfull->other_charge_amount != null) ? $data->salesquatationfull->other_charge_amount : 0;
        $data->other_charge_amount =  number_format($other_charge_amount, 2);
        $total_amount = ($data->total_amount != null) ? $data->total_amount : 0;
        $pending_amonut =  ($data->pending_amonut != null) ? $data->pending_amonut : 0;
        $data->pending_amonut = $pending_amonut;
        $total_amount = is_numeric($data->total_amount) ? (float) $data->total_amount : 0;
        $pending_amonut = is_numeric($data->pending_amonut) ? (float) $data->pending_amonut : 0;
        $data->received_amonut =  number_format(($total_amount - $pending_amonut), 2);
        $totalamount = is_numeric($totalamount) ? (float) $totalamount : 0;
        $meter_charges = is_numeric($meter_charges) ? (float) $meter_charges : 0;
        $registration_fee = is_numeric($registration_fee) ? (float) $registration_fee : 0;
        $other_charge_amount = is_numeric($other_charge_amount) ? (float) $other_charge_amount : 0;
        $data->totalamt = number_format(($totalamount + $meter_charges + $registration_fee  + $other_charge_amount), 2);
        $subsidy =  ($data->salesquatationfull->subsidy != null) ? $data->salesquatationfull->subsidy : 0;
        $data->subsidy = number_format($subsidy, 2);
        $subsidy = is_numeric($subsidy) ? (float) $subsidy : 0;
        $data->net_customer_price = number_format(($totalamount - $subsidy), 2);
        $data->agentsalesperson_name = $data->agentsalesperson->name;
        $data->status = toGetSalesMasterLastStatus($data->sid);
        $data->installer = installationAsignPerson($data->installation_asian_person);
        $installation_date = (isset($data->installation) && $data->installation != null) ? $data->installation->date : '';
        $penal_company = (isset($data->installation) && $data->installation != null && isset($data->installation->panelcompany) && $data->installation->panelcompany != null) ? $data->installation->panelcompany->name : '';
        $penal_model_no = (isset($data->installation) && $data->installation != null) ? $data->installation->penal_model_no : '';
        $penal_type = (isset($data->installation) && $data->installation != null && isset($data->installation->paneltype) && $data->installation->paneltype != null) ? $data->installation->paneltype->name : '';
        $penal_watt = (isset($data->installation) && $data->installation != null && isset($data->installation->panelwatt) && $data->installation->panelwatt != null) ? $data->installation->panelwatt->name : '';
        $penal_nos = (isset($data->installation) && $data->installation != null) ? $data->installation->penal_nos : '';
        $penal_sr_no_arr = [];
        if (isset($data->installation) && $data->installation != null && isset($data->installation->installationPenals) && count($data->installation->installationPenals) > 0) {
            foreach ($data->installation->installationPenals as $pk => $pv) :
                $penal_sr_no_arr[] = $pv->serial_no;
            endforeach;
        }
        $penal_sr_no = implode(', ', $penal_sr_no_arr);
        $total_kw = (isset($data->installation) && $data->installation != null) ? $data->installation->total_kv : '';
        $type_of_inverter = (isset($data->installation) && $data->installation != null) ? $data->installation->type_of_inverter : '';
        $no_of_inverter = (isset($data->installation) && $data->installation != null) ? $data->installation->no_of_inverter : '';
        $make_of_inverter_arr = $inverter_model_no_arr = $inverter_kw_arr = $inverter_sr_no_arr = $inverter_voltage_arr = [];
        if (isset($data->installation) && $data->installation != null && isset($data->installation->invater) && count($data->installation->invater) > 0) {
            foreach ($data->installation->invater as $ik => $iv) :
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
        $cable_dc = (isset($data->installation) && $data->installation != null) ? $data->installation->cable_dc : '';
        $cable_ac = (isset($data->installation) && $data->installation != null) ? $data->installation->cable_ac : '';
        $cable_la = (isset($data->installation) && $data->installation != null) ? $data->installation->cable_la : '';
        $cable_earthing = (isset($data->installation) && $data->installation != null) ? $data->installation->cable_earthing : '';
        $dc_side = (isset($data->installation) && $data->installation != null) ? $data->installation->dc_side : '';
        $ac_Side = (isset($data->installation) && $data->installation != null) ? $data->installation->ac_side : '';
        $la_Earthing = (isset($data->installation) && $data->installation != null) ? $data->installation->la_earthing : '';
        $phase_to_earth = (isset($data->installation) && $data->installation != null) ? $data->installation->phase_to_earth : '';
        $phase_to_phase = (isset($data->installation) && $data->installation != null) ? $data->installation->phase_to_phase : '';
        $structure_40_40_2_mm = (isset($data->installation) && $data->installation != null) ? $data->installation->structure_40_40_2mm : '';
        $structure_60_40_2_mm = (isset($data->installation) && $data->installation != null) ? $data->installation->structure_60_40_2mm : '';
        $structure_80_40_2_mm = (isset($data->installation) && $data->installation != null) ? $data->installation->structure_80_40_2mm : '';
        $structure_others = (isset($data->installation) && $data->installation != null) ? $data->installation->structure_others : '';
        $data->installation_date = $installation_date;
        $data->penal_company = $penal_company;
        $data->penal_model_no = $penal_model_no;
        $data->penal_type = $penal_type;
        $data->penal_watt = $penal_watt;
        $data->penal_nos = $penal_nos;
        $data->penal_sr_no = $penal_sr_no;
        $data->total_kw = $total_kw;
        $data->type_of_inverter = $type_of_inverter;
        $data->no_of_inverter = $no_of_inverter;
        $data->make_of_inverter = $make_of_inverter;
        $data->inverter_model_no = $inverter_model_no;
        $data->inverter_kw = $inverter_kw;
        $data->inverter_sr_no = $inverter_sr_no;
        $data->inverter_voltage = $inverter_voltage;
        $data->cable_dc = $cable_dc;
        $data->cable_ac = $cable_ac;
        $data->cable_la = $cable_la;
        $data->cable_earthing = $cable_earthing;
        $data->dc_side = $dc_side;
        $data->ac_Side = $ac_Side;
        $data->la_Earthing = $la_Earthing;
        $data->phase_to_earth = $phase_to_earth;
        $data->phase_to_phase = $phase_to_phase;
        $data->structure_40_40_2_mm = $structure_40_40_2_mm;
        $data->structure_60_40_2_mm = $structure_60_40_2_mm;
        $data->structure_80_40_2_mm = $structure_80_40_2_mm;
        $data->structure_others = $structure_others;
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
            'District',
            'Taluka',
            'Pincode',
            'Invoice No',
            'Date',
            'Agent Sales Person',
            'Installation Asign Person',
            'Application Status',
            'Installation Date',
            'Panel Company',
            'Panel Model no',
            'Panel Type',
            'Panel Watt',
            'Panel Nos',
            'Panel Sr No',
            'Total KW',
            'Type Of Inverter',
            'No of Inverter',
            'Make Of Inverter',
            'Inverter Model no',
            'Inverter KW',
            'Inverter Sr No',
            'Inverter Voltage',
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
            'Structure others',
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
            $row->district->name,
            $row->taluka->name,
            $row->pin_code,
            $row->invoice_no,
            $row->installation_date,
            $row->agentsalesperson_name,
            $row->installer,
            $row->status,
            $row->installation_date,
            $row->penal_company,
            $row->penal_model_no,
            $row->penal_type,
            $row->penal_watt,
            $row->penal_nos,
            $row->penal_sr_no,
            $row->total_kw,
            $row->type_of_inverter,
            $row->no_of_inverter,
            $row->make_of_inverter,
            $row->inverter_model_no,
            $row->inverter_kw,
            $row->inverter_sr_no,
            $row->inverter_voltage,
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
            $row->structure_others,
        ];
    }
}
