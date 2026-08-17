@extends('layouts.app')
@section('title', 'Sales Order')
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
            <div class="col-lg-12">
                @can('sales-master-edit')
                    <a class="btn btn-sm bg-info waves-effect text-white" data-bs-toggle="tooltip" data-placement="left"
                        title="Edit" href="{{ route('sales-master.edit', $salesMaster->id) }}">
                        <i class="fa fa-edit"></i> Edit
                    </a>
                    <button type="button" data-id="{{ $salesMaster->id }}"
                        class="btn btn-sm bg-warning waves-effect status-view text-white" data-bs-toggle="tooltip"
                        data-placement="left" title="Form">
                        <i class="fa fa-stack-exchange"></i> Form
                    </button>
                @endcan
                @can('sales-master-delete')
                    <a data-id="{{ $salesMaster->id }}" href="javascript:void(0);"
                        class="btn btn-sm bg-danger waves-effect text-white delete" data-bs-toggle="tooltip"
                        data-placement="left" title="Delete"><i class="fa fa-trash"></i> Delete </a>
                @endcan
                @can('sales-master-edit')
                    @if ($salesMaster->hold_query == '1')
                        <a href="javascript:void(0)" data-id="{{ $salesMaster->id }}" data-status="hold_query"
                            class="btn btn-sm bg-light-danger float-end waves-effect text-danger remove-status"
                            data-bs-toggle="tooltip" data-placement="left" title="Click to remove status"
                            style="margin-right: 100px;"><i class="fa fa-close"></i> Remove From Hold / Query </a>
                    @endif
                    @if ($salesMaster->file_cancel_order == '1')
                        <a href="javascript:void(0)" data-id="{{ $salesMaster->id }}" data-status="file_cancel_order"
                            class="btn btn-sm bg-light-danger float-end waves-effect text-danger remove-status"
                            data-bs-toggle="tooltip" data-placement="left" title="Click to remove status"
                            style="margin-right: 100px;"><i class="fa fa-close"></i> Remove From File Cancel Order </a>
                    @endif
                @endcan
            </div>
            <div
                class="card @if ($salesMaster->hold_query != '1' && $salesMaster->file_cancel_order != '1') border-success @endif
                @if ($salesMaster->file_cancel_order == '1') border-danger gray @endif @if ($salesMaster->hold_query == '1') border-warning gray @endif">
                @if ($salesMaster->hold_query != '1' && $salesMaster->file_cancel_order != '1')
                    <div class="card-hrader border-bottom-success p-1 fw-bold">
                        <b class="fs-5"> <i class='fa fa-user'></i> </b> <span class="fs-4"
                            style="font-weight: 900;">{{ $salesMaster->consumer_number }}</span>
                        {{ $salesMaster->consumer_name }}
                    </div>
                @endif
                @if ($salesMaster->hold_query == '1')
                    @if ($salesMaster->remark != '')
                        <div class="card-hrader border-bottom-warning p-1 fw-bold">
                            <b class="fs-5"> <i class='fa fa-user'></i> </b> <span class="fs-4"
                                style="font-weight: 900;">{{ $salesMaster->consumer_number }}</span>
                            {{ $salesMaster->consumer_name }} <span class="pe-5 float-end"> <b class="ps-2">Remark :
                                </b>{{ $salesMaster->remark }} </span>
                        </div>
                    @endif
                    <div class="ribbon warning"><span>Hold / Query</span></div>
                @endif
                @if ($salesMaster->file_cancel_order == '1')
                    @if ($salesMaster->remark != '')
                        <div class="card-hrader border-bottom-danger p-1 fw-bold">
                            <b class="fs-5"> <i class='fa fa-user'></i> </b> <span class="fs-4"
                                style="font-weight: 900;">{{ $salesMaster->consumer_number }}</span>
                            {{ $salesMaster->consumer_name }} <span class="pe-5 float-end"> <b class="ps-2">Remark :
                                </b>{{ $salesMaster->remark }} </span>
                        </div>
                    @endif
                    <div class="ribbon red"><span>File Cancel</span></div>
                @endif
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-12 mb-50">
                            <h2 class="font-black" style="font-weight: bold;color: var(--ck-color-base-text);">Application
                                Details</h2>
                            <hr class="m-0" />
                        </div>
                        <div class="col-10 col-lg-10">
                            <div class="row">
                                <!--  First -->
                                <div class="col-lg-4">
                                    <div class="info-container">
                                        <ul class="list-unstyled mb-50">
                                            <li class="mb-50 d-flex align-items-center">
                                                <span class="h6">Consumer No. : </span>
                                                <span
                                                    class="text-dark ms-1"><b>{{ $salesMaster->consumer_number != '' && $salesMaster->consumer_number != null ? $salesMaster->consumer_number : 'N/A' }}</b></span>
                                            </li>
                                        </ul>

                                    </div>
                                </div>
                                <!-- / First -->
                                <!-- Second -->
                                <div class="col-lg-8">
                                    <div class="info-container">
                                        <ul class="list-unstyled mb-50">
                                            <li class="mb-1  d-flex align-items-center">
                                                <span class="h6">Consumer Name : </span>
                                                <span
                                                    class="text-dark  ms-1"><b>{{ $salesMaster->consumer_name != '' && $salesMaster->consumer_name != null ? $salesMaster->consumer_name : 'N/A' }}</b></span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                <!-- / Second -->
                                <!-- Third -->
                                <div class="col-lg-4">
                                    <div class="info-container">
                                        <ul class="list-unstyled mb-50">
                                            <li class="mb-50 d-flex align-items-center">
                                                <span class="h6">Register KW : </span>
                                                <span
                                                    class="text-dark  ms-1"><b>{{ $salesMaster->register_kw != '' && $salesMaster->register_kw != null ? $salesMaster->register_kw : 'N/A' }}</b></span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                <!-- / Third -->

                                <!-- Third -->
                                <div class="col-lg-4">
                                    <div class="info-container">
                                        <ul class="list-unstyled mb-50">
                                            <li class="mb-1  d-flex align-items-center">
                                                <span class="h6">Consumer Type : </span>
                                                <span
                                                    class="text-dark  ms-1"><b>{{ $salesMaster->consumer_type != '' && $salesMaster->consumer_type != null ? $salesMaster->consumer_type : 'N/A' }}</b></span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                <!-- / Third -->
                                <!-- Third -->
                                <div class="col-lg-4"></div>
                                <div class="col-lg-4">
                                    <div class="info-container">
                                        <ul class="list-unstyled mb-50">
                                            <li class="mb-1  d-flex align-items-center">
                                                <span class="h6">R. Portal : </span>
                                                <span
                                                    class="text-dark  ms-1"><b>{{ $salesMaster->ragistration_portal != '' && $salesMaster->ragistration_portal != null ? $salesMaster->ragistration_portal : 'N/A' }}</b></span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                <!-- / Third -->
                                <!-- Third -->
                                <div class="col-lg-4">
                                    <div class="info-container">
                                        <ul class="list-unstyled mb-50">
                                            <li class="mb-1  d-flex align-items-center">
                                                <span class="h6">R. No. : </span>
                                                <span
                                                    class="text-dark  ms-1"><b>{{ $salesMaster->ragistration_number != '' && $salesMaster->ragistration_number != null ? $salesMaster->ragistration_number : 'N/A' }}</b></span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                <!-- / Third -->
                                <!-- Third -->
                                <div class="col-lg-3">
                                    <div class="info-container">
                                        <ul class="list-unstyled mb-50">
                                            <li class="mb-1  d-flex align-items-center">
                                                <span class="h6">R. Date : </span>
                                                <span
                                                    class="text-dark  ms-1"><b>{{ $salesMaster->ragistration_date != '' && $salesMaster->ragistration_date != null && $salesMaster->ragistration_date != '0000-00-00' && $salesMaster->ragistration_date != '1970-01-01' ? date('d-m-Y', strtotime($salesMaster->ragistration_date)) : 'N/A' }}</b></span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                <!-- / Third -->
                                <!-- Third -->
                                <div class="col-lg-4">
                                    <div class="info-container">
                                        <ul class="list-unstyled mb-50">
                                            <li class="mb-1  d-flex align-items-center">
                                                <span class="h6">DISCOM Sr.No. : </span>
                                                <span
                                                    class="text-dark  ms-1"><b>{{ $salesMaster->feasibility_discom_sr_number != '' && $salesMaster->feasibility_discom_sr_number != null ? $salesMaster->feasibility_discom_sr_number : 'N/A' }}</b></span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                <!-- / Third -->
                                <!-- Third -->
                                <div class="col-lg-4">
                                    <div class="info-container">
                                        <ul class="list-unstyled mb-50">
                                            <li class="mb-1  d-flex align-items-center">
                                                <span class="h6">F. Amount : </span>
                                                <span
                                                    class="text-dark  ms-1"><b>{{ $salesMaster->feasibility_amount != '' && $salesMaster->feasibility_amount != null ? $salesMaster->feasibility_amount : 'N/A' }}</b></span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                <!-- / Third -->

                                <!-- Third -->
                                <div class="col-lg-3">
                                    <div class="info-container">
                                        <ul class="list-unstyled mb-50">
                                            <li class="mb-1  d-flex align-items-center">
                                                <span class="h6">F. Date : </span>
                                                <span
                                                    class="text-dark  ms-1"><b>{{ $salesMaster->feasibility_date != '' && $salesMaster->feasibility_date != null && $salesMaster->feasibility_date != '1970-01-01' && $salesMaster->feasibility_date != '0000-00-00' ? date('d-m-Y', strtotime($salesMaster->feasibility_date)) : 'N/A' }}</b></span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                <!-- / Third -->

                                <!-- Third -->
                                <div class="col-lg-4">
                                    <div class="info-container">
                                        <ul class="list-unstyled mb-50">
                                            <li class="mb-1  d-flex align-items-center">
                                                <span class="h6">Payment Ref. Number : </span>
                                                <span
                                                    class="text-dark  ms-1"><b>{{ $salesMaster->feasibility_date != '' && $salesMaster->payment_ref_number != null ? $salesMaster->payment_ref_number : 'N/A' }}</b></span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                <!-- / Third -->

                                <!-- Third -->
                                <div class="col-lg-4">
                                    <div class="info-container">
                                        <ul class="list-unstyled mb-50">
                                            <li class="mb-1  d-flex align-items-center">
                                                <span class="h6">Payment Date : </span>
                                                <span
                                                    class="text-dark  ms-1"><b>{{ $salesMaster->installation_date != '' && $salesMaster->installation_date != null && $salesMaster->installation_date != '0000-00-00' && $salesMaster->installation_date != '1970-01-01' ? date('d-m-Y', strtotime($salesMaster->installation_date)) : 'N/A' }}</b></span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                <!-- / Third -->

                                <div class="col-lg-12 mt-50 mb-50">
                                    <h5 class="font-black" style="font-weight: bold;color: var(--ck-color-base-text);">
                                        <i class="fa fa-address-card pe-50"></i> Contact Details
                                    </h5>
                                    <hr class="m-0" />
                                </div>

                                <!--  Contact First -->
                                <div class="col-lg-4">
                                    <div class="info-container">
                                        <ul class="list-unstyled mb-50">
                                            <li class="mb-50 d-flex align-items-center">
                                                <span class="h6">Contact No. :</span>
                                                <span
                                                    class="text-dark  ms-1"><b>{{ $salesMaster->contact_number != '' && $salesMaster->contact_number != null ? $salesMaster->contact_number : 'N/A' }}</b></span>
                                            </li>
                                        </ul>

                                    </div>
                                </div>
                                <!-- /  Contact First -->
                                <!-- Contact Second -->
                                <div class="col-lg-4">
                                    <div class="info-container">
                                        <ul class="list-unstyled mb-50">
                                            <li class="mb-50 d-flex align-items-center">
                                                <span class="h6">Email : </span>
                                                <span
                                                    class="text-dark  ms-1"><b>{{ $salesMaster->email != '' && $salesMaster->email != null ? $salesMaster->email : 'N/A' }}</b></span>
                                            </li>

                                        </ul>
                                    </div>
                                </div>
                                <!-- / Contact Second -->

                                <!--  Contact Address -->
                                <div class="col-lg-12">
                                    <div class="info-container">
                                        <ul class="list-unstyled mb-50">
                                            <li class="mb-1  d-flex align-items-center">
                                                <span class="h6">Address : </span>
                                                <span
                                                    class="text-dark  ms-1"><b>{{ $salesMaster->address != '' && $salesMaster->address != null ? $salesMaster->address : 'N/A' }}</b></span>
                                            </li>

                                        </ul>

                                    </div>
                                </div>
                                <!-- /  Contact First -->

                                <!--  Contact State First -->
                                <div class="col-lg-4">
                                    <div class="info-container">
                                        <ul class="list-unstyled mb-50">
                                            <li class="mb-50 d-flex align-items-center">
                                                <span class="h6">State : </span>
                                                <span
                                                    class="text-dark  ms-1"><b>{{ $salesMaster->district != '' && $salesMaster->district->state != null ? $salesMaster->district->state->state_name : 'N/A' }}</b></span>
                                            </li>
                                        </ul>

                                    </div>
                                </div>
                                <!-- /  Contact State First -->
                                <!--  Contact District First -->
                                <div class="col-lg-4">
                                    <div class="info-container">
                                        <ul class="list-unstyled mb-50">
                                            <li class="mb-50 d-flex align-items-center">
                                                <span class="h6">District : </span>
                                                <span
                                                    class="text-dark  ms-1"><b>{{ $salesMaster->district->name != '' && $salesMaster->district->name != null ? $salesMaster->district->name : 'N/A' }}</b></span>
                                            </li>
                                        </ul>

                                    </div>
                                </div>
                                <!-- /  Contact District First -->
                                <!-- Contact Taluka Second -->
                                <div class="col-lg-4">
                                    <div class="info-container">
                                        <ul class="list-unstyled mb-50">

                                            <li class="mb-50 d-flex align-items-center">
                                                <span class="h6">Taluka : </span>
                                                <span
                                                    class="text-dark  ms-1"><b>{{ $salesMaster->taluka->name != '' && $salesMaster->taluka->name != null ? $salesMaster->taluka->name : 'N/A' }}</b></span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                <!-- / Contact Taluka Second -->
                                <div class="col-lg-4">
                                    <div class="info-container">
                                        <ul class="list-unstyled mb-50">
                                            <li class="mb-1  d-flex align-items-center">
                                                <span class="h6">Village : </span>
                                                <span
                                                    class="text-dark  ms-1"><b>{{ $salesMaster->village_id != '' && $salesMaster->village_id != null && $salesMaster->village_id != 0 ? $salesMaster->village_id : 'N/A' }}</b></span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>

                                <!-- Contact Pincode Third -->
                                <div class="col-lg-3">
                                    <div class="info-container">
                                        <ul class="list-unstyled mb-50">
                                            <li class="mb-1  d-flex align-items-center">
                                                <span class="h6">Pincode : </span>
                                                <span
                                                    class="text-dark  ms-1"><b>{{ $salesMaster->pin_code != '' && $salesMaster->pin_code != null ? $salesMaster->pin_code : 'N/A' }}</b></span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                <!-- / Contact Pincode Third -->

                                <div class="col-lg-12 mt-50 mb-50">
                                    <h5 class="font-black" style="font-weight: bold;color: var(--ck-color-base-text);">
                                        <i class="fa fa-university pe-50"></i> Bank Details
                                    </h5>
                                    <hr class="m-0" />
                                </div>

                                <!--  Bank First -->
                                <div class="col-lg-4">
                                    <div class="info-container">
                                        <ul class="list-unstyled mb-50">
                                            <li class="mb-1  d-flex align-items-center">
                                                <span class="h6">Bank Name : </span>
                                                <span
                                                    class="text-dark ms-1"><b>{{ $salesMaster->bank_name != '' && $salesMaster->bank_name != null ? $salesMaster->bank_name : 'N/A' }}</b></span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                <!-- / Bank First -->
                                <!-- Bank Second -->
                                <div class="col-lg-4">
                                    <div class="info-container">
                                        <ul class="list-unstyled mb-50">
                                            <li class="mb-1  d-flex align-items-center">
                                                <span class="h6">Bank Account : </span>
                                                <span
                                                    class="text-dark ms-1"><b>{{ $salesMaster->bank_account != '' && $salesMaster->bank_account != null ? $salesMaster->bank_account : 'N/A' }}</b></span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                <!-- / Bank Second -->
                                <!-- Bank Third -->
                                <div class="col-lg-4">
                                    <div class="info-container">
                                        <ul class="list-unstyled mb-50">
                                            <li class="mb-1  d-flex align-items-center">
                                                <span class="h6">IFSC Code : </span>
                                                <span
                                                    class="text-dark ms-1"><b>{{ $salesMaster->ifsc_code != '' && $salesMaster->ifsc_code != null ? $salesMaster->ifsc_code : 'N/A' }}</b></span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                <!-- / Bank Third -->

                                <div class="col-lg-12 mt-50 mb-50">
                                    <h5 class="font-black" style="font-weight: bold;color: var(--ck-color-base-text);">
                                        <i class="fa fa-home pe-50"></i> Division Details
                                    </h5>
                                    <hr class="m-0" />
                                </div>

                                <!--  Division First -->
                                <div class="col-lg-4">
                                    <div class="info-container">
                                        <ul class="list-unstyled mb-50">
                                            <li class="mb-1  d-flex align-items-center">
                                                <span class="h6">Sub Division : </span>
                                                <span
                                                    class="text-dark ms-1"><b>{{ isset($salesMaster->subDivisionPDF) && $salesMaster->subDivisionPDF->name != '' && $salesMaster->subDivisionPDF->name != null ? $salesMaster->subDivisionPDF->name : 'N/A' }}</b></span>
                                            </li>
                                        </ul>

                                    </div>
                                </div>
                                <!-- / Division First -->
                                <!-- Division Second -->
                                <div class="col-lg-4">
                                    <div class="info-container">
                                        <ul class="list-unstyled mb-50">
                                            <li class="mb-1 d-flex align-items-center">
                                                <span class="h6">Division : </span>
                                                <span
                                                    class="text-dark ms-1"><b>{{ $salesMaster->division != '' && $salesMaster->division != null ? $salesMaster->division : 'N/A' }}</b></span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                <!-- / Division Second -->
                                <!-- Division Third -->
                                <div class="col-lg-4">
                                    <div class="info-container">
                                        <ul class="list-unstyled mb-50">
                                            <li class="mb-1  d-flex align-items-center">
                                                <span class="h6">Circle : </span>
                                                <span
                                                    class="text-dark ms-1"><b>{{ $salesMaster->circle != '' && $salesMaster->circle != null ? $salesMaster->circle : 'N/A' }}</b></span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                <!-- / Division Third -->

                                <!--  DISCOM First -->
                                <div class="col-lg-12">
                                    <div class="info-container">
                                        <ul class="list-unstyled mb-50">
                                            <li class="mb-1  d-flex align-items-center">
                                                <span class="h6">DISCOM : </span>
                                                <span
                                                    class="text-dark ms-1"><b>{{ $salesMaster->discom != '' && $salesMaster->discom != null ? $salesMaster->discom : 'N/A' }}</b></span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                <!-- / Division First -->

                                <div class="col-lg-12 mt-50 mb-50">
                                    <h5 class="font-black" style="font-weight: bold;color: var(--ck-color-base-text);">
                                        <i class="fa fa-info-circle pe-50"></i> Other Details
                                    </h5>
                                    <hr class="m-0" />
                                </div>


                                <!--  Other First -->
                                <div class="col-lg-4">
                                    <div class="info-container">
                                        <ul class="list-unstyled mb-50">
                                            <li class="mb-1  d-flex align-items-center">
                                                <span class="h6">GST Number : </span>
                                                <span
                                                    class="text-dark ms-1"><b>{{ $salesMaster->gst_number != '' && $salesMaster->gst_number != null ? $salesMaster->gst_number : 'N/A' }}</b></span>
                                            </li>
                                            <li class="mb-1  d-flex align-items-center">
                                                <span class="h6">Phase : </span>
                                                <span
                                                    class="text-dark ms-1"><b>{{ $salesMaster->phase != '' && $salesMaster->phase != null ? $salesMaster->phase : 'N/A' }}</b></span>
                                            </li>
                                        </ul>

                                    </div>
                                </div>
                                <!-- / Other First -->
                                <!-- Other Second -->
                                <div class="col-lg-4">
                                    <div class="info-container">
                                        <ul class="list-unstyled mb-50">
                                            <li class="mb-1  d-flex align-items-center">
                                                <span class="h6">Aadhaar No. : </span>
                                                <span
                                                    class="text-dark"><b>{{ $salesMaster->aadhaar_number != '' && $salesMaster->aadhaar_number != null ? $salesMaster->aadhaar_number : 'N/A' }}</b></span>
                                            </li>
                                            <li class="mb-1  d-flex align-items-center">
                                                <span class="h6">Reference : </span>
                                                <span
                                                    class="text-dark"><b>{{ $salesMaster->reference != '' && $salesMaster->reference != null ? $salesMaster->reference : 'N/A' }}</b></span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                <!-- / Other Second -->
                                <!-- Other Third -->
                                <div class="col-lg-4">
                                    <div class="info-container">
                                        <ul class="list-unstyled mb-50">
                                            <li class="mb-1  d-flex align-items-center">
                                                <span class="h6">Contracted Load : </span>
                                                <span
                                                    class="text-dark ms-1"><b>{{ $salesMaster->contracted_load != '' && $salesMaster->contracted_load != null ? $salesMaster->contracted_load : 'N/A' }}</b></span>
                                            </li>
                                            <li class="mb-50 d-flex align-items-center">
                                                <span class="h6">Agent / Sales Person : </span>
                                                <span
                                                    class="text-dark ms-1"><b>{{ $salesMaster->agentsalesperson->name != '' && $salesMaster->agentsalesperson->name != null ? $salesMaster->agentsalesperson->name : 'N/A' }}</b></span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                <!-- / Other Third -->

                                <div class="col-lg-12">
                                    <div class="info-container">
                                        <ul class="list-unstyled mb-50">
                                            <li class="mb-1  d-flex align-items-center">
                                                <span class="h6">Remark : </span>
                                                <span class="text-dark ms-1"><b>{{ $salesMaster->remark }}</b></span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>

                                <div class="col-lg-12 mt-50 mb-50">
                                    <h5 class="font-black" style="font-weight: bold;color: var(--ck-color-base-text);">
                                        <i class="fa fa-cubes pe-50"></i> Panel & Inverter Details
                                    </h5>
                                    <hr class="m-0" />
                                </div>


                                <!--  Panel & Inverter First -->
                                <div class="col-lg-4">
                                    <div class="info-container">
                                        <ul class="list-unstyled mb-50">
                                            <li class="mb-1  d-flex align-items-center">
                                                <span class="h6">Panel Company : </span>
                                                <span
                                                    class="text-dark ms-1"><b>{{ isset($salesMaster->panel) && $salesMaster->panel->name != '' && $salesMaster->panel->name != null ? $salesMaster->panel->name : 'N/A' }}</b></span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                <!-- / Panel & Inverter First -->
                                <!-- Panel & Inverter Second -->
                                <div class="col-lg-4">
                                    <div class="info-container">
                                        <ul class="list-unstyled mb-50">
                                            <li class="mb-1  d-flex align-items-center">
                                                <span class="h6">Panel Watt : </span>
                                                <span
                                                    class="text-dark ms-1"><b>{{ isset($salesMaster->panelwatt) && $salesMaster->panelwatt->name != '' && $salesMaster->panelwatt->name != null ? $salesMaster->panelwatt->name : 'N/A' }}</b></span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                <!-- / Panel & Inverter Second -->
                                <!-- Panel & Inverter Third -->
                                <div class="col-lg-4">
                                    <div class="info-container">
                                        <ul class="list-unstyled mb-50">
                                            <li class="mb-1  d-flex align-items-center">
                                                <span class="h6">Inverter Company : </span>
                                                <span
                                                    class="text-dark ms-1"><b>{{ isset($salesMaster->inveter) && $salesMaster->inveter->name != '' && $salesMaster->inveter->name != null ? $salesMaster->inveter->name : 'N/A' }}</b></span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                <!-- / Panel & Inverter Third -->


                                <div class="col-lg-12 mt-50 mb-50">
                                    <h5 class="font-black" style="font-weight: bold;color: var(--ck-color-base-text);">
                                        <i class="fa fa-print pe-50"></i> Invoice Details
                                    </h5>
                                    <hr class="m-0" />
                                </div>

                                <div class="col-lg-4">
                                    <div class="info-container">
                                        <ul class="list-unstyled mb-50">
                                            <li class="mb-50 d-flex align-items-center">
                                                <span class="h6">Invoice No : </span>
                                                <span
                                                    class="text-dark ms-1"><b>{{ $salesMaster->invoice_no != '' && $salesMaster->invoice_no != null ? $salesMaster->invoice_no : 'N/A' }}</b></span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>

                                <div class="col-lg-4">
                                    <div class="info-container">
                                        <ul class="list-unstyled mb-50">
                                            <li class="mb-50 d-flex align-items-center">
                                                <span class="h6">Invoice Date : </span>
                                                <span
                                                    class="text-dark ms-1"><b>{{ $salesMaster->invoice_date != '' && $salesMaster->invoice_date != '0000-00-00' && $salesMaster->invoice_date != null && $salesMaster->invoice_date != '1970-01-01' && $salesMaster->invoice_date != '0000-00-00' ? date('d-m-Y', strtotime($salesMaster->invoice_date)) : 'N/A' }}</b></span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>

                                <div class="col-lg-4">
                                    <div class="info-container">
                                        <ul class="list-unstyled mb-50">
                                            <li class="mb-50 d-flex align-items-center">
                                                <span class="h6">Installation Assign Person : </span>
                                                <span
                                                    class="text-dark ms-1"><b>{{ $salesMaster->installation_asian_person != '' && $salesMaster->installation_asian_person != null ? installationAsignPerson($salesMaster->installation_asian_person) : 'N/A' }}</b></span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>

                                @if (!is_null($salesMaster->installation))
                                    <div class="col-lg-12 mt-50 mb-50">
                                        <h2 class="font-black"
                                            style="font-weight: bold;color: var(--ck-color-base-text);">
                                            Installation Details</h2>
                                        <hr class="m-0" />
                                    </div>

                                    <!--  Other First -->
                                    <div class="col-lg-4">
                                        <div class="info-container">
                                            <ul class="list-unstyled mb-50">
                                                <li class="mb-1  d-flex align-items-center">
                                                    <span class="h6">Installation Date : </span>
                                                    <span
                                                        class="text-dark ms-1"><b>{{ $salesMaster->installation->date != '' && $salesMaster->installation->date != null && $salesMaster->installation != '1970-01-01' && $salesMaster->installation != '0000-00-00' ? date('d-m-Y', strtotime($salesMaster->installation->date)) : 'N/A' }}</b></span>
                                                </li>
                                            </ul>

                                        </div>
                                    </div>
                                    <!-- / Other First -->

                                    <div class="col-lg-12 mt-50 mb-50">
                                        <h5 class="font-black"
                                            style="font-weight: bold;color: var(--ck-color-base-text);">
                                            <i class="fa fa-th-large pe-50"></i> Panel Details
                                        </h5>
                                        <hr class="m-0" />
                                    </div>

                                    @if ($salesMaster->allpanels->count() > 0 )
                                        <div class="col-lg-12">
                                            @foreach ($salesMaster->allpanels as $k => $panel)
											
                                                <ul class="p-0 list-group list-group-horizontal-md  mb-1">
                                                    <li class="list-group-item">  Panel Company : <b
                                                            class="data">{{ $panel->itemgroup->panel_company->name != '' && $panel->itemgroup->panel_company->name != null ? $panel->itemgroup->panel_company->name : 'N/A' }}</b>
                                                    </li>
                                                    <li class="list-group-item"> Panel Type : <b
                                                            class="data">{{ $panel->itemgroup->panel_type->name != '' && $panel->itemgroup->panel_type->name != null ? $panel->itemgroup->panel_type->name : 'N/A' }}</b>
                                                    </li>
                                                    <li class="list-group-item"> Panel Watt : <b
                                                            class="data">{{ $panel->itemgroup->panel_watt->name != '' && $panel->itemgroup->panel_watt->name != null ? $panel->itemgroup->panel_watt->name : 'N/A' }}</b>
                                                    </li>
                                                    <li class="list-group-item"> Panel Nos : <b
                                                            class="data">{{ $panel->use_stock != '' && $panel->use_stock != null ? $panel->use_stock : 'N/A' }}</b>
                                                    </li>
                                                    <li class="list-group-item"> Panel Model No : <b
                                                            class="data">{{ $panel->model_number != '' && $panel->model_number != null ? $panel->model_number : 'N/A' }}</b>
                                                    </li>
                                                    <li class="list-group-item"> Total KW : <b
                                                            class="data">{{ $panel->total_kw != '' && $panel->total_kw != null ? $panel->total_kw : 'N/A' }}</b>
                                                    </li>
                                                </ul>

                                                @if (!is_null($salesMaster->installation->installationPenals))
                                                    <div class="col-lg-12">
                                                        <span class="h6">Serial No. : </span>
                                                        @foreach ($salesMaster->installation->installationPenals as $k => $ips)
                                                            @if ($ips->item_group_id == $panel->item_group_id)
                                                                <span
                                                                    class="badge bg-label-secondary text-dark mb-1">{{ strtoupper($ips->serial_no) }}</span>
                                                            @endif
                                                        @endforeach
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    @else
                                        <!--  Other First -->
                                        <div class="col-lg-4">
                                            <div class="info-container">
                                                <ul class="list-unstyled mb-50">
                                                    <li class="mb-1  d-flex align-items-center">
                                                        <span class="h6">  Panel Company : </span>
                                                        <span
                                                            class="text-dark ms-1"><b>{{ !is_null($salesMaster->installation->panelcompany) && $salesMaster->installation->panelcompany->name != '' && $salesMaster->installation->panelcompany->name != null ? $salesMaster->installation->panelcompany->name : 'N/A' }}</b></span>
                                                    </li>
                                                    <li class="mb-1  d-flex align-items-center">
                                                        <span class="h6">Panel Watt : </span>
                                                        <span
                                                            class="text-dark ms-1"><b>{{ !is_null($salesMaster->installation->panelwatt) && $salesMaster->installation->panelwatt->name != '' && $salesMaster->installation->panelwatt->name != null ? $salesMaster->installation->panelwatt->name : 'N/A' }}</b></span>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                        <!-- / Other First -->

                                        <!--  Other First -->
                                        <div class="col-lg-4">
                                            <div class="info-container">
                                                <ul class="list-unstyled mb-50">
                                                    <li class="mb-1  d-flex align-items-center">
                                                        <span class="h6">Panel Model No : </span>
                                                        <span
                                                            class="text-dark ms-1"><b>{{ $salesMaster->installation->penal_model_no != '' && $salesMaster->installation->penal_model_no != null ? $salesMaster->installation->penal_model_no : 'N/A' }}</b></span>
                                                    </li>
                                                    <li class="mb-1  d-flex align-items-center">
                                                        <span class="h6">Panel Nos : </span>
                                                        <span
                                                            class="text-dark ms-1"><b>{{ $salesMaster->installation->penal_nos != '' && $salesMaster->installation->penal_nos != null ? $salesMaster->installation->penal_nos : 'N/A' }}</b></span>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                        <!-- / Other First -->

                                        <!--  Other First -->
                                        <div class="col-lg-4">
                                            <div class="info-container">
                                                <ul class="list-unstyled mb-50">
                                                    <li class="mb-1  d-flex align-items-center">
                                                        <span class="h6">Panel Type : </span>
                                                        <span
                                                            class="text-dark ms-1"><b>{{ !is_null($salesMaster->installation->paneltype) && $salesMaster->installation->paneltype->name != '' && $salesMaster->installation->paneltype->name != null ? $salesMaster->installation->paneltype->name : 'N/A' }}</b></span>
                                                    </li>
                                                    <li class="mb-1  d-flex align-items-center">
                                                        <span class="h6">Total KW : </span>
                                                        <span
                                                            class="text-dark ms-1"><b>{{ $salesMaster->installation->total_kv != '' && $salesMaster->installation->total_kv != null ? $salesMaster->installation->total_kv : 'N/A' }}</b></span>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                        <!-- / Other First -->

                                        @if (!is_null($salesMaster->installation->installationPenals))
                                            <div class="col-lg-12">
                                                <span class="h6">Serial No. : </span>
                                                @foreach ($salesMaster->installation->installationPenals as $k => $ips)
                                                    <span
                                                        class="badge bg-label-secondary text-dark mb-1">{{ $ips->serial_no }}</span>
                                                @endforeach
                                            </div>
                                        @endif
                                    @endif



                                    <div class="col-lg-12 mt-50 mb-50">
                                        <h5 class="font-black"
                                            style="font-weight: bold;color: var(--ck-color-base-text);">
                                            <i class="fa fa-tasks pe-50"></i> Inverter Details
                                        </h5>
                                        <hr class="m-0" />
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="info-container">
                                            <ul class="list-unstyled mb-50">
                                                <li class="mb-1  d-flex align-items-center">
                                                    <span class="h6">Type of Inverter : </span>
                                                    <span
                                                        class="text-dark ms-1"><b>{{ $salesMaster->installation->type_of_inverter != '' && $salesMaster->installation->type_of_inverter != null ? $salesMaster->installation->type_of_inverter : 'N/A' }}</b></span>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="info-container">
                                            <ul class="list-unstyled mb-50">
                                                <li class="mb-1  d-flex align-items-center">
                                                    <span class="h6">No. of Inverter : </span>
                                                    <span
                                                        class="text-dark ms-1"><b>{{ $salesMaster->installation->no_of_inverter != '' && $salesMaster->installation->no_of_inverter != null ? $salesMaster->installation->no_of_inverter : 'N/A' }}</b></span>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>

                                    @if (!is_null($salesMaster->installation->invater))
                                        <div class="col-lg-12">
                                            @foreach ($salesMaster->installation->invater as $k => $inverter)
                                                <ul class="p-0 list-group list-group-horizontal-md">
                                                    <li class="list-group-item">Inverter Company : <b
                                                            class="data">{{ $inverter->company->name != '' && $inverter->company->name != null ? $inverter->company->name : 'N/A' }}</b>
                                                    </li>
                                                    <li class="list-group-item">Inverter KW : <b
                                                            class="data">{{ $inverter->invater_kw != '' && $inverter->invater_kw != null ? $inverter->invater_kw : 'N/A' }}</b>
                                                    </li>
                                                    <li class="list-group-item">Model Number : <b
                                                            class="data">{{ $inverter->model_number != '' && $inverter->model_number != null ? $inverter->model_number : 'N/A' }}</b>
                                                    </li>
                                                    <li class="list-group-item">Serial No. : <b
                                                            class="data">{{ $inverter->serial_no_of_inverter != '' && $inverter->serial_no_of_inverter != null ? $inverter->serial_no_of_inverter : 'N/A' }}</b>
                                                    </li>
                                                    <li class="list-group-item">Voltage : <b
                                                            class="data">{{ $inverter->voltage != '' && $inverter->voltage != null ? $inverter->voltage : 'N/A' }}</b>
                                                    </li>

                                                </ul>
                                            @endforeach
                                        </div>
                                    @endif
                                @endif
                            </div>
                            <div class="row">
                                <div class="col-lg-12 mt-50 mb-50">
                                    <h5 class="font-black" style="font-weight: bold;color: var(--ck-color-base-text);">
                                        <i class="fa fa-bolt pe-50"></i> Meter Application Details
                                    </h5>
                                    <hr class="m-0" />
                                </div>
                                <div class="col-lg-4">
                                    <div class="info-container">
                                        <ul class="list-unstyled mb-50">
                                            <li class="mb-50 d-flex align-items-center">
                                                <span class="h6">Meter Application Date : </span>
                                                <span
                                                    class="text-dark ms-1"><b>{{ $salesMaster->meter_application_date != '' && $salesMaster->meter_application_date != '0000-00-00' && $salesMaster->meter_application_date != null && $salesMaster->meter_application_date != '1970-01-01' ? date('d-m-Y', strtotime($salesMaster->meter_application_date)) : 'N/A' }}</b></span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="info-container">
                                        <ul class="list-unstyled mb-50">
                                            <li class="mb-50 d-flex align-items-center">
                                                <span class="h6">Meter Assign Person : </span>
                                                <span
                                                    class="text-dark ms-1"><b>{{ $salesMaster->meter_asian_person != '' && $salesMaster->meter_asian_person != null ? installationAsignPerson($salesMaster->meter_asian_person) : 'N/A' }}</b></span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="col-lg-4"></div>
                                <div class="col-lg-4">
                                    <div class="info-container">
                                        <ul class="list-unstyled mb-50">
                                            <li class="mb-50 d-flex align-items-center">
                                                <span class="h6">Courier Number : </span>
                                                <span
                                                    class="text-dark ms-1"><b>{{ $salesMaster->couriar_no != '' && $salesMaster->couriar_no != null ? $salesMaster->couriar_no : 'N/A' }}</b></span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="info-container">
                                        <ul class="list-unstyled mb-50">
                                            <li class="mb-50 d-flex align-items-center">
                                                <span class="h6">Courier Company : </span>
                                                <span
                                                    class="text-dark ms-1"><b>{{ $salesMaster->courair_company != '' && $salesMaster->courair_company != null ? $salesMaster->courair_company : 'N/A' }}</b></span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="info-container">
                                        <ul class="list-unstyled mb-50">
                                            <li class="mb-1  d-flex align-items-center">
                                                <span class="h6">Meter Installation Date : </span>
                                                <span
                                                    class="text-dark ms-1"><b>{{ $salesMaster->meter_installation_date != '' && $salesMaster->meter_installation_date != '1970-01-01' && $salesMaster->meter_installation_date != '0000-00-00' ? date('d-m-Y', strtotime($salesMaster->meter_installation_date)) : 'N/A' }}</b></span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="info-container">
                                        <ul class="list-unstyled mb-50">
                                            <li class="mb-1  d-flex align-items-center">
                                                <span class="h6">Project Completion Date : </span>
                                                <span
                                                    class="text-dark ms-1"><b>{{ $salesMaster->project_completion_date != '' && $salesMaster->project_completion_date != '1970-01-01' && $salesMaster->project_completion_date != '0000-00-00' && $salesMaster->project_completion_date != null ? date('d-m-Y', strtotime($salesMaster->project_completion_date)) : 'N/A' }}</b></span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                @if(!($salesMaster->ragistration_portal == 'GEDA' || ($salesMaster->ragistration_portal == 'National' && $salesMaster->subsidy_giveup)))
                                <div class="col-lg-12 mt-50 mb-50">
                                    <h5 class="font-black" style="font-weight: bold;color: var(--ck-color-base-text);">
                                        <i class="fa fa-university pe-50"></i> Subsidy Details
                                    </h5>
                                    <hr class="m-0" />
                                </div>
                                <div class="col-lg-4">
                                    <div class="info-container">
                                        <ul class="list-unstyled mb-50">
                                            <li class="mb-50 d-flex align-items-center">
                                                <span class="h6">Request Date : </span>
                                                <span
                                                    class="text-dark ms-1"><b>{{ $salesMaster->subsidy_request_date != '' && $salesMaster->subsidy_request_date != '1970-01-01' && $salesMaster->subsidy_request_date != '0000-00-00' ? date('d-m-Y', strtotime($salesMaster->subsidy_request_date)) : 'N/A' }}</b></span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="info-container">
                                        <ul class="list-unstyled mb-50">
                                            <li class="mb-1  d-flex align-items-center">
                                                <span class="h6">Verify Date : </span>
                                                <span
                                                    class="text-dark ms-1"><b>{{ $salesMaster->subsidy_disbursement_verify_date != '' && $salesMaster->subsidy_disbursement_verify_date != '1970-01-01' && $salesMaster->subsidy_disbursement_verify_date != '0000-00-00' ? date('d-m-Y', strtotime($salesMaster->subsidy_disbursement_verify_date)) : 'N/A' }}</b></span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="info-container">
                                        <ul class="list-unstyled mb-50">
                                            <li class="mb-50 d-flex align-items-center">
                                                <span class="h6">Disbursement Date : </span>
                                                <span
                                                    class="text-dark ms-1"><b>{{ $salesMaster->subsidy_disbursement_date != '' && $salesMaster->subsidy_disbursement_date != '1970-01-01' && $salesMaster->subsidy_disbursement_date != '0000-00-00' ? date('d-m-Y', strtotime($salesMaster->subsidy_disbursement_date)) : 'N/A' }}</b></span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="col-lg-8">
                                    <div class="info-container">
                                        <ul class="list-unstyled mb-50">
                                            <li class="mb-1  d-flex align-items-center">
                                                <span class="h6">Remark : </span>
                                                <span
                                                    class="text-dark ms-1"><b>{{ $salesMaster->subsidy_disbursal_remark }}</b></span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                @endif
                                <div class="col-lg-12 mt-50 mb-50">
                                    <h5 class="font-black" style="font-weight: bold;color: var(--ck-color-base-text);">
                                        <i class="fa fa-file pe-50"> </i> Uploaded Documents
                                    </h5>
                                    <hr class="m-0" />
                                </div>
                                <div class="col-lg-12">
                                    <div class="d-flex w-100 flex-wrap align-items-center gap-1">
                                        @if ($salesMaster->document->count() > 0)
                                            @foreach ($salesMaster->document as $docVal)
                                                @if ($docVal->image != '')
                                                    <a href="{{ asset('upload/document/' . $docVal->image) }}"
                                                        download="{{ $docVal->name }}">
                                                        <button type="button"
                                                            class="btn btn-sm btn-outline-primary"><span>{{ ucwords(str_replace('_', ' ', $docVal->name)) }}</span>
                                                            <i data-feather="download" class="me-25"></i></button>
                                                    </a>
                                                @endif
                                            @endforeach
                                        @endif
                                        @if (!is_null($salesMaster->lead) && $salesMaster->lead->site_visit_images != '')
                                            @foreach (explode(',', $salesMaster->lead->site_visit_images) as $siteKey => $siteImg)
                                                <a href="{{ asset('uploads/site_visit_images/' . $siteImg) }}"
                                                    download="{{ $siteImg }}">
                                                    <button type="button"
                                                        class="btn btn-sm btn-outline-primary"><span>Site Visit
                                                            - {{ $siteKey + 1 }}</span> <i data-feather="download"
                                                            class="me-25"></i></button>
                                                </a>
                                            @endforeach
                                        @endif

                                        @if (!is_null($salesMaster->feasibility_letter) && $salesMaster->feasibility_letter != '')
                                            <a href="{{ asset('upload/document/' . $salesMaster->feasibility_letter) }}"
                                                download="Feasibility Letter">
                                                <button type="button"
                                                    class="btn btn-sm btn-outline-primary"><span>Feasibility Letter</span>
                                                    <i data-feather="download" class="me-25"></i></button>
                                            </a>
                                        @endif

                                        @if (!is_null($salesloan) && $salesloan->loan_pdf != '')
                                            <a href="{{ asset('upload/document/' . $salesloan->loan_pdf) }}"
                                                download="Loan PDF">
                                                <button type="button" class="btn btn-sm btn-outline-primary"><span>Loan
                                                        PDF</span> <i data-feather="download" class="me-25"></i></button>
                                            </a>
                                        @endif


                                        @if (
                                            !is_null($salesMaster->installation) &&
                                                !is_null($salesMaster->installation->penalImage) &&
                                                $salesMaster->installation->penalImage->count() > 0)
                                            @foreach ($salesMaster->installation->penalImage as $panel => $panelImg)
                                                <a href="{{ asset('uploads/penal/' . $panelImg->image) }}"
                                                    download="{{ $panelImg->image }}">
                                                    <button type="button"
                                                        class="btn btn-sm btn-outline-primary"><span>Panel
                                                            Image - {{ $panel + 1 }}</span> <i data-feather="download"
                                                            class="me-25"></i></button>
                                                </a>
                                            @endforeach
                                        @endif
                                        @if (
                                            !is_null($salesMaster->installation) &&
                                                !is_null($salesMaster->installation->invaterImages) &&
                                                $salesMaster->installation->invaterImages->count() > 0)
                                            @foreach ($salesMaster->installation->invaterImages as $invater => $invaterImg)
                                                <a href="{{ asset('uploads/invater/' . $invaterImg->image) }}"
                                                    download="{{ $invaterImg->image }}">
                                                    <button type="button"
                                                        class="btn btn-sm btn-outline-primary"><span>Invater
                                                            Image - {{ $invater + 1 }}</span> <i data-feather="download"
                                                            class="me-25"></i></button>
                                                </a>
                                            @endforeach
                                        @endif

                                        @if ($salesMaster->meter_application_oc != '')
                                            <a href="{{ asset('upload/document/' . $salesMaster->meter_application_oc) }}"
                                                download="Meter Application OC">
                                                <button type="button" class="btn btn-sm btn-outline-primary"><span>Meter
                                                        Application OC</span> <i data-feather="download"
                                                        class="me-25"></i></button>
                                            </a>
                                        @endif

                                        @if ($salesMaster->payment_receipt != '')
                                            <a href="{{ asset('upload/document/' . $salesMaster->payment_receipt) }}"
                                                download="Payment Receipt">
                                                <button type="button"
                                                    class="btn btn-sm btn-outline-primary"><span>Payment
                                                        Receipt</span> <i data-feather="download"
                                                        class="me-25"></i></button>
                                            </a>
                                        @endif

                                        @if ($salesMaster->proforma_15 != '')
                                            <a href="{{ asset('upload/document/' . $salesMaster->proforma_15) }}"
                                                download="Proforma 15">
                                                <button type="button"
                                                    class="btn btn-sm btn-outline-primary"><span>Proforma
                                                        15</span> <i data-feather="download" class="me-25"></i></button>
                                            </a>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-lg-12 mt-50 mb-50">
                                    <h5 class="font-black" style="font-weight: bold;color: var(--ck-color-base-text);">
                                        <i class="fa fa-file pe-50"> </i> Generated Documents
                                    </h5>
                                    <hr class="m-0" />
                                </div>

                                <div class="col-lg-12">

                                    <div class="d-flex w-100 flex-wrap align-items-center gap-1">
                                        @php $selectedPdf = getSelectedDiscom($salesMaster->discom); @endphp
                                        @if (count($selectedPdf) > 0)
                                            @foreach ($selectedPdf as $k => $v)
                                                @php $status_to_show = $v['status_to_show']; @endphp
                                                @if ($salesMaster->$status_to_show == '1')
                                                    @if ($v['id'] == 'declaration_dcr_general')
                                                        <a class="btn btn-sm btn-outline-primary declaration-dcr-link"
                                                            data-id="{{ $salesMaster->id }}"
                                                            data-purchase_order_number="{{ $salesMaster->purchase_order_number }}"
                                                            data-purchase_order_date="{{ $salesMaster->purchase_order_date }}"
                                                            data-cell_manufacture_name="{{ $salesMaster->cell_manufacture_name }}"
                                                            data-cell_gst_invoice_no="{{ $salesMaster->cell_gst_invoice_no }}"
                                                            href="#">{{ $v['display_name'] }} <i
                                                                data-feather="download" class="me-25"></i></a>
                                                    @elseIf(isset($v['route']) && $v['route'] != '')
                                                        <a class="btn btn-sm btn-outline-primary"
                                                            href="{{ route($v['route'], $salesMaster->id) }}"
                                                            target="_blank">{{ $v['display_name'] }} <i
                                                                data-feather="download" class="me-25"></i></a>
                                                    @endif
                                                @endif
                                            @endforeach
                                        @endif
                                    </div>
                                </div>

                                <div class="col-lg-12 mt-50 mb-50" style="margin-top:15px;">
                                    <h2 class="font-black" style="font-weight: bold;color: var(--ck-color-base-text);">
                                        Payment
                                        Details</h2>
                                    <hr class="m-0" />
                                </div>

                                <!--  Other First -->
                                <div class="col-lg-4">
                                    <div class="info-container">
                                        <ul class="list-unstyled mb-50">
                                            <li class="mb-1  d-flex align-items-center">
                                                <span class="h6">Total Amount : </span>
                                                <span
                                                    class="text-dark ms-1"><b>{{ $salesMaster->total_amount != '' || $salesMaster->total_amount != null ? '₹ ' . number_format($salesMaster->total_amount, 2) : 'N/A' }}</b></span>
                                            </li>
                                        </ul>

                                    </div>
                                </div>
                                <!-- / Other First -->
                                <!-- Other Second -->
                                <div class="col-lg-4">
                                    <div class="info-container">
                                        <ul class="list-unstyled mb-50">
                                            <li class="mb-1  d-flex align-items-center">
                                                <span class="h6">Pending Amount : </span>
                                                <span
                                                    class="text-dark"><b>{{ $salesMaster->pending_amonut != '' || $salesMaster->pending_amonut != null ? '₹ ' . number_format($salesMaster->pending_amonut, 2) : 'N/A' }}</b></span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                <!-- / Other Second -->

                                <div class="col-lg-12">
                                    <div class="table-responsive">
                                        <table id="table" class="datatables-basic table ">
                                            <thead>
                                                <tr style="background-color: #f3f3f3;">
                                                    <td class="text-center fw-bold">#</td>
                                                    <td class="text-center fw-bold">Status</td>
                                                    <td class="text-center fw-bold">Amount</td>
                                                    <td class="text-center fw-bold">Payment Date</td>
                                                    <td class="text-center fw-bold">Payment Type</td>
                                                    <td class="text-center fw-bold">Cheque/UTR/UPI ID</td>
                                                    <td class="text-center fw-bold">Bank Name</td>
                                                    <td class="text-center fw-bold">Branch Name</td>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @if ($payment->count() > 0)
                                                    @foreach ($payment as $key => $value)
                                                        <tr>
                                                            <td class="text-center">{{ $key + 1 }}</td>
                                                            <td class="text-center">
                                                                @php $payStatus = getPaymentStatus($value->status); @endphp
                                                                <span
                                                                    class="badge bg-light-{{ $payStatus['class'] }} w-100">{{ $payStatus['status'] }}</span>
                                                            </td>
                                                            <td class="text-center">
                                                                {{ number_format($value->amount, 2) }}</td>
                                                            <td class="text-center">
                                                                {{ date('d-m-Y', strtotime($value->payment_date)) }}</td>
                                                            <td class="text-center">{{ $value->payment_type }}</td>
                                                            <td class="text-center">{{ $value->cheque_number }}
                                                                {{ $value->utr_number }} {{ $value->upi_id }}</td>
                                                            <td class="text-center">{{ $value->bank_name }}</td>
                                                            <td class="text-center">{{ $value->branch_name }}</td>
                                                        </tr>
                                                    @endforeach
                                                @else
                                                    <tr>
                                                        <td colspan="10" class="text-center">No Data Found!</td>
                                                    </tr>
                                                @endif
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-2 col-lg-2">
                            <div class="row">
                                <div class="col-lg-12">
                                    @php
                                        $statuses = allSalesStatus();
                                    @endphp
                                    <ul class="timeline-vertical">
                                        @foreach ($statuses as $status)
                                            @if (($status['for_loan'] == 0 && $salesMaster->file_type == 'C') || $salesMaster->file_type == 'L')
                                                @if (!isset($status['is_not_in_timeline']))
                                                    @php
                                                        $value = $status['value'];
                                                        $label = $status['name'];
                                                        $remove = $status['is_remove'];
                                                        $active = ($salesMaster->$value ?? '0') == '1';
                                                    @endphp
                                                    @if (($value == 'subsidy_claimed' || $value == 'subsidy_receveid') && ($salesMaster->ragistration_portal == 'GEDA' || ($salesMaster->ragistration_portal == 'National' && $salesMaster->subsidy_giveup)))
                                                    @else
                                                    @if ($value == 'disbursement' && $percentage > 0)
                                                        <li
                                                            class="{{ $percentage < 100 ? 'active-tl-light' : 'active-tl' }}">
                                                        @else
                                                        <li class="{{ $active ? 'active-tl' : '' }}">
                                                    @endif
                                                    {{ $label }}

                                                    @if ($value == 'disbursement' && $percentage > 0)
                                                        ({{ number_format($percentage, 2) }}%)
                                                    @endif
                                                    {{-- Remove button --}}
                                                    @if ($active && $remove == 1)
                                                        <a href="javascript:void(0)" data-id="{{ $salesMaster->id }}"
                                                            data-status="{{ $value }}" class="remove-status"
                                                            data-bs-toggle="tooltip" title="Click to remove status">
                                                            <i data-feather="x" class="text-danger"></i>
                                                        </a>
                                                    @endif
                                                    </li>
                                                    @endif
                                                @endif
                                            @endif
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="statusexampleModal" tabindex="-1" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-transparent border-bottom">
                    <h4 class="text-center mb-0" id="exampleModalTitle"> Form</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-2" id="status-body">

                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="declarationexampleModal" tabindex="-1" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-md">
            <div class="modal-content">
                <div class="modal-header bg-transparent border-bottom">
                    <h4 class="text-center mb-0" id="exampleModalTitle">Declaration</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-2" id="payment-body">
                    <form id="form_declaration" class="form" action="javascript:void(0);" method="POST">
                        @csrf
                        <div class="row">
                            <input type="hidden" id="sales_master_id" name="sales_master_id" value="">

                            <div class="col-12 col-md-12 mb-1 custom-input-group">
                                <label class="form-label" for="purchase_order_number">Purchase Order Number</label>
                                <input type="text" class="form-control" name="purchase_order_number"
                                    id="purchase_order_number" placeholder="Purchase Order Number *">
                                <span class="invalid-feedback d-block" id="error_purchase_order_number"
                                    role="alert"></span>
                            </div>
                            <div class="col-12 col-md-12 mb-1 custom-input-group">
                                <label class="form-label" for="purchase_order_date">Purchase Order Date</label>
                                <input type="text" class="form-control flatpickr-date purchase_order_date"
                                    name="purchase_order_date" id="purchase_order_date"
                                    placeholder="Purchase Order Date *">
                                <span class="invalid-feedback d-block" id="error_purchase_order_date"
                                    role="alert"></span>
                            </div>
                            <div class="col-12 col-md-12 mb-1 custom-input-group">
                                <label class="form-label" for="cell_manufacture_name">Cell Manufacture Name</label>
                                <input type="text" class="form-control" name="cell_manufacture_name"
                                    id="cell_manufacture_name" placeholder="Cell Manufacture Name *">
                                <span class="invalid-feedback d-block" id="error_cell_manufacture_name"
                                    role="alert"></span>
                            </div>
                            <div class="col-12 col-md-12 mb-1 custom-input-group">
                                <label class="form-label" for="cell_gst_invoice_no">Cell gst invoice no</label>
                                <input type="text" class="form-control" name="cell_gst_invoice_no"
                                    id="cell_gst_invoice_no" placeholder="Cell Cell GST Invoice No *">
                                <span class="invalid-feedback d-block" id="error_cell_gst_invoice_no"
                                    role="alert"></span>
                            </div>
                            <div class="col-md-12 col-12">
                                <button type="submit"
                                    class="btn btn-sm btn-primary float-end save_declaration">{{ __('message.Submit') }}</button>
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
        const URL = "{{ route('sales-master.index') }}";

        $(document).on('click', '.declaration-dcr-link', function() {
            var id = $(this).data('id');
            var purchase_order_number = $(this).data('purchase_order_number');
            var purchase_order_date = $(this).data('purchase_order_date');
            var cell_manufacture_name = $(this).data('cell_manufacture_name');
            var cell_gst_invoice_no = $(this).data('cell_gst_invoice_no');

            var formattedDate = '';
            if (purchase_order_date != '') {
                var dateParts = purchase_order_date.split('-');
                var formattedDate = dateParts[2] + '-' + dateParts[1] + '-' + dateParts[0];
            }

            $('#sales_master_id').val(id);
            $('#purchase_order_number').val(purchase_order_number);
            $('#purchase_order_date').val(formattedDate);
            $('#cell_manufacture_name').val(cell_manufacture_name);
            $('#cell_gst_invoice_no').val(cell_gst_invoice_no);
            $("#declarationexampleModal").modal("show");
        });

        $(document).on('click', '.save_declaration', function(event) {
            event.preventDefault();
            $("#form_declaration").validate({
                rules: {
                    purchase_order_number: {
                        required: true
                    },
                    purchase_order_date: {
                        required: true
                    },
                    cell_manufacture_name: {
                        required: true
                    },
                    cell_gst_invoice_no: {
                        required: true
                    },
                },
                messages: {
                    purchase_order_number: "Enter Purchase Order Number",
                    purchase_order_date: "Select Purchase Order Date",
                    cell_manufacture_name: "Enter Cell Manufacture Name",
                    cell_gst_invoice_no: "Enter Cell GST Invoice No",
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

            if ($("#form_declaration").valid()) {
                var formData = new FormData($("#form_declaration")[0]);
                $.ajax({
                    type: "POST",
                    url: "{{ route('declaration-update') }}",
                    data: formData,
                    dataType: 'json',
                    cache: false,
                    contentType: false,
                    processData: false,
                    beforeSend: function() {
                        $("#error_name").html(' ');
                        $(".save_declaration").html(
                            `<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> {{ __('message.Wait') }}`
                        );
                        $(".save_declaration").attr('disabled', true);
                    },
                    success: function(response) {
                        $(".save_declaration").html("{{ __('message.Submit') }}");
                        $(".save_declaration").attr('disabled', false);
                        if (response.server_error && response.status == false) {
                            toastr.error(
                                "{{ __('message.Something went wrong. Please try again.') }}",
                                "{{ __('message.Error') }}");
                        } else if (response.status == false) {
                            $.each(response.errors, function(key, value) {
                                $('#error_' + key).html('<p class="text-danger mb-0">' + value +
                                    '</p>');
                            });
                            toastr.warning("{{ __('message.Please input proper data.') }}",
                                "{{ __('message.Warning') }}");
                        } else {
                            $('#form_declaration')[0].reset();

                            $("#declarationexampleModal").modal("hide");
                            location.href = response.data;

                        }
                    }
                });
            } else {
                return false;
            }
        });

        $(document).on('click', '.remove-status', function(e) {
            e.preventDefault();
            var id = $(this).data('id');
            var status = $(this).data('status');
            var url = "{{ route('sales-order-remove-status') }}";

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
                                    toastr.success(response.message, "Success");
                                    location.reload();
                                } else {
                                    toastr.error(response.server_error, "Opps!");
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

        $(document).on('click', '.save-status-form', function(e) {
            e.preventDefault();
            var id = $("#sales_master_id").val();
            var url = "{{ route('status-save', 'id') }}".replace('id', id);
            var aformData = new FormData($("#form")[0]);
            $.ajax({
                url: url,
                type: 'POST',
                datatype: 'json',
                data: aformData,
                cache: false,
                contentType: false,
                processData: false,
                success: function(response) {
                    if (response.status) {
                        $("#statusexampleModal").modal("hide");
                        toastr.success(response.message, "{{ __('message.Success') }}");
                        location.reload(true);
                    } else {
                        toastr.error(response.server_error, "{{ __('message.Error') }}");
                    }
                }
            });
        });

        $(document).on('click', '.status-view', function(e) {
            e.preventDefault();
            var id = $(this).data('id');
            var url = "{{ route('status-view', 'id') }}".replace('id', id);
            $("#statusexampleModal").modal("show");
            $.ajax({
                url: url,
                type: 'POST',
                datatype: 'json',
                data: {
                    "id": id,
                    "_token": "{{ csrf_token() }}",
                },
                success: function(response) {
                    $("#status-body").html(response.html);

                    flatpickr('.flatpickr-date', {
                        enableTime: false,
                        dateFormat: 'd-m-Y',
                        defaultDate: '',
                    });

                    if (feather) {
                        feather.replace({
                            width: 14,
                            height: 14
                        });
                    }

                    var bsStepper = document.querySelectorAll('.bs-stepper'),
                        verticalWizard = document.querySelector('.vertical-wizard-example');

                    // Adds crossed class
                    if (typeof bsStepper !== undefined && bsStepper !== null) {
                        for (var el = 0; el < bsStepper.length; ++el) {
                            bsStepper[el].addEventListener('show.bs-stepper', function(event) {
                                var index = event.detail.indexStep;
                                var numberOfSteps = $(event.target).find('.step').length - 1;
                                var line = $(event.target).find('.step');

                                for (var i = 0; i < index; i++) {
                                    line[i].classList.add('crossed');

                                    for (var j = index; j < numberOfSteps; j++) {
                                        line[j].classList.remove('crossed');
                                    }
                                }
                                if (event.detail.to == 0) {
                                    for (var k = index; k < numberOfSteps; k++) {
                                        line[k].classList.remove('crossed');
                                    }
                                    line[0].classList.remove('crossed');
                                }
                            });
                        }
                    }

                    // Vertical Wizard
                    // --------------------------------------------------------------------
                    if (typeof verticalWizard !== undefined && verticalWizard !== null) {
                        var verticalStepper = new Stepper(verticalWizard, {
                            linear: false
                        });
                        $(verticalWizard)
                            .find('.btn-next')
                            .on('click', function() {
                                verticalStepper.next();
                            });
                        $(verticalWizard)
                            .find('.btn-prev')
                            .on('click', function() {
                                verticalStepper.previous();
                            });

                        $(verticalWizard)
                            .find('.btn-submit')
                            .on('click', function() {
                                alert('Submitted..!!');
                            });
                    }
                }
            });
        });

        $(document).on('click', '.delete', function() {
            var btn = $(this);
            var id = btn.data('id');
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
                        axios.delete(URL + '/' + id)
                            .then(function(response) {
                                if (response.data.status == true) {
                                    toastr.success("{{ __('message.Deleted successfully.') }}",
                                        "{{ __('message.Success') }}");
                                    window.location.href = URL;
                                } else if (response.data.status == false && response.data.server_error) {
                                    toastr.error(response.data.server_error, "{{ __('message.Error') }}");
                                } else {
                                    toastr.warning(
                                        "{{ __('message.This SalesMaster activity has been used.') }}",
                                        "{{ __('message.Warning') }}");
                                }
                            })
                            .catch(function() {
                                toastr.error(
                                    "{{ __('message.Something went wrong. Please try again.') }}",
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
