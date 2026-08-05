<!--Start PO show details model Open-->
<div class="modal-header bg-transparent border-bottom">
    <div class="row g-50 w-100">
       
            <h4 class="mb-0"> #{{$purchaseOrder->po_number}} | Supplier: {{$purchaseOrder->supplier->name}}</h4>
      
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body p-1">
    <div class="table-responsive">
    <table  id="estimate_item" class="table table-sm table-bordered table-hover">
          <thead class="bg-head">
               <tr>
                    <th style="width:20px;">#</th>
                    <th style="width:calc(100%-320px);">Item</th>
                    <th class="text-end" style="width:80px;">Qty.</th>
                    <th class="text-end" style="width:50px;">GST(%)</th>
                    <th class="text-end" style="width:70px;">Price</th>
                    <th class="text-end" style="width:70px;">GST Amt.</th>
                    <th class="text-end" style="width:70px;">Total</th>
               </tr>
          </thead>
          <tbody>
               @php $total = $totalGST = 0; @endphp
               @foreach($purchaseOrderMeta as $key => $pom)
               @php
               $total += $pom->total;
               $totalGST += $pom->gst_amount;
               @endphp
               <tr>
                    <td>{{$key+1}}</td>
                    @if($pom->type == "Item")
                    <td class="text-nowrap" >
                         {{$pom->product->name}} {!! ($pom->remarks != '') ? '<br />'.$pom->remarks : '' !!}
                    </td>
                    @else
                    <td class="text-nowrap" >{{ getItemGropName($pom,1) }} {!! ($pom->remarks != '') ? '<br />'.$pom->remarks : '' !!}</td>
                    @endif
                    <td class="text-end">{{$pom->quantity}} {{$pom->unit->unit_name}}</td>
                    <td class="text-end">{{$pom->gst_tax}}</td>
                    <td class="text-end"> {{$pom->price}}</td>
                    <td class="text-end"> {{$pom->gst_amount}}</td>
                    <td class="text-end">{{$pom->total}}</td>
               </tr>
               @endforeach
                    <tr>
                         <th colspan="4" >Total Amount In Words:</th>
                         <th >Sub Total</th>
                         <th class="text-end">{{ number_format($totalGST,2) }}</th>
                         <th class="text-end">{{ number_format($total,2) }}</th>
                    </tr>
                    <tr>
                         <td colspan="5" rowspan="4" style="vertical-align: top;">
                              {{ ucwords(convertNumberToWords(round($totalGST+$total))) }}
                              <br /><br />
                              Remark : {{$purchaseOrder->remark}}
                         </td>
                         <th class="text-end">CGST</th>
                         <th class="text-end">{{ number_format(($totalGST/2),2) }}</th>
                    </tr>
                    <tr>
                         <th class="text-end">SGST</th>
                         <th class="text-end">{{ number_format(($totalGST/2),2) }}</th>
                    </tr>
                    <tr>
                         <th class="text-end">Roundoff</th>
                         <th class="text-end">{{ number_format((round($totalGST+$total) - ($totalGST+$total)),2) }}</th>
                    </tr>
                    <tr>
                         <th class="text-end">Total </th>
                         <th class="text-end">{{ number_format(round($totalGST+$total),2) }}</th>
                    </tr>
          </tbody>
     </table>
    </div>
</div>
<!--Start PO show details model Open-->