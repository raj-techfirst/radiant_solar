<?php

namespace App\Http\Controllers;

use App\Exports\PaymentExport;
use App\Models\AgentSalesPerson;
use App\Models\Bank;
use App\Models\CompanyProfile;
use App\Models\Loandata;
use App\Models\Loandisbursement;
use App\Models\PaymetCollection;
use App\Models\SalesMaster;
use App\Models\SalesQuatation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use PgSql\Lob;
use Yajra\DataTables\Facades\DataTables;

class PaymetCollectionController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:payment-collection-list|payment-collection-create|payment-collection-edit|payment-collection-delete', ['only' => ['index', 'store']]);
        $this->middleware('permission:payment-collection-create', ['only' => ['create', 'store']]);
        //$this->middleware('permission:payment-collection-edit', ['only' => ['edit', 'store']]);
        $this->middleware('permission:payment-collection-delete', ['only' => ['destroy']]);
    }

    public function index()
    {
        $query = SalesMaster::where('pending_amonut', '!=', 0);
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
            $query->whereIn('agent_sales_person_id', $agentIds);
        }
        if ($company->user_type == 'S') {
            $agent = AgentSalesPerson::where('user_id', Auth::id())->first();
            $id = $agent->id;
            $query->where('agent_sales_person_id', $id);
        }
        $sales_payment_dd = $query->orderBy('id', 'DESC')->get();

        if (request()->ajax()) {
            return DataTables::of(PaymetCollection::with('salesMaster', 'creator', 'updater')->orderBy('id', 'DESC'))
                ->addIndexColumn()
                ->filter(function ($query) {
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

                    if (request()->input('from_date') != "" && request()->input('to_date') == '') {
                        $query->where('payment_date', '>=', date('Y-m-d 00:00:00', strtotime(request()->input('from_date'))));
                        $query->where('payment_date', '<=', date('Y-m-d 23:59:59'));
                    }
                    if (request()->input('from_date') != "" && request()->input('to_date') != '') {
                        $query->where('payment_date', '>=', date('Y-m-d 00:00:00', strtotime(request()->input('from_date'))));
                        $query->where('payment_date', '<=', date('Y-m-d 23:59:59', strtotime(request()->input('to_date'))));
                    }
                    if (request()->input('from_date') == "" && request()->input('to_date') != '') {
                        $query->where('payment_date', '<=', date('Y-m-d 23:59:59', strtotime(request()->input('to_date'))));
                    }
                    if (request()->input('consumer') != "") {
                        $query->whereHas('salesMaster', function ($q) {
                            $q->where('sales_masters.consumer_name', 'like', '%' . request()->input('consumer') . '%');
                            $q->orWhere('sales_masters.consumer_number', 'like', '%' . request()->input('consumer') . '%');
                            $q->orWhere('sales_masters.contact_number', 'like', '%' . request()->input('consumer') . '%');
                        });
                    }
                    if (request()->input('payment_type') != "") {
                        $query->where('payment_type', request()->input('payment_type'));
                    }
                    if (request()->input('status') != "") {
                        $query->where('status', request()->input('status'));
                    }
                })

                ->editColumn('status', function ($row) {
                    if (Gate::check('payment-collection-edit')) {
                        $payStatus = getPaymentStatus($row->status);

                        $btn = "btn-outline-" . $payStatus['class'];
                        $title = $payStatus['status'];

                        $html = '<div class="">
                        <div class="btn-group p-0">
                        <button type="button" class="btn-sm btn ' . $btn . '">' . $title . '</button>
                        <button type="button" class="btn-sm btn ' . $btn . ' dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown"   aria-expanded="false">
                        <span class="visually-hidden">Toggle Dropdown</span>
                        </button>
                        <div class="dropdown-menu" container="body">
                        <a class="dropdown-item change-status" href="javascript:void(0);" data-id="' . $row->id . '" data-status="1">Approved</a>
                        <a class="dropdown-item change-status" href="javascript:void(0);" data-id="' . $row->id . '" data-status="0">Pending</a>
                        <a class="dropdown-item change-status" href="javascript:void(0);" data-id="' . $row->id . '" data-status="2">Hold</a>
                        <a class="dropdown-item change-status" href="javascript:void(0);" data-id="' . $row->id . '" data-status="3">Return</a>
                        </div>
                        </div>';
                        return $html;

                    } else {
                        $payStatus = getPaymentStatus($row->status);
                        return '<span class="badge bg-light-' . $payStatus['class'] . ' w-100">' . $payStatus['status'] . '</span>';
                    }
                })
                ->addColumn('consumer_name', function ($row) {
                    return '<a target="_blank" href="' . route("sales-master.show", $row->salesMaster->id) . '" ><span  class="d-inline-block text-truncate mt-50" style="max-width: 200px;">' . $row->salesMaster->consumer_name . '</span><p class="mb-50">' . $row->salesMaster->contact_number . '</p></a>';
                })
                ->editColumn('bank_name', function ($row) {
                    return $row->bank_name . '<br/>' . $row->branch_name;
                })
                ->editColumn('utr_number', function ($row) {
                    return $row->utr_number . ' ' . $row->upi_id . ' ' . $row->cheque_number;
                })
                ->addColumn('consumer_number', function ($row) {
                    return $row->salesMaster->consumer_number ?? 'N/A';
                })
                ->addColumn('consumer_type', function ($row) {
                    return $row->salesMaster->consumer_type ?? 'N/A';
                })
                ->addColumn('amount', function ($row) {
                    return "₹ " . number_format($row->amount, 2);
                })
                ->editColumn('payment_date', function ($row) {
                    return date('d-m-Y', strtotime($row->payment_date));
                })
                ->addColumn('action', function ($row) {
                    $html = '<td>';
                    if (Gate::check('payment-collection-edit') && $row->status == 0) {
                        $html .= '<a data-id="' . $row->id . '" href="javascript:void(0);" class="avatar bg-light-info p-50 m-0 edit" data-bs-toggle="tooltip" data-placement="left" title="Edit"><i class="fa fa-edit"></i></a>';
                    }
                    if (Gate::check('payment-collection-delete')) {
                        $html .= ' <a data-id="' . $row->id . '" href="javascript:void(0);" class="avatar bg-light-danger p-50 m-0 delete" data-bs-toggle="tooltip" data-placement="left" title="Delete"><i class="fa fa-trash"></i></a>';
                    }
                    $html .= '</td>';
                    return $html;
                })
                ->escapeColumns([])
                ->make(true);
        } else {
            $banks = Bank::orderBy('name', 'ASC')->get();
            return view('admin.payment-collection.index', compact('sales_payment_dd', 'banks'));
        }
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

            if (!is_null($request->payment_collection_id)) {
                $paymetCollection = PaymetCollection::where('id', $request->payment_collection_id)->first();
                $response = ['data' => route('sales-master.index'), 'status' => true, 'message' => ' Payment Collection updated successfully.'];
            } else {
                $paymetCollection = new PaymetCollection();
                $response = ['data' => route('sales-master.index'), 'status' => true, 'message' => ' Payment Collection added successfully.'];
                 $paymetCollection->sales_master_id = $request->sales_master_id;
            }

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

            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $filename = time() . '_' . $file->getClientOriginalName();
                $file->move(public_path('uploads/payment_collections'), $filename);
                $paymetCollection->file = $filename;
            }

            if (!is_null($request->payment_collection_id)) {
                $paymetCollection->updated_by = Auth::id();
            } else {
                $paymetCollection->created_by = Auth::id();
            }

            $result = $paymetCollection->save();

            if ($request->payment_type == "Disbursement") {
                $salesMaster = SalesMaster::where('id', $request->sales_master_id)->first();
                if ($salesMaster->file_type == "L") {

                    $loansale = Loandata::where('sales_master_id', $request->sales_master_id)->first();
                    if (!is_null($request->payment_collection_id)) {
                        $loandisbursement = Loandisbursement::where('paymet_collection_id', $paymetCollection->id)->first();
                        if (is_null($loandisbursement)) {
                            $loandisbursement = new Loandisbursement();
                        }
                    } else {
                        $loandisbursement = new Loandisbursement();
                    }

                    $loandisbursement->sales_master_id = $request->sales_master_id;
                    $loandisbursement->loandata_id = $loansale->id ?? 0;
                    $loandisbursement->paymet_collection_id = $paymetCollection->id;
                    $loandisbursement->amount = $request->amount;
                    $loandisbursement->disbursement_date = date('Y-m-d', strtotime($request->payment_date));
                    $loandisbursement->remark = $request->remark;
                    $loandisbursement->save();
                } else {
                    $response = ['status' => false, 'message' => 'Cannot process disbursement, this is not a loan file.'];
                    return response()->json($response);
                }
            }
            DB::commit();
            if (!is_null($result)) {
                return response()->json($response);
            } else {
                $response = ['status' => false, 'server_error' => 'Something went wrong. Please try again.'];
                return response()->json($response);
            }
        } catch (\Exception $e) {
            DB::rollback();
            $response = ['status' => false, 'server_error' => $e->getMessage()];
            return response()->json($response);
        }
    }

    public function edit($id)
    {
        $paymetCollection = PaymetCollection::where('id', $id)->first();
        if (!is_null($paymetCollection)) {
            $paymetCollection->payment_date = date('d-m-Y', strtotime($paymetCollection->payment_date));
            if ($paymetCollection->file) {
                $paymetCollection->file_url = asset('uploads/payment_collections/' . $paymetCollection->file);
            }
            $query = SalesMaster::where('pending_amonut', '!=', 0)->orWhere('id', $paymetCollection->sales_master_id);
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
                $query->whereIn('agent_sales_person_id', $agentIds);
            }
            if ($company->user_type == 'S') {
                $agent = AgentSalesPerson::where('user_id', Auth::id())->first();
                $id = $agent->id;
                $query->where('agent_sales_person_id', $id);
            }
            $sales_payment_dd = $query->orderBy('id', 'DESC')->get();
            $res = array('msg_type' => 'success', 'msg_title' => 'Success!', 'result' => $paymetCollection, 'sales_payment_dd' => $sales_payment_dd);
            header('Content-Type:application/json');
            echo json_encode($res);
        } else {
            return abort(404);
        }
    }

    public function destroy($id)
    {
        try {
            $paymetCollection = PaymetCollection::where('id', $id)->first();
            if ($paymetCollection->status == '1') {
                $sales_master_id = $paymetCollection->sales_master_id;
                $salesMaster = SalesMaster::where('id', $sales_master_id)->first();
                $rec_amt = ($paymetCollection->amount != "") ? $paymetCollection->amount : 0;
                $final_amt = $salesMaster->pending_amonut + $rec_amt;
                $salesMaster->pending_amonut = $final_amt;
                if ($final_amt != "0") {
                    $salesMaster->payment_receveid = "0";
                }
                $salesMaster->save();
            }
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
                $paymetCollection->remarks = $request->remarks;
                $salesMaster = SalesMaster::where('id', $sales_master_id)->first();
                $rec_amt = ($paymetCollection->amount != "") ? $paymetCollection->amount : 0;
                $final_amt = $salesMaster->pending_amonut - $rec_amt;
                $salesMaster->pending_amonut = $final_amt;
                if ($final_amt == 0) {
                    $salesMaster->payment_receveid = "1";
                }
                if ($salesMaster->feasibility_approved == "1" && $salesMaster->payment_receveid == "1") {
                    $salesMaster->dispach_pending_list = "1";
                }
                $salesMaster->save();
            } else if ($paymetCollection->status == 1 && ($request->status == 0 || $request->status == 2 || $request->status == 3)) {
                $paymetCollection->status = $request->status;
                $salesMaster = SalesMaster::where('id', $sales_master_id)->first();
                $rec_amt = ($paymetCollection->amount != "") ? $paymetCollection->amount : 0;
                $final_amt = $salesMaster->pending_amonut + $rec_amt;
                $salesMaster->pending_amonut = $final_amt;
                if ($final_amt != "0") {
                    $salesMaster->payment_receveid = "0";
                }
                $salesMaster->save();
            } else if (($paymetCollection->status == 0 || $paymetCollection->status == 2 || $paymetCollection->status == 3) && ($request->status == 0 || $request->status == 2 || $request->status == 3)) {
                $paymetCollection->status = $request->status;
            }

            $paymetCollection->approved_by = Auth::id();
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

    public function paymentReport(Request $request)
    {
        return Excel::download(new PaymentExport($request), 'Cheque_Colletion.xlsx');
    }
}
