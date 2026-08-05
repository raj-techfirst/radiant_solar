<?php

namespace App\Http\Controllers;

use App\Models\EstimateItem;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class UnitController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:unit-list|unit-create|unit-edit|unit-delete', ['only' => ['index', 'store']]);
        $this->middleware('permission:unit-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:unit-edit', ['only' => ['edit', 'store']]);
        $this->middleware('permission:unit-delete', ['only' => ['destroy']]);
    }

    public function index()
    {
        if (request()->ajax()) {
            return DataTables::of(Unit::orderBy('unit_name', 'ASC'))
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $html = '<td>';
                    if (Gate::allows('unit-edit')) {
                        $html .= '<a href="' . route('unit.edit', $row->id) . '" class="avatar bg-light-info p-50 m-0" data-bs-toggle="tooltip" data-placement="left" title="Edit"><i class="fa fa-edit"></i></a>';
                    }
                    if (Gate::allows('unit-delete')) {
                        $html .= ' <a data-id="' . $row->id . '" href="javascript:void(0);" id="confirm-text" class="avatar bg-light-danger p-50 m-0 text-danger delete" data-bs-toggle="tooltip" data-placement="left" title="Delete"><i class="fa fa-trash"></i></a>';
                    }
                    $html .= '</td>';
                    return $html;
                })
                ->escapeColumns([])
                ->make(true);
        } else {
            return view('unit.view_unit');
        }
    }

    public function create()
    {
        return view('unit.add_unit');
    }

    public function store(Request $request)
    {
        if (!is_null($request->unit_id)) {
            $name = 'required|unique:units,unit_name,' . $request->unit_id;
        } else {
            $name = 'required|unique:units,unit_name';
        }
        $validator = Validator::make($request->all(), [
            'unit_name' => $name
        ], [
            'unit_name.required' => 'Enter unit',
            'unit_name.unique' => 'The unit has already been taken'
        ]);
        if ($validator->fails()) {
            $response = ['status' => false, 'message' => 'Please input proper data.', 'errors' => $validator->errors()];
            return response()->json($response);
        }
        DB::beginTransaction();
        try {
            if (!is_null($request->unit_id)) {
                $unit = Unit::where('id', $request->unit_id)->first();
                $response = ['data' => route('unit.index'), 'status' => true, 'message' => ' Unit updated successfully.'];
            } else {
                $unit = new Unit();
                $response = ['data' => route('unit.index'), 'status' => true, 'message' => ' Unit added successfully.'];
            }
            $unit->unit_name = $request->unit_name;
            $result = $unit->save();
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

    public function show(unit $unit)
    {
        //
    }

    public function edit($id)
    {
        $unit = Unit::where('id', $id)->first();
        return view('unit.add_unit', compact('unit'));
    }

    public function update(Request $request, Unit $unit)
    {
        //
    }

    public function destroy($id)
    {
        try {
            $unit = Unit::where('id', $id)->first();
            $estimateItem = EstimateItem::where('unit_id', $unit->id)->count();
            if ($estimateItem <= 0) {
                $unit->delete();
                $response = ['status' => true, 'message' => ' Deleted successfully.'];
            } else {
                $response = ['status' => false, 'message' => ' Also used this unit.'];
            }
            return response()->json($response);
        } catch (\Exception $e) {
            $response = ['status' => false, 'server_error' => 'Something went wrong. Please try again.'];
            return response()->json($response);
        }
    }
}
