<div class="modal fade" id="updateRecordModal" tabindex="-1" aria-labelledby="updateRecordModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-transparent border-bottom">
                <h4 class="modal-title mb-0" id="updateRecordModalTitle">Update Records</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="update-record-form" action="{{ route('sales-master-update-records') }}" method="POST">
                @csrf
                <div class="modal-body p-2">
                    <div class="d-flex flex-wrap gap-3 mb-2">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="update_mode" id="update_mode_default"
                                value="default" checked>
                            <label class="form-check-label" for="update_mode_default">Default Update Field</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="update_mode" id="update_mode_recommend"
                                value="recommend">
                            <label class="form-check-label" for="update_mode_recommend">Recommend Field</label>
                        </div>
                    </div>

                    <div id="default-update-fields">
                        <div class="d-flex flex-wrap gap-1 mb-2">
                            @foreach ([
                                'Registration Date',
                                'Registration Number',
                                'Address',
                                'District',
                                'Contracted Load',
                                'Register KW',
                                'Application Submitted At',
                                'Loan Portal',
                                'Loan Sanction Date',
                                'Feasibility Date',
                                'Meter Application Date',
                                'Subsidy Request Date',
                                'Subsidy Verify Date',
                                'Subsidy Disbursal Date',
                            ] as $field)
                                <span class="badge bg-light-primary border text-primary fw-normal">{{ $field }}</span>
                            @endforeach
                        </div>
                    </div>

                    <div id="recommend-update-fields" class="d-none mb-2">
                        <div class="small fw-bold mb-1">Recommend Field</div>
                        <div class="d-flex flex-wrap gap-2">
                            @foreach ([
                                'consumer_name' => 'Consumer Name',
                                'discom_name' => 'DISCOM',
                                'circle_name' => 'Circle',
                                'division_name' => 'Division',
                                'sub_division_name' => 'Sub Division',
                            ] as $value => $label)
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input recommend-field-checkbox" type="checkbox"
                                        name="recommended_fields[]" id="recommend_{{ $value }}" value="{{ $value }}">
                                    <label class="form-check-label" for="recommend_{{ $value }}">{{ $label }}</label>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="small text-muted mb-2">Selected records will be updated from the uploaded preview file.</div>
                    <div class="text-danger fw-bold mt-1 d-none" id="update-record-error" style="font-size:1.05rem;"></div>
                    <div id="update-record-summary" class="d-none"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-primary" id="update-record-submit">Confirm Update</button>
                </div>
            </form>
        </div>
    </div>
</div>
