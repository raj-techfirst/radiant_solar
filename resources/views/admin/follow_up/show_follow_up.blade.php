@extends('layouts.app')
@section('title', 'Follow Up Show')
@section('content')
    <div class="row mt-2">
        <!-- Lead Information Card -->
        <div class="col-12 col-md-6">
            <div class="card">
                <div class="card-header pb-50">
                    <h4 class="card-title">{{ __('Lead Information') }}</h4>
                    <div class="heading-elements d-flex">
                        <span class="badge bg-{{ $leadMaster->leadStatus->class_name ?? '' }} w-100">{{ $leadMaster->leadStatus->name ?? '' }}</span>
                    </div>
                </div>
                <div class="card-header pb-50">
                    <div class="d-flex align-items-start">
                        <div class="d-flex align-items-center">
                            <div class="avatar m-50 me-1">
                                <img src="{{ asset('img/user.png') }}" alt="Avatar" class="rounded-circle"
                                    style="width: 50px; height: 50px;">
                            </div>
                            <div class="me-2">
                                <h5 class="mb-0">
                                    <a href="javascript:;" class="stretched-link text-heading">{{ $leadMaster->name }}
                                        {{ $leadMaster->last_name }}</a>
                                    <br /><i class="fa fa-phone-square"></i> {{ $leadMaster->mobile }}

                                    @if ($leadMaster->address != '')
                                        <br /><small><i class="fa fa-map-marker"></i>
                                            {{ $leadMaster->address }}</small>
                                    @endif
                                </h5>
                            </div>
                        </div>
                        <div class="ms-auto">
                            <div class="dropdown z-2">
                                <button type="button"
                                    class="btn btn-icon btn-text-secondary rounded-pill dropdown-toggle hide-arrow p-0 waves-effect waves-light"
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="ti ti-dots-vertical ti-md text-muteds"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end" style="">
                                    <li><a class="dropdown-item waves-effect" href="javascript:void(0);">Rename
                                            project</a></li>
                                    <li><a class="dropdown-item waves-effect" href="javascript:void(0);">View
                                            details</a></li>
                                    <li><a class="dropdown-item waves-effect" href="javascript:void(0);">Add to
                                            favorites</a></li>
                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>
                                    <li><a class="dropdown-item text-danger waves-effect" href="javascript:void(0);">Leave
                                            Project</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body pt-1">
                    <div class="row mb-2">
                        @if ($leadMaster->remark != '')
                            <div class="col-md-12 mb-2">
                                Remark : {{ $leadMaster->remark }}
                            </div>
                        @endif
                        <div class="col-md-6">
                            <div class="media">
                                <div class="avatar bg-light-primary rounded me-1">
                                    <div class="avatar-content">
                                        <i class="fa fa-bolt"></i>
                                    </div>
                                </div>
                                <div class="media-body">
                                    <h6 class="mb-0">{{ $leadMaster->kw ?? 'N/A' }}
                                    </h6>
                                    <small>KW</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="media">
                                <div class="avatar bg-light-primary rounded me-1">
                                    <div class="avatar-content">
                                        <i class="fa fa-share-square"></i>
                                    </div>
                                </div>
                                <div class="media-body">
                                    <h6 class="mb-0">{{ $leadMaster->source->source_name ?? 'N/A' }}
                                    </h6>
                                    <small>Source</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-md-6">
                            <div class="media">
                                <div class="avatar bg-light-primary rounded me-1">
                                    <div class="avatar-content">
                                        <i class="fa fa-users"></i>
                                    </div>
                                </div>
                                <div class="media-body">
                                    <h6 class="mb-0">
                                        {{ $leadMaster->reference != '' ? $leadMaster->reference : 'N/A' }}
                                    </h6>
                                    <small>Reference</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="media">
                                <div class="avatar bg-light-primary rounded me-1">
                                    <div class="avatar-content">
                                        <i class="fa fa-briefcase"></i>
                                    </div>
                                </div>
                                <div class="media-body">
                                    <h6 class="mb-0">{{ $leadMaster->agentSalesPerson->name }}
                                    </h6>
                                    <small>Agent Sales Person</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="media">
                                <div class="avatar bg-light-primary rounded me-1">
                                    <div class="avatar-content">
                                        <i class="fa fa-user"></i>
                                    </div>
                                </div>
                                <div class="media-body">
                                    <h6 class="mb-0">{{ $leadMaster->company->user->name }}
                                        {{ $leadMaster->company->user->last_name }}
                                    </h6>
                                    <small>Assign User</small>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            <div class="card">
                <div class="card-header pb-50">
                    <h4 class="card-title">{{ __('Sales Quotations') }}</h4>

                </div>
                <div class=" card-body">

                    <div class="demo-inline-spacing">
                        <div class="list-group w-100">
                            @if ($salesQuatations->count() > 0)
                                @foreach ($salesQuatations as $key => $value)
                                    <div class="media border p-1">
                                        <a href="{{ route('sales-quatation-pdf', $value->id) }}" target="_blank"
                                            data-bs-toggle="tooltip" data-bs-placement="top"
                                            title="Click to download pdf">
                                            <div class="avatar bg-light-primary rounded me-1">
                                                <div class="avatar-content">
                                                    <i class="fa fa-download"></i>
                                                </div>
                                            </div>
                                        </a>
                                        <div class="media-body">

                                                <h6 class="mb-0">
                                                    {{ env('APP_SORT') }}/EPC/{{ str_pad($value->id, 2, '0', STR_PAD_LEFT) }}
                                                </h6>
                                                <small>
                                                    @if ($value->form_type == 'trading')
                                                        Trading
                                                    @elseif ($value->form_type == 'resident')
                                                        Resident With Subsidy
                                                    @elseif ($value->form_type == 'roof')
                                                        Solar RoofTop
                                                    @endif
                                                </small>

                                        </div>
                                        <div>
                                            <small>{{ date('d M Y h:i A', strtotime($value->created_at)) }}</small>
                                            <br />
                                            @php
                                                $payStatus = getSalesQuotationStatusClass($value->current_status);
                                                $salesQuatationStatus = salesQuotationStatus();
                                                $btn = 'btn-outline-' . $payStatus['class'];
                                                $title = $payStatus['status'];
                                            @endphp
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <div class="text-center p-3">
                                    <p class="text-muteds">Till now, sales quotations have not been generated..</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Timeline Section -->
        <div class="col-12 col-md-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">{{ __('Activity') }}</h4>
                </div>
                <div class="card-body pt-2" style="max-height: 800px; overflow-y: scroll;">
                    @if (count($followUp) > 0)
                        <ul class="timeline">
                            @foreach ($followUp as $key => $value)
                                <li class="timeline-item">
                                    <!-- <span class="timeline-point timeline-point-indicator"></span> -->
                                    <span
                                        class="timeline-point timeline-indicator timeline-indicator-success aos-init aos-animate"
                                        data-aos="zoom-in" data-aos-delay="200">
                                        <i class="fa fa-{{ $value->status->icon_class ?? 'circle' }}"></i>
                                    </span>
                                    <div class="timeline-event">
                                        <div class="d-flex justify-content-between flex-sm-row flex-column mb-sm-0 mb-0">
                                            <h6 class="mb-0">{{ $value->status->name ?? 'New Lead' }}</h6>
                                            <span
                                                class="timeline-event-time me-1">{{ $value->created_at->format('d M Y, h:i A') }}</span>
                                        </div>
                                        @if ($value->call_detail)
                                            <p class="m-0"> <small
                                                    class="text-muteds">{{ __('Call Detail') }}:</small>
                                                {{ $value->call_detail }}</p>
                                        @endif
                                        @if ($value->remark)
                                            <p class="m-0"><small class="text-muteds">{{ __('Remark') }}:</small>
                                                {{ $value->remark }}</p>
                                        @endif
                                        @if ($value->reminder_date)
                                            <div class="mt-50">
                                                <span class="badge rounded-pill badge-light-warning">
                                                    {{ __('Reminder') }}:
                                                    {{ date('d-m-Y h:i A', strtotime($value->reminder_date)) }}
                                                </span>
                                            </div>
                                        @endif
                                        @if ($value->call_recording)
                                            <div class="mt-50">
                                                <audio controls class="w-100">
                                                    <source
                                                        src="{{ asset('upload/recording/' . $value->call_recording) }}"
                                                        type="audio/mp3">
                                                </audio>
                                            </div>
                                        @endif
                                        @if (count($value->image) > 0)
                                            <div class="d-flex flex-wrap gap-1 mt-50">
                                                @foreach ($value->image as $kyImg1 => $valImg1)
                                                    <a href="{{ asset('upload/follow_up_image') . '/' . $valImg1->image }}"
                                                        class="fancybox" data-fancybox="gallery"
                                                        rel="{{ $valImg1->id }}">
                                                        <img src="{{ asset('upload/follow_up_image/thumbnail') . '/' . $valImg1->image }}"
                                                            alt="Image" class="rounded" width="50" height="50"
                                                            style="object-fit: cover;">
                                                    </a>
                                                @endforeach
                                            </div>
                                        @endif
                                        <div class="mb-1">
                                            <small class="text-muted"> <i class="fa fa-user-circle"></i> By
                                                {{ $value->company->user->name }}
                                                {{ $value->company->user->last_name }}</small>
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <div class="text-center p-3">
                            <p class="text-muteds">No lead activity found.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
   </div>
@endsection
