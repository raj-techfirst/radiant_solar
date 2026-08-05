<?php

namespace App\Http\Controllers;

use App\Models\Bank;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

use function PHPUnit\Framework\isNull;

class BankController extends Controller
{

    function __construct()
    {
        $this->middleware('permission:bank-list|bank-create|bank-edit|bank-delete', ['only' => ['index', 'store']]);
        $this->middleware('permission:bank-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:bank-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:bank-delete', ['only' => ['destroy']]);
    }
    public function index()
    {
        if (request()->ajax()) {
            return DataTables::of(bank::orderBy('id', 'DESC'))
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $html = '<td>';
                    $url = route('bank.edit', $row->id);
                    if (Gate::check('bank-edit')) {
                        $html .= '<a data-id="' . $row->id . '" href="' . $url . '" class="avatar bg-light-info p-50 m-0 edit" data-bs-toggle="tooltip" data-placement="left" title="Edit"><i class="fa fa-edit"></i></a>';
                    }
                    if (Gate::check('bank-delete')) {
                        $html .= ' <a data-id="' . $row->id . '" href="javascript:void(0);" class="avatar bg-light-danger p-50 m-0 delete" data-bs-toggle="tooltip" data-placement="left" title="Delete"><i class="fa fa-trash"></i></a>';
                    }
                    $html .= '</td>';
                    return $html;
                })
                ->editColumn('default', function ($row) {
                    if ($row->default == '1') {
                        return '<span class="badge bg-light-success w-100">Default</span>';
                    } else {
                        return '';
                    }
                })
                ->escapeColumns([])
                ->make(true);
        } else {
            return view('admin.bank.view');
        }
    }

    public function create()
    {
        return view('admin.bank.index');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'accout_number' => 'required',
            'ifsc_number' => 'required',
            'branch' => 'required',
        ], [
            'name.required' => 'Enter Bank Name',
            'accout_number.required' => 'Enter Account Number',
            'ifsc_number.required' => 'Enter IFSC Code',
            'branch.required' => 'Enter Bank Branch',

        ]);

        if ($validator->fails()) {
            $response = ['status' => false, 'message' => 'Please input proper data.', 'errors' => $validator->errors()];
            return response()->json($response);
        }
        DB::beginTransaction();
        try {
            if (isset($request->bank_id) && !is_null($request->bank_id)) {
                $bank = Bank::where('id', $request->bank_id)->first();
                $response = ['data' => route('bank.index'), 'status' => true, 'message' => ' Bank updated successfully.'];
            } else {
                $bank = new Bank();
                $response = ['data' => route('bank.index'), 'status' => true, 'message' => ' Bank added successfully.'];
            }
            $bank->name = $request->name;
            $bank->holder_name = $request->holder_name;
            $bank->account_number = $request->accout_number;
            $bank->ifsc_number = $request->ifsc_number;
            $bank->branch = $request->branch;
            if (!empty($request->default) && isNull($request->default)) {
                Bank::where('default', "1")->update(['default' => "0"]);

                $bank->default = '1';
              
            } else {
                $bank->default = '0';
            }
            $result = $bank->save();

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

    public function show(Bank $bank)
    {
        //
    }

    public function edit($id)
    {
        $bank = Bank::where('id', $id)->first();
        return view('admin.bank.index', compact('bank'));
        //
    }

    public function update(Request $request, Bank $bank)
    {
        //
    }

    public function destroy($id)
    {
        try {
            $bank = Bank::where('id', $id)->first();
            $bank->delete();
            $response = ['status' => true, 'message' => ' Deleted successfully.'];
            return response()->json($response);
        } catch (\Exception $e) {
            $response = ['status' => false, 'server_error' => 'Something went wrong. Please try again.'];
            return response()->json($response);
        }
    }
}
