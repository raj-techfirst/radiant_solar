@extends('layouts.app')
@section('title', 'Reports')
@section('content')
<div class="row">
    <div class="col-12 mt-2">
        <div class="row match-height">
            @can('reports-total-collection')
            <div class="col-lg-3 col-md-6 col-12">
                <a href="{{ route('total-collection-reports') }}">
                    <div class="card report-card border cursor-pointer">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <img src="{{ asset('img/icon/total-collection.png') }}" alt="google drive" height="38">
                                <span class="text-truncate fw-bolder h4">{{ $totalcollection[0]['total'] }}</span>
                            </div>
                            <div class="my-1 mb-0 d-flex justify-content-between">
                                <span class="text-truncate fw-bolder h4">{{ number_format($totalcollection[0]['kw'],3) }} <small>KW</small></span>
                            </div>
                            <div class=" mb-50">
                                <h5>Total Collection </h5>
                            </div>

                            <div class="progress progress-bar-secondary progress-md mb-0" style="height: 5px">
                                <div class="progress-bar" role="progressbar" aria-valuenow="100" aria-valuemin="30" aria-valuemax="100" style="width: 30%"></div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            @endcan

            @can('reports-payment-pending')
            <div class="col-lg-3 col-md-6 col-12">
                <a href="{{ route('payment-pending-reports') }}">
                    <div class="card report-card border cursor-pointer">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <img src="{{ asset('img/icon/payment-pending.png') }}" alt="google drive" height="38">
                                <span class="text-truncate fw-bolder h4">{{ $paymentpending[0]['total'] }}</span>
                            </div>
                            <div class="my-1 mb-0 d-flex justify-content-between">
                                <span class="text-truncate fw-bolder h4">{{ number_format($paymentpending[0]['kw'],3) }} <small>KW</small></span>
                            </div>
                            <div class=" mb-50">
                                <h5>Payment Pending</h5>
                            </div>

                            <div class="progress progress-bar-secondary progress-md mb-0" style="height: 5px">
                                <div class="progress-bar" role="progressbar" aria-valuenow="100" aria-valuemin="30" aria-valuemax="100" style="width: 30%"></div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            @endcan

            @can('reports-meter-charges')
            <div class="col-lg-3 col-md-6 col-12">
                <a href="{{ route('meter-charges-reports') }}">
                    <div class="card report-card border cursor-pointer">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <img src="{{ asset('img/icon/meter-charges.png') }}" alt="google drive" height="38">
                                <span class="text-truncate fw-bolder h4">{{ $meterCharges[0]['total'] }}</span>
                            </div>
                            <div class="my-1 mb-0 d-flex justify-content-between">
                                <span class="text-truncate fw-bolder h4">{{ number_format($meterCharges[0]['kw'],3) }} <small>KW</small></span>
                            </div>
                            <div class=" mb-50">
                                <h5>Meter Charges</h5>
                            </div>

                            <div class="progress progress-bar-secondary progress-md mb-0" style="height: 5px">
                                <div class="progress-bar" role="progressbar" aria-valuenow="100" aria-valuemin="30" aria-valuemax="100" style="width: 30%"></div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            @endcan

            @can('reports-dispach')
            <div class="col-lg-3 col-md-6 col-12">
                <a href="{{ route('dispach-reports') }}">
                    <div class="card report-card border cursor-pointer">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <img src="{{ asset('img/icon/dispach.png') }}" alt="google drive" height="38">
                                <span class="text-truncate fw-bolder h4">{{ $dispach[0]['total'] }}</span>
                            </div>
                            <div class="my-1 mb-0 d-flex justify-content-between">
                                <span class="text-truncate fw-bolder h4">{{ number_format($dispach[0]['kw'],3) }} <small>KW</small></span>
                            </div>
                            <div class=" mb-50">
                                <h5>Dispatch List</h5>
                            </div>

                            <div class="progress progress-bar-secondary progress-md mb-0" style="height: 5px">
                                <div class="progress-bar" role="progressbar" aria-valuenow="100" aria-valuemin="30" aria-valuemax="100" style="width: 30%"></div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            @endcan

            @can('reports-installation')
            <div class="col-lg-3 col-md-6 col-12">
                <a href="{{ route('installation-reports') }}">
                    <div class="card report-card border cursor-pointer">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <img src="{{ asset('img/icon/panel-installation.png') }}" alt="google drive" height="38">
                                <span class="text-truncate fw-bolder h4">{{ $installation[0]['total'] }}</span>
                            </div>
                            <div class="my-1 mb-0 d-flex justify-content-between">
                                <span class="text-truncate fw-bolder h4">{{ number_format($installation[0]['kw'],3) }} <small>KW</small></span>
                            </div>
                            <div class=" mb-50">
                                <h5>Installation List Old(Without ERP)</h5>
                            </div>

                            <div class="progress progress-bar-secondary progress-md mb-0" style="height: 5px">
                                <div class="progress-bar" role="progressbar" aria-valuenow="100" aria-valuemin="30" aria-valuemax="100" style="width: 30%"></div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            @endcan

            @can('reports-installation')
            <div class="col-lg-3 col-md-6 col-12">
                <a href="{{ route('installation-new-reports') }}">
                    <div class="card report-card border cursor-pointer">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <img src="{{ asset('img/icon/panel-installation.png') }}" alt="google drive" height="38">
                                <span class="text-truncate fw-bolder h4">{{ $installationNew[0]['total'] }}</span>
                            </div>
                            <div class="my-1 mb-0 d-flex justify-content-between">
                                <span class="text-truncate fw-bolder h4">{{ number_format($installationNew[0]['kw'],3) }} <small>KW</small></span>
                            </div>
                            <div class=" mb-50">
                                <h5>Installation List (NEW)</h5>
                            </div>

                            <div class="progress progress-bar-secondary progress-md mb-0" style="height: 5px">
                                <div class="progress-bar" role="progressbar" aria-valuenow="100" aria-valuemin="30" aria-valuemax="100" style="width: 30%"></div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            @endcan

            @can('reports-meter-application')
            <div class="col-lg-3 col-md-6 col-12">
                <a href="{{ route('meter-application-reports') }}">
                    <div class="card report-card border cursor-pointer">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <img src="{{ asset('img/icon/meter-application.png') }}" alt="google drive" height="38">
                                <span class="text-truncate fw-bolder h4">{{ $meterApplication[0]['total'] }}</span>
                            </div>
                            <div class="my-1 mb-0 d-flex justify-content-between">
                                <span class="text-truncate fw-bolder h4">{{ number_format($meterApplication[0]['kw'],3) }} <small>KW</small></span>
                            </div>
                            <div class=" mb-50">
                                <h5>Meter Application</h5>
                            </div>

                            <div class="progress progress-bar-secondary progress-md mb-0" style="height: 5px">
                                <div class="progress-bar" role="progressbar" aria-valuenow="100" aria-valuemin="30" aria-valuemax="100" style="width: 30%"></div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            @endcan

            @can('reports-final')
            <div class="col-lg-3 col-md-6 col-12">
                <a href="{{ route('final-reports') }}">
                    <div class="card report-card border cursor-pointer">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <img src="{{ asset('img/icon/report.png') }}" alt="google drive" height="38">
                                <span class="text-truncate fw-bolder h4">{{ $finalReport[0]['total'] }}</span>
                            </div>
                            <div class="my-1 mb-0 d-flex justify-content-between">
                                <span class="text-truncate fw-bolder h4">{{ number_format($finalReport[0]['kw'],3) }} <small>KW</small></span>
                            </div>
                            <div class=" mb-50">
                                <h5>Final Report</h5>
                            </div>
                            <div class="progress progress-bar-secondary progress-md mb-0" style="height: 5px">
                                <div class="progress-bar" role="progressbar" aria-valuenow="100" aria-valuemin="30" aria-valuemax="100" style="width: 30%"></div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            @endcan
            @can('reports-invoice')
            <div class="col-lg-3 col-md-6 col-12">
                <a href="{{ route('invoice-reports') }}">
                    <div class="card report-card border cursor-pointer">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <img src="{{ asset('img/icon/invoice.png') }}" alt="google drive" height="38">
                                <span class="text-truncate fw-bolder h4">{{ $invoice[0]['total'] }}</span>
                            </div>
                            <div class="my-1 mb-0 d-flex justify-content-between">
                                <span class="text-truncate fw-bolder h4">{{ number_format($invoice[0]['kw'],3) }} <small>KW</small></span>
                            </div>
                            <div class=" mb-50">
                                <h5>Invoice Report</h5>
                            </div>
                            <div class="progress progress-bar-secondary progress-md mb-0" style="height: 5px">
                                <div class="progress-bar" role="progressbar" aria-valuenow="100" aria-valuemin="30" aria-valuemax="100" style="width: 30%"></div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            @endcan
            <div class="col-lg-3 col-md-6 col-12">
                <div class="card report-card border cursor-pointer">
                    <a href="{{ route('subsidy-claim-reports') }}">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <img src="{{ asset('img/icon/subsidy.png') }}" alt="google drive" height="38">
                                <span class="text-truncate fw-bolder h4">{{ $subClaim[0]['total'] }}</span>
                            </div>
                            <div class="my-1 mb-0 d-flex justify-content-between">
                                <span class="text-truncate fw-bolder h4">{{ number_format($subClaim[0]['kw'],3) }} <small>KW</small></span>
                            </div>
                            <div class="mt-1 mb-50">
                                <h5>Subsidy Claim Report</h5>
                            </div>
                            <div class="progress progress-bar-secondary progress-md mb-0" style="height: 5px">
                                <div class="progress-bar" role="progressbar" aria-valuenow="100" aria-valuemin="30" aria-valuemax="100" style="width: 30%"></div>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
            @can('panels-required-reports')
            <div class="col-lg-3 col-md-6 col-12">
                <div class="card report-card border cursor-pointer">
                    <a href="{{ route('panels-required-reports') }}">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <img src="{{ asset('img/icon/panel.png') }}" alt="google drive" height="38">
                            </div>
                            <div class="my-1 mb-50">
                                <h5>Panel Required Report</h5>
                            </div>
                            <div class="progress progress-bar-secondary progress-md mb-0" style="height: 5px">
                                <div class="progress-bar" role="progressbar" aria-valuenow="100" aria-valuemin="30" aria-valuemax="100" style="width: 30%"></div>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
            @endcan
            @can('inverters-required-reports')
            <div class="col-lg-3 col-md-6 col-12">
                <div class="card report-card border cursor-pointer">
                    <a href="{{ route('inverters-required-reports') }}">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <img src="{{ asset('img/icon/inverter.png') }}" alt="google drive" height="38">
                            </div>

                            <div class="mt-1 mb-50">
                                <h5>Inverter Required Report</h5>
                            </div>
                            <div class="progress progress-bar-secondary progress-md mb-0" style="height: 5px">
                                <div class="progress-bar" role="progressbar" aria-valuenow="100" aria-valuemin="30" aria-valuemax="100" style="width: 30%"></div>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
            @endcan

            @can('b2b-accept')
            <div class="col-lg-3 col-md-6 col-12">
                <div class="card report-card border cursor-pointer">
                    <a href="{{ route('b2b-accept') }}">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <img src="{{ asset('img/icon/b2b.png') }}" alt="b2b" height="38">
                                <span class="text-truncate fw-bolder h4">{{ $b2bAccept }}</span>
                            </div>

                            <div class="mt-1 mb-50">
                                <h5>B2B Accept Report</h5>
                            </div>
                            <div class="progress progress-bar-secondary progress-md mb-0" style="height: 5px">
                                <div class="progress-bar" role="progressbar" aria-valuenow="100" aria-valuemin="30" aria-valuemax="100" style="width: 30%"></div>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
            @endcan

            @can('b2b-dispatch')
            <div class="col-lg-3 col-md-6 col-12">
                <div class="card report-card border cursor-pointer">
                    <a href="{{ route('b2b-dispatch') }}">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <img src="{{ asset('img/icon/b2b.png') }}" alt="b2b" height="38">
                                <span class="text-truncate fw-bolder h4">{{ $b2bDispatch }}</span>
                            </div>

                            <div class="mt-1 mb-50">
                                <h5>B2B Dispatch Report</h5>
                            </div>
                            <div class="progress progress-bar-secondary progress-md mb-0" style="height: 5px">
                                <div class="progress-bar" role="progressbar" aria-valuenow="100" aria-valuemin="30" aria-valuemax="100" style="width: 30%"></div>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
            @endcan

            @can('b2b-rate')
            <div class="col-lg-3 col-md-6 col-12">
                <div class="card report-card border cursor-pointer">
                    <a href="{{ route('b2b-rate') }}">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <img src="{{ asset('img/icon/b2b.png') }}" alt="b2b" height="38">
                                <span class="text-truncate fw-bolder h4">{{ $b2bRate }}</span>
                            </div>

                            <div class="mt-1 mb-50">
                                <h5>B2B Rate Report</h5>
                            </div>
                            <div class="progress progress-bar-secondary progress-md mb-0" style="height: 5px">
                                <div class="progress-bar" role="progressbar" aria-valuenow="100" aria-valuemin="30" aria-valuemax="100" style="width: 30%"></div>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
            @endcan

            
        </div>
    </div>
</div>
@endsection