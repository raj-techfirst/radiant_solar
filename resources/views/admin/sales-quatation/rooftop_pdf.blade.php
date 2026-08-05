<!DOCTYPE html>
<html>

<head>
     <title>{{$title}}</title>
     <style>
          html body {
               font-family: Roboto, 'Segoe UI', Tahoma, sans-serif;
               font-size: 14px;
               line-height: 1.3rem;
          }
          h4,
          h2 {
               text-align: center;
          }
          td {
               padding: 3px;
          }
          .text_right{
               text-align: right;
          }
          table {
               width: 100%;
               /* border: 1px solid #000; */
               border-spacing: 0;
          }
          .page-break {
               page-break-before: always;
          }
          body {
              padding: 50px;
              padding-top: 120px;
              padding-bottom: 100px;             
              background-image:  url({{ public_path('img/pdf-bg.png') }});
               background-size: cover;
               background-repeat: no-repeat;
               background-position: center;
          }
          @page { margin:0px !important; } 
          p {
               text-align: justify;
          }
     </style>
</head>

<body>
     <h2 style="font-size:22px;">SOLAR POWER PLANT PROPOSAL FOR RESIDENTIAL PROJECT</h2>
     <hr>
     <br/>
     <table>
          <tr>
               <td>Ref : {{ env('APP_SORT') }}/EPC/{{str_pad($sales_data->id, 2, '0', STR_PAD_LEFT)}}</td>
               <td class="text_right"><b>Date : {{date('d-m-Y', strtotime($sales_data->created_at))}} </b></td>
          <tr>
          
          <tr>
               <td colspan="6" style="text-align: left; padding-left: 5px;padding-top:20px;"><b>To : </b></td>
          </tr>
          <tr>
               <td colspan="6" style="text-align: left; padding-left: 5px;"><b>{{$sales_data->name}} </b></td>
          </tr>
          <tr>
               <td colspan="6" style="text-align: left; padding-left: 5px;"><b>{{$sales_data->address}}</b></td>
          </tr>
          <tr>
               <td colspan="6" style="text-align: left; padding-left: 5px;"><b>{{$sales_data->mobile}}</b></td>
          </tr>
     </table>
     <br/>
     <p>Subject :- Design, Supply, Engineering, Installation & Commissioning of Grid Tied <b>{{$sales_data->pv_capacity_kw}} KW</b> Solar Power Generating System</p>
     <br/>
     <p>Respected Sir </p>
     <p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;We Thank You Very Much For Showing Interest In Our Grid Connected Solar System. We Are Pleased To Submit Our Most Competitive Offer For<b> {{$sales_data->pv_capacity_kw}} KW </b>Solar On Grid Rooftop System As Per Your Requirement. The Price Quoted You Here under Is For Turnkey Project Including Installation & Commissioning Of The System At Your Premises. Please Feel Free To Write / Speak To Us For Any Further Query Or Any Clarification If Required.</p>
    <br/> <p>We Look Forward To Hear From You Shortly.</p>
     <p>Thanking You,</p>
     <p>Sunniest Regards,<br>For, {{ env('APP_NAME') }}</p>
     <p>Authorised Person<br>{{$sales_data->agent_name}}<br>{{$sales_data->agent_mobile}}</p>

     <!-- <div class="page-break"></div>
     <h2>WORK IN GUJARAT</h2>
     <h2>Our Valuable Client Network</h2> -->
    
     <div class="page-break"></div>
     <h2>Commercial Offer</h2>
     <table border="1">
          <tr>
               <th style="width: 20px;">Sr. no.</th>
               <th style="width: 250px;">Description</th>
               <th style="width: 80px;">Installed Capacity (KW)</th>
               <th style="width: 60px;">Rate / kw</th>
               <th style="width: 60px;">Value</th>
          </tr>
          <tr>
               <td>1</td>
               <td>Complete Epc Price For Design, Engineering, Supply And Installation And Testing Of Solar Power Generating System</td>
               <td class="text_right">{{$sales_data->pv_capacity_kw}}</td>
               <td class="text_right"><span style="font-family: DejaVu Sans; sans-serif;">&#8377;</span> {{ ($sales_data->total_system_cost > 0 && $sales_data->pv_capacity_kw > 0)? number_format($sales_data->total_system_cost / $sales_data->pv_capacity_kw,2) : 0}}</td>
               <td class="text_right"><span style="font-family: DejaVu Sans; sans-serif;">&#8377;</span> {{number_format($sales_data->total_system_cost,2)}}</td>
          </tr>
          <tr>
               <td colspan="4" class="text_right">Meter Charge (Approx)</td>
               <td class="text_right">
                    
                    {!! isset($sales_data) && ($sales_data->meter_charges_extra == 'Yes') ? 'Extra' : '<span style="font-family: DejaVu Sans; sans-serif;">&#8377;</span> '.number_format($sales_data->meter_charges,2) !!}
               </td>
          </tr>
          <tr>
               <td colspan="4" class="text_right">Registration Charges</td>
               <td class="text_right"><span style="font-family: DejaVu Sans; sans-serif;">&#8377;</span> {{number_format($sales_data->registration_fee,2)}}</td>
          </tr>
          @if($sales_data->other_charge_amount != null && $sales_data->other_charge_amount != 0)
          <tr>
               <td colspan="4" class="text_right">{{$sales_data->other_charge_name}}</td>
               <td class="text_right"><span style="font-family: DejaVu Sans; sans-serif;">&#8377;</span> {{number_format($sales_data->other_charge_amount,2)}}</td>
          </tr>
          @endif
          <tr>
               <td colspan="4" class="text_right"><b>Total Project Cost (Payable)</b></td>
               <td class="text_right"><span style="font-family: DejaVu Sans; sans-serif;">&#8377;</span> {{number_format($sales_data->total_amount,2)}}</td>
          </tr>
          <tr>
               <td colspan="4" class="text_right"><b>Total Subsidy</b></td>
               <td class="text_right"><span style="font-family: DejaVu Sans; sans-serif;">&#8377;</span> {{number_format($sales_data->subsidy,2)}}</td>
          </tr>
          <tr>
               <td colspan="4" class="text_right"><b>Net Customer Price</b></td>
               <td class="text_right"><span style="font-family: DejaVu Sans; sans-serif;">&#8377;</span> {{number_format($sales_data->total_amount - $sales_data->subsidy,2)}}</td>
          </tr>
     </table>

     <h2>Technical Specifications & BOM</h2>
     <table border="1">
          <tr>
               <th style="width: 50px;">Sr.no.</th>
               <th>Item Description</th>
               <th style="width: 60px;">Qty</th>
               <th style="width: 100px;">Size</th>
               <th style="width: 150px;">Make</th>
          </tr>
          <tr>
               <td>1</td>
               <td>Solar Modules <b>({{$sales_data->penal_type_name}})</b></td>
               <td><b>{{$sales_data->penal_nos}} Nos</b></td>
               <td><b>{{$sales_data->panel_watt}} Watt</b></td>
               <td>
                    <b>
                         <table border="0">
                              <tr>
                              @foreach ($sales_data->company as $item)
                                   <td>
                                        <center>
                                             @if($item['logo'] != '')
                                             @php 
                                             $imagePath = public_path('upload/company/'.$item['logo']);
                                             $imageData = base64_encode(file_get_contents($imagePath));
                                             $imageMime = mime_content_type($imagePath);
                                             $imageSrc = 'data:' . $imageMime . ';base64,' . $imageData;
                                             @endphp
                                             <img src="{{$imageSrc }}" width="30" height="30"><br>
                                             @endif
                                             {{ $item['name'] }}
                                        </center>
                                   </td>
                              @endforeach
                              </tr>
                         </table>                              
                    </b>
               </td>
          </tr>
          <tr>
               <td>2</td>
               <td>String Type Grid Tied Inveter</td>
               <td><b>{{$sales_data->no_of_inveter}} Nos</b></td>
               <td><b>{{$sales_data->inveter_capacity}} KW</b></td>
               <td><b>{{$sales_data->inveter_company_id}}</b></td>
          </tr>

          @if($technicalSpecification->count() > 0)
          @php $sr = 2; $note = ''; @endphp
          @foreach($technicalSpecification as $techKey => $techValue)

          @if ($techValue['type'] !== 'note')
          @php $sr++; @endphp
          <tr>
               <td>{{ $sr }}</td>
               <td>{!! $techValue['itemDescription'] !!}</td>
               <td>{{ $techValue['qty'] }}</td>
               <td>{{ $techValue['size'] }}</td>
               <td>{{ $techValue['make'] }}</td>
          </tr>
          @else
          @php $note = $techValue['itemDescription']; @endphp
          @endif

          @endforeach

     </table>
     <p>Note: {!! $note !!}</p>

     @else

          <tr>
               <td>3</td>
               <td>Module Mounting Structure<br>- Hot Dip Galvanize Iron<br>- 80- Micron Zinc Coting<br>- Ss 304 Bolt.</td>
               <td>Set</td>
               <td><b>{{$sales_data->structure}}</b></td>
               <td>Reputed Make</td>
          </tr>
          <tr>
               <td>4</td>
               <td>AC Distribution Box</td>
               <td>Nos</td>
               <td>As Per Design</td>
               <td>L&T/Schneider Equivalent</td>
          </tr>
          <tr>
               <td>5</td>
               <td>DC Distribution Box</td>
               <td>Nos</td>
               <td>As Per Design</td>
               <td>L&T/Schneider Equivalent</td>
          </tr>
          <tr>
               <td>6</td>
               <td>AC Cables</td>
               <td>Mtr</td>
               <td>As Per Design</td>
               <td>Havells/Polycab/Equivalent</td>
          </tr>
          <tr>
               <td>7</td>
               <td>DC Cables</td>
               <td>Mtr</td>
               <td>As Per Design</td>
               <td>Havells/Polycab/Equivalent</td>
          </tr>
          <tr>
               <td>8</td>
               <td>LA Cables</td>
               <td>Mtr</td>
               <td>As Per Design</td>
               <td>Johnson/Kanbary/Equivalent</td>
          </tr>
          <tr>
               <td>9</td>
               <td>Earthing Kit</td>
               <td>Nos</td>
               <td>As Per Design</td>
               <td>Reputed Make</td>
          </tr>
          <tr>
               <td>10</td>
               <td>Lighting Arrester System</td>
               <td>Nos</td>
               <td>As Per Design</td>
               <td>Reputed Make</td>
          </tr>
          <tr>
               <td>11</td>
               <td>BOS</td>
               <td>Nos</td>
               <td>As Per Design</td>
               <td>Reputed Make</td>
          </tr>
     </table>
     <p>Note: - If any Condition, any Material is not available which is mentioned above for Supply, that Material will replace with Comparable Reputed Brand for Completion of Project without Prior Notice</p>
 @endif
     <h2>Terms And Conditions</h2>
     {!! $policy_data->policy !!}
     
     <p><b>Bank Account Details</b></p>
     <p><b>Name: -</b> {{$sales_data->holder_name}}<br /> <b>Bank Name : -</b> {{$sales_data->bank_name}}<br />
     <b>Account Number: -</b> {{$sales_data->account_number}} <br />
     <b>Rtgs / Neft Ifs Code: -</b> {{$sales_data->ifsc_number}} <br />
     <b>Bank Branch: -</b> {{$sales_data->branch}}</p>
</body>

</html>