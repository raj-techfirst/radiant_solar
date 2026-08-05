<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class SupplierController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:supplier-list', ['only' => ['index']]);
        $this->middleware('permission:supplier-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:supplier-edit', ['only' => ['edit']]);
        $this->middleware('permission:supplier-delete', ['only' => ['destroy']]);
    }

    public function index()
    {
        if (request()->ajax()) {
            return DataTables::of(Supplier::select('id','name', 'mobile', 'email'))
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $html = '';
                    if (Gate::check('supplier-edit')) {
                        $html .= '<a data-id="' . $row->id . '" href="javascript:void(0);" class="avatar bg-light-info p-50 m-0 edit" data-bs-toggle="tooltip" data-placement="left" title="Edit"><i class="fa fa-edit"></i></a>';
                    }
                    if (Gate::check('supplier-delete')) {
                        $html .= ' <a data-id="' . $row->id . '" href="javascript:void(0);" class="avatar bg-light-danger p-50 m-0 delete" data-bs-toggle="tooltip" data-placement="left" title="Delete"><i class="fa fa-trash"></i></a>';
                    }
                    return $html;
                })
                ->escapeColumns([])
                ->make(true);
        } else {
            return view('admin.supplier.index');
        }
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', Rule::unique('suppliers')->where(function ($query) use ($request) {
                if (!is_null($request->id)) {
                    return $query->where([['deleted_at', '=', null], ['id', '!=', $request->id]]);
                } else {
                    return $query->where([['deleted_at', '=', null]]);
                }
            })],
            'mobile' =>  ['digits:10', Rule::unique('suppliers')->where(function ($query) use ($request) {
                if (!is_null($request->id)) {
                    return $query->where([['deleted_at', '=', null], ['id', '!=', $request->id]]);
                } else {
                    return $query->where([['deleted_at', '=', null]]);
                }
            })],
        ], [
            'name.required' => 'Enter Name',
            'name.unique' => 'The name has already been taken',
            'address.required' => 'Enter Address',
            'mobile.digits' => 'Please enter mobile number at least 10 digits.',
            'mobile.unique' => 'The mobile number has already aeen taken',
        ]);

        if ($validator->fails()) {
            $response = ['status_code' => 201, 'message' => 'Please input proper data.', 'errors' => $validator->errors()];
            return response()->json($response);
        }

        DB::beginTransaction();
        try {
            if (!is_null($request->id)) {
                $qry = Supplier::where('id', $request->id)->first();
                $response = array('status_code' => 200, 'data' => route('supplier.index'), 'message' => 'Supplier Updated Successfully.');
            } else {
                $qry = new Supplier();
                $response = array('status_code' => 200, 'data' => route('supplier.index'), 'message' => 'Supplier Added Successfully.');
            }

            $qry->name = $request->name;
            $qry->email = $request->email;
            $qry->mobile = $request->mobile;
            $qry->alt_mobile = $request->alt_mobile;
            $qry->address = $request->address;
            $qry->country = $request->country;
            $qry->state = $request->state;
            $qry->city = $request->city;
            $qry->payment_condition = $request->payment_condition;
            $qry->gst_number = $request->gst_number;
            $result = $qry->save();
            DB::commit();
            if (!is_null($result)) {
                return response()->json($response);
            } else {
                DB::rollback();
                return response()->json(array('status_code' => 403, 'message' => 'Something went wrong. Please try again.'));
            }
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(array('status_code' => 500, 'message' => 'Something went wrong. Please try again.'));
        }
    }

    public function show(Supplier $supplier)
    {
        //
    }

    public function edit($id)
    {
        $qry = Supplier::where('id', $id)->first();
        if (!is_null($qry)) {
            $res = array('msg_type' => 'success', 'msg_title' => 'Success!', 'result' => $qry);
            header('Content-Type:application/json');
            echo json_encode($res);
        } else {
            return abort(404);
        }
    }

    public function update(Request $request, Supplier $supplier)
    {
        //
    }

    public function destroy($id)
    {
        try {
            // $purchaseOrder  = PurchaseOrder::where('vendor_id', $id)->count();
            // if ($purchaseOrder > 0) {
            //     $response = ['status_code' => 201, 'message' => 'The Supplier exists in  other module.'];
            //     return response()->json($response);
            // } else {
            Supplier::where('id', $id)->delete();
            return response()->json(['status' => true, 'message' => ' Deleted successfully.']);
            // }
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'server_error' => 'Something went wrong. Please try again.']);
        }
    }
}
