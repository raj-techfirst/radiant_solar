<div class="modal fade" id="updateRecordModal" tabindex="-1" aria-labelledby="updateRecordModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-transparent border-bottom">
                <h4 class="modal-title mb-0" id="updateRecordModalTitle">Default Update Field</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="update-record-form" action="{{ route('sales-master-update-records') }}" method="POST">
                @csrf
                <div class="modal-body p-2">
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

                    <div class="small text-muted mb-2">Selected records will be updated from the uploaded preview file.</div>
                    <div class="text-danger small mt-1 d-none" id="update-record-error"></div>
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
