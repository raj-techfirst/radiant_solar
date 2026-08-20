@extends('layouts.app')
@section('title', 'Payment Collection')
@section('content')
    <div class="row">
        <div class="col-12 mb-1">
            <h4 class="content-header-title float-start">Payment Collection List</h4>

            @can('payment-collection-create')
                <button type="button" data-bs-toggle="modal" data-bs-target="#inlineModal"
                    class="btn btn-sm btn-primary float-end add-new"><i class="fa fa-plus me-25"></i>
                    {{ __('message.Add New') }}</button>
            @endcan
        </div>

        <div class="col-12">
            <div class="card p-1">
                <div class="row">
                    <div class="col-12">
                        <h3>Filter</h3>
                    </div>
                    <div class="col-sm-12 col-md-3 col-lg-2 custom-input-group">
                        <label class="form-label" for="from_date">From Date</label>
                        <input type="text" class="form-control" name="date" id="from_date" placeholder="dd-mm-yyyy">
                    </div>
                    <div class="col-sm-12 col-md-3 col-lg-2 custom-input-group">
                        <label class="form-label" for="from_date">To Date</label>
                        <input type="text" class="form-control" name="date" id="to_date" placeholder="dd-mm-yyyy">
                    </div>
                    <div class="col-sm-12 col-md-6 col-lg-2 custom-input-group">
                        <label class="form-label" for="consumer">Consumer</label>
                        <input type="text" class="form-control" name="consumer" id="consumer"
                            placeholder="Name / Mobile / Consumer Number">
                    </div>

                    <div class="col-sm-12 col-md-3 col-lg-2 custom-input-group">
                        <label class="form-label" for="status">Status</label>
                        <select class="form-select select2" name="status" id="status">
                            <option value="" selected>ALL Status</option>
                            <option value="0">Pending</option>
                            <option value="1">Approved</option>
                            <option value="2">Hold</option>
                            <option value="3">Return</option>
                        </select>
                    </div>

                    <div class="col-sm-12 col-md-3 col-lg-2 custom-input-group">
                        <label class="form-label" for="payment_type">Payment Type</label>
                        <select class="form-select select2" name="payment_types" id="payment_types">
                            <option value="" selected>ALL Type</option>
                            <option value="Cash">Cash</option>
                            <option value="Cheque">Cheque</option>
                            <option value="Disbursement">Disbursement</option>
                            <option value="NEFT">NEFT</option>
                            <option value="UPI">UPI</option>
                            <option value="RTGS">RTGS</option>
                            <option value="IMPS">IMPS</option>
                            <option value="Discount">Discount</option>
                            <option value="Adjustment">Adjustment</option>
                        </select>
                    </div>

                    <div class="col-sm-12 col-md-4 col-lg-2 custom-input-group pt-2">
                        <div class="d-flex">
                            <button class="btn btn-gradient-primary btn-sm filter" type="button" data-bs-toggle="tooltip"
                                data-placement="top" title="Click to Filter">
                                <i data-feather='search'></i>
                            </button>
                            <button class="btn btn-gradient-danger btn-sm reset ms-1" type="reset" data-bs-toggle="tooltip"
                                data-placement="top" title=" Click to Reset Filter">
                                <i data-feather='x'></i>
                            </button>
                            <button class="btn btn-gradient-success btn-sm download ms-1" type="button"
                                data-bs-toggle="tooltip" data-placement="top" title="Click to Download">
                                <i data-feather='download'></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card p-1">
                <div class="table-responsive">
                    <table id="payment_collection" class="datatables-basic table table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>{{ __('message.Action') }}</th>
                                <th>Status</th>
                                <th>Consumer Number</th>
                                <th>Consumer Details</th>
                                <!-- <th>Consumer Type</th> -->
                                <th>Amount</th>
                                <th>Payment Date</th>
                                <th>Payment Type</th>
                                <th>Credited Bank</th>
                                <th>Bank/Branch Name</th>
                                <th>Cheque/UTR/UPI</th>
                                <th>File</th>
                                <!-- <th>Created By</th>
                                    <th>Approved By</th> -->
                                <th>Remark</th>
                            </tr>
                        </thead>
                        <tbody>

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="inlineModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-transparent border-bottom">
                    <h4 class="text-center mb-0" id="exampleModalTitle">Add Payment Collection</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-1" id="body">
                    <form id="form" class="form" action="javascript:void(0);" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-12 col-md-12 mb-1 custom-input-group">
                                <label class="form-label" for="sales_master_id">Consumer<span
                                        class="text-danger">*</span></label>
                                <select class="form-control anlayst  form-select select2 custom-select2"
                                    name="sales_master_id" id="sales_master_id">
                                    <option selected disabled>{{ __('message.-- Select --') }}</option>
                                    @foreach($sales_payment_dd as $value)
                                        <option value="{{$value->id}}" data-pending_amonut="{{$value->pending_amonut}}" {{ (isset($paymetCollection) && ($paymetCollection->sales_master_id == $value->id) ? 'selected' : '')}}>{{$value->consumer_name}} {{$value->consumer_number}}
                                            {{$value->consumer_type}}
                                        </option>
                                    @endforeach
                                </select>
                                <span class="invalid-feedback d-block" id="error_sales_master_id" role="alert"></span>
                            </div>
                            <div class="col-12 col-md-6 mb-1 custom-input-group">
                                <input type="hidden" name="payment_collection_id" id="payment_collection_id" value="">
                                <label class="form-label" for="payment_type">Payment Type</label>
                                <select class="form-control  form-select select2 custom-select2" name="payment_type"
                                    id="payment_type">
                                    <option selected disabled>-- Select --</option>
                                    <option value="Cash" {{ (isset($paymetCollection) && $paymetCollection->payment_type == 'Cash') ? 'Case' : '' }}>Cash</option>
                                    <option value="Cheque" {{ (isset($paymetCollection) && $paymetCollection->payment_type == 'Cheque') ? 'selected' : '' }}>Cheque</option>

                                    <option value="Disbursement" {{ (isset($paymetCollection) && $paymetCollection->payment_type == 'Disbursement') ? 'selected' : '' }}>Disbursement
                                    </option>

                                    <option value="NEFT" {{ (isset($paymetCollection) && $paymetCollection->payment_type == 'NEFT') ? 'selected' : '' }}>NEFT</option>
                                    <option value="UPI" {{ (isset($paymetCollection) && $paymetCollection->payment_type == 'UPI') ? 'selected' : '' }}>UPI</option>
                                    <option value="RTGS" {{ (isset($paymetCollection) && $paymetCollection->payment_type == 'RTGS') ? 'selected' : '' }}>RTGS</option>
                                    <option value="IMPS" {{ (isset($paymetCollection) && $paymetCollection->payment_type == 'IMPS') ? 'selected' : '' }}>IMPS</option>
                                    <option value="Discount" {{ (isset($paymetCollection) && $paymetCollection->payment_type == 'Discount') ? 'selected' : '' }}>Discount</option>
                                    <option value="Adjustment" {{ (isset($paymetCollection) && $paymetCollection->payment_type == 'Adjustment') ? 'selected' : '' }}>Adjustment
                                    </option>
                                </select>
                                <span class="invalid-feedback d-block" id="error_payment_type" role="alert"></span>
                            </div>
                            <div class="col-12 col-md-6 mb-1 custom-input-group d-none" id="bankDropdown">
                                <label class="form-label" for="bank_id">Credited In (Bank)</label>
                                <select class="form-control form-select select2 custom-select2" name="bank_id" id="bank_id">
                                    <option selected disabled>-- Select Bank --</option>
                                    @if(isset($banks))
                                        @foreach($banks as $bank)
                                            <option value="{{ $bank->id }}">{{ $bank->name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                                <span class="invalid-feedback d-block" id="error_bank_id" role="alert"></span>
                            </div>
                            <div class="col-12 col-md-6 mb-1 custom-input-group d-none" id="upiFields">
                                <label class="form-label" for="upi_id">UPI ID</label>
                                <input type="text" class="form-control" name="upi_id" id="upi_id" placeholder="UPI ID*">
                                <span class="invalid-feedback d-block" id="error_upi_id" role="alert"></span>
                            </div>
                            <div class="col-12 col-md-6 mb-1 custom-input-group d-none" id="chequeFields">
                                <label class="form-label" for="cheque_number">Cheque Number</label>
                                <input type="text" class="form-control" name="cheque_number" id="cheque_number"
                                    placeholder="Cheque Number *">
                                <span class="invalid-feedback d-block" id="error_cheque_number" role="alert"></span>
                            </div>
                            <div class="col-12 col-md-6 mb-1 custom-input-group d-none" id="bankFields">
                                <label class="form-label" for="bank_name">Bank Name</label>
                                <input type="text" class="form-control" name="bank_name" id="bank_name"
                                    placeholder="Bank Name*">
                                <span class="invalid-feedback d-block" id="error_bank_name" role="alert"></span>
                            </div>

                            <div class="col-12 col-md-6 mb-1 custom-input-group d-none" id="branchFields">
                                <label class="form-label" for="branch_name">Branch Name</label>
                                <input type="text" class="form-control" name="branch_name" id="branch_name"
                                    placeholder="Branch Name *">
                                <span class="invalid-feedback d-block" id="error_branch_name" role="alert"></span>
                            </div>
                            <div class="col-12 col-md-6 mb-1 custom-input-group d-none" id="utrFields">
                                <label class="form-label utr-no" for="utr_number">UTR Number</label>
                                <input type="text" class="form-control" name="utr_number" id="utr_number"
                                    placeholder="UTR Number *">
                                <span class="invalid-feedback d-block" id="error_utr_number" role="alert"></span>
                            </div>
                            <div class="col-12 col-md-6 mb-1 custom-input-group " id="amountFields">
                                <label class="form-label" for="amount">Amount</label>
                                <input type="number" class="form-control pending_amonut" name="amount" id="amount" value="">
                                <span class="invalid-feedback d-block" id="error_amount" role="alert"></span>
                            </div>
                            <div class="col-12 col-md-6 mb-1 custom-input-group " id="dateFields">
                                <label class="form-label" for="payment_date">Payment Date</label>
                                <input type="text" class="form-control flatpickr-date" name="payment_date" id="payment_date"
                                    placeholder="Payment Date *">
                                <span class="invalid-feedback d-block" id="error_payment_date" role="alert"></span>
                            </div>

                            <div class="col-12 col-md-6 mb-1 custom-input-group">
                                <label class="form-label" for="file">File</label>
                                <input type="file" class="form-control" name="file" id="file">
                                <div id="file_preview" class="mt-1"></div>
                            </div>
                            <div class="col-12 col-md-6 mb-1 custom-input-group">
                                <label class="form-label" for="remark">Remark</label>
                                <input type="text" class="form-control " name="remark" id="remark"
                                    placeholder="Remark (If Any)">
                            </div>
                            <div class="col-md-12 col-12">
                                <button type="submit"
                                    class="btn btn-sm btn-primary float-end save">{{ __('message.Submit') }}</button>
                            </div>
                        </div>
                        <div class="row payment-data-div">

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
        const URL = "{{route('payment-collection.index')}}";

        $(document).on('show.bs.dropdown', '.dropdown', function () {
            var $dropdownMenu = $(this).find('.dropdown-menu');
            $('body').append($dropdownMenu.detach());
        });
        $(document).on('hide.bs.dropdown', '.dropdown', function () {
            var $dropdownMenu = $(this).find('.dropdown-menu');
            $(this).append($dropdownMenu.detach());
            $dropdownMenu.hide();
        });

        $("#from_date").flatpickr({
            altInput: true,
            altFormat: 'd-m-Y',
            dateFormat: 'Y-m-d'
        });
        $("#to_date").flatpickr({
            altInput: true,
            altFormat: 'd-m-Y',
            dateFormat: 'Y-m-d'
        });

        flatpickr('.flatpickr-date', {
            enableTime: false,
            dateFormat: 'd-m-Y',
            defaultDate: '',
        });

        $(document).on('click', '.add-new', function () {
            $('#sales_master_id').prop('disabled', false);
        });

        var table = '';
        $(function () {
            table = $('#payment_collection').DataTable({
                ajax: {
                    url: URL,
                    data: function (d) {
                        d.from_date = $('#from_date').val();
                        d.to_date = $('#to_date').val();
                        d.consumer = $('#consumer').val();
                        d.payment_type = $('#payment_types').val();
                        d.status = $('#status').val();
                    }
                },
                processing: true,
                serverSide: true,
                fixedHeader: true,
                scrollX: false,
                aLengthMenu: [
                    [20, -1],
                    [20, "All"],
                ],
                columns: [{
                    data: 'id',
                    render: function (data, type, row, meta) {
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
                    data: 'status',
                    name: 'status'
                },
                {
                    data: 'consumer_number',
                    name: 'consumer_number'
                },
                {
                    data: 'consumer_name',
                    name: 'consumer_name',
                    className: 'text-nowrap'
                },
                /* {
                     data: 'consumer_type',
                     name: 'consumer_type'
                 }, */
                {
                    data: 'amount',
                    name: 'amount',
                    className: 'text-nowrap'
                },
                {
                    data: 'payment_date',
                    name: 'payment_date',
                    className: 'text-nowrap'
                },
                {
                    data: 'payment_type',
                    name: 'payment_type'
                },
                {
                    data: 'credited_bank_name',
                    name: 'credited_bank_name',
                    defaultContent: 'N/A'
                },

                {
                    data: 'bank_name',
                    name: 'bank_name'
                },
                {
                    data: 'utr_number',
                    name: 'utr_number'
                },
                {
                    data: 'file',
                    name: 'file',
                    render: function (data, type, row) {
                        if (row.file) {
                            return '<a href="{{ asset("uploads/payment_collections") }}/' + row.file + '" target="_blank"><i data-feather="file"></i> View</a>';
                        }
                        return 'N/A';
                    }
                },
                // {
                //     data: 'date',
                //     render: function (data, type, row) {
                //         return (row.creator) ? row.creator.name : '-';
                //     }
                // },
                // {
                //     data: 'approved_by',
                //     render: function (data, type, row) {
                //         return (row.updater) ? row.updater.name : '-';
                //     }
                // },
                {
                    data: 'remark',
                    name: 'remark'
                }

                ],
                initComplete: function (settings, json) {
                    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                        return new bootstrap.Tooltip(tooltipTriggerEl)
                    })
                }
            });
        });

        $(document).on('click', '.save', function () {
            $('#sales_master_id').prop('disabled', false);
            var formData = new FormData($("#form")[0]);
            if ($("#payment_type").val() != "") {
                $.ajax({
                    type: "POST",
                    url: "{{route('payment-collection.store')}}",
                    data: formData,
                    dataType: 'json',
                    cache: false,
                    contentType: false,
                    processData: false,
                    beforeSend: function () {
                        $("#error_name").html(' ');
                        $(".save").html(`<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> {{ __('message.Wait') }}`);
                        $(".save").attr('disabled', true);
                    },
                    success: function (response) {
                        $('#sales_master_id').prop('disabled', true);
                        $(".save").html("{{ __('message.Submit') }}");
                        $(".save").attr('disabled', false);
                        if (response.server_error && response.status == false) {
                            toastr.error("{{ __('message.Something went wrong. Please try again.') }}", "{{ __('message.Error') }}");
                        } else if (response.status == false) {
                            $.each(response.errors, function (key, value) {
                                $('#error_' + key).html('<p class="text-danger mb-0">' + value + '</p>');
                            });
                            toastr.warning(response.message, "{{ __('message.Warning') }}");
                        } else {
                            $('#form')[0].reset();
                            toastr.success(response.message, "{{ __('message.Success') }}");
                            setTimeout(function () {
                                location.href = "{{ route('payment-collection.index') }}";
                            }, 1000);
                        }
                    }
                });
            } else {
                $("#form").validate({
                    rules: {
                        payment_type: {
                            required: true,
                        },
                    },
                    messages: {
                        payment_type: {
                            required: "payment Type"
                        },
                    },
                    errorElement: "p",
                    errorClass: "text-danger mb-0",

                    highlight: function (element) {
                        $(element).addClass('has-error');
                    },
                    unhighlight: function (element) {
                        $(element).removeClass('has-error');
                    },
                    errorPlacement: function (error, element) {
                        $(element).closest('.custom-input-group').append(error);
                    }
                });
            }
        });

        $("#inlineModal").on("hidden.bs.modal", function (e) {
            $(this).find('form').trigger('reset');
            $("#payment_collection_id").val("");
            $("#name-error").html("");
            $("#exampleModalTitle").html("Add Payment Collection");
            $("#file_preview").html("");
            $('#bank_id').val(null).trigger('change');
        });

        $(document).on('click', '.edit', function () {
            var id = $(this).data('id');
            var url = "{{route('payment-collection.edit', 'id')}}".replace('id', id);
            $.ajax({
                type: "GET",
                url: url,
                dataType: 'json',
                cache: false,
                contentType: false,
                processData: false,
                beforeSend: function () { },
                success: function (data) {
                    if (data.msg_type == "success") {

                        $('#sales_master_id').prop('disabled', true);
                        $("#exampleModalTitle").html("Edit Payment Collection");
                        $("#sales_master_id").val(data.result.sales_master_id).trigger('change');
                        $("#payment_type").val(data.result.payment_type).trigger('change');
                        $("#amount").val(data.result.amount);
                        $("#payment_date").val(data.result.payment_date);
                        $("#cheque_number").val(data.result.cheque_number);
                        $("#bank_name").val(data.result.bank_name);
                        $("#branch_name").val(data.result.branch_name);
                        $("#utr_number").val(data.result.utr_number);
                        $("#upi_id").val(data.result.upi_id);
                        $("#remark").val(data.result.remark);
                        $("#payment_collection_id").val(id);
                        $("#bank_id").val(data.result.bank_id).trigger('change');

                        if (data.result.file_url) {
                            $("#file_preview").html('<a href="' + data.result.file_url + '" target="_blank">View File</a>');
                        } else {
                            $("#file_preview").html('');
                        }

                        flatpickr('.flatpickr-date', {
                            enableTime: false,
                            dateFormat: 'd-m-Y',
                            defaultDate: '',
                        });

                        $("#inlineModal").modal('show');
                    } else {
                        swal(data.msg_content, {
                            icon: "error",
                        });
                    }
                }
            });
        });

        $(document).on('click', '.delete', function () {
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
                .then(function (result) {
                    if (result.value) {
                        axios.delete(URL + '/' + id)
                            .then(function (response) {
                                if (response.data.status == true) {
                                    table.ajax.reload(null, false);
                                    toastr.success("{{ __('message.Deleted successfully.') }}", "{{ __('message.Success') }}");
                                } else if (response.data.status == false && response.data.server_error) {
                                    toastr.error("{{ __('message.Something went wrong. Please try again.') }}", "{{ __('message.Error') }}");
                                } else {
                                    toastr.warning("{{ __('message.This Payment Collection has been used.') }}", "{{ __('message.Warning') }}");
                                }
                            })
                            .catch(function () {
                                toastr.error("{{ __('message.Something went wrong. Please try again.') }}", "{{ __('message.Error') }}");
                            });
                    } else {
                        Swal.fire({
                            text: "{{ __('message.Your data is safe.') }}"
                        });
                    }
                });
        });

        $(document).ready(function () {
            $('#sales_master_id').change(function () {
                var selectedOption = $(this).find('option:selected');
                var pending_amonut = selectedOption.data('pending_amonut');
                $('#amount').val(pending_amonut);

                var url = "{{route('sales-order-payment', 'id')}}".replace('id', $(this).val());
                $.ajax({
                    url: url,
                    type: 'post',
                    datatype: 'json',
                    data: {
                        "_token": "{{ csrf_token() }}",
                    },
                    success: function (response) {

                        $(".payment-data-div").html(response.html);
                        $('[data-bs-toggle="tooltip"]').tooltip();
                        if (feather) {
                            feather.replace({
                                width: 14,
                                height: 14
                            });
                        }
                    }
                });

            });

        });

        $(document).on('change', '#payment_type', function () {
            var paymentType = $(this).val();
            if (paymentType == 'Cheque') {
                $('#chequeFields').removeClass('d-none');
                $('#bankDropdown').removeClass('d-none');
                $('#bankFields').removeClass('d-none'); // Hide manual bank name
                $('#branchFields').removeClass('d-none');
                $('#upiFields').addClass('d-none');
                $('#utrFields').addClass('d-none');
                $('#upi_id').val('');
                $('#utr_number').val('');
            } else if (paymentType == 'UPI') {
                $('#chequeFields').addClass('d-none');
                $('#bankFields').addClass('d-none');
                $('#bankDropdown').removeClass('d-none');
                $('#branchFields').addClass('d-none');
                $('#cheque_number').val('');
                $('#bank_name').val('');
                $('#branch_name').val('');
                $('#upiFields').removeClass('d-none');
                $('#utrFields').addClass('d-none');
                $('#utr_number').val('');
            } else if (paymentType == 'Cash' || paymentType == 'Discount' || paymentType == 'Adjustment') {
                $('#chequeFields').addClass('d-none');
                $('#bankFields').addClass('d-none');
                $('#bankDropdown').addClass('d-none');
                $('#branchFields').addClass('d-none');
                $('#upiFields').addClass('d-none');
                $('#utrFields').addClass('d-none');
                $('#cheque_number').val('');
                $('#bank_name').val('');
                $('#branch_name').val('');
                $('#upi_id').val('');
                $('#utr_number').val('');

            } else if (paymentType == 'NEFT') {
                $('#chequeFields').addClass('d-none');
                $('#cheque_number').val('');
                $('#bankFields').removeClass('d-none');
                $('#bankDropdown').removeClass('d-none');
                $('#branchFields').removeClass('d-none');
                $('#upiFields').addClass('d-none');
                $('#upi_id').val('');
                $('#utrFields').removeClass('d-none');
            } else if (paymentType == 'RTGS' || paymentType == 'IMPS' || paymentType == 'Disbursement') {
                if (paymentType == 'IMPS') {
                    $('.utr-no').html('IMPS Ref. No.');
                    $('#utr_number').attr('placeholder', 'IMPS Reference Number *');
                } else {
                    $('.utr-no').html('UTR Number');
                    $('#utr_number').attr('placeholder', 'UTR Number *');
                }
                $('#chequeFields').addClass('d-none');
                $('#cheque_number').val('');
                $('#bankFields').removeClass('d-none');
                $('#bankDropdown').removeClass('d-none');
                $('#branchFields').removeClass('d-none');
                $('#upiFields').addClass('d-none');
                $('#upi_id').val('');
                $('#utrFields').removeClass('d-none');
            }
        });

        $(document).on('click', '.change-status', function () {
            let status = $(this).data('status');
            let id = $(this).data('id');
            var url = "{{route('change-payment-status')}}";
            Swal.fire({
                title: "{{ __('message.Are you sure?') }}",
                text: "{{ __('message.You won`t be able to revert this!') }}",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: "Yes, Change it!",
                customClass: {
                    confirmButton: 'btn btn-primary',
                    cancelButton: 'btn btn-outline-danger ms-1'
                },
                buttonsStyling: false
            })
                .then(function (result) {
                    if (result.value) {
                        $.ajax({
                            url: url,
                            type: 'POST',
                            datatype: 'json',
                            data: {
                                "id": id,
                                "status": status,
                                "_token": "{{ csrf_token() }}",
                            },
                            success: function (response) {
                                if (response.status) {
                                    table.ajax.reload(null, false);
                                    toastr.success(response.message, 'Success');
                                } else {
                                    toastr.error(response.server_error, 'Opps!');

                                }
                            }
                        });
                    } else {
                        Swal.fire({
                            text: "{{ __('message.Your data is safe.') }}"
                        });
                    }
                });
        });

        $(document).on('click', '.filter', function () {
            table.draw();
        });
        $(document).on('click', '.download', function () {
            $.ajax({
                url: "{{route('payment-report')}}",
                type: 'POST',
                datatype: 'json',
                data: {
                    "from_date": $('#from_date').val(),
                    "to_date": $('#to_date').val(),
                    "consumer": $('#consumer').val(),
                    "payment_type": $('#payment_types').val(),
                    "status": $('#status').val(),
                    "_token": "{{ csrf_token() }}",
                },
                cache: false,
                xhr: function () {
                    var xhr = new XMLHttpRequest();
                    xhr.onreadystatechange = function () {
                        if (xhr.readyState == 2) {
                            if (xhr.status == 200) {
                                xhr.responseType = "blob";
                            } else {
                                xhr.responseType = "text";
                            }
                        }
                    };
                    return xhr;
                },
                success: function (data) {
                    var blob = new Blob([data], {
                        type: "application/octetstream"
                    });
                    var fileName = 'Cheque_Colletion.xlsx';
                    var isIE = false || !!document.documentMode;
                    if (isIE) {
                        window.navigator.msSaveBlob(blob, fileName);
                    } else {
                        var url = window.URL || window.webkitURL;
                        var link = url.createObjectURL(blob);
                        var a = $("<a />");
                        a.attr("download", fileName);
                        a.attr("href", link);
                        $("body").append(a);
                        a[0].click();
                        $("body").remove(a);
                    }
                }
            });

        });
        $(document).on('click', '.reset', function () {
            $('#from_date').val('');
            $('#to_date').val('');
            $('#consumer').val('');
            $("#from_date").flatpickr({
                altInput: true,
                altFormat: 'd-m-Y',
                dateFormat: 'Y-m-d'
            });
            $("#to_date").flatpickr({
                altInput: true,
                altFormat: 'd-m-Y',
                dateFormat: 'Y-m-d'
            });
            $('#payment_types').val('');
            $('#status').val('');
            $('.select2').select2();
            table.draw();
        });
    </script>
@endsection
