<!DOCTYPE html>
<html>

<head>
     <style>
          html body {
               font-family: Roboto, 'Segoe UI', Tahoma, sans-serif;
               font-size: 14px;
               line-height: 1.4rem;
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
          .main-list li { font-weight: 500;line-height: 1.5rem; }
          .sub-list li { text-align: justify;font-weight: normal !important; }
          .meta-list li { list-style: lower-roman; }
     </style>
</head>

<body>



     <p style="text-align: right;"><b>Annexure - IV</b></p>

     <p style="text-align: center;"><b><u>Net Metering inter connection agreement</u></b></p>

     <p> This Agreement is made and entered into at (location)__________________on this (date) ____ Day of ____ Month _____ Year between
          The Eligible Consumer, by the name of <b><u>{{$order_data->consumer_name}}</u></b> having premises at (address) <b>{{$order_data->address}}</b> as first party.</p>

     <p style="text-align: center;"><b>AND</b></p>

     <p> Distribution Licensee (herein after called as Discom) and represented by <b>{{$order_data->discom}}</b>
          (designation of office) and having its registered office at (address) Regd. Office: <b>Head Office at, {{ $discom_address }} </b>
          as second party of the agreement And whereas, the Discom agrees to provide grid connectivity to the Eligible Consumer for injection of the electricity
          generated from his SPV plant of capacity <b>{{number_format($order_data->register_kw,3, '.', '')}}</b> kilowatts into the power system of Discom and as per
          conditions of this agreement and RERC (Connectivity and Net Metering for Rooftop and Small Solar Grid
          Interactive Systems) Regulations, 2015 issued by the RajasthanElectricity Regulatory Commission.</p>

     <p style="text-align: left;">Both the parties hereby agree to as follows:</p>

     <ol class="main-list">
          <li>Eligibility
               <ol class="sub-list">
                    <li>Eligibility for net-metering has been specified in the relevant order of the Rajasthan Electricity Regulatory Commission. Eligible Consumer has to meet the standards and conditions for being integrated into grid/distribution system.</li>
               </ol>
          </li>
          <li>Technical and Interconnection Requirements
               <ol class="sub-list">
                    <li>The Eligible Consumer agrees that his solar generation plant and net metering system will conform to the standards and requirements specified in RERC (Connectivity and Net Metering for Rooftop and Small Solar Grid Interactive Systems) Regulations, 2015 and in the following Regulations and codes as amended from time to time.</li>
                    <li>Eligible Consumer agrees that he has installed or will install, prior to connection of Photovoltaic system to Discom’s distribution system, an isolation device (both automatic and inbuilt within inverter and external manual relays) and agrees for the Discom to have access to and operation of this, if required and for repair & maintenance of the distribution system.
                         <ol class="meta-list">
                              <li>CEA’s (Technical Standards for connectivity of the Distributed Generating Resources) Regulations, 2013</li>
                              <li>Central Electricity Authority (Installation and Operation of Meters) Regulation 2006</li>
                              <li>RERC Supply Code Regulations 2004</li>
                         </ol>
                    </li>
                    <li>Eligible Consumer agrees that in case of a power outage on Discom’s system, photovoltaic system will disconnect/isolate automatically and his plant will not inject power into Licensee’s distribution system.</li>
                    <li>All the equipment connected to distribution system shall be compliant with relevan International (IEEE/IEC) or Indian standards (BIS) and installations of electrical equipment must comply with Central Electricity Authority (Measures of Safety and Electricity Supply) Regulations, 2010.</li>
                    <li>Eligible Consumer agrees that licensee will specify the interface/interconnection point and metering point.</li>
                    <li>Eligible Consumer and licensee agree to comply with the relevant CEA regulations and RERC (Metering) Regulations, 2007 in respect of operation and maintenance of the plant, drawing and diagrams, site responsibility schedule, harmonics, synchronization, voltage, frequency, flicker etc.</li>
                    <li>Due to Discom’s obligation to maintain a safe and reliable distribution system, Eligible Consumer agrees that if it is determined by the Discom that Eligible Consumer’s photovoltaic system either causes damage to and/or produces adverse effects affecting other consumers or Discom’s assets, Eligible Consumer will have to disconnect photovoltaic system immediately from the distribution system upon direction from the Discom and correct the problem at his own expense prior to a reconnection.</li>
                    <li>The consumer shall be solely responsible for any accident to human being/animals whatsoever (fatal/non-fatal/departmental/non-departmental) that may occur due to back feeding from the SPG plant when the grid supply is off. The distribution licensee reserves the right to disconnect the consumer’s installation at any time in the event of such exigencies to prevent accident or damage to man and material.</li>
               </ol>
          </li>
          <li>Clearances and Approvals
               <ol class="sub-list">
                    <li>The Eligible Consumer shall obtain all the necessary approvals and clearances (environmental and grid connection related) before connecting the photovoltaic system to the distribution system.</li>
               </ol>
          </li>
          <li>Access and Disconnection
               <ol class="sub-list">
                    <li>Discom shall have access to metering equipment and disconnecting means of the solar photovoltaic system, both automatic and manual, at all times.</li>
                    <li>In emergency or outage situation, where there is no access to the disconnecting means, both automatic and manual, such as a switch or breaker, Discom may disconnect service to the premises of the Eligible Consumer</li>
               </ol>
          </li>
          <li>Liabilities
               <ol class="sub-list">
                    <li>Eligible Consumer and Discom shall indemnify each other for damages or adverse effects from either party’s negligence or intentional misconduct in the connection and operation of photovoltaic system or Discom’s distribution system.</li>
                    <li>Discom and Eligible Consumer shall not be liable to each other for any loss of profits or revenues, business interruption losses, loss of contract or loss of goodwill, or for indirect, consequential, incidental or special damages, including, but not limited to, punitive or exemplary damages, whether any of the said liability, loss or damages arise in contract, or otherwise.</li>
                    <li>Discom shall not be liable for delivery or realization by Eligible Consumer for any fiscal or other incentive provided by the Central/State Government beyond the scope specified by the Commission in its relevant Order</li>
                    <li>The Discom may consider the quantum of electricity generation from the rooftop solar PV system under net metering arrangement towards RPO. (Applicable only in case of Eligible Consumer who is not defined as an Obligated Entity).</li>
                    <li>The proceeds from CDM benefits shall be retained by the Discom.</li>
               </ol>
          </li>
          <li>Commercial Settlement
               <ol class="sub-list">
                    <li>All the commercial settlement under this agreement shall follow the Net Metering Regulations,2015 issued by the RERC.</li>
               </ol>
          </li>
          <li>Connection Costs
               <ol class="sub-list">
                    <li>The Eligible Consumer shall bear all costs related to setting up of photovoltaic system including metering and interconnection costs. The Eligible Consumer agrees to pay the actual cost of modifications and upgrades to the service line required to connect photovoltaic system to the grid in case it is required.</li>
               </ol>
          </li>
          <li>Termination
               <ol class="sub-list">
                    <li>The Eligible Consumer can terminate agreement at any time by providing Discom with 90 days prior notice.</li>
                    <li>Discom has the right to terminate Agreement on 30 days prior written notice, if Eligible Consumer commits breach of any of the term of this Agreement and does not remedy the breach within 30 days of receiving written notice from Discom of the breach.</li>
                    <li>Eligible Consumer shall upon termination of this Agreement, disconnect the photovoltaic system from Discom’s distribution system in a timely manner and to Discom’s satisfaction.</li>
               </ol>
          </li>

     </ol>


     <p>In witness, whereof, Mr. ----------------for and on behalf of -------- ------------------ (Eligible Consumer) and
     Mr. --------------------- for and on behalf of <b>{{$order_data->discom}}</b> (Discom) sign this agreement in two originals.</p>
<br/><br/>
     <table>
          <tr>
               <td style="width: 60%;"><b>Eligible Consumer</b></td>
               <td style="width: 40%;"><b>Distribution Licensee</b></td>
          </tr>
          <tr>
               <td style="width: 60%;">Name : <b>{{$order_data->consumer_name}}</b></td>
               <td style="width: 40%;">Name : <b>{{ env('APP_OWNER_NAME') }}</b></td>
          </tr>
          <tr>
               <td style="width: 60%;">Address : <b>{{$order_data->address}}</b></td>
               <td style="width: 40%;">Address : <b>{{ env('APP_OWNER_ADDRESS_NO_BR') }}</b></td>
          </tr>
          <tr>
               <td style="width: 60%;">Service connection No : <b>{{$order_data->consumer_number}}</b></td>
               <td style="width: 40%;">Designation : <b>{{ env('APP_OWNER_DESIGNATION') }}</b></td>
          </tr>
     </table>

</body>

</html>