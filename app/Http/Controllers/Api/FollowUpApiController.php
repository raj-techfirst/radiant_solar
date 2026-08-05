<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CompanyProfile;
use App\Models\FollowUp;
use App\Models\FollowUpImage;
use App\Models\LeadMaster;
use App\Models\Notification;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Facades\Image;

class FollowUpApiController extends Controller
{
    public function store(Request $request)
    {
        // dd(Auth::id());
        if ($request->status != 3) {
            $temp = 'nullable';
        } else {
            $temp = 'required';
        }
        $validator = Validator::make($request->all(), [
            'call_detail' => 'required',
            'reminder_date' => $temp,
        ], [
            'call_detail.required' => 'Enter call detail',
            'reminder_date.required' => 'Enter reminder date',
        ]);
        if ($validator->fails()) {
            $response = ['status' => false, 'message' => 'Please input proper data.', 'errors' => $validator->errors()];
            return response()->json($response);
        }
        DB::beginTransaction();
        try {
            $leadMaster = LeadMaster::where('id', $request->lead_master_id)->first();
            $leadMaster->last_contacted = date("Y-m-d");
            $leadMaster->status = $request->status;
            if (!is_null($request->reminder_date)) {
                $leadMaster->reminder_date = date("Y-m-d h:i", strtotime($request->reminder_date));
            } else {
                $leadMaster->reminder_date = null;
            }
            $leadMaster->save();
            $followUp = new FollowUp();
            $followUp->lead_master_id = $leadMaster->id;
            $followUp->call_detail = $request->call_detail;
            $followUp->remark = $request->remark;
            if (!is_null($request->reminder_date)) {
                $followUp->reminder_date = date("Y-m-d h:i", strtotime($request->reminder_date));
            } else {
                $followUp->reminder_date = null;
            }

            // $notification = new Notification();
            // $notification->user_id = '1';
            // $notification->title = 'Status Change';
            // $notification->description = 'Status Change.';
            // $notification->is_read = '0';
            // $notification->count = '0';
            // $notification->save();

            //jaydeep code changes 07-03-2024
            $notification = new Notification();
            $notification->user_id = $leadMaster->assign_id;
            $notification->title = 'Follow up';
            $notification->lead_type = '1';
            $notification->reminder_date = date("Y-m-d", strtotime($request->reminder_date));
            $notification->description = $request->call_detail;
            $notification->is_read = '0';
            $notification->count = '0';
            $notification->save();

            $get_id = CompanyProfile::where('id', $leadMaster->assign_id)->first();

            if($get_id->user_type == 'S' && $get_id->user_id == Auth::id()){
                $notification = new Notification();
                $notification->user_id = $get_id->manager_id;
                $notification->title = 'Follow up';
                $notification->lead_type = '0';
                // $notification->reminder_date = date("Y-m-d", strtotime($request->reminder_date));
                $notification->description = $request->call_detail;
                $notification->is_read = '0';
                $notification->count = '0';
                $notification->save();
            }
            //jaydeep code changes 07-03-2024

            $company = CompanyProfile::with('user')->where('user_id', Auth::id())->first();
            $followUp->follow_up_by = $company->id;

            // if ($request->call_recording) {
            //     $PhotosDir = 'upload/recording/';
            //     if (!file_exists($PhotosDir)) {
            //         mkdir($PhotosDir, 0777, true);
            //     }
            //     $file = $request->file('call_recording');
            //     $filename = 'lead-' . time() . rand(0000, 9999) . $file->getClientOriginalExtension();
            //     $file->move($PhotosDir, $filename);
            //     $followUp->call_recording = $filename;
            // }
            $result = $followUp->save();
            // if ($request->image) {
            if ($request->hasFile('images')) {
             
                $PhotosDir = 'upload/follow_up_image/';
                $PhotosDirthumbnail = 'upload/follow_up_image/thumbnail/';

                if (!file_exists($PhotosDir)) {
                    mkdir($PhotosDir, 0777, true);
                }
                if (!file_exists($PhotosDirthumbnail)) {
                    mkdir($PhotosDirthumbnail, 0777, true);
                }
                foreach ($request->file('images') as $image) {
                    $file = $image;
                    $images = Image::make($file)->insert($file);
                    $filename = time() . rand(0000, 9999) .'.'. $image->getClientOriginalExtension();
                    
                    $images->resize(150, 150, function ($constraint) {
                        $constraint->aspectRatio();
                    })->save($PhotosDirthumbnail . '/' . $filename, 80);
                    $file->move($PhotosDir, $filename);

                    // $file->move($PhotosDir, $filename);
                    $bank_param = [                    
                        'lead_master_id' => $leadMaster->id,
                        'follow_up_id' =>  $followUp->id,
                        'image' => $filename,
                    ];
                    FollowUpImage::create($bank_param);
                }
            }

            if (!empty($request['follow_image']) && count($request['follow_image']) > 0) {
                // dd($request['penal_image']);
                // / Upload image photos /
                $follow_image_store = public_path('upload/follow_up_image/');
                $follow_medium_image_store = public_path('upload/follow_up_image/thumbnail/');

                // / Check dir is exists. if not, create new one /
                if (!file_exists($follow_image_store)) {
                    mkdir($follow_image_store, 0777, true);
                }
                if (!file_exists($follow_medium_image_store)) {
                    mkdir($follow_medium_image_store, 0777, true);
                }
                foreach ($request['follow_image'] as $key => $value) {
                    $followimg = '';
                    if (!empty($value['image'])) {
                        $image_64 = $value['image'];
                        $extension = explode('/', explode(':', substr($image_64, 0, strpos($image_64, ';')))[1])[1];
                        $replace = substr($image_64, 0, strpos($image_64, ',') + 1);
                        $image = str_replace($replace, '', $image_64);
                        $image = str_replace(' ', '+', $image);
                        $followimg = sha1(time() . uniqid()) . '.' . $extension;
                        File::put($follow_image_store . '/' . $followimg, base64_decode($image));
                        $images = Image::make($image)->insert($image);
                        $images->resize(150, 150, function ($constraint) {
                            $constraint->aspectRatio();
                        })->save($follow_medium_image_store . '/' . $followimg, 80);
                    }
                    $follow_param = [
                        'lead_master_id' => $leadMaster->id,
                        'follow_up_id' =>  $followUp->id,
                        'image' => $followimg,
                    ];
                    FollowUpImage::create($follow_param);
                }
            }

            $response = ['status' => true, 'message' => ' Follow up added successfully.'];
            DB::commit();
            if (!is_null($result)) {
                return response()->json($response);
            } else {
                dd($result);
                $response = ['status' => false, 'server_error' => 'Something went wrong. Please try again.'];
                return response()->json($response);
            }
        } catch (\Exception $e) {
            dd($e);
            DB::rollback();
            $response = ['status' => false, 'server_error' => 'Something went wrong. Please try again.'];
            return response()->json($response);
        }
    }

    public function show($id)
    {
        //
    }

    public function edit($id)
    {
        //
    }

    public function update(Request $request, $id)
    {
        //
    }

    public function destroy($id)
    {
        //
    }
}
