<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AgentSalesPerson;
use App\Models\City;
use App\Models\CompanyProfile;
use App\Models\District;
use App\Models\Estimate;
use App\Models\FollowUp;
use App\Models\LeadMaster;
use App\Models\UserCommission;
use App\Models\SalesMaster;
use App\Models\SalesQuatation;
use App\Models\State;
use App\Models\Taluka;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;
use Spatie\Permission\Models\Role;

class EmployeeController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:employee-list|employee-create|employee-edit|employee-delete', ['only' => ['index', 'store']]);
        $this->middleware('permission:employee-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:employee-edit', ['only' => ['edit', 'store']]);
        $this->middleware('permission:employee-delete', ['only' => ['destroy']]);
        $this->middleware('permission:employee-status', ['only' => ['show']]);
    }

    public function index()
    {
        $company = CompanyProfile::where('user_id', Auth::id())->first();
        $companyProfile = CompanyProfile::where('parent_id', $company->id)->with('user', 'manager.user', 'state', 'city')->get();
        if (request()->ajax()) {
            return DataTables::of($companyProfile)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    // $html = '<a data-id="' . $row->id . '" href="javascript:void(0)" class="view avatar bg-light-success p-50 m-0" data-bs-toggle="tooltip" data-placement="left" title="View"><i class="fa fa-eye"></i></a>';
                    if (Gate::allows('employee-edit')) {
                        $html = ' <a href="' . route('employee.edit', $row->id) . '" class="avatar bg-light-info p-50 m-0" data-bs-toggle="tooltip" data-placement="left" title="Edit"><i class="fa fa-edit"></i></a>';
                    }
                    if (Gate::allows('employee-delete')) {
                        $html .= ' <a data-id="' . $row->id . '" href="javascript:void(0);" class="delete avatar bg-light-danger p-50 m-0" data-bs-toggle="tooltip" data-placement="left" title="Delete"><i class="fa fa-trash"></i></a>';
                    }
                    return $html;
                })
                ->addColumn('status', function ($row) {
                    $html = '<div class="">';
                    $active1 =  $active2  = $active3 = '';
                    if ($row->user->status == 0) {
                        $btn = "btn-outline-danger";
                        $title = "Deactivate";
                        $active1 = 'active bg-danger';
                    }
                    if ($row->user->status == 1) {
                        $btn = "btn-outline-success";
                        $title = "Active";
                        $active2 = 'active bg-success';
                    }
                    if (Gate::allows('employee-status')) {
                        $html .= '<div class="btn-group">
                                    <button type="button" class="btn-sm btn ' . $btn . '">' . $title . '</button>
                                    <button type="button" class="btn-sm btn ' . $btn . ' dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown"   aria-expanded="false">
                                        <span class="visually-hidden">Toggle Dropdown</span>
                                    </button>
                                    <div class="dropdown-menu p-0" container="body">
                                        <a class="dropdown-item status ' . $active1 . '" href="javascript:void(0);" data-id="' . $row->user->id . '" data-value="0">Deactivate</a>
                                        <a class="dropdown-item status ' . $active2 . '" href="javascript:void(0);" data-id="' . $row->user->id . '" data-value="1">Active</a>
                                    </div>
                                </div>
                            </div>';
                        return $html;
                    } else {
                        return "";
                    }
                })
                ->editColumn('user_type', function ($row) {
                    if ($row->user->roles[0]->name == 'Manager') {
                        return '<span class="badge bg-light-primary w-100">Manager</span>';
                    } else if ($row->user->roles[0]->name == 'Sales') {
                        return '<span class="badge bg-light-success w-100">Sales</span>';
                    } else if ($row->user->roles[0]->name == 'Office') {
                        return '<span class="badge bg-light-warning w-100">Office</span>';
                    } else {
                        return '<span class="badge bg-light-info w-100">' . $row->user->roles[0]->name . '</span>';
                    }
                })
                ->editColumn('name', function ($row) {
                    return $row->user->name . " " . $row->user->last_name;
                })
                ->editColumn('manager_id', function ($row) {
                    if ($row->manager_id == 0 && $row->user_type == 'M') {
                        return "Self";
                    } else if ($row->manager_id == 0) {
                        return "-";
                    } else {
                        return $row->manager->user->name . " " . $row->manager->user->last_name;
                    }
                })
                ->rawColumns(['action', 'status', 'user_type', 'name', 'manager_id'])
                ->make(true);
        } else {
            return view('admin.employee.view_employee');
        }
    }

    public function create()
    {
        $district = District::get();
        $taluka = Taluka::get();
        $roles = Role::where('id', '>', 4)->get();
        $company = CompanyProfile::where('user_id', Auth::id())->first();
        $companyManager = CompanyProfile::with('user')->get(); //->where('parent_id', $company->id)->where('user_type', 'M')
        $userCommissions = collect();
        return view('admin.employee.add_employee', compact('district', 'taluka', 'companyManager', 'roles', 'userCommissions'));
    }

    public function store(Request $request)
    {
        $company = CompanyProfile::where('user_id', Auth::id())->first();
        $check = CompanyProfile::where('user_type', $request->user_type)->where('parent_id', $company->id)->count();
        if ($request->user_type == 'M') {
            $limitField = 'manager_limit';
            $limitLebel = 'manager';
        } else {
            $limitField = 'sales_limit';
            $limitLebel = 'sales';
        }
        if ($check < Auth::user()->$limitField || $request->employee_id) {
            if (!is_null($request->employee_id)) {
                $mobile = 'nullable';
                $temp = 'nullable';
                $password = 'nullable|min:8|regex:/[a-z]/|regex:/[A-Z]/|regex:/[0-9]/|regex:/[@$!%*#?&]/';
                $confirm_password = 'nullable|same:password';
            } else {
                $temp = 'required|unique:users,email';
                $mobile = 'required|unique:users,mobile';
                $password = 'required_with:confirm_password|min:8|regex:/[a-z]/|regex:/[A-Z]/|regex:/[0-9]/|regex:/[@$!%*#?&]/';
                $confirm_password = 'required|same:password';
            }
            $validator = Validator::make($request->all(), [
                'name' => 'required',
                'last_name' => 'required',
                'email' => $temp,
                'mobile' => $mobile,
                'district_id' => 'required',
                'taluka_id' => 'required',
                'password' => $password,
                'confirm_password' => $confirm_password,
            ], [
                'name.required' => 'Enter first name',
                'last_name.required' => 'Enter last name',
                'email.required' => 'Enter email',
                'mobile.required' => 'Enter mobile',
                'district_id.required' => 'Select state',
                'taluka_id.required' => 'Select taluka',
                'password.min' => 'The password must have minimum 8 characters',
                'password.regex' => 'at least 1 uppercase letter, 1 lowercase letter, 1 special character and 1 number',
            ]);
            if ($validator->fails()) {
                $response = ['status' => false, 'message' => 'Please input proper data.', 'errors' => $validator->errors()];
                return response()->json($response);
            } else if ($request->user_type == 'S' && $request->manager_id == "" && $request->manager_id == 0) {
                $response = ['status' => false, 'message' => 'Please Select Manager.', 'manager' => 'Select Manager'];
                return response()->json($response);
            } else if ($request->user_type == 'O' && $request->role == "" && $request->role == 0) {
                $response = ['status' => false, 'message' => 'Please Select Role.', 'manager' => 'Select Manager'];
                return response()->json($response);
            }
            DB::beginTransaction();
            try {
                if (!is_null($request->employee_id)) {
                    $companyProfile = CompanyProfile::where('id', $request->employee_id)->first();
                    $user = User::where('id', $companyProfile->user_id)->first();
                    $response = ['data' => route('employee.index'), 'status' => true, 'message' => 'User updated successfully.'];
                } else {
                    $user = new User();
                    $companyProfile = new CompanyProfile();
                    $response = ['data' => route('employee.index'), 'status' => true, 'message' => 'User added successfully.'];
                }
                $user->name = $request->name;
                $user->last_name = $request->last_name;
                if (!$request->employee_id) {
                    $user->email = $request->email;
                }
                $user->mobile = $request->mobile;
                if ($request->password) {
                    $user->password = Hash::make($request->password);
                }
                $user->status = '1';
                $user->if_erp = !empty($request->if_erp) ? 1 : 0;
                $result = $user->save();

                if (!is_null($result)) {

                    if (!is_null($request->employee_id)) {
                        $user->removeRole($user->roles->first());
                    }
                    if ($request->user_type == 'M') {
                        $role = Role::where('name', 'Manager')->first();
                    } elseif ($request->user_type == 'S') {
                        $role = Role::where('name', 'Sales')->first();
                    } else {
                        $role = Role::where('name', $request->role)->first();
                    }

                    $user->assignRole($role);

                    if (!is_null($request->employee_id)) {
                        $agentSalesPerson = AgentSalesPerson::where('user_id', $user->id)->first();
                        if (is_null($agentSalesPerson)) {
                            $agentSalesPerson = new AgentSalesPerson();
                        }
                    } else {
                        $agentSalesPerson = new AgentSalesPerson();
                    }

                    $agentSalesPerson->user_id = $user->id;
                    $agentSalesPerson->name = $request->name . ' ' . $request->last_name;
                    $agentSalesPerson->number = $request->mobile;
                    $result = $agentSalesPerson->save();

                    $company = CompanyProfile::where('user_id', Auth::id())->first();
                    $companyProfile->user_id = $user->id;
                    $companyProfile->parent_id = $company->id;
                    $companyProfile->user_type = $request->user_type;

                    $companyProfile->manager_id = (!empty($request->manager_id)) ? $request->manager_id : 0;
                    $companyProfile->state_id = $request->district_id;
                    $companyProfile->city_id = $request->taluka_id;
                    $companyProfile->address = $request->address;
                    $companyProfile->save();

                    // Handle commission rows
                    $invoiceRows = $request->get('invoice', []);

                    // Validate: no duplicate (effective_date, sub_agent_id) pairs for this user
                    $seenKeys = [];
                    foreach ($invoiceRows as $row) {
                        if (empty($row['effective_date'])) {
                            // skip empty entries from blank rows
                            continue;
                        }
                        $subAgent = isset($row['sub_agent_id']) && $row['sub_agent_id'] != '' ? (string)$row['sub_agent_id'] : '0';
                        $key = $row['effective_date'] . '|' . $subAgent;
                        if (isset($seenKeys[$key])) {
                            DB::rollBack();
                            return response()->json([
                                'status' => false,
                                'message' => 'Duplicate commission rows are not allowed for same date and sub agent.',
                                'errors' => ['invoice' => ['Duplicate date and sub agent found in commission details.']]
                            ]);
                        }
                        $seenKeys[$key] = true;
                    }
                    UserCommission::where('user_id', $user->id)->delete();
                    if (!empty($invoiceRows) && is_array($invoiceRows)) {
                        foreach ($invoiceRows as $row) {
                            if (empty($row['effective_date'])) {
                                continue;
                            }
                            if (isset($row['sub_agent_id']) && $row['sub_agent_id'] != '' && $row['sub_agent_id'] != 0) {
                                $subAgentId = (int) $row['sub_agent_id'];
                                $existing = UserCommission::where('sub_agent_id', $subAgentId)
                                    ->where('user_id', '!=', $user->id)
                                    ->first();
                                if ($existing) {
                                    DB::rollBack();
                                    $existingUser = User::where('id', $existing->user_id)->first();

                                    $subAgent = CompanyProfile::where('id', $subAgentId)->first();
                                    $subagentname = $subAgent ? $subAgent->user->name . ' ' . $subAgent->user->last_name : 'This sub agent';
                                    return response()->json([
                                        'status' => false,
                                        'message' => $subagentname . ' already assigned to another user: ' . $existingUser->name . ' ' . $existingUser->last_name,
                                        'errors' => ['invoice' => [$subagentname . ' already assigned to another user: ' . $existingUser->name . ' ' . $existingUser->last_name]]
                                    ]);
                                }
                            }
                            UserCommission::create([
                                'user_id' => $user->id,
                                'effective_date' => $row['effective_date'],
                                'commission' => isset($row['commission']) ? (float) $row['commission'] : 0,
                                'installation' => isset($row['installation']) ? (float) $row['installation'] : 0,
                                'sub_agent_id' => isset($row['sub_agent_id']) && $row['sub_agent_id'] != '' ? (int) $row['sub_agent_id'] : 0,
                            ]);
                        }
                        // After saving all slabs, optionally recalculate SalesMaster commission fields
                        if ($request->boolean('recalculate_commission')) {
                            if (!function_exists('recalculateSalesMasterCommissionByEffectiveDates')) {
                                // helper autoloaded via composer; just in case, ensure availability
                            }
                            recalculateSalesMasterCommissionByEffectiveDates($user->id);
                        }
                    }
                    DB::commit();
                    return response()->json($response);
                } else {
                    $response = ['status' => false, 'server_error' => 'Something went wrong. Please try again.'];
                    return response()->json($response);
                }
            } catch (\Exception $e) {
                DB::rollback();
                $response = ['status' => false, 'server_error' => 'Something went wrong. Please try again.', 'e' => $e];
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
        $roles = Role::where('id', '>', 4)->orWhere('id', 2)->get();
        $company = CompanyProfile::where('id', $id)->with('user')->first();
        if (!is_null($company)) {
            $district = District::get();
            $taluka = Taluka::where('district_id', $company->state_id)->get();
            $companyFind = CompanyProfile::where('user_id', Auth::id())->first();
         $companyManager = CompanyProfile::with('user')->get(); //->where('parent_id', $company->id)->where('user_type', 'M')
            $userCommissions = UserCommission::where('user_id', $company->user_id)->orderBy('effective_date')->get();
            return view('admin.employee.add_employee', compact('company', 'district', 'taluka', 'companyManager', 'roles', 'userCommissions'));
        } else {
            return abort(404);
        }
    }

    public function update($id)
    {
        $companyProfile = CompanyProfile::with('user', 'state', 'city')->where('id', $id)->first();
        if (!is_null($companyProfile)) {
            $data['html'] = view('admin.employee.model', compact('companyProfile'))->render();
            return response()->json($data);
        } else {
            return abort(404);
        }
    }


    public function destroy($id)
    {
        try {
            $manager = 0;
            $companyProfile = CompanyProfile::where('id', $id)->first();
            if ($companyProfile->user_type == "M") {
                $manager = CompanyProfile::where('manager_id', $id)->count();
            }
            $leadMaster = LeadMaster::where('assign_id', $id)->orWhere('agent_sales_person_id', $companyProfile->user_id)->count();
            $followUp = FollowUp::where('follow_up_by', $id)->count();
            $salesorder = SalesMaster::where('agent_sales_person_id', $companyProfile->user_id)->count();
            $salesQuatation = SalesQuatation::where('agent_sales_person_id', $companyProfile->user_id)->count();
            if ($leadMaster <= 0 && $salesorder <= 0 && $salesQuatation <= 0 && $followUp <= 0 && $manager <= 0) {
                User::where('id', $companyProfile->user_id)->delete();
                CompanyProfile::where('id', $id)->delete();
                $response = ['status' => true, 'message' => ' Deleted successfully.'];
            } else {
                $response = ['status' => false, 'message' => 'This User assigned in other process.'];
            }
            return response()->json($response);
        } catch (\Exception $e) {
            $response = ['status' => false, 'server_error' => 'Something went wrong. Please try again.'];
            return response()->json($response);
        }
    }
}
