<!--Start Model Card Open-->
<div class="modal-header bg-transparent border-bottom">
    <h4 class="text-center mb-0" id="detailModalTitle">Stock History | {{ $warehouseStock->warehouse->name }} |
        @php
        $display_name = '';
        if ($warehouseStock->type == "Item") {
        $display_name = $warehouseStock->item->name;
        } else {
        $display_name = getItemGropName($warehouseStock,1);
        }
        @endphp
        {{ $display_name }} | Stock : {{ $warehouseStock->quantity }}
    </h4>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body p-1" id="body">
    <table id="viewData" class="datatables-basic table table-hover">
        <thead>
            <tr>
                <th>#</th>
                <th>Date</th>
                <th class="text-nowrap">Doc. No.</th>
                <th class="text-nowrap">Doc. Type</th>
                <th class="text-nowrap">Party Name</th>
                <th class="">Party No.</th>
                <!-- <th class="">Remark</th> -->
                <th class="">In</th>
                <th class="">Out</th>
                <th class="">Closing Balance</th>
            </tr>
        </thead>
        <tbody>
            @php $balance = 0; @endphp
            @foreach($warehouseStock->warehouse_stock_history as $key => $item)
            <tr>
                @if($item->type == 'Credit')
                @php $balance += $item->quantity; @endphp
                @else
                @php $balance -= $item->quantity; @endphp
                @endif
                <td>{{ $key+1 }}</td>
                <td class="text-nowrap">{{ (!is_null($item->created_at) && $item->created_at != '1970-01-01') ? date('d-m-Y',strtotime($item->created_at)) : '' }}</td>
                @php
                $doc_no = $party_name = $party_no ='-';
                if($item->purchase_direct_meta_id != 0 && !empty($item->purchase_direct_meta->purchase_direct)){
                    $doc_no = $item->purchase_direct_meta->purchase_direct->grn_number ?? "";
                    $party_name = $item->purchase_direct_meta->purchase_direct->supplier->name ?? "";
                    $party_no = $item->purchase_direct_meta->purchase_direct->supplier_number ?? "";
                } elseif($item->delivery_challan_meta_id != 0 && !empty($item->delivery_challan_meta->delivery_challan)) {
                    $doc_no = $item->delivery_challan_meta->delivery_challan->challan_number ?? "";
                    if($item->delivery_challan_meta->delivery_challan->issue_type == "trading")
                    {
                        $party_name = $item->delivery_challan_meta->delivery_challan->salesQuatation->name ?? "";
                        $party_no = "";
                    }
                    else if($item->delivery_challan_meta->delivery_challan->issue_type == "project"){
                        $party_name = $item->delivery_challan_meta->delivery_challan->project->consumer_name ?? "";
                        $party_no = $item->delivery_challan_meta->delivery_challan->project->consumer_number ?? "";
                    }
                    else if($item->delivery_challan_meta->delivery_challan->issue_type == "installer"){
                        $party_name_first = $item->delivery_challan_meta->delivery_challan->installer->name ?? "";
                        $party_name_last = $item->delivery_challan_meta->delivery_challan->installer->name ?? "";
                        $party_name = $party_name_first . ' '.$party_name_last;
                        $party_no = "";
                    }
                    else if($item->delivery_challan_meta->delivery_challan->issue_type == "warehouse"){
                        $party_name = $item->delivery_challan_meta->delivery_challan->warehouse_from->name ?? "";
                        $party_no = "";
                    }
                }
                @endphp
                <td>{{ $doc_no }}</td>
                <td class="text-nowrap">{{ $item->stock_type }}</td>
                <td class="text-nowrap">{{ $party_name }}</td>
                <td>{{ $party_no }}</td>
                <!-- <td>{{ $item->remark }}</td> -->
                @if($item->type == 'Credit')
                <td>{{ $item->quantity }}</td>
                <td>-</td>
                @else
                <td>-</td>
                <td>{{ $item->quantity }}</td>
                @endif
                <td>{{ $balance }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
<!--End Model Card Open-->