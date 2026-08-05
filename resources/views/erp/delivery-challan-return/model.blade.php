<!--Start PO show details model Open-->
<div class="modal-header bg-transparent border-bottom">
    <h4 class="text-center mb-0" id="detailModalTitle">Details</h4>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>

<div class="modal-body p-1">
    <div class="table-responsive">

        @php if ($qry->issue_type == "project") {
        $dis_name = $qry->project->consumer_name;
        } else {
        $dis_name = '(Ins) '. $qry->installer->name . ' ' . $qry->installer->last_name;
        }
        @endphp

        <table class="table">
            <tr>
                <td><b>Challan No.</b> : {{$qry->challan_number}}</td>
                <td><b>Project / Installer </b> : {{ $dis_name }}</td>
            </tr>
            <tr>
                <td><b>Challan Date.</b> : {{ date('d-m-Y',strtotime($qry->created_at))}}</td>
                <td><b>Warehouse</b> : {{$qry->warehouse->name}}</td>
            </tr>
        </table>
        <table id="view_table" class="datatables-basic table table-hover">
            <thead>
                <th>#</th>
                <th>Item</th>
                <th>Qty.</th>
                <th>Unit</th>
            </thead>
            <tbody>

                @foreach ($qry->delivery_challan_return_meta as $key => $pom)
                <tr>
                    <td>{{$key+1}}</td>
                    @if($pom->type == "Item")
                    <td class="text-nowrap">{{$pom->item->name}}</td>
                    <td>{{$pom->quantity}}</td>
                    <td>{{$pom->item->unit->unit_name}}</td>
                    @else
                    <td class="text-nowrap">{{ getItemGropName($pom,1) }}</td>
                    <td>{{$pom->quantity}}</td>
                    <td>{{$pom->itemGroup->unit->unit_name}}</td>
                    @endif
                </tr>

                @endforeach
            </tbody>
        </table>
    </div>
</div>
<!--Start PO show details model Open-->