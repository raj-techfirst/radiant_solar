<?php

namespace App\Http\Controllers\Admin;

use App\Exports\TaskExport;
use App\Http\Controllers\Controller;

use App\Models\CompanyProfile;
use App\Models\Notification;
use App\Models\Product;
use App\Models\Task;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class TaskController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:task-list|task-create|task-edit|task-delete', ['only' => ['index', 'store']]);
        $this->middleware('permission:task-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:task-edit', ['only' => ['edit', 'store']]);
        $this->middleware('permission:task-delete', ['only' => ['destroy']]);
    }

    public function index(Request $request)
    {
        $user = $request->user;
        $status = $request->status;
        $product = $request->product;

        $where = "1 = 1";

        if ($user != 'All') {
            $where .= ' AND tasks.assign_id = "' . $user . '"';
        } 

        if ($status != 'All') {
            $where .= ' AND tasks.status = "' . $status . '"';
        } 

        if ($product != 'All') {
            $where .= ' AND tasks.product_id = "' . $product . '"';
        }

        $company_where = "1 = 1";
        $company = CompanyProfile::where('user_id', Auth::id())->first();
        if ($company->user_type == 'O') {
            $id = $company->id;
            $company_where .= ' AND company_profiles.id = ' . $company->id . ' OR company_profiles.parent_id = ' . $company->id;
            $where .= ' AND tasks.company_profile_id =' . $company->id;
        } else if ($company->user_type == 'M') {
            $id = $company->parent_id;
            $company_where .= ' AND company_profiles.parent_id = ' . $company->parent_id . ' AND company_profiles.id = ' . $company->parent_id . ' OR (company_profiles.manager_id = ' . $company->id . ' OR company_profiles.user_id = ' . $company->user_id . ')';
            $where .= ' AND tasks.company_profile_id = ' . $company->parent_id . ' AND tasks.manager_id = ' . $company->id;
        } else {
            $id = $company->parent_id;
            $company_where .= ' AND company_profiles.id = ' . $company->id . ' AND company_profiles.parent_id =' . $company->parent_id;
            $where .= ' AND tasks.assign_id =' . $company->id . ' AND tasks.company_profile_id =' . $company->parent_id;
        }

        if (!empty($request->s_date) && !empty($request->e_date)) {
            $s_date = date('Y-m-d', strtotime($request->s_date)) . ' 00:00:00';
            $e_date = date('Y-m-d', strtotime($request->e_date)) . ' 23:59:59';
            $where .= ' AND (tasks.created_at >= "' . $s_date . '" AND tasks.created_at <= "' . $e_date . '")';
        }

        $companyProfile = CompanyProfile::with('user')->whereRaw($company_where)->get();
        $product = Product::where('company_profile_id', $id)->get();

        if (request()->ajax()) {
            return DataTables::of(Task::with('company', 'product')->whereRaw($where)->orderBy('id', 'DESC'))
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $html = '<div class="d-flex">';
                    $html .= '<a data-id="' . $row->id . '" href="javascript:void(0)" class="view avatar bg-light-success p-50 m-0" data-bs-toggle="tooltip" data-placement="left" title="View"><i class="fa fa-eye"></i></a>';
                    if (Gate::allows('task-edit')) {
                        $html .= ' <a href="' . route('task.edit', $row->id) . '" class="avatar bg-light-info p-50 m-0" data-bs-toggle="tooltip" data-placement="left" title="Edit"><i class="fa fa-edit"></i></a>';
                    }
                    if (Gate::allows('task-delete')) {
                        $html .= ' <a data-id="' . $row->id . '" href="javascript:void(0);" class="delete avatar bg-light-danger p-50 m-0" data-bs-toggle="tooltip" data-placement="left" title="Delete"><i class="fa fa-trash"></i></a>';
                    }
                    $html .= '</div>';
                    return $html;
                })
                ->editColumn('product_id', function ($row) {
                    if (isset($row->product) && $row->product->product_name != "") {
                        return $row->product->product_name;
                    } else {
                        return "";
                    }
                })
                ->editColumn('priority', function ($row) {
                    if ($row->priority == '1') {
                        return '<span class="badge bg-light-danger w-100">High</span>';
                    } elseif ($row->priority == '2') {
                        return '<span class="badge bg-light-primary w-100">Medium</span>';
                    } else {
                        return '<span class="badge bg-light-warning w-100">Low</span>';
                    }
                })
                ->editColumn('status', function ($row) {
                    if ($row->status == '1') {
                        return '<span class="badge bg-light-warning w-100">Pending</span>';
                    } elseif ($row->status == '2') {
                        return '<span class="badge bg-light-primary w-100">In Progress</span>';
                    } elseif ($row->status == '3') {
                        return '<span class="badge bg-light-success w-100">Completed</span>';
                    } else {
                        return '<span class="badge bg-light-danger w-100">Cancelled</span>';
                    }
                })
                ->editColumn('task_date', function ($row) {
                    if (!is_null($row->task_date)) {
                        return date('d-m-Y', strtotime($row->task_date));
                    } else {
                        return '';
                    }
                })
                ->editColumn('timespand', function ($row) {
                    if (!is_null($row->hours)) {
                        return str_pad($row->hours, 2, '0', STR_PAD_LEFT) . "H:" . str_pad($row->minutes, 2, '0', STR_PAD_LEFT) . "M";
                    } else {
                        return '';
                    }
                })
                ->rawColumns(['action', 'priority', 'status', 'task_date', 'product_id', 'timespand'])
                ->make(true);
        } else {
            return view('admin.task.view_task', compact('product', 'companyProfile'));
        }
    }

    public function export(Request $request)
    {
        $user = $request->user;
        $status = $request->status;
        $product = $request->product;

        $where = "1 = 1";
        if ($user == 'All') {
            if ($status != 'All') {
                $where .= ' AND (tasks.status = "' . $status . '")';
            }
            if ($product != 'All') {
                $where .= ' AND (tasks.product_id = "' . $product . '")';
            }
        } else {
            if ($status != 'All') {
                $where .= ' AND (tasks.status = ' . $status . ' AND tasks.assign_id = "' . $user . '")';
            } else {
                $where .= ' AND (tasks.assign_id = "' . $user . '")';
            }

            if ($product != 'All') {
                $where .= ' AND (tasks.product_id = ' . $product . ' AND tasks.assign_id = "' . $user . '")';
            } else {
                $where .= ' AND (tasks.assign_id = "' . $user . '")';
            }
        }

        $company = CompanyProfile::where('user_id', Auth::id())->first();
        if ($company->user_type == 'O') {
            $where .= ' AND tasks.company_profile_id =' . $company->id;
        } else if ($company->user_type == 'M') {
            // $where .= ' AND tasks.assign_id !=' . $company->parent_id . ' AND tasks.company_profile_id =' . $company->parent_id;
            $where .= ' AND tasks.company_profile_id = ' . $company->parent_id . ' AND tasks.manager_id = ' . $company->id;
        } else {
            $where .= ' AND tasks.assign_id =' . $company->id . ' AND tasks.company_profile_id =' . $company->parent_id;
        }

        if (!empty($request->s_date) && !empty($request->e_date)) {
            $s_date = date('Y-m-d', strtotime($request->s_date)) . ' 00:00:00';
            $e_date = date('Y-m-d', strtotime($request->e_date)) . ' 23:59:59';
            $where .= ' AND (tasks.created_at >= "' . $s_date . '" AND tasks.created_at <= "' . $e_date . '")';
        }
        ob_end_clean();
        ob_start();
        $task = Task::with('product', 'company')->select('assign_id', 'product_id', 'task_name', 'timespand', 'hours', 'minutes', 'description', DB::raw("DATE_FORMAT(tasks.task_date,'%d-%m-%Y') AS task_date"), DB::raw("DATE_FORMAT(tasks.expiry_date,'%d-%m-%Y') AS expiry_date"), 'priority', 'status',)
            ->whereRaw($where)
            ->get();
        if (count($task) > 0) {
            return Excel::download(new TaskExport($where), 'Task_Report.xlsx');
        } else {
            return redirect()->back();
        }
    }

    public function create()
    {
        $companyFind = CompanyProfile::where('user_id', Auth::id())->first();
        $where = "1 = 1";
        if ($companyFind->user_type == 'O') {
            $id = $companyFind->id;
            $where .= ' AND company_profiles.user_id =' . Auth::id() . ' OR company_profiles.parent_id =' . $companyFind->id;
        } else {
            $id = $companyFind->parent_id;
            $where .= ' AND company_profiles.parent_id = ' . $id . ' AND company_profiles.id = ' . $id . ' OR (company_profiles.manager_id = ' . $companyFind->id . ' OR company_profiles.user_id = ' . $companyFind->user_id . ')';
            // $where .= ' AND company_profiles.id =' . $companyFind->parent_id . ' OR company_profiles.parent_id =' . $companyFind->parent_id;
        }
        $product = Product::where('company_profile_id', $id)->get();
        $companyProfile = CompanyProfile::with('user')->whereRaw($where)->get();
        return view('admin.task.add_task', compact('product', 'companyProfile'));
    }

    public function store(Request $request)
    {
        $companyFind = CompanyProfile::where('user_id', Auth::id())->first();
        if ($companyFind->user_type == 'S') {
            $temp = 'nullable';
        } else {
            $temp = 'required';
        }
        $validator = Validator::make($request->all(), [
            'assign_id' => $temp,
            'task_name' => 'required',
            'task_date' => 'required',
        ], [
            'assign_id.required' => 'Enter assign user',
            'task_name.required' => 'Enter task name',
            'task_date.required' => 'Enter task date',
        ]);
        if ($validator->fails()) {
            $response = ['status' => false, 'message' => 'Please input proper data.', 'errors' => $validator->errors()];
            return response()->json($response);
        }
        DB::beginTransaction();
        try {
            if (!is_null($request->task_id)) {
                $task = Task::where('id', $request->task_id)->first();
                if ($request->status == '2') {
                    $task->timespand =  date("Y-m-d H:i:s");
                } elseif ($request->status  == '3') {
                    //$task->time = strtotime($task->timespand) - strtotime(date('Y-m-d H:i:s'));
                    $datetime1 = new DateTime($task->timespand);
                    $datetime2 = new DateTime(date('Y-m-d H:i:s'));
                    $interval = $datetime1->diff($datetime2);
                    $task->time = $interval->format('%h') . ":" . $interval->format('%i');
                }

                $response = ['data' => route('task.index'), 'status' => true, 'message' => ' Task updated successfully.'];
            } else {
                $task = new Task();
                $response = ['data' => route('task.index'), 'status' => true, 'message' => ' Task added successfully.'];
            }

            if (!is_null($request->assign_id)) {
                $manager = CompanyProfile::where('id', $request->assign_id)->first();
            } else {
                $manager = $companyFind;
            }
            if ($manager->user_type == 'O') {
                $manager_id = 0;
            } elseif ($manager->user_type == 'M') {
                $manager_id = $manager->id;
            } else {
                $manager_id = $manager->manager_id;
            }

            if ($companyFind->user_type == 'O') {
                $task->company_profile_id = $companyFind->id;
                $task->assign_id = $request->assign_id;
            } elseif ($companyFind->user_type == 'M') {
                $task->company_profile_id = $companyFind->parent_id;
                $task->assign_id = $request->assign_id;
            } else {
                $task->company_profile_id = $companyFind->parent_id;
                $task->assign_id = $companyFind->id;
            }
            $task->user_id = Auth::id();
            $task->manager_id = $manager_id;
            $task->product_id = $request->product_id;
            $task->task_name = $request->task_name;
            $task->description = $request->description;

            $task->hours = (!is_null($request->hours)) ? $request->hours : 0;
            $task->minutes = (!is_null($request->minutes)) ? $request->minutes : 0;


            $task->task_date = date("Y-m-d", strtotime($request->task_date));
            if (!is_null($request->expiry_date)) {
                $task->expiry_date = date("Y-m-d", strtotime($request->expiry_date));
            } else {
                $task->expiry_date = null;
            }
            $task->priority = (!is_null($request->priority)) ? $request->priority : "3";
            $task->status = $request->status;
            $result = $task->save();
            DB::commit();
            if (!is_null($result)) {
                if (!$request->task_id) {
                    if ($companyFind->user_type == 'O' && $task->assign_id == $manager_id) {
                        $notification = new Notification();
                        $notification->user_id = $task->assign_id;
                        $notification->title = "New Task";
                        $notification->description = $task->task_name . " Task has been Assigned to you.";
                        $notification->is_read = '0';
                        $notification->count = '1';
                        $notification->save();
                    } elseif ($companyFind->user_type == 'O' && $task->assign_id != $manager_id && $task->assign_id != $task->company_profile_id) {
                        for ($i = 1; $i <= 2; $i++) {
                            $notification = new Notification();
                            $notification->title = "New Task";
                            if ($i == 1) {
                                $notification->user_id = $manager_id;
                            } else {
                                $notification->user_id = $task->assign_id;
                            }
                            $notification->description = $task->task_name . " Task has been Assigned to you.";
                            $notification->is_read = '0';
                            $notification->count = '1';
                            $notification->save();
                        }
                    } elseif ($companyFind->user_type == 'M' && $task->assign_id != $manager_id) {
                        $notification = new Notification();
                        $notification->user_id = $task->assign_id;
                        $notification->title = "New Task";
                        $notification->description = $task->task_name . " Task has been Assigned to you.";
                        $notification->is_read = '0';
                        $notification->count = '1';
                        $notification->save();
                    } else {
                    }
                }
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

    public function show($id)
    {
        $task = Task::with('company')->where('id', $id)->first();
        if (!is_null($task)) {
            $data['html'] = view('admin.task.model', compact('task'))->render();
            return response()->json($data);
        } else {
            return abort(404);
        }
    }


    public function edit($id)
    {
        $task = Task::where('id', $id)->first();
        if (!is_null($task)) {
            $companyFind = CompanyProfile::where('user_id', Auth::id())->first();
            $where = "1 = 1";
            if ($companyFind->user_type == 'O') {
                $id = $companyFind->id;
                $where .= ' AND company_profiles.user_id =' . Auth::id() . ' OR company_profiles.parent_id =' . $companyFind->id;
            } else {
                $id = $companyFind->parent_id;
                $where .= ' AND company_profiles.parent_id = ' . $id . ' AND company_profiles.id = ' . $id . ' OR (company_profiles.manager_id = ' . $companyFind->id . ' OR company_profiles.user_id = ' . $companyFind->user_id . ')';
            }
            $product = Product::where('company_profile_id', $id)->get();
            $companyProfile = CompanyProfile::with('user')->whereRaw($where)->get();
            return view('admin.task.add_task', compact('task', 'product', 'companyProfile'));
        } else {
            return abort(404);
        }
    }

    public function update(Request $request, Task $task)
    {
        //
    }

    public function destroy($id)
    {
        try {
            $task = task::where('id', $id)->delete();
            $response = ['status' => true, 'message' => 'Deleted successfully.'];
            return response()->json($response);
        } catch (\Exception $e) {
            $response = ['status' => false, 'server_error' => 'Something went wrong. Please try again.'];
            return response()->json($response);
        }
    }
}
