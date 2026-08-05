@extends('layouts.app')
@section('title', 'Project Wise Stock Transfer')
@section('content')
<div class="row">
    <div class="col-12 mb-50">
        <h4 class="content-header-title float-start">Project Wise Stock Transfer</h4>
        @can('project-wise-stock-list')
        <a href="{{ route('project-wise-stock.index') }}" class="btn btn-sm btn-primary float-end"><i class="fa fa-angle-double-left me-25"></i> List</a>
        @endcan
    </div>
    <div class="col-12">
        <div class="card p-1">
            <form id="form" action="javascript:void(0);" method="POST">
                @csrf
                <div class="row">
                    <div class="col-12 col-md-6 col-lg-4 mb-1 custom-input-group">
                        <label class="form-label" for="delivery_challan_id">Delivery Challan <span class="text-danger">*</span></label>
                        <select class="form-select select2" name="delivery_challan_id" id="delivery_challan_id">
                            <option selected disabled>{{ __('message.-- Select --') }}</option>
                            @foreach($deliveryChallan as $value)
                            <option value="{{ $value->id }}">{{ $value->challan_number }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 my-25 d-none" id="table-col">
                        <table class="table table-sm table-bordered w-auto">
                            <thead>
                                <tr>
                                    <th class="text-center"> #</th>
                                    <th>Item</th>
                                    <th>Stock</th>
                                    <th>Transfer Qty. <span class="text-danger">*</span></th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody class="sub_data"></tbody>
                        </table>
                    </div>

                    <div class="col-12 col-md-12 col-lg-12 mb-1 custom-input-group">
                        <label class="form-label" for="remark">Remark <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="remark" id="remark" placeholder="Type here.."></textarea>
                        <span class="invalid-feedback d-block" id="error_remark" role="alert"></span>
                    </div>

                    <div class="col-md-12 col-12">
                        <button type="submit" class="btn btn-sm btn-primary float-end save">Submit</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('pagescript')
<script type="application/javascript">
    $(document).on('change', '#delivery_challan_id', function() {
        $('#table-col').addClass('d-none');
        var id = $(this).val();
        if (id != "") {
            var url = "{{route('get-project-stock')}}";
            $.ajax({
                type: "GET",
                url: url,
                data: {
                    "id": id,
                    "_token": "{{ csrf_token() }}",
                },
                dataType: 'json',
                success: function(response) {
                    if (response.status_code == 403) {
                        $('#table-col').addClass('d-none');
                        toastr.warning(response.message, "Warning");
                    } else {
                        $('#table-col').removeClass('d-none');
                        $(".sub_data").html('');
                        $(".sub_data").html(response.html);
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
    });

    $(document).on('click', '.remove-item', function() {
        if (($('.remove-item').length) > 1) {
            $(this).parent().parent().remove();
            setTimeout(function() {
                updateSerialNumbers();
            }, 200);
        } else {
            Swal.fire({
                text: "Can`t delete first item",
                icon: 'warning',
                confirmButtonText: 'OK',
            });
        }
    });

    function updateSerialNumbers() {
        $('.sr_no').each(function(index) {
            $(this).text(index + 1);
        });
    }

    $(document).on('input', '.quantity', function() {
        var row = $(this).closest('tr');
        calculateTotal(row);
    });

    function calculateTotal(row) {
        var quantity = parseFloat(row.find('.quantity').val()) || 0;
        var stock = parseFloat(row.find('.stock-find').val()) || 0;
        if (quantity > stock) {
            toastr.warning("Quantity cannot be greater than stock!", "Warning");
            row.find('.quantity').val(stock);
            quantity = stock;
        }

        var finalStock = stock - quantity;
        row.find('.stock').val(finalStock);
    }

    document.querySelectorAll('.number').forEach(function(input) {
        input.addEventListener('input', function(e) {
            this.value = this.value.replace(/\D/g, '').slice(0, 10);
            if (this.value.charAt(0) === '0') {
                this.value = this.value.substring(1);
            }
        });
    });

    $("#form").validate({
        rules: {
            delivery_challan_id: {
                required: true,
            },
            remark: {
                required: true,
            },
            "quantity[]": {
                required: true,
            },
        },
        messages: {
            delivery_challan_id: {
                required: "Select delivery challan",
            },
            "quantity[]": {
                required: "Enter quantity",
            },
            remark: {
                required: "Enter remark",
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
        var formData = new FormData($("#form")[0]);
        if ($("#form").valid()) {
            $.ajax({
                type: "POST",
                url: "{{route('project-wise-stock.store')}}",
                data: formData,
                dataType: 'json',
                cache: false,
                contentType: false,
                processData: false,
                beforeSend: function() {
                    $(".save").html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Wait');
                    $(".save").attr('disabled', true);
                },
                success: function(response) {
                    $(".save").html('Submit');
                    $(".save").attr('disabled', false);
                    if (response.status_code == 201) {
                        $.each(response.errors, function(key, value) {
                            toastr.error(value, 'Error');
                            // $('#error_' + key).html('<p class="text-danger mb-0">' + value + '</p>');
                        });
                        //toastr.warning('Please Input Propper Data.', 'Warning');
                    } else if (response.status_code == 500) {
                        toastr.error(response.message, 'Error');
                    } else {
                        $('#form')[0].reset();
                        toastr.success(response.message, 'Success');
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
</script>
@endsection