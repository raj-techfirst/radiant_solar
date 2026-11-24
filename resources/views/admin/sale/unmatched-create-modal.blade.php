<div class="modal fade" id="unmatchedCreateModal" tabindex="-1" aria-labelledby="unmatchedCreateModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-xl">
        <div class="modal-content">
            <form id="unmatched-create-form" action="{{ route('sales-master-consumer-preview-create-from-unmatched') }}" method="POST" enctype="multipart/form-data" novalidate>
                @csrf
                <input type="hidden" name="target" value="both">
                <input type="hidden" name="preview_row" id="unmatched_preview_row">
                <input type="hidden" name="consumer_number" id="unmatched_consumer_number">
                <input type="hidden" name="consumer_name" id="unmatched_consumer_name">

                <div class="modal-header">
                    <h5 class="modal-title" id="unmatchedCreateModalLabel">Create From Unmatched Record</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div id="unmatched-create-error" class="alert alert-danger d-none py-1 px-2 small mb-2"></div>

                    <div class="row mb-2">
                        <div class="col-md-6">
                            <label class="form-label">Excel Consumer Name</label>
                            <input type="text" class="form-control" id="unmatched_excel_name" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Excel Consumer Number</label>
                            <input type="text" class="form-control" id="unmatched_excel_number" readonly>
                        </div>
                    </div>

                    <h6 class="fw-bold mt-2">Common And Resident Quotation Details</h6>
                    <hr class="my-1">
                    <div class="row">
                        <div class="col-md-4 mb-1">
                            <label class="form-label" for="unmatched_name">Customer Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="consumer_name_display" id="unmatched_name" oninput="this.value = this.value.toUpperCase()">
                        </div>
                        <div class="col-md-4 mb-1">
                            <label class="form-label" for="unmatched_mobile">Mobile <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="mobile" id="unmatched_mobile">
                        </div>
                        <div class="col-md-6 mb-1">
                            <label class="form-label" for="unmatched_address">Address <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="address" id="unmatched_address">
                        </div>
                        <div class="col-md-3 mb-1">
                            <label class="form-label" for="unmatched_reference">Reference</label>
                            <input type="text" class="form-control" name="reference" id="unmatched_reference" readonly>
                        </div>
                        <div class="col-md-3 mb-1">
                            <label class="form-label" for="unmatched_agent_sales_person_id">Agent/Sales Person <span class="text-danger">*</span></label>
                            <select class="form-select" name="agent_sales_person_id" id="unmatched_agent_sales_person_id">
                                <option value="">-- Select --</option>
                                @foreach ($unmatchedCreateOptions['agentSalesPersons'] as $agent)
                                    <option value="{{ $agent->id }}">{{ $agent->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3 mb-1">
                            <label class="form-label" for="unmatched_penal_company_id">Panel Company <span class="text-danger">*</span></label>
                            <select class="form-select" name="penal_company_id" id="unmatched_penal_company_id">
                                <option value="">-- Select --</option>
                                @foreach ($unmatchedCreateOptions['penalCompanies'] as $panelCompany)
                                    <option value="{{ $panelCompany->id }}">{{ $panelCompany->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 mb-1">
                            <label class="form-label" for="unmatched_penal_type_id">Panel Type</label>
                            <select class="form-select" name="penal_type_id" id="unmatched_penal_type_id">
                                <option value="">-- Select --</option>
                                @foreach ($unmatchedCreateOptions['penalTypes'] as $panelType)
                                    <option value="{{ $panelType->id }}">{{ $panelType->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 mb-1">
                            <label class="form-label" for="unmatched_penal_watt_id">Panel Watt <span class="text-danger">*</span></label>
                            <select class="form-select" name="penal_watt_id" id="unmatched_penal_watt_id">
                                <option value="">-- Select --</option>
                                @foreach ($unmatchedCreateOptions['penalWatts'] as $panelWatt)
                                    <option value="{{ $panelWatt->id }}">{{ $panelWatt->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 mb-1">
                            <label class="form-label" for="unmatched_penal_nos">Panel Nos</label>
                            <input type="number" class="form-control" name="penal_nos" id="unmatched_penal_nos">
                        </div>
                        <div class="col-md-2 mb-1">
                            <label class="form-label" for="unmatched_pv_capacity_kw">PV Capacity KW <span class="text-danger">*</span></label>
                            <input type="number" step="any" class="form-control" name="pv_capacity_kw" id="unmatched_pv_capacity_kw">
                        </div>

                        <div class="col-md-3 mb-1">
                            <label class="form-label" for="unmatched_inveter_company_id">Inverter Company <span class="text-danger">*</span></label>
                            <select class="form-select" name="inveter_company_id" id="unmatched_inveter_company_id">
                                <option value="">-- Select --</option>
                                @foreach ($unmatchedCreateOptions['inverterCompanies'] as $inverterCompany)
                                    <option value="{{ $inverterCompany->id }}">{{ $inverterCompany->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 mb-1">
                            <label class="form-label" for="unmatched_inveter_capacity">Inverter Capacity</label>
                            <input type="text" class="form-control" name="inveter_capacity" id="unmatched_inveter_capacity">
                        </div>
                        <div class="col-md-2 mb-1">
                            <label class="form-label" for="unmatched_no_of_inveter">No. Of Inverter</label>
                            <input type="number" class="form-control" name="no_of_inveter" id="unmatched_no_of_inveter">
                        </div>
                        <div class="col-md-2 mb-1">
                            <label class="form-label" for="unmatched_structure">Structure <span class="text-danger">*</span></label>
                            <select class="form-select" name="structure" id="unmatched_structure">
                                <option value="">-- Select --</option>
                                @foreach (['6ft * 8ft', '8ft * 10ft', '10ft * 12ft', 'As Per Design'] as $structure)
                                    <option value="{{ $structure }}">{{ $structure }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 mb-1">
                            <label class="form-label" for="unmatched_common_meter">Common Meter</label>
                            <select class="form-select" name="common_meter" id="unmatched_common_meter">
                                <option value="No">No</option>
                                <option value="Yes">Yes</option>
                            </select>
                        </div>

                        <div class="col-md-2 mb-1">
                            <label class="form-label" for="unmatched_total_system_cost">Total System Cost</label>
                            <input type="number" step="any" class="form-control" name="total_system_cost" id="unmatched_total_system_cost">
                        </div>
                        <div class="col-md-2 mb-1">
                            <label class="form-label" for="unmatched_meter_charges_extra">Meter Charge Extra</label>
                            <select class="form-select" name="meter_charges_extra" id="unmatched_meter_charges_extra">
                                <option value="No">No</option>
                                <option value="Yes">Yes</option>
                            </select>
                        </div>
                        <div class="col-md-2 mb-1">
                            <label class="form-label" for="unmatched_meter_charges">Meter Charges</label>
                            <input type="number" step="any" class="form-control" name="meter_charges" id="unmatched_meter_charges">
                        </div>
                        <div class="col-md-2 mb-1">
                            <label class="form-label" for="unmatched_registration_fee">Registration Fee</label>
                            <input type="number" step="any" class="form-control" name="registration_fee" id="unmatched_registration_fee">
                        </div>
                        <div class="col-md-2 mb-1">
                            <label class="form-label" for="unmatched_other_charge_name">Other Charge Name</label>
                            <input type="text" class="form-control" name="other_charge_name" id="unmatched_other_charge_name">
                        </div>
                        <div class="col-md-2 mb-1">
                            <label class="form-label" for="unmatched_other_charge_amount">Other Charge Amount</label>
                            <input type="number" step="any" class="form-control" name="other_charge_amount" id="unmatched_other_charge_amount">
                        </div>

                        <div class="col-md-3 mb-1">
                            <label class="form-label" for="unmatched_subsidy">Subsidy</label>
                            <input type="number" step="any" class="form-control" name="subsidy" id="unmatched_subsidy" value="0">
                        </div>
                        <div class="col-md-3 mb-1">
                            <label class="form-label" for="unmatched_bank_id">Bank</label>
                            <select class="form-select" name="bank_id" id="unmatched_bank_id">
                                <option value="">-- Select --</option>
                                @foreach ($unmatchedCreateOptions['banks'] as $bank)
                                    <option value="{{ $bank->id }}" {{ $bank->default == 1 ? 'selected' : '' }}>{{ $bank->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 mb-1">
                            <label class="form-label" for="unmatched_total_amount">Total Amount <span class="text-danger">*</span></label>
                            <input type="number" step="any" class="form-control" name="total_amount" id="unmatched_total_amount" readonly>
                        </div>
                    </div>

                    <div class="mt-2">
                        <h6 class="fw-bold">Technical Specification</h6>
                        <hr class="my-1">
                        <table class="table table-sm table-bordered" id="unmatched-technical-table">
                            <thead>
                                <tr>
                                    <th>Description</th>
                                    <th>Qty</th>
                                    <th>Size</th>
                                    <th>Make</th>
                                    <th>Type</th>
                                    <th style="width:40px;"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr data-unmatched-technical-row>
                                    <td><input type="text" class="form-control form-control-sm" name="technical[description][]"></td>
                                    <td><input type="text" class="form-control form-control-sm" name="technical[qty][]"></td>
                                    <td><input type="text" class="form-control form-control-sm" name="technical[size][]"></td>
                                    <td><input type="text" class="form-control form-control-sm" name="technical[make][]"></td>
                                    <td><input type="text" class="form-control form-control-sm" name="technical[type][]"></td>
                                    <td><button type="button" class="btn btn-sm btn-outline-danger unmatched-technical-remove">x</button></td>
                                </tr>
                            </tbody>
                        </table>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="unmatched-technical-add">Add Row</button>
                    </div>

                    <h6 class="fw-bold mt-3">Sales Order Details</h6>
                    <hr class="my-1">
                    <div class="row">
                        <div class="col-md-3 mb-1">
                            <label class="form-label" for="unmatched_consumer_type">Consumer Type <span class="text-danger">*</span></label>
                            <select class="form-select" name="consumer_type" id="unmatched_consumer_type">
                                <option value="">-- Select --</option>
                                <option value="Resident">Resident</option>
                                <option value="Industrial">Industrial</option>
                                <option value="Commercial">Commercial</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-1">
                            <label class="form-label" for="unmatched_contact_number">Contact Number <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="contact_number" id="unmatched_contact_number">
                        </div>
                        <div class="col-md-3 mb-1">
                            <label class="form-label" for="unmatched_email">Email</label>
                            <input type="email" class="form-control" name="email" id="unmatched_email">
                        </div>
                        <div class="col-md-3 mb-1">
                            <label class="form-label" for="unmatched_pin_code">Pincode <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="pin_code" id="unmatched_pin_code">
                        </div>

                        <div class="col-md-3 mb-1">
                            <label class="form-label" for="unmatched_district_id">District <span class="text-danger">*</span></label>
                            <select class="form-select" name="district_id" id="unmatched_district_id">
                                <option value="">-- Select --</option>
                                @foreach ($unmatchedCreateOptions['districts'] as $district)
                                    <option value="{{ $district->id }}">{{ $district->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 mb-1">
                            <label class="form-label" for="unmatched_taluka_id">Taluka <span class="text-danger">*</span></label>
                            <select class="form-select" name="taluka_id" id="unmatched_taluka_id">
                                <option value="">-- Select --</option>
                                @foreach ($unmatchedCreateOptions['talukas'] as $taluka)
                                    <option value="{{ $taluka->id }}" data-district-id="{{ $taluka->district_id }}">{{ $taluka->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 mb-1">
                            <label class="form-label" for="unmatched_gst_number">GST Number</label>
                            <input type="text" class="form-control" name="gst_number" id="unmatched_gst_number">
                        </div>
                        <div class="col-md-3 mb-1">
                            <label class="form-label" for="unmatched_aadhaar_number">Aadhaar Number</label>
                            <input type="text" class="form-control" name="aadhaar_number" id="unmatched_aadhaar_number">
                        </div>

                        <div class="col-md-3 mb-1">
                            <label class="form-label" for="unmatched_contracted_load">Contracted Load</label>
                            <input type="text" class="form-control" name="contracted_load" id="unmatched_contracted_load">
                        </div>
                        <div class="col-md-3 mb-1">
                            <label class="form-label" for="unmatched_phase">Phase</label>
                            <select class="form-select" name="phase" id="unmatched_phase">
                                <option value="Single phase">Single phase</option>
                                <option value="Three phase">Three phase</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-1">
                            <label class="form-label" for="unmatched_bank_name">Bank Name</label>
                            <input type="text" class="form-control" name="bank_name" id="unmatched_bank_name">
                        </div>
                        <div class="col-md-3 mb-1">
                            <label class="form-label" for="unmatched_bank_account">Bank Account</label>
                            <input type="text" class="form-control" name="bank_account" id="unmatched_bank_account">
                        </div>
                        <div class="col-md-3 mb-1">
                            <label class="form-label" for="unmatched_ifsc_code">IFSC Code</label>
                            <input type="text" class="form-control" name="ifsc_code" id="unmatched_ifsc_code">
                        </div>

                        <div class="col-md-3 mb-1">
                            <label class="form-label" for="unmatched_sub_division_id">Sub Division</label>
                            <select class="form-select" name="sub_division_id" id="unmatched_sub_division_id">
                                <option value="">-- Select --</option>
                                @foreach ($unmatchedCreateOptions['subDivisions'] as $subDivision)
                                    <option value="{{ $subDivision->id }}" data-division="{{ $subDivision->division_name }}" data-circle="{{ $subDivision->circle_name }}" data-discom="{{ $subDivision->discom }}">{{ $subDivision->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 mb-1">
                            <label class="form-label" for="unmatched_division">Division</label>
                            <input type="text" class="form-control" name="division" id="unmatched_division" readonly>
                        </div>
                        <div class="col-md-3 mb-1">
                            <label class="form-label" for="unmatched_circle">Circle</label>
                            <input type="text" class="form-control" name="circle" id="unmatched_circle" readonly>
                        </div>
                        <div class="col-md-3 mb-1">
                            <label class="form-label" for="unmatched_discom">DISCOM</label>
                            <input type="text" class="form-control" name="discom" id="unmatched_discom" readonly>
                        </div>

                        <div class="col-md-3 mb-1">
                            <label class="form-label" for="unmatched_bom_id">BOM</label>
                            <select class="form-select" name="bom_id" id="unmatched_bom_id">
                                <option value="0">None</option>
                                @foreach ($unmatchedCreateOptions['boms'] as $bom)
                                    <option value="{{ $bom->id }}">{{ $bom->bom_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 mb-1">
                            <label class="form-label" for="unmatched_file_type">Payment Type</label>
                            <select class="form-select" name="file_type" id="unmatched_file_type">
                                <option value="C">Cash</option>
                                <option value="L">Loan</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-1">
                            <label class="form-label" for="unmatched_remark">Remark</label>
                            <textarea class="form-control" name="remark" id="unmatched_remark" rows="1"></textarea>
                        </div>

                        <div class="col-md-6 mb-1">
                            <label class="form-label" for="unmatched_document_name">Document Name</label>
                            <select class="form-select" name="document_name" id="unmatched_document_name">
                                <option value="">-- Select --</option>
                                <option value="light_bill">Light Bill</option>
                                <option value="bank_details">Bank Details</option>
                                <option value="aadhaar_card">Aadhaar Card</option>
                                <option value="pancard">Pancard</option>
                                <option value="dastavej">Dastavej</option>
                                <option value="gst_certificate">GST Certificate</option>
                                <option value="udyam_registration">Udyam Registration</option>
                                <option value="customer_signture">Customer Signature</option>
                                <option value="passport_size_photo">Passport Size Photo</option>
                                <option value="sales_invoice">Sales Invoice</option>
                                <option value="application_acknowledgement_form">Application Acknowledgement / Form</option>
                                <option value="dcr_certificate">DCR Certificate</option>
                                <option value="other_document">Other Document</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-1">
                            <label class="form-label" for="unmatched_document_file">Upload Document</label>
                            <input type="file" class="form-control" name="document_file" id="unmatched_document_file">
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-primary" id="unmatched-create-submit">Create</button>
                </div>
            </form>
        </div>
    </div>
</div>
