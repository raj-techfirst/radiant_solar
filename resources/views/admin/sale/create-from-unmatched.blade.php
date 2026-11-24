@extends('layouts.app')
@section('title', 'Create From Unmatched Record')
@section('content')
    @php
        $recommended = $unmatchedRecord['recommended'] ?? [];
        $selectedBank = old('bank_id', $unmatchedCreateOptions['banks']->firstWhere('default', 1)->id ?? '');
        $selectedSubDivision = old('sub_division_id', $unmatchedRecord['sub_division_id'] ?? '');
        $documentOptions = ['light_bill' => 'Light Bill', 'bank_details' => 'Bank Details', 'aadhaar_card' => 'Aadhaar Card', 'pancard' => 'Pancard', 'dastavej' => 'Dastavej', 'gst_certificate' => 'GST Certificate', 'udyam_registration' => 'Udyam Registration', 'customer_signture' => 'Customer Signature', 'passport_size_photo' => 'Passport Size Photo', 'sales_invoice' => 'Sales Invoice', 'application_acknowledgement_form' => 'Application Acknowledgement / Form', 'dcr_certificate' => 'DCR Certificate', 'other_document' => 'Other Document'];
        $oldDocuments = old('document');

        if (! is_array($oldDocuments) || count($oldDocuments) === 0) {
            $oldDocuments = [['name' => '']];
        }

        $technicalSpecification = [];
        $oldTechnicalDescriptions = old('res_itemDescription');
        if (is_array($oldTechnicalDescriptions)) {
            foreach ($oldTechnicalDescriptions as $technicalIndex => $technicalDescription) {
                $technicalSpecification[] = [
                    'itemDescription' => $technicalDescription,
                    'qty' => old('res_qty.' . $technicalIndex, ''),
                    'size' => old('res_size.' . $technicalIndex, ''),
                    'make' => old('res_make.' . $technicalIndex, ''),
                    'type' => old('res_type.' . $technicalIndex, ''),
                ];
            }
        }
    @endphp

    <div class="row">
        <div class="col-12 mb-1">
            <h4 class="content-header-title float-start">Create From Unmatched Record</h4>
            <div class="float-end">
                <a class="btn btn-sm btn-outline-secondary" href="{{ route('sales-master-consumer-preview') }}">
                    <i data-feather="arrow-left" class="me-25"></i> Back to Preview
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card p-2">
                <form id="unmatched-create-form" action="{{ route('sales-master-consumer-preview-create-from-unmatched') }}"
                    method="POST" enctype="multipart/form-data" novalidate
                    data-preview-url="{{ route('sales-master-consumer-preview') }}"
                    data-validation-errors='@json(($errors ?? null) ? $errors->all() : [])'>
                    @csrf
                    <input type="hidden" name="target" value="both">
                    <input type="hidden" name="preview_row" id="unmatched_preview_row" value="{{ old('preview_row', $unmatchedRecord['row'] ?? '') }}">
                    <input type="hidden" name="consumer_number" id="unmatched_consumer_number" value="{{ old('consumer_number', $unmatchedRecord['consumer_number'] ?? '') }}">
                    <input type="hidden" name="consumer_name" id="unmatched_consumer_name" value="{{ old('consumer_name', $unmatchedRecord['consumer_name'] ?? '') }}">

                    <div class="row mb-2">
                        <div class="col-md-6">
                            <label class="form-label">Excel Consumer Name</label>
                            <input type="text" class="form-control" id="unmatched_excel_name" value="{{ $unmatchedRecord['consumer_name'] ?? '' }}" placeholder="Excel consumer name" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Excel Consumer Number</label>
                            <input type="text" class="form-control" id="unmatched_excel_number" value="{{ $unmatchedRecord['consumer_number'] ?? '' }}" placeholder="Excel consumer number" readonly>
                        </div>
                    </div>

                    <h6 class="fw-bold mt-2">Common And Resident Quotation Details</h6>
                    <hr class="my-1">
                    <div class="row">
                        <div class="col-md-4 mb-1">
                            <label class="form-label" for="unmatched_name">Customer Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="consumer_name_display" id="unmatched_name"
                                value="{{ old('consumer_name', $unmatchedRecord['consumer_name'] ?? '') }}"
                                placeholder="Enter customer name"
                                oninput="this.value = this.value.toUpperCase()">
                        </div>
                        <div class="col-md-4 mb-1">
                            <label class="form-label" for="unmatched_mobile">Mobile <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="mobile" id="unmatched_mobile" value="{{ old('mobile') }}" placeholder="Enter mobile number">
                        </div>
                        <div class="col-md-6 mb-1">
                            <label class="form-label" for="unmatched_address">Address <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="address" id="unmatched_address" value="{{ old('address') }}" placeholder="Enter address">
                        </div>
                        <div class="col-md-3 mb-1">
                            <label class="form-label" for="unmatched_reference">Reference</label>
                            <input type="text" class="form-control" name="reference" id="unmatched_reference"
                                value="{{ old('reference', $unmatchedRecord['consumer_number'] ?? '') }}" placeholder="Consumer number" readonly>
                        </div>
                        <div class="col-md-3 mb-1">
                            <label class="form-label" for="unmatched_agent_sales_person_id">Agent/Sales Person <span class="text-danger">*</span></label>
                            <select class="form-select" name="agent_sales_person_id" id="unmatched_agent_sales_person_id" data-placeholder="Select agent/sales person">
                                <option value="">-- Select --</option>
                                @foreach ($unmatchedCreateOptions['agentSalesPersons'] as $agent)
                                    <option value="{{ $agent->id }}" @selected(old('agent_sales_person_id') == $agent->id)>{{ $agent->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3 mb-1">
                            <label class="form-label" for="unmatched_penal_company_id">Panel Company <span class="text-danger">*</span></label>
                            <select class="form-select" name="penal_company_id" id="unmatched_penal_company_id" data-placeholder="Select panel company">
                                <option value="">-- Select --</option>
                                @foreach ($unmatchedCreateOptions['penalCompanies'] as $panelCompany)
                                    <option value="{{ $panelCompany->id }}" @selected(old('penal_company_id') == $panelCompany->id)>{{ $panelCompany->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 mb-1">
                            <label class="form-label" for="unmatched_penal_type_id">Panel Type <span class="text-danger">*</span></label>
                            <select class="form-select" name="penal_type_id" id="unmatched_penal_type_id" data-placeholder="Select panel type">
                                <option value="">-- Select --</option>
                                @foreach ($unmatchedCreateOptions['penalTypes'] as $panelType)
                                    <option value="{{ $panelType->id }}" @selected(old('penal_type_id') == $panelType->id)>{{ $panelType->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 mb-1">
                            <label class="form-label" for="unmatched_penal_watt_id">Panel Watt <span class="text-danger">*</span></label>
                            <select class="form-select" name="penal_watt_id" id="unmatched_penal_watt_id" data-placeholder="Select panel watt">
                                <option value="">-- Select --</option>
                                @foreach ($unmatchedCreateOptions['penalWatts'] as $panelWatt)
                                    <option value="{{ $panelWatt->id }}" @selected(old('penal_watt_id') == $panelWatt->id)>{{ $panelWatt->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 mb-1">
                            <label class="form-label" for="unmatched_penal_nos">Panel Nos <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="penal_nos" id="unmatched_penal_nos" value="{{ old('penal_nos') }}" placeholder="Enter panel nos">
                        </div>
                        <div class="col-md-2 mb-1">
                            <label class="form-label" for="unmatched_pv_capacity_kw">PV Capacity KW <span class="text-danger">*</span></label>
                            <input type="number" step="any" class="form-control" name="pv_capacity_kw" id="unmatched_pv_capacity_kw" value="{{ old('pv_capacity_kw') }}" placeholder="Calculated capacity in KW" readonly>
                        </div>

                        <div class="col-md-3 mb-1">
                            <label class="form-label" for="unmatched_inveter_company_id">Inverter Company <span class="text-danger">*</span></label>
                            <select class="form-select" name="inveter_company_id" id="unmatched_inveter_company_id" data-placeholder="Select inverter company">
                                <option value="">-- Select --</option>
                                @foreach ($unmatchedCreateOptions['inverterCompanies'] as $inverterCompany)
                                    <option value="{{ $inverterCompany->id }}" @selected(old('inveter_company_id') == $inverterCompany->id)>{{ $inverterCompany->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 mb-1">
                            <label class="form-label" for="unmatched_inveter_capacity">Inverter Capacity <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="inveter_capacity" id="unmatched_inveter_capacity" value="{{ old('inveter_capacity') }}" placeholder="Enter inverter capacity">
                        </div>
                        <div class="col-md-2 mb-1">
                            <label class="form-label" for="unmatched_no_of_inveter">No. Of Inverter <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="no_of_inveter" id="unmatched_no_of_inveter" value="{{ old('no_of_inveter') }}" placeholder="Enter inverter count">
                        </div>
                        <div class="col-md-2 mb-1">
                            <label class="form-label" for="unmatched_structure">Structure <span class="text-danger">*</span></label>
                            <select class="form-select" name="structure" id="unmatched_structure" data-placeholder="Select structure">
                                <option value="">-- Select --</option>
                                @foreach (['6ft * 8ft', '8ft * 10ft', '10ft * 12ft', 'As Per Design'] as $structure)
                                    <option value="{{ $structure }}" @selected(old('structure') === $structure)>{{ $structure }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 mb-1">
                            <label class="form-label" for="unmatched_common_meter">Common Meter</label>
                            <select class="form-select" name="common_meter" id="unmatched_common_meter" data-placeholder="Select common meter">
                                <option value="No" @selected(old('common_meter', 'No') === 'No')>No</option>
                                <option value="Yes" @selected(old('common_meter') === 'Yes')>Yes</option>
                            </select>
                        </div>

                        <div class="col-md-2 mb-1">
                            <label class="form-label" for="unmatched_total_system_cost">Total System Cost <span class="text-danger">*</span></label>
                            <input type="number" step="any" class="form-control" name="total_system_cost" id="unmatched_total_system_cost" value="{{ old('total_system_cost') }}" placeholder="Enter system cost">
                        </div>
                        <div class="col-md-2 mb-1">
                            <label class="form-label" for="unmatched_meter_charges_extra">Meter Charge Extra</label>
                            <select class="form-select" name="meter_charges_extra" id="unmatched_meter_charges_extra" data-placeholder="Select meter charge option">
                                <option value="No" @selected(old('meter_charges_extra', 'No') === 'No')>No</option>
                                <option value="Yes" @selected(old('meter_charges_extra') === 'Yes')>Yes</option>
                            </select>
                        </div>
                        <div class="col-md-2 mb-1">
                            <label class="form-label" for="unmatched_meter_charges">Meter Charges <span class="text-danger">*</span></label>
                            <input type="number" step="any" class="form-control" name="meter_charges" id="unmatched_meter_charges" value="{{ old('meter_charges') }}" placeholder="Enter meter charges">
                        </div>
                        <div class="col-md-2 mb-1">
                            <label class="form-label" for="unmatched_registration_fee">Registration Fee</label>
                            <input type="number" step="any" class="form-control" name="registration_fee" id="unmatched_registration_fee" value="{{ old('registration_fee') }}" placeholder="Enter registration fee">
                        </div>
                        <div class="col-md-2 mb-1">
                            <label class="form-label" for="unmatched_other_charge_name">Other Charge Name</label>
                            <input type="text" class="form-control" name="other_charge_name" id="unmatched_other_charge_name" value="{{ old('other_charge_name') }}" placeholder="Enter charge name">
                        </div>
                        <div class="col-md-2 mb-1">
                            <label class="form-label" for="unmatched_other_charge_amount">Other Charge Amount</label>
                            <input type="number" step="any" class="form-control" name="other_charge_amount" id="unmatched_other_charge_amount" value="{{ old('other_charge_amount') }}" placeholder="Enter charge amount">
                        </div>

                        <div class="col-md-3 mb-1">
                            <label class="form-label" for="unmatched_bank_id">Bank</label>
                            <select class="form-select" name="bank_id" id="unmatched_bank_id" data-placeholder="Select bank">
                                <option value="">-- Select --</option>
                                @foreach ($unmatchedCreateOptions['banks'] as $bank)
                                    <option value="{{ $bank->id }}" @selected((string) $selectedBank === (string) $bank->id)>{{ $bank->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 mb-1">
                            <label class="form-label" for="unmatched_state_subsidy_select">State Subsidy Type <span class="text-danger">*</span></label>
                            <select class="form-select" name="state_subsidy_select" id="unmatched_state_subsidy_select" data-placeholder="Select state subsidy type">
                                <option value="">-- Select --</option>
                                <option value="general" @selected(old('state_subsidy_select') === 'general')>GENERAL</option>
                                <option value="sc_st" @selected(old('state_subsidy_select') === 'sc_st')>SC/ST</option>
                                <option value="give_up_subsidy" @selected(old('state_subsidy_select') === 'give_up_subsidy')>Give up subsidy</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-1">
                            <label class="form-label" for="unmatched_total_amount">Total Amount <span class="text-danger">*</span></label>
                            <input type="number" step="any" class="form-control" name="total_amount" id="unmatched_total_amount" value="{{ old('total_amount') }}" placeholder="Calculated total amount" readonly>
                        </div>
                    </div>

                    <div class="accordion mt-3" id="unmatchedCommercialAccordion">
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="unmatchedCommercialHeading">
                                <button class="accordion-button collapsed bg-warning" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#unmatchedCommercialCollapse" aria-expanded="false"
                                    aria-controls="unmatchedCommercialCollapse">
                                    <b>Commercial Offer</b>
                                </button>
                            </h2>
                            <div id="unmatchedCommercialCollapse" class="accordion-collapse collapse"
                                aria-labelledby="unmatchedCommercialHeading" data-bs-parent="#unmatchedCommercialAccordion">
                                <div class="accordion-body">
                                    <table class="table table-bordered">
                                        <tr>
                                            <th style="width: 20px;">Sr. no.</th>
                                            <th style="min-width: 250px;">Description</th>
                                            <th style="width: 150px;">Installed Capacity (KW)</th>
                                            <th style="width: 150px;">Rate / KW</th>
                                            <th style="width: 150px;">Value</th>
                                        </tr>
                                        <tr>
                                            <td>1</td>
                                            <td>Complete Epc Price For Design, Engineering, Supply And Installation And Testing Of Solar Power Generating System</td>
                                            <td id="unmatched_span_pv_capacity_kw">{{ number_format((float) old('pv_capacity_kw', 0), 2, '.', '') }}</td>
                                            <td id="unmatched_span_rate">{{ ((float) old('pv_capacity_kw', 0) > 0 && (float) old('total_system_cost', 0) > 0) ? number_format((float) old('total_system_cost') / (float) old('pv_capacity_kw'), 2, '.', '') : '0.00' }}</td>
                                            <td id="unmatched_span_value">{{ number_format((float) old('total_system_cost', 0), 2, '.', '') }}</td>
                                        </tr>
                                        <tr>
                                            <td colspan="4" align="right">Meter Charge (Approx)</td>
                                            <td id="unmatched_span_meter_charges">{{ number_format((float) old('meter_charges', 0), 2, '.', '') }}</td>
                                        </tr>
                                        <tr>
                                            <td colspan="4" align="right">Registration Charges</td>
                                            <td id="unmatched_span_registration_fee">{{ number_format((float) old('registration_fee', 0), 2, '.', '') }}</td>
                                        </tr>
                                        <tr>
                                            <td id="unmatched_span_other_charge_name" colspan="4" align="right">{{ old('other_charge_name') ?: 'Other Charges' }}</td>
                                            <td id="unmatched_span_other_charge">{{ number_format((float) old('other_charge_amount', 0), 2, '.', '') }}</td>
                                        </tr>
                                        <tr>
                                            <td colspan="4" align="right"><b>Total Project Cost (Payable)</b></td>
                                            <td id="unmatched_span_total_project_cost">{{ number_format((float) old('total_system_cost', 0) + (float) old('meter_charges', 0) + (float) old('registration_fee', 0) + (float) old('other_charge_amount', 0), 2, '.', '') }}</td>
                                        </tr>
                                        <tr>
                                            <td colspan="4" align="right"><b>Central Gov. Subsidy</b></td>
                                            <td><input type="number" step="any" id="unmatched_span_subsidy" class="form-control p-50" name="subsidy" value="{{ number_format((float) old('subsidy', 0), 2, '.', '') }}" placeholder="Enter central subsidy"></td>
                                        </tr>
                                        <tr>
                                            <td colspan="4" align="right"><b>State Gov. Subsidy</b></td>
                                            <td><input type="number" step="any" id="unmatched_span_state_subsidy" class="form-control p-50" name="state_subsidy" value="{{ number_format((float) old('state_subsidy', 0), 2, '.', '') }}" placeholder="Enter state subsidy"></td>
                                        </tr>
                                        <tr>
                                            <td colspan="4" align="right"><b>Total Subsidy</b></td>
                                            <td id="unmatched_span_total_subsidy">{{ number_format((float) old('subsidy', 0) + (float) old('state_subsidy', 0), 2, '.', '') }}</td>
                                        </tr>
                                        <tr>
                                            <td colspan="4" align="right"><b>Net Customer Price</b></td>
                                            <td id="unmatched_span_net_customer_price">{{ number_format((float) old('total_system_cost', 0) + (float) old('meter_charges', 0) + (float) old('registration_fee', 0) + (float) old('other_charge_amount', 0) - (float) old('subsidy', 0) - (float) old('state_subsidy', 0), 2, '.', '') }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="accordion mt-2" id="unmatchedTechnicalAccordion">
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="unmatchedTechnicalHeading">
                                <button class="accordion-button collapsed bg-warning" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#unmatchedTechnicalCollapse" aria-expanded="false"
                                    aria-controls="unmatchedTechnicalCollapse">
                                    <b>Technical Specifications &amp; BOM</b>
                                </button>
                            </h2>
                            <div id="unmatchedTechnicalCollapse" class="accordion-collapse collapse"
                                aria-labelledby="unmatchedTechnicalHeading" data-bs-parent="#unmatchedTechnicalAccordion">
                                <div class="accordion-body">
                                    @include('admin.sales-quatation.technical_specifications', [
                                        'type' => 'res',
                                        'prefillData' => $technicalSpecification,
                                    ])
                                </div>
                            </div>
                        </div>
                    </div>

                    <h6 class="fw-bold mt-3">Sales Order Details</h6>
                    <hr class="my-1">
                    <div class="row">
                        <div class="col-md-3 mb-1">
                            <label class="form-label" for="unmatched_consumer_type">Consumer Type <span class="text-danger">*</span></label>
                            <select class="form-select" name="consumer_type" id="unmatched_consumer_type" data-placeholder="Select consumer type">
                                <option value="">-- Select --</option>
                                <option value="Resident" @selected(old('consumer_type') === 'Resident')>Resident</option>
                                <option value="Industrial" @selected(old('consumer_type') === 'Industrial')>Industrial</option>
                                <option value="Commercial" @selected(old('consumer_type') === 'Commercial')>Commercial</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-1">
                            <label class="form-label" for="unmatched_contact_number">Contact Number <span class="text-danger">*</span></label>
                            <input type="text" maxlength="10" class="form-control" name="contact_number" id="unmatched_contact_number" value="{{ old('contact_number') }}" placeholder="Enter contact number">
                        </div>
                        <div class="col-md-3 mb-1">
                            <label class="form-label" for="unmatched_email">Email</label>
                            <input type="email" class="form-control" name="email" id="unmatched_email" value="{{ old('email') }}" placeholder="Enter email address">
                        </div>
                        <div class="col-md-3 mb-1">
                            <label class="form-label" for="unmatched_pin_code">Pincode <span class="text-danger">*</span></label>
                            <input type="text" maxlength="6" class="form-control" name="pin_code" id="unmatched_pin_code" value="{{ old('pin_code') }}" placeholder="Enter pincode">
                        </div>

                        <div class="col-md-3 mb-1">
                            <label class="form-label" for="unmatched_district_id">District <span class="text-danger">*</span></label>
                            <select class="form-select" name="district_id" id="unmatched_district_id" data-placeholder="Select district">
                                <option value="">-- Select --</option>
                                @foreach ($unmatchedCreateOptions['districts'] as $district)
                                    <option value="{{ $district->id }}" @selected(old('district_id') == $district->id)>{{ $district->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 mb-1">
                            <label class="form-label" for="unmatched_taluka_id">Taluka <span class="text-danger">*</span></label>
                            <select class="form-select" name="taluka_id" id="unmatched_taluka_id" data-placeholder="Select taluka">
                                <option value="">-- Select --</option>
                                @foreach ($unmatchedCreateOptions['talukas'] as $taluka)
                                    <option value="{{ $taluka->id }}" data-district-id="{{ $taluka->district_id }}" @selected(old('taluka_id') == $taluka->id)>{{ $taluka->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 mb-1">
                            <label class="form-label" for="unmatched_gst_number">GST Number</label>
                            <input type="text" maxlength="15" class="form-control" name="gst_number" id="unmatched_gst_number" value="{{ old('gst_number') }}" placeholder="Enter GST number">
                        </div>
                        <div class="col-md-3 mb-1">
                            <label class="form-label" for="unmatched_aadhaar_number">Aadhaar Number</label>
                            <input type="text" maxlength="12" class="form-control" name="aadhaar_number" id="unmatched_aadhaar_number" value="{{ old('aadhaar_number') }}" placeholder="Enter Aadhaar number">
                        </div>

                        <div class="col-md-3 mb-1">
                            <label class="form-label" for="unmatched_contracted_load">Contracted Load</label>
                            <input type="text" class="form-control" name="contracted_load" id="unmatched_contracted_load" value="{{ old('contracted_load') }}" placeholder="Enter contracted load">
                        </div>
                        <div class="col-md-3 mb-1">
                            <label class="form-label" for="unmatched_phase">Phase</label>
                            <select class="form-select" name="phase" id="unmatched_phase" data-placeholder="Select phase">
                                <option value="Single phase" @selected(old('phase', 'Single phase') === 'Single phase')>Single phase</option>
                                <option value="Three phase" @selected(old('phase') === 'Three phase')>Three phase</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-1">
                            <label class="form-label" for="unmatched_bank_name">Bank Name</label>
                            <input type="text" class="form-control" name="bank_name" id="unmatched_bank_name" value="{{ old('bank_name') }}" placeholder="Enter bank name">
                        </div>
                        <div class="col-md-3 mb-1">
                            <label class="form-label" for="unmatched_bank_account">Bank Account</label>
                            <input type="text" maxlength="50" class="form-control" name="bank_account" id="unmatched_bank_account" value="{{ old('bank_account') }}" placeholder="Enter bank account number">
                        </div>
                        <div class="col-md-3 mb-1">
                            <label class="form-label" for="unmatched_ifsc_code">IFSC Code</label>
                            <input type="text" class="form-control" name="ifsc_code" id="unmatched_ifsc_code" value="{{ old('ifsc_code') }}" placeholder="Enter IFSC code">
                        </div>

                        <div class="col-md-3 mb-1">
                            <label class="form-label" for="unmatched_sub_division_id">Sub Division</label>
                            <select class="form-select" name="sub_division_id" id="unmatched_sub_division_id" data-placeholder="Select sub division">
                                <option value="">-- Select --</option>
                                @foreach ($unmatchedCreateOptions['subDivisions'] as $subDivision)
                                    <option value="{{ $subDivision->id }}"
                                        data-division="{{ $subDivision->division_name }}"
                                        data-circle="{{ $subDivision->circle_name }}"
                                        data-discom="{{ $subDivision->discom }}"
                                        @selected((string) $selectedSubDivision === (string) $subDivision->id)>{{ $subDivision->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 mb-1">
                            <label class="form-label" for="unmatched_division">Division</label>
                            <input type="text" class="form-control" name="division" id="unmatched_division" value="{{ old('division', $recommended['division_name'] ?? '') }}" placeholder="Division" readonly>
                        </div>
                        <div class="col-md-3 mb-1">
                            <label class="form-label" for="unmatched_circle">Circle</label>
                            <input type="text" class="form-control" name="circle" id="unmatched_circle" value="{{ old('circle', $recommended['circle_name'] ?? '') }}" placeholder="Circle" readonly>
                        </div>
                        <div class="col-md-3 mb-1">
                            <label class="form-label" for="unmatched_discom">DISCOM</label>
                            <input type="text" class="form-control" name="discom" id="unmatched_discom" value="{{ old('discom', $recommended['discom_name'] ?? '') }}" placeholder="DISCOM" readonly>
                        </div>

                        <div class="col-md-3 mb-1">
                            <label class="form-label" for="unmatched_bom_id">BOM</label>
                            <select class="form-select" name="bom_id" id="unmatched_bom_id" data-placeholder="Select BOM">
                                <option value="0">None</option>
                                @foreach ($unmatchedCreateOptions['boms'] as $bom)
                                    <option value="{{ $bom->id }}" @selected(old('bom_id') == $bom->id)>{{ $bom->bom_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 mb-1">
                            <label class="form-label" for="unmatched_file_type">Payment Type</label>
                            <select class="form-select" name="file_type" id="unmatched_file_type" data-placeholder="Select payment type">
                                <option value="C" @selected(old('file_type', 'C') === 'C')>Cash</option>
                                <option value="L" @selected(old('file_type') === 'L')>Loan</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-1">
                            <label class="form-label" for="unmatched_remark">Remark</label>
                            <textarea class="form-control" name="remark" id="unmatched_remark" rows="1" placeholder="Enter remark">{{ old('remark') }}</textarea>
                        </div>

                        <div class="col-12 mb-1">
                            <label class="form-label">Documents</label>
                            <div id="unmatched-document-list">
                                @foreach ($oldDocuments as $documentIndex => $oldDocument)
                                    <div class="row g-1 mb-1 unmatched-document-row" data-index="{{ $documentIndex }}">
                                        <div class="col-md-5">
                                            <select class="form-select select2" name="document[{{ $documentIndex }}][name]" data-placeholder="Select document">
                                                <option value="">-- Select --</option>
                                                @foreach ($documentOptions as $documentValue => $documentLabel)
                                                    <option value="{{ $documentValue }}" @selected(($oldDocument['name'] ?? '') === $documentValue)>{{ $documentLabel }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <input type="file" class="form-control" name="document[{{ $documentIndex }}][image]" data-placeholder="Upload document" title="Upload document">
                                        </div>
                                        <div class="col-md-1 d-flex align-items-center">
                                            <button type="button" class="btn btn-sm btn-outline-danger unmatched-document-remove" aria-label="Remove document">
                                                <i data-feather="x"></i>
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-secondary mt-1" id="unmatched-document-add">Add Document</button>
                            <template id="unmatched-document-template">
                                <div class="row g-1 mb-1 unmatched-document-row" data-index="__INDEX__">
                                    <div class="col-md-5">
                                        <select class="form-select select2" name="document[__INDEX__][name]" data-placeholder="Select document">
                                            <option value="">-- Select --</option>
                                            @foreach ($documentOptions as $documentValue => $documentLabel)
                                                <option value="{{ $documentValue }}">{{ $documentLabel }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <input type="file" class="form-control" name="document[__INDEX__][image]" data-placeholder="Upload document" title="Upload document">
                                    </div>
                                    <div class="col-md-1 d-flex align-items-center">
                                        <button type="button" class="btn btn-sm btn-outline-danger unmatched-document-remove" aria-label="Remove document">
                                            <i data-feather="x"></i>
                                        </button>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-1 mt-2">
                        <a class="btn btn-sm btn-outline-secondary" href="{{ route('sales-master-consumer-preview') }}">Cancel</a>
                        <button type="submit" class="btn btn-sm btn-primary" id="unmatched-create-submit">Create</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('pagescript')
    <script>
        $(function() {
            function unmatchedText(value) {
                return value == null ? '' : String(value);
            }

            var unmatchedFieldMessageMap = {
                'Enter Consumer Number.': 'consumer_number',
                'The Consumer Number has already been taken.': 'consumer_number',
                'Enter Consumer Name.': 'consumer_name_display',
                'Enter Mobile Number.': 'mobile',
                'Enter Valid Mobile Number.': 'mobile',
                'Enter Address.': 'address',
                'Select Agent/Sales Person.': 'agent_sales_person_id',
                'Select Panel Company.': 'penal_company_id',
                'Select Panel Type.': 'penal_type_id',
                'Select Panel Watt.': 'penal_watt_id',
                'Enter Panel Nos.': 'penal_nos',
                'Enter PV Capacity.': 'pv_capacity_kw',
                'Select Inverter Company.': 'inveter_company_id',
                'Enter Inverter Capacity.': 'inveter_capacity',
                'Enter No. Of Inverter.': 'no_of_inveter',
                'Select Structure.': 'structure',
                'Enter Total System Cost.': 'total_system_cost',
                'Enter Meter Charges.': 'meter_charges',
                'Select State Subsidy Type.': 'state_subsidy_select',
                'Enter Total Amount.': 'total_amount',
                'Select Consumer Type.': 'consumer_type',
                'Select District.': 'district_id',
                'Select Taluka.': 'taluka_id',
                'Enter Pincode.': 'pin_code',
                'Enter Valid Pincode.': 'pin_code',
                'Enter Valid Email Address.': 'email',
                'Enter Contact Number.': 'contact_number',
                'Enter Valid Contact Number.': 'contact_number'
            };

            var unmatchedRequiredMessages = {
                consumer_number: 'Enter Consumer Number.',
                consumer_name_display: 'Enter Consumer Name.',
                mobile: 'Enter Mobile Number.',
                address: 'Enter Address.',
                agent_sales_person_id: 'Select Agent/Sales Person.',
                penal_company_id: 'Select Panel Company.',
                penal_type_id: 'Select Panel Type.',
                penal_watt_id: 'Select Panel Watt.',
                penal_nos: 'Enter Panel Nos.',
                pv_capacity_kw: 'Enter PV Capacity.',
                inveter_company_id: 'Select Inverter Company.',
                inveter_capacity: 'Enter Inverter Capacity.',
                no_of_inveter: 'Enter No. Of Inverter.',
                structure: 'Select Structure.',
                total_system_cost: 'Enter Total System Cost.',
                meter_charges: 'Enter Meter Charges.',
                state_subsidy_select: 'Select State Subsidy Type.',
                total_amount: 'Enter Total Amount.',
                consumer_type: 'Select Consumer Type.',
                district_id: 'Select District.',
                taluka_id: 'Select Taluka.',
                pin_code: 'Enter Pincode.',
                contact_number: 'Enter Contact Number.'
            };

            function unmatchedClearFieldErrors() {
                $('#unmatched-create-form .unmatched-field-error').remove();
                $('#unmatched-create-form .is-invalid').removeClass('is-invalid');
            }

            function unmatchedFieldElement(name) {
                if (name === 'consumer_number') {
                    return $('#unmatched_excel_number');
                }

                var selectorName = name;
                var nameParts = String(name).split('.');

                if (nameParts.length > 1) {
                    selectorName = nameParts[0];

                    for (var index = 1; index < nameParts.length; index++) {
                        selectorName += '[' + nameParts[index] + ']';
                    }
                }

                return $('#unmatched-create-form').find('[name="' + selectorName + '"]').not('[type="hidden"]').first();
            }

            function unmatchedSetFieldError(name, message) {
                var $container = name === 'submit'
                    ? $('#unmatched-create-submit').closest('div')
                    : unmatchedFieldElement(name).closest('.mb-1, .row, .accordion-body');
                var $error = $container.children('.unmatched-field-error[data-error-for="' + name + '"]');

                if (!$error.length) {
                    $error = $('<div></div>')
                        .addClass('invalid-feedback d-block unmatched-field-error small text-danger mb-0 mt-1')
                        .attr('data-error-for', name);
                    $container.append($error);
                }

                $error.text(message).removeClass('d-none');

                if (name !== 'submit') {
                    unmatchedFieldElement(name).addClass('is-invalid');
                }
            }

            function unmatchedFieldForMessage(message) {
                if (unmatchedFieldMessageMap[message]) {
                    return unmatchedFieldMessageMap[message];
                }

                var documentFieldMatch = String(message).match(/document\.(\d+)\.(name|image)/i);

                if (documentFieldMatch) {
                    return 'document[' + documentFieldMatch[1] + '][' + documentFieldMatch[2] + ']';
                }

                if (/document file/i.test(message)) {
                    return 'document[0][image]';
                }

                if (/up to 10 documents/i.test(message)) {
                    return 'document[0][image]';
                }

                if (/email/i.test(message)) {
                    return 'email';
                }

                if (/gst/i.test(message)) {
                    return 'gst_number';
                }

                if (/aadhaar/i.test(message)) {
                    return 'aadhaar_number';
                }

                if (/bank account/i.test(message)) {
                    return 'bank_account';
                }

                if (/ifsc/i.test(message)) {
                    return 'ifsc_code';
                }

                if (/sub division/i.test(message)) {
                    return 'sub_division_id';
                }

                if (/bank name/i.test(message)) {
                    return 'bank_name';
                }

                if (/contracted load/i.test(message)) {
                    return 'contracted_load';
                }

                if (/phase/i.test(message)) {
                    return 'phase';
                }

                if (/division/i.test(message)) {
                    return 'division';
                }

                if (/circle/i.test(message)) {
                    return 'circle';
                }

                if (/discom/i.test(message)) {
                    return 'discom';
                }

                if (/remark/i.test(message)) {
                    return 'remark';
                }

                if (/file type/i.test(message)) {
                    return 'file_type';
                }

                if (/pincode/i.test(message)) {
                    return 'pin_code';
                }

                if (/consumer number/i.test(message)) {
                    return 'consumer_number';
                }

                if (/contact number/i.test(message)) {
                    return 'contact_number';
                }

                if (/mobile/i.test(message)) {
                    return 'mobile';
                }

                return 'submit';
            }

            function unmatchedScrollToFirstError() {
                var $first = $('#unmatched-create-form .unmatched-field-error').first();

                if ($first.length) {
                    $first[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }

            function unmatchedFieldErrors(errors) {
                unmatchedClearFieldErrors();

                $.each(errors, function(name, message) {
                    unmatchedSetFieldError(name, unmatchedText(message));
                });

                unmatchedScrollToFirstError();
            }

            function unmatchedErrors(errors) {
                unmatchedClearFieldErrors();

                if (!errors || !errors.length) {
                    return;
                }

                $.each(errors, function(_, item) {
                    if (item && typeof item === 'object') {
                        $.each(item, function(_, message) {
                            var text = unmatchedText(message);

                            unmatchedSetFieldError(unmatchedFieldForMessage(text), text);
                        });
                    } else {
                        var text = unmatchedText(item);

                        unmatchedSetFieldError(unmatchedFieldForMessage(text), text);
                    }
                });

                unmatchedScrollToFirstError();
            }

            function unmatchedNumber(value) {
                var number = parseFloat(value);

                return isNaN(number) ? 0 : number;
            }

            function applyUnmatchedMeterChargeMode() {
                var isExtra = $('#unmatched_meter_charges_extra').val() === 'Yes';

                if (isExtra) {
                    $('#unmatched_meter_charges').val('0').prop('readonly', true);
                } else {
                    $('#unmatched_meter_charges').prop('readonly', false);
                }

                calculateUnmatchedTotal();
            }

            function calculateUnmatchedCapacity() {
                var panelNos = unmatchedNumber($('#unmatched_penal_nos').val());
                var panelWatt = unmatchedNumber($('#unmatched_penal_watt_id option:selected').text());

                $('#unmatched_pv_capacity_kw').val(((panelWatt * panelNos) / 1000).toFixed(3));
            }

            function calculateUnmatchedSubsidy() {
                var commonMeter = $('#unmatched_common_meter').val();
                var capacity = unmatchedNumber($('#unmatched_pv_capacity_kw').val());
                var subsidy = 0;

                if (commonMeter === 'Yes') {
                    subsidy = capacity > 500 ? 500 * 18000 : capacity * 18000;
                } else if (capacity > 3) {
                    subsidy = 78000;
                } else if (capacity <= 2) {
                    subsidy = capacity * 30000;
                } else {
                    subsidy = 2 * 30000 + (capacity - 2) * 18000;
                }

                $('#unmatched_span_subsidy').val(subsidy.toFixed(2));
            }

            function calculateUnmatchedStateSubsidy() {
                var subsidyType = $('#unmatched_state_subsidy_select').val();
                var capacity = unmatchedNumber($('#unmatched_pv_capacity_kw').val());
                var subsidy = 0;

                if (subsidyType === 'give_up_subsidy' || capacity <= 0 || capacity > 2.18) {
                    subsidy = 0;
                } else {
                    var firstKw = Math.min(capacity, 1);
                    var secondKw = Math.min(Math.max(capacity - 1, 0), 1);
                    var remainingKw = Math.max(capacity - 2, 0);
                    var firstRate = subsidyType === 'sc_st' ? 17500 : 15000;
                    var secondRate = subsidyType === 'sc_st' ? 17500 : 15000;
                    var remainingRate = subsidyType === 'sc_st' ? 24750 : 22500;

                    subsidy = (firstKw * firstRate) + (secondKw * secondRate) + (remainingKw * remainingRate);
                }

                $('#unmatched_span_state_subsidy').val(subsidy.toFixed(2));
            }

            function recalculateUnmatchedQuotation() {
                calculateUnmatchedCapacity();
                calculateUnmatchedSubsidy();
                calculateUnmatchedStateSubsidy();
                calculateUnmatchedTotal();
            }

            function updateUnmatchedCommercialOffer() {
                var capacity = unmatchedNumber($('#unmatched_pv_capacity_kw').val());
                var systemCost = unmatchedNumber($('#unmatched_total_system_cost').val());
                var meterCharges = unmatchedNumber($('#unmatched_meter_charges').val());
                var registrationFee = unmatchedNumber($('#unmatched_registration_fee').val());
                var otherCharge = unmatchedNumber($('#unmatched_other_charge_amount').val());
                var subsidy = unmatchedNumber($('#unmatched_span_subsidy').val());
                var stateSubsidy = unmatchedNumber($('#unmatched_span_state_subsidy').val());
                var totalProjectCost = systemCost + meterCharges + registrationFee + otherCharge;
                var rate = capacity > 0 ? systemCost / capacity : 0;
                var totalSubsidy = subsidy + stateSubsidy;

                $('#unmatched_span_pv_capacity_kw').text(capacity.toFixed(2));
                $('#unmatched_span_rate').text(rate.toFixed(2));
                $('#unmatched_span_value').text(systemCost.toFixed(2));
                $('#unmatched_span_meter_charges').text(meterCharges.toFixed(2));
                $('#unmatched_span_registration_fee').text(registrationFee.toFixed(2));
                $('#unmatched_span_other_charge_name').text($('#unmatched_other_charge_name').val() || 'Other Charges');
                $('#unmatched_span_other_charge').text(otherCharge.toFixed(2));
                $('#unmatched_span_total_project_cost').text(totalProjectCost.toFixed(2));
                $('#unmatched_span_total_subsidy').text(totalSubsidy.toFixed(2));
                $('#unmatched_span_net_customer_price').text((totalProjectCost - totalSubsidy).toFixed(2));
            }

            function calculateUnmatchedTotal() {
                var values = [
                    '#unmatched_total_system_cost',
                    '#unmatched_meter_charges',
                    '#unmatched_registration_fee',
                    '#unmatched_other_charge_amount'
                ];
                var total = 0;

                $.each(values, function(_, selector) {
                    var value = parseFloat($(selector).val());

                    if (!isNaN(value)) {
                        total += value;
                    }
                });

                $('#unmatched_total_amount').val(total.toFixed(2));
                updateUnmatchedCommercialOffer();
            }

            function filterUnmatchedTalukas() {
                var districtId = $('#unmatched_district_id').val();

                $('#unmatched_taluka_id option').each(function() {
                    var $option = $(this);
                    var matches = districtId === '' || String($option.data('district-id')) === String(districtId);

                    $option.prop('hidden', !matches);

                    if (!matches && $option.prop('selected')) {
                        $('#unmatched_taluka_id').val('');
                    }
                });
            }

            var $form = $('#unmatched-create-form');
            var initialErrors = $form.data('validation-errors');

            if (initialErrors && initialErrors.length) {
                unmatchedErrors(initialErrors);
            }

            $form.find('select').select2({
                width: '100%',
                placeholder: function () {
                    return $(this).data('placeholder');
                }
            });

            if ($.fn.summernote) {
                $form.find('.editor').summernote({
                    placeholder: 'Enter description',
                    tabsize: 2,
                    height: 100,
                    toolbar: [
                        ['font', ['bold', 'underline']]
                    ]
                });
            }

            $form.on('input change', 'input, select, textarea', function() {
                var name = this.name;

                if (!name || name === 'target') {
                    return;
                }

                $form.find('.unmatched-field-error[data-error-for="' + name + '"]').remove();
                unmatchedFieldElement(name).removeClass('is-invalid');
            });

            filterUnmatchedTalukas();
            applyUnmatchedMeterChargeMode();
            recalculateUnmatchedQuotation();

            if ($('#unmatched_sub_division_id').val() !== '') {
                $('#unmatched_sub_division_id').trigger('change');
            }

            $(document).on('click', '#unmatched-document-add', function() {
                var nextIndex = 0;

                $('#unmatched-document-list .unmatched-document-row').each(function() {
                    nextIndex = Math.max(nextIndex, parseInt($(this).attr('data-index'), 10) + 1);
                });

                var $row = $($('#unmatched-document-template').html());

                $row.attr('data-index', nextIndex);
                $row.find('[name]').each(function() {
                    $(this).attr('name', $(this).attr('name').replace('__INDEX__', nextIndex));
                });
                $('#unmatched-document-list').append($row);

                $row.find('select').select2({
                    width: '100%',
                    placeholder: function () {
                        return $(this).data('placeholder');
                    }
                });

                if (typeof feather !== 'undefined' && typeof feather.replace === 'function') {
                    feather.replace();
                }
            });

            $(document).on('click', '.unmatched-document-remove', function() {
                var $rows = $('#unmatched-document-list .unmatched-document-row');

                if ($rows.length > 1) {
                    $(this).closest('.unmatched-document-row').remove();
                    return;
                }

                var $row = $(this).closest('.unmatched-document-row');
                $row.find('select').val('').trigger('change');
                $row.find('input[type="file"]').val('');
                $row.find('.unmatched-field-error').remove();
                $row.find('.is-invalid').removeClass('is-invalid');
            });

            $(document).on('input', '#unmatched_name', function() {
                $('#unmatched_consumer_name').val(this.value);
            });

            $(document).on('input', '#unmatched_penal_nos', function() {
                recalculateUnmatchedQuotation();
            });

            $(document).on('change', '#unmatched_penal_watt_id, #unmatched_common_meter, #unmatched_state_subsidy_select', function() {
                recalculateUnmatchedQuotation();
            });

            $(document).on('change', '#unmatched_meter_charges_extra', function() {
                applyUnmatchedMeterChargeMode();
            });

            $(document).on('input', '#unmatched_total_system_cost, #unmatched_meter_charges, #unmatched_registration_fee, #unmatched_other_charge_name, #unmatched_other_charge_amount, #unmatched_span_subsidy, #unmatched_span_state_subsidy', function() {
                calculateUnmatchedTotal();
            });

            $(document).on('change', '#unmatched_district_id', function() {
                filterUnmatchedTalukas();
            });

            $(document).on('change', '#unmatched_sub_division_id', function() {
                var $option = $('#unmatched_sub_division_id option:selected');

                $('#unmatched_division').val(unmatchedText($option.data('division')));
                $('#unmatched_circle').val(unmatchedText($option.data('circle')));
                $('#unmatched_discom').val(unmatchedText($option.data('discom')));
            });

            $(document).on('click', '.variant-delete', function() {
                $(this).closest('tr').remove();
                unmatchedUpdateTechnicalSrNo($(this).data('type'));
            });

            $(document).on('click', '.add-more-res', function() {
                var $lastRow = $('.tech_res_table tbody tr:last');
                var $newRow = $lastRow.clone();
                var lastSerial = parseInt($lastRow.find('.sr_no').text(), 10);

                $newRow.find('input, textarea').val('');
                $newRow.find('.sr_no').text(lastSerial + 1);
                $('.tech_res_table tbody').append($newRow);
            });

            function unmatchedUpdateTechnicalSrNo(type) {
                $('.' + type).each(function(index) {
                    $(this).find('.sr_no').text(index + 3);
                });
            }

            $(document).on('submit', '#unmatched-create-form', function(e) {
                e.preventDefault();

                var $submit = $('#unmatched-create-submit');
                var originalHtml = $submit.data('original-html') || $submit.html();
                var required = [
                    'consumer_number', 'consumer_name_display', 'mobile', 'address',
                    'agent_sales_person_id', 'penal_company_id', 'penal_type_id', 'penal_watt_id',
                    'penal_nos', 'pv_capacity_kw', 'inveter_company_id', 'inveter_capacity',
                    'no_of_inveter', 'structure', 'total_system_cost', 'meter_charges',
                    'state_subsidy_select', 'total_amount', 'consumer_type', 'district_id',
                    'taluka_id', 'pin_code', 'contact_number'
                ];

                var fieldErrors = {};

                $.each(required, function(_, name) {
                    var value = $form.find('[name="' + name + '"]').val();

                    if (value == null || String(value).trim() === '') {
                        fieldErrors[name] = unmatchedRequiredMessages[name];
                    }
                });

                if (!/^[6-9][0-9]{9}$/.test(String($('#unmatched_mobile').val() || ''))) {
                    fieldErrors.mobile = 'Enter Valid Mobile Number.';
                }

                if (!/^[6-9][0-9]{9}$/.test(String($('#unmatched_contact_number').val() || ''))) {
                    fieldErrors.contact_number = 'Enter Valid Contact Number.';
                }

                var pinCode = String($('#unmatched_pin_code').val() || '').trim();

                if (pinCode && !/^[0-9]{6}$/.test(pinCode)) {
                    fieldErrors.pin_code = 'Enter Valid Pincode.';
                }

                var email = String($('#unmatched_email').val() || '').trim();

                if (email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                    fieldErrors.email = 'Enter Valid Email Address.';
                }

                var documentCount = 0;

                $('#unmatched-document-list .unmatched-document-row').each(function() {
                    documentCount++;

                    var documentIndex = $(this).attr('data-index');
                    var documentName = $(this).find('select').val();
                    var hasDocumentFile = $(this).find('input[type="file"]').val() !== '';

                    if (hasDocumentFile && !documentName) {
                        fieldErrors['document[' + documentIndex + '][name]'] = 'Select Document Name.';
                    }

                    if (documentName && !hasDocumentFile) {
                        fieldErrors['document[' + documentIndex + '][image]'] = 'Upload Document.';
                    }
                });

                if (documentCount > 10) {
                    fieldErrors['document[0][image]'] = 'You can upload up to 10 documents.';
                }

                $('#unmatched_consumer_name').val($('#unmatched_name').val());

                if (!$.isEmptyObject(fieldErrors)) {
                    unmatchedFieldErrors(fieldErrors);
                    return;
                }

                unmatchedClearFieldErrors();
                $submit.data('original-html', originalHtml).prop('disabled', true)
                    .html('<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>Creating...');

                $.ajax({
                    url: $form.attr('action'),
                    type: 'POST',
                    data: new FormData(this),
                    processData: false,
                    contentType: false,
                    dataType: 'json',
                    success: function(response) {
                        if (!response.success) {
                            unmatchedErrors(response.errors || [response.message]);
                            return;
                        }

                        Swal.fire({
                            title: "{{ __('message.Success') }}",
                            text: response.message,
                            icon: 'success',
                            customClass: {
                                confirmButton: 'btn btn-primary'
                            },
                            buttonsStyling: false
                        }).then(function() {
                            window.location.href = $form.data('preview-url');
                        });
                    },
                    error: function(xhr) {
                        var errors = xhr.responseJSON && xhr.responseJSON.errors
                            ? xhr.responseJSON.errors
                            : ['Unable to create the selected record(s). Please try again.'];

                        unmatchedErrors(errors);
                    },
                    complete: function() {
                        $submit.prop('disabled', false).html(originalHtml);
                    }
                });
            });
        });
    </script>
@endsection
