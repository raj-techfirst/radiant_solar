<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\Facades\DataTables;

class RoleController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:role-view|role-add|role-edit|role-delete', ['only' => ['index', 'store']]);
        $this->middleware('permission:role-add', ['only' => ['create', 'store']]);
        $this->middleware('permission:role-edit', ['only' => ['edit', 'store']]);
        $this->middleware('permission:role-delete', ['only' => ['destroy']]);
    }

    public function index(Request $request)
    {
        if (request()->ajax()) {
            return DataTables::of(Role::orderBy('id', 'ASC'))
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $html = '<div>';
                    if (Gate::allows('role-edit')) {
                        $html .= '<a href="' . route('role.edit', $row->id) . '" class="avatar bg-light-info p-50 m-0" data-bs-toggle="tooltip" data-placement="left" title="Edit"><i class="fa fa-edit"></i></a>';
                    } // $html .= ' <a data-id="' . $row->id . '" href="javascript:void(0);" class="delete avatar bg-light-danger p-50 m-0 text-danger" data-bs-toggle="tooltip" data-placement="left" title="Delete"><i class="fa fa-trash"></i></a>';
                    $html .= '</div>';
                    return $html;
                })
                ->editColumn('name', function ($row) {
                    return ucfirst($row->name);
                })
                ->rawColumns(['action', 'name'])
                ->make(true);
        } else {
            return view('role.view_role');
        }
    }

    public function create()
    {
        $permission = Permission::get();
        return view('role.add_role', compact('permission'));
    }

    public function store(Request $request)
    {
        if (!is_null($request->role_id)) {
            $name = 'required|unique:roles,name,' . $request->role_id;
        } else {
            $name = 'required|unique:roles,name';
        }
        $validator = Validator::make($request->all(), [
            'name' => $name,
            'permission' => 'required',
        ], [
            'name.required' => 'Enter role name',
            'permission.required' => 'Select permission',
        ]);
        if ($validator->fails()) {
            $response = ['status' => false, 'message' => 'Please input proper data.', 'errors' => $validator->errors()];
            return response()->json($response);
        }
        try {
            if (!is_null($request->role_id)) {
                $role = Role::find($request->role_id);
                $role->name = $request->name;
                $role->syncPermissions($request->input('permission'));
                $role->save();
                $response = ['data' => route('role.index'), 'status' => true, 'message' => ' Role updated successfully.'];
            } else {
                $role = Role::create(['name' => $request->input('name')]);
                $role->syncPermissions($request->input('permission'));
                $response = ['data' => route('role.index'), 'status' => true, 'message' => ' Role added successfully.'];
            }
            return response()->json($response);
        } catch (\Exception $e) {
            $response = ['status' => false, 'server_error' => 'Something went wrong. Please try again.'];
            return response()->json($response);
        }
    }

    public function show($id)
    {
        //
    }

    public function edit($id)
    {
        $role = Role::find($id);
        $permission = Permission::get();
        $rolePermissions = DB::table('role_has_permissions')
            ->where('role_has_permissions.role_id', $id)
            ->pluck('role_has_permissions.permission_id', 'role_has_permissions.permission_id')
            ->all();
        return view('role.add_role', compact('role', 'permission', 'rolePermissions'));
    }

    public function update(Request $request, $id)
    {
        //
    }

    public function destroy($id)
    {
        try {
            Role::find($id)->delete();
            $response = ['status' => true, 'message' => 'Role deleted successfully.'];
            return response()->json($response);
        } catch (\Exception $e) {
            $response = ['status' => false, 'server_error' => 'Something went wrong. Please try again.'];
            return response()->json($response);
        }
    }
}
