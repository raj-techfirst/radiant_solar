<?php

namespace App\Exports;

use App\Models\AgentSalesPerson;
use App\Models\CompanyProfile;
use App\Models\LeadMaster;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class LeadExport implements FromCollection, WithHeadings
{
    private $request;
    public function __construct($request)
    {
        $this->request = $request;
    }

    public function collection()
    {
        $where = "1 = 1";
        $company = CompanyProfile::where('user_id', Auth::id())->first();
        if ($company->user_type == 'M') {
            $agent = AgentSalesPerson::where('user_id', Auth::id())->first();
            $agentIds = [$agent->id];
            $assignIds = [$agent->id];
            $sales = CompanyProfile::select('id', 'user_id')->where('manager_id', $company->id)->get();
            if ($sales->count() > 0) {
                foreach ($sales as $k => $v) :
                    array_push($agentIds, $v->user_id);
                    array_push($assignIds, $v->id);
                endforeach;
            }
            if ($this->request->assign == "") {
            $where .= ' AND (agent_sales_person_id IN(' . implode(',', $agentIds) . ') OR assign_id IN(' . implode(',', $assignIds) . '))';
            }
        }
        if ($company->user_type == 'S') {
            $agent = AgentSalesPerson::where('user_id', Auth::id())->first();
            $id = $agent->id;
            $where .= ' AND ((agent_sales_person_id = ' . $id . ' OR assign_id = ' . $id . ') OR (assign_id = ' . $company->id . '))';
        }
        $query = LeadMaster::select('lead_masters.id', 'assign_id', 'lead_masters.name', 'mobile', 'address', 'kw', 'reference', 'last_contacted', 'reminder_date', 'agent_sales_people.name as agent_sales_person', 'status','lead_status_id')
            ->leftJoin('agent_sales_people', 'lead_masters.agent_sales_person_id', '=', 'agent_sales_people.id')->whereRaw($where);
      /*   if ($this->request->mstatus == "") {
            $query->whereNotIn('status', ['1']);
        }
        if ($this->request->mstatus == "1") {
            $query->where('status', '1');
        } */
        if ($this->request->from_date != "" && $this->request->to_date == '') {
            $query->where('created_at', '>=', date('Y-m-d', strtotime($this->request->from_date)));
            $query->where('created_at', '<=', date('Y-m-d'));
        }
        if ($this->request->from_date != "" && $this->request->to_date != '') {
            $query->where('created_at', '>=', date('Y-m-d', strtotime($this->request->from_date)));
            $query->where('created_at', '<=', date('Y-m-d', strtotime($this->request->to_date)));
        }
        if ($this->request->from_date == "" && $this->request->to_date != '') {
            $query->where('created_at', '<=', date('Y-m-d', strtotime($this->request->to_date)));
        }
       /* if ($this->request->status != "") {
            $query->where('status', $this->request->status);
        } */
        if ($this->request->assign != "") {
            $query->where('agent_sales_person_id', $this->request->assign);
        }
        $lead = $query->get();
        foreach ($lead as $value) {
			$name = $value->company->user->name ?? '';
			$name .= ' ';
			$name .= $value->company->user->last_name ?? '';
			
            $value['assign_id'] = $name;
            $value['name'] = $value->name . ' ' . $value->last_name;
            $value['mobile'] = $value->mobile;
            $value['address'] = $value->address;
            $value['kw'] = $value->kw;
            $value['reference'] = $value->reference;
            $value['agent_sales_person'] = $value->agent_sales_person;

            if (!is_null($value->last_contacted)) {
                $value['last_contacted'] = date('d-m-Y', strtotime($value->last_contacted));
            } else {
                $value['last_contacted'] = "";
            }

            if (!is_null($value->reminder_date)) {
                $value['reminder_date'] = date('d-m-Y', strtotime($value->reminder_date));
            } else {
                $value['reminder_date'] = "";
            }
             /* if ($value->status == '0') {
                $value['status'] = 'New';
            } elseif ($value->status == '1') {
                $value['status'] = 'Completed';
            } elseif ($value->status == '2') {
                $value['status'] = 'Not Interested';
            } elseif ($value->status == '3') {
                $value['status'] = 'Next Follow up';
            } elseif ($value->status == '4') {
                $value['status'] = 'Site Vsit';
            } elseif ($value->status == '5') {
                $value['status'] = 'Make Quatation';
            } */
			            $value['status'] = $value->leadStatus->name ?? '';
            unset($value->website, $value->category_id, $value->product_id, $value->source_id, $value->tags, $value->last_name, $value->last_name, $value->notes, $value->id, $value->user_id, $value->manager_id, $value->company_profile_id, $value->category, $value->sourceMaster, $value->statusMaster, $value->state, $value->city, $value->status_master_id, $value->created_at, $value->updated_at, $value->deleted_at,  $value->lead_status_id);
        }
        return $lead;
    }

    public function headings(): array
    {
        return [
            'Assign User',
            'Name',
            'Mobile',
            'Address',
            'KW',
            'Reference',            
            'Last Contacted',
            'Reminder Date',
            'Agent / Sales Person',
            'Status',
        ];
    }
}
