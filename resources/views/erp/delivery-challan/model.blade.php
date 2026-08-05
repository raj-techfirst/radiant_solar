<!--Start PO show details model Open-->
<div class="modal-header bg-transparent border-bottom">
    <h4 class="text-center mb-0" id="detailModalTitle">Details</h4>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>

<div class="modal-body p-1">
    <div class="table-responsive">
        @php if ($qry->issue_type == "project") {
        $type = 'Project';
        $dis_name = $qry->project->consumer_name;
        } else if ($qry->issue_type == "warehouse") {
        $type = 'Warehouse From';
        $dis_name = $qry->warehouse_from->name;
        } else if ($qry->issue_type == "trading") {
        $type = 'B2B';
        $dis_name = $qry->salesQuatation->name ?? '';
        }
        else {
        $type = 'Installer';
        $dis_name = $qry->installer->name . ' ' . $qry->installer->last_name;
        }
        @endphp
        <table class="table table-sm">
            <tr>
                <td><b>Challan No.</b> : {{$qry->challan_number}}</td>
                <td><b>Warehouse To </b> : {{$qry->warehouse->name}}</td>
            </tr>
            <tr>
                <td><b>Challan Date.</b> : {{ date('d-m-Y',strtotime($qry->created_at))}}</td>
                <td><b>{{ $type }}</b> : {{ $dis_name }}</td>
            </tr>
        </table>
        <table id="view_table" class="datatables-basic table table-hover">
            <thead>
                <th>#</th>
                <th>Item</th>
                <th>Qty.</th>
                <th>Unit</th>
                <th class="d-none">GST</th>
                <th class="d-none">Rate</th>
                <th class="d-none">GST Amt.</th>
                <th class="d-none">Amount</th>
            </thead>
            <tbody>
                @php
                $i=1;
                $tgst = $tamt = 0;
                @endphp
                @foreach ($qry->delivery_challan_meta as $pom)
                <tr>
                    <td>{{$i}}</td>

                    @if($pom->type == "Item")

                    <td class="text-nowrap">{{$pom->item->name}}</td>
                    <td>{{$pom->quantity}}</td>
                    <td>{{$pom->item->unit->unit_name}}</td>
                    <td class="d-none">{{$pom->item->gst_rate}}</td>

                    @else

                    <td class="text-nowrap">{{ getItemGropName($pom,1) }}</td>
                    <td>{{$pom->quantity}}</td>
                    <td>{{$pom->itemGroup->unit->unit_name}}</td>
                    <td class="d-none">{{$pom->itemGroup->gst_rate}}</td>
                    @endif
                    <td class="d-none">{{$pom->rate}}</td>
                    <td class="d-none">{{$pom->gst_amount}}</td>
                    <td class="d-none">{{$pom->amount}}</td>
                </tr>
                @php
                $i++;
                $tgst += $pom->gst_amount;
                $tamt += $pom->amount;
                @endphp
                @endforeach
            </tbody>
            <tfoot class="d-none">
                <tr>
                    <td colspan="5"></td>
                    <td><b>Sub Total</b></td>
                    <td><b>{{ number_format($tgst,2)}}</b></td>
                    <td><b>{{ number_format($tamt,2)}}</b></td>
                </tr>
                <tr>
                    <td colspan="6"></td>
                    <td><b>CGST</b></td>
                    <td><b>{{ number_format($tgst/2,2)}}</b></td>
                </tr>
                <tr>
                    <td colspan="6"></td>
                    <td><b>SGST</b></td>
                    <td><b>{{ number_format($tgst/2,2)}}</b></td>
                </tr>
                <tr>
                    <td colspan="6"></td>
                    <td><b>Total</b></td>
                    <td><b>{{ number_format($tgst+$tamt,2)}}</b></td>
                </tr>
            </tfoot>
        </table>

        @if($type == 'Installer')

        @php $salesOrders = getSalesOrderUsingIds($qry->sales_master_id); @endphp
        @if(count($salesOrders) > 0)
        <br /><br />
        @foreach($salesOrders as $k => $v)
        <table class="table table-bordered table-hover table-sm mb-0 pb-0">
            <thead class="bg-light">
                <tr>
                    <td >
                        <b>Consumer Name : </b> {{ $v->consumer_name  }}
                    </td>
                    <td style="width:180px;text-align:center;">
                        <b>Mobile : </b> {{ $v->contact_number }}
                    </td>
                    <td style="width:100px;text-align:center;">
                        <b>K/W : </b> {{ $v->register_kw  }}
                    </td>
                </tr>
                <tr>
                    <td colspan="3">
                        <b>Address : </b> {{ $v->address }}
                    </td>
                </tr>
            </thead>
        </table>
        <table class="table table-bordered table-hover table-sm">
            <tr>
                <th style="width: 5%; text-align: center; ">#</th>
                <th style="text-align: center;">Item</th>
                <th style="width: 15%; text-align: center;">Qty.</th>
            </tr>
            <tr>
                <td style="text-align: center;">1</td>
                <td>{{ $v->salesquatationfull->penalWatt->name }}W Solar Module ({{ $v->panel->name }} - {{$v->salesquatationfull->penalType->name}})</td>
                <td style="text-align: center;">{{ $v->salesquatationfull->penal_nos }} Nos</td>
            </tr>
            <tr>
                <td style="text-align: center;">2</td>
                <td>{{ $v->salesquatationfull->inveter_capacity }} KW Inverter ({{ $v->inveter->name }})</td>
                <td style="text-align: center;">{{ $v->salesquatationfull->no_of_inveter }} Nos</td>
            </tr>
        </table>
        @endforeach
        @endif
        @endif
    </div>
</div>
<!--Start PO show details model Open-->