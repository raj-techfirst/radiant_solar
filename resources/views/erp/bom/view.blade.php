<!--Start show details model Open-->
<div class="modal-header bg-transparent border-bottom">
    <div class="row g-50 w-100">
        <div class="col-12 col-lg-5 col-xl-4">
            <h4 class="mb-0">View BOM</h4>
        </div>
        <div class="col-12 col-lg-3">
            <span class="badge bg-success">{{$bOM->bom_name}}</span>
        </div>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body p-1">
    <div class="table-responsive">
        <table class="datatables-basic table table-hover table-sm">
            <thead>
                <th>#</th>
                <th>Product Name</th>
                <th>Quantity</th>
            </thead>
            <tbody>
                @foreach ($bOMMeta as $key => $pom)
                <tr>
                    <td>{{$key+1}}</td>
                    @if($pom->type == "Item")
                    <td class="text-nowrap">{{$pom->product->name}}</td>
                    @else
                    <td class="text-nowrap">{{getItemGropName($pom,1)}}</td>
                    @endif
                    <td>{{$pom->quantity}} {{$pom->unit->unit_name}}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
<!--Start show details model Open-->