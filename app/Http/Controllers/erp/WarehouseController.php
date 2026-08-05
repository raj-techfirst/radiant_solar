<?php

namespace App\Http\Controllers\erp;

use App\Http\Controllers\Controller;

use App\Models\erp\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class WarehouseController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:warehouse-list', ['only' => ['index']]);
        $this->middleware('permission:warehouse-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:warehouse-edit', ['only' => ['edit']]);
        $this->middleware('permission:warehouse-delete', ['only' => ['destroy']]);
    }

    public function index()
    {
        if (request()->ajax()) {
            return DataTables::of(Warehouse::all())
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $html = '';
                    if (Gate::check('warehouse-edit')) {
                        $html .= '<a data-id="' . $row->id . '" href="javascript:void(0);" class="avatar bg-light-info p-50 m-0 edit" data-bs-toggle="tooltip" data-placement="left" title="Edit"><i class="fa fa-edit"></i></a>';
                    }
                    if (Gate::check('warehouse-delete')) {
                        $html .= ' <a data-id="' . $row->id . '" href="javascript:void(0);" class="avatar bg-light-danger p-50 m-0 delete" data-bs-toggle="tooltip" data-placement="left" title="Delete"><i class="fa fa-trash"></i></a>';
                    }
                    return $html;
                })
                ->escapeColumns([])
                ->make(true);
        } else {
            return view('erp.warehouse.index');
        }
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', Rule::unique('warehouses')->where(function ($query) use ($request) {
                if (!is_null($request->id)) {
                    return $query->where([['deleted_at', '=', null], ['id', '!=', $request->id]]);
                } else {
                    return $query->where([['deleted_at', '=', null]]);
                }
            })],
        ], [
            'name.required' => 'Enter warehouse name',
            'name.unique' => 'The warehouse name has already been taken',
            'address.required' => 'Enter address',
        ]);

        if ($validator->fails()) {
            $response = ['status_code' => 201, 'message' => 'Please input proper data.', 'errors' => $validator->errors()];
            return response()->json($response);
        }

        DB::beginTransaction();
        try {
            if (!is_null($request->id)) {
                $qry = Warehouse::where('id', $request->id)->first();
                $response = array('status_code' => 200, 'message' => 'Warehouse Updated Successfully.');
            } else {
                $qry = new Warehouse();
                $response = array('status_code' => 200, 'message' => 'Warehouse Added Successfully.');
            }

            $qry->name = $request->name;
            $qry->contact_person = $request->contact_person;
            $qry->contact_person_no = $request->contact_person_no;
            $qry->address = $request->address;
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

    public function show(Warehouse $warehouse)
    {
        //
    }

    public function edit($id)
    {
        $qry = Warehouse::where('id', $id)->first();
        if (!is_null($qry)) {
            $res = array('msg_type' => 'success', 'msg_title' => 'Success!', 'result' => $qry);
            header('Content-Type:application/json');
            echo json_encode($res);
        } else {
            return abort(404);
        }
    }

    public function update(Request $request, Warehouse $warehouse)
    {
        //
    }

    public function destroy($id)
    {
        try {
            Warehouse::where('id', $id)->delete();
            return response()->json(['status' => true, 'message' => 'Deleted successfully.']);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'server_error' => 'Something went wrong. Please try again.']);
        }
    }
}
