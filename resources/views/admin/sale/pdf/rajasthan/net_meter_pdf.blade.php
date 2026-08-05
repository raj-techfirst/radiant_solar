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

          .table {
               width: 100%;
               margin-bottom: 0rem;
               color: #212529;
               border-collapse: collapse;
               line-height: 1rem;
          }

          .table th,
          .table td {
               padding: 4px;
               vertical-align: middle;
               border: 1px solid #212529;

          }


          .table th {
               text-align: inherit;

          }
     </style>
</head>

<body>
     <br />
     <p style="text-align: right;">DATE : '__-__-____'</p>
     <br />
     <p style="text-align: left;">To<br />{{$order_data->subDivisionPDF->name}}<br /><b>{{$order_data->discom}}</b></p>
     <br /><br />
     <p style="text-align: center;">Sub – Submission of Solar and Net Meter for testing</p>

     <p style="text-align: left;">Dear sir,</p>

     <p><span style="padding-right: 100px;"> </span>We Request you to kindly submit Solar meters and net Meters for testing as
          The solar plant is ready for installation</p>
     <br /><br />
     <p style="text-align: left;">Solar Meter#-<br />Net Meter#-</p>

     <br /><br />

     <p style="text-align: left;">Customer Name : <b>{{$order_data->consumer_name}}</b></br>
          Address : <b>{{$order_data->address}}</b></br>
          State : <b>{{ $order_data->district->state->state_name ?? '_____________' }}</b></br>
          Mobile : <b>{{$order_data->contact_number}}</b></p>

     <br /><br />
     <p style="text-align: left;">Thanking you</p>

     <div class="page-break"></div>

     <p style="text-align: right;line-height:13px;"><b> Comml.JDP/682 &nbsp; &nbsp; &nbsp; &nbsp; <br />Annexure-I</b></p>

     <p style="text-align: center;"><b><u>Application for Net Metering and Grid Connectivity of Grid Connected <br /> Rooftop & Small Solar Photovoltaic System</u></b></p>


     To: <br /> {{$order_data->subDivisionPDF->name}}<br /><b>{{$order_data->discom}}</b>
     <br /> 
     <p> Date : '__-__-____'
          <br />
          1/ we herewith apply for a solar energy net-metering connection at the service connection and for the solar PV plant of which details are given below.
     </p>

     <table class="table">
          <tr>
               <td style="width:10px;text-align: center;">1</td>
               <td>Name of applicant</td>
               <td style="width:200px;">{{$order_data->consumer_name}}</td>
          </tr>
          <tr>
               <td style="width:10px;text-align: center;">2</td>
               <td>Address of applicant</td>
               <td>{{$order_data->address}}</td>
          </tr>
          <tr>
               <td style="width:10px;text-align: center;">3</td>
               <td>Service connection number/Account Number</td>
               <td></td>
          </tr>
          <tr>
               <td style="width:10px;text-align: center;">4</td>
               <td>Single Phase/Three Phase</td>
               <td>{{$order_data->phase}}</td>
          </tr>
          <tr>
               <td style="width:10px;text-align: center;">5</td>
               <td>Sanctioned Load(KW)/Contract Demand(KVA)</td>
               <td>{{$order_data->register_kw}}</td>
          </tr>
          <tr>
               <td style="width:10px;text-align: center;">6</td>
               <td>Category(DS/NDS etc.)</td>
               <td>DS</td>
          </tr>
          <tr>
               <td style="width:10px;text-align: center;">7</td>
               <td>Telephone number(s)</td>
               <td>{{$order_data->contact_number}}</td>
          </tr>
          <tr>
               <td style="width:10px;text-align: center;">8</td>
               <td>Email ID</td>
               <td>{{$order_data->email}}</td>
          </tr>
          <tr>
               <td style="width:10px;text-align: center;">9</td>
               <td>Solar PV plant capacity (Kilo Watts)</td>
               <td>{{$order_data->register_kw}}</td>
          </tr>
          <tr>
               <td style="width:10px;text-align: center;">10</td>
               <td>Solar grid inverter make and type</td>
               <td>
                    {{ $order_data->inveter->name }}
               </td>
          </tr>
          <tr>
               <td style="width:10px;text-align: center;">11</td>
               <td>Solar grid inverter has automatic isolation protection (Y/N)?</td>
               <td>YES</td>
          </tr>
          <tr>
               <td style="width:10px;text-align: center;">12</td>
               <td>Has a Solar Generation Meter been installed(Y/N)?</td>
               <td>NO</td>
          </tr>
          <tr>
               <td style="width:10px;text-align: center;">13</td>
               <td>Expected date of commissioning of solar PV system.</td>
               <td>-</td>
          </tr>
          <tr>
               <td style="width:10px;text-align: center;">14</td>
               <td>Details of test certificates of Solar PV plant/inverter for standards required under the Regulations.</td>
               <td>YES</td>
          </tr>
          <tr>
               <td style="width:10px;text-align: center;">15</td>
               <td>Whether applicant wish to avail MNRE subsidy (Yes/No) </td>
               <td>YES</td>
          </tr>
          <tr>
               <td style="width:10px;text-align: center;">16</td>
               <td>Whether MNRE subsidy is sanctioned(Yes/No)</td>
               <td>YES</td>
          </tr>

     </table>

     <p style="text-align: left;margin:0px;padding:0px;">
          <b>Documents Enclosed:</b>
     <ol style="margin:0px;">
          <li>Copy of latest Electricity Bill</li>
          <li>Copy of subsidy sanction letter from MNRE</li>
          <li>Self certification for not availing MNRE subsidy</li>
     </ol>
     <b>Certificate:</b> The above stated information is true to the best of my knowledge</p>
<br/><br/>
     <table style="width:100%">
          <tr>
               <td style="width: 40%;">Place : __________________</td>
               <td style="width: 60%;text-align:right;">Name : _________________________________</td>
          </tr>
          <tr>
               <td style="width: 40%;">Date : ____________</td>
               <td style="width: 60%;text-align:right;">Signature : _________________________________</td>
          </tr>
     </table>

</body>

</html>