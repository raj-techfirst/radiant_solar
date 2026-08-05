@extends('layouts.app')
@section('title', 'Sales Order')
@section('content')
    <div class="row">
        <div class="col-12 mb-1">
            <h4 class="content-header-title float-start">Sales Order List</h4>
            {{-- @can('sales-master-create') --}}
            <a role="button" class="btn btn-sm btn-primary float-end" href="{{ route('sales-master.create') }}"><i
                    class="fa fa-plus me-25"></i> {{ __('message.Add New') }}</a>
            {{-- @endcan  --}}
        </div>

        <div class="col-12">
            <div class="card p-1">
                <div class="row">
                    <div class="col-12">
                        <h3>Filter</h3>
                    </div>
                    <div class="col-sm-12 col-md-3 col-lg-2 custom-input-group">
                        <label class="form-label" for="from_date">From Date</label>
                        <input type="text" class="form-control flatpickr-date" name="date" id="from_date"
                            placeholder="dd-mm-yyyy">
                    </div>
                    <div class="col-sm-12 col-md-3 col-lg-2 custom-input-group">
                        <label class="form-label" for="from_date">To Date</label>
                        <input type="text" class="form-control flatpickr-date" name="date" id="to_date"
                            placeholder="dd-mm-yyyy">
                    </div>
                    <div class="col-sm-12 col-md-6 col-lg-2 custom-input-group">
                        <label class="form-label" for="consumer">Consumer</label>
                        <input type="text" class="form-control" name="consumer" id="consumer"
                            placeholder="Name / Mobile / Consumer Number">
                    </div>

                    <div class="col-sm-12 col-md-3 col-lg-2 custom-input-group">
                        <label class="form-label" for="agent_sales_person_id">Agent / Sales Person</label>
                        <select class="form-select select2" name="agent_sales_person_id" id="agent_sales_person_id">
                            <option value="" selected>ALL</option>
                            @foreach ($agentSalesPerson as $value)
                                <option value="{{ $value->id }}">{{ $value->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-sm-12 col-md-3 col-lg-2 custom-input-group">
                        <label class="form-label" for="status">Status(Include)</label>
                        <select class="form-select select2" name="status" id="status">
                            <option value="" selected>ALL Status</option>
                            @foreach (allSalesStatus() as $statusKey => $statusValue)
                                <option value="{{ $statusValue['value'] }}">{{ $statusValue['name'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-sm-12 col-md-3 col-lg-2 custom-input-group">
                        <label class="form-label" for="not_status">Status(Not Include)</label>
                        <select class="form-select select2" name="not_status" id="not_status">
                            <option value="" selected>None</option>
                            @foreach (allSalesStatus() as $statusKey => $statusValue)
                                <option value="{{ $statusValue['value'] }}">{{ $statusValue['name'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-sm-12 col-md-3 col-lg-2 custom-input-group">
                        <label class="form-label" for="ragistration_portal">Registration Portal</label>
                        <select class="form-select select2" name="ragistration_portal" id="ragistration_portal">
                            <option value="" selected>All</option>
                            <option value="National">National</option>
                            <option value="GEDA">GEDA</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>

                    <div class="col-sm-12 col-md-3 col-lg-2 custom-input-group">
                        <label class="form-label" for="file_type">Payment Type</label>
                        <select class="form-select select2" name="file_type" id="file_type">
                            <option value="" selected>All</option>
                            <option value="C">Cash</option>
                            <option value="L">Loan</option>
                        </select>
                    </div>

                    <div class="col-sm-12 col-md-6 col-lg-8 custom-input-group pt-2">
                        <div class="d-flex justify-content-end">
                            <button class="btn btn-gradient-primary btn-sm filter" type="button" data-bs-toggle="tooltip"
                                data-placement="top" title="Click to Filter">
                                <i data-feather='search'></i>
                            </button>
                            <button class="btn btn-gradient-danger btn-sm reset ms-1" type="reset"
                                data-bs-toggle="tooltip" data-placement="top" title=" Click to Reset Filter">
                                <i data-feather='x'></i>
                            </button>
                            <button class="btn btn-gradient-success btn-sm download ms-1" type="button"
                                data-bs-toggle="tooltip" data-placement="top" title="Click to Download">
                                <i data-feather='download'></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card p-1">
                <div class="table-responsive">
                    <table id="table" class="datatables-basic table table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>{{ __('message.Action') }}</th>
                                <th>pdf</th>
                                <th>status</th>
                                <th> Register KW</th>
                                <th>Consumer Type</th>
                                <th>Consumer Name</th>
                                <th>Consumer Number</th>
                                <th>Master Date</th>
                                <th>Agent</th>
                            </tr>
                        </thead>
                        <tbody>

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="exampleModal" aria-labelledby="exampleModalLabel" tabindex="-2">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-transparent border-bottom">
                    <h4 class="text-center mb-0" id="exampleModalTitle">{{ __('message.Details') }}</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-2" id="body">

                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="applicationexampleModal" tabindex="-1" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">
                <div class="modal-header bg-transparent border-bottom">
                    <h4 class="text-center mb-0" id="exampleModalTitle">Application Form</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-2" id="application-body">

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
                <div class="modal-body p-0" id="status-body">

                </div>
            </div>
        </div>
    </div>


    <div class="modal fade" id="documentexampleModal" tabindex="-1" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">
                <div class="modal-header bg-transparent border-bottom">
                    <h4 class="text-center mb-0" id="exampleModalTitle">document Form</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-2" id="document-body">

                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="paymentexampleModal" tabindex="-1" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-transparent border-bottom">
                    <h4 class="text-center mb-0" id="exampleModalTitle">Payment</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-2" id="payment-body">
                    <form id="formNew" class="form" action="javascript:void(0);" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="col-12 col-md-6 mb-1 custom-input-group">
                                <input type="hidden" id="sales_id" name="sales_master_id" value="">
                                <input type="hidden" name="payment_collection_id" id="payment_collection_id"
                                    value="">
                                <label class="form-label" for="payment_type">Payment Type<sup
                                        class="error">*</sup></label>
                                <select class="form-control  form-select select2 custom-select2" name="payment_type"
                                    id="payment_type">
                                    <option selected disabled>-- Select --</option>
                                    <option value="Cash"
                                        {{ isset($paymetCollection) && $paymetCollection->payment_type == 'Cash' ? 'selected' : '' }}>
                                        Cash</option>
                                    <option value="Cheque"
                                        {{ isset($paymetCollection) && $paymetCollection->payment_type == 'Cheque' ? 'selected' : '' }}>
                                        Cheque</option>
                                    <option value="Disbursement"
                                        {{ isset($paymetCollection) && $paymetCollection->payment_type == 'Disbursement' ? 'selected' : '' }}>
                                        Disbursement
                                    </option>
                                    <option value="NEFT"
                                        {{ isset($paymetCollection) && $paymetCollection->payment_type == 'NEFT' ? 'selected' : '' }}>
                                        NEFT</option>
                                    <option value="UPI"
                                        {{ isset($paymetCollection) && $paymetCollection->payment_type == 'UPI' ? 'selected' : '' }}>
                                        UPI</option>
                                    <option value="RTGS"
                                        {{ isset($paymetCollection) && $paymetCollection->payment_type == 'RTGS' ? 'selected' : '' }}>
                                        RTGS</option>
                                    <option value="IMPS"
                                        {{ isset($paymetCollection) && $paymetCollection->payment_type == 'IMPS' ? 'selected' : '' }}>
                                        IMPS</option>
                                    <option value="Discount"
                                        {{ isset($paymetCollection) && $paymetCollection->payment_type == 'Discount' ? 'selected' : '' }}>
                                        Discount</option>
                                    <option value="Adjustment"
                                        {{ isset($paymetCollection) && $paymetCollection->payment_type == 'Adjustment' ? 'selected' : '' }}>
                                        Adjustment
                                    </option>
                                </select>
                                <span class="invalid-feedback d-block" id="error_payment_type" role="alert"></span>
                            </div>
                            <div class="col-12 col-md-6 mb-1 custom-input-group d-none" id="bankDropdown">
                                <label class="form-label" for="bank_id">Credited In (Bank)</label>
                                <select class="form-control form-select select2 custom-select2" name="bank_id"
                                    id="bank_id">
                                    <option selected disabled>-- Select Bank --</option>
                                    @if (isset($banks))
                                        @foreach ($banks as $bank)
                                            <option value="{{ $bank->id }}">{{ $bank->name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                                <span class="invalid-feedback d-block" id="error_bank_id" role="alert"></span>
                            </div>
                            <div class="col-12 col-md-6 mb-1 custom-input-group d-none" id="upiFields">
                                <label class="form-label" for="upi_id">UPI ID</label>
                                <input type="text" class="form-control" name="upi_id" id="upi_id"
                                    placeholder="UPI ID*">
                                <span class="invalid-feedback d-block" id="error_upi_id" role="alert"></span>
                            </div>
                            <div class="col-12 col-md-6 mb-1 custom-input-group d-none" id="chequeFields">
                                <label class="form-label" for="cheque_number">Cheque Number</label>
                                <input type="text" class="form-control" name="cheque_number" id="cheque_number"
                                    placeholder="Cheque Number *">
                                <span class="invalid-feedback d-block" id="error_cheque_number" role="alert"></span>
                            </div>
                            <div class="col-12 col-md-6 mb-1 custom-input-group d-none" id="bankFields">
                                <label class="form-label" for="bank_name">Bank Name</label>
                                <input type="text" class="form-control" name="bank_name" id="bank_name"
                                    placeholder="Bank Name*">
                                <span class="invalid-feedback d-block" id="error_bank_name" role="alert"></span>
                            </div>
                            <div class="col-12 col-md-6 mb-1 custom-input-group d-none" id="branchFields">
                                <label class="form-label" for="branch_name">Branch Name</label>
                                <input type="text" class="form-control" name="branch_name" id="branch_name"
                                    placeholder="Branch Name *">
                                <span class="invalid-feedback d-block" id="error_branch_name" role="alert"></span>
                            </div>
                            <div class="col-12 col-md-6 mb-1 custom-input-group d-none" id="utrFields">
                                <label class="form-label" for="utr_number">UTR Number</label>
                                <input type="text" class="form-control" name="utr_number" id="utr_number"
                                    placeholder="UTR Number*">
                                <span class="invalid-feedback d-block" id="error_utr_number" role="alert"></span>
                            </div>

                            <div class="col-12 col-md-6 mb-1 custom-input-group " id="amountFields">
                                <label class="form-label" for="amount">Amount</label>
                                <input type="number" class="form-control pending_amonut" name="amount" id="amount"
                                    value="">
                                <span class="invalid-feedback d-block" id="error_amount" role="alert"></span>
                            </div>
                            <div class="col-12 col-md-6 mb-1 custom-input-group " id="dateFields">
                                <label class="form-label" for="payment_date">Payment Date <sup
                                        class="error">*</sup></label>
                                <input type="text" class="form-control flatpickr-date payment_date "
                                    name="payment_date" id="payment_date" required placeholder="Payment Date *">
                                <span class="invalid-feedback d-block" id="error_payment_date" role="alert"></span>
                            </div>

                            <div class="col-12 col-md-6 mb-1 custom-input-group">
                                <label class="form-label" for="file">File</label>
                                <input type="file" class="form-control" name="file" id="file">
                                <div id="file_preview" class="mt-1"></div>
                                <span class="invalid-feedback d-block" id="error_file" role="alert"></span>
                            </div>
                            <div class="col-12 col-md-6 mb-1 custom-input-group" id="remark">
                                <label class="form-label" for="remark">Remark</label>
                                <input type="text" class="form-control " name="remark" id="remark"
                                    placeholder="Remark">
                            </div>
                            <div class="col-md-12 col-12">
                                <button type="submit"
                                    class="btn btn-sm btn-primary float-end save">{{ __('message.Submit') }}</button>
                            </div>
                        </div>

                        <div class="row payment-data-div">

                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="paymentexampleModal" tabindex="-1" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-md">
            <div class="modal-content">
                <div class="modal-header bg-transparent border-bottom">
                    <h4 class="text-center mb-0" id="exampleModalTitle">Payment</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-2" id="payment-body">
                    <form id="pqr_form" class="form" action="" method="POST">
                        @csrf
                        <div class="row">
                            <input type="hidden" id="my_sales_id" name="sales_master_id" value="">
                            <div class="col-md-12 col-12">
                                <button type="submit"
                                    class="btn btn-sm btn-primary float-end pqr_save">{{ __('message.Submit') }}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="receveidexampleModal" tabindex="-1" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-md">
            <div class="modal-content">
                <div class="modal-header bg-transparent border-bottom">
                    <h4 class="text-center mb-0" id="exampleModalTitle"></h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-2" id="receveid-body">
                    <form id="receveid_form" class="form" action="" method="POST">
                        @csrf
                        <div class="row">
                            <input type="hidden" id="receveid_id" name="receveid_id" value="">
                            <div class="col-md-12 col-12">
                                <button type="submit"
                                    class="btn btn-sm btn-primary float-end receveid_save">{{ __('message.Submit') }}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="installationexampleModal" tabindex="-1" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-md">
            <div class="modal-content">
                <div class="modal-header bg-transparent border-bottom">
                    <h4 class="text-center mb-0" id="exampleModalTitle"></h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-2" id="installation-body">
                    <form id="installation_form" class="form" action="" method="POST">
                        @csrf
                        <div class="row">
                            <input type="hidden" id="installation_id" name="installation_id" value="">
                            <div class="col-md-12 col-12">
                                <button type="submit"
                                    class="btn btn-sm btn-primary float-end installation_save">{{ __('message.Submit') }}</button>
                            </div>
                        </div>
                    </form>
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

    <div class="modal fade" id="meterApp" tabindex="-1" aria-labelledby="meterAppLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-md">
            <div class="modal-content">
                <div class="modal-header bg-transparent border-bottom">
                    <h4 class="text-center mb-0" id="meterAppTitle">Meter Application</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-2" id="meter-body">
                    <form id="form_meter" class="form" action="javascript:void(0);" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <input type="hidden" id="meter_sales_master_id" name="id" value="">
                            <input type="hidden" id="meter_status" name="status" value="">

                            <div class="col-12 col-md-12 mb-1 custom-input-group d-none" id="meter_file">
                                <label class="form-label" for="proforma_15">Proforma 15</label>
                                <input type="file" class="form-control" name="proforma_15" id="proforma_15">
                                <a id="imgPreview" href="" download="Proforma 15">
                                    <button type="button" class="btn btn-sm mt-1 btn-outline-primary"><span>Proforma
                                            15</span> <i data-feather="download" class="me-25"></i></button>
                                </a>
                            </div>

                            <div class="col-12 col-md-12 mb-1 custom-input-group d-none" id="meter_date">
                                <label class="form-label" for="meter_installation_date">Date</label>
                                <input type="text" class="form-control flatpickr-date meter_installation_date"
                                    name="meter_installation_date" id="meter_installation_date"
                                    placeholder="Meter Installation Date">
                            </div>
							
							
                            <div class="col-12 col-md-12 mb-1 custom-input-group d-none" id="meter_subsidy">
							
								<div class="">
									<small class="d-block">Non Subsidy</small>
									<h6 class="mb-0 form-switch pt-25"><input class="form-check-input" type="checkbox" role="switch" name="is_non_subsidy" value="yes">
									Yes </h6>
								</div>
                            </div>
							

                            <div class="col-12 col-md-12 mb-1 custom-input-group d-none" id="subsidy_date">
                                <label class="form-label" for="subsidy_request_date">Date</label>
                                <input type="text" class="form-control flatpickr-date subsidy_request_date"
                                    name="subsidy_request_date" id="subsidy_request_date"
                                    placeholder="Subsidy Request Date">
                            </div>

                            <div class="col-12 col-md-12 mb-1 custom-input-group d-none" id="sub_dis_date">
                                <label class="form-label" for="subsidy_disbursement_date">Date</label>
                                <input type="text" class="form-control flatpickr-date"
                                    name="subsidy_disbursement_date" id="subsidy_disbursement_date"
                                    placeholder="Subsidy Disbursement Date">
                            </div>

                            <div class="col-12 col-md-12 mb-1 custom-input-group d-none" id="sub_ver_date">
                                <label class="form-label" for="subsidy_disbursement_verify_date">Verify Date</label>
                                <input type="text" class="form-control flatpickr-date"
                                    name="subsidy_disbursement_verify_date" id="subsidy_disbursement_verify_date"
                                    placeholder="Subsidy Verify Date">
                            </div>

                            <div class="col-12 col-md-12 mb-1 custom-input-group d-none" id="sub_text">
                                <label class="form-label" for="subsidy_disbursal_remark">Remark</label>
                                <textarea type="text" class="form-control flatpickr-date" name="subsidy_disbursal_remark"
                                    id="subsidy_disbursal_remark" placeholder="Type here.."></textarea>
                            </div>

                            <div class="col-md-12 col-12">
                                <button type="submit"
                                    class="btn btn-sm btn-primary float-end save_meter">{{ __('message.Submit') }}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="dispatchListModal" tabindex="-1" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-md">
            <div class="modal-content">
                <div class="modal-header bg-transparent border-bottom">
                    <h4 class="text-center mb-0" id="exampleModalTitle">Dispatch Pending List</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-2" id="payment-body">
                    <form id="form_dispatch_list" class="form" action="javascript:void(0);" method="POST">
                        @csrf
                        <div class="row">
                            <input type="hidden" id="dispach_sales_master_id" name="id" value="">
                            <input type="hidden" id="status" name="status" value="dispach_pending_list">
                            <div class="col-12 col-md-12  mb-1 custom-input-group ">
                                <label class="form-label" for="bom_id">BOM<sup class="error">*</sup></label>
                                <select class="form-select select2" name="bom_id" id="bom_id">
                                    <option value="0" selected disabled>None</option>
                                    @foreach ($boms as $bomValue)
                                        <option value="{{ $bomValue->id }}">{{ $bomValue->bom_name }}</option>
                                    @endforeach
                                </select>
                                <span class="invalid-feedback d-block" id="error_bom_id" role="alert"></span>
                            </div>
                            <div class="col-12 col-md-12  mb-1 custom-input-group penal_watt_inverter">

                            </div>
                            <div class="col-md-12 col-12">
                                <button type="submit"
                                    class="btn btn-sm btn-primary float-end save_dispatch_list">{{ __('message.Submit') }}</button>
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
        var table = '';
        $(function() {

            var type = new URLSearchParams(window.location.search).get('type');
            var value = new URLSearchParams(window.location.search).get('value');
            var value_not = new URLSearchParams(window.location.search).get('value_not');

            if (type == "agent") {
                $('#agent_sales_person_id').val(value);
                $('#agent_sales_person_id').select2();
            }
            if (type == "portal") {
                $('#ragistration_portal').val(value);
                $('#ragistration_portal').select2();
            }
            if (type == "status") {
                console.log(value);
                $('#status').val(value);
                $('#status').select2();

                if (value_not != '' && value_not != null) {
                    $('#not_status').val(value_not);
                    $('#not_status').select2();
                }
            }

            flatpickr('.flatpickr-date', {
                enableTime: false,
                dateFormat: 'd-m-Y',
                defaultDate: '',
            });

            table = $('#table').DataTable({
                ajax: {
                    url: URL,
                    data: function(d) {
                        d.from_date = $('#from_date').val();
                        d.to_date = $('#to_date').val();
                        d.consumer = $('#consumer').val();
                        d.agent_sales_person_id = $('#agent_sales_person_id').val();
                        d.status = $('#status').val();
                        d.not_status = $('#not_status').val();
                        d.ragistration_portal = $('#ragistration_portal').val();
                         d.file_type = $('#file_type').val();

                    }
                },
                processing: true,
                serverSide: true,
                fixedHeader: false,
                scroll: false,
                aLengthMenu: [
                    [20, -1],
                    [20, "All"],
                ],
                columns: [{
                        data: 'id',
                        render: function(data, type, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1;
                        }
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        sortable: false,
                        className: "text-nowrap"
                    },
                    {
                        data: 'pdf',
                        name: 'pdf',
                    },
                    {
                        data: 'application_pending',
                        name: 'application_pending',
                    },
                    {
                        data: 'register_kw',
                        name: 'register_kw',
                    },
                    {
                        data: 'consumer_type',
                        name: 'consumer_type'
                    },
                    {
                        data: 'consumer_name',
                        name: 'consumer_name'
                    },
                    {
                        data: 'consumer_number',
                        name: 'consumer_number'
                    },
                    {
                        data: 'master_create_date',
                        name: 'master_create_date',
                    },
                    {
                        data: 'agentsalesperson.name',
                        name: 'agentsalesperson.name',
                    },
                ],
                initComplete: function(settings, json) {
                    var tooltipTriggerList = [].slice.call(document.querySelectorAll(
                        '[data-bs-toggle="tooltip"]'));
                    var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                        return new bootstrap.Tooltip(tooltipTriggerEl)
                    })
                }
            });
        });

        $(document).on('click', '.save', function() {
            var formData = new FormData($("#formNew")[0]);
            var select = $('.select2');
            if ($("#payment_type").val() != "") {
                $.ajax({
                    type: "POST",
                    url: "{{ route('payment-collection.store') }}",
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
                        if (response.server_error && response.status == false) {
                            toastr.error("{{ __('message.Something went wrong. Please try again.') }}",
                                "{{ __('message.Error') }}");
                        } else if (response.status == false) {
                            $.each(response.errors, function(key, value) {
                                $('#error_' + key).html('<p class="text-danger mb-0">' + value +
                                    '</p>');
                            });
                            toastr.warning(response.message, "{{ __('message.Warning') }}");
                        } else {
                            $('#formNew')[0].reset();
                            toastr.success(response.message, "{{ __('message.Success') }}");
                            setTimeout(function() {
                                location.href = response.data;
                            }, 1000);
                        }
                    }
                });
            } else {
                $("#formNew").validate({
                    rules: {
                        payment_type: {
                            required: true,
                        },
                        payment_date: {
                            required: true,
                        },
                    },
                    messages: {
                        payment_type: {
                            required: "payment Type"
                        },
                        payment_date: {
                            required: "payment Date"
                        },
                    },
                    errorElement: "p",
                    errorClass: "text-danger mb-0 ",

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

        $(document).on('click', '.view', function(e) {
            //e.preventDefault();
            var id = $(this).data('id');
            var url = "{{ route('sales-master.show', 'id') }}".replace('id', id);
            $("#exampleModal").modal("show");
            $.ajax({
                url: url,
                type: 'get',
                datatype: 'json',
                data: {
                    "_token": "{{ csrf_token() }}",
                },
                success: function(response) {
                    $("#body").html(response.html);
                    $('[data-bs-toggle="tooltip"]').tooltip();
                    if (feather) {
                        feather.replace({
                            width: 14,
                            height: 14
                        });
                    }
                }
            });
        });

        $(document).on('click', '.payment-view', function() {
            $(".payment-data-div").html('');
            var id = $(this).data('id');
            var pending_amonut = $(this).data('amt');
            $('#sales_id').val(id);
            $('.pending_amonut').val(pending_amonut);
            $("#paymentexampleModal").modal("show");
            var url = "{{ route('sales-order-payment', 'id') }}".replace('id', id);
            $.ajax({
                url: url,
                type: 'post',
                datatype: 'json',
                data: {
                    "_token": "{{ csrf_token() }}",
                },
                success: function(response) {

                    $(".payment-data-div").html(response.html);
                    $('[data-bs-toggle="tooltip"]').tooltip();
                    if (feather) {
                        feather.replace({
                            width: 14,
                            height: 14
                        });
                    }
                }
            });



        });

        $(document).on('click', '.application-view', function(e) {
            e.preventDefault();
            var id = $(this).data('id');
            var status = $(this).data('value');
            var remark = $(this).data('remark');
            var url = "{{ route('application-save') }}";

            $('#form_meter')[0].reset();

            $('#meter_date').addClass('d-none');
            $('#meter_subsidy').addClass('d-none');
            $('#meter_file').addClass('d-none');
            $('#subsidy_date').addClass('d-none');

            $('#sub_dis_date').addClass('d-none');
            $('#sub_ver_date').addClass('d-none');
            $('#sub_text').addClass('d-none');
            $('#imgPreview').addClass('d-none');
            $('#meterAppTitle').html('');
            if (status == 'dispach_pending_list') {
                $(".penal_watt_inverter").html('');
                var bom_id = $(this).data('bomid');
                var nos = $(this).data('nos');
                var penal = $(this).data('penal');
                var watt = $(this).data('watt');
                var inveter = $(this).data('inveter');
                var registerkw = $(this).data('registerkw');
                $(".penal_watt_inverter").html(
                    '<div class="row"><div class="col-12"><table class="table table-sm"><tr><td colspan="2"><h5 class="mb-1">Panel & Inverter Details</h5></td></tr><tr><td>Panel Nos </td><td><b>' +
                    nos + '</b></td></tr><tr><td>Panel Company </td><td><b>' + penal +
                    '</b></td></tr><tr><td>Panel Watt </td><td><b>' + watt +
                    '</b></td></tr><tr><td>Register KW </td><td><b>' + registerkw +
                    '</b></td></tr><tr><td>Inverter Company </td><td><b>' + inveter +
                    '</b></td></tr></table></div></div>');
                $('#dispach_sales_master_id').val(id);
                $('#bom_id').val(bom_id);
                $('#bom_id').select2({
                    dropdownParent: $('#dispatchListModal')
                });
            } else
            if (status == 'subsidy_receveid') {
                $('#meter_sales_master_id').val(id);
                $('#meter_status').val(status);
                $('#sub_dis_date').removeClass('d-none');
                // $('#sub_ver_date').removeClass('d-none');
                // $('#sub_text').removeClass('d-none');
                var date = $(this).data('date');
                $('#subsidy_disbursement_date').val(date);

                $('#meterAppTitle').html('Subsidy Disbursal');
                $('#meterApp').modal('show');
            } else if (status == 'subsidy_claimed') {
                $('#subsidy_date').removeClass('d-none');
                $('#sub_ver_date').removeClass('d-none');
                $('#sub_text').removeClass('d-none');

                $('#meter_sales_master_id').val(id);
                $('#meter_status').val(status);
                var date = $(this).data('date');
                $('#subsidy_request_date').val(date);
                var date2 = $(this).data('date2');
                $('#subsidy_disbursement_verify_date').val(date2);
                var remark = $(this).data('remark');
                $('#subsidy_disbursal_remark').val(remark);

                $('#meterAppTitle').html('Subsidy Request');
                $('#meterApp').modal('show');
            } else if (status == 'meter_installation') {
                $('#meter_date').removeClass('d-none');
                $('#meter_subsidy').removeClass('d-none');
                $('#meter_file').removeClass('d-none');
                var date = $(this).data('date');
                var img = $(this).data('img');
                var path = "{{ asset('upload/document/') }}";

                if (img != null && img != "") {
                    $('#imgPreview').removeClass('d-none');
                    var fullPath = path + '/' + img;
                    $('#imgPreview').attr('href', fullPath);
                }

                $('#meter_installation_date').val(date);
                $('#meter_sales_master_id').val(id);
                $('#meter_status').val(status);
                $('#meterAppTitle').html('Meter Application');
                $('#meterApp').modal('show');
            } else if (status == "hold_query" || status == "file_cancel_order") {
                Swal.fire({
                    title: "{{ __('message.Are you sure?') }}",
                    icon: 'warning',
                    input: "text",
                    inputValue: remark,
                    inputAttributes: {
                        autocapitalize: "off",
                        placeholder: "Please Enter Reason"
                    },
                    showCancelButton: true,
                    confirmButtonText: "Yes, Change it!",
                    customClass: {
                        confirmButton: 'btn btn-primary',
                        cancelButton: 'btn btn-outline-danger ms-1'
                    },
                    buttonsStyling: false,
                    showLoaderOnConfirm: true,
                    preConfirm: async (reson) => {
                        if (reson == "") {
                            return Swal.showValidationMessage(`Please Enter Reason`);
                        }
                    },
                    allowOutsideClick: () => !Swal.isLoading()
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: url,
                            type: 'POST',
                            datatype: 'json',
                            data: {
                                "id": id,
                                "status": status,
                                "remark": `${result.value}`,
                                "_token": "{{ csrf_token() }}",
                            },
                            success: function(response) {
                                if (response.status) {
                                    toastr.success(response.message, "Success");
                                    table.ajax.reload(null, false);
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
            } else {
                Swal.fire({
                        title: "{{ __('message.Are you sure?') }}",
                        //text: "{{ __('message.You won`t be able to revert this!') }}",
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
                                        table.ajax.reload(null, false);
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
            }
        });

        $('#exampleModal').on('shown.bs.modal', function() {
            $(document).off('focusin.modal');
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
                                    var url = "{{ route('sales-master.show', 'id') }}".replace('id',
                                        id);
                                    $.ajax({
                                        url: url,
                                        type: 'get',
                                        datatype: 'json',
                                        data: {
                                            "_token": "{{ csrf_token() }}",
                                        },
                                        success: function(response) {
                                            $("#body").html('');
                                            $("#body").html(response.html);
                                            $('[data-bs-toggle="tooltip"]').tooltip();
                                            if (feather) {
                                                feather.replace({
                                                    width: 14,
                                                    height: 14
                                                });
                                            }
                                        }
                                    });
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
                                    table.ajax.reload(null, false);
                                    toastr.success("{{ __('message.Deleted successfully.') }}",
                                        "{{ __('message.Success') }}");
                                } else if (response.data.status == false && response.data.server_error) {
                                    toastr.error(response.data.server_error, "{{ __('message.Error') }}");
                                } else {
                                    toastr.warning(
                                        "{{ __('message.This SalesMaster activity has been used.') }}",
                                        "{{ __('message.Warning') }}");
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

        $('.table-responsive').on('show.bs.dropdown', function() {
            $('.table-responsive').css("overflow", "inherit");
        });

        $('.table-responsive').on('hide.bs.dropdown', function() {
            $('.table-responsive').css("overflow", "auto");
        });

        $(document).on('click', '.receveid', function() {
            var id = $(this).data('id');
            var formAction = "{{ route('application-save', 'id') }}".replace('id', id);
            $('#receveid_id').val(id);
            $('#receveid_form').attr('action', formAction);
            $('#receveid_form').submit();
        });

        $(document).on('click', '.installation', function() {
            var id = $(this).data('id');
            var formAction = "{{ route('application-save', 'id') }}".replace('id', id);
            $('#installation_id').val(id);
            $('#installation_form').attr('action', formAction);
            $('#installation_form').submit();
        });

        $(document).on('change', '#payment_type', function () {
            var paymentType = $(this).val();

            if (paymentType == 'Cheque') {
                $('#chequeFields').removeClass('d-none');
                $('#bankFields').removeClass('d-none');
                $('#bankDropdown').removeClass('d-none');
                $('#branchFields').removeClass('d-none');
                $('#upiFields').addClass('d-none');
                $('#utrFields').addClass('d-none');
            } else if (paymentType == 'UPI') {
                $('#chequeFields').addClass('d-none');
                $('#bankFields').removeClass('d-none');
                $('#bankDropdown').removeClass('d-none');
                $('#branchFields').addClass('d-none');
                $('#upiFields').removeClass('d-none');
                $('#utrFields').addClass('d-none');
            } else if (paymentType == 'Cash' || paymentType == 'Discount' || paymentType == 'Adjustment') {
                $('#chequeFields').addClass('d-none');
                $('#bankFields').addClass('d-none');
                $('#bankDropdown').addClass('d-none');
                $('#branchFields').addClass('d-none');
                $('#upiFields').addClass('d-none');
                $('#utrFields').addClass('d-none');
            } else if (paymentType == 'NEFT') {
                $('#chequeFields').addClass('d-none');
                $('#bankFields').removeClass('d-none');
                $('#bankDropdown').removeClass('d-none');
                $('#branchFields').removeClass('d-none');
                $('#upiFields').addClass('d-none');
                $('#utrFields').removeClass('d-none');
            } else if (paymentType == 'RTGS' || paymentType == 'IMPS' || paymentType == 'Disbursement') {
                $('#chequeFields').addClass('d-none');
                $('#bankFields').removeClass('d-none');
                $('#bankDropdown').removeClass('d-none');
                $('#branchFields').removeClass('d-none');
                $('#upiFields').addClass('d-none');
                $('#utrFields').removeClass('d-none');
            }
        });

        $(document).on('click', '.filter', function() {
            table.draw();
        });

        $(document).on('click', '.download', function() {
            $.ajax({
                url: "{{ route('sales-order-report') }}",
                type: 'POST',
                datatype: 'json',
                data: {
                    "from_date": $('#from_date').val(),
                    "to_date": $('#to_date').val(),
                    "consumer": $('#consumer').val(),
                    "agent_sales_person_id": $('#agent_sales_person_id').val(),
                    "status": $('#status').val(),
                    "not_status": $('#not_status').val(),
                    "ragistration_portal": $('#ragistration_portal').val(),
                    "_token": "{{ csrf_token() }}",
                    "file_type": $('#file_type').val(),
                },
                cache: false,
                xhr: function() {
                    var xhr = new XMLHttpRequest();
                    xhr.onreadystatechange = function() {
                        if (xhr.readyState == 2) {
                            if (xhr.status == 200) {
                                xhr.responseType = "blob";
                            } else {
                                xhr.responseType = "text";
                            }
                        }
                    };
                    return xhr;
                },
                success: function(data) {
                    var blob = new Blob([data], {
                        type: "application/octetstream"
                    });
                    var fileName = 'Sales_orders.xlsx';
                    var isIE = false || !!document.documentMode;
                    if (isIE) {
                        window.navigator.msSaveBlob(blob, fileName);
                    } else {
                        var url = window.URL || window.webkitURL;
                        var link = url.createObjectURL(blob);
                        var a = $("<a />");
                        a.attr("download", fileName);
                        a.attr("href", link);
                        $("body").append(a);
                        a[0].click();
                        $("body").remove(a);
                    }
                }
            });
        });

        $(document).on('click', '.reset', function() {
            $('#from_date').val('');
            $('#to_date').val('');
            $('#consumer').val('');
            $("#from_date").flatpickr({
                altInput: true,
                altFormat: 'd-m-Y',
                dateFormat: 'Y-m-d'
            });
            $("#to_date").flatpickr({
                altInput: true,
                altFormat: 'd-m-Y',
                dateFormat: 'Y-m-d'
            });
            $('#agent_sales_person_id').val('');
            $('#status').val('');
            $('#not_status').val('');
            $('#ragistration_portal').val('');
            $('.select2').select2();
             $('#file_type').val('');
            table.draw();
        });

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
                            toastr.error("{{ __('message.Something went wrong. Please try again.') }}",
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
                            // toastr.success(response.message, "{{ __('message.Success') }}");
                            // setTimeout(function() {
                            $("#declarationexampleModal").modal("hide");
                            location.href = response.data;
                            // }, 2000);
                        }
                    }
                });
            } else {
                return false;
            }
        });

        $(document).on('click', '.save_meter', function() {
            var bformData = new FormData($("#form_meter")[0]);
            $.ajax({
                type: "POST",
                url: "{{ route('application-save') }}",
                data: bformData,
                dataType: 'json',
                cache: false,
                contentType: false,
                processData: false,
                beforeSend: function() {
                    $("#error_name").html(' ');
                    $(".save_meter").html(
                        `<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> {{ __('message.Wait') }}`
                        );
                    $(".save_meter").attr('disabled', true);
                },
                success: function(response) {
                    $(".save_meter").html("{{ __('message.Submit') }}");
                    $(".save_meter").attr('disabled', false);
                    if (response.server_error && response.status == false) {
                        toastr.error("{{ __('message.Something went wrong. Please try again.') }}",
                            "{{ __('message.Error') }}");
                    } else if (response.status == false) {
                        $.each(response.errors, function(key, value) {
                            $('#error_' + key).html('<p class="text-danger mb-0">' + value +
                                '</p>');
                        });
                        toastr.warning("{{ __('message.Please input proper data.') }}",
                            "{{ __('message.Warning') }}");
                    } else {
                        $('#form_meter')[0].reset();
                        toastr.success(response.message, "{{ __('message.Success') }}");
                        $("#meterApp").modal("hide");
                        location.reload(true);
                    }
                }
            });
        });



        $(document).on('click', '.save-status-form', function() {
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
                        toastr.error("{{ __('message.Something went wrong. Please try again.') }}",
                            "{{ __('message.Error') }}");
                    }
                }
            });
        });

        $(document).on('click', '.dispatch-pending-list', function() {
            $("#dispatchListModal").modal("show");
        });

        $(document).on('click', '.save_dispatch_list', function(event) {
            event.preventDefault();
            $("#form_dispatch_list").validate({
                rules: {
                    bom_id: {
                        required: true
                    }
                },
                messages: {
                    bom_id: "Please Select BOM"
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

            if ($("#form_dispatch_list").valid()) {
                var formData = new FormData($("#form_dispatch_list")[0]);
                $.ajax({
                    type: "POST",
                    url: "{{ route('application-save') }}",
                    data: formData,
                    dataType: 'json',
                    cache: false,
                    contentType: false,
                    processData: false,
                    beforeSend: function() {
                        $("#error_name").html(' ');
                        $(".save_dispatch_list").html(
                            `<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> {{ __('message.Wait') }}`
                            );
                        $(".save_dispatch_list").attr('disabled', true);
                    },
                    success: function(response) {
                        $(".save_dispatch_list").html("{{ __('message.Submit') }}");
                        $(".save_dispatch_list").attr('disabled', false);
                        if (response.server_error && response.status == false) {
                            toastr.error("{{ __('message.Something went wrong. Please try again.') }}",
                                "{{ __('message.Error') }}");
                        } else if (response.status == false) {
                            $.each(response.errors, function(key, value) {
                                $('#error_' + key).html('<p class="text-danger mb-0">' + value +
                                    '</p>');
                            });
                            toastr.warning("{{ __('message.Please input proper data.') }}",
                                "{{ __('message.Warning') }}");
                        } else {
                            $('#form_dispatch_list')[0].reset();

                            $("#dispatchListModal").modal("hide");
                            location.href = response.data;
                        }
                    }
                });
            } else {
                return false;
            }
        });

        @if (session()->has('message'))
            toastr.error("{{ session()->get('message') }}", "{{ __('message.Error') }}");
        @endif
    </script>

@endsection
