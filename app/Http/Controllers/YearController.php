<?php

namespace App\Http\Controllers;

use App\Models\Year;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class YearController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:year-list', ['only' => ['index']]);
        $this->middleware('permission:year-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:year-edit', ['only' => ['edit']]);
        $this->middleware('permission:year-delete', ['only' => ['destroy']]);
    }

    public function index()
    {
        if (request()->ajax()) {
            return DataTables::of(Year::select('id', 'name'))
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $html = '';
                    if (Gate::check('district-edit')) {
                        $html .= '<a data-id="' . $row->id . '" href="javascript:void(0);" class="avatar bg-light-info p-50 m-0 edit" data-bs-toggle="tooltip" data-placement="left" title="Edit"><i class="fa fa-edit"></i></a>';
                    }
                    if (Gate::check('district-delete')) {
                        $html .= ' <a data-id="' . $row->id . '" href="javascript:void(0);" class="avatar bg-light-danger p-50 m-0 delete" data-bs-toggle="tooltip" data-placement="left" title="Delete"><i class="fa fa-trash"></i></a>';
                    }
                    return $html;
                })
                ->escapeColumns([])
                ->make(true);
        } else {
            return view('admin.year.index');
        }
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', Rule::unique('years')->where(function ($query) use ($request) {
                if (!is_null($request->id)) {
                    return $query->where([['deleted_at', null], ['id', '!=', $request->id]]);
                } else {
                    return $query->where([['deleted_at', null]]);
                }
            })],
        ], [
            'name.required' => 'Enter year',
            'name.unique' => 'The year has already been taken'
        ]);

        if ($validator->fails()) {
            $response = ['status' => false, 'message' => 'Please input proper data.', 'errors' => $validator->errors()];
            return response()->json($response);
        }
        DB::beginTransaction();
        try {
            if (!is_null($request->id)) {
                $qry = Year::where('id', $request->id)->first();
                $response = ['data' => route('year.index'), 'status' => true, 'message' => 'Year updated successfully.'];
            } else {
                $qry = new Year();
                $response = ['data' => route('year.index'), 'status' => true, 'message' => 'Year added successfully.'];
            }
            $qry->name = $request->name;
            $result = $qry->save();
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

    public function show(Year $year)
    {
        //
    }

    public function edit($id)
    {
        $qry = Year::where('id', $id)->first();
        if (!is_null($qry)) {
            $res = array('msg_type' => 'success', 'msg_title' => 'Success!', 'result' => $qry);
            header('Content-Type:application/json');
            echo json_encode($res);
        } else {
            return abort(404);
        }
    }

    public function update(Request $request, Year $year)
    {
        //
    }

    public function destroy($id)
    {
        try {
            Year::where('id', $id)->delete();
            return response()->json(['status' => true, 'message' => ' Deleted successfully.']);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'server_error' => 'Something went wrong. Please try again.']);
        }
    }
}
