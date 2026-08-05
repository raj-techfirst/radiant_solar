<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CompanyProfile;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TaskApiController extends Controller
{
    public function index(Request $request)
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
            $where .= ' AND tasks.company_profile_id = ' . $company->parent_id . ' AND tasks.manager_id = ' . $company->id;
        } else {
            $where .= ' AND tasks.assign_id =' . $company->id . ' AND tasks.company_profile_id =' . $company->parent_id;
        }

        if (!empty($request->s_date) && !empty($request->e_date)) {
            $s_date = date('Y-m-d', strtotime($request->s_date)) . ' 00:00:00';
            $e_date = date('Y-m-d', strtotime($request->e_date)) . ' 23:59:59';
            $where .= ' AND (tasks.created_at >= "' . $s_date . '" AND tasks.created_at <= "' . $e_date . '")';
        }

        $task = Task::with('company', 'product')->whereRaw($where)->orderBy('id', 'DESC')->get();
        // $temp = 0;
        foreach ($task as $value) {
            // list($hours, $minutes) = explode(':', $value->hours . ':' . $value->minutes);
            // $temp += $hours * 60 + $minutes;

            $value['assign_user'] = $value->company->user->name . ' ' . $value->company->user->last_name;
            $value['product_name'] = ($value->product_id != '' && $value->product_id != 0) ? $value->product->product_name : 0;

            if ($value->priority == '1') {
                $value['priority'] = 'High';
            } elseif ($value->priority == '2') {
                $value['priority'] = 'Medium';
            } else {
                $value['priority'] = 'Low';
            }

            if ($value->status == '1') {
                $value['status_title'] = 'Pending';
            } elseif ($value->status == '2') {
                $value['status_title'] = 'In Progress';
            } elseif ($value->status == '3') {
                $value['status_title'] = 'Completed';
            } else {
                $value['status_title'] = 'Cancelled';
            }

            $value['timespand'] = str_pad($value->hours, 2, '0', STR_PAD_LEFT) . "H:" . str_pad($value->minutes, 2, '0', STR_PAD_LEFT) . "M";;
            $value['task_date'] = (!is_null($value->task_date)) ? date('d-m-Y', strtotime($value->task_date)) : '';
            $value['expiry_date'] = (!is_null($value->expiry_date)) ? date('d-m-Y', strtotime($value->expiry_date)) : '';

            $value['task_date_date'] = date('d', strtotime($value->task_date));
            $value['task_date_month'] = date('M', strtotime($value->task_date));
            $value['task_date_year'] = date('Y', strtotime($value->task_date));

            $value['expiry_date_date'] = date('d', strtotime($value->expiry_date));
            $value['expiry_date_month'] = date('M', strtotime($value->expiry_date));
            $value['expiry_date_year'] = date('Y', strtotime($value->expiry_date));

            unset($value->time,$value->task_date, $value->expiry_date, $value->user_id, $value->company_profile_id, $value->product, $value->company, $value->created_at, $value->updated_at, $value->deleted_at);
        }

        // $totalHours = floor($temp / 60);
        // $totalMinutes = $temp % 60;
        // $totalSum = str_pad($totalHours, 2, '0', STR_PAD_LEFT) . "H:" . str_pad($totalMinutes, 2, '0', STR_PAD_LEFT) . "M";
        // $task[] = ['total_time' => $totalSum];

        $response = ['status' => true, 'message' => 'Task List', 'task' => $task];
        return response($response, 200);
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
            return response($response, 200);
        }
        DB::beginTransaction();
        try {
            if (!is_null($request->task_id)) {
                $task = Task::where('id', $request->task_id)->first();
                $response = ['status' => true, 'message' => 'Task updated successfully.'];
            } else {
                $task = new Task();
                $response = ['status' => true, 'message' => 'Task added successfully.'];
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
                return response($response, 200);
            } else {
                $response = ['status' => false, 'message' => 'Something went wrong. Please try again.'];
                return response($response, 200);
            }
        } catch (\Exception $e) {
            DB::rollback();
            $response = ['status' => false, 'message' => 'Something went wrong. Please try again.'];
            return response($response, 500);
        }
    }

    public function destroy(Request $request)
    {
        try {
            $task = Task::where('id', $request->id)->first();
            if (!is_null($task)) {
                $task->delete();
                $response = ['status' => true, 'message' => 'Task deleted successfully.'];
            } else {
                $response = ['status' => false, 'message' => 'Task not found.'];
            }
            return response($response, 200);
        } catch (\Exception $e) {
            $response = ['status' => false, 'message' => 'Something went wrong. Please try again.'];
            return response($response, 500);
        }
    }
}
