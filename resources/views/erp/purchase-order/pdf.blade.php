<!DOCTYPE html>
<html>

<head>
     <title>{{$title}}</title>
     <style>
          @page {
               margin: 20px;
          }

          html body {
               font-family: Roboto, 'Segoe UI', Tahoma, sans-serif;
               font-size: 12px;
               line-height: 1.1rem;
          }

          h3 {
               text-align: center;
          }

          table {
               width: 100%;
               border: 1px solid #000;
               border-spacing: 0;
          }

          #estimate {
               text-align: left !important;
               padding-left: 2px;
          }

          #detail {
               text-align: left !important;
               padding-right: 2px;
               vertical-align: top !important;
          }

          #estimate_item .bg-head {
               background-color: #d7d7d7;
               border: none;
          }

          #estimate_item td,
          #estimate_item th {
               padding: 2px;
          }

          #estimate_item th {
               border-bottom: 1px solid #000;
               border-spacing: 0;
          }

          /*  #estimate_item td:not(:last-child),#estimate_item th:not(:last-child) */
          #estimate_item td,
          #estimate_item th {
               border-right: 1px solid #000;
               border-spacing: -2;
          }

          a {
               text-decoration: none;
               color: #000;
          }

          footer {
               position: fixed;
               bottom: 0px;
               left: 0px;
               right: 0px;
               height: 120px;
          }
     </style>
</head>

<body>
     <h3>Purchase Order</h3>
     <table>
          <tr>
               <td style="border-right: 1px solid #000;border-spacing: 0;width: 50%;">
                    <table style="border:none;">
                         <tr>
                              <td>P.O.Infavour of : <br /> {{$purchaseOrder->supplier->name}}</td>
                         </tr>
                         @if($purchaseOrder->supplier->address != '')
                         <tr>
                              <td>Address : {{$purchaseOrder->supplier->address}}</td>
                         </tr>
                         @endif
                         <tr>
                              <td>Mobile : {{$purchaseOrder->supplier->mobile}}</td>
                         </tr>
                         @if($purchaseOrder->supplier->email != '')
                         <tr>
                              <td>Email : {{$purchaseOrder->supplier->email}}</td>
                         </tr>
                         @endif
                         @if($purchaseOrder->supplier->gst_number != '')
                         <tr>
                              <td>GSTIN : {{$purchaseOrder->supplier->gst_number}}</td>
                         </tr>
                         @endif
                    </table>
               </td>
               <td style="width: 25%;border-right: 1px solid #000;border-spacing: 0;padding:3px;vertical-align: top;">
                    P.O. No : {{$purchaseOrder->po_number}}<br />
                    P.O. Date : {{ date("d-m-Y", strtotime($purchaseOrder->purchase_date))}}
               </td>
               <td style="width: 25%;padding:3px;vertical-align: top;text-align:center">
                    <img src="{{ public_path('img/logo.png') }}" class="w-100" />
               </td>
          </tr>
          <tr>
               <td style="border-top: 1px solid #000;width: 50%;border-right: 1px solid #000;border-spacing: 0;">
                    <table id="estimate" style="border:none">
                         <tr>
                              <td>Invoice To:</td>
                         </tr>
                         <tr>
                              <td>Name : {{ env('APP_NAME') }}</td>
                         </tr>
                         <tr>
                              <td>Mobile : {{ env('APP_OWNER_MOBILE') }}</td>
                         </tr>
                         <tr>
                              <td>Email : {{ env('APP_OWNER_EMAIL') }}</td>
                         </tr>
                         <tr>
                              <td>Address : {{ env('APP_OWNER_ADDRESS') }} </td>
                         </tr>
                         <tr>
                              <td>GSTIN : {{ env('APP_OWNER_GST') }}</td>
                         </tr>
                    </table>
               </td>
               <td colspan="2" style="border-top: 1px solid #000;width: 50%;">
                    <table id="estimate" style="border:none">
                         <tr>
                              <td>Ship To:</td>
                         </tr>
                         <tr>
                              <td>Name : {{ $purchaseOrder->shipping_name }}</td>
                         </tr>
                         <tr>
                              <td>Mobile : {{ $purchaseOrder->shipping_mobile }}</td>
                         </tr>
                         <tr>
                              <td>Email : {{ $purchaseOrder->shipping_email }}</td>
                         </tr>
                         <tr>
                              <td>Address : {{ $purchaseOrder->shipping_address }} </td>
                         </tr>
                         <tr>
                              <td>GSTIN : {{ $purchaseOrder->shipping_gst }}</td>
                         </tr>
                    </table>
               </td>
          </tr>
     </table>
     <table style="border-top:0px;" id="estimate_item">
          <thead class="bg-head">
               <tr>
                    <th style="width:20px;">#</th>
                    <th style="width:calc(100%-320px);">Item</th>
                    <th style="text-align: right;padding-right:10px;width:80px;">Qty.</th>
                    <th style="text-align: right;padding-right:10px;width:50px;">GST(%)</th>
                    <th style="text-align: right;padding-right:10px;width:70px;">Price</th>
                    <th style="text-align: right;padding-right:10px;width:70px;">GST Amt.</th>
                    <th style="text-align: right;padding-right:10px;width:70px;">Total</th>
               </tr>
          </thead>
          <tbody style="text-align: center;">
               @php $total = $totalGST = 0; @endphp
               @foreach($purchaseList as $key => $pom)
               @php
               $total += $pom->total;
               $totalGST += $pom->gst_amount;

               @endphp
               <tr>
                    <td style="border-bottom: 1px solid #000;">{{$key+1}}</td>
                    @if($pom->type == "Item")
                    <td class="text-nowrap" style="text-align: left;border-bottom: 1px solid #000;">
                         {{$pom->product->name}} {!! ($pom->remarks != '') ? '<br />'.$pom->remarks : '' !!}

                    </td>
                    @else
                    <td class="text-nowrap" style="text-align: left;border-bottom: 1px solid #000;">{{ getItemGropName($pom,1) }} {!! ($pom->remarks != '') ? '<br />'.$pom->remarks : '' !!}</td>
                    @endif
                    <td style="text-align: right;padding-right:10px;border-bottom: 1px solid #000;">{{$pom->quantity}} {{$pom->unit->unit_name}}</td>
                    <td style="text-align: right;padding-right:10px;border-bottom: 1px solid #000;">{{$pom->gst_tax}}</td>
                    <td style="text-align: right;padding-right:10px;border-bottom: 1px solid #000;"> {{$pom->price}}</td>
                    <td style="text-align: right;padding-right:10px;border-bottom: 1px solid #000;"> {{$pom->gst_amount}}</td>
                    <td style="text-align: right;padding-right:10px;border-bottom: 1px solid #000;">{{$pom->total}}</td>
               </tr>
               @endforeach

               @for($j=$key+2;$j<=12;$j++)
                    <tr>
                    <td style="color:transparent;">{{$j}}<br/>&nbsp;</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    </tr>
                    @endfor
                    <tr>
                         <th colspan="4" style="text-align: left;border-top:1px #000 solid;">Total Amount In Words:</th>
                         <th style="border-top:1px #000 solid;">Sub Total</th>
                         <th style="text-align: right;border-top:1px #000 solid;">{{ number_format($totalGST,2) }}</th>
                         <th style="text-align: right;border-top:1px #000 solid;">{{ number_format($total,2) }}</th>
                    </tr>
                    <tr>
                         <td colspan="5" rowspan="4" style="text-align: left;border-top:1px #000 solid;vertical-align: top;">
                              {{ ucwords(convertNumberToWords(round($totalGST+$total))) }}
                              <br /><br />
                              Remark : {{$purchaseOrder->remark}}
                         </td>
                         <th style="text-align: right;border-top:1px #000 solid">CGST</th>
                         <th style="text-align: right;border-top:1px #000 solid">{{ number_format(($totalGST/2),2) }}</th>
                    </tr>
                    <tr>
                         <th style="text-align: right;border-top:1px #000 solid">SGST</th>
                         <th style="text-align: right;border-top:1px #000 solid;">{{ number_format(($totalGST/2),2) }}</th>
                    </tr>
                    <tr>
                         <th style="text-align: right;border-top:1px #000 solid">Roundoff</th>
                         <th style="text-align: right;border-top:1px #000 solid;">{{ number_format((round($totalGST+$total) - ($totalGST+$total)),2) }}</th>
                    </tr>
                    <tr>
                         <th style="text-align: right;border-top:1px #000 solid">Total </th>
                         <th style="text-align: right;border-top:1px #000 solid">{{ number_format(round($totalGST+$total),2) }}</th>
                    </tr>
          </tbody>
     </table>

     <footer>
          <div style="padding: 0px 5px 0px 5px;">
               <div style="text-align:right;">
                    <p><b>For, {{ env('APP_NAME') }}</b></p>
                    <br />
                    <br />
                    <p><b>(Authorised Signatory)</b></p>
               </div>
               <p style="text-align: center">*This is computer generated document hense authorized signature is not required.</p>
          </div>
     </footer>

</body>

</html>