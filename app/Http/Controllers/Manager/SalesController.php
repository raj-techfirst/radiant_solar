<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\CompanyProfile;
use App\Models\Estimate;
use App\Models\FollowUp;
use App\Models\LeadMaster;
use App\Models\State;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\Facades\DataTables;

class SalesController extends Controller
{
    function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:sales-list|sales-list|sales-edit|sales-delete', ['only' => ['index', 'store']]);
        $this->middleware('permission:sales-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:sales-edit', ['only' => ['edit', 'store']]);
        $this->middleware('permission:sales-delete', ['only' => ['destroy']]);
    }

    public function index()
    {
        $company = CompanyProfile::where('user_id', Auth::id())->first();
        $companyProfile = CompanyProfile::where('manager_id', $company->id)->where('parent_id', $company->parent_id)->with('user', 'state', 'city')->get();
        if (request()->ajax()) {
            return DataTables::of($companyProfile)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $html = '<div class="d-flex">';
                    if (Gate::allows('sales-edit')) {
                        $html .= '<a href="' . route('sales.edit', $row->id) . '" class="avatar bg-light-primary p-50 m-0 text-primary" data-bs-toggle="tooltip" data-placement="left" title="Edit"><i class="fa fa-edit"></i></a>';
                    }
                    if (Gate::allows('sales-delete')) {
                        $html .= ' <a data-id="' . $row->id . '" href="javascript:void(0);" class="avatar bg-light-danger p-50 m-0 text-danger delete" data-bs-toggle="tooltip" data-placement="left" title="Delete"><i class="fa fa-trash"></i></a>';
                    }
                    $html .= '</div>';
                    return $html;
                })
                ->editColumn('name', function ($row) {
                    return $row->user->name . " " . $row->user->last_name;
                })
                ->addColumn('status', function ($row) {
                    $html = '<div class="">';
                    $active1 =  $active2  = $active3 = '';
                    if ($row->user->status == 0) {
                        $btn = "btn-outline-danger";
                        $title = "Reject";
                        $active1 = 'active bg-danger';
                    }
                    if ($row->user->status == 1) {
                        $btn = "btn-outline-success";
                        $title = "Approve";
                        $active2 = 'active bg-success';
                    }
                    if ($row->user->status == 2) {
                        $btn = "btn-outline-warning";
                        $title = "Block";
                        $active3 = 'active bg-warning';
                    }
                    if (Gate::allows('sales-status')) {
                        $html .= '<div class="btn-group">
                                    <button type="button" class="btn-sm btn ' . $btn . '">' . $title . '</button>
                                    <button type="button" class="btn-sm btn ' . $btn . ' dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown"   aria-expanded="false">
                                    <span class="visually-hidden">Toggle Dropdown</span>
                                    </button>
                                    <div class="dropdown-menu p-0" container="body">
                                    <a class="dropdown-item status ' . $active1 . '" href="javascript:void(0);" data-id="' . $row->user->id . '" data-value="0">Reject</a>
                                    <a class="dropdown-item status ' . $active2 . '" href="javascript:void(0);" data-id="' . $row->user->id . '" data-value="1">Approve</a>
                                    <a class="dropdown-item status ' . $active3 . '" href="javascript:void(0);" data-id="' . $row->user->id . '" data-value="2">Block</a>
                                    </div>
                                </div>';
                        return $html;
                    } else {
                        return "";
                    }
                })
                ->editColumn('state_id', function ($row) {
                    if (!is_null($row->state_id)) {
                        return $row->state->state_name;
                    } else {
                        return '';
                    }
                })
                ->editColumn('city_id', function ($row) {
                    if (!is_null($row->city_id)) {
                        return $row->city->city_name;
                    } else {
                        return '';
                    }
                })
                ->rawColumns(['action', 'state_id', 'city_id', 'status', 'name'])
                ->make(true);
        } else {
            return view('manager.sales.view_sales');
        }
    }

    public function create()
    {
        $state = State::get();
        $city = [];
        $company = CompanyProfile::where('user_id', Auth::id())->first();
        $companyManager = CompanyProfile::with('user')->where('parent_id', $company->id)->where('user_type', 'M')->get();
        return view('manager.sales.add_sales', compact('state', 'city', 'companyManager'));
    }

    public function store(Request $request)
    {
        $company = CompanyProfile::where('user_id', Auth::id())->first();
        $companyFind = CompanyProfile::where('id', $company->parent_id)->first();
        $user = User::where('id', $companyFind->user_id)->first();
        $check = CompanyProfile::where('user_type', 'S')->where('parent_id', $company->parent_id)->count();

        $limitField = 'sales_limit';
        $limitLebel = 'sales';
        if ($check <= $user->$limitField || $request->sales_id) {
            if (!is_null($request->sales_id)) {
                $temp = 'nullable';
                $password = 'nullable|min:8|regex:/[a-z]/|regex:/[A-Z]/|regex:/[0-9]/|regex:/[@$!%*#?&]/';
                $confirm_password = 'nullable|same:password';
            } else {
                $temp = 'required|unique:users,email';
                $password = 'required_with:confirm_password|min:8|regex:/[a-z]/|regex:/[A-Z]/|regex:/[0-9]/|regex:/[@$!%*#?&]/';
                $confirm_password = 'required|same:password';
            }
            $validator = Validator::make($request->all(), [
                'name' => 'required',
                'last_name' => 'required',
                'email' => $temp,
                'state_id' => 'required',
                'city_id' => 'required',
                'password' => $password,
                'confirm_password' => $confirm_password,
            ], [
                'name.required' => 'Enter first name',
                'last_name.required' => 'Enter last name',
                'email.required' => 'Enter email',
                'state_id.required' => 'Select state',
                'city_id.required' => 'Select city',
                'password.min' => 'The password must have minimum 8 characters',
                'password.regex' => 'at least 1 uppercase letter, 1 lowercase letter, 1 special character and 1 number',
            ]);
            if ($validator->fails()) {
                $response = ['status' => false, 'message' => 'Please input proper data.', 'errors' => $validator->errors()];
                return response()->json($response);
            }
            DB::beginTransaction();
            try {
                if (!is_null($request->sales_id)) {
                    $companyProfile = CompanyProfile::where('id', $request->sales_id)->first();
                    $user = User::where('id', $companyProfile->user_id)->first();
                    $response = ['data' => route('sales.index'), 'status' => true, 'message' => ' Employee updated successfully.'];
                } else {
                    $user = new User();
                    $companyProfile = new CompanyProfile();
                    $response = ['data' => route('sales.index'), 'status' => true, 'message' => ' Employee added successfully.'];
                }
                $user->name = $request->name;
                $user->last_name = $request->last_name;
                if (!$request->sales_id) {
                    $user->email = $request->email;
                }
                $user->mobile = $request->mobile;
                if ($request->password) {
                    $user->password = Hash::make($request->password);
                }
                $user->status = '1';
                $result = $user->save();
                DB::commit();
                if (!is_null($result)) {
                    if (!$request->sales_id) {
                        $role = Role::where('name', 'Sales')->first();
                        $user->assignRole($role);
                        $company = CompanyProfile::where('user_id', Auth::id())->first();
                        $companyProfile->user_id = $user->id;
                        $companyProfile->parent_id = $company->parent_id;
                        $companyProfile->user_type = 'S';
                    }
                    $companyProfile->manager_id = $company->id;
                    $companyProfile->state_id = $request->state_id;
                    $companyProfile->city_id = $request->city_id;
                    $companyProfile->address = $request->address;
                    $companyProfile->save();
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
        } else {
            $response = ['status' => false, 'label' => $limitLebel, 'message' => 'Your ' . $limitLebel . ' limit`s has been ended.'];
            return response()->json($response);
        }
    }

    public function show(Request $request, $id)
    {
        $user = User::where('id', $id)->first();
        if (!is_null($user)) {
            $user->status = $request->status;
            $user->save();
            $response = ['status' => true, 'message' => ' Status updated successfully.'];
            return response()->json($response);
        } else {
            return abort(404);
        }
    }

    public function edit($id)
    {
        $company = CompanyProfile::where('id', $id)->with('user')->first();
        if (!is_null($company)) {
            $state = State::get();
            $city = City::where('state_id', $company->state_id)->get();
            $companyFind = CompanyProfile::where('user_id', Auth::id())->first();
            $companyManager = CompanyProfile::where('parent_id', $companyFind->id)->where('user_type', 'M')->get();
            return view('manager.sales.add_sales', compact('company', 'state', 'city', 'companyManager'));
        } else {
            return abort(404);
        }
    }

    public function update()
    {
        //
    }

    public function destroy($id)
    {
        try {
            $companyProfile = CompanyProfile::where('id', $id)->first();
            $leadMaster = LeadMaster::where('assign_id', $id)->count();
            $estimate = Estimate::where('assign_id', $id)->count();
            $task = Task::where('assign_id', $id)->count();
            $followUp = FollowUp::where('follow_up_by', $id)->count();
            if ($leadMaster <= 0 && $estimate <= 0 && $task <= 0 && $followUp <= 0) {
                $user = User::where('id', $companyProfile->user_id)->delete();
                $response = ['status' => true, 'message' => ' Deleted successfully.'];
            } else {
                $response = ['status' => false, 'message' => ' Also used this user.'];
            }
            return response()->json($response);
        } catch (\Exception $e) {
            $response = ['status' => false, 'server_error' => 'Something went wrong. Please try again.'];
            return response()->json($response);
        }
    }
}
