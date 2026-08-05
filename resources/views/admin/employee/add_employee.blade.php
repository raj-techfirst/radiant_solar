@extends('layouts.app')
@section('title', 'Users')
@section('content')
    <div class="row">
        <div class="col-12">
            @if (isset($company) && isset($company->id))
                <h4 class="card-title mb-1">Edit User</h4>
            @else
                <h4 class="card-title mb-1">Add User</small></h4>
            @endif
        </div>
        <div class="col-12">
            <div class="card p-1">
                <form id="form" action="javascript:void(0)" method="POST">
                    @csrf
                    <div class="row">
                        @if (isset($company) && isset($company->id))
                            <input type="hidden" id="employee_id" name="employee_id" value="{{ $company->id }}">
                        @endif
                        <div class="col-12">
                            <div class="row">
                                <div class="col-12 col-lg-4 col-md-6 mb-1 custom-input-group radioeffect">
                                    <label class="form-label mb-1">Users Type</label><br>
                                    <input name="user_type" id="radio1" class="form-check-input radioshow" type="radio"
                                        data-class="div1" value="M" checked
                                        {{ isset($company) && $company->user_type == 'M' ? 'checked' : '' }}>
                                    <label for="radio1"
                                        class="form-check-label radGroup1">{{ __('message.As a Manager') }} &nbsp;
                                        &nbsp;</label>

                                    <input name="user_type" id="radio2" class="form-check-input radioshow" type="radio"
                                        data-class="div2" value="S"
                                        {{ isset($company) && $company->user_type == 'S' ? 'checked' : '' }}>
                                    <label for="radio2" class="form-check-label radGroup1">{{ __('message.As a Sales') }}
                                        &nbsp; &nbsp;</label>

                                    <input name="user_type" id="radio3" class="form-check-input radioshow" type="radio"
                                        data-class="div3" value="O"
                                        {{ isset($company) && $company->user_type == 'O' ? 'checked' : '' }}>
                                    <label for="radio3" class="form-check-label radGroup1">Other</label>
                                </div>
                                <div class="col-12 col-md-3 col-lg-4 mb-1 custom-input-group div1 allshow">
                                    <label class="form-label" for="manager_id">{{ __('message.Manager') }}<span
                                            class="text-danger">*</span></label>
                                    <select class="form-control form-select select2 custom-select2" name="manager_id"
                                        id="manager_id">
                                        <option selected disabled>{{ __('message.-- Select --') }}</option>
                                        @foreach ($companyManager as $value)
                                            <option value="{{ $value->id }}"
                                                {{ isset($company) && $company->manager_id == $value->id ? 'selected' : '' }}>
                                                {{ $value->user->name }} {{ $value->user->last_name }}</option>
                                        @endforeach
                                    </select>
                                    <span class="invalid-feedback d-block" id="error_district_id" role="alert"></span>
                                </div>
                                <div
                                    class="col-12 col-md-3 col-lg-4 mb-1 custom-input-group div3 {{ isset($company) && $company->user_type == 'O' ? '' : 'd-none' }}  roleshow">
                                    <label class="form-label" for="manager_id">Role<span
                                            class="text-danger">*</span></label>
                                    <select class="form-control form-select select2 custom-select2" name="role"
                                        id="role">
                                        <option selected disabled>{{ __('message.-- Select --') }}</option>
                                        @foreach ($roles as $value)
                                            <option value="{{ $value->name }}"
                                                {{ isset($company) && $company->user->roles[0]->name == $value->name ? 'selected' : '' }}>
                                                {{ $value->name }}</option>
                                        @endforeach
                                    </select>
                                    <span class="invalid-feedback d-block" id="error_district_id" role="alert"></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6 col-lg-6 mb-1 custom-input-group">
                            <label class="form-label" for="name">{{ __('message.First Name') }}<span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="name" id="name"
                                placeholder="{{ __('message.First Name') }}"
                                value="{{ isset($company) && isset($company->user->name) ? $company->user->name : '' }}">
                            <span class="invalid-feedback d-block" id="error_name" role="alert"></span>
                        </div>
                        <div class="col-12 col-md-6 col-lg-6 mb-1 custom-input-group">
                            <label class="form-label" for="last_name">{{ __('message.Last Name') }}<span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="last_name" id="last_name"
                                placeholder="{{ __('message.Last Name') }}"
                                value="{{ isset($company) && isset($company->user->last_name) ? $company->user->last_name : '' }}">
                            <span class="invalid-feedback d-block" id="error_last_name" role="alert"></span>
                        </div>
                        <div class="col-12 col-md-6 col-lg-6 mb-1 custom-input-group">
                            <label class="form-label" for="mobile ">{{ __('message.Mobile') }}<span
                                    class="text-danger">*</span></label>
                            <input type="text" maxlength="10" class="form-control" name="mobile" id="mobile"
                                placeholder="{{ __('message.Mobile No.') }}"
                                value="{{ isset($company) && isset($company->user->mobile) ? $company->user->mobile : '' }}">
                            <span class="invalid-feedback d-block" id="error_mobile" role="alert"></span>
                        </div>
                        <div class="col-12 col-md-6 col-lg-6 mb-1 custom-input-group">
                            <label class="form-label" for="email">{{ __('message.Email') }}<span
                                    class="text-danger">*</span></label>
                            <input type="email" class="form-control" name="email" id="email"
                                autocomplete="new-email" placeholder="{{ __('message.Email Address') }}"
                                value="{{ isset($company) && isset($company->user->email) ? $company->user->email : '' }}"
                                @if (isset($company) && isset($company->id)) disabled @else : @endif>
                            <span class="invalid-feedback d-block" id="error_email" role="alert"></span>
                        </div>
                        <div class="col-12 col-md-6 col-lg-6 mb-1 custom-input-group">
                            <label class="form-label" for="password">{{ __('message.Password') }} @if (isset($company))
                                    <span class="text-danger"></span>
                                @else
                                    <span class="text-danger">*</span>
                                @endif
                            </label>
                            <div class="input-group input-group-merge">
                                <span id="basic-default-password" class="input-group-text cursor-pointer toggle-password">
                                    <i class="fa fa-lock"></i>
                                </span>
                                <input type="password" class="form-control" name="password" id="password"
                                    autocomplete="new-password" placeholder="{{ __('message.Password') }}"
                                    value="">
                            </div>
                            <span class="invalid-feedback d-block" id="error_password" role="alert"></span>
                        </div>
                        <div class="col-12 col-md-6 col-lg-6 mb-1 custom-input-group">
                            <label class="form-label" for="confirm_password">{{ __('message.Confirm Password') }}
                                @if (isset($company))
                                    <span class="text-danger"></span>
                                @else
                                    <span class="text-danger">*</span>
                                @endif
                            </label>
                            <div class="input-group input-group-merge">
                                <span id="basic-default-password" class="input-group-text cursor-pointer toggle-password">
                                    <i class="fa fa-lock"></i>
                                </span>
                                <input type="password" class="form-control" name="confirm_password"
                                    autocomplete="new-password" id="confirm_password"
                                    placeholder="{{ __('message.Confirm Password') }}" value="">
                            </div>
                            <span class="invalid-feedback d-block" id="error_confirm_password" role="alert"></span>
                        </div>
                        <div class="col-12 col-md-6 col-lg-6 mb-1 custom-input-group">
                            <label class="form-label" for="district_id">District<span
                                    class="text-danger">*</span></label>
                            <select class="form-control form-select select2 custom-select2" name="district_id"
                                id="district_id">
                                <option selected disabled>{{ __('message.-- Select --') }}</option>
                                @foreach ($district as $value)
                                    <option value="{{ $value->id }}"
                                        {{ isset($company) && $company->state_id == $value->id ? 'selected' : '' }}>
                                        {{ $value->name }}</option>
                                @endforeach
                            </select>
                            <span class="invalid-feedback d-block" id="error_district_id" role="alert"></span>
                        </div>
                        <div class="col-12 col-md-6 col-lg-6 mb-1 custom-input-group">
                            <label class="form-label" for="taluka_id">{{ __('message.City') }}<span
                                    class="text-danger">*</span></label>
                            <select class="form-control form-select select2 custom-select2 taluka_id" name="taluka_id"
                                id="taluka_id">
                                <option selected disabled>{{ __('message.-- Select --') }}</option>
                                @isset($company)
                                    @foreach ($taluka as $item)
                                        <option value="{{ $item->id }}"
                                            {{ isset($company) && $company->city_id == $item->id ? 'selected' : '' }}>
                                            {{ $item->name }}</option>
                                    @endforeach
                                @endisset
                            </select>
                            <span class="invalid-feedback d-block" id="error_taluka_id" role="alert"></span>
                        </div>
                        <div class="col-12 col-lg-8 mb-1 custom-input-group">
                            <label class="form-label" for="address">{{ __('message.Address') }}</label>
                            <input type="text" class="form-control" name="address" id="address"
                                placeholder="{{ __('message.Address') }}"
                                value="{{ isset($company) && isset($company->address) ? $company->address : '' }}">
                            <span class="invalid-feedback d-block" id="error_address" role="alert"></span>
                        </div>
                        <div class="col-12 col-md-12 col-lg-4">
                            <div class="">
                                <small class=" d-block">ERP Show</small>
                                <h6 class="mb-0 form-switch pt-25"><input class="form-check-input quote" type="checkbox"
                                        role="switch" name="if_erp" value="1"
                                        {{ isset($company) && isset($company->user) && $company->user->if_erp == '1' ? 'checked' : '' }}>
                                </h6>
                            </div>
                        </div>
                        <div class="col-12 col-md-12 col-lg-12 bg-light pt-1">
                            <h4><b>Commission Details</b></h4>
                        </div>
                        @if (isset($company) && isset($company->id))
                        <div class="col-12 col-md-12 col-lg-12 mb-1 custom-input-group">
                            <label class="form-label d-block">Recalculate Sales Order Commission (by Effective Dates)</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="recalculate_commission" name="recalculate_commission" value="1">
                                <label class="form-check-label" for="recalculate_commission">YES / NO</label>
                            </div>
                        </div>
                        @endif
                        <div class="col-12 col-md-12 col-lg-12">
                            <div class="row form-repeater">
                                <table class="table table-bordered table-sm">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>EFFECTIVE DATE</th>
                                            <th>COMMISSION</th>
                                            <th>INSTALLATION</th>
                                            <th>SUB AGENT</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody data-repeater-list="invoice" class="sub_data">
                                        @if (isset($userCommissions) && $userCommissions->count() > 0)
                                            @foreach ($userCommissions as $idx => $uc)
                                                <tr data-repeater-item class="clone_row">
                                                    <td class="text-center">
                                                        <b class="sr_no">{{ $idx + 1 }}</b>
                                                    </td>
                                                    <td class="custom-input-group">
                                                        <input type="text" class="form-control flatpickr-date"
                                                            name="effective_date" readonly
                                                            value="{{ $uc->effective_date ? date('d-m-Y', strtotime($uc->effective_date)) : '' }}">
                                                    </td>
                                                    <td class="custom-input-group">
                                                        <input type="number" class="form-control number"
                                                            name="commission" value="{{ $uc->commission }}">
                                                    </td>
                                                    <td class="custom-input-group">
                                                        <input type="number" class="form-control number"
                                                            name="installation" value="{{ $uc->installation }}">
                                                    </td>
                                                    <td>
                                                        <select class="form-control form-select select2"
                                                            name="sub_agent_id">
                                                            <option value="0">Self</option>
                                                            @foreach ($companyManager as $value)
                                                                <option value="{{ $value->id }}"
                                                                    {{ $uc->sub_agent_id == $value->id ? 'selected' : '' }}>
                                                                    {{ $value->user->name }} {{ $value->user->last_name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </td>
                                                    <td class="text-center">
                                                        <button type="button"
                                                            class="badge badge-light-danger border-0 data-repeater-delete remove-item"
                                                            data-repeater-delete>
                                                            <i data-feather='trash-2'></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @else
                                            <tr data-repeater-item class="clone_row">
                                                <td class="text-center">
                                                    <b class="sr_no">1</b>
                                                </td>
                                                <td class="custom-input-group">
                                                    <input type="text" class="form-control flatpickr-date"
                                                        name="effective_date" readonly>
                                                </td>
                                                <td class="custom-input-group">
                                                    <input type="number" class="form-control number" name="commission">
                                                </td>
                                                <td class="custom-input-group">
                                                    <input type="number" class="form-control number"
                                                        name="installation">
                                                </td>
                                                <td>
                                                    <select class="form-control form-select select2" name="sub_agent_id">
                                                        <option selected value="0">Self</option>
                                                        @foreach ($companyManager as $value)
                                                            <option value="{{ $value->id }}"
                                                                {{ isset($company) && $company->manager_id == $value->id ? 'selected' : '' }}>
                                                                {{ $value->user->name }} {{ $value->user->last_name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                                <td class="text-center">
                                                    <button type="button"
                                                        class="badge badge-light-danger border-0 data-repeater-delete remove-item"
                                                        data-repeater-delete>
                                                        <i data-feather='trash-2'></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @endif
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="5"></td>
                                            <td class="text-center">
                                                <button class="badge badge-light-success border-0 add-new m-0"
                                                    type="button" data-repeater-create>
                                                    <i data-feather="plus" class="me-0"></i>
                                                    <!-- <span>Add</span> -->
                                                </button>
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                        <div class="col-md-12 mt-2">
                            <button type="submit"
                                class="btn btn-sm btn-primary float-end save">{{ __('message.Submit') }}</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@section('pagescript')
    <script type="text/javascript">
        flatpickr('.flatpickr-date', {
            enableTime: false,
            dateFormat: 'd-m-Y',
            defaultDate: ''
        });

        $('.toggle-password').click(function() {
            $(this).children().toggleClass('fa fa-lock fa fa-unlock');
            let input = $(this).next();
            input.attr('type', input.attr('type') === 'password' ? 'text' : 'password');
        });

        function updateSerialNumbers() {
            $('.sr_no').each(function(index) {
                $(this).text(index + 1);
            });
        }

        $(document).on('.remove-item').on('keydown', function() {
            var me = $(this);
            me.closest('tr').next('tr').find('input[name="effective_date"]').focus();
        });

        $(document).ready(function() {




            $('.form-repeater, .repeater-default').repeater({
                show: function() {
                    $(this).slideDown();
                    var obj = $(this);
                    var sr = $('.sr_no').length;
                    obj.find('.sr_no').text(sr);
                    obj.find('input[name="effective_date"]').focus();

                    obj.find('select').on('change', function() {
                        var element = $(this).attr('name');
                        $('#form').validate().showErrors({
                            [element]: ''
                        });
                    });

                    //destory and reinitialize flatpickr
                    if (obj.find('.flatpickr-date').hasClass('flatpickr-input')) {
                        obj.find('.flatpickr-date').flatpickr().destroy();
                    }
                    obj.find('.flatpickr-date').removeClass('flatpickr-input');
                    flatpickr(obj.find('.flatpickr-date'), {
                        enableTime: false,
                        dateFormat: 'd-m-Y',
                        defaultDate: ''
                    });

                    //destroy Select2 on the old row
                    obj.find('.select2').each(function() {
                        if ($(this).hasClass('select2-hidden-accessible')) {
                            $(this).select2('destroy');
                        }
                    });

                    obj.find('.select2-container').remove();


                    // Initialize Select2 on the new row
                    obj.find('.select2').select2({
                        dropdownAutoWidth: true,
                        width: '100%'
                    });

                    if (feather) {
                        feather.replace({
                            width: 14,
                            height: 14
                        });
                    }
                },
                hide: function(deleteElement) {
                    var obj = $(this);
                    var len = obj.parent().parent().find('.remove-item').length;
                    if (len != 0) {
                        if (len > 1) {
                            $(this).slideUp(deleteElement);
                            setTimeout(function() {
                                updateSerialNumbers();
                            }, 500);
                        } else {
                            Swal.fire({
                                text: "Can`t delete first item",
                            icon: 'warning',
                            confirmButtonText: 'OK',
                        });
                    }
                }
            }
        });

        $("#form").validate({
            rules: {
                manager_id: {
                    required: function(element) {
                        return $('.radioshow:checked').val() === 'S';
                    },
                },
                role: {
                    required: function(element) {
                        return $('.radioshow:checked').val() === 'O';
                    },
                },
                name: {
                    required: true,
                },
                last_name: {
                    required: true,
                },
                mobile: {
                    required: true,
                    minlength: 10,
                    regex: "[6-7-8-9]{1}[0-9]{9}"
                },
                password: {
                    required: true,
                },
                confirm_password: {
                    required: true,
                },
                district_id: {
                    required: true,
                },
                taluka_id: {
                    required: true,
                },
                email: {
                    required: true,
                }
            },
            messages: {
                manager_id: {
                    required: "Please Select Manager",
                },
                role: {
                    required: "Please Select Role",
                },
                name: {
                    required: "{{ __('message.Enter first name') }}",
                },
                last_name: {
                    required: "{{ __('message.Enter last name') }}",
                },
                mobile: {
                    required: "{{ __('message.Enter Mobile Number') }}",
                    minlength: "{{ __('message.Enter at least 10 digits') }}",
                    regex: "{{ __('message.Enter Valid Number') }}"
                },
                email: {
                    required: "{{ __('message.Enter email') }}",
                },
                password: {
                    required: "{{ __('message.Enter password') }}",
                },
                confirm_password: {
                    required: "{{ __('message.Enter confirm password') }}",
                },
                district_id: {
                    required: "{{ __('message.Select district') }}",
                },
                taluka_id: {
                    required: "Select taluka",
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

        if ("{{ Request::segment(3) }}" == 'create') {
            $("#radio1").attr('checked', true);
        }
        $(document).on('keypress', '#mobile', function() {
            if ($("#mobile").val().length > 9) {
                $("#mobile").attr('type', 'text');
            } else {
                $("#mobile").attr('type', 'number');
            }
        });
    });
    $(document).on('click', '.save', function() {
        var formData = new FormData($("#form")[0]);
        if ($("#employee_id").val()) {
            var temp = ($("#name").val() != "" && $("#last_name").val() != "" && $("#district_id").val() !=
                null && $("#taluka_id").val() != null);
        } else {
            var temp = ($("#name").val() != "" && $("#last_name").val() != "" && $("#email").val() != "" && $(
                    "#password").val() != "" && $("#confirm_password").val() != null && $("#district_id")
                .val() != null && $("#taluka_id").val() != null);
        }
        if (temp) {
            $.ajax({
                type: "POST",
                url: "{{ route('employee.store') }}",
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

                        toastr.error(
                            "{{ __('message.Something went wrong. Please try again.') }}",
                            "{{ __('message.Error') }}");

                    } else if (response.status == false && response.label) {
                        if (response.label == 'manager') {
                            toastr.warning(
                                "{{ __('message.Your manger limit`s has been ended.') }}",
                                "{{ __('message.Warning') }}");
                        } else {
                            toastr.warning(
                                "{{ __('message.Your sales limit`s has been ended.') }}",
                                "{{ __('message.Warning') }}");
                        }
                    } else if (response.status == false) {
                        if (response.message) {
                            toastr.error(response.message, "{{ __('message.Error') }}");
                        } else {
                            $.each(response.errors, function(key, value) {
                                $('#error_' + key).html('<p class="text-danger mb-0">' +
                                    value +
                                    '</p>');
                            });
                            toastr.warning("{{ __('message.Please input proper data.') }}",
                                "{{ __('message.Warning') }}");
                        }
                    } else {
                        $('#form')[0].reset();
                        toastr.success(response.message, "{{ __('message.Success') }}");
                        setTimeout(function() {
                            location.href = response.data;
                        }, 2000);
                    }
                }
            });
        }
    });

    $(document).ready(function() {
        var temp = $('.radioshow:checked').val();
        $('.allshow').addClass('d-none');
        $('.roleshow').addClass('d-none');
        if (temp == 'S') {
            $('.allshow').removeClass('d-none');
        }
        if (temp == 'O') {
            $('.roleshow').removeClass('d-none');
        }
        $('.radioshow').on('change', function() {
            var val = $(this).attr('data-class');
            var data = $(this).val();
            $('.allshow').addClass('d-none');
            $('.roleshow').addClass('d-none');
            if (data == 'S') {
                $('.allshow').removeClass('d-none');
            }
            if (data == 'O') {
                $('.roleshow').removeClass('d-none');
            }
        });

        var taluka_id = $("#taluka_id").val();
        if (taluka_id != null) {
            changeCity(0);
        }
        $(document).on('change', '#district_id', function() {
            changeCity();
        });
    });

    function changeCity(wherefrom = 1) {
        var district_id = $("#district_id").val();
        var route = "{{ route('taluka-view') }}";
        // var route = "{{ route('city.show', 'id') }}".replace('id', district_id);
        $.ajax({
            type: 'get',
            url: route,
            datatype: 'json',
            data: {
                "district_id": district_id,
            },
            success: function(response) {
                if (wherefrom == 1) {
                    $("#taluka_id").empty('');
                    $("#taluka_id").append(
                        `<option selected disabled>{{ __('message.-- Select --') }}</option>`);
                        $.each(response, function(i, value) {
                            $("#taluka_id").append('<option value="' + value.id + '">' + value.name +
                                '</option>');
                        });
                    }
                }
            });
        }

        $(document).on('change', '.district_id', function() {
            // alert('hello');
            var district_id = $('#district_id').val();
            $.ajax({
                url: "{{ url('taluka-view') }}",
                type: "get",
                datatype: 'json',
                data: {
                    'district_id': district_id,
                },
                success: function(response) {
                    $("#taluka_id").empty();
                    $.each(response, function(i, value) {
                        $("#taluka_id").append('<option value=' + value.id + '>' + value.name +
                            '</option>');
                    });
                }
            });
        });

        $('select').on('change', function() {
            var element = $(this).attr('name');
            $('#form').validate().showErrors({
                [element]: ''
            });
        });
    </script>
@endsection
