<?php

namespace App\Http\Controllers;

use App\Models\AgentSalesPerson;
use App\Models\CompanyProfile;
use App\Models\PaymetCollection;
use App\Models\SalesMaster;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File;

class PaymetCollectionApiController extends Controller
{
    public function index(Request $request)
    {
        $query = PaymetCollection::select('id', 'sales_master_id', 'payment_type', 'amount', DB::raw('DATE_FORMAT(payment_date, "%d-%m-%Y") as payment_date'), 'cheque_number', 'bank_name', 'branch_name', 'utr_number', 'upi_id', 'status', 'approved_by', 'remarks', 'remark', 'bank_id', 'file')->with('salesMaster');

        $company = CompanyProfile::where('user_id', Auth::id())->first();
        if ($company->user_type == 'M') {
            $agent = AgentSalesPerson::where('user_id', Auth::id())->first();
            $agentIds = [$agent->id];
            $sales = CompanyProfile::select('company_profiles.id', 'company_profiles.user_id', 'agent_sales_people.id as agent_id')
                ->leftJoin('agent_sales_people', 'agent_sales_people.user_id', 'company_profiles.user_id')
                ->where('company_profiles.manager_id', $company->id)->get();
            if ($sales->count() > 0) {
                foreach ($sales as $k => $v):
                    array_push($agentIds, $v->agent_id);
                endforeach;
            }
            $query->whereHas('salesMaster', function ($query) use ($agentIds) {
                $query->whereIn('agent_sales_person_id', $agentIds);
            });
        }
        if ($company->user_type == 'S') {
            $agent = AgentSalesPerson::where('user_id', Auth::id())->first();
            $id = $agent->id;

            $query->whereHas('salesMaster', function ($query) use ($id) {
                $query->where('agent_sales_person_id', $id);
            });
        }

        if ($request->from_date != "" && $request->to_date == '') {
            $query->where('payment_date', '>=', date('Y-m-d', strtotime($request->from_date)));
            $query->where('payment_date', '<=', date('Y-m-d'));
        }
        if ($request->from_date != "" && $request->to_date != '') {
            $query->where('payment_date', '>=', date('Y-m-d', strtotime($request->from_date)));
            $query->where('payment_date', '<=', date('Y-m-d', strtotime($request->to_date)));
        }
        if ($request->from_date == "" && $request->to_date != '') {
            $query->where('payment_date', '<=', date('Y-m-d', strtotime($request->to_date)));
        }
        if ($request->consumer != "") {
            $query->whereHas('salesMaster', function ($q) use ($request) {
                $q->where('sales_masters.consumer_name', 'like', '%' . $request->consumer . '%')
                    ->orWhere('sales_masters.consumer_number', 'like', '%' . $request->consumer . '%')
                    ->orWhere('sales_masters.contact_number', 'like', '%' . $request->consumer . '%');
            });
        }
        if ($request->payment_type != "") {
            $query->where('payment_type', $request->payment_type);
        }
        if ($request->status != "") {
            $query->where('status', $request->status);
        }
        $paymetCollection = $query->orderBy('id', 'DESC')->paginate(12);
        $response = ['status' => true, 'message' => 'Paymet Collection List', 'PaymetCollection' => $paymetCollection->items()];
        return response($response, 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'payment_type' => 'required',
            'amount' => 'required',
            'payment_date' => 'required'
        ], [
            'payment_type.required' => 'Select Payment Type',
            'amount.required' => 'Enter Payment Amount',
            'payment_date.required' => 'Select Payment Date',
        ]);

        if ($validator->fails()) {
            $response = ['status' => false, 'message' => 'Please input proper data.', 'errors' => $validator->errors()];
            return response()->json($response);
        }
        DB::beginTransaction();
        try {

            $paymetCollection = new PaymetCollection();
            $response = ['status' => true, 'message' => ' Payment Collection added successfully.'];

            $paymetCollection->sales_master_id = $request->sales_master_id;
            $paymetCollection->payment_type = $request->payment_type;

            $paymetCollection->amount = $request->amount;

            $paymetCollection->payment_date = date('Y-m-d', strtotime($request->payment_date));
            $paymetCollection->cheque_number = $request->cheque_number;
            $paymetCollection->bank_name = $request->bank_name;
            $paymetCollection->branch_name = $request->branch_name;
            $paymetCollection->utr_number = $request->utr_number;
            $paymetCollection->upi_id = $request->upi_id;
            $paymetCollection->remark = $request->remark;

            $paymetCollection->bank_id = $request->bank_id;


            $follow_image_store = public_path('uploads/payment_collections/');

            // / Check dir is exists. if not, create new one /
            if (!file_exists($follow_image_store)) {
                mkdir($follow_image_store, 0777, true);
            }

            if (!empty($request['file'])) {
                $image_64 = $request['file'];
                $extension = explode('/', explode(':', substr($image_64, 0, strpos($image_64, ';')))[1])[1];
                $replace = substr($image_64, 0, strpos($image_64, ',') + 1);
                $image = str_replace($replace, '', $image_64);
                $image = str_replace(' ', '+', $image);
                $followimg = sha1(time() . uniqid()) . '.' . $extension;
                File::put($follow_image_store . '/' . $followimg, base64_decode($image));
                $paymetCollection->file = $followimg;
            }


            $paymetCollection->created_by = Auth::id();



            $result = $paymetCollection->save();
            DB::commit();

            if (!is_null($result)) {
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

    public function update(Request $request)
    {
        $paymetCollection = PaymetCollection::where('id', $request->id)->first();
        if ($paymetCollection->status != 1) {
            $validator = Validator::make($request->all(), [
                'payment_type' => 'required',
                'amount' => 'required',
                'payment_date' => 'required'
            ], [
                'payment_type.required' => 'Select Payment Type',
                'amount.required' => 'Enter Payment Amount',
                'payment_date.required' => 'Select Payment Date',
            ]);

            if ($validator->fails()) {
                $response = ['status' => false, 'message' => 'Please input proper data.', 'errors' => $validator->errors()];
                return response()->json($response);
            }
            DB::beginTransaction();
            try {
                // $sales_payment_dd = SalesMaster::where('pending_amonut', '!=', 0)->orWhere('id', $paymetCollection->sales_master_id)->orderBy('id', 'DESC')->get();

                $paymetCollection = PaymetCollection::where('id', $request->id)->first();
                $response = ['status' => true, 'message' => ' Payment Collection updated successfully.'];

                $paymetCollection->sales_master_id = $request->sales_master_id;
                $paymetCollection->payment_type = $request->payment_type;

                $paymetCollection->amount = $request->amount;

                $paymetCollection->payment_date = date('Y-m-d', strtotime($request->payment_date));
                $paymetCollection->cheque_number = $request->cheque_number;
                $paymetCollection->bank_name = $request->bank_name;
                $paymetCollection->branch_name = $request->branch_name;
                $paymetCollection->utr_number = $request->utr_number;
                $paymetCollection->upi_id = $request->upi_id;
                $paymetCollection->remark = $request->remark;


                $paymetCollection->bank_id = $request->bank_id;


                $follow_image_store = public_path('uploads/payment_collections/');

                // / Check dir is exists. if not, create new one /
                if (!file_exists($follow_image_store)) {
                    mkdir($follow_image_store, 0777, true);
                }

                if (!empty($request['file'])) {
                    $image_64 = $request['file'];
                    $extension = explode('/', explode(':', substr($image_64, 0, strpos($image_64, ';')))[1])[1];
                    $replace = substr($image_64, 0, strpos($image_64, ',') + 1);
                    $image = str_replace($replace, '', $image_64);
                    $image = str_replace(' ', '+', $image);
                    $followimg = sha1(time() . uniqid()) . '.' . $extension;
                    File::put($follow_image_store . '/' . $followimg, base64_decode($image));
                    $paymetCollection->file = $followimg;
                }


                $paymetCollection->updated_by = Auth::id();


                $result = $paymetCollection->save();
                DB::commit();

                if (!is_null($result)) {
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
        } else {
            $response = ['status' => true, 'message' => 'already Approved'];
            return response()->json($response);
        }
    }

    public function destroy(Request $request)
    {
        try {
            $paymetCollection = PaymetCollection::where('id', $request->id)->first();
            $sales_master_id = $paymetCollection->sales_master_id;

            $salesMaster = SalesMaster::where('id', $sales_master_id)->first();
            $rec_amt = ($paymetCollection->amount != "") ? $paymetCollection->amount : 0;
            $final_amt = $salesMaster->pending_amonut + $rec_amt;
            $salesMaster->pending_amonut = $final_amt;
            if ($final_amt != "0") {
                $salesMaster->payment_receveid = "0";
            }
            $salesMaster->save();

            $paymetCollection->delete();

            $response = ['status' => true, 'message' => ' Deleted successfully.'];
            return response()->json($response);
        } catch (\Exception $e) {
            $response = ['status' => false, 'server_error' => 'Something went wrong. Please try again.'];
            return response()->json($response);
        }
    }

    public function changeStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required',
            'id' => 'required'
        ], [
            'status.required' => 'Select Payment Status',
            'id.required' => 'Enter Payment id',

        ]);
        if ($validator->fails()) {
            $response = ['status' => false, 'message' => 'Please input proper data.', 'errors' => $validator->errors()];
            return response()->json($response);
        }
        DB::beginTransaction();
        try {
            $paymetCollection = PaymetCollection::where('id', $request->id)->first();
            $sales_master_id = $paymetCollection->sales_master_id;
            if ($request->status == 1) {
                $paymetCollection->status = 1;
                $paymetCollection->approved_by = Auth::id();
                $paymetCollection->remarks = $request->remarks;
                $salesMaster = SalesMaster::where('id', $sales_master_id)->first();
                $rec_amt = ($paymetCollection->amount != "") ? $paymetCollection->amount : 0;
                $final_amt = $salesMaster->pending_amonut - $rec_amt;
                $salesMaster->pending_amonut = $final_amt;
                if ($final_amt == "0") {
                    $salesMaster->payment_receveid = "1";
                }
                if ($salesMaster->feasibility_approved == "1" && $salesMaster->payment_receveid == "1") {
                    $salesMaster->dispach_pending_list = "1";
                }
                $salesMaster->save();
            } else if ($paymetCollection->status == 1 && $request->status == 0) {
                $paymetCollection->status = 0;
                $salesMaster = SalesMaster::where('id', $sales_master_id)->first();
                $rec_amt = ($paymetCollection->amount != "") ? $paymetCollection->amount : 0;
                $final_amt = $salesMaster->pending_amonut + $rec_amt;
                $salesMaster->pending_amonut = $final_amt;
                if ($final_amt != "0") {
                    $salesMaster->payment_receveid = "0";
                }
                $salesMaster->save();
            }
            $res = $paymetCollection->save();
            if ($res) {
                DB::commit();
                $response = ['status' => true, 'message' => 'Status changed successfully.'];
            } else {
                DB::rollback();
                $response = ['status' => false, 'server_error' => 'Something went wrong. Please try again.'];
            }
            return response()->json($response);
        } catch (\Exception $e) {
            DB::rollback();
            $response = ['status' => false, 'server_error' => 'Something went wrong. Please try again.'];
            return response()->json($response);
        }
    }

    public function salesOrderPayment()
    {
        $query = SalesMaster::select('id', 'consumer_number', 'consumer_name', 'consumer_type', 'total_amount', 'pending_amonut');
        if (empty(request()->input('type'))) {
            $query->where('pending_amonut', '!=', 0);
        }

        $user = User::where('id', Auth::id())->first();

        if ($user->roles[0]->name == 'Dealer') {
            $company = AgentSalesPerson::where('user_id', Auth::id())->first();
            $id = $company->id;
            $query->where('agent_sales_person_id', $id);
        }
        $sales_payment_dd = $query->orderBy('id', 'DESC')->get();
        $response = ['status' => true, 'sales_payment_dd' => $sales_payment_dd];
        return response()->json($response);
    }
}
