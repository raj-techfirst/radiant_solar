<!DOCTYPE html>
<html>
<head>
     <style>
          html body {
               font-family: Roboto, 'Segoe UI', Tahoma, sans-serif;
               font-size: 14px;
               line-height: 1.3rem;
          }
          h4,h2 {
               text-align: center;
          }
          table {
               width: 100%;
               border: none;
               border-spacing: 0;
          }
          body{
               padding: 70px;
          }
          td {
               padding: 3px;
          }
          @page {
               margin: 0px !important;
          }
          p {
               text-align: justify;
          }
     </style>
</head>
<body>
     <p><b>Undertaking/Self- Declaration for domestic content requirement fulfillment</b></p>
     <p>This is to certify that M/S <b>{{ env('APP_NAME') }}</b> has installed <b> {{$order_data->register_kw}} KW </b> [Capacity] Grid Connected Rooftop Solar PV Power Plant for <b>{{$order_data->consumer_name}}</b> at <b>{{$order_data->address}}</b> under sanction number <b>{{$order_data->ragistration_number}}</b>.dated <b>{{ ($order_data->ragistration_date != '' && $order_data->ragistration_date != '0000-00-00') ? date('d-m-Y',strtotime($order_data->ragistration_date)) : '' }} </b> [sanction date] issued by <b>{{$order_data->discom}}.</b></p>
     <p>2.  It is hereby undertaken that the PV modules installed for the above-mentioned project are domestically manufactured using domestic manufactured solar cells. The details of installed PV Modules are follows:</p>
     <p>1. PV Module Capacity: <b>{{$installation_data->panelwatt->name ?? ''}}</b> Watt<br/>
     2. Number of PV Modules: <b>{{$installation_data->penal_nos ?? ''}}</b> Nos<br/>
     3. Sr No of PV Module </p>
     <table border="1">
          @php
          $count = 0;
          @endphp
          @foreach($installation_panel_data as $val)
          @if($count % 4 == 0)
          <tr>
               @endif
               <td>{{$val->serial_no}}</td>
               @php
               $count++;
               @endphp
               @if($count % 4 == 0)
          </tr>
          @endif
          @endforeach
          @if($count % 4 != 0)
          </tr>
          @endif
     </table>

     <p>4. PV Module Make: <b>{{$installation_data->panelcompany->name}}</b><br>
     5. Purchase Order Number: {{$order_data->purchase_order_number}}<br>
     6. Purchase Order Date: {{ date("d-m-Y", strtotime($order_data->purchase_order_date))}}</br>
     7. Cell manufacturer’s name : {{$order_data->cell_manufacture_name}}<br>
     8. Cell GST invoice No: {{$order_data->cell_gst_invoice_no}}</p>

     <p>3. The above undertaking is based on the certificate issued by PV Module manufacturer/supplier while supplying the above mentioned order. </p>
     <p>4.	I, <b>{{ env('APP_OWNER_NAME') }}</b> on behalf of M/S <b>{{ env('APP_NAME') }}</b> further declare that the information given above is true and correct and nothing has been concealed therein. If anything is found incorrect at any stage then the due Central Financial Assistance (CFA) that I have not charged from the consumer can be withheld and appropriate action may be taken against me and my company for wrong declaration. Supporting documents and proof of the above information will be provided as and when requested by MNRE.</p>
        
     <br/><br/><br/><br/>
     <p style="text-align: right;">(Signature With official Seal)</p>
     <p style="text-align: right;padding:0px;margin:0px;">For M/S <b>{{ env('APP_NAME') }}</b></p>
     <p style="text-align: right;padding:0px;margin:0px;">Name: <b>{{ env('APP_OWNER_NAME') }}</b></p>
     <p style="text-align: right;padding:0px;margin:0px;">Designation: <b>{{ env('APP_OWNER_DESIGNATION') }}</b></p>
     <p style="text-align: right;padding:0px;margin:0px;">Phone:<b>{{ env('APP_OWNER_MOBILE') }}</b></p>
     <p style="text-align: right;padding:0px;margin:0px;">Email: <b>{{ env('APP_OWNER_EMAIL') }}</b></p>
</body>
</html>