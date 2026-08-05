<!DOCTYPE html>
<html>

<head>
     <style>
          html body {
               font-family: Roboto, 'Segoe UI', Tahoma, sans-serif;
               font-size: 14px;
               line-height: 1.5rem;
          }

          h4,
          h2 {
               text-align: center;
          }

          table {
               width: 100%;
               border: none;
               border-spacing: 0;
          }

          body {
               padding: 50px 95px 50px 95px;
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

          .page-break {
               page-break-before: always;
          }

          .footer_sign {
               position: absolute;
               bottom: 20;
               width: 80%;
               text-align: center;
          }
     </style>
</head>

<body>



     <p style="text-align: center;"><b><u>Residential Roof Top Solar Installation</u></b></p>
     <p style="text-align: center;"><b><u> Vendor Feasibility Report Format </u></b></p>
     <br />

     <ol>
          <li>Name of the Consmer : {{$order_data->consumer_name}}</li>
          <li>Discom Consumer ID : <b>{{$order_data->consumer_number}}</b></li>
          <li>Discom ID : {{$order_data->subDivisionPDF->name}}</li>
          <li>PM Surya Shakti Portal ID :- {{$order_data->ragistration_number}}</li>
          <li>Jan Samarth ID : ________________________</li>
          <li>Address for installation : <b>{{$order_data->address}}</b></li>
          <li>District of Installation : {{ $order_data->district->name ?? '_____________' }}</li>
          <li>State of Installation : {{ $order_data->district->state->state_name ?? '_____________' }}</li>
          <li>Pin code of Installation : {{ $order_data->pin_code }}</li>
          <li>OEM Name : _______________________________________________</li>
          <li>Channel partner If Any : <b>{{ env('APP_NAME') }}</b></li>
          <li>EPC Contractor Address : <b>{{ env('APP_OWNER_ADDRESS_NO_BR') }}</b></li>
          <li>EPC contractor Bank details :</li>
          <li>RTS Capacity In KW Applied - <b>{{number_format($order_data->register_kw,3, '.', '')}} KW</b> </li>
          <li>Actual RTS Capacity to be installed <b>{{number_format($order_data->register_kw,3, '.', '')}} KW</b> </li>
          <li>Is the vendor registered in MNRE Portal : <b>Yes</b> /No
               <br />(Note : Only vendors registered in MNRE portal will be allowed)
          </li>
          <li>Feasibility Report Status
               <br />
               <span><b>Feasible ( YES )</b></span> <span style="margin-left:150px;"> Not Feasible </span>
          </li>
          <li>Project Cost (all Inclusive) : <b>{{ number_format($order_data->total_amount,2) }} </b></li>
          <li>Site Layout –Images (2-4 images to be uploaded )</li>
     </ol>

     <br /><br /><br /><br /><br /><br />
     <p style="text-align: right;"><b>Authorised Signatory of the vendor with Stamp</b></p>


</body>

</html>