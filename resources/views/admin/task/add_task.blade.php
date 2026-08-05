@extends('layouts.app')
@section('title', 'Task')
@section('content')
<div class="row">
    <div class="col-12">
        @if((isset($task) && isset($task->id)))
        <h4 class="card-title mb-1">{{ __('message.Edit Task') }}</h4>
        @else
        <h4 class="card-title mb-1">{{ __('message.Add Task') }}</h4>
        @endif
    </div>
    <div class="col-12">
        <div class="card p-1">
            <form id="form" action="javascript:void(0);" method="POST">
                @csrf
                <div class="row">
                    @if((isset($task) && isset($task->id)))
                    <input type="hidden" name="task_id" value="{{ $task->id }}">
                    @endif

                    @if(Auth::user()->roles[0]->name != 'Sales')
                    <div class="col-12 col-md-6 @if(Auth::user()->roles[0]->name == 'Sales') col-lg-6 @else col-lg-4 @endif mb-1 custom-input-group">
                        <label class="form-label" for="assign_id">{{ __('message.Assigned') }} <span class="text-danger">*</span></label>
                        <select class="form-control anlayst" name="assign_id" id="assign_id">
                            <option selected disabled>{{ __('message.-- Select --') }}</option>
                            @foreach($companyProfile as $value)
                            <option value="{{$value->id}}" {{ (isset($task) && ($task->assign_id == $value->id) ? 'selected' : '')}}>
                                {{$value->user->name}} {{$value->user->last_name}}
                            </option>
                            @endforeach
                        </select>
                        <span class="invalid-feedback d-block" id="error_assign_id" role="alert"></span>
                    </div>
                    @endif

                    <div class="col-12 col-md-6 @if(Auth::user()->roles[0]->name == 'Sales') col-lg-6 @else col-lg-4 @endif mb-1 custom-input-group">
                        <label class="form-label" for="product_id">{{ __('message.Product') }}</label>
                        @can('product-add')
                        <span class="badge bg-primary view float-end"><i class="fa fa-plus"></i></span>
                        @endcan
                        <select class="form-control" name="product_id" id="product_id">
                            <option selected disabled>{{ __('message.-- Select --') }}</option>
                            @foreach($product as $value)
                            <option value="{{$value->id}}" {{ (isset($task) && $task->product_id == $value->id ) ? 'selected' : '' }}>
                                {{ $value->product_name}}
                            </option>
                            @endforeach
                        </select>
                        <span class="invalid-feedback d-block" id="error_product_id" role="alert"></span>
                    </div>

                    <div class="col-12 col-md-6 @if(Auth::user()->roles[0]->name == 'Sales') col-lg-6 @else col-lg-4 @endif mb-1 custom-input-group">
                        <label class="form-label" for="task_name">{{ __('message.Task') }}<span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="task_name" id="task_name" placeholder="{{ __('message.Task Name') }}" value="{{ ((isset($task) && isset($task->task_name)) ? $task->task_name : old('task_name'))  }}">
                        <span class="invalid-feedback d-block" id="error_task_name" role="alert"></span>
                    </div>

                    <div class="col-12 col-md-12 col-lg-12 mb-1 custom-input-group">
                        <label class="form-label" for="description">{{ __('message.Description') }}</label>
                        <textarea id="editor1" class="form-control" name="description">{!! ((isset($task) && isset($task->description)) ? $task->description : '')  !!}</textarea>
                        <span class="invalid-feedback d-block" id="error_description" role="alert"></span>
                    </div>

                    <div class="col-12 col-md-6 col-lg-6 mb-1 custom-input-group">
                        <label class="form-label" for="task_date">{{ __('message.Task Date') }} <span class="text-danger">*</span></label>
                        <input type="text" class="form-control date" name="task_date" id="task_date" autocomplete="off" placeholder="{{ __('message.Task Date') }}" value="{{ ((isset($task) && isset($task->task_date)) ? date('d-m-Y', strtotime($task->task_date)) : date('d-m-Y'))  }}" readonly>
                        <span class="invalid-feedback d-block" id="error_task_date" role="alert"></span>
                    </div>
                    <div class="col-12 col-md-6 col-lg-6 mb-1 custom-input-group">
                        <label class="form-label" for="expiry_date">{{ __('message.Expiry Date') }}</label>
                        <input type="text" class="form-control date" name="expiry_date" id="expiry_date" autocomplete="off" placeholder="{{ __('message.Expiry Date') }}" value="{{ ((isset($task) && isset($task->expiry_date)) ? date('d-m-Y', strtotime($task->expiry_date)) : '')  }}" readonly>
                        <span class="invalid-feedback d-block" id="error_current_date" role="alert"></span>
                    </div>
                    <div class="col-6 col-md-3 col-lg-2 mb-1 custom-input-group">
                        <label class="form-label" for="hours">{{ __('message.Hours') }}</label>
                        <input type="number" class="form-control" name="hours" id="hours" placeholder="{{ __('message.Hours') }}" value="{{ ((isset($task) && isset($task->hours)) ? $task->hours : old('hours'))  }}">
                        <span class="invalid-feedback d-block" id="error_hours" role="alert"></span>
                    </div>
                    <div class="col-6 col-md-3 col-lg-2 mb-1 custom-input-group">
                        <label class="form-label" for="minutes">{{ __('message.Minutes') }}</label>
                        <input type="number" class="form-control" name="minutes" id="minutes" placeholder="{{ __('message.Minutes') }}" value="{{ ((isset($task) && isset($task->minutes)) ? $task->minutes : old('minutes'))  }}">
                        <span class="invalid-feedback d-block" id="error_minutes" role="alert"></span>
                    </div>
                    <div class="col-12 col-md-6 col-lg-4 mb-1 custom-input-group">
                        <label class="form-label" for="priority">{{ __('message.Priority') }}</label>
                        <select class="form-control" name="priority" id="priority">
                            <option selected disabled>{{ __('message.-- Select --') }}</option>
                            <option value="1" {{ (isset($task) && $task->priority == '1' ) ? 'selected' : '' }}>High
                            </option>
                            <option value="2" {{ (isset($task) && $task->priority == '2') ? 'selected' : ''}}>Medium
                            </option>
                            <option value="3" {{ (isset($task) && $task->priority == '3') ? 'selected' : ''}}>Low
                            </option>
                        </select>
                        <span class="invalid-feedback d-block" id="error_product_id" role="alert"></span>
                    </div>
                    <div class="col-12 col-md-6 col-lg-4 mb-1 custom-input-group">
                        <label class="form-label" for="status">{{ __('message.Status') }}<span class="text-danger">*</span></label>
                        <select class="form-control" name="status" id="status">
                            <option value="1" {{ (isset($task) && $task->status == '1' ) ? 'selected' : '' }}>
                                Pending</option>
                            <option value="2" {{ (isset($task) && $task->status == '2') ? 'selected' : ''}}>In
                                Progress</option>
                            <option value="3" {{ (isset($task) && $task->status == '3') ? 'selected' : ''}}>
                                Completed</option>
                            <option value="4" {{ (isset($task) && $task->status == '4') ? 'selected' : ''}}>
                                Cancelled</option>
                        </select>
                        <span class="invalid-feedback d-block" id="error_product_id" role="alert"></span>
                    </div>
                    <div class="col-md-12 col-12">
                        <button type="submit" class="btn btn-sm btn-primary float-end save">{{ __('message.Submit') }}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header bg-transparent border-bottom">
                <h4 class="text-center mb-0" id="exampleModalTitle">{{ __('message.Add Product') }}</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-1" id="body">
                <form id="add_product" action="javascript:void(0);" method="POST">
                    @csrf
                    <div class="row">
                        <div class="col-12 col-md-12 mb-1 custom-input-group">
                            <!-- <label class="form-label" for="product_name">{{ __('message.Product') }}<span class="text-danger">*</span></label> -->
                            <input type="text" class="form-control" name="product_name" id="product_name" placeholder="{{ __('message.Product') }} *">
                            <span class="invalid-feedback d-block" id="error_product_name" role="alert"></span>
                        </div>

                        <div class="col-md-12 col-12">
                            <button type="submit" class="btn btn-sm btn-primary float-end add-product">{{ __('message.Submit') }}</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('pagescript')
<script type="text/javascript">
    $(document).ready(function() {

        var date = new Date();
        var minDate = new Date(date.getFullYear(), date.getMonth(), date.getDate() - 2);
        $("#task_date").datepicker({
            dateFormat: 'dd-mm-yy',
            minDate: minDate,
        });

        var minDateExp = new Date(date.getFullYear(), date.getMonth(), date.getDate());
        $("#expiry_date").datepicker({
            dateFormat: 'dd-mm-yy',
            minDate: minDateExp,
        });

        $("#task_date").change(function() {
            startDate = $(this).datepicker('getDate');
            $("#expiry_date").datepicker('option', 'minDate', startDate);
        });
        $("#expiry_date").change(function() {
            endDate = $(this).datepicker('getDate');
            $("#task_date").datepicker('option', 'maxDate', endDate);
        });
    });

    $(document).on('click', '.save', function() {
        var formData = new FormData($("#form")[0]);
        if ($("#assign_id") != "" && $("#task_name").val() != "" && $("#task_date").val() != "") {
            $.ajax({
                type: "POST",
                url: "{{route('task.store')}}",
                data: formData,
                dataType: 'json',
                cache: false,
                contentType: false,
                processData: false,
                beforeSend: function() {
                    $("#error_task_name").html(' ');
                    $(".save").html(
                        `<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> {{ __('message.Wait') }}`
                    );
                    $(".save").attr('disabled', true);
                },
                success: function(response) {
                    $(".save").html("{{ __('message.Submit') }}");
                    $(".save").attr('disabled', false);
                    if (response.server_error && response.status == false) {
                        toastr.error("{{ __('message.Something went wrong. Please try again.') }}",
                            "{{ __('message.Error') }}");
                    } else if (response.status == false) {
                        $.each(response.errors, function(key, value) {
                            $('#error_' + key).html('<p class="text-danger mb-0">' + value +
                                '</p>');
                        });
                        toastr.warning("{{ __('message.Please input proper data.') }}",
                            "{{ __('message.Warning') }}");
                    } else {
                        $('#form')[0].reset();
                        toastr.success("{{ __('message.Saved successfully.') }}",
                            "{{ __('message.Success') }}");
                        setTimeout(function() {
                            location.href = response.data;
                        }, 2000);
                    }
                }
            });
        } else {
            $("#form").validate({
                rules: {
                    assign_id: {
                        required: true,
                    },
                    task_name: {
                        required: true,
                    },
                    task_date: {
                        required: true,
                        date: false
                    },
                    status: {
                        required: true,
                    },
                },
                messages: {
                    assign_id: {
                        required: "{{ __('message.Enter assign user') }}"
                    },
                    task_name: {
                        required: "{{ __('message.Enter task name') }}"
                    },
                    task_date: {
                        required: "{{ __('message.Select task date') }}"
                    },
                    status: {
                        required: "{{ __('message.Select status') }}"
                    },

                },
                errorElement: "p",
                errorClass: "text-danger mb-0",

                highlight: function(element) {
                    $(element).addClass('has-error');
                },
                unhighlight: function(element) {
                    $(element).removeClass('has-error');
                },
                errorPlacement: function(error, element) {
                    $(element).closest('.custom-input-group').append(error);
                }
            });
        }
    });

    $(document).on('click', '.view', function(e) {
        e.preventDefault();
        $("#exampleModal").modal("show");
    });

    $("#exampleModal").on("hidden.bs.modal", function(e) {
        $(this).find('#add_product').trigger('reset');
        $("#product_name-error").html("");

    });

    $(document).on('click', '.add-product', function(e) {
        var formData = new FormData($("#add_product")[0]);
        if ($("#product_name").val() != "") {
            $.ajax({
                type: "POST",
                url: "{{route('add-product')}}",
                data: formData,
                dataType: 'json',
                cache: false,
                contentType: false,
                processData: false,
                beforeSend: function() {
                    $(".add-product").html(`<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> {{ __('message.Wait') }}`);
                    $(".add-product").attr('disabled', true);
                },
                success: function(response) {
                    $(".add-product").html("{{ __('message.Submit') }}");
                    $(".add-product").attr('disabled', false);
                    if (response.server_error && response.status == false) {
                        toastr.error("{{ __('message.Something went wrong. Please try again.') }}", "{{ __('message.Error') }}");
                    } else if (response.status == false) {
                        $.each(response.errors, function(key, value) {
                            $('#error_' + key).html('<p class="text-danger mb-0">' + value + '</p>');
                        });
                        toastr.warning("{{ __('message.Please input proper data.') }}", "{{ __('message.Warning') }}");
                    } else {
                        // $('#add_product')[0].reset();
                        $("#add_product").trigger("reset");
                        toastr.success("{{ __('message.Saved successfully.') }}", "{{ __('message.Success') }}");
                        setTimeout(function() {
                            $('#exampleModal').modal('hide');
                            $('#product_id').append('<option value="' + response.product.id + '">' + response.product.product_name + '</option>')
                        }, 1500);
                    }
                }
            });
        } else {
            $("#add_product").validate({
                rules: {
                    product_name: {
                        required: true,
                    },
                },
                messages: {
                    product_name: {
                        required: "{{ __('message.Enter product') }}"
                    },
                },
                errorElement: "p",
                errorClass: "text-danger mb-0",

                highlight: function(element) {
                    $(element).addClass('has-error');
                },
                unhighlight: function(element) {
                    $(element).removeClass('has-error');
                },
                errorPlacement: function(error, element) {
                    $(element).closest('.custom-input-group').append(error);
                }
            });
        }
    });
</script>
@endsection