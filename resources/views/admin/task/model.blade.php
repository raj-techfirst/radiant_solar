<!--Start Model Card Open-->
<div class="row">
    <div class="col-12">
        <div class="card mb-0">
            <div class="table-responsive">
                <table class="table">
                    <thead class="table-dark">
                        <tr>
                            <th>{{ __('message.Assign User') }}</th>
                            <th>{{ __('message.Task Date') }}</th>
                            <th>{{ __('message.Expiry Date') }}</th>
                            <th>{{ __('message.Timespand') }}</th>
                            <th>{{ __('message.Description') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>@if(isset($task->company) && $task->company->user->name != '')
                                 {{$task->company->user->name}} {{$task->company->user->last_name}}
                                @endif
                            </td>
                            <td>{{date('d-m-Y', strtotime($task->task_date))}}</td>
                            <td>{{date('d-m-Y', strtotime($task->expiry_date))}}</td>
                            <td>{{str_pad($task->hours, 2, '0', STR_PAD_LEFT)}}H:{{str_pad($task->minutes, 2, '0', STR_PAD_LEFT)}}M</td>
                            
                             <td>{{$task->description}}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<!--End Model Card Open-->