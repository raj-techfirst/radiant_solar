<!--Start Model Card Open-->
<div class="modal-header bg-transparent border-bottom">
    <h4 class="text-center mb-0" id="detailModalTitle">Project Stock History</h4>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body p-1">

    @php if ($projectWiseStock->issue_type == "project") {
    $dis_name = $projectWiseStock->project->consumer_name;
    } else {
    $dis_name = '(Ins) '. $projectWiseStock->installer->name . ' ' . $projectWiseStock->installer->last_name;
    }
    @endphp

    @php
    $display_name = '';
    if ($projectWiseStock->type == "Item") {
    $display_name = $projectWiseStock->item->name;
    } else {
        $display_name = getItemGropName($projectWiseStock,1);
    }
    @endphp
    <table class="table">
        <tr>
            <td><b>Project / Installer </b> : {{ $dis_name }}</td>

            <td><b>Item </b> : {{ $display_name }} (<b>Current Stock</b> : {{ $projectWiseStock->quantity }} )</td>
        </tr>
    </table>

    <table id="history_table" class="datatables-basic table table-hover">
        <thead>
            <tr>
                <th>#</th>
                <th>Date</th>
                <th>Doc. No.</th>
                <th>Doc. Type</th>
                <th>Remark</th>
                <th>In</th>
                <th>Out</th>
                <th>Closing Balance</th>
            </tr>
        </thead>
        <tbody>
            @php $balance = 0; @endphp
            @foreach($projectWiseStock->project_wise_history as $key => $item)
            <tr>
                @if($item->type == 'Credit')
                @php $balance += $item->quantity; @endphp
                @else
                @php $balance -= $item->quantity; @endphp
                @endif
                <td>{{ $key+1 }}</td>
                <td class="text-nowrap">{{ (!is_null($item->created_at) && $item->created_at != '1970-01-01') ? date('d-m-Y',strtotime($item->created_at)) : '' }}</td>
                <td>
                    {{ ($item->delivery_challan_meta_id != 0) ? $item->delivery_challan_meta->delivery_challan->challan_number : '-' }}
                </td>
                <td>
                    @if($item->delivery_challan_meta_id != 0)
                        Delivery Challan
                    @elseif($item->delivery_challan_return_meta_id != 0)
                        Delivery Challan Return
                    @elseif($item->installation_id != 0)
                        Installation
                    @else
                        Stock Adjustment
                    @endif
                </td>
                <td>{{ $item->remark }}</td>
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