@extends('layouts.app')
@section('title', 'Complaint Management')
@section('content')
    <div class="row">
        <div class="col-12 mb-1">
            <h4 class="content-header-title float-start">Complaint List</h4>

          
            <button type="button" data-bs-toggle="modal" data-bs-target="#addexampleModal"
                class="btn btn-sm btn-primary float-end"><i class="fa fa-plus me-25"></i> {{ __('message.Add New') }}</button>
				
				<a href="{{ route('inquiry-dashboard') }}"
                class="btn btn-sm btn-info float-end me-1"><i class="fa fa-dashboard me-25"></i> Dashboard</a>



        </div>
        <div class="col-12">
            <div class="card p-1">
                <div class="row">
                    <div class="col-12">
                        <h3>Filter</h3>
                    </div>
                    <div class="col-sm-12 col-md-3 col-lg-2 custom-input-group">
                        <label class="form-label" for="from_date">From Date</label>
                        <input type="text" class="form-control flatpickr-date" id="from_date" placeholder="dd-mm-yyyy">
                    </div>
                    <div class="col-sm-12 col-md-3 col-lg-2 custom-input-group">
                        <label class="form-label" for="to_date">To Date</label>
                        <input type="text" class="form-control flatpickr-date" id="to_date" placeholder="dd-mm-yyyy">
                    </div>
                    <div class="col-sm-12 col-md-6 col-lg-2 custom-input-group">
                        <label class="form-label" for="filter_consumer">Consumer</label>
                        <input type="text" class="form-control" id="filter_consumer" placeholder="Name / Mobile / Consumer No.">
                    </div>
                    <div class="col-sm-12 col-md-3 col-lg-2 custom-input-group">
                        <label class="form-label" for="filter_status">Status</label>
                        <select class="form-select select2" id="filter_status">
                            <option value="" selected>All</option>
                            @php $serviceStatus = serviceStatus(); @endphp
                            @foreach ($serviceStatus as $value)
                                <option value="{{ $value['id'] }}">{{ $value['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-sm-12 col-md-3 col-lg-2 custom-input-group">
                        <label class="form-label" for="filter_consumer_flag">Consumer Type</label>
                        <select class="form-select select2" id="filter_consumer_flag">
                            <option value="" selected>All</option>
                            <option value="new">New Consumer</option>
                            <option value="old">Old Consumer</option>
                        </select>
                    </div>
                    <div class="col-sm-12 col-md-3 col-lg-2 custom-input-group">
                        <label class="form-label" for="filter_assign_person">Assign Person</label>
                        <select class="form-select select2" id="filter_assign_person">
                            <option value="" selected>All</option>
                            @foreach ($agentSalesPerson as $value)
                                <option value="{{ $value->id }}">{{ $value->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-sm-12 col-md-12 col-lg-12 custom-input-group pt-2">
                        <div class="d-flex justify-content-end">
                            <button class="btn btn-gradient-primary btn-sm filter" type="button" data-bs-toggle="tooltip"
                                data-placement="top" title="Click to Filter">
                                <i data-feather='search'></i>
                            </button>
                            <button class="btn btn-gradient-danger btn-sm reset ms-1" type="reset"
                                data-bs-toggle="tooltip" data-placement="top" title="Click to Reset Filter">
                                <i data-feather='x'></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card p-1">
                <table id="inquiry" class="datatables-basic table table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Action</th>
                            <th>Consumer Name</th>
                            <th>Consumer Number</th>
                            <th>Contact Number</th>
                            <th>Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>

                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-l">
            <div class="modal-content" id="modal_content">

            </div>
        </div>
    </div>

    <div class="modal fade" id="addexampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-l">
            <div class="modal-content" id="add_modal_content">

                <div class="modal-header pb-0 bg-transparent">
                    <h4 class="text-center mb-0"> Add New</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">

                    <form class="auth-register-form" id="form" action="javascript:void(0)" method="POST">
                        @csrf
                        <div class="row">
                            <hr>

                            <div class="col-12 col-sm-12 col-md-12 col-lg-12 mb-1">
                                <label class="form-label">Consumer Type <span class="text-danger">*</span></label>
                                <div class="d-flex gap-2">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="consumer_flag" id="flag_new" value="new" checked>
                                        <label class="form-check-label" for="flag_new">New Consumer</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="consumer_flag" id="flag_old" value="old">
                                        <label class="form-check-label" for="flag_old">Old Consumer</label>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 col-sm-12 col-md-12 col-lg-6 pe-1">
                                <div class="mb-1 custom-input-group">
                                    <label class="form-label" for="contact_number">Contact Number<span
                                            class="text-danger">*</span></label>
                                    <input type="number" maxlength="10" name="contact_number" id="contact_number"
                                        class="form-control number" placeholder="Contact Number" required />
                                    <span class="invalid-feedback d-block" id="error_contact_number" role="alert"></span>
                                </div>
                            </div>

                            {{-- New Consumer: select dropdown --}}
                            <div class="col-12 col-sm-12 col-md-12 col-lg-6 ps-0" id="consumer_number_select_wrap">
                                <div class="mb-1  custom-input-group">
                                    <label class="form-label" for="consumer_number">Consumer Number<span
                                            class="text-danger">*</span></label>
                                    <select class="form-control form-select select2 custom-select2" required
                                        name="consumer_number" id="consumer_number">
                                        <option value="" selected>Select Any</option>
                                    </select>
                                    <span class="invalid-feedback d-block" id="error_consumer_number" role="alert"></span>
                                </div>
                            </div>

                            {{-- Old Consumer: text input --}}
                            <div class="col-12 col-sm-12 col-md-12 col-lg-6 ps-0" id="consumer_number_input_wrap" style="display: none;">
                                <div class="mb-1 custom-input-group">
                                    <label class="form-label" for="consumer_number_text">Consumer Number<span
                                            class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="consumer_number_text" id="consumer_number_text"
                                        placeholder="Consumer Number" disabled />
                                    <span class="invalid-feedback d-block" id="error_consumer_number_text" role="alert"></span>
                                </div>
                            </div>

                            <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                                <div class="mb-1 custom-input-group">
                                    <label class="form-label" for="consumer_name">Consumer Name<span
                                            class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="consumer_name" id="consumer_name"
                                        placeholder="Consumer Name" required />
                                    <span class="invalid-feedback d-block" id="error_consumer_name" role="alert"></span>
                                </div>
                            </div>

                            {{-- Invoice Date: hidden for new, visible for old --}}
                            <div class="col-12 col-sm-12 col-md-12 col-lg-6 pe-1" id="invoice_date_wrap" style="display: none;">
                                <div class="mb-1 custom-input-group">
                                    <label class="form-label" for="invoice_date">Invoice Date <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control flatpickr-date" name="invoice_date" id="invoice_date"
                                        placeholder="dd-mm-yyyy" disabled />
                                    <span class="invalid-feedback d-block" id="error_invoice_date" role="alert"></span>
                                </div>
                            </div>

                            {{-- Warranty Status Badge --}}
                            <div class="col-12 col-sm-12 col-md-12 col-lg-12" id="warranty_status_wrap" style="display: none;">
                                <div class="mb-1">
                                    <div id="warranty_badge" class="p-75 rounded text-center fw-bold fs-6"></div>
                                    <input type="hidden" name="warranty_status" id="warranty_status" value="">
                                    <input type="hidden" name="invoice_date_hidden" id="invoice_date_hidden" value="">
                                </div>
                            </div>

                            <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                                <div class="mb-1 custom-input-group">
                                    <label class="form-label" for="assign_person_id">Assign Person <span class="text-danger">*</span></label>
                                    <select class="form-control form-select select2 custom-select2" required
                                        name="assign_person_id" id="assign_person_id">
                                        <option value="" selected>Select Any</option>
                                        @foreach ($agentSalesPerson as $value)
                                            <option value="{{ $value->id }}">{{ $value->name }}</option>
                                        @endforeach
                                    </select>
                                    <span class="invalid-feedback d-block" id="error_assign_person_id" role="alert"></span>
                                </div>
                            </div>

                            <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                                <div class="mb-1 custom-input-group">
                                    <label class="form-label" for="image">Image</label>
                                    <input type="file" id="image" class="form-control" name="image"
                                        placeholder="Image" />
                                    <span class="invalid-feedback d-block" id="error_image" role="alert"></span>
                                </div>
                            </div>
                            <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                                <div class="mb-1 custom-input-group">
                                    <label class="form-label" for="problem">Problem<span
                                            class="text-danger">*</span></label>
                                    <textarea class="form-control" name="problem" id="problem" rows="3" placeholder="..." required></textarea>
                                    <span class="invalid-feedback d-block" id="error_problem" role="alert"></span>
                                </div>
                            </div>

                            <div class="col-12 col-sm-12 col-md-12  col-lg-12">
                                <button type="submit" class="btn btn-primary save float-end w-100">Submit</button>
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
    const URL = "{{route('inquiry-list')}}";

    var table = '';
    $(function() {

        flatpickr('.flatpickr-date', {
            enableTime: false,
            dateFormat: 'd-m-Y',
            defaultDate: '',
        });

        table = $('#inquiry').DataTable({
            ajax: {
                url: URL,
                data: function(d) {
                    d.from_date = $('#from_date').val();
                    d.to_date = $('#to_date').val();
                    d.consumer = $('#filter_consumer').val();
                    d.status = $('#filter_status').val();
                    d.consumer_flag = $('#filter_consumer_flag').val();
                    d.assign_person_id = $('#filter_assign_person').val();
                }
            },
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
                    data: 'consumer_name',
                    name: 'consumer_name'
                },
                {
                    data: 'consumer_number',
                    name: 'consumer_number'
                },
                {
                    data: 'contact_number',
                    name: 'contact_number'
                },

                {
                    data: 'created_at',
                    name: 'created_at'
                },
                {
                    data: 'status',
                    name: 'status'
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

   $(document).on('blur', '#contact_number', function () {
        const contact_number = $(this).val().trim();
        const phoneRegex = /^[0-9]{10}$/;
        if (phoneRegex.test(contact_number)) {
            var url = "{{route('get-consumer-using-mobile')}}";
            $.ajax({
                url: url,
                type: 'post',
                data: {
                    "contact_number": contact_number,
                    "_token": "{{ csrf_token() }}",
                },
                dataType: 'json',
                success: function(response) {
                    $('#consumer_number').html('');
                    if(response.status == true)
                    {
                        $('#consumer_name').val(response.message[0].consumer_name);
                        $('#consumer_number').html('');
                        $.each(response.message, function(key, value) {
                            $('#consumer_number').append('<option value="'+value.consumer_number+'" data-name="'+value.consumer_name+'" data-invoice-date="'+(value.invoice_date || '')+'">' + value.consumer_number + '</option>');
                        });
                        // Trigger warranty check for first option
                        checkWarrantyFromSelect();
                    }
                    else
                    {
                        $('#consumer_number').html('<option value="" selected>Select Any</option>');
                        $('#consumer_name').val('');
                        hideWarranty();
                    }
                }
            });
        }
    });

     $(document).on('click', '.view', function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        var url = "{{route('inquiry-show','id')}}".replace('id', id);
        $("#exampleModal").modal("show");
        $.ajax({
            url: url,
            type: 'get',
            datatype: 'json',
            data: {
                "_token": "{{ csrf_token() }}",
            },
            success: function(response) {
                $("#modal_content").html(response.html);
                if (feather) {
                    feather.replace({
                        width: 14,
                        height: 14
                    });
                }
            }
        });
    });


    $(document).on('click', '.delete', function() {
        var btn = $(this);
        var id = btn.data('id');
        const Delete = "{{route('inquiry-delete','id')}}".replace('id', id);
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
                    axios.delete(Delete)
                        .then(function(response) {
                            if (response.data.status == true) {
                                table.ajax.reload(null, false);
                                toastr.success("{{ __('message.Deleted successfully.') }}", "{{ __('message.Success') }}");
                            } else if (response.data.status == false && response.data.server_error) {
                                toastr.error("{{ __('message.Something went wrong. Please try again.') }}", "{{ __('message.Error') }}");
                            } else {
                                toastr.warning("{{ __('message.This category has been used.') }}", "{{ __('message.Warning') }}");
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

     document.querySelectorAll('.number').forEach(function(input) {
        input.addEventListener('input', function(e) {
            this.value = this.value.replace(/\D/g, '').slice(0, 10);
            if (this.value.charAt(0) === '0') {
                this.value = this.value.substring(1);
            }
        });
    });

    // Warranty check helper
    function checkWarranty(invoiceDate) {
        if (!invoiceDate || invoiceDate == '' || invoiceDate == 'null') {
            $('#warranty_status_wrap').show();
            $('#warranty_badge').html('<i class="fa fa-exclamation-triangle me-25"></i> Invoice Date Not Available')
                .removeClass('bg-light-success text-success bg-light-danger text-danger')
                .addClass('bg-light-warning text-warning');
            $('#warranty_status').val('');
            $('#invoice_date_hidden').val('');
            return;
        }
        var invoice = new Date(invoiceDate);
        var warrantyEnd = new Date(invoice);
        warrantyEnd.setFullYear(warrantyEnd.getFullYear() + 5);
        var today = new Date();
        var endFormatted = warrantyEnd.toLocaleDateString('en-GB');

        $('#warranty_status_wrap').show();
        if (today <= warrantyEnd) {
            $('#warranty_badge').html('<i class="fa fa-check-circle me-25"></i> In Warranty (Till ' + endFormatted + ')')
                .removeClass('bg-light-danger text-danger bg-light-warning text-warning')
                .addClass('bg-light-success text-success');
            $('#warranty_status').val('in_warranty');
        } else {
            $('#warranty_badge').html('<i class="fa fa-times-circle me-25"></i> Out of Warranty (Expired ' + endFormatted + ')')
                .removeClass('bg-light-success text-success bg-light-warning text-warning')
                .addClass('bg-light-danger text-danger');
            $('#warranty_status').val('out_of_warranty');
        }
        $('#invoice_date_hidden').val(invoiceDate);
    }

    function checkWarrantyFromSelect() {
        var selected = $('#consumer_number option:selected');
        var invoiceDate = selected.data('invoice-date') || '';
        checkWarranty(invoiceDate);
    }

    function hideWarranty() {
        $('#warranty_status_wrap').hide();
        $('#warranty_badge').html('');
        $('#warranty_status').val('');
        $('#invoice_date_hidden').val('');
    }

    // When consumer number select changes (new consumer)
    $(document).on('change', '#consumer_number', function() {
        checkWarrantyFromSelect();
    });

    // When invoice date changes (old consumer)
    $(document).on('change', '#invoice_date', function() {
        var val = $(this).val();
        if (val) {
            // Convert dd-mm-yyyy to yyyy-mm-dd
            var parts = val.split('-');
            var isoDate = parts[2] + '-' + parts[1] + '-' + parts[0];
            checkWarranty(isoDate);
        } else {
            hideWarranty();
        }
    });

    // Toggle between New / Old consumer fields
    $(document).on('change', 'input[name="consumer_flag"]', function() {
        var flag = $(this).val();
        if (flag == 'old') {
            // Show text input, hide select dropdown
            $('#consumer_number_select_wrap').hide();
            $('#consumer_number').prop('disabled', true).removeAttr('required');
            $('#consumer_number_input_wrap').show();
            $('#consumer_number_text').prop('disabled', false).attr('required', 'required').attr('name', 'consumer_number');
            $('#consumer_number').removeAttr('name');
            $('#consumer_name').val('').prop('readonly', false);
            // Show invoice date input for old consumer
            $('#invoice_date_wrap').show();
            $('#invoice_date').prop('disabled', false).attr('required', 'required');
            flatpickr('#invoice_date', { enableTime: false, dateFormat: 'd-m-Y', defaultDate: '' });
            hideWarranty();
        } else {
            // Show select dropdown, hide text input
            $('#consumer_number_select_wrap').show();
            $('#consumer_number').prop('disabled', false).attr('required', 'required').attr('name', 'consumer_number');
            $('#consumer_number_input_wrap').hide();
            $('#consumer_number_text').prop('disabled', true).removeAttr('required').removeAttr('name');
            $('#consumer_name').val('').prop('readonly', false);
            $('#consumer_number').html('<option value="" selected>Select Any</option>');
            $('#contact_number').val('');
            // Hide invoice date input for new consumer
            $('#invoice_date_wrap').hide();
            $('#invoice_date').prop('disabled', true).removeAttr('required').val('');
            hideWarranty();
        }
    });

    $("#form").validate({
            ignore: ":disabled",
            rules: {
                consumer_name: {
                    required: true,
                },
                consumer_number: {
                    required: true,
                },
                contact_number: {
                    regex: /^[0-9]{10}$/,
                    required: true,
                    minlength: 10,
                },
                problem: {
                    required: true,
                }
            },
            messages: {
                consumer_name: {
                    required: "Enter Consumer Name"
                },
                consumer_number: {
                    required: "Enter Consumer Number",
                },
                contact_number: {
                    regex: "Enter valid number",
                    required: "Enter contact number",
                    minlength: "Enter at least 10 digits",
                },
                problem: {
                    required: "Enter Problem"
                }
            },
            errorElement: "small",
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
                url: "{{route('save-inquiry')}}",
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
                    if (response.status_code == 500) {
                        toastr.error(response.message, "{{ __('message.Error') }}");
                    } else if (response.status_code == 403) {
                        toastr.warning(response.message, "{{ __('message.Warning') }}");
                    } else if (response.status_code == 201) {
                        $.each(response.errors, function(key, value) {
                            $('#error_' + key).html('<p class="text-danger mb-0">' + value + '</p>');
                        });
                        toastr.warning(response.message, "{{ __('message.Warning') }}");
                    } else {
                        $('#form')[0].reset();
                        toastr.success(response.message, "{{ __('message.Success') }}");
                        setTimeout(function() {
                            location.href = response.data;
                        }, 100);
                    }
                }
            });
        } else {
            return false;
        }
    });

    $(document).on('click', '.filter', function() {
        table.draw();
    });

    $(document).on('click', '.reset', function() {
        $('#from_date').val('');
        $('#to_date').val('');
        $('#filter_consumer').val('');
        $('#filter_status').val('').trigger('change');
        $('#filter_consumer_flag').val('').trigger('change');
        $('#filter_assign_person').val('').trigger('change');
        flatpickr('.flatpickr-date', {
            enableTime: false,
            dateFormat: 'd-m-Y',
            defaultDate: '',
        });
        table.draw();
    });
</script>
@endsection
