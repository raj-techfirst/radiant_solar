<?php

namespace App\Exports;

use App\Models\Task;
use DateTime;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class TaskExport implements FromCollection, WithHeadings
{
    private $where;

    public function __construct($where)
    {
        $this->where = $where;
    }

    public function collection()
    {
        $task = Task::with('product', 'company')->select('assign_id', 'product_id', 'task_name', 'timespand', 'hours', 'minutes', 'description', DB::raw("DATE_FORMAT(tasks.task_date,'%d-%m-%Y') AS task_date"), DB::raw("DATE_FORMAT(tasks.expiry_date,'%d-%m-%Y') AS expiry_date"), 'priority', 'status',)
            ->whereRaw($this->where)
            ->get();
        $temp = 0;
        foreach ($task as $value) {
            list($hours, $minutes) = explode(':', $value->hours . ':' . $value->minutes);
            $temp += $hours * 60 + $minutes;

            $value['assign_id'] = $value->company->user->name . ' ' . $value->company->user->last_name;
            if (isset($value->product) && $value->product->product_name != "") {
                $value['product_id'] = $value->product->product_name;
            } else {
                $value['product_id'] = "";
            }
            $value['timespand'] = $value->hours . 'H:' . $value->minutes . 'M';

            if ($value->priority == '1') {
                $value['priority'] = 'High';
            } elseif ($value->priority == '2') {
                $value['priority'] = 'Medium';
            } else {
                $value['priority'] = 'Low';
            }

            if ($value->status == '1') {
                $value['status'] = 'Pending';
            } elseif ($value->status == '2') {
                $value['status'] = 'In Progress';
            } elseif ($value->status == '3') {
                $value['status'] = 'Completed';
            } else {
                $value['status'] = 'Cancelled';
            }
            unset($value->hours, $value->minutes, $value->product, $value->company);
        }
        
        $totalHours = floor($temp / 60);
        $totalMinutes = $temp % 60;
        $totalSum = str_pad($totalHours, 2, '0', STR_PAD_LEFT) . "H:" . str_pad($totalMinutes, 2, '0', STR_PAD_LEFT) . "M";

        $task[] = ['blank1' => false, 'blank2' => false, 'label' => 'Total Time', 'total' => $totalSum];
        return $task;
    }

    public function headings(): array
    {
        return [
            'Assign User',
            'Product',
            'Task Name',
            'Timespand',
            'Description',
            'Task Date',
            'Expiry Date',
            'Priority',
            'Status'
        ];
    }
}
