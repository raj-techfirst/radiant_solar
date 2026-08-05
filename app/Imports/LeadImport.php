<?php

namespace App\Imports;

use App\Models\CompanyProfile;
use App\Models\LeadMaster;
use App\Models\LeadTransection;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class LeadImport implements ToModel, WithHeadingRow
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        $companyFind = CompanyProfile::where('user_id', Auth::id())->first();
        if ($companyFind->user_type == 'O') {
            $company_profile_id = $companyFind->id;
            // $statusMaster = StatusMaster::where('company_profile_id', $companyFind->id)->orderBy('id', 'ASC')->first();
        } else {
            $company_profile_id = $companyFind->parent_id;
            // $statusMaster = StatusMaster::where('company_profile_id', $companyFind->parent_id)->orderBy('id', 'ASC')->first();
        }
        
        if ($row['lead_title'] != "") {
            $leadMaster = new LeadMaster([
                'lead_title' => $row['lead_title'],
                'name' => $row['name'],
                'last_name' => $row['last_name'],
                'mobile' => $row['mobile'],
                'email' => $row['email'],
                'lead_value' => $row['lead_value'],
                'user_id' => Auth::id(),
                'company_profile_id' => $company_profile_id,
                'status_master_id' => 0,
                'assign_id' => $companyFind->id,
                'status' => '0',
            ]);
            $leadMaster->save();
            $leadTransection = new LeadTransection();
            $leadTransection->lead_master_id = $leadMaster->id;
            $leadTransection->assign_id = $companyFind->id;
            $leadTransection->assign_by = $company_profile_id;
            $leadTransection->save();
            return $leadMaster;
        }
    }
}
