<!--Start PO show details model Open-->
<div class="modal-header bg-transparent border-bottom">
    <h4 class="text-center mb-0" id="detailModalTitle">Details <small>(Warehouse : {{$qry->warehouse->name}} | Supplier : {{$qry->supplier->name}} | GRN No. : {{$qry->grn_number}})</small> </h4>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>

<div class="modal-body p-0">
    <div class="table-responsive">
        <table class="datatables-basic table table-hover table-sm">
            <thead>
                <th>#</th>
                <th>Item Name</th>
                <th>Quantity</th>
                <th>Price</th>
                <th>Total</th>
            </thead>
            <tbody>
                @php $i=1; @endphp
                @foreach ($qry->purchase_direct_meta as $pom)
                <tr>
                    <td>{{$i}}</td>
                    <td class="text-nowrap">{{$pom->item->name}}</td>
                    <td>{{$pom->quantity}}</td>
                    <td>{{$pom->price}}</td>
                    <td>{{$pom->total}}</td>
                </tr>
                @php $i++ @endphp
                @endforeach
            </tbody>
        </table>
    </div>
</div>
<!--Start PO show details model Open-->