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

class SalesOrderExport implements FromCollection, WithHeadings,WithMapping
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
        $query = SalesMaster::select('*','id as sid')->with('bom','district','district.state', 'taluka', 'village', 'subDivision', 'agentsalesperson', 'salesquatation', 'salesquatation.penalWatt');
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
        if ( $this->request->input('status') != "") {
            $query->where( $this->request->input('status'), "1");
        }
        if ($this->request->input('not_status') != "") {
            $query->where($this->request->input('not_status'), "0");
        }
		 if ($this->request->input('ragistration_portal') != "") {
            $ragistration_portal = $this->request->input('ragistration_portal');
            if ($ragistration_portal == "Other") {
                $query->where(function ($q) use ($ragistration_portal) {
                    $q->where('ragistration_portal', 'like', '%' . $ragistration_portal . '%')
                        ->orWhere('ragistration_portal', '')
                        ->orWhere('ragistration_portal', null);
                });
            } else {
                $query->where('ragistration_portal', 'like', '%' . $ragistration_portal . '%');
            }
        }
		
       $data = $query->orderBy('id', 'DESC')->get();
        foreach ($data as $key => $value) :
            $value['feasibility_date'] = (!is_null($value->feasibility_date) && $value->feasibility_date != '1970-01-01' && $value->feasibility_date != '0000-00-00') ? date('d-m-Y', strtotime($value->feasibility_date)) : '';
            $value['installation_date'] = (!is_null($value->installation_date) && $value->installation_date != '1970-01-01' && $value->installation_date != '0000-00-00') ? date('d-m-Y', strtotime($value->installation_date)) : '';
            $value['installation_asian_person'] = installationAsignPerson($value->installation_asian_person);

			$value['master_date'] = (!is_null($value->master_create_date) && $value->master_create_date != '1970-01-01' && $value->master_create_date != '0000-00-00') ? date('d-m-Y', strtotime($value->master_create_date)) : '';
		    $value['ragistration_date'] = (!is_null($value->ragistration_date) && $value->ragistration_date != '1970-01-01' && $value->ragistration_date != '0000-00-00') ? date('d-m-Y', strtotime($value->ragistration_date)) : '';
			
			$value['meter_application_date'] = (!is_null($value->meter_application_date) && $value->meter_application_date != '1970-01-01' && $value->meter_application_date != '0000-00-00') ? date('d-m-Y', strtotime($value->meter_application_date)) : '';
			$value['meter_installation_date'] = (!is_null($value->meter_installation_date) && $value->meter_installation_date != '1970-01-01' && $value->meter_installation_date != '0000-00-00') ? date('d-m-Y', strtotime($value->meter_installation_date)) : '';
			$value['subsidy_disbursement_date'] = (!is_null($value->subsidy_disbursement_date) && $value->subsidy_disbursement_date != '1970-01-01' && $value->subsidy_disbursement_date != '0000-00-00') ? date('d-m-Y', strtotime($value->subsidy_disbursement_date)) : '';
			$value['subsidy_disbursement_verify_date'] = (!is_null($value->subsidy_disbursement_verify_date) && $value->subsidy_disbursement_verify_date != '1970-01-01' && $value->subsidy_disbursement_verify_date != '0000-00-00') ? date('d-m-Y', strtotime($value->subsidy_disbursement_verify_date)) : '';
           
            $value->state_name = $value->district->state->state_name;
            $value->boms = $value->bom->bom_name ?? '';


            $value->penal_company = '';
            $value->inveter_company = '';
            $value->subDivisionname  = (isset($value->subDivision) && (isset($value->subDivision[0]))) ? $value->subDivision[0]->name : '';
            $value->division_name = (isset($value->subDivision) && (isset($value->subDivision[0]))) ? $value->subDivision[0]->division_name : '';
            $value->circle_name = (isset($value->subDivision) && (isset($value->subDivision[0]))) ? $value->subDivision[0]->circle_name : '';
            $value->discom =(isset($value->subDivision) && (isset($value->subDivision[0]))) ? $value->subDivision[0]->discom : '';
            $value->penal_watt = (isset($value->salesquatation->penalWatt) && $value->salesquatation->penalWatt->name) ? $value->salesquatation->penalWatt->name : '';
            $value->status = toGetSalesMasterLastStatus($value->sid);
            // company name
            if ($value->salesquatation->penal_company_id != null) {
                $hist = PenalCompany::whereIn('id', explode(',', $value->salesquatation->penal_company_id))->get();
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
            if ($value->salesquatation->inveter_company_id != null) {
                $hist = InveterCompany::whereIn('id', explode(',', $value->salesquatation->inveter_company_id))->get();
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
        endforeach;
        return $data;
    }
    public function headings(): array
    {
        return [
            'Consumer Number',
			'Master Date',
			'Ragistration Date',
            'Ragistration Numbar',
            'Ragistration Portal',
            'Consumer Type',
            'Consumer Name',
            'Address',
            'State',
            'District',
            'Taluka',
            'Pincode',
            'Contact Number',
            'Email',
            'Aadhaar Number',
            'Bank Name',
            'Bank Account',
            'IFSC Code',
            'Contracted Load',
            'Phase',
            'Sub Division',
            'Division',
            'Circle',
            'DISCOM',
            'Reference',
            'Agent Sales Person',
            'Total Amount',
            'Registation Kw',
            'PV Capacity Kw',
            'Remark',
            'Panel Company Name',
            'Panel Watt',
            'Panel Nos',
            'Inveter Company Name',
            'Inveter Capacity',
            'No Of Inveter',
            'Structure',
            'Common Meter',
            'Discom Sr Number',
            'Discom Sr Date',
            'Feasibility Amount',
            'Invoice No',
            'Date',
            'Application Status',
            'Payment Ref. Number',
            'Payment Date',
            'Installar Name',
            'BOM',
			'Meter Application Date',
			'Meter Installation Date',
			'Subsidy Verify Date',
			'Subsidy Disbursement Date',			
        ];
    }

    public function map($row): array
    {
        return [
            $row->consumer_number,
			$row->master_date,
			$row->ragistration_date,
            $row->ragistration_number,
            $row->ragistration_portal,
            $row->consumer_type,
            $row->consumer_name,
            $row->address,
            $row->state_name,
            $row->district->name,
            $row->taluka->name,
            $row->pin_code,
            $row->contact_number,
            $row->email,
            $row->aadhaar_number,
            $row->bank_name,
            $row->bank_account,
            $row->ifsc_code,
            $row->contracted_load,
            $row->phase,
            $row->subDivisionname,
            $row->division_name,
            $row->circle_name,
            $row->discom,
            $row->reference,
            $row->agentsalesperson->name,
            $row->total_amount,
            $row->register_kw,
            $row->salesquatation->pv_capacity_kw,
            $row->remark,
            $row->penal_company,
            $row->penal_watt,
            $row->salesquatation->penal_nos,
            $row->inveter_company,
            $row->salesquatation->inveter_capacity,
            $row->salesquatation->no_of_inveter,
            $row->salesquatation->structure,
            $row->salesquatation->common_meter,
            $row->discom_sr_numbar,
            $row->feasibility_date,
            $row->feasibility_amount,
            $row->invoice_no,
            $row->installation_date,
            $row->status,
            $row->payment_ref_number,
            $row->feasibility_date,
            $row->installation_asian_person,
            $row->boms,
            $row->meter_application_date,			
            $row->meter_installation_date,			
            $row->subsidy_disbursement_verify_date,
            $row->subsidy_disbursement_date,
			
        ];
    }
}
