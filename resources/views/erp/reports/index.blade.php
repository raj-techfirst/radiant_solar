@extends('layouts.app')
@section('title', 'Reports')
@section('content')
<div class="row">
    <div class="col-12 mt-2">
        <div class="row match-height">

            @can('get-serial-numbers')
            <div class="col-lg-3 col-md-6 col-12">
                <a href="{{ route('get-serial-numbers') }}">
                    <div class="card report-card border cursor-pointer">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <img src="{{ asset('img/icon/serial-numbers.png') }}" alt="google drive" height="38">
                            </div>
                            <div class="my-1 mb-0 d-flex justify-content-between">
                                <span class="text-truncate fw-bolder h4"></span>
                            </div>
                            <div class=" mb-50">
                                <h5>Serial Numbers</h5>
                            </div>

                            <div class="progress progress-bar-secondary progress-md mb-0" style="height: 5px">
                                <div class="progress-bar" role="progressbar" aria-valuenow="100" aria-valuemin="15" aria-valuemax="100" style="width: 15%"></div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            @endcan

            @can('stock-report')
            <div class="col-lg-3 col-md-6 col-12">
                <a href="{{ route('stock-report') }}">
                    <div class="card report-card border cursor-pointer">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <img src="{{ asset('img/icon/warehouse.png') }}" alt="google drive" height="38">
                            </div>
                            <div class="my-1 mb-0 d-flex justify-content-between">
                                <span class="text-truncate fw-bolder h4"></span>
                            </div>
                            <div class=" mb-50">
                                <h5>Warehouse Wise Stock</h5>
                            </div>

                            <div class="progress progress-bar-secondary progress-md mb-0" style="height: 5px">
                                <div class="progress-bar" role="progressbar" aria-valuenow="100" aria-valuemin="15" aria-valuemax="100" style="width: 15%"></div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            @endcan

            @can('project-wise-stock-report')

            <div class="col-lg-3 col-md-6 col-12">
                <a href="{{ route('project-wise-stock-report') }}">
                    <div class="card report-card border cursor-pointer">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <img src="{{ asset('img/icon/project-wise-stock.png') }}" alt="google drive" height="38">
                            </div>
                            <div class="my-1 mb-0 d-flex justify-content-between">
                                <span class="text-truncate fw-bolder h4"></span>
                            </div>
                            <div class=" mb-50">
                                <h5>Project Wise Stock</h5>
                            </div>
                            <div class="progress progress-bar-secondary progress-md mb-0" style="height: 5px">
                                <div class="progress-bar" role="progressbar" aria-valuenow="100" aria-valuemin="15" aria-valuemax="100" style="width: 15%"></div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            @endcan

            @can('project-wise-dispach')

            <div class="col-lg-3 col-md-6 col-12">
                <a href="{{ route('project-wise-dispach') }}">
                    <div class="card report-card border cursor-pointer">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <img src="{{ asset('img/icon/project-wise-dispach.png') }}" alt="google drive" height="38">
                            </div>
                            <div class="my-1 mb-0 d-flex justify-content-between">
                                <span class="text-truncate fw-bolder h4"></span>
                            </div>
                            <div class=" mb-50">
                                <h5>Project Wise Dispach</h5>
                            </div>
                            <div class="progress progress-bar-secondary progress-md mb-0" style="height: 5px">
                                <div class="progress-bar" role="progressbar" aria-valuenow="100" aria-valuemin="15" aria-valuemax="100" style="width: 15%"></div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            @endcan

            @can('required-stock-report')

            <div class="col-lg-3 col-md-6 col-12">
                <a href="{{ route('required-stock-report') }}">
                    <div class="card report-card border cursor-pointer">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <img src="{{ asset('img/icon/requisition.png') }}" alt="google drive" height="38">
                            </div>
                            <div class="my-1 mb-0 d-flex justify-content-between">
                                <span class="text-truncate fw-bolder h4"></span>
                            </div>
                            <div class=" mb-50">
                                <h5>Requisition Report</h5>
                            </div>
                            <div class="progress progress-bar-secondary progress-md mb-0" style="height: 5px">
                                <div class="progress-bar" role="progressbar" aria-valuenow="100" aria-valuemin="15" aria-valuemax="100" style="width: 15%"></div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            @endcan

            @can('b2b-dispach')

            <div class="col-lg-3 col-md-6 col-12">
                <a href="{{ route('b2b-dispach') }}">
                    <div class="card report-card border cursor-pointer">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <img src="{{ asset('img/icon/b2b.png') }}" alt="b2b" height="38">
                            </div>
                            <div class="my-1 mb-0 d-flex justify-content-between">
                                <span class="text-truncate fw-bolder h4"></span>
                            </div>
                            <div class=" mb-50">
                                <h5>B2B Dispach Report</h5>
                            </div>
                            <div class="progress progress-bar-secondary progress-md mb-0" style="height: 5px">
                                <div class="progress-bar" role="progressbar" aria-valuenow="100" aria-valuemin="15" aria-valuemax="100" style="width: 15%"></div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            @endcan
        </div>
    </div>
</div>
@endsection