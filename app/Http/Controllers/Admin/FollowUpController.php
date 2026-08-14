<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CompanyProfile;
use App\Models\FollowUp;
use App\Models\FollowUpImage;
use App\Models\ItemGroup;
use App\Models\LeadMaster;
use App\Models\LeadStatus;
use App\Models\Notification;
use App\Models\Product;
use App\Models\RateGiven;
use App\Models\SalesQuatation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Facades\Image;

class FollowUpController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:follow-up-list', ['only' => ['edit']]);
        $this->middleware('permission:follow-up-create', ['only' => ['store']]);
        $this->middleware('permission:follow-up-create', ['only' => ['storeRateGiven']]);
    }

    public function index()
    {
        return abort(404);
    }

    public function create()
    {
        return abort(404);
    }

    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'call_detail' => 'required',
            'images' => 'sometimes|file|mimes:jpg,png,gif,bmp,webp|max:2048',
        ], [
            'call_detail.required' => 'Enter call detail',
            'images.file' => 'The uploaded file is invalid.',
            'images.mimes' => 'The uploaded file must be a JPG, PNG, GIF, BMP, or WebP.',
            'images.max' => 'The uploaded file may not be greater than 2MB.',
        ]);
        if ($validator->fails()) {
            $response = ['status' => false, 'message' => 'Please input proper data.', 'errors' => $validator->errors()];

            return response()->json($response);
        }
        DB::beginTransaction();
        try {
            $leadMaster = LeadMaster::where('id', $request->lead_master_id)->first();
            $leadMaster->last_contacted = date('Y-m-d');
            $leadMaster->lead_status_id = $request->lead_status;
            $leadMaster->assign_id = $request->assign_id;

            if (!is_null($request->reminder_date)) {
                $leadMaster->reminder_date = date('Y-m-d H:i', strtotime($request->reminder_date));
            } else {
                $leadMaster->reminder_date = null;
            }
            $leadMaster->save();

            $followUp = new FollowUp;
            $followUp->lead_master_id = $leadMaster->id;
            $followUp->call_detail = $request->call_detail;
            $followUp->remark = $request->remark;
            $followUp->status_id = $request->lead_status;
            if (!is_null($request->reminder_date)) {
                $followUp->reminder_date = date('Y-m-d H:i', strtotime($request->reminder_date));
            } else {
                $followUp->reminder_date = null;
            }

            $company = CompanyProfile::with('user')->where('user_id', Auth::id())->first();
            $followUp->follow_up_by = $company->id;

            $result = $followUp->save();
            // if ($request->image) {
            if ($request->hasFile('images')) {
                $PhotosDir = 'upload/follow_up_image/';
                $PhotosDirthumbnail = 'upload/follow_up_image/thumbnail/';

                if (! file_exists($PhotosDir)) {
                    mkdir($PhotosDir, 0777, true);
                }
                if (! file_exists($PhotosDirthumbnail)) {
                    mkdir($PhotosDirthumbnail, 0777, true);
                }
                // foreach ($request->file('images') as $image) {
                $image = $request->file('images');
                $file = $image;
                $images = Image::make($file)->insert($file);
                $filename = time().rand(0000, 9999).'.'.$image->getClientOriginalExtension();

                $images->resize(150, 150, function ($constraint) {
                    $constraint->aspectRatio();
                })->save($PhotosDirthumbnail.'/'.$filename, 80);
                $file->move($PhotosDir, $filename);

                // $file->move($PhotosDir, $filename);
                $bank_param = [
                    'lead_master_id' => $leadMaster->id,
                    'follow_up_id' => $followUp->id,
                    'image' => $filename,
                ];
                FollowUpImage::create($bank_param);
                //  }
            }
            $response = ['status' => true, 'message' => ' Follow up added successfully.'];
            DB::commit();
            if (! is_null($result)) {
                return response()->json($response);
            } else {
                $response = ['status' => false, 'server_error' => 'Something went wrong. Please try again.'];
                return response()->json($response);
            }
        } catch (\Exception $e) {
            DB::rollback();
            $response = ['status' => false, 'server_error' => 'Something went wrong. Please try again.'];
            return response()->json($response);
        }
    }

    public function storeRateGiven(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'lead_master_id' => 'required',
            'rate_givens' => 'required|array|min:1',
            'rate_givens.*.type' => 'required',
            'rate_givens.*.nos' => 'required|numeric',
            'rate_givens.*.rate' => 'required|numeric',
            'rate_givens.*.item_gst' => 'required|numeric',
        ], [
            'lead_master_id.required' => 'Lead is required',
            'rate_givens.required' => 'Add at least one item',
            'rate_givens.*.type.required' => 'Select type',
            'rate_givens.*.nos.required' => 'Enter nos',
            'rate_givens.*.rate.required' => 'Enter rate',
            'rate_givens.*.item_gst.required' => 'Enter GST',
        ]);
        if ($validator->fails()) {
            $response = ['status' => false, 'message' => 'Please input proper data.', 'errors' => $validator->errors()];

            return response()->json($response);
        }
        DB::beginTransaction();
        try {
            foreach ($request->rate_givens as $row) {
                $rateGiven = new RateGiven();
                $rateGiven->lead_master_id = $request->lead_master_id;
                $rateGiven->type = $row['type'];
                if ($row['type'] == 'Item') {
                    $rateGiven->item_id = $row['item_id'];
                    $rateGiven->item_group_id = 0;
                } else {
                    $rateGiven->item_id = 0;
                    $rateGiven->item_group_id = $row['item_group_id'];
                }
                $rateGiven->nos = $row['nos'];
                $rateGiven->rate = $row['rate'];
                $rateGiven->item_gst = $row['item_gst'];
                $rateGiven->total_taxable = $row['total_taxable'];
                $result = $rateGiven->save();
            }

            $this->addRateGivenActivity($request->lead_master_id);

            $response = ['status' => true, 'message' => 'Rate given added successfully.'];
            DB::commit();
            if (! is_null($result)) {
                return response()->json($response);
            } else {
                $response = ['status' => false, 'server_error' => 'Something went wrong. Please try again.'];

                return response()->json($response);
            }
        } catch (\Exception $e) {
            DB::rollback();
            $response = ['status' => false, 'server_error' => 'Something went wrong. Please try again.'];

            return response()->json($response);
        }
    }

    protected function addRateGivenActivity($leadMasterId)
    {
        try {
            $leadMaster = LeadMaster::where('id', $leadMasterId)->first();
            if (is_null($leadMaster)) {
                return;
            }

            $company = CompanyProfile::with('user')->where('user_id', Auth::id())->first();
            $creatorName = 'System';
            if (!is_null($company) && !is_null($company->user) && !is_null($company->user->name)) {
                $creatorName = $company->user->name . ' ' . ($company->user->last_name ?? '');
            }
            if (trim($creatorName) == '') {
                $creatorName = 'System';
            }

            $statusId = LeadStatus::where('name', 'Rate Given')->where('is_for_system', '1')->value('id');
            if (is_null($statusId)) {
                $statusId = $leadMaster->lead_status_id;
            }

            $followUp = new FollowUp();
            $followUp->lead_master_id = $leadMaster->id;
            $followUp->call_detail = '';
            $followUp->remark = 'Rate Given added By ' . $creatorName;
            $followUp->follow_up_by = !is_null($company) ? $company->id : Auth::id();
            $followUp->status_id = $statusId;
            $followUp->save();
        } catch (\Exception $e) {
        }
    }

    public function show($id)
    {
        $leadMaster = LeadMaster::with('company', 'company.user')->where('id', $id)->first();
        if (! is_null($leadMaster)) {
            $followUp = FollowUp::with('company', 'image')->where('lead_master_id', $leadMaster->id)->orderBy('id', 'desc')->get();
            $salesQuatations = SalesQuatation::where('lead_master_id', $leadMaster->id)->get();
            $leadStatus = LeadStatus::select('id', 'name')->where('is_for_system', '0')->orderBy('id', 'asc')->get();

            return view('admin.follow_up.show_follow_up', compact('leadMaster', 'followUp', 'salesQuatations', 'leadStatus'));
        } else {
            return abort(404);
        }
    }

    public function edit($id)
    {
        $leadMaster = LeadMaster::with('company', 'company.user', 'source')->where('id', $id)->first();
        if (! is_null($leadMaster)) {
            $followUp = FollowUp::with('company', 'image')->where('lead_master_id', $leadMaster->id)->orderBy('id', 'desc')->get();
            $salesQuatations = SalesQuatation::where('lead_master_id', $leadMaster->id)->get();
            $leadStatus = LeadStatus::select('id', 'name')->where('is_for_system', '0')->orderBy('id', 'asc')->get();

            $rateGivens = RateGiven::with('item', 'itemGroup')->where('lead_master_id', $leadMaster->id)->orderBy('id', 'desc')->get();
            $product = Product::get();
            $itemGroup = ItemGroup::with('panel_company', 'panel_type', 'panel_watt', 'inveter_company')->get();

            $where = '1 = 1';

            $companyFind = CompanyProfile::where('user_id', Auth::id())->first();
            if ($companyFind->user_type == 'O') {
                $id = $companyFind->id;
                $where .= ' AND company_profiles.user_id ='.Auth::id().' OR company_profiles.parent_id ='.$companyFind->id;
            } else {
                $id = $companyFind->parent_id;
                $where .= ' AND (company_profiles.parent_id = '.$id.' AND company_profiles.id = '.$id.' OR (company_profiles.manager_id = '.$companyFind->id.' OR company_profiles.user_id = '.$companyFind->user_id.')) OR company_profiles.user_type = "O"';
            }
            $companyProfile = CompanyProfile::with('user')->whereRaw($where)->get();

            return view('admin.follow_up.view_follow_up', compact('leadMaster', 'companyProfile', 'followUp', 'salesQuatations', 'leadStatus', 'rateGivens', 'product', 'itemGroup'));
        } else {
            return abort(404);
        }
    }

    public function update(Request $request, FollowUp $followUp)
    {
        //
    }

    public function destroy(FollowUp $followUp)
    {
        //
    }
}
