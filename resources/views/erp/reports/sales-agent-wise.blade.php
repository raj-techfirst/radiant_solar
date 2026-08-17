@extends('layouts.app')
@section('title', 'Sales Agent Wise Report')
@section('content')
<div class="row">
    <div class="col-12 mb-1">
        <h4 class="content-header-title float-start">Sales Agent Wise Report</h4>
    </div>
    <div class="col-12 mt-2">
        <div class="card p-1">
            <div class="row align-items-end">
                <div class="col-12">
                    <h3>Filter</h3>
                </div>
                <div class="col-sm-12 col-md-3 col-lg-3 pe-50 custom-input-group">
                    <label class="form-label" for="from">From Month</label>
                    <input type="month" class="form-control" name="from" id="from" value="{{ old('from', $from) }}">
                </div>
                <div class="col-sm-12 col-md-3 col-lg-3 ps-50 custom-input-group">
                    <label class="form-label" for="to">To Month</label>
                    <input type="month" class="form-control" name="to" id="to" value="{{ old('to', $to) }}">
                </div>
                <div class="col-sm-12 col-md-4 col-lg-3 ps-50 custom-input-group pt-1">
                    <div class="d-flex justify-content-start">
                        <button class="btn btn-gradient-primary btn-sm filter" type="button" data-bs-toggle="tooltip" data-placement="top" title="Click to Filter">
                            <i data-feather='search'></i>
                        </button>
                        <button class="btn btn-gradient-danger btn-sm reset ms-50" type="reset" data-bs-toggle="tooltip" data-placement="top" title=" Click to Reset Filter">
                            <i data-feather='x'></i>
                        </button>
                        <button class="btn btn-gradient-success btn-sm download ms-50" type="button" data-bs-toggle="tooltip" data-placement="top" title="Click to Download Excel">
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
                <table class="table table-bordered table-striped text-center align-middle">
                    <thead class="table-light">
                        <tr>
                            <th rowspan="2" class="align-middle">Months</th>
                            @foreach($agents as $agent)
                            <th>{{ $agent['name'] }}</th>
                            @endforeach
                            <th rowspan="2" class="align-middle">Total KW</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($months as $month)
                        <tr>
                            <td class="fw-bolder">{{ $month }}</td>
                            @php $rowTotal = 0; @endphp
                            @foreach($agents as $agent)
                            @php
                                $key = $month . '|' . $agent['id'];
                                $val = isset($data[$key]) ? $data[$key] : 0;
                                $rowTotal += $val;
                            @endphp
                            <td>{{ number_format($val, 3) }}</td>
                            @endforeach
                            <td class="fw-bolder">{{ number_format($rowTotal, 3) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-light fw-bolder">
                        <tr>
                            <td>Total</td>
                            @php $grandTotal = 0; @endphp
                            @foreach($agents as $agent)
                            @php
                                $colTotal = 0;
                                foreach($months as $month) {
                                    $key = $month . '|' . $agent['id'];
                                    $colTotal += isset($data[$key]) ? $data[$key] : 0;
                                }
                                $grandTotal += $colTotal;
                            @endphp
                            <td>{{ number_format($colTotal, 3) }}</td>
                            @endforeach
                            <td>{{ number_format($grandTotal, 3) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection

@section('pagescript')
<script type="text/javascript">
    'use strict';
    const URL = "{{ route('sales-agent-wise') }}";
    $(function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        })
    });
    $(document).on('click', '.filter', function() {
        let from = $('#from').val();
        let to = $('#to').val();
        if (!from || !to) {
            toastr.error('Please select From and To Month');
            return;
        }
        if (from > to) {
            toastr.error('From Month cannot be greater than To Month');
            return;
        }
        window.location.href = URL + "?from=" + from + "&to=" + to;
    });
    $(document).on('click', '.download', function() {
        let from = $('#from').val();
        let to = $('#to').val();
        if (!from || !to) {
            toastr.error('Please select From and To Month');
            return;
        }
        if (from > to) {
            toastr.error('From Month cannot be greater than To Month');
            return;
        }
        window.location.href = URL + "?from=" + from + "&to=" + to + "&download=excel";
    });
    $(document).on('click', '.reset', function() {
        location.href = URL;
    });
</script>

@endsection