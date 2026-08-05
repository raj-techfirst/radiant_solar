<?php

namespace App\Http\Controllers;

use App\Models\AgentSalesPerson;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class AgentSalesPersonController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:agent-sales-list|agent-sales-create|agent-sales-edit|agent-sales-delete', ['only' => ['index', 'store']]);
        $this->middleware('permission:agent-sales-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:agent-sales-edit', ['only' => ['edit', 'store']]);
        $this->middleware('permission:agent-sales-delete', ['only' => ['destroy']]);
    }

    public function index()
    {
        if (request()->ajax()) {
            return DataTables::of(AgentSalesPerson::orderBy('name', 'ASC')->orderBy('id', 'DESC'))
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $html = '<td>';
                    if (Gate::check('agent-sales-edit')) {
                        $html .= '<a data-id="' . $row->id . '" href="javascript:void(0);" class="avatar bg-light-info p-50 m-0 edit" data-bs-toggle="tooltip" data-placement="left" title="Edit"><i class="fa fa-edit"></i></a>';
                    }
                    if (Gate::check('agent-sales-delete')) {
                        $html .= ' <a data-id="' . $row->id . '" href="javascript:void(0);" class="avatar bg-light-danger p-50 m-0 delete" data-bs-toggle="tooltip" data-placement="left" title="Delete"><i class="fa fa-trash"></i></a>';
                    }
                    $html .= '</td>';
                    return $html;
                })
                ->escapeColumns([])
                ->make(true);
        } else {
            return view('admin.agent-sales.index');
        }
    }
    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        //  dd($request->name);
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'number' => 'required'
        ], [
            'name.required' => 'Enter Agent Sales Person Name',
            'number.required' => 'Enter Number',
        ]);

        if ($validator->fails()) {
            $response = ['status' => false, 'message' => 'Please input proper data.', 'errors' => $validator->errors()];
            return response()->json($response);
        }
        DB::beginTransaction();
        try {
            if (!is_null($request->agent_sales_person_id)) {
                $agentSalesPerson = AgentSalesPerson::where('id', $request->agent_sales_person_id)->first();
                $response = ['data' => route('agent-sales-person.index'), 'status' => true, 'message' => ' Agent Sales Person updated successfully.'];
            } else {
                $agentSalesPerson = new AgentSalesPerson();
                $response = ['data' => route('agent-sales-person.index'), 'status' => true, 'message' => ' Agent Sales Person added successfully.'];
            }
            $agentSalesPerson->name = $request->name;
            $agentSalesPerson->number = $request->number;
            $result = $agentSalesPerson->save();
            DB::commit();
            if (!is_null($result)) {
                return response()->json($response);
            } else {
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

    public function show(AgentSalesPerson $agentSalesPerson)
    {
        //
    }

    public function edit($id)
    {
        $agentSalesPerson = AgentSalesPerson::where('id', $id)->first();
        if (!is_null($agentSalesPerson)) {
            $res = array('msg_type' => 'success', 'msg_title' => 'Success!', 'result' => $agentSalesPerson);
            header('Content-Type:application/json');
            echo json_encode($res);
        } else {
            return abort(404);
        }
    }

    public function update(Request $request, AgentSalesPerson $agentSalesPerson)
    {
        //
    }

    public function destroy($id)
    {
        try {
            $agentSalesPerson = AgentSalesPerson::where('id', $id)->first();
            $agentSalesPerson->delete();
            $response = ['status' => true, 'message' => ' Deleted successfully.'];
            return response()->json($response);
        } catch (\Exception $e) {
            $response = ['status' => false, 'server_error' => 'Something went wrong. Please try again.'];
            return response()->json($response);
        }
    }
}
