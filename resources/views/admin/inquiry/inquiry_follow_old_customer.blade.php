@extends('layouts.app')
@section('title', 'Complaint Management')
@section('content')
    <style>
        .table tr td {
            padding: 3px;
        }

        .col-lg-12.mt-50.mb-50 {
            background-color: #f3f3f3;
            padding-top: 10px;
        }
    </style>

    <div class="content-wrapper container-xxl p-0 pt-2">
        <div class="content-body">
            <div class="row">
                <div class="col-lg-8">
                    <div class="card border-warning">


                         <div class="card-hrader border-bottom-warning p-1 pb-50 fw-bold">
                            <div class="d-flex align-items-between">
                                <div class="w-50 d-flex align-items-between">
                                    <div>
                                    <h4 class="font-black" style="font-weight: bold;color: var(--ck-color-base-text);">
                                        Complaint Details  </h4></div>
                                        <div>
                                        <span class="badge bg-light-warning">Old Consumer</span>
                                        </div>


                                </div>
                                <div class="w-50">
                                    @php $payStatus = getServiceStatusClass($inquiry->status ?? 'new_service'); @endphp
                                    <span
                                        class="badge bg-light-{{ $payStatus['class'] }} float-end">{{ $payStatus['status'] }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="card-body">
                            <div class="row">
                                <div class="col-lg-12 mt-50 mb-50">
                                    <h2 class="font-black" style="font-weight: bold;color: var(--ck-color-base-text);">
                                        Complaint Details</h2>
                                    <hr class="m-0" />
                                </div>

                                <div class="col-lg-12">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td> <span class="h6">Consumer No. :</span></td>
                                            <td> <span class="text-dark ms-1"><b>{{ $inquiry->consumer_number ?? 'N/A' }}</b></span></td>

                                             <td> <span class="h6">Contact No. :</span></td>
                                            <td> <span class="text-dark ms-1"><b>{{ $inquiry->contact_number ?? 'N/A' }}</b></span></td>

                                        </tr>
                                        <tr>
                                           <td> <span class="h6">Consumer Name :</span></td>
                                            <td colspan="3"> <span class="text-dark ms-1"><b>{{ $inquiry->consumer_name ?? 'N/A' }}</b></span></td>

                                        </tr>
                                        <tr>
                                            <td> <span class="h6">Problem :</span></td>
                                            <td  colspan="3"> <span class="text-dark ms-1"><b>{{ $inquiry->problem ?? 'N/A' }}</b></span></td>
                                        </tr>
                                        <tr>
                                            <td> <span class="h6">Date :</span></td>
                                            <td  colspan="3"> <span class="text-dark ms-1"><b>{{ date('d-m-Y', strtotime($inquiry->created_at)) }}</b></span></td>
                                        </tr>
                                    </table>
                                </div>

                                @if(!is_null($inquiry->image))
                                <div class="col-lg-12 mt-1">
                                    <span class="h6">Image :</span>
                                    <div class="mt-50">
                                        <a href="{{ asset('upload/inquiry/' . $inquiry->image) }}" data-fancybox="gallery_{{ $inquiry->id }}" data-caption="{{ $inquiry->consumer_name }}">
                                            <img class="img-fluid rounded" height="100" width="100" src="{{ asset('upload/inquiry/' . $inquiry->image) }}" alt="{{ $inquiry->consumer_name }}">
                                        </a>
                                    </div>
                                </div>
                                @endif


                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card border-success">
                        <div class="card-header">
                            <h4 class="card-title">{{ __('Activity') }}</h4>
                            <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal"
                                data-bs-target="#exampleModal">
                                <i class="fa fa-plus"></i> Add Activity
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="row justify-content-center mt-2">
                                <div class="col-12">
                                    @if (count($followUp) > 0)
                                        <ul class="timeline">
                                            @foreach ($followUp as $key => $value)
                                               @php $payStatus = getServiceStatusClass($value->status); @endphp
                                                <li class="timeline-item">
                                                    <span
                                                        class="timeline-point timeline-indicator timeline-indicator-success aos-init aos-animate"
                                                        data-aos="zoom-in" data-aos-delay="200">
                                                        <i class="fa fa-{{ $payStatus['icon'] }}"></i>
                                                    </span>
                                                    <div class="timeline-event">
                                                        <div
                                                            class="d-flex justify-content-between flex-sm-row flex-column mb-sm-0 mb-0">
                                                            <h6 class="mb-0">{{ $payStatus['status'] }}</h6>
                                                            <span
                                                                class="timeline-event-time me-1">{{ $value->created_at->format('d-m-Y') }}</span>
                                                        </div>
                                                        @if ($value->call_detail)
                                                            <p class="m-0"> <small
                                                                    class="text-muteds">{{ __('Call Detail') }}:</small>
                                                                {{ $value->call_detail }}</p>
                                                        @endif
                                                        @if ($value->remark)
                                                            <p class="m-0"><small
                                                                    class="text-muteds">{{ __('Remark') }}:</small>
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

                                                        @if ($value->image != "")
                                                            <div class="d-flex flex-wrap gap-1 mt-50">
                                                                <a href="{{ asset('upload/inquiry') . '/' . $value->image }}"
                                                                    class="fancybox" data-fancybox="gallery"
                                                                    rel="{{ $value->id }}">
                                                                    <img src="{{ asset('upload/inquiry') . '/' . $value->image }}"
                                                                        alt="Image" class="rounded" width="50" height="auto">
                                                                </a>
                                                            </div>
                                                        @endif

                                                        @if ($value->assignPerson)
                                                            <p class="m-0"><small class="text-muteds">Assign To:</small>
                                                                <span class="badge bg-light-primary">{{ $value->assignPerson->name }}</span>
                                                            </p>
                                                        @endif
                                                        <div class="mb-1">
                                                            <small class="text-muted"> <i class="fa fa-user-circle"></i>
                                                                By
                                                                {{ ucfirst(Auth::user()->name) }}
                                                            </small>
                                                        </div>
                                                    </div>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @else
                                        <div class="text-center p-3">
                                            <p class="text-muteds">No activity found.</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header pb-0 bg-transparent">
                    <h4 class="text-center mb-0" id="exampleModalTitle"> Add Follow Up</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="form" method="POST" action="javascript:void(0);" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <hr>
                            <input type="hidden" name="inquiry_id" value="{{ $inquiry->id }}">
                            <input type="hidden" name="consumer_flag" value="old">
                            <div class="col-md-12 form-group mb-1 custom-input-group">
                                <label class="form-label" for="call_detail">{{ __('message.Call Detail') }} <span
                                        class="text-danger">*</span></label>
                                <textarea id="call_detail" class="form-control" name="call_detail" rows="3"
                                    placeholder="{{ __('message.Type here..') }}"></textarea>
                                <span class="invalid-feedback d-block" id="error_call_detail" role="alert"></span>
                            </div>
                            <div class="col-md-12 form-group mb-1">
                                <label class="form-label" for="remark">{{ __('message.Remark') }}</label>
                                <textarea id="remark" class="form-control" name="remark" rows="3"
                                    placeholder="{{ __('message.Type here..') }}"></textarea>
                            </div>

                            <div class="col-12 col-md-12 col-lg-6 mb-1 custom-input-group">
                                <label class="form-label" for="images">{{ __('Image') }}</label>
                                <input type="file" multiple class="form-control" name="images" id="images" />
                                <span class="invalid-feedback d-block" id="error_images" role="alert"></span>
                            </div>

                            <div class="col-12 col-md-12 col-lg-6 mb-1 custom-input-group">
                                <label class="form-label" for="reminder_date">{{ __('message.Reminder') }}</label>
                                <input type="text" class=" form-control flatpickr-date-time remimnder_date"
                                    name="reminder_date" id="reminder_date" autocomplete="off"
                                    placeholder="{{ __('message.Reminder Date') }}" readonly>
                                <span class="invalid-feedback d-block" id="error_reminder_date" role="alert"></span>
                            </div>

                            <div class="col-12 col-md-12 col-lg-6 mb-1 custom-input-group">
                                <label class="form-label" for="status">{{ __('message.Status') }} <span
                                        class="text-danger">*</span></label>
                                <select class="form-control form-select select2 custom-select2 status" name="status"
                                    id="status">
                                    @php $serviceStatus = serviceStatus();  @endphp
                                    @foreach ($serviceStatus as $key => $value)
                                        <option value="{{ $value['id'] }}">{{ $value['name'] }}</option>
                                    @endforeach
                                </select>
                                <span class="invalid-feedback d-block" id="error_status" role="alert"></span>
                            </div>

                            <div class="col-12 col-md-12 col-lg-6 mb-1 custom-input-group">
                                <label class="form-label" for="assign_person_id">Assign Person <span
                                        class="text-danger">*</span></label>
                                <select class="form-control form-select select2 custom-select2" name="assign_person_id"
                                    id="assign_person_id" required>
                                    <option value="">Select Any</option>
                                    @foreach ($agentSalesPerson as $value)
                                        <option value="{{ $value->id }}" {{ (isset($lastAssignPersonId) && $lastAssignPersonId == $value->id) ? 'selected' : '' }}>{{ $value->name }}</option>
                                    @endforeach
                                </select>
                                <span class="invalid-feedback d-block" id="error_assign_person_id" role="alert"></span>
                            </div>

                            <div class="col-12 col-md-12">
                                <button type="submit" class="btn btn-primary px-5 save">Save</button>
                            </div>

                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('pagescript')
    <script type="text/javascript">
        'use strict';
        $("#form").validate({
            rules: {
                call_detail: {
                    required: true,
                },
                status: {
                    required: true,
                }
            },
            messages: {
                call_detail: {
                    required: "{{ __('Enter call detail') }}",
                },
                status: {
                    required: "Please select status",
                },
            },
            errorElement: "small",
            errorClass: "text-danger mb-0 custom-error",
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
        $(document).on('click', '.save', function() {
            if ($("#form").valid()) {
                var formData = new FormData($("#form")[0]);
                $.ajax({
                    type: "POST",
                    url: "{{ route('inquiry-follow-up.store') }}",
                    data: formData,
                    dataType: 'json',
                    cache: false,
                    contentType: false,
                    processData: false,
                    beforeSend: function() {
                        $("#error_name").html(' ');
                        $(".save").html(
                            `<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> {{ __('message.Wait') }}`
                        );
                        $(".save").attr('disabled', true);
                    },
                    success: function(response) {
                        $(".save").html("{{ __('message.Submit') }}");
                        $(".save").attr('disabled', false);
                        if (response.status_code == 500) {
                            toastr.error(response.message, "{{ __('message.Error') }}");
                        } else if (response.status_code == 403) {
                            toastr.warning(response.message, "{{ __('message.Warning') }}");
                        } else if (response.status_code == 201) {
                            $.each(response.errors, function(key, value) {
                                $('#error_' + key).html('<p class="text-danger mb-0">' + value +
                                    '</p>');
                            });
                            toastr.warning(response.message, "{{ __('message.Warning') }}");
                        } else {
                            $('#form')[0].reset();
                            toastr.success(response.message, "{{ __('message.Success') }}");
                            setTimeout(function() {
                               location.href = response.data;
                            }, 100);
                        }
                    }
                });
            } else {
                return false;
            }
        });
    </script>
@endsection
