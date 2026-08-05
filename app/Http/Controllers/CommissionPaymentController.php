<?php

namespace App\Http\Controllers;

use App\Models\CommissionPayment;
use App\Models\CompanyProfile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class CommissionPaymentController extends Controller
{

    function __construct()
    {
        $this->middleware('permission:commission-payment-list|commission-payment-create|commission-payment-edit|commission-payment-delete', ['only' => ['index', 'store']]);
        $this->middleware('permission:commission-payment-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:commission-payment-edit', ['only' => ['edit']]);
        $this->middleware('permission:commission-payment-delete', ['only' => ['destroy']]);
    }

    public function index()
    {
        if (request()->ajax()) {
            return DataTables::of(CommissionPayment::with(['user','approver'])->orderBy('id', 'DESC'))
                ->addIndexColumn()
                ->filter(function ($query) {
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
                    if (request()->input('user') != "") {
                        $query->whereHas('user', function ($q) {
                            $q->where('name', 'like', '%' . request()->input('user') . '%');
                            $q->orWhere('email', 'like', '%' . request()->input('user') . '%');
                            $q->orWhere('mobile', 'like', '%' . request()->input('user') . '%');
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
                        $html = '<div class=""><div class="btn-group p-0">'
                            .'<button type="button" class="btn-sm btn ' . $btn . '">' . $title . '</button>'
                            .'<button type="button" class="btn-sm btn ' . $btn . ' dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown"   aria-expanded="false">'
                            .'<span class="visually-hidden">Toggle Dropdown</span>'
                            .'</button>'
                            .'<div class="dropdown-menu" container="body">'
                            .'<a class="dropdown-item change-status" href="javascript:void(0);" data-id="' . $row->id . '" data-status="1">Approved</a>'
                            .'<a class="dropdown-item change-status" href="javascript:void(0);" data-id="' . $row->id . '" data-status="0">Pending</a>'
                            .'<a class="dropdown-item change-status" href="javascript:void(0);" data-id="' . $row->id . '" data-status="2">Hold</a>'
                            .'<a class="dropdown-item change-status" href="javascript:void(0);" data-id="' . $row->id . '" data-status="3">Return</a>'
                            .'</div></div>';
                        return $html;
                    } else {
                        $payStatus = getPaymentStatus($row->status);
                        return '<span class="badge bg-light-' . $payStatus['class'] . ' w-100">' . $payStatus['status'] . '</span>';
                    }
                })
                ->addColumn('user_name', function ($row) {
                    if (!$row->user) return 'N/A';
                    return '<span class="d-inline-block text-truncate mt-50" style="max-width: 200px;">' . e($row->user->name) . ' ' . e($row->user->last_name) . '</span>';
                })
                ->addColumn('amount', function ($row) {
                    return "₹ ". number_format($row->amount, 2);
                })
                ->editColumn('payment_date', function ($row) {
                    return $row->payment_date ? date('d-m-Y', strtotime($row->payment_date)) : '';
                })
                ->addColumn('bank_name', function ($row) {
                    return ($row->bank_name ?: '') . '<br/>' . ($row->branch_name ?: '');
                })
                ->addColumn('utr_number', function ($row) {
                    return ($row->utr_number ?: '') . ' ' . ($row->upi_id ?: '') . ' ' . ($row->cheque_number ?: '');
                })
                ->addColumn('action', function ($row) {
                    $html = '<td>';
                    if (Gate::check('commission-payment-edit') && $row->status == 0) {
                        $html .= '<a data-id="' . $row->id . '" href="javascript:void(0);" class="avatar bg-light-info p-50 m-0 edit" data-bs-toggle="tooltip" data-placement="left" title="Edit"><i class="fa fa-edit"></i></a>';
                    }
                    if (Gate::check('commission-payment-delete')) {
                        $html .= ' <a data-id="' . $row->id . '" href="javascript:void(0);" class="avatar bg-light-danger p-50 m-0 delete" data-bs-toggle="tooltip" data-placement="left" title="Delete"><i class="fa fa-trash"></i></a>';
                    }
                    $html .= '</td>';
                    return $html;
                })
                ->escapeColumns([])
                ->make(true);
        } else {
            $users = User::orderBy('name')->get();
            return view('admin.commission-payment.index', compact('users'));
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        abort(404);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'payment_type' => 'required',
            'amount' => 'required|numeric',
            'payment_date' => 'required'
        ], [
            'user_id.required' => 'Select User',
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
            if (!is_null($request->commission_payment_id)) {
                $commissionPayment = CommissionPayment::where('id', $request->commission_payment_id)->first();
                $response = ['data' => route('commission-payment.index'), 'status' => true, 'message' => 'Commission Payment updated successfully.'];
            } else {
                $commissionPayment = new CommissionPayment();
                $response = ['data' => route('commission-payment.index'), 'status' => true, 'message' => 'Commission Payment added successfully.'];
            }

            $commissionPayment->user_id  = $request->user_id;
            $commissionPayment->payment_type = $request->payment_type;
            $commissionPayment->amount = $request->amount;
            $commissionPayment->payment_date = date('Y-m-d', strtotime($request->payment_date));
            $commissionPayment->cheque_number = $request->cheque_number;
            $commissionPayment->bank_name = $request->bank_name;
            $commissionPayment->branch_name = $request->branch_name;
            $commissionPayment->utr_number = $request->utr_number;
            $commissionPayment->upi_id = $request->upi_id;
            $commissionPayment->remark = $request->remark;

            $result = $commissionPayment->save();
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

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\CommissionPayment  $commissionPayment
     * @return \Illuminate\Http\Response
     */
    public function show(CommissionPayment $commissionPayment)
    {
        abort(404);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\CommissionPayment  $commissionPayment
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $commissionPayment = CommissionPayment::where('id', $id)->with('user')->first();
        if (!is_null($commissionPayment)) {
            $commissionPayment->payment_date = $commissionPayment->payment_date ? date('d-m-Y', strtotime($commissionPayment->payment_date)) : '';
            $users = User::orderBy('name')->get();
            $res = array('msg_type' => 'success', 'msg_title' => 'Success!', 'result' => $commissionPayment, 'users' =>  $users);
            header('Content-Type:application/json');
            echo json_encode($res);
        } else {
            return abort(404);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\CommissionPayment  $commissionPayment
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, CommissionPayment $commissionPayment)
    {
        abort(404);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\CommissionPayment  $commissionPayment
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $commissionPayment = CommissionPayment::where('id', $id)->first();
            $commissionPayment->delete();
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
            $commissionPayment = CommissionPayment::where('id', $request->id)->first();

            $commissionPayment->status = (int) $request->status;
            $commissionPayment->approved_by = Auth::id();
            $commissionPayment->remarks = $request->remarks;
            $res =  $commissionPayment->save();
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
}
