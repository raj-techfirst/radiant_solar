<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AgentSalesPerson;
use App\Models\Bank;
use App\Models\Category;
use App\Models\City;
use App\Models\CompanyProfile;
use App\Models\erp\BOM;
use App\Models\InstallationImage;
use App\Models\InstallationInvater;
use App\Models\InstallationPenal;
use App\Models\InvaterImage;
use App\Models\InveterCompany;
use App\Models\ItemGroup;
use App\Models\LeadMaster;
use App\Models\LeadStatus;
use App\Models\PenalCompany;
use App\Models\PenalImage;
use App\Models\PenalType;
use App\Models\PenalWatt;
use App\Models\Product;
use App\Models\SalesMaster;
use App\Models\SalesQuatation;
use App\Models\Source;
use App\Models\State;
use App\Models\Task;
use App\Models\Unit;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class ListApiController extends Controller
{
    public function dashboard()
    {
        $where = "1 = 1";
        $task_where = "1 = 1";

        $companyFind = CompanyProfile::where('user_id', Auth::id())->first();
        if (!is_null($companyFind)) {
            if ($companyFind->user_type == 'O') {
                $id = $companyFind->id;
                $where .= ' AND lead_masters.company_profile_id =' . $companyFind->id;
                $task_where .= ' AND tasks.company_profile_id =' . $companyFind->id;
            } else if ($companyFind->user_type == 'M') {
                $id = $companyFind->parent_id;
                $where .= ' AND lead_masters.company_profile_id = ' . $companyFind->parent_id . ' AND lead_masters.manager_id = ' . $companyFind->id;
                $task_where .= ' AND tasks.company_profile_id = ' . $companyFind->parent_id . ' AND tasks.manager_id = ' . $companyFind->id;
            } else {
                $id = $companyFind->parent_id;
                $where .= ' AND lead_masters.assign_id =' . $companyFind->id . ' AND lead_masters.company_profile_id =' . $companyFind->parent_id;
                $task_where .= ' AND tasks.assign_id =' . $companyFind->id . ' AND tasks.company_profile_id =' . $companyFind->parent_id;
            }

            $user = CompanyProfile::where('parent_id', $id)->count();
            $category = Category::where('company_profile_id', $id)->count();
            $product = Product::where('company_profile_id', $id)->count();

            // start lead statistics
            $lead = LeadMaster::whereRaw($where)->count();
            $leadDone = LeadMaster::where('status', '1')->whereRaw($where)->count();
            $leadFollow = LeadMaster::where('status', '3')->whereRaw($where)->count();
            $newLead = LeadMaster::where('status', '0')->whereRaw($where)->count();
            // end lead statistics

            // start todayFollowUp
            $todayFollowUp = LeadMaster::select('id', 'name', 'last_name', 'mobile', 'lead_title', 'status', DB::raw("DATE_FORMAT(reminder_date,'%d-%m-%Y') AS reminder_date"), DB::raw("DATE_FORMAT(last_contacted,'%d-%m-%Y') AS last_contacted"))->whereDate('reminder_date', Carbon::today())->where('status', '3')->whereRaw($where)->orderBy('id', 'DESC')->get();
            foreach ($todayFollowUp as $value) {
                if ($value->status == '0') {
                    $value['status_title'] = 'New';
                } elseif ($value->status == '1') {
                    $value['status_title'] = 'Completed';
                } elseif ($value->status == '2') {
                    $value['status_title'] = 'Not Interested';
                } else {
                    $value['status_title'] = 'Next Follow up';
                }

                $value['last_contacted_date'] = date('d', strtotime($value->last_contacted));
                $value['last_contacted_month'] = date('M', strtotime($value->last_contacted));
                $value['last_contacted_year'] = date('Y', strtotime($value->last_contacted));

                $value['reminder_date_date'] = date('d', strtotime($value->reminder_date));
                $value['reminder_date_month'] = date('M', strtotime($value->reminder_date));
                $value['reminder_date_year'] = date('Y', strtotime($value->reminder_date));
            }
            // end todayFollowUp

            // start task statistics
            $task = Task::whereRaw($task_where)->count();
            $taskPending = Task::where('status', '1')->whereRaw($task_where)->count();
            $taskProgress = Task::where('status', '2')->whereRaw($task_where)->count();
            $taskComplete = Task::where('status', '3')->whereRaw($task_where)->count();
            $taskCancel = Task::where('status', '4')->whereRaw($task_where)->count();
            //end task statistics

            // start today task details
            $todayTask = Task::select('id', 'assign_id', 'priority', 'status', 'task_name', 'product_id', 'description', 'hours', 'minutes', DB::raw("DATE_FORMAT(task_date,'%d-%m-%Y') AS task_date"), DB::raw("DATE_FORMAT(expiry_date,'%d-%m-%Y') AS expiry_date"), DB::raw("DATE_FORMAT(timespand,'%hH:%iM') AS timespand"))
                ->with('product', 'company')
                ->whereDate('task_date', Carbon::today())
                ->where('status', '!=', '4')
                ->whereRaw($task_where)
                ->orderBy('id', 'DESC')
                ->get();
            foreach ($todayTask as $value) {
                $value['timespand'] = str_pad($value->hours, 2, '0', STR_PAD_LEFT) . "H:" . str_pad($value->minutes, 2, '0', STR_PAD_LEFT) . "M";;

                $value['assign_user'] = $value->company->user->name . ' ' . $value->company->user->last_name;
                $value['product_name'] = ($value->product_id != '' && $value->product_id != 0) ? $value->product->product_name : 0;

                if ($value->priority == '1') {
                    $value['priority'] = 'High';
                } elseif ($value->priority == '2') {
                    $value['priority'] = 'Medium';
                } else {
                    $value['priority'] = 'Low';
                }

                if ($value->status == '1') {
                    $value['status_title'] = 'Pending';
                } elseif ($value->status == '2') {
                    $value['status_title'] = 'In Progress';
                } elseif ($value->status == '3') {
                    $value['status_title'] = 'Completed';
                } else {
                    $value['status_title'] = 'Cancelled';
                }

                $value['task_date_date'] = date('d', strtotime($value->task_date));
                $value['task_date_month'] = date('M', strtotime($value->task_date));
                $value['task_date_year'] = date('Y', strtotime($value->task_date));

                $value['expiry_date_date'] = date('d', strtotime($value->expiry_date));
                $value['expiry_date_month'] = date('M', strtotime($value->expiry_date));
                $value['expiry_date_year'] = date('Y', strtotime($value->expiry_date));

                unset($value->assign_id, $value->company, $value->product_id, $value->product);
            }
            //end today task details

            // start check basic details
            if ($category >= 1 && $product >= 1) {
                if ($companyFind->user_type == 'O') {
                    $company_detail['total_employee'] = $user;
                    $company_detail['total_category'] = $category;
                    $company_detail['total_product'] = $product;
                } else {
                    $company_detail['total_employee'] = null;
                    $company_detail['total_category'] = null;
                    $company_detail['total_product'] = null;
                }

                $lead_detail['total_lead'] = $lead;
                $lead_detail['total_completed_lead'] = $leadDone;
                $lead_detail['total_follow_up_lead'] = $leadFollow;
                $lead_detail['new_lead'] = $newLead;

                $task_detail['total_task'] = $task;
                $task_detail['total_task_pending'] = $taskPending;
                $task_detail['total_task_progress'] = $taskProgress;
                $task_detail['total_task_complete'] = $taskComplete;
                $task_detail['total_task_cancel'] = $taskCancel;

                $response = [
                    'status' => true,
                    'message' => 'Dashboard View',
                    'company_detail' => $company_detail,
                    'lead_detail' => $lead_detail,
                    'task_detail' => $task_detail,
                    'today_follow_up_lead' => $todayFollowUp,
                    'today_task' => $todayTask,
                ];
            } else {
                $detail['title'] = 'Complete your details';
                $detail['description_one'] = 'Before you go Further Add Category, Product and Status Details.';
                $detail['description_two'] = 'After You will able to view Employee, Lead etc. will available to you.';
                $detail['description_three'] = 'Afterwards Manager and Sales also will able to view the details.';
                $response = ['status' => true, 'message' => 'Dashboard View', 'detail' => $detail];
            }
            // end check basic details

            return response($response, 200);
        } else {
            $response = ['status' => false, 'message' => 'You are not authorized.'];
            return response($response, 401);
        }
    }

    public function salesMaster(Request $request)
    {
        $user = User::where('id', Auth::id())->first();

        $salesMasterQuery = SalesMaster::select('id','file_type', 'lead_master_id', 'sales_quatation_id', 'consumer_number', 'master_create_date', 'consumer_type', 'consumer_name',  'district_id', 'taluka_id', 'address', 'pin_code', 'contact_number', 'register_kw', 'reference', 'agent_sales_person_id', 'remark', 'installation_pending', 'installation_done', 'ragistration_portal', 'ragistration_number', 'feasibility_discom_sr_number', 'feasibility_amount', 'invoice_no', 'discom_sr_numbar', 'installation_date', 'installation_asian_person', 'sub_division_id', 'bom_id')
            ->with('district', 'taluka', 'subDivision', 'agentsalesperson', 'salesquatation', 'salesquatation.penalType', 'salesquatation.penalWatt', 'lead');

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
            if ($request->type == '') {
                $salesMasterQuery->whereIn('agent_sales_person_id', $agentIds);
            }
        }
        if ($company->user_type == 'S') {
            if ($request->type == '') {
                $agent = AgentSalesPerson::where('user_id', Auth::id())->first();
                $id = $agent->id;
                $salesMasterQuery->where('agent_sales_person_id', $id);
            }
        }

        if ($request->type == 1) {
            if ($company->user_type != 'O') {
                $salesMasterQuery->where('installation_asian_person', $user->id);
            }
            $salesMasterQuery->where('installation_pending', '1');
            $salesMasterQuery->where('installation_done', '0');
        }
        if ($request->type == 2) {
            if ($company->user_type != 'O') {
                $salesMasterQuery->where('installation_asian_person', $user->id);
            }
            $salesMasterQuery->where('installation_done', '1');
            $salesMasterQuery->where('meter_application_done', '0');
        }
        // filter code start
        if ($request->from_date != "" && $request->to_date == '') {
            $salesMasterQuery->where('master_create_date', '>=', date('Y-m-d 00:00:00', strtotime($request->from_date)));
            $salesMasterQuery->where('master_create_date', '<=', date('Y-m-d 23:59:59'));
        }
        if ($request->from_date != "" && $request->to_date != '') {
            $salesMasterQuery->where('master_create_date', '>=', date('Y-m-d 00:00:00', strtotime($request->from_date)));
            $salesMasterQuery->where('master_create_date', '<=', date('Y-m-d 23:59:59', strtotime($request->to_date)));
        }
        if ($request->from_date == "" && $request->to_date != '') {
            $salesMasterQuery->where('master_create_date', '<=', date('Y-m-d 23:59:59', strtotime($request->to_date)));
        }
        if ($request->consumer != "") {
            $salesMasterQuery->where(function ($query) use ($request) {
                $query->where('consumer_name', 'like', '%' . $request->consumer . '%')
                    ->orWhere('consumer_number', 'like', '%' . $request->consumer . '%')
                    ->orWhere('contact_number', 'like', '%' . $request->consumer . '%');
            });
        }
        if ($request->consumer_type != "") {
            $salesMasterQuery->where('consumer_type', $request->consumer_type);
        }
        if ($request->status != "") {
            $salesMasterQuery->where($request->status, "1");
        }
        if ($request->not_status != "") {
            $salesMasterQuery->where($request->not_status, "0");
        }

        if ($request->user != "") {
            $salesMasterQuery->where('agent_sales_person_id', $request->user);
        }
        // filter code end
        // jaydeep code
        $salesMaster = $salesMasterQuery->orderBy('id', 'DESC')->paginate(12);

        if ($salesMaster->count() > 0) {
            foreach ($salesMaster as $key => $value) :
                $value->master_create_date = date('d-m-Y', strtotime($value->master_create_date));
                $value->status = toGetSalesMasterLastStatus($value->id);
                // company name
                if ($value->salesquatation->penal_company_id != null) {
                    $hist = PenalCompany::whereIn('id', explode(',', $value->salesquatation->penal_company_id))->get();
                    if ($hist != '') {
                        $penel_companies_name =  "";
                        foreach ($hist as $ri) {
                            $penel_companies_name != "" && $penel_companies_name .= " / ";
                            $penel_companies_name .= $ri->name;
                        }
                        $value->salesquatation->penal_company_names = $penel_companies_name;
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
                        $value->salesquatation->inveter_company_names = $inveter_companies_name;
                    }
                }
                // Inveter company name

                if (!is_null($value->lead) && isset($value->lead->site_visit_images) && $value->lead->site_visit_images != "") {
                    $arr = explode(',', $value->lead->site_visit_images);
                    $array = [];
                    foreach ($arr as $k => $img) :
                        $array[] = asset('uploads/site_visit_images/' . $img);
                    endforeach;
                    $value->lead->site_visit_images_new = $array;
                    $value->lead->site_visit_images_original = explode(',', $value->lead->site_visit_images);
                }

                $value->bom_id = ($value->bom_id != null) ? $value->bom_id : 1;

            endforeach;
        }


        $response = ['status' => true, 'message' => 'Sales Master List', 'Sales Master' => $salesMaster->items()];
        return response($response, 200);
    }

    public function salesQuatation()
    {
        $salesQuatation = SalesQuatation::where('form_type', 'resident')->get();
        $salesQuatationroof = SalesQuatation::where('form_type', 'roof')->get();
        $salesQuatationtrading = SalesQuatation::where('form_type', 'trading')->get();
        foreach ($salesQuatation as $value) {
            unset($value->created_at, $value->updated_at, $value->deleted_at);
        }
        foreach ($salesQuatationroof as $value) {
            unset($value->created_at, $value->updated_at, $value->deleted_at);
        }
        foreach ($salesQuatationtrading as $value) {
            unset($value->created_at, $value->updated_at, $value->deleted_at);
        }
        $response = ['status' => true, 'message' => 'Sales Quatation List', 'Resident With Subsidy' => $salesQuatation, 'Solar Roof Top' => $salesQuatationroof, 'Trading' => $salesQuatationtrading];
        return response($response, 200);
    }

    public function agentSales()
    {
        $companyFind = CompanyProfile::where('user_id', Auth::id())->first();
        $agentWhere = "";
        if ($companyFind->user_type == 'M') {
            $id = $companyFind->id;
            $agentWhere .= 'company_profiles.user_id = ' . Auth::id() . ' OR  company_profiles.manager_id = ' . $id;
        }
        if ($companyFind->user_type == 'S') {
            $id = $companyFind->id;
            $manager_id = $companyFind->manager_id;
            $agentWhere .= 'company_profiles.id = ' . $id . ' OR  company_profiles.id = ' . $manager_id;
        }

        $q = CompanyProfile::select('agent_sales_people.*')->join('agent_sales_people', 'agent_sales_people.user_id', 'company_profiles.user_id');
        if ($agentWhere != "") {
            $q->whereRaw($agentWhere);
        }

        $agentSalesPerson = $q->get();

        $response = ['status' => true, 'message' => 'Agent Sales Person List', 'Agent Sales Person' => $agentSalesPerson];
        return response($response, 200);
    }

    public function assign()
    {
        $where = "1 = 1";
        $companyFind = CompanyProfile::where('user_id', Auth::id())->first();
        if ($companyFind->user_type == 'O') {
            $id = $companyFind->id;

			$user = Auth::user();
			$roleName = $user->getRoleNames()->first();
	if($roleName != "Accountant" && $roleName != "Office"){
            $where .= ' AND company_profiles.user_id =' . Auth::id() . ' OR company_profiles.parent_id =' . $companyFind->id;
	}
        } else {
            $id = $companyFind->parent_id;
            $where .= ' AND (company_profiles.parent_id = ' . $id . ' AND company_profiles.id = ' . $id . ' OR (company_profiles.manager_id = ' . $companyFind->id . ' OR company_profiles.user_id = ' . $companyFind->user_id . ')) OR company_profiles.user_type = "O"';
        }
        $assign = CompanyProfile::with('user')->whereRaw($where)->get();
        if ($assign->count() > 0) {
            foreach ($assign as $value) {
                $value['assign_name'] = $value->user->name . ' ' . $value->user->last_name;
                unset($value->created_at, $value->updated_at, $value->deleted_at, $value->user, $value->assign);
            }
        }
        $response = ['status' => true, 'message' => 'Assign Person List', 'Assign Person' => $assign];
        return response($response, 200);
    }


    public function sourceList()
    {
        $source = Source::orderBy('id', 'DESC')->get();
        foreach ($source as $value) {
            unset($value->created_at, $value->updated_at, $value->deleted_at);
        }
        $response = ['status' => true, 'message' => 'Source List', 'source' => $source];
        return response($response, 200);
    }

    public function stateList()
    {
        $state = State::orderBy('state_name', 'ASC')->get();
        foreach ($state as $value) {
            $city = City::where('state_id', $value->id)->get();
            foreach ($city as $item) {
                unset($item->state_id, $item->created_at, $item->updated_at, $item->deleted_at);
            }
            $value['cities'] = $city;
            unset($value->created_at, $value->updated_at, $value->deleted_at);
        }
        $response = ['status' => true, 'message' => 'State List', 'state' => $state];
        return response($response, 200);
    }

    public function assignList()
    {
        $company_where = "1 = 1";
        $company = CompanyProfile::where('user_id', Auth::id())->first();
        if ($company->user_type == 'O') {
            $company_where .= ' AND company_profiles.id = ' . $company->id . ' OR company_profiles.parent_id = ' . $company->id;
        } else if ($company->user_type == 'M') {
            $company_where .= ' AND company_profiles.parent_id = ' . $company->parent_id . ' AND company_profiles.id = ' . $company->parent_id . ' OR (company_profiles.manager_id = ' . $company->id . ' OR company_profiles.user_id = ' . $company->user_id . ')';
        } else {
            $company_where .= ' AND company_profiles.id = ' . $company->id . ' AND company_profiles.parent_id =' . $company->parent_id;
        }
        $companyProfile = CompanyProfile::select('id', 'user_id')->with('user')->whereRaw($company_where)->orderBy('id', 'DESC')->get();
        foreach ($companyProfile as $value) {
            $value['name'] = $value->user->name . ' ' . $value->user->last_name;
            unset($value->user, $value->user_id);
        }
        $response = ['status' => true, 'message' => 'Assign List', 'employee' => $companyProfile];
        return response($response, 200);
    }

    public function unitList()
    {
        $unit = Unit::orderBy('id', 'DESC')->get();
        foreach ($unit as $value) {
            unset($value->created_at, $value->updated_at, $value->deleted_at);
        }
        $response = ['status' => true, 'message' => 'Unit List', 'Unit' => $unit];
        return response($response, 200);
    }

    public function clientList()
    {
        $where = "1 = 1";
        $company = CompanyProfile::where('user_id', Auth::id())->first();
        if ($company->user_type == 'O') {
            $id = $company->id;
            $where .= ' AND lead_masters.company_profile_id =' . $company->id;
        } else if ($company->user_type == 'M') {
            $id = $company->parent_id;
            $where .= ' AND lead_masters.company_profile_id = ' . $company->parent_id . ' AND lead_masters.manager_id = ' . $company->id;
        } else {
            $id = $company->parent_id;
            $where .= ' AND lead_masters.assign_id =' . $company->id . ' AND lead_masters.company_profile_id =' . $company->parent_id;
        }
        $leadMaster = LeadMaster::select('id', 'state_id', 'city_id', 'lead_title', 'name', 'last_name', 'mobile', 'email', 'company_name', 'address', 'website')->with('state', 'city')->whereRaw($where)->where('status', '!=', '2')->orderBy('id', 'DESC')->get();
        foreach ($leadMaster as $value) {
            $value['state_name'] = ($value->state_id != 0) ? $value->state->state_name : '';
            $value['city_name'] = ($value->city_id != 0) ? $value->city->city_name : '';
            unset($value->state, $value->city, $value->city_id, $value->state_id,);
        }
        $companyProfile = CompanyProfile::select('id', 'user_id', 'state_id', 'city_id', 'parent_id', 'manager_id')->with('user', 'state', 'city')->where('id', $id)->first();
        $companyProfile['user_name'] =  $companyProfile->user->name;
        $companyProfile['last_name'] =  $companyProfile->user->last_name;
        $companyProfile['company_name'] =  $companyProfile->user->company_name;
        $companyProfile['mobile'] =  $companyProfile->user->mobile;
        $companyProfile['email'] =  $companyProfile->user->email;
        $companyProfile['state_name'] = (!is_null($companyProfile->state_id)) ? $companyProfile->state->state_name : '';
        $companyProfile['city_name'] = (!is_null($companyProfile->city_id)) ? $companyProfile->city->city_name : '';
        unset($companyProfile->user_id, $companyProfile->user, $companyProfile->parent_id, $companyProfile->manager_id, $companyProfile->state_id, $companyProfile->city_id, $companyProfile->state, $companyProfile->city);
        $response = ['status' => true, 'message' => 'Client List', 'Client' => $leadMaster, 'companyProfile' => $companyProfile];
        return response($response, 200);
    }

    public function managerList()
    {
        $company = CompanyProfile::where('user_id', Auth::id())->first();
        $companyManager = CompanyProfile::select('id', 'user_id')->with('user')->where('parent_id', $company->id)->where('user_type', 'M')->orderBy('id', 'DESC')->get();
        foreach ($companyManager as $value) {
            $value['name'] = $value->user->name . ' ' . $value->user->last_name;
            unset($value->user, $value->user_id,);
        }
        $response = ['status' => true, 'message' => 'Manager List', 'Manager' => $companyManager];
        return response($response, 200);
    }

    public function penalCompany()
    {
        $penalCompany = PenalCompany::orderBy('id', 'DESC')->get();
        foreach ($penalCompany as $value) {
            unset($value->created_at, $value->updated_at, $value->deleted_at);
        }
        $response = ['status' => true, 'message' => 'Panel Company List', 'PenalCompany' => $penalCompany];
        return response($response, 200);
    }

    public function penalType(Request $request)
    {
        $penalType = PenalType::orderBy('id', 'DESC')->get();
        foreach ($penalType as $value) {
            unset($value->created_at, $value->updated_at, $value->deleted_at);
        }
        $response = ['status' => true, 'message' => 'Panel Type List', 'PenalType' => $penalType];
        return response($response, 200);
    }

    public function penalWatt(Request $request)
    {
        $penalWatt = PenalWatt::orderBy('id', 'DESC')->get();
        foreach ($penalWatt as $value) {
            unset($value->created_at, $value->updated_at, $value->deleted_at, $value->user_id);
        }
        $response = ['status' => true, 'message' => 'Panel Watt List', 'PenalWatt' => $penalWatt];
        return response($response, 200);
    }

    public function installationImage()
    {
        $installationImage = InstallationImage::orderBy('id', 'DESC')->get();
        foreach ($installationImage as $value) {
            unset($value->created_at, $value->updated_at, $value->deleted_at);
        }
        $response = ['status' => true, 'message' => 'Installation Image List', 'InstallationImage' => $installationImage];
        return response($response, 200);
    }

    public function invaterImage()
    {
        $invaterImage = InvaterImage::orderBy('id', 'DESC')->get();
        foreach ($invaterImage as $value) {
            unset($value->created_at, $value->updated_at);
        }
        $response = ['status' => true, 'message' => 'Invater Image List', 'InvaterImage' => $invaterImage];
        return response($response, 200);
    }

    public function penalImage()
    {
        $penalImage = PenalImage::orderBy('id', 'DESC')->get();
        foreach ($penalImage as $value) {
            unset($value->created_at, $value->updated_at);
        }
        $response = ['status' => true, 'message' => 'Panel Image List', 'PenalImage' => $penalImage];
        return response($response, 200);
    }
    public function installationInvater()
    {
        $installationInvater = InstallationInvater::orderBy('id', 'DESC')->get();
        foreach ($installationInvater as $value) {
            unset($value->created_at, $value->updated_at, $value->deleted_at);
        }
        $response = ['status' => true, 'message' => 'InstallationInvater List', 'InstallationInvater' => $installationInvater];
        return response($response, 200);
    }

    public function installationPenal()
    {
        $installationPenal = InstallationPenal::orderBy('id', 'DESC')->get();
        foreach ($installationPenal as $value) {
            unset($value->created_at, $value->updated_at, $value->deleted_at);
        }
        $response = ['status' => true, 'message' => 'Installation Panel List', 'InstallationPenal' => $installationPenal];
        return response($response, 200);
    }

    public function invater()
    {
        $invater = InveterCompany::orderBy('id', 'DESC')->get();
        foreach ($invater as $value) {
            unset($value->created_at, $value->updated_at, $value->deleted_at);
        }
        $response = ['status' => true, 'message' => 'invater Company List', 'Invater Company' => $invater];
        return response($response, 200);
    }



    public function leadList()
    {
        $lead = LeadMaster::where('status', '1')->select('id', 'name', 'mobile')->get();
        foreach ($lead as $value) {
            unset($value->created_at, $value->updated_at, $value->deleted_at);
        }
        $response = ['status' => true, 'message' => 'Lead List', 'Lead' => $lead];
        return response($response, 200);
    }

    public function pastLead(Request $request)
    {
        try {
            $company = CompanyProfile::where('user_id', Auth::id())->first();
            if ($company->user_type == 'O') {
                $id = $company->id;
            } else if ($company->user_type == 'M') {
                $id = $company->parent_id;
            } else {
                $id = $company->parent_id;
            }
            $lead = LeadMaster::with('agentsalesperson', 'companyProfile', 'followUp.image')->select('id', 'company_profile_id', 'mobile', 'name', 'address', 'kw', 'reference', 'agent_sales_person_id', 'assign_id', 'status')
                ->where('mobile', $request->mobile)
                ->orderBy('id', 'DESC')->get();


            foreach ($lead as $value) {
                if ($value->status == '0') {
                    $value['status_title'] = "New";
                } elseif ($value->status == '1') {
                    $value['status_title'] = "Completed";
                } elseif ($value->status == '2') {
                    $value['status_title'] = "Not Interested";
                } else {
                    $value['status_title'] = "Next Follow up";
                }
                $value['assign_name'] = $value->companyProfile->user->name;
                $value['agent_sales_person'] = $value->agentsalesperson->name;
                unset($value->agentsalesperson, $value->companyProfile);
                foreach ($value->followUp as $item) {

                    $item['reminder_date_date'] = date('d', strtotime($item->reminder_date));
                    $item['reminder_date_month'] = date('M', strtotime($item->reminder_date));
                    $item['reminder_date_year'] = date('Y', strtotime($item->reminder_date));

                    $item['created_at_date'] = date('d', strtotime($item->created_at));
                    $item['created_at_month'] = date('M', strtotime($item->created_at));
                    $item['created_at_year'] = date('Y', strtotime($item->created_at));

                    $path = asset('upload/follow_up_image/');
                    foreach ($item->image as $img) {
                        $img['follow_up_image'] = !is_null($img->image) ? $path . '/' . $img->image : '';
                        unset($img->deleted_at, $img->created_at, $img->updated_at, $img->image, $img->lead_master_id, $img->follow_up_id);
                    }
                    unset($item->deleted_at, $item->created_at, $item->updated_at, $item->call_recording, $item->reminder_date);
                }
            }

            $response = ['status' => true, 'message' => 'Lead List', 'Lead' => $lead];
            return response($response, 200);
        } catch (Exception $e) {
            $response = ['status' => false, 'message' => 'Something went wrong. Please try again.'];
            return response($response, 500);
        }
    }
   public function item()
    {
       // $item = Product::select('id', 'name', 'gst_rate')->orderBy('id', 'DESC')->get();

        $item = Product::select('products.id', 'products.name', 'products.gst_rate', 'units.unit_name')
    ->leftJoin('units', 'products.unit_id', '=', 'units.id')
    ->orderBy('products.id', 'DESC')
    ->get();

        $response = ['status' => true, 'message' => 'Item List', 'Item' => $item];
        return response($response, 200);
    }
    public function bom()
    {
        $item = BOM::select('id', 'bom_name')->get();
        $response = ['status' => true, 'message' => 'ItBOMem List', 'BOM' => $item];
        return response($response, 200);
    }
    public function itemGroup()
    {
        $itemGroup = ItemGroup::with('panel_company', 'panel_type', 'panel_watt', 'inveter_company', 'unit')->get();
        $data = [];
        if ($itemGroup->count() > 0) {
            foreach ($itemGroup as $k => $v):
                $data[] = [
                    "id" => $v->id,
                    "name" => getItemGropName($v, 0),
                    "gst_rate" => $v->gst_rate,
                    "unit_name" => $v->unit->unit_name
                ];
            endforeach;
        }
        $response = ['status' => true, 'message' => 'Item Group List', 'ItemGroup' => $data];
        return response($response, 200);
    }
    public function bankList(Request $request)
    {

        $bank = Bank::orderBy('id', 'DESC')->get();
        foreach ($bank as $value) {
            if ($value->default == '1') {
                $value['defaultBank'] = 'Yes';
            } else {
                $value['defaultBank'] = 'No';
            }
            unset($value->created_at, $value->updated_at, $value->deleted_at);
        }
        $response = ['status' => true, 'message' => 'Bank List', 'Bank' => $bank];
        return response($response, 200);
    }

    public function getSalesQuotationStatus()
    {
        $response = ['status' => true, 'message' => 'Sales Quotation Status', 'quotation_status' => salesQuotationStatus()];
        return response($response, 200);
    }

    public function getLeadStatus()
    {
        $response = ['status' => true, 'message' => 'Lead Status', 'lead_status' => LeadStatus::select('id', 'name', 'class_name', 'color_code', 'icon_class')->get()];
        return response($response, 200);
    }
}
