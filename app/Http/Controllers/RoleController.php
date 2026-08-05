<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class RoleController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:role-list|role-create|role-edit|role-delete', ['only' => ['index', 'store']]);
        $this->middleware('permission:role-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:role-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:role-delete', ['only' => ['destroy']]);
    }

    public function index(Request $request)
    {
        $where = "1 = 1";
        if (Auth::user()->roles[0]->name != 'Super Admin') {
            $where = 'roles.name != "Super Admin"';
        }
        if (request()->ajax()) {
            return DataTables::of(Role::whereRaw($where)->orderBy('id', 'DESC'))
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $html = '';
                    if ($row->id > 6) {
                        $html .= '<a  href="' . route('roles.show', $row->id) . '" class="avatar bg-light-info p-50 m-0 text-info" data-bs-toggle="tooltip" data-placement="left" title="View"><i class="fa fa-eye"></i></a>';
                    }
                    if (Gate::check('role-edit') && $row->id != 5 && $row->id != 6) {
                        $html .= ' <a  href="' . route('roles.edit', $row->id) . '" class="avatar bg-light-primary p-50 m-0 text-primary edit" data-bs-toggle="tooltip" data-placement="left" title="Edit"><i class="fa fa-edit"></i></a>';
                    }
                    if (Gate::check('role-delete')  && $row->id > 6) {
                        $html .= ' <a data-id="' . $row->id . '" href="javascript:void(0);" class="avatar bg-light-danger p-50 m-0 text-danger delete" data-bs-toggle="tooltip" data-placement="left" title="Delete"><i class="fa fa-trash"></i></a>';
                    }
                    return $html;
                })
                ->escapeColumns([])
                ->make(true);
        } else {
            return view('admin.role-permission.role-index');
        }
    }

    public function create(): View
    {
        $permission = Permission::whereNotIn('title_tag', ['Permission', 'Estimate', 'Source', 'Village'])->orderBy('type', 'asc')->get();
        $permissionTitle = Permission::groupBy('title_tag')->orderBy('type', 'asc')->get();
        $url = route('roles.store');
        return view('admin.role-permission.role-add', compact('permission', 'url', 'permissionTitle'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:roles,name',
            'permission.*' => 'required',
        ], [
            'name.required' => 'Enter role name',
            'name.unique' => 'The role name has already taken',
            'permission.*.required' => 'Select permission',
        ]);

        if ($validator->fails()) {
            return response()->json(array('status_code' => 201, 'message' => 'Please input proper data.', 'errors' => $validator->errors()));
        }

        DB::beginTransaction();
        try {
            $role = Role::create(['name' => $request->input('name')]);
            $permissions = Permission::whereIn('id', $request->input('permission'))->pluck('id')->all();
            $role->syncPermissions($permissions);
            if ($role) {
                DB::commit();
                return response()->json(array('status_code' => 200, 'data' => route('roles.index'), 'message' => 'Role added successfully.'));
            } else {
                DB::rollback();
                return response()->json(array('status_code' => 403, 'message' => 'Something went wrong. Please try again.'));
            }
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(array('status_code' => 500, 'message' => 'Something went wrong. Please try again.'));
        }
    }

    public function show($id)
    {
        try {
            $role = Role::find($id);
            $rolePermissions = Permission::join("role_has_permissions", "role_has_permissions.permission_id", "=", "permissions.id")
                ->where("role_has_permissions.role_id", $id)
                ->get();

            return view('admin.role-permission.role-show', compact('role', 'rolePermissions'));
        } catch (\Exception $e) {
            return response()->json(array('status_code' => 500, 'message' => 'Something went wrong. Please try again.'));
        }
    }

    public function edit($id)
    {
        try {
            $role = Role::find($id);
            $permission = Permission::whereNotIn('title_tag', ['Permission', 'Estimate', 'Source', 'Village'])->orderBy('type', 'asc')->get();
            $permissionTitle = Permission::groupBy('title_tag')->orderBy('type', 'asc')->get();
            $rolePermissions = DB::table("role_has_permissions")->where("role_has_permissions.role_id", $id)
                ->pluck('role_has_permissions.permission_id', 'role_has_permissions.permission_id')
                ->all();
            $url = route('roles.update', $id);
            return view('admin.role-permission.role-edit', compact('role', 'permission', 'rolePermissions', 'url', 'permissionTitle'));
        } catch (\Exception $e) {
            return response()->json(array('status_code' => 500, 'message' => 'Something went wrong. Please try again.'));
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:roles,name,' . $id,
            'permission.*' => 'required',
        ], [
            'name.required' => 'Enter role name',
            'name.unique' => 'The role name has already taken',
            'permission.*.required' => 'Select permission',
        ]);

        if ($validator->fails()) {
            return response()->json(array('status_code' => 201, 'message' => 'Please input proper data.', 'errors' => $validator->errors()));
        }

        DB::beginTransaction();
        try {
            $role = Role::find($id);
            $role->name = $request->input('name');
            $role->save();
            $permissions = Permission::whereIn('id', $request->input('permission'))->pluck('id')->all();
            $role->syncPermissions($permissions);
            if ($role) {
                DB::commit();
                return response()->json(array('status_code' => 200, 'data' => route('roles.index'), 'message' => 'Role updated successfully.'));
            } else {
                DB::rollback();
                return response()->json(array('status_code' => 403, 'message' => 'Something went wrong. Please try again.'));
            }
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(array('status_code' => 500, 'message' => 'Something went wrong. Please try again.'));
        }
    }

    public function destroy($id)
    {
        try {
            DB::table("roles")->where('id', $id)->delete();
            return response()->json(array('status_code' => 200, 'message' => 'Deleted successfully.'));
        } catch (\Exception $e) {
            return response()->json(array('status_code' => 500, 'message' => 'Something went wrong. Please try again.'));
        }
    }
}
