<?php

namespace App\Http\Controllers;

use App\Models\State;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class StateController extends Controller
{
    function __construct()
    {
         $this->middleware('permission:state-view|state-add|state-edit|state-delete', ['only' => ['index','store']]);
         $this->middleware('permission:state-add', ['only' => ['create','store']]);
         $this->middleware('permission:state-edit', ['only' => ['edit','store']]);
         $this->middleware('permission:state-delete', ['only' => ['destroy']]);
    }

    public function index()
    {
        if (request()->ajax()) {
            return DataTables::of(State::orderBy('state_name', 'ASC'))
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $html = '<div>';
                    if (Gate::allows('state-edit')) {
                        $html .= '<a href="' . route('state.edit', $row->id) . '" class="avatar bg-light-info p-50 m-0" data-bs-toggle="tooltip" data-placement="left" title="Edit"><i class="fa fa-edit"></i></a>';
                    } //$html .= ' <a data-id="' . $row->id . '" href="javascript:void(0);" class="delete avatar bg-light-danger p-50 m-0 text-danger" data-bs-toggle="tooltip" data-placement="left" title="Delete"><i class="fa fa-trash"></i></a>';
                    $html .= '</div>';
                    return $html;
                })
                ->escapeColumns([])
                ->make(true);
        } else {
            return view('state.view_state');
        }
    }

    public function create()
    {
        return view('state.add_state');
    }

    public function store(Request $request)
    {
        if (!is_null($request->state_id)) {
            $state = 'required|unique:states,state_name,' . $request->state_id;
        } else {
            $state = 'required|unique:states,state_name';
        }
        $validator = Validator::make($request->all(), [
            'state_name' => $state
        ], [
            'state_name.required' => 'Enter state',
            'state_name.unique' => 'The state has already been taken'
        ]);
        if ($validator->fails()) {
            $response = ['status' => false, 'message' => 'Please input proper data.', 'errors' => $validator->errors()];
            return response()->json($response);
        }
        DB::beginTransaction();
        try {
            if (!is_null($request->state_id)) {
                $state = State::where('id', $request->state_id)->first();
                $response = ['data' => route('state.index'), 'status' => true, 'message' => ' State updated successfully.'];
            } else {
                $state = new State();
                $response = ['data' => route('state.index'), 'status' => true, 'message' => ' State added successfully.'];
            }
            $state->state_name = $request->state_name;
            $result = $state->save();
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

    public function show(State $state)
    {
        //
    }

    public function edit($id)
    {
        $state = State::where('id', $id)->first();
        return view('state.add_state', compact('state'));
    }

    public function update(Request $request, State $state)
    {
        //
    }

    public function destroy($id)
    {
        try {
            $state = State::where('id', $id)->delete();
            $response = ['status' => true, 'message' => ' State deleted successfully.'];
            return response()->json($response);
        } catch (\Exception $e) {
            $response = ['status' => false, 'server_error' => 'Something went wrong. Please try again.'];
            return response()->json($response);
        }
    }
}
