<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Imports\LeadImport;
use App\Models\AgentSalesPerson;
use App\Models\CompanyProfile;
use App\Models\Estimate;
use App\Models\EstimateItem;
use App\Models\FollowUp;
use App\Models\LeadMaster;
use App\Models\LeadTransection;
use App\Models\SalesQuatation;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Intervention\Image\Facades\Image;

class LeadApiController extends Controller
{
    public function index(Request $request)
    {
        try {
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
                $where .= ' AND (agent_sales_person_id IN(' . implode(',', $agentIds) . ') OR assign_id IN(' . implode(',', $assignIds) . '))';
            }
            if ($company->user_type == 'S' || $company->user_type == 'C') {
                $agent = AgentSalesPerson::where('user_id', Auth::id())->first();
                $id = $agent->id;
                $where .= ' AND ((agent_sales_person_id = ' . $id . ' OR assign_id = ' . $id . ') OR (assign_id = ' . $company->id . '))';
            }
            $lead = LeadMaster::select('lead_masters.id',  'lead_masters.mobile', 'lead_masters.name', 'lead_masters.kw','lead_masters.agent_sales_person_id','lead_masters.reference','lead_masters.address','agent_sales_people.name as agent_sales_name', DB::raw("CONCAT(users.name, ' ', users.last_name) as assign_name"),'lead_statuses.name as status_name')
                ->leftJoin('agent_sales_people','agent_sales_people.id','lead_masters.agent_sales_person_id')
                ->leftJoin('company_profiles','company_profiles.id','lead_masters.assign_id')
                ->leftJoin('users','users.id','company_profiles.user_id')
                                ->leftJoin('lead_statuses','lead_statuses.id','lead_masters.lead_status_id')

                ->whereRaw($where);

            if ($request->s_date != "" && $request->e_date == '') {
                $lead->where('lead_masters.created_at', '>=', date('Y-m-d 00:00:00', strtotime($request->s_date)));
                $lead->where('lead_masters.created_at', '<=', date('Y-m-d 23:59:59'));
            }
            if ($request->s_date != "" && $request->e_date != '') {
                $lead->where('lead_masters.created_at', '>=', date('Y-m-d 00:00:00', strtotime($request->s_date)));
                $lead->where('lead_masters.created_at', '<=', date('Y-m-d 23:59:59', strtotime($request->e_date)));
            }
            if ($request->s_date == "" && $request->e_date != '') {
                $lead->where('lead_masters.created_at', '<=', date('Y-m-d 23:59:59', strtotime($request->e_date)));
            }

            if ($request->status != "") {
                $lead->where('lead_masters.lead_status_id', $request->status);
            }
            if ($request->user != "") {
                $lead->where('lead_masters.agent_sales_person_id', $request->user);
            }

            if ($request->consumer != "") {
                $lead->where(function ($q) {
                    $consumer = request()->input('consumer');
                    $q->where('lead_masters.name', 'like', '%' . $consumer . '%')
                        ->orWhere('lead_masters.mobile', 'like', '%' . $consumer . '%');
                });
            }

            if ($request->type == 1) {
                $lead->where('lead_masters.site_visiter', null);
                $lead->where('lead_masters.lead_status_id', '4');
            }
            if ($request->type == 2) {
                $user = User::where('id', Auth::id())->first();
                if ($user->roles[0]->name == 'Dealer' || $user->roles[0]->name == 'Owner') {
                    $lead->where('lead_masters.site_visiter', "!=", null);
                } else {
                    $lead->where('lead_masters.site_visiter', Auth::id());
                }
            }
            if (isset($request->page)) {
                $lead = $lead->orderBy('id', 'DESC')->paginate(12);
            } else {
                $lead = $lead->orderBy('id', 'DESC')->get();
            }

            if (isset($request->page)) {
                $response = ['status' => true, 'message' => 'Lead List', 'Lead' => $lead->items()];
            } else {
                $response = ['status' => true, 'message' => 'Lead List', 'Lead' => $lead];
            }


            return response($response, 200);
        } catch (Exception $e) {
            $response = ['status' => false, 'message' => 'Something went wrong. Please try again.', 'e' => $e];
            return response($response, 500);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'mobile' => 'required',
            'assign_id' => 'required',

        ], [
            'mobile.required' => 'Enter mobile number',
            'assign_id.required' => 'Choose assign user'
        ]);

        if ($validator->fails()) {
            $response = ['status' => false, 'message' => 'Please input proper data.', 'errors' => $validator->errors()];
            return response($response, 200);
        }
        DB::beginTransaction();
        try {
            if (!is_null($request->id)) {
                $lead = LeadMaster::where('id', $request->id)->first();
                $response = ['status' => true, 'message' => 'Lead updated successfully.'];
            } else {
                $lead = new LeadMaster();
                $response = ['status' => true, 'message' => 'Lead added successfully.'];
            }
            $companyProfile = CompanyProfile::where('user_id', Auth::id())->first();
            $lead->user_id = Auth::id();
            $lead->company_profile_id = $companyProfile->id;
            $lead->mobile = $request->mobile;
            $lead->name = strtoupper($request->name);
            $lead->address = $request->address;
            $lead->kw = $request->kw;
            $lead->reference = strtoupper($request->reference);
            $lead->agent_sales_person_id = $request->agent_sales_person_id;
            $lead->assign_id = $request->assign_id;
            if ($request->lead_status != '') {
                $lead->status = $request->lead_status;
            } else {
                if (!isset($request->id)) {
                    $lead->status = '0';
                    $lead->status_master_id = 0;
                }
            }
            $lead->remark = $request->remark;
            $result = $lead->save();
            DB::commit();
            if (!is_null($result)) {
                return response($response, 200);
            } else {
                $response = ['status' => false, 'message' => 'Something went wrong. Please try again.'];
                return response($response, 200);
            }
        } catch (\Exception $e) {
            DB::rollback();
            $response = ['status' => false, 'message' => 'Something went wrong. Please try again.'];
            return response($response, 500);
        }
    }

    public function leadSync(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'leadSync' => 'required',
        ], [
            'leadSync.required' => 'Enter lead',
        ]);
        if ($validator->fails()) {
            $response = ['status' => false, 'message' => 'Please input proper data.', 'errors' => $validator->errors()];
            return response($response, 201);
        }
        DB::beginTransaction();
        try {
            $front_side_path = public_path('upload/leadMaster/front_side/');
            $back_side_path = public_path('upload/leadMaster/back_side/');
            if (!file_exists($front_side_path)) {
                mkdir($front_side_path, 0777, true);
            }

            if (!file_exists($back_side_path)) {
                mkdir($back_side_path, 0777, true);
            }

            $leadSync = $request->leadSync;
            $addArray = array();
            $companyFind = CompanyProfile::where('user_id', Auth::id())->first();
            foreach ($leadSync as $is) {
                if ($companyFind->user_type == 'O') {
                    $company_profile_id = $companyFind->id;
                    // $assign_id = $is['assign_id'];
                } elseif ($companyFind->user_type == 'M') {
                    $company_profile_id = $companyFind->parent_id;
                    // $assign_id = $is['assign_id'];
                } else {
                    $company_profile_id = $companyFind->parent_id;
                    // $assign_id = $companyFind->id;
                }
                $assign_id = $companyFind->id;
                $manager = CompanyProfile::where('id', $assign_id)->first();
                if ($manager->user_type == 'O') {
                    $manager_id = 0;
                } elseif ($manager->user_type == 'M') {
                    $manager_id = $manager->id;
                } else {
                    $manager_id = $manager->manager_id;
                }

                $front_side = '';
                if (!empty($is['front_side'])) {
                    $image_64 = $is['front_side'];
                    $extension = explode('/', explode(':', substr($image_64, 0, strpos($image_64, ';')))[1])[1];
                    $replace = substr($image_64, 0, strpos($image_64, ',') + 1);
                    $image = str_replace($replace, '', $image_64);
                    $image = str_replace(' ', '+', $image);
                    // $front_side = sha1(time() . uniqid()) . '.' . $extension;
                    $front_side = 'img-' . time() . rand() . '.webp';
                    File::put($front_side_path . '/' . $front_side, base64_decode($image));
                    $images = Image::make($image)->insert($image);
                }

                $back_side = '';
                if (!empty($is['back_side'])) {
                    $image_64 = $is['back_side'];
                    $extension = explode('/', explode(':', substr($image_64, 0, strpos($image_64, ';')))[1])[1];
                    $replace = substr($image_64, 0, strpos($image_64, ',') + 1);
                    $image = str_replace($replace, '', $image_64);
                    $image = str_replace(' ', '+', $image);
                    $back_side = 'img-' . time() . rand() . '.webp';
                    File::put($back_side_path . '/' . $back_side, base64_decode($image));
                    $images = Image::make($image)->insert($image);
                }

                $addArray[] = array(
                    'user_id' => Auth::id(),
                    'company_profile_id' => $company_profile_id,
                    'assign_id' => $assign_id,
                    'manager_id' => $manager_id,
                    'lead_title' => $is['lead_title'],
                    'name' => $is['name'],
                    'last_name' => $is['last_name'],
                    'mobile' => $is['mobile'],
                    'email' =>  $is['email'],
                    'lead_value' => $is['lead_value'],
                    'notes' => $is['notes'],
                    'source_id' => !is_null($is['source_id']) ? $is['source_id'] : 0,
                    'category_id' => !is_null($is['category_id']) ? $is['category_id'] : 0,
                    'product_id' => !is_null($is['product_id']) ? $is['product_id'] : 0,
                    'state_id' => !is_null($is['state_id']) ? $is['state_id'] : 0,
                    'city_id' => !is_null($is['city_id']) ? $is['city_id'] : 0,
                    'last_contacted' => !is_null($is['last_contacted']) ? date("Y-m-d", strtotime($is['last_contacted'])) : null,
                    'reminder_date' => null,
                    'tags' => $is['tags'],
                    'company_name' => $is['company_name'],
                    'website' => $is['website'],
                    'pincode' => $is['pincode'],
                    'status' => '0',
                    'status_master_id' => 0,
                    'front_side' => $front_side,
                    'back_side' => $back_side,
                    'created_at' => Carbon::now(),
                );
            }
            $result = LeadMaster::insert($addArray);
            if (!is_null($result)) {
                DB::commit();
                return response()->json(array('status' => true, 'message' => 'Lead added successfully.'));
            } else {
                return response()->json(array('status' => false, 'message' => 'Something went wrong. Please try again.'));
            }
        } catch (\Exception $e) {
            dd($e);
            DB::rollback();
            return response()->json(array('status' => false, 'message' => 'Something went wrong. Please try again.'));
        }
    }

    public function import(Request $request)
    {
        try {
            if (!is_null($request->excel_file)) {
                $file = $request->file('excel_file');
                Excel::import(new LeadImport, $file);
                $response = ['status' => true, 'message' => 'Lead added successfully.'];
            } else {
                $response = ['status' => false, 'message' => 'Please input proper data.'];
            }
            return response($response, 200);
        } catch (\Exception $e) {
            $response = ['status' => false, 'server_error' => 'Something went wrong. Please try again.'];
            return response($response, 500);
        }
    }

    public function destroy(Request $request)
    {
        try {
            $salesQuatation = SalesQuatation::where('lead_master_id', $request->id)->first();
            if (is_null($salesQuatation)) {
                $leadMaster = LeadMaster::where('id', $request->id)->first();
                if (!is_null($leadMaster)) {
                    LeadTransection::where('lead_master_id', $leadMaster->id)->delete();
                    $leadMaster->delete();
                    $response = ['status' => true, 'message' => 'Lead deleted successfully.'];
                } else {
                    $response = ['status' => false, 'message' => 'Lead not found.'];
                }
            } else {
                $response = ['status' => false, 'message' => "You cannot delete this lead because it is referenced in a sales quotation."];
            }
            return response($response, 200);
        } catch (\Exception $e) {
            $response = ['status' => false, 'server_error' => 'Something went wrong. Please try again.'];
            return response($response, 500);
        }
    }

    public function folloUp(Request $request)
    {
    }

    public function siteVisited(Request $request)
    {
        try {

            $leadMaster = LeadMaster::where('id', $request->id)->first();
            if (!is_null($leadMaster)) {

                $siteimages = [];

                if (isset($request->site_visit_old_images) && !empty($request->site_visit_old_images)) {
                    $siteimages = $request->site_visit_old_images;
                }

                if (!empty($request['site_visit_images']) && count($request['site_visit_images']) > 0) {

                    // / Upload image photos /
                    $site_visit_image_store = public_path('uploads/site_visit_images/');
                    $site_visit_medium_image_store = public_path('uploads/site_visit_images/thumbnail/');

                    // / Check dir is exists. if not, create new one /
                    if (!file_exists($site_visit_image_store)) {
                        mkdir($site_visit_image_store, 0777, true);
                    }
                    if (!file_exists($site_visit_medium_image_store)) {
                        mkdir($site_visit_medium_image_store, 0777, true);
                    }

                    foreach ($request['site_visit_images'] as $key => $value) {
                        if (!empty($value['image'])) {
                            $image_64 = $value['image'];
                            $extension = explode('/', explode(':', substr($image_64, 0, strpos($image_64, ';')))[1])[1];
                            $replace = substr($image_64, 0, strpos($image_64, ',') + 1);
                            $image = str_replace($replace, '', $image_64);
                            $image = str_replace(' ', '+', $image);
                            $site_visitimg = sha1(time() . uniqid()) . '.' . $extension;
                            File::put($site_visit_image_store . '/' . $site_visitimg, base64_decode($image));
                            $images = Image::make($image)->insert($image);
                            $images->resize(150, 150, function ($constraint) {
                                $constraint->aspectRatio();
                            })->save($site_visit_medium_image_store . '/' . $site_visitimg, 80);
                        }
                        array_push($siteimages, $site_visitimg);
                    }
                }
                $leadMaster->site_visiter = Auth::id();
                $leadMaster->site_visit_remark = $request->site_visit_remark;
                $leadMaster->site_visit_images = implode(',', $siteimages);
                $leadMaster->save();

                $response = ['status' => true, 'message' => 'Site Visited successfully.'];
            } else {
                $response = ['status' => false, 'message' => 'Lead not found.'];
            }
            return response($response, 200);
        } catch (\Exception $e) {
            $response = ['status' => false, 'server_error' => 'Something went wrong. Please try again.'];
            return response($response, 500);
        }
    }

    public function details(Request $request)
    {
        try {
            $lead = LeadMaster::select(
                'lead_masters.id',
                'lead_masters.mobile',
                'lead_masters.name',
                'lead_masters.kw',
                'lead_masters.reference',
                'lead_masters.remark',
                'lead_masters.address',
                'lead_sources.source_name',
                'lead_masters.source_id',
                'lead_masters.assign_id',
                'lead_masters.agent_sales_person_id',
                'agent_sales_people.name as agent_sales_name',
                 DB::raw("CONCAT(users.name, ' ', users.last_name) as assign_name"),
                 'lead_statuses.name as status_name',
                 'lead_statuses.icon_class',
                 'lead_statuses.id as status')
                ->leftJoin('agent_sales_people','agent_sales_people.id','lead_masters.agent_sales_person_id')
                ->leftJoin('company_profiles','company_profiles.id','lead_masters.assign_id')
                ->leftJoin('users','users.id','company_profiles.user_id')
                ->leftJoin('lead_statuses','lead_statuses.id','lead_masters.lead_status_id')
                ->leftJoin('lead_sources','lead_sources.id','lead_masters.source_id')


                ->where('lead_masters.id', $request->id)->first();

                $followUp = FollowUp::
                select('follow_ups.*', 'lead_statuses.name as status',
                 'lead_statuses.icon_class', DB::raw("DATE_FORMAT(follow_ups.created_at,'%d-%m-%Y') AS created_at"),'follow_up_images.image')
                ->leftJoin('lead_statuses','lead_statuses.id','follow_ups.status_id')
                ->leftJoin('follow_up_images','follow_up_images.follow_up_id','follow_ups.id')

                ->with('company')->where('follow_ups.lead_master_id', $request->id)
                ->orderBy('id','desc')
                ->get();

                if($followUp->count() > 0){
                $path = asset('upload/recording/');
                $follow_up_image_path = asset('upload/follow_up_image/');

                foreach ($followUp as $item) {

                    $item['follow_up_by'] = $item->company->user->name . " " . $item->company->user->last_name;
                    if (!is_null($item->call_recording)) {
                        $item['call_recording'] = $path . '/' . $item->call_recording;
                    } else {
                        $item['call_recording'] = "";
                    }
                    if (!is_null($item->image)) {
                        $item['follow_up_image_thumbnail'] = $follow_up_image_path . '/thumbnail/' . $item->image;
                        $item['follow_up_image'] = $follow_up_image_path .'/'.  $item->image;
                    } else {
                        $item['follow_up_image_thumbnail'] = "";
                        $item['follow_up_image'] = "";
                    }

                    $item['created_at_date'] = date('d M Y, h:i A', strtotime($item->created_at));

                    if (!is_null($item->reminder_date)) {
                        $item['reminder_date'] = date('d M Y, h:i A', strtotime($item->reminder_date));

                    } else {
                        $item['reminder_date'] = "";
                    }
                    unset($item->lead_master_id, $item->company, $item->created_at, $item->updated_at, $item->deleted_at,$item->image,$item->status_id);
                }
            }
                $salesQuatations = SalesQuatation::select('id','form_type','created_at','current_status','name','mobile','total_amount')->where('lead_master_id', $request->id)->get();

                if($salesQuatations->count() > 0){
                  foreach ($salesQuatations as $item) {
                    $item['quatation_no'] = env('APP_SORT') .'/EPC/'. str_pad($item->id, 2, '0', STR_PAD_LEFT);

                      if($item->form_type == 'trading') {
                                    $item['sub_name'] = 'Trading';
                       } elseif ($item->form_type == 'resident'){
                                    $item['sub_name'] = 'Resident With Subsidy';
                                }   elseif ($item->form_type == 'roof') {
                                    $item['sub_name'] = 'Solar RoofTop';
                            }
                    $item['created_at_date'] = date('d M Y, h:i A', strtotime($item->created_at));
                    unset($item->created_at);
                  }
                }

                $response = ['status' => true, 'message' => 'Lead details', 'lead' => $lead,'followup'=> $followUp, 'sales_quatations'=> $salesQuatations];

            return response($response, 200);
        } catch (Exception $e) {
            $response = ['status' => false, 'message' => 'Something went wrong. Please try again.', 'e' => $e];
            return response($response, 500);
        }
    }
}
