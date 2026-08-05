@extends('layouts.app')
@section('title', 'Dashboard')
@section('content')
    <link rel="stylesheet" href="{{ asset('app-assets/vendors/apex-charts/apex-charts.css') }}" />
    <div class="content-wrapper container-xxl p-0 dashboard">
        <div class="content-body">
            @if (session()->get('soft') == 'crm')
                <!-- KPI Cards -->
                <div class="row match-height mt-1">
                    <div class="col-lg-9 col-sm-9 col-12">
                        <div class="row">
                            <!-- Today's Follow Ups -->
                            <div class="col-lg-3 col-sm-6 col-6">
                                <div class="card">
                                    <a href="{{ url('lead?type=today-followups') }}" class="text-decoration-none">
                                        <div class="kpi-card pb-0">
                                            <div class="kpi-content">
                                                <h2>{{ $todayFollowUp }}</h2>
                                            </div>
                                            <div class="kpi-icon bg-light-warning text-warning">
                                                <i data-feather="phone-call"></i>
                                            </div>

                                        </div>
                                         <div class="kpi-content ps-1 pb-1">
                                                 <p>Today's Follow Ups</p>
                                            </div>
                                    </a>
                                </div>
                            </div>

                            <!-- Total Site Visits -->
                            <div class="col-lg-3 col-sm-6 col-6">
                                <div class="card">
                                    <a href="{{ url('lead?type=site-visit') }}" class="text-decoration-none">
                                        <div class="kpi-card pb-0">
                                            <div class="kpi-content">
                                                <h2>{{ $sitevisit }}</h2>
                                            </div>
                                            <div class="kpi-icon bg-light-info text-info">
                                                <i data-feather="map-pin"></i>
                                            </div>

                                        </div>
                                         <div class="kpi-content ps-1 pb-1">
                                                 <p>Total Site Visits</p>
                                            </div>
                                    </a>
                                </div>
                            </div>

                             <!-- Total Won Leads -->
                             <div class="col-lg-3 col-sm-6 col-6">
                                <div class="card">
                                    <a href="{{ route('lead-complet') }}" class="text-decoration-none">
                                        <div class="kpi-card pb-0">
                                            <div class="kpi-content">
                                                <h2>{{ $totalwonLeads }}</h2>
                                            </div>
                                            <div class="kpi-icon bg-light-success text-success">
                                                <i data-feather="user-check"></i>
                                            </div>

                                        </div>
                                         <div class="kpi-content ps-1 pb-1">
                                                 <p>Lead Won</p>
                                            </div>
                                    </a>
                                </div>
                            </div>

                            <!-- Total Leads -->
                             <div class="col-lg-3 col-sm-6 col-6">
                                <div class="card">
                                    <a href="{{ route('lead.index') }}" class="text-decoration-none">
                                        <div class="kpi-card pb-0">
                                            <div class="kpi-content">
                                                  @php
                                                    $totalLeads = isset($graph1Data['series'][0]['data'][0])
                                                        ? $graph1Data['series'][0]['data'][0]
                                                        : 0;
                                                @endphp
                                                <h2>{{ $totalLeads }}</h2>
                                            </div>
                                            <div class="kpi-icon bg-light-primary text-primary">
                                                <i data-feather="users"></i>
                                            </div>

                                        </div>
                                         <div class="kpi-content ps-1 pb-1">
                                                 <p>Total Leads</p>
                                            </div>
                                    </a>
                                </div>
                            </div>


                        </div>

                    </div>

                    <!-- Total Sales Quotations-->
                    <div class="col-lg-3 col-sm-6 col-12">
                        <div class="card">
                            <a href="{{ route('sales-quatation.index') }}" class="text-decoration-none">
                                <div class="kpi-card">
                                    <div class="kpi-content">
                                        <h2>{{ $totalQuotations }}</h2>
                                        <p>Total Quotations</p>
                                    </div>
                                    <div class="kpi-icon bg-light-primary text-primary">
                                        <i data-feather="file"></i>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>

                </div>

                <div class="row match-height">

                    @if (isset($salesData['forPortal']) && count($salesData['forPortal']) > 0)
                        @foreach ($salesData['forPortal'] as $key => $value)
                            <!-- Total Portal Wise -->
                            <div class="col-lg-3 col-sm-6 col-6">
                                <div class="card">
                                    <a href="{{ route('sales-master.index', 'type=portal&value=' . $key) }}"
                                        class="text-decoration-none">
                                        <div class="kpi-card">
                                            <div class="kpi-content">
                                                <h4>{{ number_format($value['register_kw'], 3) }}
                                                    <small>KW</small>
                                                </h4>
                                                <p>{{ $key }} ({{ number_format($value['count'], 0) }})</p>
                                            </div>
                                            <div class="kpi-icon bg-light-success text-success">
                                                <img src='{{ asset('img/icon/' . $key . '.png') }}'>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    @endif

                    @if (isset($salesData['forstatus']['total']['count']))
                        <!-- Total Sales Orders -->
                        <div class="col-lg-3 col-sm-6 col-6">
                            <div class="card">
                                <a href="{{ route('sales-master.index') }}" class="text-decoration-none">
                                    <div class="kpi-card">
                                        <div class="kpi-content">
                                            @php
                                                $totalSales = isset($salesData['forstatus']['total']['count'])
                                                    ? $salesData['forstatus']['total']['count']
                                                    : 0;
                                            @endphp
                                            <h2>{{ $totalSales }}</h2>
                                            <p>Total Sales Orders</p>
                                        </div>
                                        <div class="kpi-icon bg-light-info text-info">
                                            <img src='{{ asset('img/icon/order.png') }}'>
                                        </div>
                                    </div>
                                </a>

                            </div>
                        </div>
                    @endif
                </div>

                <!-- Tables Section -->
                <div class="row match-height">
                    <!-- Sales Order Status Wise -->
                    @if (isset($salesData['forstatus']) && count($salesData['forstatus']) > 0)
                        <div class="col-md-6 col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">Sales Order Status Wise</h4>
                                </div>
                                <div class="card-body p-0 table-responsive-custom">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Status</th>
                                                <th class="text-center">Files</th>
                                                <th class="text-end">Total KW</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($salesData['forstatus'] as $key => $value)
                                                @if ($key == 'total')
                                                    <tr>
                                                        <td>
                                                            <a href="{{ route('sales-master.index') }}"
                                                                class="fw-bold text-success">
                                                                {{ $value['name'] }}
                                                            </a>
                                                        </td>
                                                        <td class="text-center">
                                                            <span
                                                                class="badge rounded-pill bg-light-primary text-primary">{{ $value['count'] }}</span>
                                                        </td>
                                                        <td class="text-end font-monospace">
                                                            {{ number_format($value['register_kw'], 3) }}
                                                        </td>
                                                    </tr>
                                                @endif
                                                @if ($key !== 'total')
                                                    <tr>
                                                        <td>
                                                            @if (!empty($value['status']))
                                                                <a href="{{ route('sales-master.index', 'type=status&value=' . $value['status'] . '&value_not=' . $value['status_not']) }}"
                                                                    class="fw-bold text-body ">
                                                                    {{ $value['name'] }}
                                                                </a>
                                                            @else
                                                                <span class="fw-bold text-body">{{ $value['name'] }}</span>
                                                            @endif
                                                        </td>
                                                        <td class="text-center">
                                                            <span class="badge rounded-pill bg-light-primary text-primary">{{ $value['count'] }}</span>
                                                        </td>
                                                        <td class="text-end font-monospace">
                                                            {{ number_format($value['register_kw'], 3) }}
                                                        </td>
                                                    </tr>
                                                @endif
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Sales Order Agent Wise -->
                    @if (isset($salesData['foragent']) && count($salesData['foragent']) > 0)
                        <div class="col-md-6 col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">Sales Order Agent Wise</h4>
                                </div>
                                <div class="card-body p-0 table-responsive-custom">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Agent Name</th>
                                                <th class="text-center">Files</th>
                                                <th class="text-end">Total KW</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($salesData['foragent'] as $key => $value)
                                                @if ($key == 'total')
                                                    <tr>
                                                        <td>
                                                            <a href="{{ route('sales-master.index') }}"
                                                                class="fw-bold text-success">
                                                                {{ $value['name'] }}
                                                            </a>
                                                        </td>
                                                        <td class="text-center">
                                                            <span
                                                                class="badge rounded-pill bg-light-info text-info">{{ $value['count'] }}</span>
                                                        </td>
                                                        <td class="text-end font-monospace">
                                                            {{ number_format($value['register_kw'], 3) }}
                                                        </td>
                                                    </tr>
                                                @endif
                                                @if ($key !== 'total')
                                                    <tr>
                                                        <td>
                                                            <a href="{{ route('sales-master.index', 'type=agent&value=' . ($value['id'] ?? '')) }}"
                                                                class="fw-bold text-body">
                                                                {{ $value['name'] }}
                                                            </a>
                                                        </td>
                                                        <td class="text-center">
                                                            <span
                                                                class="badge rounded-pill bg-light-info text-info">{{ $value['count'] }}</span>
                                                        </td>
                                                        <td class="text-end font-monospace">
                                                            {{ number_format($value['register_kw'], 3) }}
                                                        </td>
                                                    </tr>
                                                @endif
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Charts Section -->
                @if (isset($graph1Data['series'][0]['data'][0]) && $graph1Data['series'][0]['data'][0] > 0)
                    <div class="row match-height">
                        <div class="col-md-6 col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">Leads vs Quotations</h4>
                                    <span class="badge bg-light-success text-success">Conv:
                                        {{ $graph1Data['conversionRate'] }}%</span>
                                </div>
                                <div class="card-body">
                                    <div id="leadsVsQuotationsChart"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">Quotations vs Orders</h4>
                                    <span class="badge bg-light-success text-success">Conv:
                                        {{ $graph2Data['conversionRate'] }}%</span>
                                </div>
                                <div class="card-body">
                                    <div id="quotationsVsOrdersChart"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            @elseif (session()->get('soft') == 'erp')
                <!-- ERP Dashboard -->
                <div class="row match-height mt-1">
                    <!-- KPI Cards -->
                    <div class="col-lg-3 col-sm-6 col-12">
                        <a href="{{ route('purchase-order.index') }}">
                            <div class="card">
                                <div class="kpi-card">
                                    <div class="kpi-content">
                                        <h2>{{ $data['summary']['total_purchase_orders'] }}</h2>
                                        <p>Purchase Orders</p>
                                    </div>
                                    <div class="kpi-icon bg-light-primary text-primary">
                                        <i data-feather="shopping-bag"></i>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-lg-3 col-sm-6 col-12">
                        <a href="{{ route('purchase-direct.index') }}">
                            <div class="card">
                                <div class="kpi-card">
                                    <div class="kpi-content">
                                        <h2>{{ $data['summary']['total_goods_receipt'] }}</h2>
                                        <p>Goods Receipt</p>
                                    </div>
                                    <div class="kpi-icon bg-light-success text-success">
                                        <i data-feather="inbox"></i>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-lg-3 col-sm-6 col-12">
                        <a href="{{ route('delivery-challan.index') }}">
                            <div class="card">
                                <div class="kpi-card">
                                    <div class="kpi-content">
                                        <h2>{{ $data['summary']['total_goods_issue'] }}</h2>
                                        <p>Goods Issue</p>
                                    </div>
                                    <div class="kpi-icon bg-light-info text-info">
                                        <i data-feather="send"></i>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-lg-3 col-sm-6 col-12">
                        <a href="{{ route('warehouse-stock.index') }}">
                            <div class="card">
                                <div class="kpi-card">
                                    <div class="kpi-content">
                                        <h2>{{ $data['summary']['total_stock_items'] }}</h2>
                                        <p>Total Stock Items</p>
                                    </div>
                                    <div class="kpi-icon bg-light-warning text-warning">
                                        <i data-feather="package"></i>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>

                <div class="row match-height">
                    <!-- Monthly Activity Chart -->
                    <div class="col-lg-12 col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">Monthly Activity (Last 30 Days)</h4>
                            </div>
                            <div class="card-body">
                                <div id="barChartmonth"></div>
                            </div>
                        </div>
                    </div>


                </div>

                <!-- Stock Alert Panel -->
                <div class="row">
                    <div class="col-12">
                        <div class="card border-danger">
                            <div class="card-header border-bottom">
                                <h4 class="card-title text-danger">
                                    Low Stock Alerts (Below MOQ)
                                </h4>
                            </div>
                            <div class="card-body p-0 table-responsive-custom">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Warehouse</th>
                                            <th>Item</th>
                                            <th class="text-center">Quantity</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if (isset($lowStockDetails) && count($lowStockDetails) > 0)
                                            @foreach ($lowStockDetails as $stock)
                                                <tr>
                                                    <td>
                                                        <span class="fw-bold">{{ $stock->warehouse->name }}</span>
                                                    </td>
                                                    <td>
                                                        <span
                                                            class="fw-bold">{{ $stock->item->name ?? getItemGropName($stock->itemGroup, 0) }}</span>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="fw-bolder text-danger">{{ $stock->quantity ?? 0 }}
                                                            {{ $stock->unit->unit_name ?? '' }}</span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @else
                                            <tr>
                                                <td colspan="3" class="text-center py-3">
                                                    <span class="text-success fw-bold"><i data-feather="check-circle"></i>
                                                        All stock
                                                        levels are healthy!</span>
                                                </td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="row match-height mt-3">
                    <div class="col-12">
                        <p class="text-center"> <i>Just a moment while we load your data...</i></p>
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
            var colors = ['#9AD3DA', '#00A8A8', '#03444A', '#F59E0B'];

            var options = {
                series: @json($data['data_count']),
                chart: {
                    type: 'bar',
                    height: 350,
                    toolbar: {
                        show: false
                    }
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
                },
                legend: {
                    show: false
                },
                stroke: {
                    width: 2,
                    colors: ['transparent']
                }
            };

            var chart = new ApexCharts(document.querySelector("#barChartmonth"), options);
            chart.render();
        @endif

        @if (isset($graph1Data))
            var graph1Options = {
                series: @json($graph1Data['series']),
                chart: {
                    type: 'bar',
                    height: 350,
                    toolbar: {
                        show: false
                    }
                },
                plotOptions: {
                    bar: {
                        horizontal: false,
                        columnWidth: '55%',
                        borderRadius: 8,
                    },
                },
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    show: true,
                    width: 2,
                    colors: ['transparent']
                },
                xaxis: {
                    categories: @json($graph1Data['categories']),
                    axisBorder: {
                        show: false
                    },
                    axisTicks: {
                        show: false
                    }
                },
                yaxis: {
                    title: {
                        text: 'Count'
                    }
                },
                fill: {
                    opacity: 1,
                    colors: ['#03417d', '#7367F0', '#28C76F']
                },
                tooltip: {
                    y: {
                        formatter: function(val) {
                            return val
                        }
                    }
                },
                grid: {
                    borderColor: '#f1f1f1',
                }
            };

            var graph1Chart = new ApexCharts(document.querySelector("#leadsVsQuotationsChart"), graph1Options);
            graph1Chart.render();
        @endif

        @if (isset($graph2Data))
            var graph2Options = {
                series: @json($graph2Data['series']),
                chart: {
                    type: 'bar',
                    height: 350,
                    toolbar: {
                        show: false
                    }
                },
                plotOptions: {
                    bar: {
                        horizontal: false,
                        columnWidth: '55%',
                        borderRadius: 8,
                    },
                },
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    show: true,
                    width: 2,
                    colors: ['transparent']
                },
                xaxis: {
                    categories: @json($graph2Data['categories']),
                    axisBorder: {
                        show: false
                    },
                    axisTicks: {
                        show: false
                    }
                },
                yaxis: {
                    title: {
                        text: 'Count'
                    }
                },
                fill: {
                    opacity: 1,
                    colors: ['#FF9F43', '#FF9F43', '#00CFDD']
                },
                tooltip: {
                    y: {
                        formatter: function(val) {
                            return val
                        }
                    }
                },
                grid: {
                    borderColor: '#f1f1f1',
                }
            };

            var graph2Chart = new ApexCharts(document.querySelector("#quotationsVsOrdersChart"), graph2Options);
            graph2Chart.render();
        @endif
    </script>

@endsection


@endsection
