<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\State;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Validation\Rule;

class CityController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:city-view|city-add|city-edit|city-delete', ['only' => ['index', 'store']]);
        $this->middleware('permission:city-add', ['only' => ['create', 'store']]);
        $this->middleware('permission:city-edit', ['only' => ['edit', 'store']]);
        $this->middleware('permission:city-delete', ['only' => ['destroy']]);
    }

    public function index()
    {
        if (request()->ajax()) {
            return DataTables::of(City::with('state')->orderBy('state_id', 'ASC'))
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $html = '<div>';
                    if (Gate::allows('city-edit')) {
                        $html .= '<a href="' . route('city.edit', $row->id) . '" class="avatar bg-light-info p-50 m-0" data-bs-toggle="tooltip" data-placement="left" title="Edit"><i class="fa fa-edit"></i></a>';
                    } //$html .= ' <a data-id="' . $row->id . '" href="javascript:void(0);" class="delete avatar bg-light-danger p-50 m-0 text-danger" data-bs-toggle="tooltip" data-placement="left" title="Delete"><i class="fa fa-trash"></i></a>';
                    $html .= '</div>';
                    return $html;
                })
                ->escapeColumns([])
                ->make(true);
        } else {
            return view('city.view_city');
        }
    }

    public function create()
    {
        $state = State::get();
        return view('city.add_city', compact('state'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'state_id' => 'required',
            'city_name' => [
                'required',
                Rule::unique('cities')->where(function ($query) use ($request) {
                    return $query->where('state_id', $request->state_id);
                })->ignore($request->city_id),
            ],
        ], [
            'city_name.required' => 'Enter city',
            'city_name.unique' => 'The city has already been taken'

        ]);
        if ($validator->fails()) {
            $response = ['status' => false, 'message' => 'Please input proper data.', 'errors' => $validator->errors()];
            return response()->json($response);
        }
        DB::beginTransaction();
        try {
            if (!is_null($request->city_id)) {
                $city = City::where('id', $request->city_id)->first();
                $response = ['data' => route('city.index'), 'status' => true, 'message' => ' City updated successfully.'];
            } else {
                $city = new City();
                $response = ['data' => route('city.index'), 'status' => true, 'message' => ' City added successfully.'];
            }
            $city->state_id = $request->state_id;
            $city->city_name = $request->city_name;
            $result = $city->save();
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
        $city = City::where('state_id', $request->state_id)->get();
        return response()->json($city);
    }

    public function edit($id)
    {
        $state = State::get();
        $city = City::where('id', $id)->first();
        return view('city.add_city', compact('city', 'state'));
    }

    public function update($id)
    {
        //
    }

    public function destroy(City $city)
    {
        //
    }
}
