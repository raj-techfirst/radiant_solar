@extends('layouts.app')
@section('title', 'Panel/Inverter')
@section('content')
<div class="row">
    <div class="col-12 mb-1">
        <h4 class="content-header-title float-start">Panel/Inverter List</h4>

        @can('item-group-create')
        <button type="button" data-bs-toggle="modal" data-bs-target="#inlineModal" class="btn btn-sm btn-primary float-end"><i class="fa fa-plus me-25"></i> {{ __('message.Add New') }}</button>
        @endcan
    </div>
    <div class="col-12">
        <div class="card p-1">
            <table id="table" class="datatables-basic table table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ __('message.Action') }}</th>
                        <th>Panel/Inverter </th> <!-- (Company|type|watt) -->
                        <th>Item Code</th>
                        <th>HSN Code</th>
                        <th>GST Rate</th>
                    </tr>
                </thead>
                <tbody>

                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="inlineModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-transparent border-bottom">
                <h4 class="text-center mb-0" id="exampleModalTitle">Add Item Group</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-1" id="body">
                <form id="form" class="form" action="javascript:void(0);" method="POST">
                    @csrf
                    <div class="row">

                        <div class="col-12 col-md-12 col-lg-4 mb-1 custom-input-group">
                            <label class="form-label w-100">Group Type <span class="text-danger">*</span></label>
                            <label class="form-label btn btn-outline-primary"><input type="radio" name="group_type" value="panel" checked> Panel</label>
                            <label class="form-label btn  btn-outline-primary"><input type="radio" name="group_type" value="inverter"> Inverter</label>
                        </div>
                        <div class="col-12 col-md-12 col-lg-4 mb-1 custom-input-group inveter-selected d-none">
                            <label class="form-label" for="inveter_company_id">Inveter Company <span class="text-danger">*</span></label>
                            <select class="form-select select2 custom-select2" name="inveter_company_id" id="inveter_company_id">
                                <option selected disabled>{{ __('message.-- Select --') }}</option>
                                @foreach($inveterCompany as $value)
                                <option value="{{$value->id}}">{{$value->name}}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12 col-md-12 col-lg-4 mb-1 custom-input-group inveter-selected d-none">
                            <label class="form-label" for="inveter_kw">Inveter KW</label>
                            <input type="text" class="form-control" name="inveter_kw" id="inveter_kw" placeholder="Inveter KW">
                            <span class="invalid-feedback d-block" id="error_inveter_kw" role="alert"></span>
                        </div>

                        <div class="col-12 col-md-12 col-lg-4 mb-1 custom-input-group inveter-selected d-none">
                            <label class="form-label" for="inveter_phase">Inveter Phase <span class="text-danger">*</span></label>
                            <select class="form-select select2 custom-select2" name="inveter_phase" id="inveter_phase">
                                <option selected disabled>{{ __('message.-- Select --') }}</option>
                                <option value="Single Phase">Single Phase</option>
                                <option value="Three Phase">Three Phase</option>
                            </select>
                        </div>

                        <div class="col-12 col-md-12 col-lg-4 mb-1 custom-input-group panel-selected">
                            <input type="hidden" name="id" id="id" value="">
                            <label class="form-label" for="panel_company_id">Penal Company <span class="text-danger">*</span></label>
                            <select class="form-select select2 custom-select2" name="panel_company_id" id="panel_company_id">
                                <option selected disabled>{{ __('message.-- Select --') }}</option>
                                @foreach($penalCompany as $value)
                                <option value="{{$value->id}}">{{$value->name}}</option>
                                @endforeach
                            </select>
                        </div>


                        <div class="col-12 col-md-12 col-lg-4 mb-1 custom-input-group panel-selected">
                            <label class="form-label" for="panel_type_id">Penal Type <span class="text-danger">*</span></label>
                            <select class="form-select select2 custom-select2" name="panel_type_id" id="panel_type_id">
                                <option selected disabled>{{ __('message.-- Select --') }}</option>
                                @foreach($penalType as $value)
                                <option value="{{$value->id}}">{{$value->name}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-md-12 col-lg-4 mb-1 custom-input-group panel-selected">
                            <label class="form-label" for="panel_watt_id">Penal Watts <span class="text-danger">*</span></label>
                            <select class="form-select select2 custom-select2" name="panel_watt_id" id="panel_watt_id">
                                <option selected disabled>{{ __('message.-- Select --') }}</option>
                                @foreach($penalWatt as $value)
                                <option value="{{$value->id}}">{{$value->name}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-md-12 col-lg-4 mb-1 custom-input-group panel-selected">
                            <label class="form-label" for="p_type">Penal Type <span class="text-danger">*</span></label>
                            <select class="form-select select2 custom-select2" name="p_type" id="p_type">
                                 <option value="DCR" selected>DCR</option>
                                 <option value="Non DCR">Non DCR</option>
                            </select>
                        </div>

                        <div class="col-12 col-md-12 col-lg-4 mb-1 custom-input-group">
                            <label class="form-label" for="item_code">Item Code</label>
                            <input type="text" class="form-control" name="item_code" id="item_code" placeholder="Item Code">
                            <span class="invalid-feedback d-block" id="error_item_code" role="alert"></span>
                        </div>
                        <div class="col-12 col-md-12 col-lg-4 mb-1 custom-input-group">
                            <label class="form-label" for="hsn_code">HSN Code</label>
                            <input type="text" class="form-control" name="hsn_code" id="hsn_code" placeholder="HSN Code">
                            <span class="invalid-feedback d-block" id="error_hsn_code" role="alert"></span>
                        </div>
                        <div class="col-12 col-md-12 col-lg-4 mb-1 custom-input-group">
                            <label class="form-label" for="gst_rate">GST Rate <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="gst_rate" id="gst_rate" placeholder="GST Rate">
                            <span class="invalid-feedback d-block" id="error_gst_rate" role="alert"></span>
                        </div>
 <div class="col-12 col-md-12 mb-1 custom-input-group">
                            <label class="form-label" for="moq_level">MOQ Level<span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="moq_level" id="moq_level" placeholder="MOQ Level *">
                            <span class="invalid-feedback d-block" id="error_moq_level" role="alert"></span>
                        </div>
                        <div class="col-md-12 col-12">
                            <button type="submit" class="btn btn-sm btn-primary float-end save">{{ __('message.Submit') }}</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('pagescript')
<script type="application/javascript">
    'use strict';
    const URL = "{{route('item-group.index')}}";

    var table = '';
    $(function() {
        table = $('#table').DataTable({
            ajax: URL,
            processing: true,
            serverSide: true,
            fixedHeader: true,
            scrollX: true,
            aLengthMenu: [
                [20, -1],
                [20, "All"],
            ],
            columns: [{
                    data: 'id',
                    render: function(data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    }
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    sortable: false
                },
                {
                    data: 'item_group',
                    name: 'item_group'
                },
                {
                    data: 'item_code',
                    name: 'item_code'
                },
                {
                    data: 'hsn_code',
                    name: 'hsn_code'
                },
                {
                    data: 'gst_rate',
                    name: 'gst_rate'
                },
            ],
            initComplete: function(settings, json) {
                var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl)
                })
            }
        });
    });


    $("#form").validate({
        rules: {
            group_type: {
                required: true,
            },
            inveter_company_id: {
                required: function() {
                    return $("input[name='group_type']:checked").val() === "inverter";
                },
            },
            inveter_kw: {
                required: function() {
                    return $("input[name='group_type']:checked").val() === "inverter";
                },
            },
            inveter_phase: {
                required: function() {
                    return $("input[name='group_type']:checked").val() === "inverter";
                },
            },
            panel_company_id: {
                required: function() {
                    return $("input[name='group_type']:checked").val() === "panel";
                },
            },
            panel_type_id: {
                required: function() {
                    return $("input[name='group_type']:checked").val() === "panel";
                },
            },
            panel_watt_id: {
                required: function() {
                    return $("input[name='group_type']:checked").val() === "panel";
                },
            },
            gst_rate: {
                required: true,
            },
        },
        messages: {
            group_type: {
                required: "Select Group Type",
            },
            inveter_company_id: {
                required: "Select Inverter Company",
            },
            inveter_kw: {
                required: "Enter Inverter KW",
            },
            inveter_phase: {
                required: "Select Inverter Phase",
            },
            panel_company_id: {
                required: "Select panel company",
            },
            panel_type_id: {
                required: "Select panel type",
            },
            panel_watt_id: {
                required: "Select panel watt",
            },
            gst_rate: {
                required: "Enter GST rate",
            },
        },
        errorElement: "p",
        errorClass: "text-danger mb-0 custom-error",

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

    $(document).on('click', '.save', function() {
        if ($("#form").valid()) {
            var formData = new FormData($("#form")[0]);
            $.ajax({
                type: "POST",
                url: "{{route('item-group.store')}}",
                data: formData,
                dataType: 'json',
                cache: false,
                contentType: false,
                processData: false,
                beforeSend: function() {
                    $("#error_name").html(' ');
                    $(".save").html(`<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> {{ __('message.Wait') }}`);
                    $(".save").attr('disabled', true);
                },
                success: function(response) {
                    $(".save").html("{{ __('message.Submit') }}");
                    $(".save").attr('disabled', false);
                    if (response.server_error && response.status == false) {
                        toastr.error(response.server_error, "{{ __('message.Error') }}");
                    } else if (response.status == false) {
                        $.each(response.errors, function(key, value) {
                            $('#error_' + key).html('<p class="text-danger mb-0">' + value + '</p>');
                        });
                        toastr.warning("{{ __('message.Please input proper data.') }}", "{{ __('message.Warning') }}");
                    } else {
                        $('#form')[0].reset();
                        toastr.success(response.message, "{{ __('message.Success') }}");
                        setTimeout(function() {
                            location.href = response.data;
                        }, 500);
                    }
                }
            });
        } else {
            return false;
        }
    });

    $("input[name='group_type']").change(function() {
        var selectedValue = $("input[name='group_type']:checked").val();
        if (selectedValue == "panel") {
            $(".text-danger.mb-0.custom-error").remove();
            $(".inveter-selected").addClass('d-none');
            $(".panel-selected").removeClass('d-none');
        } else {
            $(".inveter-selected").removeClass('d-none');
            $(".panel-selected").addClass('d-none');
        }
    });

    $("#inlineModal").on("hidden.bs.modal", function(e) {
        $(this).find('form').trigger('reset');
        $("#id").val("");
        $(".select2").val('').trigger('change');
        $(".invalid-feedback").html("");
        $(".custom-error").html("");
        $("#exampleModalTitle").html("Add Item Group");
    });

    $(document).on('click', '.edit', function() {
        var id = $(this).data('id');
        var url = "{{route('item-group.edit','id')}}".replace('id', id);
        $.ajax({
            type: "GET",
            url: url,
            dataType: 'json',
            cache: false,
            contentType: false,
            processData: false,
            beforeSend: function() {},
            success: function(data) {
                if (data.msg_type == "success") {
                    $("#exampleModalTitle").html("Edit Item Group");
                    $("input[name='group_type'][value='" + data.result.group_type + "']").prop('checked', true).trigger('change');
                    $("#inveter_company_id").val(data.result.inveter_company_id).trigger('change');
                    $("#inveter_kw").val(data.result.inveter_kw);
                    $("#inveter_phase").val(data.result.inverter_type).trigger('change');
                    $("#panel_company_id").val(data.result.panel_company_id).trigger('change');
                    $("#panel_type_id").val(data.result.panel_type_id).trigger('change');
                    $("#panel_watt_id").val(data.result.panel_watt_id).trigger('change');
                    $("#p_type").val(data.result.p_type).trigger('change');
                    $("#item_code").val(data.result.item_code);
                    $("#hsn_code").val(data.result.hsn_code);
                    $("#gst_rate").val(data.result.gst_rate);
                                        $("#moq_level").val(data.result.moq_level);

                    $("#id").val(id);
                    $("#inlineModal").modal('show');
                } else {
                    swal(data.msg_content, {
                        icon: "error",
                    });
                }
            }
        });
    });

    $(document).on('click', '.delete', function() {
        var btn = $(this);
        var id = btn.data('id');
        Swal.fire({
                title: "{{ __('message.Are you sure?') }}",
                text: "{{ __('message.You won`t be able to revert this!') }}",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: "{{ __('message.Yes, delete it!') }}",
                customClass: {
                    confirmButton: 'btn btn-primary',
                    cancelButton: 'btn btn-outline-danger ms-1'
                },
                buttonsStyling: false
            })
            .then(function(result) {
                if (result.value) {
                    axios.delete(URL + '/' + id)
                        .then(function(response) {
                            if (response.data.status == true) {
                                table.ajax.reload(null, false);
                                toastr.success("{{ __('message.Deleted successfully.') }}", "{{ __('message.Success') }}");
                            } else if (response.data.status == false && response.data.server_error) {
                                toastr.error("{{ __('message.Something went wrong. Please try again.') }}", "{{ __('message.Error') }}");
                            } else {
                                toastr.warning("{{ __('message.This Item has been used.') }}", "{{ __('message.Warning') }}");
                            }
                        })
                        .catch(function() {
                            toastr.error("{{ __('message.Something went wrong. Please try again.') }}", "{{ __('message.Error') }}");
                        });
                } else {
                    Swal.fire({
                        text: "{{ __('message.Your data is safe.') }}"
                    });
                }
            });
    });
</script>
@endsection
