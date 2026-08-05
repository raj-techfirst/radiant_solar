@extends('layouts.app')
@section('title', 'Project Stock Adjustment')
@section('content')
<div class="row">
    <div class="col-12">
        <h4 class="content-header-title float-start">Add Project Stock Adjustment</h4>
    </div>
    <div class="col-12">
        <div class="card p-1">
            <form id="form" action="javascript:void(0);" method="POST">
                @csrf
                <div class="row">

                    <div class="col-12 col-md-4 col-lg-4 mb-1 custom-input-group">
                        <label class="form-label w-100"> Type <span class="text-danger">*</span></label>
                        <label class="form-label btn btn-outline-primary"><input type="radio" name="issue_type" value="project"> Project Wise</label>
                        <label class="form-label btn btn-outline-primary"><input type="radio" name="issue_type" value="installer"> Installer Wise</label>
                    </div>

                    <div class="col-12 col-md-6 col-lg-4 mb-1 custom-input-group project-wise d-none">
                        <label class="form-label" for="project_id">Project <span class="text-danger">*</span></label>
                        <select class="form-select select2" name="project_id" id="project_id">
                            <option selected disabled>{{ __('message.-- Select --') }}</option>
                            @foreach($project as $value)
                            <option value="{{ $value->id }}">{{ $value->consumer_name }} | {{ $value->consumer_number }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 col-md-6 col-lg-4 mb-1 custom-input-group installer-wise d-none">
                        <label class="form-label" for="installer_id">Installer <span class="text-danger">*</span></label>
                        <select class="form-select select2" name="installer_id" id="installer_id">
                            <option selected disabled>{{ __('message.-- Select --') }}</option>
                            @foreach($installer as $value)
                            <option value="{{ $value->user->id }}" {{ (isset($data) && ($data->installer_id == $value->user->id) ? 'selected' : '')}}>{{ $value->user->name.' '. $value->user->last_name   }}</option>
                            @endforeach
                        </select>
                    </div>


                </div>
                <div class="table-responsive d-none" id="table-col">
                    <table class="table table-bordered table-stock table-sm">
                        <thead>
                            <tr>
                                <th class="text-center" style="width: 80px;">#</th>
                                <th>Item</th>
                                <th class="text-center">Current Stock</th>
                                <th class="text-center text-nowrap">Real Stock</th>
                                <th class="text-center">Difference</th>
                            </tr>
                        </thead>
                        <tbody id="append_data">
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="5"> <button type="submit" class="btn btn-sm btn-primary float-end save">Submit</button></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('pagescript')
<script type="application/javascript">
    $(document).on('change', '#project_id', function() {
        var id = $(this).val();
        if (id != "") {
            loadStock(id,'project');
        }
    });
    $(document).on('change', '#installer_id', function() {
        var id = $(this).val();
        if (id != "") {
            loadStock(id,'installer');
        }
    });

    function loadStock(id,issue_type) {
        var url = "{{route('get-project-stock')}}";
        $.ajax({
            type: "GET",
            url: url,
            data: {
                "id": id,
                "issue_type": issue_type,
                "type": 'Adjust',
                "_token": "{{ csrf_token() }}",
            },
            dataType: 'json',
            success: function(response) {
                if (response.status_code == 403) {
                    $('#table-col').addClass('d-none');
                    toastr.warning(response.message, "Warning");
                } else {
                    $('#table-col').removeClass('d-none');
                    $("#append_data").html('');
                    $("#append_data").html(response.html);
                    if (feather) {
                        feather.replace({
                            width: 14,
                            height: 14
                        });
                    }
                }
            }
        });
    }

    $(document).on('click', '.save', function() {
        var formData = new FormData($("#form")[0]);
        $.ajax({
            type: "POST",
            url: "{{route('project-stock-adjustments.store')}}",
            data: formData,
            dataType: 'json',
            cache: false,
            contentType: false,
            processData: false,
            beforeSend: function() {
                $(".invalid-feedback").html(' ');
                $(".save").html(`<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Wait`);
                $(".save").attr('disabled', true);
            },
            success: function(response) {
                $(".save").html("Submit");
                $(".save").attr('disabled', false);
                if (response.status_code == 500) {
                    toastr.error(response.message, "Error");
                } else if (response.status_code == 403) {
                    toastr.warning(response.message, "Warning");
                } else if (response.status_code == 201) {
                    $.each(response.errors, function(key, value) {
                        $('#error_' + key).html('<p class="text-danger mb-0">' + value + '</p>');
                    });
                    toastr.warning(response.message, "Warning");
                } else {
                    $('#form')[0].reset();
                    toastr.success(response.message, "Success");
                    setTimeout(function() {
                        location.href = response.data;
                    }, 500);
                }
            }
        });
    });

    $(document).on('blur', '.real_stock', function() {
        calc();
    });

    function calc() {
        $(".difference").each(function() {
            let real_stock = $(this).closest('.clone_row').find('.real_stock').val();
            let current_stock = $(this).closest('.clone_row').find('.current_stock').html();
            var fianlTotal = 0.00;
            let getBaseAmount = (current_stock - real_stock);
            fianlTotal = (getBaseAmount);
            $(this).val(parseFloat(fianlTotal).toFixed(2));
        });
    }
    $("input[name='issue_type']").change(function() {
        var issue_type = $("input[name='issue_type']:checked").val();
        if (issue_type == "project") {
            $(".installer-wise").addClass('d-none');
            $(".project-wise").removeClass('d-none');
        }
        if (issue_type == "installer") {
            $(".installer-wise").removeClass('d-none');
            $(".project-wise").addClass('d-none');
        }
    });
</script>
@endsection