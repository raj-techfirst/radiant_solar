<?php

namespace App\Imports;

use App\Models\PaymetCollection;
use App\Models\SalesMaster;
use App\Models\SalesQuatation;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;


class Salesorder implements ToModel, WithHeadingRow
{
    private $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function model(array $row)
    {
        if (isset($row['c_no']) && $row['c_no'] != "") {
            $salesMaster = SalesMaster::where('consumer_number', $row['c_no'])->first();
            if (!is_null($salesMaster)) {
                $paymetCollection = new PaymetCollection();
                $paymetCollection->sales_master_id  = $salesMaster->id;
                $paymetCollection->payment_type = $row['type'];
                $paymetCollection->amount = $row['amount'];
                $paymetCollection->payment_date = ($row['date'] != "") ? date('Y-m-d', (($row['date'] - 25569) * 86400)) : '';

                if ($row['type'] == 'Cheque') {
                    $paymetCollection->cheque_number = $row['nos'];
                } else if ($row['type'] == 'NEFT') {
                    $paymetCollection->utr_number = $row['nos'];
                } else if ($row['type'] == 'UPI') {
                    $paymetCollection->upi_id = $row['nos'];
                } else {
                    $paymetCollection->upi_id = $row['nos'];
                }

                $paymetCollection->bank_name = $row['bank_name'];
                $paymetCollection->branch_name = $row['branch_name'];

                $paymetCollection->remark = '';

                if ($row['status'] == 'Approved') {

                    $final_amt = (float)$salesMaster->pending_amonut - (float)$row['amount'];

                    $salesMaster->pending_amonut = $final_amt;
                    if ($final_amt == "0") {
                        $salesMaster->payment_receveid = "1";

                        if ($salesMaster->feasibility_approved == "1" && $salesMaster->payment_receveid == "1") {
                            $salesMaster->dispach_pending_list = "1";
                        }

                        if (!empty($salesMaster->installation_asian_person) && $salesMaster->dispach_pending_list == "1") {
                            $salesMaster->installation_pending = "1";
                        } 
                    }
                    
                    $salesMaster->save();
                    $paymetCollection->status = 1;
                }

                $result = $paymetCollection->save();
            }
        }
        return array();
    }

    public function model_old(array $row)
    {
        if (isset($row['consumer_number']) && $row['consumer_number'] != "") {
            $arr = [
                'form_type' => 'resident',
                'user_id' => Auth::id(),
                'name' => strtoupper($row['consumer_name']),
                'mobile' => $row['contact_number'],
                'address' => $row['address'],
                'penal_company_id' => penalCompany($row['penal_company_name']),
                'penal_type_id' =>  penalType($row['penal_type']),
                'penal_watt_id' => penalWatt($row['penal_watt']),
                'penal_nos' => $row['penal_nos'],
                'pv_capacity_kw' => $row['pv_capacity_kw'],
                'inveter_company_id' => inveterCompany($row['inveter_company_name']),
                'inveter_capacity' => $row['inveter_capacity'],
                'no_of_inveter' => $row['no_of_inveter'],
                'structure' => $row['structure'],
                'meter_charges' => $row['meter_charges'],
                'registration_fee' => $row['registration_free'],
                'rate_per_kw' => number_format($row['total_system_cost'] / $row['pv_capacity_kw'], 2, '.', ''),
                'gst' => 'Including',
                'quatation_type' => $row['consumer_type'],
                'bank_id' => 1,
                'common_meter' => $row['common_meter'],
                'total_amount' => $row['total_amount'],
                'reference' => strtoupper($row['reference']),
                'total_system_cost' => $row['total_system_cost'],
                'subsidy' => $row['subsidy'],
                'agent_sales_person_id' => $row['agent']
            ];
            $salesQuatation = SalesQuatation::create($arr);


            $salesMaster = new SalesMaster();
            $salesMaster->user_id = Auth::id();
            $salesMaster->sales_quatation_id = $salesQuatation->id;
            $salesMaster->agent_sales_person_id = $row['agent'];
            $salesMaster->consumer_number = $row['consumer_number'];
            $salesMaster->master_create_date = date('Y-m-d');
            $salesMaster->consumer_type = $row['consumer_type'];
            $salesMaster->consumer_name = $row['consumer_name'];
            $salesMaster->district_id = district($row['district']);
            $salesMaster->taluka_id = taluka($row['taluka']);
            $salesMaster->village_id = 0;
            $salesMaster->pin_code = $row['pincode'];
            $salesMaster->contact_number = $row['contact_number'];
            $salesMaster->register_kw = $row['registation_kw'];
            $salesMaster->email = $row['email'];
            $salesMaster->address = $row['address'];
            $salesMaster->aadhaar_number = $row['aadhaar_number'];
            $salesMaster->bank_name = $row['bank_name'];
            $salesMaster->bank_account = $row['bank_account'];
            $salesMaster->ifsc_code = $row['ifsc_code'];
            $salesMaster->contracted_load = $row['contracted_load'];
            $salesMaster->phase = $row['phase'];
            $salesMaster->sub_division_id = subDivision($row['sub_division']);
            $salesMaster->division = $row['division'];
            $salesMaster->circle = $row['circle'];
            $salesMaster->discom = $row['discom'];
            $salesMaster->reference = $row['reference'];
            $salesMaster->total_amount = $row['total_amount'];
            $salesMaster->pending_amonut = $row['total_amount'];
            $salesMaster->remark = $row['remark'];
            $salesMaster->ragistration_portal = $row['ragistration_portal'];
            $salesMaster->ragistration_number = $row['ragistration_numbar'];

            if (!empty($row['ragistration_portal']) && !empty($row['ragistration_numbar'])) {
                $salesMaster->pending_approvel = "1";
            }

            $salesMaster->ragistration_date = ($row['date'] != "") ? date('Y-m-d', (($row['date'] - 25569) * 86400)) : '';

            $salesMaster->feasibility_discom_sr_number = $row['discom_sr_number'];
            $salesMaster->discom_sr_numbar = $row['discom_sr_number'];
            $salesMaster->feasibility_amount = $row['feasibility_amount'];

            if (!empty($row['discom_sr_number']) && !empty($row['feasibility_amount'])) {
                $salesMaster->feasibility_approved = "1";
            }

            $salesMaster->feasibility_date = ($row['discom_sr_date'] != "") ? date('Y-m-d', (($row['discom_sr_date'] - 25569) * 86400)) : '';
            $salesMaster->invoice_no = $row['invoice_no'];
            $salesMaster->invoice_date = ($row['invoice_date'] != "") ? date('Y-m-d', (($row['invoice_date'] - 25569) * 86400)) : '';
            $salesMaster->payment_ref_number = $row['payment_ref_number'];
            $salesMaster->installation_date = ($row['installation_date'] != "") ? date('Y-m-d', (($row['installation_date'] - 25569) * 86400)) : '';
            $salesMaster->installation_asian_person = installationParson($row['installation_parson']);
            $salesMaster->save();

            return $salesQuatation;
        } else {
            return false;
        }
    }
}
