<!DOCTYPE html>
<html>
<head>
     <style>
          html body {
               font-family: Roboto, 'Segoe UI', Tahoma, sans-serif;
               font-size: 15px;
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
               padding: 50px 100px 50px 100px;
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
     <p style="text-align: center;"><b>Inter Connection agreement (Provisional)</b></p>
     <p style="text-align: center;"><b>(Residential Projects Registered at GEDA / National Portal)</b></p>
     <p style="text-align: center;"><b>Project Registered under New RE Policy-2023</b></p>
     <p style="text-align: center;"><b>Net Metering Inter Connection agreement</b></p>
     <p>This Provisional Agreement is made and entered into at (location)____________on this____day of ____ Month _____ Year the between Consumer, by the name of<br><b> {{$order_data->consumer_name}} </b>Consumer Number <b>{{$order_data->consumer_number}}</b> having premises at<br> <b>{{$order_data->address}}</b> as first party</p>
     <p style="text-align: center;"><b>AND</b></p>
     <p><b>{{$order_data->discom}}</b> a Company registered under the Companies Act 1956/2013 and functioning as the ”Distribution Company” or “DISCOM” under the Electricity Act 2003 having its <b>Head Office at,  {{ $discom_address }}  </b>(hereinafter referred to as PGVCL or “Distribution Licensee” or “DISCOM” which expression shall include its permitted assigns and successors) a Party of the Second Part.</p>
     <p style="text-align: center;"><b>AND WHEREAS</b></p>
     <p>The solar project of <b>{{$order_data->consumer_name}}</b> has been registered on GEDA / National Portal on <b>{{$order_data->ragistration_number}}</b> dtd <b>{{ date('d-m-Y', strtotime($order_data->ragistration_date))}}</b> Agreement!to set up Photovoltaic (PV) based Solar Power Generating Plant (SPG) of <b>{{number_format($order_data->register_kw,3, '.', '')}}</b> KW (AC) capacity at his/her/its premises in legal possession including any rooftop or terrace at <b>{{$order_data->address}}</b> connected with<b> {{$order_data->discom}}</b> grid at <b>_________</b> Voltage level for his/her/its own use within the same premises.</p>
     <p>Government of Gujarat has declared Gujarat Renewable Energy Policy 2023 on 4.10.2023 operative for the control period from date of its notification (4.10.2023) to 30th September 2028.The RE Project installed and commissioned during the operative period shall become eligible for the benefits and incentives declared under the Policy, for the period of 25 years from the date of commissioning or for the life span of the RE Project System whichever is earlier.</p>
     <p style="text-align: center;">AND WHEREAS</p>
     <p>In order to facilitate commissioning of the solar projects pursuant to notification of New the Gujarat Renewable energy Policy - 2023 effective from 04.10.2023, PGVCL has agreed to sign this agreement on Provisional basis with Consumer in terms of provisions of the Gujarat RE Policy-2023 and its incorporation in the Gujarat Electricity Regulatory Commission (Net Metering Rooftop Solar PV Grid Interactive Systems Regulations) Notification No. 5 of 2016 and its subsequent amendments subject to
     <div class="footer_sign">
          @if($type == "1" && !is_null($customer_sign))

                @php
                    $imagePath = public_path('upload/document/'.$customer_sign->image);
                    $imageData = base64_encode(file_get_contents($imagePath));
                    $imageMime = mime_content_type($imagePath);
                    $imagescustomer = 'data:' . $imageMime . ';base64,' . $imageData;
                @endphp

            <img src="{{ $imagescustomer }}" style="max-width:250px" />

            @else
                 <b>X_____________________</b>

                @endif
     </div>
     <div class="page-break"></div>

     <b>{{$order_data->consumer_name}}</b> the first party under the agreement, hereby acknowledges that the present agreement has been entered into by both the parties, taking in to account the notification of new Gujarat RE policy -2023 and on provisional basis as an interim arrangement subject to change as per further regulation /order /decision of the Hon‟ble GERC in relation to Gujarat Renewable Energy Policy 2023 and further agree to incorporate requisite modification and amendments in the agreement as per the same, if required. The first party must not dispute the applicability of the GERC order / Regulation and must make necessary modifications in the agreement as per the applicable GERC order and Regulation. The settlement will be done accordingly.</p><br>
     <p style="text-align: center;">AND WHEREAS</p>
     <p>The Distribution Licensee agrees to provide grid connectivity to the Consumer and injection of the electricity generated from his Solar PV System of capacity <b>{{number_format($order_data->register_kw,3, '.', '')}}</b> KW (AC) into the power system of Distribution Licensee as per conditions of this agreement and in compliance with the applicable Policy / rules/ Regulations/ Codes (as amended from time to time) by the Consumer which includes-</p>
     <p>1. Government of Gujarat Renewable Energy Policy 2023.</p>
     <p>2. Central Electricity Authority (Measures relating to Safety and Electric Supply) Regulations, 2010.</p>
     <p>3. Central Electricity Authority (Technical Standards for Connectivity to the Grid) Regulations, 2007 as amended from time to time.</p>
     <p>4. Central Electricity Authority (Installation and Operation of Meters) Regulation 2006.</p>
     <p>5. Gujarat Electricity Regulatory Commission (Electricity Supply Code & Related Matters) Regulations, 2015,</p>
     <p>6. Gujarat Electricity Regulatory Commission Distribution Code, 2004 and amendments thereto,</p>
     <p>7. Instruction, Directions and Circulars issued by Chief Electrical Inspector from time to time.</p>
     <p>8. CEA (Technical Standards for connectivity of the Distributed Generation) Regulations, 2013 as amended from time to time.</p>
     <p>9. Gujarat Electricity Regulatory Commission (Net Metering Rooftop Solar PV Grid Interactive Systems) Regulations, 2016 as amended from time to time.</p>
     <p>Both the parties hereby agree as follows:</p>
     <div class="footer_sign">
          @if($type == "1" && !is_null($customer_sign))

                @php
                    $imagePath = public_path('upload/document/'.$customer_sign->image);
                    $imageData = base64_encode(file_get_contents($imagePath));
                    $imageMime = mime_content_type($imagePath);
                    $imagescustomer = 'data:' . $imageMime . ';base64,' . $imageData;
                @endphp

            <img src="{{ $imagescustomer }}" style="max-width:250px" />

            @else
                 <b>X_____________________</b>

                @endif
     </div>
     <div class="page-break"></div>

     <p>1. Eligibility</p>
     <p>1.1 Consumer shall own the Solar PV System set up on its own premises or premises in his legal possession.</p>
     <p>1.2 Consumer needs to consume electricity in the same premises where Solar PV System is set up.</p>
     <p>1.3 Consumer has to meet the standards and conditions as specified in Gujarat Electricity Regulatory Commission Regulations and Central Electricity Authority Regulations and provisions of Government of Gujarat‟s Renewable Policy -2023 for being integrated into grid/distribution system.</p>

     <p>2. Technical and Interconnection Requirements</p>
     <p>2.1 Consumer agrees that his Solar PV System and Metering System will conform to the standards and requirements specified in the Policy, Regulations and Supply Code as amended from time to time.</p>
     <p>2.2 Consumer agrees that metering system(s) shall be installed at Solar PV System for recording the solar generation.</p>
     <p>2.3 Consumer agrees that he has installed or will install, prior to connection of Solar Photovoltaic System to Distribution Licensee‟s distribution system, an isolation device (both automatic and inbuilt within inverter and external manual relays) and agrees for the Distribution Licensee to have access to and operation of this, if required and for repair & maintenance of the distribution system.</p>
     <p>2.4 Consumer agrees that in case of a power outage on Discom‟s system, solar photovoltaic system will disconnect/isolate automatically and his plant will not inject power into Licensee‟s distribution system.</p>
     <p>2.5 All the equipment connected to the distribution system shall be compliant with relevant International (IEEE/IEC) or Indian Standards (BIS) and installations of electrical equipment must comply with Central Electricity Authority (Measures of Safety and Electricity Supply) Regulations, 2010 as amended from time to time.</p>
     <p>2.6 Consumer agrees that licensee will specify the interface/inter connection point and metering point.</p>
     <p>2.7 Consumer and licensee agree to comply with the relevant CEA Regulations in respect of operation and maintenance of the plant, drawing and diagrams, site responsibility schedule, harmonics, synchronization, voltage, frequency, flicker etc.</p>

     <div class="footer_sign">
          @if($type == "1" && !is_null($customer_sign))

                @php
                    $imagePath = public_path('upload/document/'.$customer_sign->image);
                    $imageData = base64_encode(file_get_contents($imagePath));
                    $imageMime = mime_content_type($imagePath);
                    $imagescustomer = 'data:' . $imageMime . ';base64,' . $imageData;
                @endphp

            <img src="{{ $imagescustomer }}" style="max-width:250px" />

            @else
                 <b>X_____________________</b>

                @endif
     </div>
     <div class="page-break"></div>

     <p>2.8 In order to fulfill Distribution Licensee‟s obligation to maintain a safe and reliable distribution system, Consumer agrees that if it is determined by the Distribution Licensee that Consumer‟s Solar Photovoltaic System either causes damage to and/or produces adverse effects affecting other consumers or Distribution Licensee‟s assets, Consumer will have to disconnect Solar Photovoltaic System immediately from the distribution system upon direction from the Distribution Licensee and correct the problem to the satisfaction of distribution licensee at his own expense prior to reconnection.</p>
     <p>2.9 The consumer shall be solely responsible for any accident to human being/animals whatsoever (fatal/non-fatal/departmental/non-departmental) that may occur due to back feeding from the Solar Photovoltaic System when the grid supply is off if so decided by CEI. The distribution licensee reserves the right to disconnect the consumer‟s installation at any time in the event of such exigencies to prevent accident or damage to man and material.</p>

     <p>3. Clearances and Approvals</p>
     <p>3.1 The Consumer shall obtain all the necessary statutory approvals and clearances (environmental and grid connection related) before connecting the photovoltaic system to the distribution system.</p>

     <p>4. Access and Disconnection</p>
     <p>4.1 Distribution Licensee shall have access to metering equipment and disconnecting means of the Solar Photovoltaic System, both automatic and manual, at all times.</p>
     <p>4.2 In emergency or outage situation, where there is no access to the disconnecting means, both automatic and manual, such as a switch or breaker, Distribution Licensee may disconnect service to the premises of the Consumer.</p>

     <p>5. Liabilities</p>
     <p>5.1 Consumer shall indemnify Distribution Licensee for damages or adverse effects from his negligence or intentional misconduct in the connection and operation of Solar Photovoltaic System.</p>
     <p>5.2 Distribution Licensee shall not be liable for delivery or realization by the Consumer of any fiscal or other incentive provided by the Central/State Government.</p>
     <p>5.3 Distribution Licensee may consider the quantum of electricity generation from the Rooftop Solar PV System owned and operated by individual Residential, Group Housing Societies, and Residential Welfare Association consumers under net metering arrangement towards RPO compliance.</p>

     <div class="footer_sign">
          @if($type == "1" && !is_null($customer_sign))

                @php
                    $imagePath = public_path('upload/document/'.$customer_sign->image);
                    $imageData = base64_encode(file_get_contents($imagePath));
                    $imageMime = mime_content_type($imagePath);
                    $imagescustomer = 'data:' . $imageMime . ';base64,' . $imageData;
                @endphp

            <img src="{{ $imagescustomer }}" style="max-width:250px" />

            @else
                 <b>X_____________________</b>

                @endif
     </div>
     <div class="page-break"></div>

     <p>6. Metering:</p>
     <p>6.1 Metering arrangement shall be as per Central Electricity Authority (InstallationAnd Operation of Meters) Regulations, 2006 as amended from time to time.</p>
     <p>6.2 Bi-directional meter shall be installed of same accuracy class as installed before Setting up of Rooftop Solar PV System.</p>

     <p>7. Commercial Settlement</p>
     <p>All the commercial settlements under this agreement shall be on provisional basis taking into account the notification of new Gujarat RE policy-2023 and as an interim arrangement subject to change as per further regulation/order/decision of GERC. Gujarat Electricity Regulatory Commission Regulations for Net Metering Rooftop Solar PV Grid Interactive Systems notification no.5 of 2016 and its subsequent amendments.</p>
     <p>The commercial settlement will be as follows:</p>
     <p>For Residential and common facility connections of Group Housing Societies/ Residential Welfare Association consumers.</p>
     <p>(i) In case of net import of energy by the consumer from distribution grid during billing cycle, the energy consumed from Distribution Licensee shall be billed as per applicable tariff to respective category of consumers as approved by the Commission from time to time. The energy generated by Rooftop Solar PV System shall be set off against units consumed (not against load/demand) and consumer shall pay demand charges, other charges, penalty etc as applicable to other consumers.</p>
     <p>(ii) In case of net export of energy by the consumer to distribution grid during billing cycle, Distribution Licensee shall purchase surplus power, after giving set off against consumption during the billing period, at Rs. 2.25/Unit for the first 5 years from commissioning of project and thereafter for the remaining term of the project at 75% of the simple average of tariff discovered and contracted under competitive bidding process conducted by GUVNL for non-park based solar projects in the preceding 6-month period, i.e. either April to September or October to March as the case may be, from the commercial operation date (COD) of the project, subject to approval of Hon‟ble GERC. Such surplus purchase shall be utilized for meeting RPO of Distribution Licensee. However, fixed / demand charges, other charges, penalty etc shall be payable as applicable to other consumers.</p>
     <p>Provided that in case the consumer is setting up additional solar rooftop capacity under the scheme over and above solar rooftop capacity set up prior to this scheme, surplus energy of entire solar rooftop capacity shall be purchased by Distribution Company at the rate of Rs. 2.25/Unit for the first 5 years from commissioning of project and thereafter for the remaining term of the project at 75% of the simple average of tariff discovered and contracted under competitive bidding process conducted by GUVNL for non-park based solar projects in the preceding 6- month period, i.e. either April to September or October to March as

     <div class="footer_sign">
          @if($type == "1" && !is_null($customer_sign))

                @php
                    $imagePath = public_path('upload/document/'.$customer_sign->image);
                    $imageData = base64_encode(file_get_contents($imagePath));
                    $imageMime = mime_content_type($imagePath);
                    $imagescustomer = 'data:' . $imageMime . ';base64,' . $imageData;
                @endphp

            <img src="{{ $imagescustomer }}" style="max-width:250px" />

            @else
                 <b>X_____________________</b>

                @endif
     </div>
     <div class="page-break"></div>

     the case may be, from the commercial operation date (COD) of the project, treating earlier agreement as cancelled.</p>
     <p>In case of net injection, net amount receivable by consumer in a bill shall be credited in consumers account and will be adjusted against bill amount payable in subsequent months. However, at the end of year, if net amount receivable by consumer is more than Rs. 100/- and consumer requests for payment, the same may be paid. Such payment shall be made only once in a year based on year end position and request of consumer.</p>

     <p>8. Connection Costs:</p>
     <p>The Consumer shall bear all costs related to setting up of Solar Photovoltaic System including metering and inter-connection. The Consumer agrees to pay the actual cost of modifications and upgrades to the service line, cost of up gradation of transformer to connect photovoltaic system to the grid in case it is required.</p>

     <p>9. Inspection, Test, Calibration and Maintenance prior to connection10</p>
     <p>Before connecting, Consumer shall complete all inspections and tests finalized in consultation with the (Name of the Distribution license) and if required Gujarat Energy Transmission Corporation Limited (GETCO) to which his equipment is connected. Consumer shall make available to <b>{{$order_data->discom}}</b> all drawings, specifications and test records of the project or generating station as the case may be.</p>

     <p>10. Records:</p>
     <p>Each Party shall keep complete and accurate records and all other data required by each of them for the purposes of proper administration of this Agreement and the operation of the Solar PV System.</p>

     <p>11. Dispute Resolution:</p>
     <p>11.1 All disputes or differences between the Parties arising out of or in connection with this Agreement shall be first tried to be settled through mutual negotiation, promptly, equitably and in good faith.</p>
     <p>11.2 In the event that such differences or disputes between the Parties are not settled through mutual negotiations within sixty (60) days or mutually extended period, after such dispute arises, then for</p>
     <p>(a) Any dispute in billing pertaining to energy injection and billing amount, would be settled by the Consumer Grievance Redressal Forum and Electricity Ombudsman.</p>
     <p>(b) Any other issues pertaining to the Regulations and its interpretation; it shall be decided by the Gujarat Electricity Regulatory Commission following appropriate prescribed procedure.</p>

     <div class="footer_sign">
           @if($type == "1" && !is_null($customer_sign))

                @php
                    $imagePath = public_path('upload/document/'.$customer_sign->image);
                    $imageData = base64_encode(file_get_contents($imagePath));
                    $imageMime = mime_content_type($imagePath);
                    $imagescustomer = 'data:' . $imageMime . ';base64,' . $imageData;
                @endphp

            <img src="{{ $imagescustomer }}" style="max-width:250px" />

            @else
                 <b>X_____________________</b>

                @endif
     </div>
     <div class="page-break"></div>

     <p>12. Termination</p>
     <p>12.1 The Consumer can terminate agreement at any time by giving Distribution Licensee 90 days prior notice.</p>
     <p>12.2 Distribution Licensee has the right to terminate Agreement with 30 days prior written notice, if Consumer commits breach of any of the terms of this Agreement and does not remedy the breach within 30 days of receiving written notice from Distribution Licensee of the breach.</p>
     <p>12.3 Consumer shall upon termination of this Agreement, disconnect the Solar Photovoltaic System from Distribution Licensee‟s distribution system within one week to the satisfaction of Distribution Licensee.</p>
     <p>Communication:</p>
     <p>The names of the officials and their addresses, for the purpose of any communication in relation to the matters covered under this Agreement shall be as under:</p>
     <table border="1">
          <tr>
               <td style="text-align:center; width: 55%;"><b>In Respect Of The<br><br><br><br><br><br></b>Chief Engineer<br><b>{{$order_data->discom}}</b></td>
               <td style="text-align:center; width: 45%;"><b>In Respect Of The Consumer

                 @if($type == "1" && !is_null($customer_sign))

                @php
                    $imagePath = public_path('upload/document/'.$customer_sign->image);
                    $imageData = base64_encode(file_get_contents($imagePath));
                    $imageMime = mime_content_type($imagePath);
                    $imagescustomer = 'data:' . $imageMime . ';base64,' . $imageData;
                @endphp

            <img src="{{ $imagescustomer }}" style="max-width:250px" />

            @else
 <br><br><br><br><br>
            @endif
               <br><br></b><b>{{$order_data->consumer_name}}</b></td>
          </tr>
     </table>
     <p>IN WITNESS WHEREOF, the Parties hereto have caused this Agreement to be executed by their authorized officers, and copies delivered to each Party, as of the day and year herein above stated.</p>
     <table>
          <tr>
               <td>FOR AND ON BEHALF OF</td>
               <td>FOR AND ON BEHALF OF THE PROJECT OWNER</td>
          </tr><br><br>
          <tr>
               <td style="text-align: center; padding-top:30px;"><u>Authorized Signatory</u></td>
               <td style="text-align: center; padding-top:30px;">
                 @if($type == "1" && !is_null($customer_sign))

                @php
                    $imagePath = public_path('upload/document/'.$customer_sign->image);
                    $imageData = base64_encode(file_get_contents($imagePath));
                    $imageMime = mime_content_type($imagePath);
                    $imagescustomer = 'data:' . $imageMime . ';base64,' . $imageData;
                @endphp

            <img src="{{ $imagescustomer }}" style="max-width:250px" />

            @endif
                <b><u>{{$order_data->consumer_name}}</u></b></td>
          </tr>
          <tr>
               <td><b>WITNESS</b></td>
               <td><b>WITNESS</b></td>
          </tr>
          <tr>
               <td>1.______________________________</td>
               <td>1.______________________________</td>
          </tr>
          <tr>
               <td>(______________________________)</td>
               <td>(______________________________)</td>
          </tr><br><br>
          <tr>
               <td>2.______________________________</td>
               <td>2.______________________________</td>
          </tr>
          <tr>
               <td>(______________________________)</td>
               <td>(______________________________)</td>
          </tr>
     </table>
</body>
</html>
