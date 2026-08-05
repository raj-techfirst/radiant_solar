<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\Facades\DataTables;

class PermissionsController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:permission-view|permission-add|permission-edit|permission-delete', ['only' => ['index', 'store']]);
        $this->middleware('permission:permission-add', ['only' => ['create', 'store']]);
        $this->middleware('permission:permission-edit', ['only' => ['edit', 'store']]);
        $this->middleware('permission:permission-delete', ['only' => ['destroy']]);
    }

    public function index(Request $request)
    {
        if (request()->ajax()) {
            return DataTables::of(Permission::orderBy('role_id', 'ASC'))
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $html = '<div>';
                    if (Gate::allows('role-edit')) {
                        $html .= '<a href="' . route('permission.edit', $row->id) . '" class="avatar bg-light-info p-50 m-0" data-bs-toggle="tooltip" data-placement="left" title="Edit"><i class="fa fa-edit"></i></a>';
                    } //$html .= ' <a data-id="' . $row->id . '" href="javascript:void(0);" class="delete avatar bg-light-danger p-50 m-0 text-danger" data-bs-toggle="tooltip" data-placement="left" title="Delete"><i class="fa fa-trash"></i></a>';
                    $html .= '</div>';
                    return $html;
                })
                ->editColumn('role_id', function ($row) {
                    if ($row->role_id == 1) {
                        return "Super";
                    } elseif ($row->role_id == 2) {
                        return "Owner";
                    } elseif ($row->role_id == 3) {
                        return "Manager";
                    } elseif ($row->role_id == 6) {
                        return "Sales";
                    } else {
                        return "Common";
                    }
                })
                ->rawColumns(['action', 'role_id'])
                ->make(true);
        } else {
            return view('permission.view_permission');
        }
    }

    public function create()
    {
        $role = Role::get();
        return view('permission.add_permission', compact('role'));
    }

    public function store(Request $request)
    {
        if (!is_null($request->permission_id)) {
            $name = 'required|unique:permissions,name,' . $request->permission_id;
            $title = 'required|unique:permissions,title,' . $request->permission_id;
        } else {
            $name = 'required|unique:permissions,name';
            $title = 'required|unique:permissions,title';
        }
        $validator = Validator::make($request->all(), [
            'role_id' => 'required',
            'name' => $name,
            'title' => $title,
        ], [
            'name.required' => 'Enter name',
            'role_id.required' => 'Select role',
            'title.required' => 'Enter title',
        ]);
        if ($validator->fails()) {
            $response = ['status' => false, 'message' => 'Please input proper data.', 'errors' => $validator->errors()];
            return response()->json($response);
        }
        try {
            if (!is_null($request->permission_id)) {
                $permission = Permission::find($request->permission_id);
                $permission->role_id = $request->role_id;
                $permission->name = $request->name;
                $permission->title = $request->title;
                $permission->save();
                $response = ['data' => route('permission.index'), 'status' => true, 'message' => ' Permission updated successfully.'];
            } else {
                Permission::create(['role_id' => $request->input('role_id'), 'name' => $request->input('name'), 'title' => $request->input('title')]);
                $response = ['data' => route('permission.index'), 'status' => true, 'message' => ' Permission added successfully.'];
            }
            return response()->json($response);
        } catch (\Exception $e) {
            $response = ['status' => false, 'server_error' => 'Something went wrong. Please try again.'];
            return response()->json($response);
        }
    }

    public function show($id)
    {
        $permission = Permission::find($id);
        return view('permissions.show', compact('permission'));
    }

    public function edit($id)
    {
        $role = Role::get();
        $permission = Permission::find($id);
        return view('permission.add_permission', compact('role', 'permission'));
    }

    public function destroy($id)
    {
        try {
            Permission::find($id)->delete();
            $response = ['status' => true, 'message' => 'Permission deleted successfully.'];
            return response()->json($response);
        } catch (\Exception $e) {
            $response = ['status' => false, 'server_error' => 'Something went wrong. Please try again.'];
            return response()->json($response);
        }
    }
}
