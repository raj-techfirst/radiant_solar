<!DOCTYPE html>
<html>
<head>
     <style>
          html body {
               font-family: Roboto, 'Segoe UI', Tahoma, sans-serif;
               font-size: 83%;
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
          td {
               padding: 3px;
          }
          body{
               padding: 80px 50px 0 50px;
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
     <table>
          <tr>
               <td colspan="2" style="text-align: right;">Ref : {{ env('APP_SORT') }}/{{$order_data->id }}</td>
          <tr>
               <td style="text-align: left; padding-left: 5px;"><b>To : </b></td>
               <td style="text-align: right;"><b>Date :  @if(isset($installation_data) && $installation_data->date != '' && $installation_data->date != '0000-00-00')
               {{ date('d-m-Y', strtotime($installation_data->date)) }}
               @endif</b></td>
          </tr>
          <tr>
               <td style="text-align: left; padding-left: 5px;"><b>{{$order_data->discom}}</b></td>
          </tr>
          <tr>
               <td style="text-align: left; padding-left: 5px;"><b>{{$order_data->subDivisionPDF->name}}</b></td>
          </tr>
          <tr>
               <td style="text-align: left; padding-left: 5px;"><b>{{$order_data->division}}</b></td>
          </tr>
          <tr>
               <td style="text-align: left; padding-left: 5px;"><b>{{$order_data->circle}}</b></td>
          </tr>
          <br>
          <tr>
               <td style="text-align: left; padding-left: 5px; font-size:17px;"><b><u>Project Registration No:</u> <u>{{$order_data->ragistration_number}}</u></b></td>
               <td style="text-align: left; padding-left: 5px; font-size:17px;"><b><u>Consumer No:</u> <u>{{$order_data->consumer_number}}</u></b></td>
          </tr>
     </table>
     <p><b><u>Subject :- REQUEST FOR CHANGE THE METER AND INSTALLATION THE SOLAR (BI-DIRECTIONAL) METER</u></b></p>
     <p>Respected Sir </p>
     <p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;We Would like To Introduce Ourselves As <b>“ {{ env('APP_NAME') }} “,</b> Solar System Integrators And Channel Partner Of GUVNL For Grid Connected Solar Programmer Under GUVNL. We Are Also Empanelment Agency Of PGVCL with empanelment Numbar :-<b> {{ env('APP_EMPANELMENT') }}</b></p>
     <p>With Reference To Above Mention Subject, We Are Requesting To You, Please Change The Meter And Installation The Solar (Bi-Directional) Meter For The Solar Systems At Below Mentioned Address.</p>
     <p><b>For {{ env('APP_NAME') }}</b></p><br><br>
     <table>
          <tr>
               <td style="width: 50%;">Authorised Signatory</td>
               <td style="width: 50%;"><b>Name : {{$order_data->consumer_name}}</td>
          </tr>
          <tr>
               <td style="width: 50%;"><b>Mo. {{ env('APP_OWNER_MOBILE') }}</b></td>
               <td style="width: 50%;"><b>Address : <b>{{$order_data->address}}</b> <br/> <b>Mo. {{$order_data->contact_number}}</b> </td>
          </tr>
     </table>
     <p>Attached copy</p>
     <p style="padding-left: 20px;">1) NATIONAL PORTAL FEASIBILITY REPORT<br/>
     2) NATIONAL SELF CERTIFICATE<br/>
     3) QUOTATION PAYMENT RECEIPT<br/>
     4) ELECTRICITY BILL<br/>
     5) PANELS CERTIFICATE<br/>
     6) INVERTER CERTIFICATE<br/>
     7) INVOICE<br/>
     8) AGREEMENT WITH 300 NON JUDICIAL STAMP<br/>
     9) BANK DETAILS<br/>
     10) SITE PHOTO<br/>
     11) MODEL AGREEMENT</p>
</body>
</html>