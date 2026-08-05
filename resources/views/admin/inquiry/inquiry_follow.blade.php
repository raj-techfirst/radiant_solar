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
                                <div class="w-50">
                                    <h4 class="font-black" style="font-weight: bold;color: var(--ck-color-base-text);">
                                        Complaint Details</h4>
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


                                <div class="col-lg-12">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td> <span class="h6">Consumer No. :</span></td>
                                            <td> <span
                                                    class="text-dark ms-1"><b>{{ $inquiry->consumer_number ?? 'N/A' }}</b></span>
                                            </td>

                                            <td> <span class="h6">Contact No. :</span></td>
                                            <td> <span
                                                    class="text-dark ms-1"><b>{{ $inquiry->contact_number ?? 'N/A' }}</b></span>
                                            </td>

                                        </tr>
                                        <tr>
                                            <td> <span class="h6">Consumer Name :</span></td>
                                            <td colspan="3"> <span
                                                    class="text-dark ms-1"><b>{{ $inquiry->consumer_name ?? 'N/A' }}</b></span>
                                            </td>

                                        </tr>
                                        <tr>
                                            <td> <span class="h6">Problem :</span></td>
                                            <td colspan="3"> <span
                                                    class="text-dark ms-1"><b>{{ $inquiry->problem ?? 'N/A' }}</b></span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td> <span class="h6">Date :</span></td>
                                            <td colspan="3"> <span
                                                    class="text-dark ms-1"><b>{{ date('d-m-Y', strtotime($inquiry->created_at)) }}</b></span>
                                            </td>
                                        </tr>
                                    </table>
                                </div>

                                @if (!is_null($inquiry->image))
                                    <div class="col-lg-12 mt-1">
                                        <span class="h6">Image :</span>
                                        <div class="mt-50">
                                            <a href="{{ asset('upload/inquiry/' . $inquiry->image) }}"
                                                data-fancybox="gallery_{{ $inquiry->id }}"
                                                data-caption="{{ $inquiry->consumer_name }}">
                                                <img class="img-fluid rounded" height="100" width="100"
                                                    src="{{ asset('upload/inquiry/' . $inquiry->image) }}"
                                                    alt="{{ $inquiry->consumer_name }}">
                                            </a>
                                        </div>
                                    </div>
                                @endif


                            </div>
                        </div>
                    </div>



                    <div
                        class="card rounded p-0 @if ($salesMaster->hold_query != '1' && $salesMaster->file_cancel_order != '1') border-success @endif
                        @if ($salesMaster->file_cancel_order == '1') border-danger gray @endif @if ($salesMaster->hold_query == '1') border-warning gray @endif">

                    <div class="accordionrounded" id="accordionPanelsStayOpen">
                    <div class="accordion-item rounded">
                        <h1 class="accordion-header d-flex" id="panelsStayOpen-headingOne">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#panelsStayOpen-collapseOne" aria-expanded="true" aria-controls="panelsStayOpen-collapseOne">
                                 <span class="fs-4"
                                    style="font-weight: 900;"> &nbsp; Application Details </span> &nbsp;  &nbsp;
                            </button>
                        </h1>
                        <div id="panelsStayOpen-collapseOne" class="accordion-collapse collapse" aria-labelledby="panelsStayOpen-headingOne" data-bs-parent="#accordionPanelsStayOpen" >




                        <div class="card-body pt-0">

                            <div class="row ">
                                <hr/>

                                <!--  First -->
                                <div class="col-lg-12">
                                    <div class="info-container">
                                        <ul class="list-unstyled mb-50">
                                            <li class="mb-50 d-flex align-items-center">
                                                <span class="h6">Consumer No. : </span>
                                                <span
                                                    class="text-dark ms-1"><b>{{ $salesMaster->consumer_number != '' || $salesMaster->consumer_number != null ? $salesMaster->consumer_number : 'N/A' }}</b></span>
                                            </li>
                                        </ul>

                                    </div>
                                </div>
                                <!-- / First -->
                                <!-- Second -->
                                <div class="col-lg-12">
                                    <div class="info-container">
                                        <ul class="list-unstyled mb-50">
                                            <li class="mb-1  d-flex align-items-center">
                                                <span class="h6">Consumer Name : </span>
                                                <span
                                                    class="text-dark  ms-1"><b>{{ $salesMaster->consumer_name != '' || $salesMaster->consumer_name != null ? $salesMaster->consumer_name : 'N/A' }}</b></span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                <!-- / Second -->
                                <!-- Third -->
                                <div class="col-lg-6">
                                    <div class="info-container">
                                        <ul class="list-unstyled mb-50">
                                            <li class="mb-50 d-flex align-items-center">
                                                <span class="h6">Register KW : </span>
                                                <span
                                                    class="text-dark  ms-1"><b>{{ $salesMaster->register_kw != '' || $salesMaster->register_kw != null ? $salesMaster->register_kw : 'N/A' }}</b></span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                <!-- / Third -->

                                <!-- Third -->
                                <div class="col-lg-6">
                                    <div class="info-container">
                                        <ul class="list-unstyled mb-50">
                                            <li class="mb-1  d-flex align-items-center">
                                                <span class="h6">Consumer Type : </span>
                                                <span
                                                    class="text-dark  ms-1"><b>{{ $salesMaster->consumer_type != '' || $salesMaster->consumer_type != null ? $salesMaster->consumer_type : 'N/A' }}</b></span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                <!-- / Third -->

                                <div class="col-lg-12 mt-50 mb-50">
                                    <h5 class="font-black" style="font-weight: bold;color: var(--ck-color-base-text);">
                                        Contact Details</h5>
                                    <hr class="m-0" />
                                </div>

                                <div class="col-lg-12">

                                    <table class="table table-borderless">
                                        <tr>
                                            <td> <span class="h6">Contact No. :</span></td>
                                            <td> <span
                                                    class="text-dark  ms-1"><b>{{ $salesMaster->contact_number != '' || $salesMaster->contact_number != null ? $salesMaster->contact_number : 'N/A' }}</b></span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td> <span class="h6">Email :</span></td>
                                            <td> <span
                                                    class="text-dark  ms-1"><b>{{ $salesMaster->email != '' || $salesMaster->email != null ? $salesMaster->email : 'N/A' }}</b></span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td> <span class="h6">Address :</span></td>
                                            <td> <span
                                                    class="text-dark  ms-1"><b>{{ $salesMaster->address != '' || $salesMaster->address != null ? $salesMaster->address : 'N/A' }}</b></span>
                                            </td>
                                        </tr>
                                    </table>
                                    <table class="table table-borderless">
                                        <tr>
                                            <td> <span class="h6">District :</span> <span
                                                    class="text-dark  ms-1"><b>{{ $salesMaster->district->name != '' || $salesMaster->district->name != null ? $salesMaster->district->name : 'N/A' }}</b></span>
                                            </td>
                                            <td> <span class="h6">Taluka :</span> <span
                                                    class="text-dark  ms-1"><b>{{ $salesMaster->taluka->name != '' || $salesMaster->taluka->name != null ? $salesMaster->taluka->name : 'N/A' }}</b></span>
                                            </td>
                                            <td> <span class="h6">Pincode :</span> <span
                                                    class="text-dark  ms-1"><b>{{ $salesMaster->pin_code != '' || $salesMaster->pin_code != null ? $salesMaster->pin_code : 'N/A' }}</b></span>
                                            </td>
                                        </tr>
                                    </table>

                                </div>


                                <div class="col-lg-12 mt-50 mb-50">
                                    <h5 class="font-black" style="font-weight: bold;color: var(--ck-color-base-text);">
                                        Bank
                                        Details</h5>
                                    <hr class="m-0" />
                                </div>

                                <!--  Bank First -->
                                <div class="col-lg-4">
                                    <div class="info-container">
                                        <ul class="list-unstyled mb-50">
                                            <li class="mb-1  d-flex align-items-center">
                                                <span class="h6">Bank Name : </span>
                                                <span
                                                    class="text-dark ms-1"><b>{{ $salesMaster->bank_name != '' || $salesMaster->bank_name != null ? $salesMaster->bank_name : 'N/A' }}</b></span>
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
                                                    class="text-dark ms-1"><b>{{ $salesMaster->bank_account != '' || $salesMaster->bank_account != null ? $salesMaster->bank_account : 'N/A' }}</b></span>
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
                                                    class="text-dark ms-1"><b>{{ $salesMaster->ifsc_code != '' || $salesMaster->ifsc_code != null ? $salesMaster->ifsc_code : 'N/A' }}</b></span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                <!-- / Bank Third -->


                                <div class="col-lg-12 mt-50 mb-50">
                                    <h5 class="font-black" style="font-weight: bold;color: var(--ck-color-base-text);">
                                        Division Details</h5>
                                    <hr class="m-0" />
                                </div>

                                <div class="col-lg-12">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td> <span class="h6">Sub Division :</span> <span
                                                    class="text-dark ms-1"><b>{{ isset($salesMaster->subDivisionPDF) && $salesMaster->subDivisionPDF->name != '' && $salesMaster->subDivisionPDF->name != null ? $salesMaster->subDivisionPDF->name : 'N/A' }}</b></span>
                                            </td>
                                            <td> <span class="h6">Division :</span> <span
                                                    class="text-dark ms-1"><b>{{ $salesMaster->division != '' || $salesMaster->division != null ? $salesMaster->division : 'N/A' }}</b></span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td> <span class="h6">Circle :</span> <span
                                                    class="text-dark ms-1"><b>{{ $salesMaster->circle != '' || $salesMaster->circle != null ? $salesMaster->circle : 'N/A' }}</b></span>
                                            </td>

                                            <td> <span class="h6">DISCOM :</span> <span
                                                    class="text-dark ms-1"><b>{{ $salesMaster->discom != '' || $salesMaster->discom != null ? $salesMaster->discom : 'N/A' }}</b></span>
                                            </td>
                                        </tr>
                                    </table>
                                </div>


                                <div class="col-lg-12 mt-50 mb-50">
                                    <h5 class="font-black" style="font-weight: bold;color: var(--ck-color-base-text);">
                                        Other Details</h5>
                                    <hr class="m-0" />
                                </div>

                                <div class="col-lg-12">

                                    <table class="table table-borderless">
                                        <tr>
                                            <td> <span class="h6">GST Number :</span> <span
                                                    class="text-dark ms-1"><b>{{ $salesMaster->gst_number != '' || $salesMaster->gst_number != null ? $salesMaster->gst_number : 'N/A' }}</b></span>
                                            </td>
                                            <td> <span class="h6">Aadhaar No. :</span> <span
                                                    class="text-dark"><b>{{ $salesMaster->aadhaar_number != '' || $salesMaster->aadhaar_number != null ? $salesMaster->aadhaar_number : 'N/A' }}</b></span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td> <span class="h6">Phase :</span> <span
                                                    class="text-dark ms-1"><b>{{ $salesMaster->phase != '' || $salesMaster->phase != null ? $salesMaster->phase : 'N/A' }}</b></span>
                                            </td>
                                            <td> <span class="h6">Contracted Load :</span> <span
                                                    class="text-dark ms-1"><b>{{ $salesMaster->contracted_load != '' || $salesMaster->contracted_load != null ? $salesMaster->contracted_load : 'N/A' }}</b></span>
                                            </td>

                                        </tr>
                                        <tr>
                                            <td> <span class="h6">Reference :</span> <span
                                                    class="text-dark"><b>{{ $salesMaster->reference != '' || $salesMaster->reference != null ? $salesMaster->reference : 'N/A' }}</b></span>
                                            </td>
                                            <td> <span class="h6">Agent / Sales Person :</span> <span
                                                    class="text-dark ms-1"><b>{{ $salesMaster->agentsalesperson->name != '' || $salesMaster->agentsalesperson->name != null ? $salesMaster->agentsalesperson->name : 'N/A' }}</b></span>
                                            </td>
                                        </tr>

                                        <tr>
                                            <td> <span class="h6">Meter Installation Date :</span> <span
                                                    class="text-dark ms-1"><b>{{ $salesMaster->meter_installation_date != '' && $salesMaster->meter_installation_date != '1970-01-01' ? date('d-m-Y', strtotime($salesMaster->meter_installation_date)) : 'N/A' }}</b></span>
                                            </td>
                                            <td> <span class="h6">Subsidy Request Date :</span> <span
                                                    class="text-dark ms-1"><b>{{ $salesMaster->subsidy_request_date != '' && $salesMaster->subsidy_request_date != '1970-01-01' ? date('d-m-Y', strtotime($salesMaster->subsidy_request_date)) : 'N/A' }}</b></span>
                                            </td>
                                        </tr>

                                        <tr>
                                            <td> <span class="h6">Subsidy Verify Date :</span> <span
                                                    class="text-dark ms-1"><b>{{ $salesMaster->subsidy_disbursement_verify_date != '' && $salesMaster->subsidy_disbursement_verify_date != '1970-01-01' ? date('d-m-Y', strtotime($salesMaster->subsidy_disbursement_verify_date)) : 'N/A' }}</b></span>
                                            </td>
                                            <td> <span class="h6">Subsidy Disbursement Date :</span> <span
                                                    class="text-dark ms-1"><b>{{ $salesMaster->subsidy_disbursement_date != '' || $salesMaster->subsidy_disbursement_date != '1970-01-01' ? date('d-m-Y', strtotime($salesMaster->subsidy_disbursement_date)) : 'N/A' }}</b></span>
                                            </td>
                                        </tr>

                                        <tr>
                                            <td colspan="2"> <span class="h6">Subsidy Disbursal Remark :</span>
                                                <span
                                                    class="text-dark ms-1"><b>{{ $salesMaster->subsidy_disbursal_remark }}</b></span>
                                            </td>

                                        </tr>
                                        <tr>
                                            <td colspan="2"> <span class="h6">Remark :</span> <span
                                                    class="text-dark ms-1"><b>{{ $salesMaster->remark }}</b></span>
                                            </td>

                                        </tr>
                                    </table>
                                </div>


                                <div class="col-lg-12 mt-50 mb-50">
                                    <h5 class="font-black" style="font-weight: bold;color: var(--ck-color-base-text);">
                                        Panel & Inverter Details</h5>
                                    <hr class="m-0" />
                                </div>


                                <!--  Panel & Inverter First -->
                                <div class="col-lg-6">
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
                                <div class="col-lg-6">
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
                                <div class="col-lg-12">
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


                                @if (!is_null($salesMaster->installation))
                                    <div class="col-lg-12 mt-50 mb-50">
                                        <h2 class="font-black"
                                            style="font-weight: bold;color: var(--ck-color-base-text);">Installation
                                            Details</h2>
                                        <hr class="m-0" />
                                    </div>

                                    <!--  Other First -->
                                    <div class="col-lg-12">
                                        <div class="info-container">
                                            <ul class="list-unstyled mb-50">
                                                <li class="mb-1  d-flex align-items-center">
                                                    <span class="h6">Installation Date : </span>
                                                    <span
                                                        class="text-dark ms-1"><b>{{ $salesMaster->installation->date != '' && $salesMaster->installation->date != null ? date('d-m-Y', strtotime($salesMaster->installation->date)) : 'N/A' }}</b></span>
                                                </li>
                                            </ul>

                                        </div>
                                    </div>
                                    <!-- / Other First -->

                                    <div class="col-lg-12 mt-50 mb-50">
                                        <h5 class="font-black"
                                            style="font-weight: bold;color: var(--ck-color-base-text);">Panel Details</h5>
                                        <hr class="m-0" />
                                    </div>

                                    <!--  Other First -->
                                    <div class="col-lg-6">
                                        <div class="info-container">
                                            <ul class="list-unstyled mb-50">
                                                <li class="mb-1  d-flex align-items-center">
                                                    <span class="h6">Panel Company : </span>
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
                                    <div class="col-lg-6">
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
                                    <div class="col-lg-6">
                                        <div class="info-container">
                                            <ul class="list-unstyled mb-50">
                                                <li class="mb-1  d-flex align-items-center">
                                                    <span class="h6">Panel Type : </span>
                                                    <span
                                                        class="text-dark ms-1"><b>{{ !is_null($salesMaster->installation->paneltype) && $salesMaster->installation->paneltype->name != '' && $salesMaster->installation->paneltype->name != null ? $salesMaster->installation->paneltype->name : 'N/A' }}</b></span>
                                                </li>

                                            </ul>
                                        </div>
                                    </div>
                                    <!-- / Other First -->
                                    <!--  Other First -->
                                    <div class="col-lg-6">
                                        <div class="info-container">
                                            <ul class="list-unstyled mb-50">

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

                                    <div class="col-lg-12 mt-50 mb-50">
                                        <h5 class="font-black"
                                            style="font-weight: bold;color: var(--ck-color-base-text);">Inverter Details
                                        </h5>
                                        <hr class="m-0" />
                                    </div>
                                    <div class="col-lg-6">
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
                                    <div class="col-lg-6">
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

                                                    <li class="list-group-item">Voltage : <b
                                                            class="data">{{ $inverter->voltage != '' && $inverter->voltage != null ? $inverter->voltage : 'N/A' }}</b>
                                                    </li>

                                                </ul>
                                                <ul class="p-0 list-group list-group-horizontal-md">

                                                    <li class="list-group-item">Model No. : <b
                                                            class="data">{{ $inverter->model_number != '' && $inverter->model_number != null ? $inverter->model_number : 'N/A' }}</b>
                                                    </li>
                                                    <li class="list-group-item">Serial No. : <b
                                                            class="data">{{ $inverter->serial_no_of_inverter != '' && $inverter->serial_no_of_inverter != null ? $inverter->serial_no_of_inverter : 'N/A' }}</b>
                                                    </li>
                                                </ul>
                                            @endforeach
                                        </div>
                                    @endif
                                @endif
                            </div>


                        </div>
                    </div>

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

                                                        @if ($value->image != '')
                                                            <div class="d-flex flex-wrap gap-1 mt-50">
                                                                <a href="{{ asset('upload/inquiry') . '/' . $value->image }}"
                                                                    class="fancybox" data-fancybox="gallery"
                                                                    rel="{{ $value->id }}">
                                                                    <img src="{{ asset('upload/inquiry') . '/' . $value->image }}"
                                                                        alt="Image" class="rounded" width="50"
                                                                        height="auto">
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
                                            <p class="text-muteds">No lead activity found.</p>
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
                            <input type="hidden" name="sales_master_id" value="{{ $salesMaster->id }}">
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
