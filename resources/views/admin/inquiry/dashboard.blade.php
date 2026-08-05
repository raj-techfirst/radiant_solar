@extends('layouts.app')
@section('title', 'Complaint Dashboard')
@section('content')
    <link rel="stylesheet" href="{{ asset('app-assets/vendors/apex-charts/apex-charts.css') }}" />

    <div class="content-wrapper container-xxl p-0">
        <div class="content-body">

            <div class="row mb-1">
                <div class="col-12 d-flex justify-content-between align-items-center">
                    <h4 class="content-header-title mb-0">Complaint Dashboard</h4>
                    <a href="{{ route('inquiry-list') }}" class="btn btn-sm btn-primary"><i class="fa fa-list me-25"></i> View
                        All Complaints</a>
                </div>
            </div>

            <!-- KPI Cards Row 1 -->

            <div class="row">
                <!-- Today's Reminders -->
                <div class="col-lg-5 col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Today's Reminders</h4>
                        </div>
                        <div class="card-body pt-2" style="max-height:315px;overflow-y: auto;">
                            @if (count($todayReminders) > 0)
                                <ul class="timeline">
                                    @foreach ($todayReminders as $reminder)
                                        @php
                                            $payStatus = getServiceStatusClass($reminder->status);
                                            $inquiryId = $reminder->inquiry_id ?? ($reminder->inquiry->id ?? null);
                                        @endphp
                                        <li class="timeline-item">
                                            <span class="timeline-point timeline-indicator timeline-indicator-warning">
                                                <i class="fa fa-bell"></i>
                                            </span>
                                            <div class="timeline-event">
                                                <a href="{{ $inquiryId ? route('inquiry-follow', $inquiryId) : 'javascript:void(0);' }}"
                                                    class="text-decoration-none" target="_blank">
                                                    <div class="d-flex justify-content-between mb-sm-0 mb-0">
                                                        <h6 class="mb-0">
                                                            {{ $reminder->inquiry->consumer_name ?? 'N/A' }}
                                                        </h6>
                                                        <span class="timeline-event-time">
                                                            {{ date('h:i A', strtotime($reminder->reminder_date)) }}
                                                        </span>
                                                    </div>
                                                    <span
                                                        class="badge bg-light-{{ $payStatus['class'] }}">{{ $payStatus['status'] }}</span>
                                                    @if ($reminder->assignPerson)
                                                        <small class="text-muted d-block mt-25">
                                                            <i class="fa fa-user-circle"></i>
                                                            {{ $reminder->assignPerson->name }}
                                                        </small>
                                                    @endif
                                                </a>
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <div class="text-center py-3">
                                    <i data-feather="check-circle" class="text-success mb-1"
                                        style="width: 40px; height: 40px;"></i>
                                    <p class="text-muted">No reminders for today</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-lg-7 col-12">
                    <div class="row match-height">


                         <div class="col-lg-4 col-sm-4 col-6">
                            <div class="card">
                                <div class="kpi-card pb-0">
                                    <div class="kpi-content">
                                        <h2>{{ $randomVisitdTodayCount }}</h2>
                                    </div>
                                    <div class="kpi-icon bg-light-secondary text-secondary">
                                        <i data-feather="calendar"></i>
                                    </div>
                                </div>
                                <div class="kpi-content ps-1 pb-1">
                                    <p>Today's Random Visit</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-sm-4 col-6">
                            <div class="card">
                                <div class="kpi-card pb-0">
                                    <div class="kpi-content">
                                        <h2>{{ $todayComplaints }}</h2>
                                    </div>
                                    <div class="kpi-icon bg-light-warning text-warning">
                                        <i data-feather="alert-circle"></i>
                                    </div>
                                </div>
                                <div class="kpi-content ps-1 pb-1">
                                    <p>Today's Complaints</p>
                                </div>
                            </div>
                        </div>


                        <div class="col-lg-4 col-sm-4 col-6">
                            <div class="card">
                                <div class="kpi-card pb-0">
                                    <div class="kpi-content">
                                        <h2>{{ $thisMonthComplaints }}</h2>
                                    </div>
                                    <div class="kpi-icon bg-light-info text-info">
                                        <i data-feather="calendar"></i>
                                    </div>
                                </div>
                                <div class="kpi-content ps-1 pb-1">
                                    <p>This Month</p>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4 col-sm-4 col-6">
                            <div class="card">
                                <div class="kpi-card pb-0">
                                    <div class="kpi-content">
                                        <h2>{{ $pendingCount }}</h2>
                                    </div>
                                    <div class="kpi-icon bg-light-danger text-danger">
                                        <i data-feather="clock"></i>
                                    </div>
                                </div>
                                <div class="kpi-content ps-1 pb-1">
                                    <p>Pending</p>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4 col-sm-4 col-6">
                            <div class="card">
                                <div class="kpi-card pb-0">
                                    <div class="kpi-content">
                                        <h2>{{ $completedCount }}</h2>
                                    </div>
                                    <div class="kpi-icon bg-light-success text-success">
                                        <i data-feather="check-circle"></i>
                                    </div>
                                </div>
                                <div class="kpi-content ps-1 pb-1">
                                    <p>Completed</p>
                                </div>
                            </div>
                        </div>
                         <div class="col-lg-4 col-sm-4 col-6">
                            <div class="card">
                                <div class="kpi-card pb-0">
                                    <div class="kpi-content">
                                        <h2>{{ $totalComplaints }}</h2>
                                    </div>
                                    <div class="kpi-icon bg-light-primary text-primary">
                                        <i data-feather="inbox"></i>
                                    </div>
                                </div>
                                <div class="kpi-content ps-1 pb-1">
                                    <p>Total Complaints</p>
                                </div>
                            </div>
                        </div>


                        <div class="col-lg-6 col-sm-6 col-6">
                    <div class="card">
                        <div class="kpi-card pb-0">
                            <div class="kpi-content">
                                <h2>{{ $newConsumer }}</h2>
                            </div>
                            <div class="kpi-icon bg-light-success text-success">
                                <i data-feather="user-plus"></i>
                            </div>
                        </div>
                        <div class="kpi-content ps-1 pb-1">
                            <p>New Consumer</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 col-sm-6 col-6">
                    <div class="card">
                        <div class="kpi-card pb-0">
                            <div class="kpi-content">
                                <h2>{{ $oldConsumer }}</h2>
                            </div>
                            <div class="kpi-icon bg-light-warning text-warning">
                                <i data-feather="user"></i>
                            </div>
                        </div>
                        <div class="kpi-content ps-1 pb-1">
                            <p>Old Consumer</p>
                        </div>
                    </div>
                </div>

                    </div>
                </div>
            </div>



            <!-- Charts Row -->
            <div class="row match-height">
                <!-- Status Wise Donut Chart -->
                <div class="col-lg-4 col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Status Wise</h4>
                        </div>
                        <div class="card-body">
                            <div id="statusDonutChart"></div>
                        </div>
                    </div>
                </div>

                <!-- Monthly Trend Bar Chart -->
                <div class="col-lg-8 col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Monthly Trend (Last 6 Months)</h4>
                        </div>
                        <div class="card-body">
                            <div id="monthlyTrendChart"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tables Row -->
            <div class="row match-height">
                <!-- Status Wise Table -->
                <div class="col-lg-6 col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Status Wise Breakdown</h4>
                        </div>
                        <div class="card-body  p-0">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Status</th>
                                            <th class="text-end">Count</th>
                                            <th class="text-end">%</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($serviceStatus as $index => $s)
                                            @php
                                                $found = $statusWise->firstWhere('status', $s['id']);
                                                $count = $found ? $found->total : 0;
                                                $percent =
                                                    $totalComplaints > 0
                                                        ? round(($count / $totalComplaints) * 100, 1)
                                                        : 0;
                                                $payStatus = getServiceStatusClass($s['id']);
                                            @endphp
                                            <tr>
                                                <td>
                                                    <span class="badge bg-light-{{ $payStatus['class'] }}">
                                                        <i class="fa fa-{{ $payStatus['icon'] }} me-25"></i>
                                                        {{ $s['name'] }}
                                                    </span>
                                                </td>
                                                <td class="text-end fw-bold">{{ $count }}</td>
                                                <td class="text-end">{{ $percent }}%</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr class="fw-bold">
                                            <td>Total</td>
                                            <td class="text-end">{{ $totalComplaints }}</td>
                                            <td class="text-end">100%</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Assign Person Wise Table -->
                <div class="col-lg-6 col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Assign Person Wise</h4>
                        </div>
                        <div class="card-body  p-0">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Assign Person</th>
                                            <th class="text-end">Count</th>
                                            <th class="text-end">%</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($assignPersonWise as $item)
                                            @php
                                                $percent =
                                                    $totalComplaints > 0
                                                        ? round(($item->total / $totalComplaints) * 100, 1)
                                                        : 0;
                                            @endphp
                                            <tr>
                                                <td>
                                                    <i class="fa fa-user-circle me-25 text-primary"></i>
                                                    {{ $item->assignPerson->name ?? 'N/A' }}
                                                </td>
                                                <td class="text-end fw-bold">{{ $item->total }}</td>
                                                <td class="text-end">{{ $percent }}%</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="3" class="text-center py-2">No data available</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


        </div>
    </div>

@endsection

@section('pagescript')
    <script src="app-assets/vendors/apex-charts/apexcharts.js"></script>
    <script>
        // Status Donut Chart
        var statusDonutOptions = {
            series: @json($statusData),
            chart: {
                type: 'donut',
                height: 320,
            },
            labels: @json($statusLabels),
            colors: @json($statusColors),
            legend: {
                position: 'bottom',
                fontSize: '13px',
            },
            dataLabels: {
                enabled: true,
                formatter: function(val, opts) {
                    return opts.w.config.series[opts.seriesIndex];
                }
            },
            plotOptions: {
                pie: {
                    donut: {
                        size: '55%',
                        labels: {
                            show: true,
                            total: {
                                show: true,
                                label: 'Total',
                                fontSize: '16px',
                                fontWeight: 600,
                            }
                        }
                    }
                }
            },
            responsive: [{
                breakpoint: 480,
                options: {
                    chart: {
                        height: 280
                    },
                    legend: {
                        position: 'bottom'
                    }
                }
            }]
        };
        new ApexCharts(document.querySelector("#statusDonutChart"), statusDonutOptions).render();

        // Monthly Trend Bar Chart
        var monthlyOptions = {
            series: [{
                name: 'Complaints',
                data: @json($monthData)
            }],
            chart: {
                type: 'bar',
                height: 320,
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
                enabled: true,
                style: {
                    fontSize: '13px',
                    fontWeight: 'bold',
                }
            },
            stroke: {
                show: true,
                width: 2,
                colors: ['transparent']
            },
            xaxis: {
                categories: @json($monthLabels),
                axisBorder: {
                    show: false
                },
                axisTicks: {
                    show: false
                }
            },
            yaxis: {
                title: {
                    text: 'Complaints'
                }
            },
            fill: {
                opacity: 1,
                colors: ['#7367F0']
            },
            tooltip: {
                y: {
                    formatter: function(val) {
                        return val + " complaints"
                    }
                }
            },
            grid: {
                borderColor: '#f1f1f1',
            }
        };
        new ApexCharts(document.querySelector("#monthlyTrendChart"), monthlyOptions).render();
    </script>
@endsection
