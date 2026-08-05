<form id="formReceive" class="form p-0" action="javascript:void(0);" method="post">
    @csrf
    <div class="modal-header bg-transparent border-bottom">
        <h6 class="mb-0"> <small class="h4">Goods Receive Note</small>
            <small class="ms-2 me-2 badge bg-success">Supplier: {{$purchaseOrder->supplier->name}}</small>
            <small class="badge bg-primary">PO Number: {{$purchaseOrder->po_number}}</small>
        </h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>
    <div class="modal-body p-0">
        @if($goods->count() > 0)
        <div class="table-responsive">
            @foreach ($goods as $key => $val)
            <table class="table table-sm">
                <tbody class="text-center">
                    <tr class="bg-light bg-gradient">
                        <th>
                            GRN No.: {{$val->grn_number}}
                        </th>
                        <th>
                            Bill No: {{$val->invoice_number}}
                        </th>
                        <th>
                            Receive Date: {{ date('d-m-Y h:i A',strtotime($val->created_at)) }}
                        </th>
                    </tr>
                    <tr>
                        <th class="text-start" colspan="3">
                            <b>Remark:</b> {{$val->remark}}
                        </th>
                    </tr>
                </tbody>
            </table>
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>#</th>
                        <th class="text-center text-nowrap">Item</th>
                        <th class="text-center text-nowrap">Receive Qty</th>
                    </tr>
                </thead>
                <tbody class="text-center">
                    @foreach ($val->receiveProducts as $key => $receiveValue)
                    <tr class="mb-1">
                        <td class="text-start" width="5%">
                            <b class="sr_no">{{$key+1}}</b>
                        </td>
                        <td class="text-center text-nowrap">
                            {{$receiveValue->product->name}}
                        </td>
                        <td class="text-center">
                            {{ $receiveValue->receive_qty }} {{$receiveValue->name}}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @endforeach
        </div>
        @endif

        @if($purchaseOrder->status != 'Manualy Close' && $purchaseOrder->status != 'Receive')
        @if(count($purchaseOrderMeta) > 0)
        <div class="table-responsive">
            <table class="table table-sm">
                <thead>
                    <tr class="bg-info">
                        <th>#</th>
                        <th class="text-center text-nowrap">Item</th>
                        <th class="text-center text-nowrap">Quantity</th>
                        <th class="text-center text-nowrap">Receive</th>
                        <th class="text-center text-nowrap">Remaining</th>
                    </tr>
                </thead>
                <tbody class="text-center">
                    @php $i=1; @endphp
                    @foreach ($purchaseOrderMeta as $po)
                    <tr>
                        <td class="text-start" width="5%">
                            <b class="sr_no">{{$i}}</b>
                        </td>
                        <td class="custom-input-group text-center text-nowrap">
                            {{$po->product->name}}
                            <input type="hidden" name="meta_id[]" value="{{$po->id}}">
                        </td>
                        <td class="custom-input-group text-center">
                            <div class="input-group">
                                <input type="number" class="form-control quantity" name="quantity[]" value="{{$po->quantity}}" readonly>
                                <span class="input-group-text unit_type">{{$po->unit->unit_name}}</span>
                            </div>
                        </td>
                        <td class="custom-input-group text-center">
                            <div class="input-group d-inline-flex">
                                <input type="number" min="0" class="form-control receive_qty" name="receive_qty[]" value="0" />
                            </div>
                        </td>
                        <td class="custom-input-group">
                            <input type="hidden" class="premaining_quty" name="premaining[]" @if(isset($po->remaining_qty)) value="{{ $po->remaining_qty }}" @else value="0" @endif readonly>
                            <input type="number" class="form-control remaining_quty" name="remaining[]" @if(isset($po->remaining_qty)) value="{{ $po->remaining_qty }}" @endif readonly>
                        </td>
                    </tr>
                    @php $i++; @endphp
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="row px-1">
            <div class="col-12 col-sm-12 col-md-4 col-lg-4 form-group custom-input-group">
                <label class="form-label" for="invoice_number"><b>Bill Number</b> <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="invoice_number" id="invoice_number" placeholder="Enter invoice number">
                <span class="invalid-feedback d-block" id="error_invoice_number" role="alert"></span>
            </div>

            <div class="col-12 col-sm-12 col-md-8 col-8-12 form-group custom-input-group">
                <label class="form-label" for="remark"><b>Remark</b> </label>
                <textarea class="form-control" name="remark" id="remark" placeholder="Enter remark"></textarea>
                <span class="invalid-feedback d-block" id="error_remark" role="alert"></span>
            </div>
        </div>

        <div class="row px-1">
            <div class="col-12 text-end p-1">
                <button type="button" class="btn btn-sm btn-gradient-primary save-receive">Submit</button>
            </div>
        </div>
        @endif
        @endif
    </div>
</form>