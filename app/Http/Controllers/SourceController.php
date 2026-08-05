<?php

namespace App\Http\Controllers;

use App\Models\CompanyProfile;
use App\Models\LeadMaster;
use App\Models\Source;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Validation\Rule;

class SourceController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:source-list|source-create|source-edit|source-delete', ['only' => ['index', 'store']]);
        $this->middleware('permission:source-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:source-edit', ['only' => ['edit', 'store']]);
        $this->middleware('permission:source-delete', ['only' => ['destroy']]);
    }

    public function index()
    {
        if (request()->ajax()) {
            return DataTables::of(Source::orderBy('source_name', 'ASC'))
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $html = '<td>';
                    if (Gate::allows('source-edit')) {
                        $html .= '<a href="' . route('source.edit', $row->id) . '" class="avatar bg-light-info p-50 m-0" data-bs-toggle="tooltip" data-placement="left" title="Edit"><i class="fa fa-edit"></i></a>';
                    }
                    if (Gate::allows('source-delete')) {
                        $html .= ' <a data-id="' . $row->id . '" href="javascript:void(0);" id="confirm-text" class="avatar bg-light-danger p-50 m-0 text-danger delete" data-bs-toggle="tooltip" data-placement="left" title="Delete"><i class="fa fa-trash"></i></a>';
                    }
                    $html .= '</td>';
                    return $html;
                })
                ->escapeColumns([])
                ->make(true);
        } else {
            return view('source.view_source');
        }
    }

    public function create()
    {
        return view('source.add_source');
    }

    public function store(Request $request)
    {
        if (!is_null($request->source_id)) {
            $name = 'required|unique:sources,source_name,' . $request->source_id;
        } else {
            $name = 'required|unique:sources,source_name';
        }
        $validator = Validator::make($request->all(), [
            'source_name' => $name
        ], [
            'source_name.required' => 'Enter source',
            'source_name.unique' => 'The source has already been taken'
        ]);
        if ($validator->fails()) {
            $response = ['status' => false, 'message' => 'Please input proper data.', 'errors' => $validator->errors()];
            return response()->json($response);
        }
        DB::beginTransaction();
        try {
            if (!is_null($request->source_id)) {
                $source = Source::where('id', $request->source_id)->first();
                $response = ['data' => route('source.index'), 'status' => true, 'message' => ' Source updated successfully.'];
            } else {
                $source = new Source();
                $response = ['data' => route('source.index'), 'status' => true, 'message' => ' Source added successfully.'];
            }
            $source->source_name = $request->source_name;
            $source->source_link = $request->source_link;            
            $result = $source->save();
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

    public function show(Source $source)
    {
        //
    }

    public function edit($id)
    {
        $source = Source::where('id', $id)->first();
        return view('source.add_source', compact('source'));
    }

    public function update(Request $request, Source $source)
    {
        //
    }

    public function destroy($id)
    {
        try {
            $source = Source::where('id', $id)->first();
            $leadMaster = LeadMaster::where('source_id', $source->id)->count();
            if ($leadMaster <= 0) {
                $source->delete();
                $response = ['status' => true, 'message' => ' Source deleted successfully.'];
            } else {
                $response = ['status' => false, 'message' => ' Also used this source.'];
            }
            return response()->json($response);
        } catch (\Exception $e) {
            $response = ['status' => false, 'server_error' => 'Something went wrong. Please try again.'];
            return response()->json($response);
        }
    }
}
