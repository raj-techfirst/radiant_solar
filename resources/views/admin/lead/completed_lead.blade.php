@extends('layouts.app')
@section('title', 'Lead Won')
@section('content')
    <div class="row">
        <div class="col-12 mb-1">
            <h4 class="content-header-title float-start">Lead Won</h4>
        </div>

        <div class="col-12">
            <div class="card p-1">
                <div class="row">
                    <div class="col-12">
                        <h3>Filter</h3>
                    </div>
                    <div class="col-sm-12 col-md-3 col-lg-2 custom-input-group">
                        <label class="form-label" for="from_date">From Date</label>
                        <input type="text" class="form-control flatpickr-date" name="date" id="from_date"
                            placeholder="dd-mm-yyyy">
                    </div>
                    <div class="col-sm-12 col-md-3 col-lg-2 custom-input-group">
                        <label class="form-label" for="to_date">To Date</label>
                        <input type="text" class="form-control flatpickr-date" name="date" id="to_date"
                            placeholder="dd-mm-yyyy">
                    </div>
                    <div class="col-sm-12 col-md-6 col-lg-2 custom-input-group">
                        <label class="form-label" for="consumer">Name / Mobile</label>
                        <input type="text" class="form-control" name="consumer" id="consumer"
                            placeholder="Name / Mobile">
                    </div>

                    <div class="col-sm-12 col-md-3 col-lg-2 custom-input-group">
                        <label class="form-label" for="status">Agent / Sales Person</label>
                        <select class="form-select select2" name="assign" id="assign">
                            <option value="" selected>ALL</option>
                            @foreach ($agentSalesPerson as $value)
                                <option value="{{ $value->id }}"
                                    {{ isset($lead) && $lead->agent_sales_person_id == $value->id ? 'selected' : '' }}>
                                    {{ $value->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-sm-12 col-md-4 col-lg-2 custom-input-group pt-2">
                        <div class="d-flex">
                            <button class="btn btn-gradient-primary btn-sm filter" type="button" data-bs-toggle="tooltip"
                                data-placement="top" title="Click to Filter">
                                <i data-feather='search'></i>
                            </button>
                            <button class="btn btn-gradient-danger btn-sm reset ms-1" type="reset"
                                data-bs-toggle="tooltip" data-placement="top" title=" Click to Reset Filter">
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
            <div class="card p-1 mb-0">
                <table id="table" class="datatables-basic table table-hover">
                    <thead>
                        <tr>
                             <th>#</th>
                            <th>{{ __('message.Action') }}</th>
                            <th>{{ __('message.Status') }}</th>
                            <th>{{ __('message.Name') }}</th>
                            <th>{{ __('message.Mobile') }}</th>
                            <th>{{ __('message.KW') }}</th>
                            <th>{{ __('message.Agent / Sales Person') }}</th>
                        </tr>
                    </thead>
                    <tbody>

                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('pagescript')
    <script type="text/javascript">
        'use strict';
        const URL = "{{ route('lead-complet') }}";
        var table = '';
        $(function() {
            flatpickr('.flatpickr-date', {
                enableTime: false,
                dateFormat: 'd-m-Y',
                defaultDate: '',
            });
            table = $('#table').DataTable({
                processing: true,
                serverSide: true,
                fixedHeader: true,
                scrollX: true,
                aLengthMenu: [
                    [20, -1],
                    [20, "All"],
                ],
                ajax: {
                    url: URL,
                    data: function(d) {
                        d.mstatus = '1';
                        d.consumer = $('#consumer').val();
                        d.status = $('#status').val();
                        d.assign = $('#assign').val();
                        d.from_date = $('#from_date').val();
                        d.to_date = $('#to_date').val();
                    }
                },
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
                        data: 'status',
                        name: 'status'
                    },

                    {
                        data: 'name',
                        name: 'name'
                    },

                    {
                        data: 'mobile',
                        name: 'mobile'
                    },

                    {
                        data: 'kw',
                        name: 'kw'
                    },


                    {
                        data: 'agent_sales_person_id',
                        name: 'agent_sales_person_id'
                    },
                ],
                initComplete: function(settings, json) {
                    var tooltipTriggerList = [].slice.call(document.querySelectorAll(
                        '[data-bs-toggle="tooltip"]'));
                    var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                        return new bootstrap.Tooltip(tooltipTriggerEl)
                    })
                }
            });
        });

        $(document).on('click', '.status', function() {
            let status = $(this).data('value');
            let id = $(this).data('id');
            var route = "{{ route('lead.show', 'id') }}".replace('id', id);
            $.ajax({
                type: "get",
                url: route,
                dataType: 'json',
                data: {
                    "_token": "{{ csrf_token() }}",
                    "id": id,
                    "status": status,
                },
                success: function(response) {
                    if (response.status == true) {
                        table.ajax.reload(null, false);
                        toastr.success("{{ __('message.Status updated successfully.') }}",
                            "{{ __('message.Success') }}");
                    } else {
                        toastr.error("{{ __('message.Something wen`t wrong. Please try again.') }}",
                            "{{ __('message.Error') }}");
                    }
                },
                error: function(error) {
                    toastr.error("{{ __('message.Something went wrong. Please try again.') }}",
                        "{{ __('message.Error') }}");
                    $(document.body).css('pointer-events', '');
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
                                    toastr.success("{{ __('message.Deleted successfully.') }}",
                                        "{{ __('message.Success') }}");
                                } else {
                                    toastr.error(
                                        "{{ __('message.Something went wrong. Please try again.') }}",
                                        "{{ __('message.Error') }}");
                                }
                            })
                            .catch(function() {
                                toastr.error("{{ __('message.Something went wrong. Please try again.') }}",
                                    "{{ __('message.Error') }}");
                            });
                    } else {
                        Swal.fire({
                            text: "{{ __('message.Your data is safe.') }}"
                        });
                    }
                });
        });

        $(document).ready(function() {
            $(".date").datepicker({
                dateFormat: 'dd-mm-yy',
            });
        });

        $(document).on('click', '.lead-report', function() {
            table.draw();
        });

        $(document).on('click', '.lead-report-export', function() {
            $('#lead_report').attr('action', "{{ route('export') }}");
            $('form#lead_report').submit();
        });

        $(document).on('click', '.referral_share', function() {
            var name = $(this).data('name');
            var link = 'https://api.whatsapp.com/send?phone=' + mbl;
            var mbl = $(this).data('mobile');
            var country_code = +91;
            var text = "Hey! " + name + " your lead added successfully.";
            var url = 'https://api.whatsapp.com/send?phone=' + country_code + ' ' + mbl + '&text=' + text;

            window.open(url, '_blank');

        });

        $(document).on('click', '.filter', function() {
            table.draw();
        });

        $(document).on('click', '.reset', function() {
            $('#from_date').val('');
            $('#to_date').val('');
            $('#status').val('');
            $('#consumer').val('');
            $('#assign').val('');

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

            $('.select2').select2();
            table.draw();
        });


        $(document).on('click', '.download', function() {
            $.ajax({
                url: "{{ route('export') }}",
                type: 'POST',
                datatype: 'json',
                data: {
                    "mstatus": '1',
                    "from_date": $('#from_date').val(),
                    "to_date": $('#to_date').val(),
                    "consumer": $('#consumer').val(),
                    "assign": $('#assign').val(),
                    "status": $('#status').val(),
                    "_token": "{{ csrf_token() }}",
                },
                cache: false,
                xhr: function() {
                    var xhr = new XMLHttpRequest();
                    xhr.onreadystatechange = function() {
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
                success: function(data) {
                    var blob = new Blob([data], {
                        type: "application/octetstream"
                    });
                    var fileName = 'Lead_Report.xlsx';
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
    </script>
@endsection
