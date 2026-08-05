@extends('layouts.app')
@section('title', 'Commission List')
@section('content')
    <div class="row">
        <div class="col-12 mb-1">
            <h4 class="content-header-title float-start">Commission List</h4>
        </div>
        <div class="col-12">
            <div class="card p-1">
                <div class="row">
                    <div class="col-12">
                        <h3>Filter</h3>
                    </div>
                    <div class="col-sm-12 col-md-3 col-lg-2 custom-input-group">
                        <label class="form-label" for="from_date">From Date</label>
                        <input type="text" class="form-control flatpickr-date" id="from_date" readonly>
                    </div>
                    <div class="col-sm-12 col-md-3 col-lg-2 custom-input-group">
                        <label class="form-label" for="to_date">To Date</label>
                        <input type="text" class="form-control flatpickr-date" id="to_date" readonly>
                    </div>
                    <div class="col-sm-12 col-md-6 col-lg-3 custom-input-group">
                        <label class="form-label" for="agent_id">Agent / Sales Person</label>
                        <select class="form-select select2" id="agent_id">
                            <option value="" selected>-- Select --</option>
                            @foreach ($agents ?? [] as $ag)
                                <option value="{{ $ag->id }}">{{ $ag->name ?? '' }} {{ $ag->last_name ?? '' }}
                                </option>
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
            <div class="card p-1">
                <table id="commission_list" class="datatables-basic table table hover">
                    <thead>
                        <tr>
                            <th>SR</th>
                            <th>AGENT SALES PERSON</th>
                            <th>NO OF FILE</th>
                            <th>KW</th>
                            <th>comm\'n</th>
                            <th>SUB comm\'n</th>
                            <th>install</th>
                            <th>T. PAYABALE</th>
                            <th>T. PAID</th>
                            <th>PENING PAYOUT</th>
                            <th>CUST. PENDING</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('pagescript')
    <script type="application/javascript">
    'use strict';

    const URL = "{{ route('commission-list.index') }}";
    $(function() {
          flatpickr('.flatpickr-date', {
            enableTime: false,
            dateFormat: 'd-m-Y',
            defaultDate: '',
            maxDate: new Date().fp_incr(0)
        });
        $('#commission_list').DataTable({
            ajax: {
                url: URL,
                data: function(d) {
                    d.from_date = $('#from_date').val();
                    d.to_date = $('#to_date').val();
                    d.agent_sales_person_id = $('#agent_id').val();
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
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'agent_name', name: 'users.name', },
                { data: 'no_of_file', name: 'no_of_file' },
                { data: 'kw', name: 'kw' },
                { data: 'commission', name: 'commission' },
                { data: 'sub_commission', name: 'sub_commission' },
                { data: 'installation', name: 'installation' },
                { data: 'total_payable', name: 'total_payable' },
                { data: 'total_paid', name: 'total_paid' },
                { data: 'pending_payout', name: 'pending_payout' },
                { data: 'customer_payment_pending', name: 'customer_payment_pending' }
            ]
        });
    });

    $(document).on('click', '.filter', function() {
        $('#commission_list').DataTable().draw();
    });
    $(document).on('click', '.download', function() {
        var fromDate = $('#from_date').val();
        var toDate = $('#to_date').val();
        var agentId = $('#agent_id').val();

        var url = "{{ route('commission-list.excel') }}";
        var params = [];

        if (fromDate) {
            params.push('from_date=' + encodeURIComponent(fromDate));
        }
        if (toDate) {
            params.push('to_date=' + encodeURIComponent(toDate));
        }
        if (agentId) {
            params.push('agent_sales_person_id=' + encodeURIComponent(agentId));
        }

        if (params.length > 0) {
            url += '?' + params.join('&');
        }

         window.open(url, '_blank');
    });
    $(document).on('click', '.reset', function() {
        $('#from_date').val('');
        $('#to_date').val('');
        $('#agent_id').val('');
        $('#commission_list').DataTable().draw();
    });
</script>
@endsection
