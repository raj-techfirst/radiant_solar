<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CompanyProfile;
use App\Models\Estimate;
use App\Models\EstimateItem;
use App\Models\LeadMaster;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;

class EstimateApiController extends Controller
{
    public function index(Request $request)
    {
        $where = "1 = 1";
        $company = CompanyProfile::where('user_id', Auth::id())->first();
        if ($company->user_type == 'O') {
            $where .= ' AND estimates.company_profile_id =' . $company->id;
        } else if ($company->user_type == 'M') {
            $where .= ' AND estimates.company_profile_id = ' . $company->parent_id . ' AND estimates.manager_id = ' . $company->id;
        } else {
            $where .= ' AND estimates.assign_id =' . $company->id . ' AND estimates.company_profile_id =' . $company->parent_id;
        }
        $pdf_path =  asset('upload/pdf/');
        $estimates = Estimate::with('leadMaster', 'estimateItem')->whereRaw($where)->orderBy('id', 'DESC')->get();

        foreach ($estimates as $value) {
            $value['pdf'] = (!is_null($value->pdf)) ? $pdf_path . '/' . $value->pdf : "";

            $value['estimate_date'] = (!is_null($value->estimate_date)) ? date('d-m-Y', strtotime($value->estimate_date)) : "";
            $value['expiry_date'] = (!is_null($value->expiry_date)) ? date('d-m-Y', strtotime($value->expiry_date)) : "";

            $value['estimate_date_date'] = date('d', strtotime($value->estimate_date));
            $value['estimate_date_month'] = date('M', strtotime($value->estimate_date));
            $value['estimate_date_year'] = date('Y', strtotime($value->estimate_date));

            $value['expiry_date_date'] = date('d', strtotime($value->expiry_date));
            $value['expiry_date_month'] = date('M', strtotime($value->expiry_date));
            $value['expiry_date_year'] = date('Y', strtotime($value->expiry_date));

            $value['client'] = $value->leadMaster->name . " " . $value->leadMaster->last_name;
            $value['lead_title'] = $value->leadMaster->lead_title;
            $value['mobile'] = $value->leadMaster->mobile;

            foreach ($value->estimateItem as $item) {
                $item['product_name'] = ($item->product_id != "" && $item->product_id != 0) ? $item->product->product_name : "";
                $item['unit_name'] = ($item->unit_id != "" && $item->unit_id != 0) ? $item->unit->unit_name : "";
                unset($item->estimate_id, $item->product, $item->unit, $item->created_at, $item->updated_at, $item->deleted_at);
            }
            unset($value->estimate_date, $value->expiry_date, $value->user_id, $value->company_profile_id, $value->leadMaster, $value->assign_id, $value->created_at, $value->updated_at, $value->deleted_at);
        }
        $response = ['status' => true, 'message' => 'Estimate List', 'estimate' => $estimates];
        return response()->json($response);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'lead_id' => 'required',
            'estimate_title' => 'required',
            'estimate_date' => 'required',
            'expiry_date' => 'required',
        ], [
            'lead_id.required' => 'Select client',
            'estimate_title.required' => 'Enter estimate title',
            'estimate_date.required' => 'Enter estimate date',
            'expiry_date.required' => 'Enter expiry date',
        ]);
        if ($validator->fails()) {
            $response = ['status' => false, 'message' => 'Please input proper data.', 'errors' => $validator->errors()];
            return response($response, 200);
        }
        DB::beginTransaction();
        try {
            if (!is_null($request->estimate_id)) {
                $estimate = Estimate::where('id', $request->estimate_id)->first();
                $path = 'upload/pdf/' . $estimate->pdf;
                if ($estimate->pdf) {
                    if (File::exists($path)) {
                        unlink($path);
                    }
                }
                $response = ['status' => true, 'message' => 'Estimate updated successfully.'];
            } else {
                $estimate = new Estimate();
                $response = ['status' => true, 'message' => 'Estimate added successfully.'];
            }
            $companyFind = CompanyProfile::where('user_id', Auth::id())->first();
            if ($companyFind->user_type == 'O') {
                $id = $companyFind->id;
            } else {
                $id = $companyFind->parent_id;
            }
            $leadMaster = LeadMaster::where('id', $request->lead_id)->first();
            $estimate->user_id = Auth::id();
            $estimate->lead_master_id = $request->lead_id;
            $estimate->company_profile_id = $id;
            $estimate->assign_id = $leadMaster->assign_id;
            $estimate->manager_id = $leadMaster->manager_id;
            $estimate->estimate_title = $request->estimate_title;
            $estimate->remark = $request->remark;
            if ($request->estimate_date) {
                $estimate->estimate_date = date("Y-m-d", strtotime($request->estimate_date));
            } else {
                $estimate->estimate_date = null;
            }
            if ($request->expiry_date) {
                $estimate->expiry_date = date("Y-m-d", strtotime($request->expiry_date));
            } else {
                $estimate->expiry_date = null;
            }
            $total_amount = 0;
            if (count($request->item) > 0) {
                foreach ($request->item as $key => $value) {
                    if (!is_null($value['quantity']) &&  !is_null($value['rate']) && !is_null($value['product_id']) && $value['quantity'] > 0) {
                        $total_amount += $value['rate'] * $value['quantity'];
                    } else {
                        $response = ['status' => false, 'message' => 'Please input proper data.'];
                        return response($response);
                    }
                }
            }
            $estimate->discount = $request->discount;
            $estimate->subtotal = $total_amount;
            $estimate->total = ($total_amount - ($total_amount * $request->discount / 100));

            $result = $estimate->save();
            foreach ($request->item as $key => $value) {
                if (isset($value['estimateItem_id']) && !is_null($value['estimateItem_id'])) {
                    $estimateitem = EstimateItem::where('id', $value['estimateItem_id'])->first();
                } else {
                    $estimateitem = new EstimateItem();
                }
                $estimateitem->estimate_id = $estimate->id;
                $estimateitem->product_id = $value['product_id'];
                $estimateitem->unit_id = $value['unit_id'];
                $estimateitem->quantity = $value['quantity'];
                $estimateitem->rate = $value['rate'];
                $estimateitem->total = $value['rate'] * $value['quantity'];
                $estimateitem->save();
            }

            DB::commit();
            if (!is_null($result)) {
                estimatePDF($estimate->id);
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

    public function show(Request $request)
    {
        //
    }

    public function destroy(Request $request)
    {
        try {
            $estimate = Estimate::where('id', $request->id)->first();
            if (!is_null($estimate)) {
                $estimate_item = EstimateItem::where('estimate_id', $estimate->id)->get();
                if (count($estimate_item) > 0) {
                    foreach ($estimate_item as $item) {
                        $item->delete();
                    }
                }
                $path = 'upload/pdf/' . $estimate->pdf;
                if ($estimate->pdf) {
                    if (File::exists($path)) {
                        unlink($path);
                    }
                }
                $estimate->delete();
                $response = ['status' => true, 'message' => 'Estimate deleted successfully.'];
            } else {
                $response = ['status' => false, 'message' => 'Estimate not found.'];
            }
            return response($response);
        } catch (\Exception $e) {
            $response = ['status' => false, 'server_error' => 'Something went wrong. Please try again.'];
            return response($response);
        }
    }

    public function removeItem(Request $request)
    {
        try {
            $estimateitem = EstimateItem::where('id', $request->id)->first();
            if (!is_null($estimateitem)) {
                $estimate = Estimate::where('id', $estimateitem->estimate_id)->first();
                $total = $estimateitem->total;
                $subtotal = $estimate->subtotal - $total;
                $estimate->subtotal = $subtotal;
                $estimate->total = ($subtotal - ($subtotal * $estimate->discount / 100));
                $estimate->save();
                $estimateitem->delete();
                $response = ['status' => true, 'message' => 'Remove successfully.'];
                return response($response, 200);
            } else {
                $response = ['status' => false, 'message' => 'Item not found.'];
                return response($response, 200);
            }
        } catch (\Exception $e) {
            $response = ['status' => false, 'message' => 'Something went wrong. Please try again.'];
            return response($response, 500);
        }
    }
}
