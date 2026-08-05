<section class="vertical-wizards">
    <form id="form" class="form" action="{{ route('status-save', $salesMaster->id) }}" method="POST"
        enctype="multipart/form-data">
        @csrf

        <div class="bs-stepper vertical vertical-wizard-example shadow-none">
            <div class="bs-stepper-header p-1">
                <div class="step active" data-target="#account-details-vertical" role="tab"
                    id="account-details-vertical-trigger">
                    <button type="button" class="step-trigger p-50" aria-selected="true">
                        <span class="bs-stepper-box">2</span>
                        <span class="bs-stepper-label">
                            <span class="bs-stepper-title">Pending Approval</span>
                            <span class="bs-stepper-subtitle">Ragistration Portal Details</span>
                        </span>
                    </button>
                </div>
                <div class="step" data-target="#personal-info-vertical" role="tab"
                    id="personal-info-vertical-trigger">
                    <button type="button" class="step-trigger p-50" aria-selected="false">
                        <span class="bs-stepper-box">3</span>
                        <span class="bs-stepper-label">
                            <span class="bs-stepper-title">Feasibility Approved</span>
                            <span class="bs-stepper-subtitle">Feasibility Details</span>
                        </span>
                    </button>
                </div>
                <div class="step" data-target="#meter_charge_paid" role="tab" id="personal-info-vertical-trigger">
                    <button type="button" class="step-trigger p-50" aria-selected="false">
                        <span class="bs-stepper-box">4</span>
                        <span class="bs-stepper-label">
                            <span class="bs-stepper-title">Meter Charge Paid</span>
                            <span class="bs-stepper-subtitle">Meter Charge Details</span>
                        </span>
                    </button>
                </div>
                <div class="step" data-target="#address-step-vertical" role="tab"
                    id="address-step-vertical-trigger">
                    <button type="button" class="step-trigger p-50" aria-selected="false">
                        <span class="bs-stepper-box">8</span>
                        <span class="bs-stepper-label">
                            <span class="bs-stepper-title">Installation Pending</span>
                            <span class="bs-stepper-subtitle">Installation Assign Person</span>
                        </span>
                    </button>
                </div>
                <div class="step" data-target="#social-links-vertical" role="tab"
                    id="social-links-vertical-trigger">
                    <button type="button" class="step-trigger p-50" aria-selected="false">
                        <span class="bs-stepper-box">10</span>
                        <span class="bs-stepper-label">
                            <span class="bs-stepper-title">Meter Application Done</span>
                            <span class="bs-stepper-subtitle">Meter Application Details</span>
                        </span>
                    </button>
                </div>


                <div class="step" data-target="#commission-vertical" role="tab" id="commission-vertical-trigger">
                    <button type="button" class="step-trigger p-50" aria-selected="false">
                        <span class="bs-stepper-box">₹</span>
                        <span class="bs-stepper-label">
                            <span class="bs-stepper-title">Commission</span>
                            <span class="bs-stepper-subtitle">Commission Amount Details</span>
                        </span>
                    </button>
                </div>

            </div>
            <div class="bs-stepper-content p-1">
                <div id="account-details-vertical" class="content dstepper-block active" role="tabpanel"
                    aria-labelledby="account-details-vertical-trigger">
                    <!-- <div class="content-header">
                        <h5 class="mb-0">Pending Approval</h5>
                        <small class="text-muted">Enter Ragistration Portal Details.</small>
                    </div> -->
                    <div class="row">
                        <div class="mb-2 col-md-6">
                            <label class="form-label" for="ragistration_portal">Registration Portal</label>
                            <div class="d-flex">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="ragistration_portal"
                                        id="national" value="National"
                                        @if ($salesMaster->ragistration_portal == 'National') checked @endif>
                                    <label class="form-check-label" for="national">National</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="ragistration_portal"
                                        id="grda" value="GEDA" @if ($salesMaster->ragistration_portal == 'GEDA') checked @endif>
                                    <label class="form-check-label" for="grda">GEDA</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="ragistration_portal"
                                        id="other" value="Other"
                                        @if ($salesMaster->ragistration_portal == 'Other') checked @endif>
                                    <label class="form-check-label" for="other">Other</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="mb-1 col-md-6">
                            <label class="form-label" for="ragistration_numbar">Registration Numbar</label>
                            <input type="text" class="form-control" name="ragistration_number"
                                id="ragistration_number" placeholder="Registration Number"
                                value="{{ $salesMaster->ragistration_number }}">
                            <span class=" invalid-feedback d-block" id="error_ragistration_number"
                                role="alert"></span>
                        </div>
                        <div class="mb-1 col-md-6">
                            <label class="form-label" for="ragistration_date">Registration Date</label>
                            <input type="text" class="form-control flatpickr-date" name="ragistration_date"
                                id="ragistration_date" placeholder="Registration Date"
                                value="{{ $salesMaster->ragistration_date != '' && $salesMaster->ragistration_date != '0000-00-00' ? date('d-m-Y', strtotime($salesMaster->ragistration_date)) : '' }}">
                            <span class=" invalid-feedback d-block" id="error_ragistration_date"
                                role="alert"></span>
                        </div>
                    </div>
                </div>
                <div id="personal-info-vertical" class="content dstepper-block" role="tabpanel"
                    aria-labelledby="personal-info-vertical-trigger">
                    <!-- <div class="content-header">
                        <h5 class="mb-0">Feasibility Approved</h5>
                        <small>Enter Feasibility Details</small>
                    </div> -->
                    <div class="row">
                        <div class="mb-1 col-md-6">
                            <label class="form-label" for="feasibility_discom_sr_number">Discom Sr. Number </label>
                            <input type="text" class="form-control" name="feasibility_discom_sr_number"
                                id="feasibility_discom_sr_number"
                                value="{{ $salesMaster->feasibility_discom_sr_number }}"
                                placeholder="Feasibility Discom Sr. Number">
                            <span class="invalid-feedback d-block" id="error_feasibility_discom_sr_number"
                                role="alert"></span>
                        </div>
                        <div class="mb-1 col-md-6">
                            <label class="form-label" for="feasibility_date">Feasibility Date</label>
                            <input type="text" class="form-control flatpickr-date feasibility_date"
                                name="feasibility_date" id="feasibility_date" placeholder="Feasibility Date"
                                value="{{ $salesMaster->feasibility_date != '' && $salesMaster->feasibility_date != '0000-00-00' ? date('d-m-Y', strtotime($salesMaster->feasibility_date)) : '' }}">
                            <span class="invalid-feedback d-block" id="error_feasibility_date" role="alert"></span>
                        </div>
                        <div class="mb-1 col-md-6">
                            <label class="form-label" for="feasibility_amount">Feasibility Amount</label>
                            <input type="text" class="form-control" name="feasibility_amount"
                                id="feasibility_amount" value="{{ $salesMaster->feasibility_amount }}"
                                placeholder="Feasibility Amount">
                            <span class="invalid-feedback d-block" id="error_feasibility_amount"
                                role="alert"></span>
                        </div>
                    </div>
                </div>
                <div id="meter_charge_paid" class="content dstepper-block" role="tabpanel"
                    aria-labelledby="meter_charge_paid-trigger">
                    <div class="row">
                        <div class="mb-1 col-md-6">
                            <label class="form-label" for="feasibility_amount">Feasibility Amount : </label>
                            @if ($salesMaster->feasibility_amount != '')
                                <b>₹ {{ number_format($salesMaster->feasibility_amount, 2) }}</b>
                            @else
                                <b>₹ {{ number_format(0, 2) }}</b>
                            @endif
                        </div>
                        <div class="mb-1 col-md-6"></div>
                        <div class="mb-1 col-md-6">
                            <label class="form-label" for="payment_ref_number">Payment Ref. Number</label>
                            <input type="text" class="form-control" name="payment_ref_number"
                                id="payment_ref_number" placeholder="Payment Ref. Number"
                                value="{{ $salesMaster->payment_ref_number }}">
                            <span class="invalid-feedback d-block" id="error_payment_ref_number"
                                role="alert"></span>
                        </div>
                        <div class="mb-1 col-md-6">
                            <label class="form-label" for="payment_date">Payment Date</label>
                            <input type="text" class="form-control flatpickr-date payment_date"
                                name="payment_date" id="payment_date" placeholder="Payment Date"
                                value="{{ $salesMaster->installation_date != '' && $salesMaster->installation_date != '0000-00-00' ? date('d-m-Y', strtotime($salesMaster->installation_date)) : '' }}">
                            <span class="invalid-feedback d-block" id="error_payment_date" role="alert"></span>
                        </div>
                        <div class="col-12 col-md-12 mb-1 custom-input-group">
                            <label class="form-label" for="payment_receipt">Payment Receipt</label>
                            <input type="file" class="form-control" name="payment_receipt" id="payment_receipt">
                            @if ($salesMaster->payment_receipt != '')
                                <a href="{{ asset('upload/document/' . $salesMaster->payment_receipt) }}"
                                    download="Payment Receipt">
                                    <button type="button" class="btn btn-sm mt-1 btn-outline-primary"><span>Payment
                                            Receipt</span> <i data-feather="download" class="me-25"></i></button>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
                <div id="address-step-vertical" class="content dstepper-block" role="tabpanel"
                    aria-labelledby="address-step-vertical-trigger">
                    <!-- <div class="content-header">
                        <h5 class="mb-0">Installation Pending</h5>
                        <small>Enter Invoice And Installation Details</small>
                    </div> -->
                    <div class="row">
                        <div class="mb-1 col-md-6">
                            <label class="form-label" for="invoice_no">Invoice Number</label>
                            <input type="text" class="form-control" name="invoice_no" id="invoice_no"
                                placeholder="Invoice Number" value="{{ $salesMaster->invoice_no }}">
                            <span class="invalid-feedback d-block" id="error_invoice_no" role="alert"></span>
                        </div>
                        <div class="mb-1 col-md-6">
                            <label class="form-label" for="invoice_date">Invoice Date</label>
                            <input type="text" class="form-control flatpickr-date invoice_date"
                                name="invoice_date" id="invoice_date" placeholder="Invoice Date"
                                value="{{ $salesMaster->invoice_date != '' && $salesMaster->invoice_date != '0000-00-00' ? date('d-m-Y', strtotime($salesMaster->invoice_date)) : '' }}">
                            <span class="invalid-feedback d-block" id="error_invoice_date" role="alert"></span>
                        </div>
                        <div class="mb-1 col-md-6">
                            <label class="form-label" for="installation_asian_person">Installation Assign
                                Person</label>
                            <select class="form-control form-select select2 custom-select2"
                                name="installation_asian_person" id="installation_asian_person">
                                <option selected>{{ __('message.-- Select --') }}</option>
                                @foreach ($user as $value)
                                    <option value="{{ $value->id }}"
                                        {{ isset($salesMaster) && $salesMaster->installation_asian_person == $value->id ? 'selected' : '' }}>
                                        {{ $value->name }} {{ $value->last_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div id="social-links-vertical" class="content dstepper-block" role="tabpanel"
                    aria-labelledby="social-links-vertical-trigger">
                    <!-- <div class="content-header">
                        <h5 class="mb-0">Meter Application Done</h5>
                        <small>Enter Meter Application Details</small>
                    </div> -->
                    <div class="row">
                        <div class="mb-1 col-md-6">
                            <label class="form-label" for="couriar_no">Courier Number</label>
                            <input type="text" class="form-control" name="couriar_no" id="couriar_no"
                                value="{{ $salesMaster->couriar_no }}" placeholder="Courier Number">
                            <span class="invalid-feedback d-block" id="error_couriar_no" role="alert"></span>
                        </div>
                        <div class="mb-1 col-md-6">
                            <label class="form-label" for="courair_company">Courier Company</label>
                            <input type="text" class="form-control" name="courair_company"
                                value="{{ $salesMaster->courair_company }}" id="courair_company"
                                placeholder="Courier Company">
                            <span class="invalid-feedback d-block" id="error_courair_company" role="alert"></span>
                        </div>
                        <div class="mb-1 col-md-6">
                            <label class="form-label" for="meter_application_date">Meter Application Date</label>
                            <input type="text" class="form-control  flatpickr-date meter_application_date"
                                name="meter_application_date"
                                value="{{ $salesMaster->meter_application_date != '' && $salesMaster->meter_application_date != '0000-00-00' ? date('d-m-Y', strtotime($salesMaster->meter_application_date)) : '' }}"
                                id="meter_application_date" placeholder="Meter Application Date">
                            <span class="invalid-feedback d-block" id="error_meter_application_date"
                                role="alert"></span>
                        </div>
                        <div class="mb-1 col-md-6">
                            <label class="form-label" for="meter_asian_person">Meter Assign Person</label>
                            <select class="form-control form-select select2 custom-select2" name="meter_asian_person"
                                id="meter_asian_person">
                                <option selected disabled>{{ __('message.-- Select --') }}</option>
                                @foreach ($user as $value)
                                    <option value="{{ $value->id }}"
                                        {{ isset($salesMaster) && $salesMaster->meter_asian_person == $value->id ? 'selected' : '' }}>
                                        {{ $value->name }} {{ $value->last_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-1 col-md-12">
                            <label class="form-label" for="meter_application_oc">Meter Application OC Copy</label>
                            <input type="file" class="form-control" name="meter_application_oc"
                                id="meter_application_oc">

                            @if ($salesMaster->meter_application_oc != '')
                                <a href="{{ asset('upload/document/' . $salesMaster->meter_application_oc) }}"
                                    download="Meter Application OC">
                                    <button type="button" class="btn btn-sm mt-1 btn-outline-primary"><span>Meter
                                            Application OC</span> <i data-feather="download"
                                            class="me-25"></i></button>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>

                <div id="commission-vertical" class="content dstepper-block" role="tabpanel"
                    aria-labelledby="commission-vertical-trigger">
                    <div class="content-header">
                        <h5 class="mb-0">Payout Details</h5>
                        <small>Individual Payout Breakdown After Installation Done</small>
                        <hr/>
                    </div> 
                    <div class="row">

                        <div class="mb-1 col-md-12">
                            <label class="form-label" for="commission_amount">Commission <i>({{ ($salesMaster->agentsalesperson->name != "" && $salesMaster->agentsalesperson->name != null) ? $salesMaster->agentsalesperson->name : 'N/A' }})</i></label>
                            <input type="number" class="form-control" name="commission_amount"
                                id="commission_amount" value="{{ $salesMaster->commission_amount }}"
                                placeholder="Enter commission amount">
                            <span class="invalid-feedback d-block" id="error_commission_amount"
                                role="alert"></span>
                        </div>

                        <div class="mb-1 col-md-12">
                        
                            <label class="form-label" for="sub_commission_amount">Sub Commission <i>({{ getSubCommissionMainAgentName($salesMaster->agent_sales_person_id) }})</i></label>
                            <input type="number" class="form-control" name="sub_commission_amount"
                                value="{{ $salesMaster->sub_commission_amount }}" id="sub_commission_amount"
                                placeholder="Enter sub commission amount">
                            <span class="invalid-feedback d-block" id="error_sub_commission_amount"
                                role="alert"></span>
                        </div>

                        <div class="mb-1 col-md-12">
                            <label class="form-label" for="installation_amount">Installation <i>({{ ($salesMaster->installation_asian_person != "" && $salesMaster->installation_asian_person != null) ? installationAsignPerson($salesMaster->installation_asian_person) : 'N/A' }})</i></label>
                            <input type="number" class="form-control installation_amount" name="installation_amount"
                                value="{{ $salesMaster->installation_amount }}" id="installation_amount"
                                placeholder="Enter installation amount">
                            <span class="invalid-feedback d-block" id="error_installation_amount"
                                role="alert"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row m-1 mb-2">
            @if (isset($salesMaster) && isset($salesMaster->id))
                <input type="hidden" id="sales_master_id" name="sales_master_id" value="{{ $salesMaster->id }}">
                <input type="hidden" id="type" name="type" value="{{ $type }}">
            @endif
            <div class="col-12 col-md-10 custom-input-group">
                <label class="form-label" for="remark">Remark</label>
                <input type="text" class="form-control" name="remark" id="remark"
                    value="{{ $salesMaster->remark }}" placeholder="Remark (If Any)">
                <span class="invalid-feedback d-block" id="error_remark" role="alert"></span>
            </div>
            <div class="col-12 col-md-2 pt-2">
                <button type="button"
                    class="btn btn-sm btn-primary float-end save-status-form ">{{ __('message.Submit') }}</button>
            </div>
        </div>
        <!--
<div class="col-12 col-md-6 mb-1 custom-input-group">
            <label class="form-label" for="couriar_ditails">Courier Details</label>
            <input type="text" class="form-control" name="couriar_ditails" id="couriar_ditails" value="{{ $salesMaster->couriar_ditails }}" placeholder="Courier Details">
            <span class="invalid-feedback d-block" id="error_couriar_ditails" role="alert"></span>
        </div> -->
    </form>
</section>
