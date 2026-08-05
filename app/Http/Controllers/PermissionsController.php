<?php

namespace App\Http\Controllers;

use App\Models\Permissions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class PermissionsController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:permission-list|permission-create|permission-edit|permission-delete', ['only' => ['index', 'store']]);
        $this->middleware('permission:permission-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:permission-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:permission-delete', ['only' => ['destroy']]);
    }

    public function index()
    {
        if (request()->ajax()) {
            return DataTables::of(Permissions::all())
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $html = '';
                    if (Gate::check('permission-edit')) {
                        $html .= '<a data-id="' . $row->id . '" href="javascript:void(0);" class="avatar bg-light-primary p-50 m-0 text-primary edit" data-bs-toggle="tooltip" data-placement="left" title="Edit"><i class="fa fa-edit"></i></a>';
                    }
                    if (Gate::check('permission-delete')) {
                        $html .= ' <a data-id="' . $row->id . '" href="javascript:void(0);" class="avatar bg-light-danger p-50 m-0 text-danger delete" data-bs-toggle="tooltip" data-placement="left" title="Delete"><i class="fa fa-trash"></i></a>';
                    }
                    return $html;
                })
                ->escapeColumns([])
                ->make(true);
        } else {
            return view('admin.role-permission.permission_index');
        }
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', Rule::unique('permissions')->where(function ($query) use ($request) {
                if (!is_null($request->id)) {
                    return $query->where('name',  $request->name)->where('deleted_at', null)->where('id', '!=', $request->id);
                } else {
                    return $query->where('name',  $request->name)->where('deleted_at', null);
                }
            })],
            // 'title' => ['required', Rule::unique('permissions')->where(function ($query) use ($request) {
            //     if (!is_null($request->id)) {
            //         return $query->where('title',  $request->title)->where('deleted_at', null)->where('id', '!=', $request->id);
            //     } else {
            //         return $query->where('title',  $request->title)->where('deleted_at', null);
            //     }
            // })],
            'title' => 'required',
            'title_tag' => 'required',
        ], [
            'name.required' => 'Enter name',
            'name.unique' => 'The name has already been taken',
            'title.required' => 'Enter title',
            // 'title.unique' => 'The title has already been taken',
            'title_tag.required' => 'Enter title tag',
        ]);

        if ($validator->fails()) {
            $response = ['status_code' => 201, 'message' => 'Please input proper data.', 'errors' => $validator->errors()];
            return response()->json($response);
        }

        DB::beginTransaction();
        try {
            if (!is_null($request->id)) {
                $oldData = Permissions::where('id', $request->id)->first();
                $permissions = Permissions::where('id', $request->id)->first();
                $response = ['status_code' => 200, 'message' => 'Permissions updated successfully.'];
                $operation = 'Updated (Permissions)';
            } else {
                $permissions = new Permissions();
                $response = ['status_code' => 200, 'message' => 'Permissions added successfully.'];
                $operation = 'Added (Permissions)';
                $oldData = "";
            }

            $permissions->title_tag = ucwords($request->title_tag);
            $permissions->title = ucwords($request->title);
            $permissions->name = $request->name;
            $result = $permissions->save();

            if (!is_null($result)) {
                DB::commit();
                // $data = array(
                //     'module_id' => $permissions->id,
                //     'module_name' => 'Permissions',
                //     'old_data' => $oldData,
                //     'new_data' => $permissions,
                //     'operation' => $operation,
                // );
                // logData($data);
                return response()->json($response);
            } else {
                $response = ['status_code' => 403, 'message' => 'Something went wrong. Please try again.'];
                return response()->json($response);
            }
        } catch (\Exception $e) {
            DB::rollback();
            $response = ['status_code' => 500, 'message' => 'Something went wrong. Please try again.'];
            return response()->json($response);
        }
    }

    public function show(Permissions $permissions)
    {
        //
    }

    public function edit($id)
    {
        try {
            $permissions = Permissions::find($id);
            return response()->json(array('status_code' => 200, 'result' => $permissions));
        } catch (\Exception $e) {
            return response()->json(array('status_code' => 500, 'message' => 'Something went wrong. Please try again.'));
        }
    }

    public function update(Request $request, Permissions $permissions)
    {
        //
    }

    public function destroy($id)
    {
        $permissions = Permissions::where('id',$id)->first();
        try {
            // $data = array(
            //     'module_id' => $permissions->id,
            //     'module_name' => 'Permissions',
            //     'old_data' => $permissions,
            //     'new_data' => $permissions,
            //     'operation' => 'Deleted (Permissions)',
            // );
            // logData($data);
            $permissions->delete();
            $response = ['status_code' => 200, 'message' => 'Deleted successfully.'];
            return response()->json($response);
        } catch (\Exception $e) {
            $response = ['status_code' => 500, 'message' => 'Something went wrong. Please try again.'];
            return response()->json($response);
        }
    }
}
