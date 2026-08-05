@extends('layouts.app')
@section('title', 'Sales Order')
@section('content')
    <div class="row">
        <div class="col-12">
            @if (isset($salesMaster) && isset($salesMaster->id))
                <h4 class="card-title mb-1">Edit Sales Order</h4>
            @else
                <h4 class="card-title mb-1">Add Sales Order</h4>
            @endif
        </div>
        <div class="col-12">
            <div class="card border-1 border-secondary p-1">
                <form id="form" class="form invoice-repeater" action="javascript:void(0)" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        @if (isset($salesMaster) && isset($salesMaster->id))
                            <input type="hidden" id="sales_master_id" name="sales_master_id"
                                value="{{ $salesMaster->id }}">
                        @endif
                        <div class="col-12 col-md-6 col-lg-10 mb-1 custom-input-group">
                            <label class="form-label" for="sales_quatation_id">{{ __('message.Sales Quatation') }} <span
                                    class="text-danger">*</span></label>
                            <select class="form-control  form-select select2 custom-select2 anlayst"
                                name="sales_quatation_id" id="sales_quatation_id">
                                <option selected disabled>{{ __('message.-- Select --') }}</option>
                                @foreach ($sales_quatation as $value)
                                    <option value="{{ $value->id }}" data-mobile="{{ $value->mobile }}"
                                        data-inveter-company-id="{{ $value->inveter_company_id }}"
                                        data-inveter-company="{{ getSalesQuatationInveter($value->inveter_company_id) }}"
                                        data-penal-company="{{ getSalesQuatationPanels($value->penal_company_id) }}"
                                        data-penal-company-id="{{ $value->penal_company_id }}"
                                        data-penal-watt="{{ $value->penal_watt_id }}"
                                        data-penal-watt-name="{{ isset($value->penalwatt) ? $value->penalwatt->name : '' }}"
                                        data-reference="{{ $value->reference }}"
                                        data-agent_sales_person_id="{{ $value->agent_sales_person_id }}"
                                        data-pv_capacity_kw="{{ $value->pv_capacity_kw }}"
                                        data-total_amount="{{ $value->total_amount }}" data-name="{{ $value->name }}"
                                        {{ isset($salesMaster) && $salesMaster->sales_quatation_id == $value->id ? 'selected' : '' }}>
                                        {{ $value->name }} - {{ $value->mobile }} - {{ $value->pv_capacity_kw }} -
                                        {{ $value->total_amount }}</option>
                                @endforeach
                            </select>
                            <span class="invalid-feedback d-block" id="error_sales_quatation_id" role="alert"></span>
                        </div>
                        <div class="col-12 col-md-6 col-lg-2 mb-1 custom-input-group">
                            <label class="form-label" for="file_type">Payment Type</label>
                            <select class="form-control form-select select2 custom-select2 file_type anlayst"
                                name="file_type" id="file_type">
                                <option
                                    {{ isset($salesMaster) && $salesMaster->file_type == 'C' ? 'selected' : 'selected' }}
                                    value="C">Cash</option>
                                <option {{ isset($salesMaster) && $salesMaster->file_type == 'L' ? 'selected' : '' }}
                                    value="L">Loan</option>

                            </select>
                            <span class="invalid-feedback d-block" id="error_file_type" role="alert"></span>
                        </div>
                        <!-- Application Details -->
                        <div class="col-lg-12 mt-50 mb-50">
                            <h5 class="font-black" style="font-weight: bold;color: var(--ck-color-base-text);"> Application
                                Details</h5>
                            <hr class="m-0">
                        </div>
                        <div class="col-12 col-md-6 col-lg-3 mb-1 custom-input-group">
                            <label class="form-label" for="consumer_number">Consumer Number<span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="consumer_number" id="consumer_number"
                                placeholder="Consumer Number"
                                value="{{ isset($salesMaster) && isset($salesMaster->consumer_number) ? $salesMaster->consumer_number : '' }}">
                            <span class="invalid-feedback d-block" id="error_consumer_number" role="alert"></span>
                        </div>
                        <div class="col-12 col-md-6 col-lg-5 mb-1 custom-input-group">
                            <label class="form-label" for="consumer_name">Consumer Name<span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="consumer_name" id="consumer_name"
                                placeholder="Consumer Name"
                                value="{{ isset($salesMaster) && isset($salesMaster->consumer_name) ? $salesMaster->consumer_name : '' }}"
                                oninput="this.value = this.value.toUpperCase()">
                            <span class="invalid-feedback d-block" id="error_consumer_name" role="alert"></span>
                        </div>
                        <div class="col-12 col-md-3 col-lg-2 mb-1 custom-input-group">
                            <label class="form-label" for="register_kw ">Register KW<span
                                    class="text-danger">*</span></label>
                            <input type="text" maxlength="10" class="form-control" name="register_kw" id="register_kw"
                                placeholder="Register KW"
                                value="{{ isset($salesMaster) && isset($salesMaster->register_kw) ? $salesMaster->register_kw : '' }}">
                            <span class="invalid-feedback d-block" id="error_register_kw" role="alert"></span>
                        </div>
                        <div class="col-12 col-md-3 col-lg-2 mb-1 custom-input-group">
                            <label class="form-label" for="consumer_type">Consumer Type<span
                                    class="text-danger">*</span></label>
                            <select class="form-control  form-select select2 custom-select2" name="consumer_type"
                                id="consumer_type">
                                <option selected disabled>-- Select --</option>
                                <option value="Resident"
                                    {{ isset($salesMaster) && $salesMaster->consumer_type == 'Resident' ? 'selected' : '' }}>
                                    Resident</option>
                                <option value="Industrial"
                                    {{ isset($salesMaster) && $salesMaster->consumer_type == 'Industrial' ? 'selected' : '' }}>
                                    Industrial</option>
                                <option value="Commercial"
                                    {{ isset($salesMaster) && $salesMaster->consumer_type == 'Commercial' ? 'selected' : '' }}>
                                    Commercial</option>

                            </select>
                            <span class="invalid-feedback d-block" id="error_consumer_type" role="alert"></span>
                        </div>
                        <!-- /  Application Details -->

                        <!--  Contact Details -->
                        <div class="col-lg-12 mt-50 mb-50">
                            <h5 class="font-black" style="font-weight: bold;color: var(--ck-color-base-text);"> Contact
                                Details</h5>
                            <hr class="m-0">
                        </div>
                        <div class="col-12 col-md-6 col-lg-4 mb-1 custom-input-group">
                            <label class="form-label" for="contact_number ">Contact Number<span
                                    class="text-danger">*</span></label>
                            <input type="text" maxlength="10" class="form-control" name="contact_number"
                                id="contact_number" placeholder="Contact Number"
                                value="{{ isset($salesMaster) && isset($salesMaster->contact_number) ? $salesMaster->contact_number : '' }}">
                            <span class="invalid-feedback d-block" id="error_contact_number" role="alert"></span>
                        </div>
                        <div class="col-12 col-md-6 col-lg-4 mb-1 custom-input-group">
                            <label class="form-label" for="email">{{ __('message.Email') }}</label>
                            <input type="email" class="form-control" name="email" id="email"
                                placeholder="{{ __('message.Email Address') }}"
                                value="{{ isset($salesMaster) && isset($salesMaster->email) ? $salesMaster->email : '' }}">
                            <span class="invalid-feedback d-block" id="error_email" role="alert"></span>
                        </div>
                        <div class="col-12 col-lg-12 mb-1 custom-input-group">
                            <label class="form-label" for="address">{{ __('message.Address') }}</label>
                            <input type="text" class="form-control" name="address" id="address"
                                placeholder="{{ __('message.Address') }}"
                                value="{{ isset($salesMaster) && isset($salesMaster->address) ? $salesMaster->address : '' }}">
                            <span class="invalid-feedback d-block" id="error_address" role="alert"></span>
                        </div>
                        <div class="col-12 col-md-6 col-lg-4 mb-1 custom-input-group">
                            <label class="form-label" for="district_id">District<span
                                    class="text-danger">*</span></label>
                            <select class="form-control form-select select2 custom-select2 district_id" name="district_id"
                                id="district_id">
                                <option selected disabled>{{ __('message.-- Select --') }}</option>
                                @foreach ($district as $value)
                                    <option value="{{ $value->id }}"
                                        {{ isset($salesMaster) && $salesMaster->district_id == $value->id ? 'selected' : '' }}>
                                        {{ $value->name }}
                                        {{ !is_null($value->state) ? ' - ' . $value->state->state_name : '' }}</option>
                                @endforeach
                            </select>
                            <span class="invalid-feedback d-block" id="error_district_id" role="alert"></span>
                        </div>
                        <div class="col-12 col-md-6 col-lg-4 mb-1 custom-input-group">
                            <label class="form-label" for="taluka_id">Taluka<span class="text-danger">*</span></label>
                            <select class="form-control form-select select2 custom-select2 taluka_id" name="taluka_id"
                                id="taluka_id">
                                <option selected disabled>{{ __('message.-- Select --') }}</option>
                                @isset($salesMaster)
                                    @foreach ($taluka as $value)
                                        <option value="{{ $value->id }}"
                                            {{ isset($salesMaster) && $salesMaster->taluka_id == $value->id ? 'selected' : '' }}>
                                            {{ $value->name }}</option>
                                    @endforeach
                                @endisset
                            </select>
                            <span class="invalid-feedback d-block" id="error_taluka_id" role="alert"></span>
                        </div>
                        <div class="col-12 col-md-6 col-lg-4 mb-1 custom-input-group">
                            <label class="form-label" for="pin_code">Pincode<span class="text-danger">*</span></label>
                            <input type="text" class="form-control" maxlength="6" name="pin_code" id="pin_code"
                                placeholder="Pincode"
                                value="{{ isset($salesMaster) && isset($salesMaster->pin_code) ? $salesMaster->pin_code : '' }}">
                            <span class="invalid-feedback d-block" id="error_pin_code" role="alert"></span>
                        </div>
                        <!--  / Contact Details -->

                        <!--  Bank Details -->
                        <div class="col-lg-12 mt-50 mb-50">
                            <h5 class="font-black" style="font-weight: bold;color: var(--ck-color-base-text);"> Bank
                                Details</h5>
                            <hr class="m-0">
                        </div>
                        <div class="col-12 col-md-6 col-lg-4 mb-1 custom-input-group">
                            <label class="form-label" for="bank_name">Bank Name</label>
                            <input type="text" class="form-control" name="bank_name" id="bank_name"
                                placeholder="Bank Name"
                                value="{{ isset($salesMaster) && isset($salesMaster->bank_name) ? $salesMaster->bank_name : '' }}">
                            <span class="invalid-feedback d-block" id="error_bank_name" role="alert"></span>
                        </div>
                        <div class="col-12 col-md-6 col-lg-4 mb-1 custom-input-group">
                            <label class="form-label" for="bank_account">Bank Account</label>
                            <input type="text" class="form-control" name="bank_account" id="bank_account"
                                placeholder="Bank Account"
                                value="{{ isset($salesMaster) && isset($salesMaster->bank_account) ? $salesMaster->bank_account : '' }}">
                            <span class="invalid-feedback d-block" id="error_bank_account" role="alert"></span>
                        </div>
                        <div class="col-12 col-md-6 col-lg-4 mb-1 custom-input-group">
                            <label class="form-label" for="ifsc_code">IFSC Code</label>
                            <input type="text" class="form-control" name="ifsc_code" id="ifsc_code"
                                placeholder="IFSC Code"
                                value="{{ isset($salesMaster) && isset($salesMaster->ifsc_code) ? $salesMaster->ifsc_code : '' }}">
                            <span class="invalid-feedback d-block" id="error_ifsc_code" role="alert"></span>
                        </div>
                        <!--  / Bank Details -->

                        <!--  Division Details -->
                        <div class="col-lg-12 mt-50 mb-50">
                            <h5 class="font-black" style="font-weight: bold;color: var(--ck-color-base-text);"> Division
                                Details</h5>
                            <hr class="m-0">
                        </div>
                        <div class="col-12 col-md-6 col-lg-3 mb-1 custom-input-group">
                            <label class="form-label" for="sub_division_id">Sub Division</label>
                            <select class="form-control form-select select2 custom-select2 sub_division_id"
                                name="sub_division_id" id="sub_division_id">
                                <option selected disabled>-- Select --</option>
                                @foreach ($subDivision as $value)
                                    <option value="{{ $value->id }}"
                                        {{ isset($salesMaster) && $salesMaster->sub_division_id == $value->id ? 'selected' : '' }}>
                                        {{ $value->name }}</option>
                                @endforeach
                            </select>
                            <span class="invalid-feedback d-block" id="error_sub_division" role="alert"></span>
                        </div>
                        <div class="col-12 col-md-6 col-lg-3 mb-1 custom-input-group">
                            <label class="form-label" for="division">Division</label>
                            <input type="text" class="form-control" name="division" id="division"
                                placeholder="Division"
                                value="{{ isset($salesMaster) && isset($salesMaster->division) ? $salesMaster->division : '' }}"
                                readonly>
                            <span class="invalid-feedback d-block" id="error_division" role="alert"></span>
                        </div>
                        <div class="col-12 col-md-6 col-lg-3 mb-1 custom-input-group">
                            <label class="form-label" for="circle">Circle</label>
                            <input type="text" class="form-control" name="circle" id="circle"
                                placeholder="Circle"
                                value="{{ isset($salesMaster) && isset($salesMaster->circle) ? $salesMaster->circle : '' }}"
                                readonly>
                            <span class="invalid-feedback d-block" id="error_circle" role="alert"></span>
                        </div>
                        <div class="col-12 col-md-6 col-lg-3 mb-1 custom-input-group">
                            <label class="form-label" for="discom">DISCOM</label>
                            <input type="text" class="form-control" name="discom" id="discom"
                                placeholder="DISCOM"
                                value="{{ isset($salesMaster) && isset($salesMaster->discom) ? $salesMaster->discom : '' }}"
                                readonly>
                            <span class="invalid-feedback d-block" id="error_discom" role="alert"></span>
                        </div>
                        <!--  / Division Details -->

                        <!--  Other Details -->
                        <div class="col-lg-12 mt-50 mb-50">
                            <h5 class="font-black" style="font-weight: bold;color: var(--ck-color-base-text);"> Other
                                Details</h5>
                            <hr class="m-0">
                        </div>
                        <div class="col-12 col-md-6 col-lg-4 mb-1 custom-input-group">
                            <label class="form-label" for="gst_number">GST Number</label>
                            <input type="text" maxlength="15" class="form-control text-uppercase" name="gst_number"
                                id="gst_number" placeholder="GST Number"
                                value="{{ isset($salesMaster) && isset($salesMaster->gst_number) ? $salesMaster->gst_number : '' }}">
                            <span class="invalid-feedback d-block" id="error_gst_number" role="alert"></span>
                        </div>
                        <div class="col-12 col-md-6 col-lg-4 mb-1 custom-input-group">
                            <label class="form-label" for="aadhaar_number">Aadhaar Number</label>
                            <input type="text" maxlength="12" class="form-control" name="aadhaar_number"
                                id="aadhaar_number" placeholder="Aadhaar Number"
                                value="{{ isset($salesMaster) && isset($salesMaster->aadhaar_number) ? $salesMaster->aadhaar_number : '' }}">
                            <span class="invalid-feedback d-block" id="error_aadhaar_number" role="alert"></span>
                        </div>
                        <div class="col-12 col-md-6 col-lg-4 mb-1 custom-input-group">
                            <label class="form-label" for="contracted_load">Contracted Load</label>
                            <input type="text" class="form-control" name="contracted_load" id="contracted_load"
                                placeholder="Contracted Load"
                                value="{{ isset($salesMaster) && isset($salesMaster->contracted_load) ? $salesMaster->contracted_load : '' }}">
                            <span class="invalid-feedback d-block" id="error_contracted_load" role="alert"></span>
                        </div>
                        <div class="col-12 col-md-6 col-lg-4 mb-1 custom-input-group">
                            <label class="form-label" for="phase">Phase</label>

                            <select class="form-control form-select select2 custom-select2 phase" name="phase"
                                id="phase">
                                <option value="Single phase"
                                    {{ isset($salesMaster) && $salesMaster->phase == 'Single phase' ? 'selected' : 'selected' }}>
                                    Single phase</option>
                                <option value="Three phase"
                                    {{ isset($salesMaster) && $salesMaster->phase == 'Three phase' ? 'selected' : '' }}>
                                    Three phase</option>

                            </select>


                            <span class="invalid-feedback d-block" id="error_phase" role="alert"></span>
                        </div>

                        <div class="col-12 col-md-6 col-lg-4 mb-1 custom-input-group">
                            <label class="form-label" for="reference">Reference</label>
                            <input type="text" class="form-control" name="reference" id="reference"
                                placeholder="Reference"
                                value="{{ isset($salesMaster) && isset($salesMaster->reference) ? $salesMaster->reference : '' }}"
                                oninput="this.value = this.value.toUpperCase()">
                            <span class="invalid-feedback d-block" id="error_reference" role="alert"></span>
                        </div>
                        <div class="col-12 col-md-6 col-lg-4 mb-1 custom-input-group">
                            <label class="form-label" for="agent_sales_person_id">Agent Sales Person<span
                                    class="text-danger">*</span></label>
                            <select class="form-control form-select select2 custom-select2 agent_sales_person_id"
                                name="agent_sales_person_id" id="agent_sales_person_id">
                                <option selected disabled>-- Select --</option>
                                @foreach ($agentSalesPerson as $value)
                                    <option value="{{ $value->id }}"
                                        {{ isset($salesMaster) && $salesMaster->agent_sales_person_id == $value->id ? 'selected' : '' }}>
                                        {{ $value->name }}</option>
                                @endforeach
                            </select>
                            <span class="invalid-feedback d-block" id="agent_sales_person_id" role="alert"></span>
                        </div>
                        <div class="col-12 col-md-6 col-lg-12 mb-1 custom-input-group">
                            <label class="form-label" for="remark">Remark</label>
                            <textarea class="form-control" name="remark" id="remark" placeholder="Remark (If Any)">{{ isset($salesMaster) && isset($salesMaster->remark) ? $salesMaster->remark : '' }}</textarea>
                            <span class="invalid-feedback d-block" id="error_remark" role="alert"></span>
                        </div>
                        <!--  Other Details -->

                        <!--  Panel & Inverter Details -->
                        <div class="col-lg-12 mt-50 mb-50">
                            <h5 class="font-black" style="font-weight: bold;color: var(--ck-color-base-text);"> Panel &
                                Inverter Details</h5>
                            <hr class="m-0">
                        </div>
                        <div class="col-12 col-md-6 col-lg-3 mb-1 custom-input-group">
                            <label class="form-label" for="penal_company_id">Panel Company<span
                                    class="text-danger">*</span> <span class="quatation_penal_company"></span></label>
                            <select class="form-control anlayst custom-select2" name="penal_company_id"
                                id="penal_company_id">
                                <option selected disabled>{{ __('message.-- Select --') }}</option>
                                @foreach ($penal_company as $value)
                                    @php
                                        $selected =
                                            isset($salesMaster) && $value->id == $salesMaster->penal_company_id
                                                ? 'selected'
                                                : '';
                                    @endphp
                                    <option value="{{ $value->id }}" {{ $selected }}>{{ $value->name }}</option>
                                @endforeach
                            </select>
                            <span class="invalid-feedback d-block" id="error_penal_company_id" role="alert"></span>
                        </div>
                        <div class="col-12 col-md-6 col-lg-2 mb-1 custom-input-group">
                            <label class="form-label" for="penal_watt_id">{{ __('message.Panel Watt') }} <span
                                    class="text-danger">*</span> <span class="quatation_penal_watt_id"></span></label>
                            <select class="form-control anlayst select2 custom-select2" name="penal_watt_id"
                                id="penal_watt_id">
                                <option selected disabled>{{ __('message.-- Select --') }}</option>
                                @foreach ($penal_watt as $value)
                                    <option value="{{ $value->id }}"
                                        {{ isset($salesMaster) && $salesMaster->penal_watt_id == $value->id ? 'selected' : '' }}>
                                        {{ $value->name }}</option>
                                @endforeach
                            </select>
                            <span class="invalid-feedback d-block" id="error_penal_watt_id" role="alert"></span>
                        </div>
                        <div class="col-12 col-md-6 col-lg-3 mb-1 custom-input-group">
                            <label class="form-label" for="inveter_company_id">Inverter Company<span
                                    class="text-danger">*</span> <span class="quatation_inveter_company"></span></label>
                            <select class="form-control anlayst custom-select2" name="inveter_company_id"
                                id="inveter_company_id">
                                <option selected disabled>{{ __('message.-- Select --') }}</option>
                                @foreach ($inveter_company as $value)
                                    @php
                                        $selected =
                                            isset($salesMaster) && $value->id == $salesMaster->inveter_company_id
                                                ? 'selected'
                                                : '';
                                    @endphp
                                    <option value="{{ $value->id }}" {{ $selected }}>{{ $value->name }}</option>
                                @endforeach
                            </select>
                            <span class="invalid-feedback d-block" id="error_inveter_company_id" role="alert"></span>
                        </div>
                        <!-- BOM -->
                        <div class="col-12 col-md-6 col-lg-2 mb-1 custom-input-group ">
                            <label class="form-label" for="bom_id">BOM</label>
                            <select class="form-select select2" name="bom_id" id="bom_id">
                                <option value="0">None</option>
                                @foreach ($boms as $bomValue)
                                    <option value="{{ $bomValue->id }}"
                                        {{ isset($salesMaster) && $bomValue->id == $salesMaster->bom_id ? 'selected' : '' }}>
                                        {{ $bomValue->bom_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <!-- / BOM -->
                        <div class="col-12 col-md-6 col-lg-2 mb-1 custom-input-group">
                            <label class="form-label" for="total_amount">Total Amount <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" readonly name="total_amount" id="total_amount"
                                placeholder="Total Amount"
                                value="{{ isset($salesMaster) && isset($salesMaster->total_amount) ? $salesMaster->total_amount : '' }}">
                            <span class="invalid-feedback d-block" id="error_total_amount" role="alert"></span>
                        </div>
                        <!--  / Panel & Inverter Details -->

                        <!--  Document Details -->
                        <div class="col-lg-12 mt-50 mb-50">
                            <h5 class="font-black" style="font-weight: bold;color: var(--ck-color-base-text);"> Document
                                Details</h5>
                            <hr class="m-0">
                        </div>

                        @if (isset($salesMaster) && isset($salesMaster->id))
                            <div data-repeater-list="document" class="col-12 sub_data">
                                @foreach ($salesMaster->document as $key => $value)
                                    <div data-repeater-item>
                                        <div class="row">
                                            <div class="col-12 col-md-6 col-lg-5 mb-1">
                                                <input type="hidden" name="document_id" value="{{ $value->id }}">

                                                <label class="form-label">Document Name {{ $value->name }}
                                                </label>
                                                <select class="form-control" name="name">
                                                    <option selected disabled>-- Select --</option>
                                                    <option {{ $value->name == 'light_bill' ? 'selected' : '' }}
                                                        value="light_bill">Light Bill</option>
                                                    <option {{ $value->name == 'bank_details' ? 'selected' : '' }}
                                                        value="bank_details">Bank Details</option>
                                                    <option {{ $value->name == 'aadhaar_card' ? 'selected' : '' }}
                                                        value="aadhaar_card">Aadhaar Card</option>
                                                    <option {{ $value->name == 'pancard' ? 'selected' : '' }}
                                                        value="pancard">
                                                        Pancard</option>
                                                    <option {{ $value->name == 'dastavej' ? 'selected' : '' }}
                                                        value="dastavej">Dastavej</option>
                                                    <option {{ $value->name == 'gst_certificate' ? 'selected' : '' }}
                                                        value="gst_certificate">GST Certificate</option>
                                                    <option {{ $value->name == 'udyam_registration' ? 'selected' : '' }}
                                                        value="udyam_registration">Udyam Registration</option>
                                                    <option {{ $value->name == 'customer_signture' ? 'selected' : '' }}
                                                        value="customer_signture">Customer signture</option>
                                                    <option {{ $value->name == 'passport_size_photo' ? 'selected' : '' }}
                                                        value="passport_size_photo">Passport size photo</option>
                                                    <option {{ $value->name == 'sales_invoice' ? 'selected' : '' }}
                                                        value="sales_invoice">Sales Invoice</option>
                                                    <option
                                                        {{ $value->name == 'application_acknowledgement_form' ? 'selected' : '' }}
                                                        value="application_acknowledgement_form">Application
                                                        Acknowledgement / FORM</option>

                                                    <option {{ $value->name == 'dcr_certificate' ? 'selected' : '' }}
                                                        value="dcr_certificate">DCR Certificate</option>
                                                    <option {{ $value->name == 'other_document' ? 'selected' : '' }}
                                                        value="other_document">Other Document</option>
                                                </select>

                                            </div>
                                            <div class="col-12 col-md-5 col-lg-5 mb-1">
                                                <input type="hidden" name="id" value="" class="img_hide"
                                                    value="{{ isset($value) && isset($value->id) ? $value->id : '' }}">
                                                <label class="form-label">Upload Document </label>
                                                <input type="file" class="form-control" name="image">
                                                @if (isset($value) && isset($value->id) && $value->image != '')
                                                    <a href="{{ asset('upload/document/' . $value->image) }}"
                                                        download="{{ $value->name }}">
                                                        <button type="button"
                                                            class="btn btn-sm mt-1 btn-outline-primary"><span>Download</span>
                                                            <i data-feather="download" class="me-25"></i></button>
                                                    </a>
                                                @endif
                                            </div>
                                            <div class="col-12 col-lg-2 d-flex align-items-center">
                                                <button
                                                    class="btn btn-outline-danger text-nowrap px-1 mt-2 float-end data-repeater-delete remove-item"
                                                    data-id="{{ $value->id }}" data-repeater-delete type="button">
                                                    <i data-feather="x"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach

                                @if (count($salesMaster->document) == 0)
                                    <div data-repeater-list="document" class="col-12 sub_data">
                                        <div data-repeater-item>
                                            <div class="row">
                                                <div class="col-12 col-md-6 col-lg-5 mb-1">
                                                    <label class="form-label">Document Name </label>
                                                    <select class="form-control" name="name">
                                                        <option selected disabled>-- Select --</option>
                                                        <option value="light_bill">Light Bill</option>
                                                        <option value="bank_details">Bank Details</option>
                                                        <option value="aadhaar_card">Aadhaar Card</option>
                                                        <option value="pancard">Pancard</option>
                                                        <option value="dastavej">Dastavej</option>
                                                        <option value="gst_certificate">GST Certificate</option>
                                                        <option value="udyam_registration">Udyam Registration</option>
                                                        <option value="customer_signture">Customer signture</option>
                                                        <option value="passport_size_photo">Passport size photo</option>
                                                        <option value="sales_invoice">Sales Invoice</option>
                                                        <option value="application_acknowledgement_form">Application
                                                            Acknowledgement / FORM</option>

                                                        <option value="dcr_certificate">DCR Certificate</option>
                                                        <option value="other_document">Other Document</option>
                                                    </select>
                                                </div>
                                                <div class="col-12 col-md-6 col-lg-5 mb-1">
                                                    <label class="form-label">Upload Document</label>
                                                    <input type="file" class="form-control " name="image">
                                                </div>
                                                <div class="col-12 col-lg-2 d-flex align-items-center">
                                                    <button
                                                        class="btn btn-outline-danger text-nowrap px-1 mt-2 float-end data-repeater-delete remove-item"
                                                        data-repeater-delete type="button">
                                                        <i data-feather="x"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @else
                            <div data-repeater-list="document" class="col-12 sub_data">
                                <div data-repeater-item>
                                    <div class="row">
                                        <div class="col-12 col-md-6 col-lg-5 mb-1">
                                            <label class="form-label">Document Name </label>
                                            <select class="form-control" name="name">
                                                <option selected disabled>-- Select --</option>
                                                <option value="light_bill">Light Bill</option>
                                                <option value="bank_details">Bank Details</option>
                                                <option value="aadhaar_card">Aadhaar Card</option>
                                                <option value="pancard">Pancard</option>
                                                <option value="dastavej">Dastavej</option>
                                                <option value="gst_certificate">GST Certificate</option>
                                                <option value="udyam_registration">Udyam Registration</option>
                                                <option value="customer_signture">Customer signture</option>
                                                <option value="passport_size_photo">Passport size photo</option>
                                                <option value="sales_invoice">Sales Invoice</option>
                                                <option value="application_acknowledgement_form">Application
                                                    Acknowledgement / FORM</option>
                                                <option value="dcr_certificate">DCR Certificate</option>
                                                <option value="other_document">Other Document</option>
                                            </select>
                                        </div>
                                        <div class="col-12 col-md-6 col-lg-5 mb-1">
                                            <label class="form-label">Upload Document</label>
                                            <input type="file" class="form-control " name="image">
                                        </div>
                                        <div class="col-12 col-lg-2 d-flex align-items-center">
                                            <button
                                                class="btn btn-outline-danger text-nowrap px-1 mt-2 float-end data-repeater-delete remove-item"
                                                data-repeater-delete type="button">
                                                <i data-feather="x"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                        <div class="col-md-7">
                            <button class="btn btn-sm btn-icon btn-secondary" type="button" data-repeater-create>
                                <i class="fa fa-plus me-25"></i> <span>{{ __('message.Add New') }}</span>
                            </button>
                        </div>
                        <!-- / Document Details -->
                        <div class="col-md-12 mt-2">
                            <button type="submit"
                                class="btn btn-sm btn-primary float-end save">{{ __('message.Submit') }}</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@section('pagescript')
    <script type="text/javascript">
        $('.custom-select2').on('change', function() {
            var element = $(this).attr('name');
            $('#form').validate().showErrors({
                [element]: ''
            });
        });

        $(document).ready(function() {

            $("#form").validate({
                rules: {
                    penal_company_id: {
                        required: true,
                    },
                    inveter_company_id: {
                        required: true,
                    },
                    penal_watt_id: {
                        required: true,
                    },
                    consumer_number: {
                        required: true,
                    },
                    consumer_type: {
                        required: true,
                    },
                    sales_quatation_id: {
                        required: true,
                    },
                    consumer_name: {
                        required: true,
                    },
                    district_id: {
                        required: true,
                    },
                    taluka_id: {
                        required: true,
                    },
                    pin_code: {
                        required: true,
                        minlength: 6,
                        regex: "[0-9]{6}",
                    },
                    contact_number: {
                        required: true,
                    },
                    // discom: {
                    //     required: true,
                    // },
                    agent_sales_person_id: {
                        required: true,
                    },
                    register_kw: {
                        required: true,
                        regex: "[0-9].[0-9]"
                    }
                },
                messages: {
                    penal_company_id: {
                        required: "Select Panel Company Name",
                    },
                    inveter_company_id: {
                        required: "Select Inverter Company Name",
                    },
                    penal_watt_id: {
                        required: "Select Panel Watt",
                    },
                    consumer_number: {
                        required: "Enter Consumer Number",
                    },
                    consumer_type: {
                        required: "Enter Consumer Type",
                    },
                    sales_quatation_id: {
                        required: "Select Sales Quatation",
                    },
                    consumer_name: {
                        required: "Enter Consumer Name",
                    },
                    password: {
                        required: "{{ __('message.Enter password') }}",
                    },
                    district_id: {
                        required: "Enter District",
                    },
                    taluka_id: {
                        required: "Select Taluka",
                    },
                    pin_code: {
                        required: "Enter Pincode",
                        minlength: "Enter at least 6 digits",
                        regex: "Enter Valid Pincode"
                    },
                    contact_number: {
                        required: "Enter Contact Number",
                    },
                    // discom: {
                    //     required: "Enter DISCOM",
                    // },
                    agent_sales_person_id: {
                        required: "Choose Agent Sales Person",
                    },
                    register_kw: {
                        required: "Enter Register KW",
                        regex: "Enter Valid Register KW"
                    }
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

            $('#penal_company_id,#inveter_company_id').select2({
                placeholder: "-- Select --",
                allowClear: true
            });

            $('#lead_master_id').change(function() {
                var selectedOption = $(this).find('option:selected');
                var mobile = selectedOption.data('mobile');
                var name = selectedOption.data('name');
                var kw = selectedOption.data('kw');
                var reference = selectedOption.data('reference');
                var agent_sales_person_id = selectedOption.data('agent_sales_person_id');
                $('#contact_number').val(mobile);
                $('#consumer_name').val(name);
                $('#register_kw').val(kw);
                $('#reference').val(reference);
                $('#agent_sales_person_id').val(agent_sales_person_id).trigger('change');
            });

            $('#sales_quatation_id').change(function() {
                var selectedOption = $(this).find('option:selected');
                var mobile = selectedOption.data('mobile');
                var name = selectedOption.data('name');
                var pv_capacity_kw = selectedOption.data('pv_capacity_kw');
                var total_amount = selectedOption.data('total_amount');
                var reference = selectedOption.data('reference');
                var agent_sales_person_id = selectedOption.data('agent_sales_person_id');
                var penal_watt_id = selectedOption.data('penal-watt');
                var penal_watt = selectedOption.data('penal-watt-name');
                var inveter_company = selectedOption.data('inveter-company');
                var penal_company = selectedOption.data('penal-company');

                var penal_company_id = selectedOption.data('penal-company-id');
                var inveter_company_id = selectedOption.data('inveter-company-id');

                $('.quatation_inveter_company').html('(' + inveter_company + ')');
                $('.quatation_penal_company').html('(' + penal_company + ')');

                $('#penal_company_id').val(penal_company_id).trigger('change');
                $('#inveter_company_id').val(inveter_company_id).trigger('change');

                $('.quatation_penal_watt_id').html('(' + penal_watt + ')');
                $('#penal_watt_id').val(penal_watt_id).trigger('change');
                $('#contact_number').val(mobile);
                $('#consumer_name').val(name);
                $('#register_kw').val(pv_capacity_kw);
                $('#total_amount').val(total_amount);
                $('#reference').val(reference);
                $('#agent_sales_person_id').val(agent_sales_person_id).trigger('change');
            });
            $(document).on('keypress', '#contact_number', function() {
                if ($("#contact_number").val().length > 9) {
                    $("#contact_number").attr('type', 'text');
                } else {
                    $("#contact_number").attr('type', 'number');
                }
            });
            $(document).on('keypress', '#aadhaar_number', function() {
                if ($("#aadhaar_number").val().length > 11) {
                    $("#aadhaar_number").attr('type', 'text');
                } else {
                    $("#aadhaar_number").attr('type', 'number');
                }
            });
            $(document).on('keypress', '#bank_account', function() {
                if ($("#bank_account").val().length > 50) {
                    $("#bank_account").attr('type', 'text');
                } else {
                    $("#bank_account").attr('type', 'number');
                }
            });
            $(document).on('keypress', '#pin_code', function() {
                if ($("#pin_code").val().length > 5) {
                    $("#pin_code").attr('type', 'text');
                } else {
                    $("#pin_code").attr('type', 'number');
                }
            });
            $(document).on('change', '#sub_division_id', function() {
                $('.division').trigger('change');
                $('.circle').trigger('change');
                $('.discom').trigger('change');
            });
            $(document).on('change', '.sub_division_id', function() {
                var sub_division_id = $('.sub_division_id').val();
                $.ajax({
                    url: "{{ url('division-view') }}",
                    type: "get",
                    datatype: 'json',
                    data: {
                        'sub_division_id': sub_division_id,
                    },
                    success: function(response) {
                        $("#division").val(response.division_name);
                        $('#circle').val(response.circle_name);
                        $('#discom').val(response.discom);
                    }
                });
            });
            var city_id = $("#city_id").val();
            if (city_id != null) {
                changeCity(0);
            }
            $(document).on('click', '.remove-item', function(e) {
                e.preventDefault();
                var select = $('.select2');
                var btn = $(this);
                var id = $(this).data('id');
                var url = "{{ route('sales-master.update', 'id') }}".replace('id', id);
                if (id != null && ($('.remove-item').length) > 1) {
                    $.ajax({
                        url: url,
                        type: 'put',
                        datatype: 'json',
                        data: {
                            "_token": "{{ csrf_token() }}",
                        },
                        success: function(response) {
                            toastr.success(response.message, "{{ __('message.Success') }}");
                        }
                    });
                } else {
                    toastr.warning('You can not delete First Item', 'Opps!');
                }
            });
            // district wise taluka on change
            $(document).on('change', '.district_id', function() {
                var district_id = $('.district_id').val();
                $.ajax({
                    url: "{{ url('taluka-view') }}",
                    type: "get",
                    datatype: 'json',
                    data: {
                        'district_id': district_id,
                    },
                    success: function(response) {
                        console.log(response);
                        $("#taluka_id").empty();
                        $("#taluka_id").append(
                            '<option value="" selected disabled>-- Select --</option>');
                        $.each(response, function(i, value) {
                            if (taluka_id == value.id) {
                                $("#taluka_id").append('<option selected value=' + value
                                    .id + '>' + value.name + '</option>');
                            } else {
                                $("#taluka_id").append('<option value=' + value.id +
                                    '>' + value.name + '</option>');
                            }
                        });
                    }
                });
            });
        });

        $(document).on('click', '.save', function() {
            if ($("#form").valid()) {
                var formData = new FormData($("#form")[0]);
                $.ajax({
                    type: "POST",
                    url: "{{ route('sales-master.store') }}",
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
                            toastr.error(
                                "{{ __('message.Something went wrong. Please try again.') }}",
                                "{{ __('message.Error') }}");
                        } else if (response.status == false && response.label) {
                            if (response.label == 'manager') {
                                toastr.warning(
                                    "{{ __('message.Your manger limit`s has been ended.') }}",
                                    "{{ __('message.Warning') }}");
                            } else {
                                toastr.warning(
                                    "{{ __('message.Your sales limit`s has been ended.') }}",
                                    "{{ __('message.Warning') }}");
                            }
                        } else if (response.status == false) {
                            $.each(response.errors, function(key, value) {
                                $('#error_' + key).html('<p class="text-danger mb-0">' + value +
                                    '</p>');
                            });
                            toastr.warning("{{ __('message.Please input proper data.') }}",
                                "{{ __('message.Warning') }}");
                        } else {
                            $('#form')[0].reset();
                            toastr.success(response.message, "{{ __('message.Success') }}");
                            setTimeout(function() {
                                location.href = response.data;
                            }, 2000);
                        }
                    }
                });
            } else {
                return false;
            }
        });
    </script>
@endsection
