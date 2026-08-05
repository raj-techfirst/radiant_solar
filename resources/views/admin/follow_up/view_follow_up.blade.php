@extends('layouts.app')
@section('title', 'Follow Up')
@section('content')
    <div class="row mt-2">
        <!-- Lead Information Card -->
        <div class="col-12 col-md-6">
            <div class="card">
                <div class="card-header pb-50">
                    <h4 class="card-title">{{ __('Lead Information') }}</h4>
                    <div class="heading-elements d-flex">
                        <span
                            class="badge bg-{{ $leadMaster->leadStatus->class_name ?? '' }} w-100">{{ $leadMaster->leadStatus->name ?? '' }}</span>


						@can('lead-edit')
                            <a href="{{ route('lead.edit', $leadMaster->id) }}" class="avatar bg-light-info p-50 m-0"
                                data-bs-toggle="tooltip" data-placement="left" title="Edit"><i class="fa fa-edit"></i></a>
                        @endcan
						 @if($leadMaster->lead_status_id != 1)
                        @can('lead-delete')
                            <a data-id="{{ $leadMaster->id }}" href="javascript:void(0);"
                                class="delete avatar bg-light-danger p-50 m-0" data-bs-toggle="tooltip" data-placement="left"
                                title="Delete"><i class="fa fa-trash"></i></a>
                        @endcan
						@endif
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
                        <div class="col-md-4">
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
                        <div class="col-md-4">
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



                        <div class="col-md-4">
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

                    <div class="row mt-2">


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
                                    <h6 class="mb-0">{{ $leadMaster->company->user->name ?? '' }}
                                        {{ $leadMaster->company->user->last_name ?? '' }}
                                    </h6>
                                    <small>Assign User</small>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                </div>
            </div>
            <div class="card">
                <div class="card-header pb-50">
                    <h4 class="card-title">{{ __('Sales Quotations') }}</h4>
                    @can('sales-quatation-create')
                        <a role="button" class="btn btn-sm btn-primary float-end"
                            href="{{ route('sales-quatation.create', 'id=' . $leadMaster->id) }}" target="_blank"><i
                                class="fa fa-plus me-25"></i>
                            {{ __('message.Add New') }}</a>
                    @endcan
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
                                            @can('sales-quatation-edit')
                                                <a href="{{ route('sales-quatation.edit', $value->id) }}" target="_blank"
                                                    data-bs-toggle="tooltip" data-bs-placement="top" title="Click to revise">
                                                @endcan
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
                                                @can('sales-quatation-edit')
                                                </a>
                                            @endcan
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


                                            @can('sales-quatation-edit')
                                                <div class="btn-group p-0">
                                                    <button type="button" data-bs-toggle="tooltip" data-bs-placement="top"
                                                        title="Click to change status"
                                                        class="btn-xs w-100 btn {{ $btn }}">{{ $title }}</button>
                                                    <button type="button"
                                                        class="btn-xs btn pe-1 {{ $btn }} dropdown-toggle dropdown-toggle-split"
                                                        data-bs-toggle="dropdown" aria-expanded="false">
                                                        <span class="visually-hidden">Toggle Dropdown</span>
                                                    </button>
                                                    <div class="dropdown-menu" container="body">
                                                        @foreach ($salesQuatationStatus as $k => $v)
                                                            <a class="dropdown-item change-status" href="javascript:void(0);"
                                                                data-id="{{ $value->id }}"
                                                                data-status="{{ $v['id'] }}">{{ $v['name'] }}</a>
                                                        @endforeach
                                                    </div>
                                                </div>
                                                 @endcan
                                                @cannot('sales-quatation-edit')
                                                <span class="btn btn-xs {{ $btn }}">{{ $title }}</span>
                                            @endcannot


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
                    <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal"
                        data-bs-target="#exampleModal">
                        <i class="fa fa-plus"></i> Add Activity
                    </button>
                </div>
                <div class="card-body pt-2" style="height: 800px; overflow-y: scroll;">
                    @if (count($followUp) > 0)
                        <ul class="timeline">
                            @foreach ($followUp as $key => $value)
                                <li class="timeline-item">
                                    <!-- <span class="timeline-point timeline-point-indicator"></span> -->
                                    <span class="timeline-point timeline-indicator timeline-indicator-success aos-init aos-animate"
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

    <div class="modal fade" id="exampleModal" aria-labelledby="exampleModalLabel" tabindex="-2">
        <div class="modal-dialog modal-dialog-centered ">
            <div class="modal-content">
                <div class="modal-header bg-transparent border-bottom">
                    <h4 class="text-center mb-0" id="exampleModalTitle">{{ __('Add Follow Up') }}</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-2" id="body">
                    <!-- Add Follow Up Form -->
                    @can('follow-up-create')
                        @if ($leadMaster->status != '1' && $leadMaster->status != '2')

                            <form id="form" method="POST" action="javascript:void(0);" enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" name="lead_master_id" value="{{ $leadMaster->id }}">

                                <div class="mb-1 custom-input-group">
                                    <label class="form-label" for="call_detail">{{ __('Call Detail') }} <span
                                            class="text-danger">*</span></label>
                                    <textarea id="call_detail" class="form-control" name="call_detail" rows="3"
                                        placeholder="{{ __('Type here..') }}"></textarea>
                                    <span class="invalid-feedback d-block" id="error_call_detail" role="alert"></span>
                                </div>

                                <div class="mb-1">
                                    <label class="form-label" for="remark">{{ __('Remark') }}</label>
                                    <textarea id="remark" class="form-control" name="remark" rows="2" placeholder="{{ __('Type here..') }}"></textarea>
                                </div>

                                <div class="mb-1 custom-input-group">
                                    <label class="form-label" for="images">{{ __('Image') }}</label>
                                    <input type="file" multiple class="form-control" name="images" id="images" />
                                    <span class="invalid-feedback d-block" id="error_images" role="alert"></span>
                                </div>

                                <div class="mb-1 custom-input-group">
                                    <label class="form-label" for="reminder_date">{{ __('Reminder') }}</label>
                                    <input type="text" class="form-control flatpickr-date-time reminder_date"
                                        name="reminder_date" id="reminder_date" autocomplete="off"
                                        placeholder="{{ __('Reminder Date') }}" readonly>
                                    <span class="invalid-feedback d-block" id="error_reminder_date" role="alert"></span>
                                </div>
                                <div class="mb-1 custom-input-group">
                                    <label class="form-label" for="lead_status">{{ __('message.Status') }} <span
                                            class="text-danger">*</span></label>
                                    <select class="form-control form-select select2 custom-select2 lead_status"
                                        name="lead_status" id="lead_status">
                                        <option value="" selected>Select Status</option>
                                        @if ($leadStatus->count() > 0)
                                            @foreach ($leadStatus as $value)
                                                <option value="{{ $value->id }}"
                                                    {{ isset($leadMaster) && $leadMaster->lead_status_id == $value->id ? 'selected' : '' }}>
                                                    {{ $value->name }}
                                                </option>
                                            @endforeach
                                        @endif

                                    </select>
                                    <span class="invalid-feedback d-block" id="error_lead_status" role="alert"></span>
                                </div>

                                @if (Auth::user()->roles[0]->name != 'Sales')
                                <div class="mt-1 custom-input-group">
                                    <label class="form-label" for="assign_id">{{ __('message.Assigned') }} <span
                                            class="text-danger">*</span></label>
                                    <select class="form-control form-select select2 custom-select2 assign_id anlayst"
                                        name="assign_id" id="assign_id">
                                        <option selected disabled>{{ __('message.-- Select --') }}</option>
                                        @foreach ($companyProfile as $value)
                                            <option value="{{ $value->id }}"
                                                {{ isset($leadMaster) && $leadMaster->assign_id == $value->id ? 'selected' : '' }}>
                                                {{ $value->user->name }} {{ $value->user->last_name }}</option>
                                        @endforeach
                                    </select>
                                    <span class="invalid-feedback d-block" id="error_assign_id" role="alert"></span>
                                </div>
                            @endif

                                <div class="mt-1">
                                    <button type="submit" class="btn btn-primary next save" data-status="3">Save</button>

                                </div>
                            </form>

                        @endif
                    @endcan
                </div>
            </div>
        </div>
    </div>

@endsection

@section('pagescript')
    <script type="text/javascript">
        'use strict';
        $(document).ready(function() {
            var date = new Date();

            flatpickr('.reminder_date', {
                enableTime: true,
                dateFormat: 'd-m-Y H:i',
                defaultDate: '',
                minDate: 'today',
            });

        });

        $(document).on('click', '.change-status', function() {
            let status = $(this).data('status');
            let id = $(this).data('id');
            var url = "{{ route('sales-quatation-status') }}";
            Swal.fire({
                    title: "{{ __('message.Are you sure?') }}",
                    text: "{{ __('message.You won`t be able to revert this!') }}",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: "Yes, Change it!",
                    customClass: {
                        confirmButton: 'btn btn-primary',
                        cancelButton: 'btn btn-outline-danger ms-1'
                    },
                    buttonsStyling: false
                })
                .then(function(result) {
                    if (result.value) {
                        $.ajax({
                            url: url,
                            type: 'POST',
                            datatype: 'json',
                            data: {
                                "id": id,
                                "status": status,
                                "_token": "{{ csrf_token() }}",
                            },
                            success: function(response) {
                                if (response.status) {
                                    setTimeout(function() {
                                        location.reload(true);
                                    }, 500);
                                    toastr.success(response.message, 'Success');
                                } else {
                                    toastr.error(response.server_error, 'Opps!');

                                }
                            }
                        });
                    } else {
                        Swal.fire({
                            text: "{{ __('message.Your data is safe.') }}"
                        });
                    }
                });
        });


        $(document).on('click', '.save', function() {
            var status = $(this).data('status');
            $("<input />").attr("type", "hidden")
                .attr("name", "status")
                .attr("value", status)
                .appendTo("#form");
            if (status == 3) {
                var name = "next";
                var text = "{{ __('Save') }}";
            } else if (status == 2) {
                var name = "close";
                var text = "{{ __('Not Interested') }}";
            } else {
                var name = "done";
                var text = "{{ __('Completed') }}";
            }
            var formData = new FormData($("#form")[0]);
            if (true) {
                $.ajax({
                    type: "POST",
                    url: "{{ route('follow-up.store') }}",
                    data: formData,
                    dataType: 'json',
                    cache: false,
                    contentType: false,
                    processData: false,
                    beforeSend: function() {
                        $("." + name).html(
                            `<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> {{ __('Wait') }}`
                        );
                        $("." + name).attr('disabled', true);
                    },
                    success: function(response) {
                        $("." + name).html(text);
                        $("." + name).attr('disabled', false);
                        if (response.server_error && response.status == false) {
                            toastr.error("{{ __('Something went wrong. Please try again.') }}",
                                "{{ __('Error') }}");
                        } else if (response.status == false) {
                            $.each(response.errors, function(key, value) {
                                $('#error_' + key).html('<p class="text-danger mb-0">' + value +
                                    '</p>');
                            });
                            toastr.warning("{{ __('Please input proper data.') }}",
                                "{{ __('Warning') }}");
                        } else {
                            $('#form')[0].reset();
                            toastr.success("{{ __('Saved successfully.') }}", "{{ __('Success') }}");
                            setTimeout(function() {
                                location.reload(true);
                            }, 1500);
                        }
                    }
                });
            } else {
                $("#form").validate({
                    rules: {
                        reminder_date: {
                            required: true,
                            date: false
                        },
                        call_detail: {
                            required: true,
                        },
                         assign_id: {
                            required: true,
                        },
                         lead_status: {
                            required: true,
                        }
                    },
                    messages: {
                        reminder_date: {
                            required: "{{ __('Enter reminder date') }}",
                        },
                        call_detail: {
                            required: "{{ __('Enter call detail') }}",
                        },
                        assign_id: {
                            required: "Please select assign person",
                        },
                        lead_status: {
                            required: "Please select status",
                        },
                    },
                    errorElement: "p",
                    errorClass: "text-danger mb-0",

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
            }
        });

        $(document).on('click', '.delete', function() {
            var btn = $(this);
            var id = btn.data('id');
            var leadUrl = "{{ route('lead.index') }}";
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
                        axios.delete(leadUrl + '/' + id)
                            .then(function(response) {
                                if (response.data.status == true) {
                                    toastr.success("{{ __('message.Deleted successfully.') }}",
                                        "{{ __('message.Success') }}");
                                    window.location = leadUrl;
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
    </script>
@endsection
