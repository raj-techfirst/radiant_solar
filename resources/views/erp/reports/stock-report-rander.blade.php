<!DOCTYPE html>
<html lang="en">

<head>
     <meta charset="UTF-8">
     <meta name="viewport" content="width=device-width, initial-scale=1.0">
     <title>Stock Report</title>
     <style>
          @page {
               margin: 15px;
          }

          html body {
               font-family: Roboto, 'Segoe UI', Tahoma, sans-serif;
               font-size: 83%;
               line-height: 1.1rem;
               margin: 0px;
               padding: 0px;
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
               padding: 3px;
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
               border-bottom: 1px solid #000;

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

          .page-break {
               page-break-after: always;
          }
     </style>
</head>

<body>
     @if(count($ndata) > 0)
     @php $i = 0; @endphp
     @foreach($ndata as $nkey => $nvalue)
     @php $i++; @endphp
     <h3>Warehouse Wise Stock Report</h3>
     <table style="margin-bottom: 15px;">
          <tr>
               <td style="border-bottom: 1px solid #000;width: 80%;border-right: 1px solid #000;border-spacing: 0;">
                    <table id="estimate" style="border:none">
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
                              <td>Address : {!! env('APP_OWNER_ADDRESS') !!} </td>
                         </tr>
                         <tr>
                              <td>GSTIN : {{ env('APP_OWNER_GST') }}</td>
                         </tr>
                    </table>
               </td>
               <td style="width: 20%;padding:3px;vertical-align: middle;text-align:center;border-bottom: 1px solid #000;border-spacing: 0;">
                    <img src="{{ public_path('img/logo.png') }}" class="w-100" /><br /><br /> Date : {{ date('d-m-Y') }}
               </td>
          </tr>
     </table>
     <table class="table table-hover table-bordered" id="estimate_item">
          <thead>
               <tr>
                    <td style="padding:10px !important;"><b style="text-align: left;font-size:18px;">Warehouse : {{ $nvalue['name'] ?? '' }}<b></td>
               </tr>
          </thead>
     </table>
     <table class="table table-hover table-bordered" id="estimate_item">
          <thead>
               <tr>
                    <th>#</th>
                    <th>Product</th>
                    <th>Unit</th>
                    <th>Qty</th>
                    <th>Total Value</th>
                    <th>GST Amount</th>
                    <th>Total Amount</th>
               </tr>
          </thead>
          <tbody>
               @php $c = $cat = ''; $total_value = $total_gst = $total = 0; @endphp
               @foreach($nvalue as $catkey => $catvalue)
               @foreach($catvalue['data'] as $key => $value)
               @php $c++; if($value->type == 'ItemGroup'){ $ncat = $value->group_type; } else{ $ncat = $value->category_name; } @endphp
               @if($cat != $ncat)
               <tr>
                    <td colspan="7" class="bg-light-warning">
                         <h3 style="text-align: left;padding:5px;font-weight: 600;margin: 0px;background-color:#ef7f1bad;">
                              <b>{{ ucfirst($ncat) }}</b>
                         </h3>
                    </td>
               </tr>
               @endif
               <tr>
                    <td>{{$c}}</td>
                    <td>
                         @if ($value->item_id != 0)
                         {{ $value->item_code . ' ' . $value->name }}
                         @else
                         @if ($value->group_type == "panel")
                         {{ $value->penal_watt . 'W Solar Module (' . $value->penal_company . ' - ' . $value->penal_type . ' | '. $value->p_type .')' }}
                         @else
                         {{ $value->inveter_kw . ' KW Inverter (' . $value->invarter_name  . ' | ' . $value->inverter_type . ')' }}
                         @endif
                         @endif
                    </td>
                    <td style="text-align: center;">{{$value->unit_name}}</td>
                    <td style="text-align: center;">{{number_format($value->quantity,2)}}</td>
                    <td style="text-align: right;">{{number_format($value->total_value,2)}}</td>
                    <td style="text-align: right;">{{number_format($value->gst_amount,2)}}</td>
                    <td style="text-align: right;">{{number_format($value->total_amount,2)}}</td>
               </tr>
               @php
               $total_value += $value->total_value;
               $total_gst += $value->gst_amount;
               $total += $value->total_amount;

               $cat = $ncat;
               @endphp
               @endforeach

               @endforeach
               <tr>
                    <th colspan="4" style="text-align:right;"><b>Total</b></th>
                    <th style="text-align:right;">{{number_format($total_value,2) }}</th>
                    <th style="text-align:right;">{{number_format($total_gst,2) }}</th>
                    <th style="text-align:right;">{{number_format($total,2) }}</th>
               </tr>
          </tbody>
     </table>


     @if(count($ndata) != $i)
     <div class="page-break"></div>
     @endif


     @endforeach
     @else
     <h4>No Data!</h4>
     @endif



</body>

</html>