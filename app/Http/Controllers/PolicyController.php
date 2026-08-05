<?php

namespace App\Http\Controllers;

use App\Models\Policy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class PolicyController extends Controller
{
    public function index()
    {
        //
    }

    public function create(Request $request)
    {

         $policy = Policy::orderBy('id','asc')->where('id',2)->first();

        return view('admin.policy.index', compact('policy'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'policy' => 'required',
                
        ], [
            'policy.required' => 'Enter Policy',

        ]);
        if ($validator->fails()) {
            $response = ['status' => false, 'message' => 'Please input proper data.', 'errors' => $validator->errors()];
            return response()->json($response);
        }
        DB::beginTransaction();
        try {
            if (!is_null($request->policy_id)) {
                $policy = Policy::where('form_type', $request->form_type)->first();
                $response = ['data' => route('policy.create'), 'status' => true, 'message' => ' policy updated successfully.'];
            } else {
                $policy = new Policy();
                $response = ['data' => route('policy.create'), 'status' => true, 'message' => ' policy added successfully.'];
            }
            $policy->policy = $request->policy;
            $result = $policy->save();
            DB::commit();
            if (!is_null($result)) {
                return response()->json($response);
            } else {
                $response = ['status' => true, 'server_error' => 'Something went wrong. Please try again.'];
                return response()->json($response);
            }
        } catch (\Exception $e) {
            DB::rollback();
            $response = ['status' => true, 'server_error' => 'Something went wrong. Please try again.'];
            return response()->json($response);
        }
    }

    public function show(Request $request)
    {
        $policy = Policy::select('form_type','policy')->where('form_type', $request->form_type)->first();
        if(!is_null($policy)){
            $response = ['status' => true, 'policy' => $policy];
        }else{
            $response = ['status' => true, 'policy' => []];
        }
        return response()->json($response);
    }

    public function edit($id)
    {
        $policy = Policy::where('id', $id)->first();
        return view('admin.policy.index', compact('policy'));
    }

    public function update(Request $request, Policy $policy)
    {
        //
    }

    public function destroy(Policy $policy)
    {
        //
    }
}
