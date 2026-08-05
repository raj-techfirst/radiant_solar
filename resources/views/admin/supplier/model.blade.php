<!--Start Model Card Open-->
<div class="modal-header bg-transparent border-bottom">
    <h4 class="text-center mb-0" id="detailModalTitle">Supplier <small> ( Name : {{$supplier->name}} | Mobile : {{$supplier->mobile}} )</small> </h4>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body p-1" id="body">
    <div class="row">
        @if($supplier->email != "" && $supplier->email != NULL)
        <div class="col-12 col-md-6 col-lg-6">
            <ul class="p-0 m-0">
                <li class="d-flex pb-1 align-items-center">
                    <div class="avatar bg-light-success me-50 rounded p-1">
                        <i data-feather='mail'></i>
                    </div>
                    <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                        <div class="me-2">
                            <small class=" d-block">Email</small>
                            <h6 class="mb-0">{{$supplier->email}}</h6>
                        </div>
                    </div>
                </li>
            </ul>
        </div>
        @endif

        @if($supplier->tax_number != "" && $supplier->tax_number != NULL)
        <div class="col-12 col-md-6 col-lg-6">
            <ul class="p-0 m-0">
                <li class="d-flex pb-1 align-items-center">
                    <div class="avatar bg-light-warning me-50 rounded p-1">
                        <i data-feather='minimize'></i>
                    </div>
                    <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                        <div class="me-2">
                            <small class=" d-block">Tax Number</small>
                            <h6 class="mb-0">{{$supplier->tax_number}}</h6>
                        </div>
                    </div>
                </li>
            </ul>
        </div>
        @endif

        @if($supplier->contact_person != "" && $supplier->contact_person != NULL)
        <div class="col-12 col-md-6 col-lg-6">
            <ul class="p-0 m-0">
                <li class="d-flex pb-1 align-items-center">
                    <div class="avatar bg-light-danger me-50 rounded p-1">
                    <i data-feather='user'></i>
                    </div>
                    <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                        <div class="me-2">
                            <small class=" d-block">Contact Person</small>
                            <h6 class="mb-0">{{$supplier->contact_person}}</h6>
                        </div>
                    </div>
                </li>
            </ul>
        </div>
        @endif

        @if($supplier->contact_person_number != "" && $supplier->contact_person_number != NULL)
        <div class="col-12 col-md-6 col-lg-6">
            <ul class="p-0 m-0">
                <li class="d-flex pb-1 align-items-center">
                    <div class="avatar bg-light-info me-50 rounded p-1">
                        <i data-feather='phone-call'></i>
                    </div>
                    <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                        <div class="me-2">
                            <small class=" d-block">Contact Person Number</small>
                            <h6 class="mb-0">{{$supplier->contact_person_number}}</h6>
                        </div>
                    </div>
                </li>
            </ul>
        </div>
        @endif

        @if($supplier->address != "" && $supplier->address != NULL)
        <div class="col-12 col-md-12 col-lg-12">
            <ul class="p-0 m-0">
                <li class="d-flex pb-1 align-items-center">
                    <div class="avatar bg-light-primary me-50 rounded p-1">
                    <i data-feather='map-pin'></i>
                    </div>
                    <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                        <div class="me-2">
                            <small class=" d-block">Address</small>
                            <h6 class="mb-0">{{$supplier->address}}</h6>
                        </div>
                    </div>
                </li>
            </ul>
        </div>
        @endif

    </div>
</div>
<!--End Model Card Open-->