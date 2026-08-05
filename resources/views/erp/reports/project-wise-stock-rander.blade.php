<!DOCTYPE html>
<html lang="en">

<head>
     <meta charset="UTF-8">
     <meta name="viewport" content="width=device-width, initial-scale=1.0">
     <title>Project Wise Stock Report</title>
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
     </style>
</head>

<body>


     <h3>Project Wise Stock Report</h3>

     <table style="margin-bottom: 15px;">
          <tr>
               <td style="border-bottom: 1px solid #000;width: 70%;border-right: 1px solid #000;border-spacing: 0;">
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
               <td style="width: 30%;padding:3px;vertical-align: middle;text-align:center;border-bottom: 1px solid #000;border-spacing: 0;">
                    <img src="{{ public_path('img/logo.png') }}" class="w-100" /><br /><br /> Date : {{ date('d-m-Y') }}
               </td>

          </tr>

     </table>
     @if(count($data) > 0)

 @foreach($data as $key => $value)
     <table class="table table-hover table-bordered" id="estimate_item" style="margin-bottom: 15px;">

          <tr>
               <th style="text-align: left;">Consumer : {{$value->consumer_number}} - {{$value->consumer_name}}</th>
     <th>K/W {{$value->register_kw}}</th>
     </tr>



     </table>
	 @php break; @endphp
	 @endforeach
     <table class="table table-hover table-bordered" id="estimate_item">

          <thead>

               <tr>
                    <th style="width:30px;">#</th>
                    <th style="min-width:350px;">Item Name</th>
                    <th>Use stock</th>
                    <th>Available stock</th>
                    <th>Total stock</th>
                    <th>Taxabale</th>
                    <th>GST</th>
                    <th>Total</th>
               </tr>
          </thead>
          <tbody>
               @php
               $total_value = $total_gst = $total = 0;
               $i = 0;
               @endphp
               @foreach($data as $key => $value)
               <tr>
                    <td style="text-align: center;">{{$i+1}}</td>
                    <td> @if ($value->item_id != 0)
                         {{ $value->item_code . ' ' . $value->name }}
                         @else
                         
                         @if ($value->group_type == "panel")
                         {{ $value->panel_watt_name . 'W Solar Module (' . $value->penal_name . ' - ' . $value->penal_type_name .' | ' . $value->p_type . ')' }}
                         @else
                         {{ $value->inveter_kw . ' KW Inverter (' . $value->inverter_name  . ' | ' . $value->inverter_type . ')' }}
                         @endif
                         @endif
                    </td>
                    <td style="text-align: center;">{{$value->use_quantity}}</td>
                    <td style="text-align: center;">{{$value->quantity}}</td>
                    <td style="text-align: center;">{{$value->total_qty}}</td>
                    <td style="text-align: right;">{{number_format($value->taxable_amount,2)}}</td>
                    <td style="text-align: right;">{{number_format($value->gst_amount,2)}}</td>
                    <td style="text-align: right;">{{number_format($value->total_amount,2)}}</td>
               </tr>
               @php
               $i++;
               $total_value += $value->taxable_amount;
               $total_gst += $value->gst_amount;
               $total += $value->total_amount;
               @endphp
               @endforeach
               <tr>
                    <th colspan="5" style="text-align:right;"><b>Total</b></th>
                    <th style="text-align:right;">{{number_format($total_value,2) }}</th>
                    <th style="text-align:right;">{{number_format($total_gst,2) }}</th>
                    <th style="text-align:right;">{{number_format($total,2) }}</th>
               </tr>
          </tbody>
     </table>
     @else
     <h4>No Data!</h4>
     @endif


</body>

</html>