@extends('layouts.app')
@section('title', 'Dashboard')
@section('content')
    <link rel="stylesheet" href="{{ asset('app-assets/vendors/apex-charts/apex-charts.css') }}" />

    <style>
        body {
            position: relative;
        }

        body::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: url("{{ asset('app-assets/images/sun-energy.svg') }}");
            background-repeat: no-repeat;
            background-size: 50%;
            filter: grayscale(1);
            background-position: center;
            opacity: 0.3;
            /* Adjust the opacity here */
            z-index: -1;
        }

        .table>:not(caption)>*>* {
            padding: .5rem !important;
        }
    </style>
    <div class="content-wrapper container-xxl p-0 pt-2">
        <div class="content-body">
            @if (session()->get('soft') == 'crm')
                <div class="row"> <!-- match-height -->
                    <div class="col-12 col-xl-6">
                        <div class="row">
                            @if ($todayFollowUp > 0)
                                <div class="col-lg-6 col-sm-6 col-12">
                                    <div class="card">
                                        <a href="{{ url('lead?type=today-followups') }}">
                                            <div class="card-header">
                                                <div>
                                                    <h2 class="font-weight-bolder mb-0">{{ $todayFollowUp }}</h2>
                                                    <p class="card-text">Today's Follow ups</p>
                                                </div>
                                                <div class="avatar bg-light-success p-50 m-0">
                                                    <div class="avatar-content">
                                                        <i data-feather='phone-call'></i>
                                                    </div>
                                                </div>

                                            </div>
                                        </a>
                                    </div>
                                </div>
                            @endif
                            @if ($todayFollowUp > 0)
                                <div class="col-lg-6 col-sm-6 col-12">
                                    <div class="card">
                                        <a href="{{ url('lead?type=site-visit') }}">
                                            <div class="card-header">
                                                <div>
                                                    <h2 class="font-weight-bolder mb-0">{{ $sitevisit }}</h2>
                                                    <p class="card-text">Total Site Visit</p>
                                                </div>
                                                <div class="avatar bg-light-success p-50 m-0">
                                                    <div class="avatar-content">
                                                        <i data-feather='user-check'></i>
                                                    </div>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                </div>
                            @endif
                            @if (isset($salesData['forPortal']) && count($salesData['forPortal']) > 0)
                                @foreach ($salesData['forPortal'] as $key => $value)
                                    <div class="col-lg-4 col-md-6 col-12">
                                        <a href="{{ route('sales-master.index', 'type=portal&value=' . $key) }}">
                                            <div class="card  border cursor-pointer">
                                                <div class="card-body">
                                                    <div class="d-flex justify-content-between">
                                                        <img src='{{ asset('img/icon/' . $key . '.png') }}'>
                                                        <span
                                                            class="text-truncate fw-bolder h4">{{ number_format($value['count'], 0) }}</span>
                                                    </div>
                                                    <div class="my-1 mb-0 d-flex justify-content-between">
                                                        <span
                                                            class="text-truncate fw-bolder h4">{{ number_format($value['register_kw'], 3) }}
                                                            <small>KW</small></span>
                                                    </div>
                                                    <div class=" mb-50">
                                                        <h5>{{ $key }}</h5>
                                                    </div>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                @endforeach
                            @endif
                            @if (isset($salesData['forstatus']) && count($salesData['forstatus']) > 0)
                                <div class="col-12 col-xl-12">
                                    <div class="card card-statistics p-0">
                                        <div class="card-header">
                                            <h4 class="card-title">Sales Order Status Wise</h4>
                                            <div class="d-flex align-items-center">
                                                <p class="card-text font-small-2 me-25 mb-0"></p>
                                            </div>
                                        </div>
                                        <div class="card-body statistics-body" style="padding: 0 !important;">
                                            <table class="table table-hover table-bordered">
                                                <thead>
                                                    <tr>
                                                        <th class="text-center">Sr.No</th>
                                                        <th class="w-50">Application Status</th>
                                                        <th class="text-center">Nos of File</th>
                                                        <th class="text-center">Total KW</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @php $i= 0; @endphp
                                                    @foreach ($salesData['forstatus'] as $key => $value)
                                                        @if ($i == 0)
                                                            <tr>
                                                                <th class="text-center">#</th>
                                                                <th><a
                                                                        href="{{ route('sales-master.index') }}">{{ $value['name'] }}</a>
                                                                </th>
                                                                <th class="text-center">{{ $value['count'] }}</th>
                                                                <th class="text-end">
                                                                    {{ number_format($value['register_kw'], 3) }}</th>
                                                            </tr>
                                                        @else
                                                            <tr>
                                                                <td class="text-center">{{ $i }}</td>
                                                                <td>
                                                                    @if (!empty($value['status']))
                                                                        <a
                                                                            href="{{ route('sales-master.index', 'type=status&value=' . $value['status'] . '&value_not=' . $value['status_not']) }}">{{ $value['name'] }}</a>
                                                                    @else
                                                                        {{ $value['name'] }}
                                                                    @endif
                                                                </td>
                                                                <td class="text-center">{{ $value['count'] }}</td>
                                                                <td class="text-end">
                                                                    {{ number_format($value['register_kw'], 3) }}</td>
                                                            </tr>
                                                        @endif
                                                        @if ($i == 4)
                                                            @php $i++; @endphp
                                                        @endif
                                                        @php $i++; @endphp
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                    @if (isset($salesData['foragent']) && count($salesData['foragent']) > 0)
                        <div class="col-12 col-xl-6">
                            <div class="card card-statistics  p-0">
                                <div class="card-header">
                                    <h4 class="card-title">Sales Order Agent Wise
                                    </h4>
                                    <div class="d-flex align-items-center">
                                        <p class="card-text font-small-2 me-25 mb-0"></p>
                                    </div>
                                </div>
                                <div class="card-body statistics-body"
                                    style="padding: 0 !important;height:838px;overflow-y:scroll;">
                                    <table class="table table-hover table-bordered">
                                        <thead>
                                            <tr>
                                                <th class="text-center">Sr.No</th>
                                                <th class="w-50">Application Status</th>
                                                <th class="text-center">Nos of File</th>
                                                <th class="text-center">Total KW</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php $i= 0; @endphp
                                            @foreach ($salesData['foragent'] as $key => $value)
                                                @if ($i == 0)
                                                    <tr>
                                                        <th class="text-center">#</th>
                                                        <th><a
                                                                href="{{ route('sales-master.index') }}">{{ $value['name'] }}</a>
                                                        </th>
                                                        <th class="text-center">{{ $value['count'] }}</th>
                                                        <th class="text-end">{{ number_format($value['register_kw'], 3) }}
                                                        </th>
                                                    </tr>
                                                @else
                                                    <tr>
                                                        <td class="text-center">{{ $i }}</td>
                                                        <td><a
                                                                href="{{ route('sales-master.index', 'type=agent&value=' . $value['id']) }}">{{ $value['name'] }}</a>
                                                        </td>
                                                        <td class="text-center">{{ $value['count'] }}</td>
                                                        <td class="text-end">{{ number_format($value['register_kw'], 3) }}
                                                        </td>
                                                    </tr>
                                                @endif
                                                @php $i++; @endphp
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            @else
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <div id="barChartmonth"></div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
@section('pagescript')
    <script src="app-assets/vendors/apex-charts/apexcharts.js"></script>
    <script>
        function decodeEntities(encodedString) {
            var parser = new DOMParser;
            var dom = parser.parseFromString(
                '<!doctype html><body>' + encodedString,
                'text/html');
            return dom.body.textContent;
        }

        @if (session()->get('soft') == 'erp')
            var colors = ['#9AD3DA', '#00A8A8', '#03444A'];

            var options = {
                series: @json($data['data_count']),
                chart: {
                    type: 'bar',
                    height: 350
                },
                colors: colors,
                dataLabels: {
                    enabled: false
                },

                title: {
                    text: 'Last 30 Days',
                    align: 'center'
                },
                xaxis: {
                    categories: @json($data['date'])
                },

                tooltip: {
                    shared: true,
                    intersect: false
                }
            };

            var chart = new ApexCharts(document.querySelector("#barChartmonth"), options);
            chart.render();
        @endif
    </script>

@endsection


@endsection
