@extends('layouts.app')
@section('title', 'Lead')
@section('content')
    <div class="row">
        <div class="col-12">
            @if (isset($lead) && isset($lead->id))
                <h4 class="card-title mb-1">{{ __('message.Edit Lead') }}</h4>
            @else
                <h4 class="card-title mb-1">{{ __('message.Add Lead') }}</h4>
            @endif
        </div>
        <div class="col-8">
            <div class="card p-1">
                <div class="div2 allshow">
                    <form id="form" action="javascript:void(0)" method="POST">
                        @csrf
                        <div class="row">
                            @if (isset($lead) && isset($lead->id))
                                <input type="hidden" id="lead_id" name="lead_id" value="{{ $lead->id }}">
                            @endif
                            <div class="col-12 col-md-6 col-lg-4 mb-1 custom-input-group">
                                <label class="form-label" for="mobile">{{ __('message.Mobile') }} <span
                                        class="text-danger">*</span></label>
                                <input type="number" maxlength="10" class="form-control" name="mobile" id="mobile"
                                    placeholder="{{ __('message.Mobile No.') }}"
                                    value="{{ isset($lead) && isset($lead->mobile) ? $lead->mobile : '' }}">
                                <span class="invalid-feedback d-block" id="error_mobile" role="alert"></span>
                            </div>
                            <div class="col-12 col-md-6 col-lg-4 mb-1 custom-input-group">
                                <label class="form-label" for="name">{{ __('message.Name') }} <span
                                        class="text-danger"></span></label>
                                <input type="text" class="form-control" name="name" id="name"
                                    placeholder="{{ __('message.Name') }}"
                                    value="{{ isset($lead) && isset($lead->name) ? $lead->name : '' }}"
                                    oninput="this.value = this.value.toUpperCase()">
                                <span class="invalid-feedback d-block" id="error_name" role="alert"></span>
                            </div>

                            <div class="col-12 col-md-6 col-lg-4 mb-1 custom-input-group">
                                <label class="form-label" for="kw">{{ __('message.KW') }} <span
                                        class="text-danger"></span></label>
                                <input type="text" class="form-control" name="kw" id="kw"
                                    placeholder="{{ __('message.KW') }}"
                                    value="{{ isset($lead) && isset($lead->kw) ? $lead->kw : '' }}">
                                <span class="invalid-feedback d-block" id="error_kw" role="alert"></span>
                            </div>
                            <div class="col-12 col-md-6 col-lg-4 mb-1 custom-input-group">
                                <label class="form-label" for="reference">{{ __('message.Reference') }} <span
                                        class="text-danger"></span></label>
                                <input type="text" class="form-control" name="reference" id="reference"
                                    placeholder="{{ __('message.Reference') }}"
                                    value="{{ isset($lead) && isset($lead->reference) ? $lead->reference : '' }}"
                                    oninput="this.value = this.value.toUpperCase()">
                                <span class="invalid-feedback d-block" id="error_reference" role="alert"></span>
                            </div>
                            <div class="col-12 col-md-6 col-lg-4 mb-1 custom-input-group">
                                <label class="form-label" for="agent_sales_person_id">Agent Sales Person<span
                                        class="text-danger">*</span></label>
                                <select class="form-control form-select select2 custom-select2 agent_sales_person_id"
                                    name="agent_sales_person_id" id="agent_sales_person_id">
                                    <option selected disabled>-- Select --</option>
                                    @foreach ($agentSalesPerson as $value)
                                        <option value="{{ $value->id }}"
                                            {{ isset($lead) && $lead->agent_sales_person_id == $value->id ? 'selected' : '' }}>
                                            {{ $value->name }}</option>
                                    @endforeach
                                </select>
                                <span class="invalid-feedback d-block" id="error_agent_sales_person_id"
                                    role="alert"></span>
                            </div>
                            <div class="col-12 col-md-6 col-lg-4 mb-1 custom-input-group">
                                <label class="form-label" for="lead_status">{{ __('message.Status') }} <span
                                        class="text-danger"></span></label>
                                <select class="form-control form-select select2 custom-select2 lead_status"
                                    name="lead_status" id="lead_status">

                                    @if($leadStatus->count() > 0)
                                    @foreach ($leadStatus as $value)

                                    <option value="{{ $value->id }}" {{ isset($lead) && $lead->lead_status_id == $value->id ? 'selected' : '' }}>
                                        {{ $value->name }}
                                    </option>

                                    @endforeach
                                    @endif

                                </select>
                                <span class="invalid-feedback d-block" id="error_lead_status" role="alert"></span>
                            </div>
                            @if (Auth::user()->roles[0]->name != 'Sales')
                                <div class="col-12 col-md-6 col-lg-4 mb-1 custom-input-group">
                                    <label class="form-label" for="assign_id">{{ __('message.Assigned') }} <span
                                            class="text-danger">*</span></label>
                                    <select class="form-control form-select select2 custom-select2 assign_id anlayst"
                                        name="assign_id" id="assign_id">
                                        <option selected disabled>{{ __('message.-- Select --') }}</option>
                                        @foreach ($companyProfile as $value)
                                            <option value="{{ $value->id }}"
                                                {{ isset($lead) && $lead->assign_id == $value->id ? 'selected' : '' }}>
                                                {{ $value->user->name }} {{ $value->user->last_name }}</option>
                                        @endforeach
                                    </select>
                                    <span class="invalid-feedback d-block" id="error_assign_id" role="alert"></span>
                                </div>
                            @endif


                            <div class="col-12 col-md-6 col-lg-4 mb-1 custom-input-group">
                                <label class="form-label" for="source_id">Source</label>
                                <select class="form-control form-select select2 custom-select2 source_id anlayst"
                                    name="source_id" id="source_id">
                                    <option selected disabled>{{ __('message.-- Select --') }}</option>
                                    @foreach ($source as $value)
                                        <option value="{{ $value->id }}"
                                            {{ isset($lead) && $lead->source_id == $value->id ? 'selected' : '' }}>
                                            {{ $value->source_name }}</option>
                                    @endforeach
                                </select>
                                <span class="invalid-feedback d-block" id="error_source_id" role="alert"></span>
                            </div>

                            <div class="col-12 col-md-6 col-lg-12 mb-1 custom-input-group">
                                <label class="form-label" for="address">{{ __('message.Address') }} <span
                                        class="text-danger"></span></label>
                                <textarea class="form-control" name="address" id="address" placeholder="{{ __('message.Address') }}">{{ isset($lead) && isset($lead->address) ? $lead->address : '' }}</textarea>
                                <span class="invalid-feedback d-block" id="error_address" role="alert"></span>
                            </div>
                            <div class="col-12 col-md-6 col-lg-12 mb-1 custom-input-group">
                                <label class="form-label" for="remark">Remark</label>
                                <textarea class="form-control" name="remark" id="remark" placeholder="Remark (If Any)">{{ isset($lead) && isset($lead->remark) ? $lead->remark : '' }}</textarea>
                                <span class="invalid-feedback d-block" id="error_remark" role="alert"></span>
                            </div>
                        </div>
                        <div class="col-md-12 mt-2">
                            <button type="submit"
                                class="btn btn-sm btn-primary float-end save">{{ __('message.Submit') }}</button>
                            <a role="botton" class="btn btn-sm btn-primary float-end mx-1"
                                href="{{ route('lead.index') }}">{{ __('message.Cancel') }}</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-4">
            <div class="card card-employee-task">
                <div class="card-header">
                    <h4 class="card-title">Past Lead</h4>
                </div>
                <div class="card-body" id="pastLeadList">

                </div>
            </div>
        </div>
    </div>

@endsection
@section('pagescript')
    <script type="text/javascript">
        $('.custom-select2').on('change', function() {
            var element = $(this).attr('name');
            $('#form').validate().showErrors({
                [element]: ''
            });
        });

        $(document).on('click', '.save', function() {

            if ($("#form").valid()) {
                var formData = new FormData($("#form")[0]);
                $.ajax({
                    type: "POST",
                    url: "{{ route('lead.store') }}",
                    data: formData,
                    dataType: 'json',
                    cache: false,
                    contentType: false,
                    processData: false,
                    beforeSend: function() {
                        $("#error_name").html(' ');
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
                            toastr.success(response.message, "{{ __('message.Success') }}");
                            setTimeout(function() {
                                location.href = response.data;
                            }, 2000);
                        }
                    }
                });
            } else {
                return false;
            }
        });

        $(document).on('click', '.view', function(e) {
            e.preventDefault();
            $("#exampleModal").modal("show");
        });

        $(document).ready(function() {
            $("#form").validate({
                rules: {
                    mobile: {
                        required: true,
                        minlength: 10,
                        regex: "[6-7-8-9]{1}[0-9]{9}",
                    },
                    agent_sales_person_id: {
                        required: true
                    },
                    assign_id: {
                        required: true
                    },
                    kw: {
                        regex: "[0-9].[0-9]"
                    }

                },
                messages: {
                    mobile: {
                        required: "{{ __('message.Enter Mobile Number') }}",
                        minlength: "{{ __('message.Enter at least 10 digits') }}",
                        regex: "{{ __('message.Enter Valid Number') }}"
                    },
                    agent_sales_person_id: {
                        required: "Select Agent Sales Person"
                    },
                    assign_id: "{{ __('message.Select assigned') }}",
                    kw: {
                        regex: "Enter Valid KW"
                    }
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

            var date = new Date();
            var maxDate = new Date(date.getFullYear(), date.getMonth(), date.getDate());
            $("#last_contacted").datepicker({
                dateFormat: 'dd-mm-yy',
                maxDate: maxDate,
            });

            $('#mobile').on('input', function() {
                var mobileNumber = $(this).val();
                if (mobileNumber.length === 10 && /^\d+$/.test(mobileNumber)) {
                    $.ajax({
                        url: "{{ route('search-lead') }}",
                        method: 'GET',
                        data: {
                            mobileNumber: mobileNumber
                        },
                        success: function(response) {
                            $('#pastLeadList').empty();
                            $.each(response, function(index, lead) {
                                // date
                                var date = new Date(lead.created_at);
                                var day = date.getDate();
                                var month = date.getMonth() + 1;
                                var year = date.getFullYear();
                                if (day < 10) {
                                    day = '0' + day;
                                }
                                if (month < 10) {
                                    month = '0' + month;
                                }
                                var formattedDate = day + '-' + month + '-' + year;
                                // date
                                // name capital
                                var name = lead.name.toLowerCase().replace(/\b\w/g,
                                    function(match) {
                                        return match.toUpperCase();
                                    });
                                // name capital
                                // url
                                var showUrl = "{{ route('follow-up.edit', ':id') }}";
                                showUrl = showUrl.replace(':id', lead.id);
                                // url
                                var referenceHtml = lead.reference !== null ?
                                    ` - ${lead.reference}` : '';
                                var leadHtml = `<a href="${showUrl}" target="_blank">
                                <div class="employee-task d-flex justify-content-between align-items-center">
                                    <div class="w-100">
                                        <div class="my-auto">
                                            <h6 class="mb-0">${name}</h6>
                                            <small>${lead.agent_sales_person.name}${referenceHtml}</small>
                                            <small class="float-end">${formattedDate}</small>
                                        </div>
                                    </div>
                                </div></a>
                            `;
                                $('#pastLeadList').append(leadHtml);
                            });
                        },
                        error: function(xhr, status, error) {
                            // Handle errors
                            console.error(xhr.responseText);
                        }
                    });
                }
            });
            $(document).on('keypress', '#mobile', function() {
                if ($("#mobile").val().length > 9) {
                    $("#mobile").attr('type', 'text');
                } else {
                    $("#mobile").attr('type', 'number');
                }
            });
            $('.radioshow').on('change', function() {
                var val = $(this).attr('data-class');
                $('.allshow').hide();
                $('.' + val).show();
            });

            $("#details").on('change', function() {
                if ($("#details").val() == 'off') {
                    $("#details").val('on');
                    $("#details_div").removeClass('d-none');
                } else {
                    $("#details_div").addClass('d-none');
                    $("#details").val('off');
                }
            });
        });
    </script>
@endsection
