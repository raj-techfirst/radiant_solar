<?php

namespace App\Http\Controllers;

use App\Models\Inquiry;
use App\Models\InquiryFollow;
use App\Models\SalesMaster;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class InquiryFollowController extends Controller
{
    public function index()
    {
        //
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'call_detail' => 'required',
        ], [
            'call_detail.required' => 'Enter call detail',
        ]);
        if ($validator->fails()) {
            $response = ['status' => false, 'message' => 'Please input proper data.', 'errors' => $validator->errors()];

            return response()->json($response);
        }
        DB::beginTransaction();
        try {

            $inquiry = Inquiry::where('id', $request->inquiry_id)->first();
            $inquiry->status = $request->status;
            $inquiry->save();

            $followUp = new InquiryFollow();
            $followUp->call_detail = $request->call_detail;
            $followUp->remark = $request->remark;
            $followUp->status = $request->status;
            $followUp->assign_person_id = $request->assign_person_id;

            if ($request->consumer_flag == 'old') {
                $followUp->inquiry_id = $inquiry->id;
            } else {
                $saleMaster = SalesMaster::where('id', $request->sales_master_id)->first();
                $followUp->sales_master_id = $saleMaster->id;
            }

            if($request->hasfile('images')) {
                $PhotosDir = 'upload/inquiry/';
                if (! file_exists($PhotosDir)) {
                    mkdir($PhotosDir, 0777, true);
                }
                $file = $request->file('images');
                $filename = $request->title.'-'.time().rand().'.webp';
                $file->move('upload/inquiry/', $filename);
                $followUp->image = $filename;
            }

            if (!is_null($request->reminder_date)) {
                $followUp->reminder_date = date('Y-m-d H:i', strtotime($request->reminder_date));
            } else {
                $followUp->reminder_date = null;
            }
            $followUp->follow_up_by = Auth::id();
            $result = $followUp->save();

            $response = ['status' => true, 'message' => ' Follow up added successfully.','data' => route('inquiry-follow',$request->inquiry_id)];
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

    public function show($id)
    {
        //
    }

    public function edit(InquiryFollow $inquiryFollow)
    {
        //
    }

    public function update(Request $request, InquiryFollow $inquiryFollow)
    {
        //
    }

    public function destroy(InquiryFollow $inquiryFollow)
    {
        //
    }
}
