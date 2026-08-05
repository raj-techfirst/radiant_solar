<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\SalesMaster;
use App\Models\SubDivision;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Intervention\Image\Facades\Image;

class SalesMasterAPIController extends Controller
{
    public function index(Request $request)
    {
        $consumer_number = [
            'required',
            Rule::unique('sales_masters')->where(function ($query) use ($request) {
                return $query->where('deleted_at', null);
            }),
        ];

        $validator = Validator::make($request->all(), [
            'consumer_number' => $consumer_number,
            'consumer_type' => 'required',
            'consumer_name' => 'required',
            'sales_quatation_id' => 'required',
            'district_id' => 'required',
            'taluka_id' => 'required',
            'pin_code' => 'required',
            'contact_number' => 'required',
            'agent_sales_person_id' => 'required',
            'total_amount' => 'required',
        ], [
            'consumer_number.required' => 'Enter Consumer Number',
            'consumer_number.unique' => 'The Consumer Number has already been taken',
            'consumer_type.required' => 'Enter Consumer Type',
            'consumer_name.required' => 'Enter Consumer Name',
            'sales_quatation_id.required' => 'Enter Sales Quatation',
            'district_id.required' => 'Enter District',
            'taluka_id.required' => 'Enter Taluka',
            'pin_code.required' => 'Enter Pincode',
            'taluka.required' => 'Enter Taluka',
            'contact_number.required' => 'Enter Contact Number',
            'agent_sales_person_id' => 'Choose Agent/Sales Person Name',
            'total_amount' => 'Enter Total Amount',
        ]);

        if ($validator->fails()) {
            $response = ['status' => false, 'message' => 'Please input proper data.', 'errors' => $validator->errors()];
            return response()->json($response);
        }
        DB::beginTransaction();
        try {
            if (!is_null($request->sales_master_id)) {
                $salesMaster = SalesMaster::where('id', $request->sales_master_id)->first();
                $response = ['status' => true, 'message' => ' Sales Master updated successfully.'];
            } else {
                $salesMaster = new SalesMaster();
                $response = ['status' => true, 'message' => ' Sales Master added successfully.'];
            }
            $salesMaster->user_id = Auth::id();
            $salesMaster->file_type = $request->file_type ?? 'C';
            $salesMaster->sales_quatation_id = $request->sales_quatation_id;
            $salesMaster->consumer_number = $request->consumer_number;
            $salesMaster->master_create_date = date('Y-m-d');
            $salesMaster->consumer_type = $request->consumer_type;
            $salesMaster->consumer_name = strtoupper($request->consumer_name);
            $salesMaster->district_id = $request->district_id;
            $salesMaster->taluka_id = $request->taluka_id;
            $salesMaster->village_id = 0;
            $salesMaster->pin_code = $request->pin_code;
            $salesMaster->contact_number = $request->contact_number;
            $salesMaster->total_amount = $request->total_amount;
            $salesMaster->pending_amonut = $request->total_amount;
            $salesMaster->agent_sales_person_id = $request->agent_sales_person_id;
            $salesMaster->remark = $request->remark;
            $salesMaster->division = $request->division;
            $salesMaster->sub_division_id = $request->sub_division_id;
            $salesMaster->circle = $request->circle;
            $salesMaster->discom = $request->discom;
            $salesMaster->register_kw = $request->register_kw;
            $salesMaster->bom_id = $request->bom_id;

            if (!empty($request->penal_company_id)) {
                $salesMaster->penal_company_id = $request->penal_company_id;
            }
            if (!empty($request->penal_watt_id)) {
                $salesMaster->penal_watt_id = $request->penal_watt_id;
            }
            if (!empty($request->inveter_company_id)) {
                $salesMaster->inveter_company_id = $request->inveter_company_id;
            }

            $result = $salesMaster->save();

            $PhotosDir = 'upload/document/';
            if (!file_exists($PhotosDir)) {
                mkdir($PhotosDir, 0777, true);
            }
            if (!empty($request->invoice) && is_array($request->invoice)) {
                foreach ($request->invoice as $key => $value) {
                    $invaterimg = '';
                    if (!empty($value['image'])) {
                        $image_64 = $value['image'];
                        $extension = explode('/', explode(':', substr($image_64, 0, strpos($image_64, ';')))[1])[1];
                        $replace = substr($image_64, 0, strpos($image_64, ',') + 1);
                        $image = str_replace($replace, '', $image_64);
                        $image = str_replace(' ', '+', $image);
                        $invaterimg = sha1(time() . uniqid()) . '.' . $extension;
                        File::put($PhotosDir . '/' . $invaterimg, base64_decode($image));
                        $document = new Document();
                        $document->sales_master_id = $salesMaster->id;
                        $document->name = $value['name'];
                        $document->image = $invaterimg;
                        $document->save();
                    }
                }
            }
            if (!is_null($result)) {
                DB::commit();
                return response($response, 200);
            } else {
                DB::rollback();
                $response = ['status' => true, 'server_error' => 'Something went wrong. Please try again.'];
                return response($response, 500);
            }
        } catch (\Exception $e) {
            DB::rollback();
            $response = ['status' => true, 'server_error' => 'Something went wrong. Please try again.'];
            return response($response, 500);
        }
    }

    public function subDivision()
    {
        $subDivision = SubDivision::select('id', 'name', 'division_name', 'circle_name', 'discom')->get();

        $response = ['status' => true, 'message' => 'Sub Division List', 'subDivision' => $subDivision];
        return response($response, 200);
    }
}
