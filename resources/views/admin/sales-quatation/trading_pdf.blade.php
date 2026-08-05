<!DOCTYPE html>
<html>

<head>
     <title>{{$title}}</title>
     <style>
          html body {
               font-family: Roboto, 'Segoe UI', Tahoma, sans-serif;
               font-size: 14px;
               line-height: 1.2rem;
          }
          h4,
          h2 {
               text-align: center;
          }
          table {
               width: 100%;
               border-spacing: 0;
          }
          td {
               padding: 3px;
          }

          .page-break {
               page-break-before: always;
          }
          body {
               padding: 50px;
               padding-top: 120px;
               padding-bottom: 120px;
               background-image: url({{ public_path('img/pdf-bg.png')}});
               background-size: cover;
               background-repeat: no-repeat;
               background-position: center;
          }
          .text_right{
               text-align: right;
          }
          .text_center{
               text-align: center;
          }
          .content-wrapper {
               position: relative;
               margin-top: -105px;
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
     <div class="content-wrapper">
          <table>
               <tr>
                    <td class="text_right" style="padding-left: 5px;">
                         <b>{{ env('APP_NAME') }} <br />
                         {!! env('APP_OWNER_ADDRESS') !!}
                         <br />
                         +91 9727272949
                              <br /> GSTIN: {{ env('APP_OWNER_GST') }}</b>
                    </td>
               </tr>
          </table>
     </div>
     <hr>
     <table>
          <tr>
               <td style="width: 50%;"></td>
               <td class="text_right" style="padding-left: 5px;width: 50%;">
                    <b>{{$sales_data->name}}<br>
                    {{$sales_data->address}}
                    <br>{{$sales_data->mobile}}
                    @if($sales_data->gst_no != "") <br>GSTIN: {{$sales_data->gst_no}} @endif</b>
               </td>
          </tr>
          <tr>
               <td>
                    <h1>Pro-Forma Invoice # {{str_pad($sales_data->id, 2, '0', STR_PAD_LEFT)}}</h1>
               </td>
          </tr>
     </table>
     <table>
          <tr>
               <td><b>Your Reference:</b><br>{{$sales_data->reference}}</td>
               <td><b>Quotation Date:</b><br>{{date('d-m-Y', strtotime($sales_data->created_at))}}</td>
               <td><b>Salesperson:</b><br>{{$sales_data->agent_name}}</td>
          </tr>
     </table><br>
     <table border="1">
          <tr>
               <th>Sr. no.</th>
               <th>DESCRIPTION</th>
               <th>NOS</th>
               <th>RATE</th>
               <th>TAXES</th>
               <th>AMOUNT</th>
          </tr>
          @php $i=1;
          $total = $finalsubTota =$finalsub=0;
          @endphp
          @foreach($meta_data as $value)
          <tr>
               <td class="text_center">{{$i;}}</td>

               @if($value->type == "Item")
                    <td class="text-nowrap">{{$value->item->name}} 
                    @if($value->item->hsn_code != "")<br>HSN/SAC Code: {{$value->item->hsn_code}}  @endif
                    </td>
                    @else
                    <td class="text-nowrap">{{getItemGropName($value,1)}} 
                         @if($value->itemGroup->hsn_code != "") <br>HSN/SAC Code: {{$value->itemGroup->hsn_code}} @endif
                    </td>
                    @endif

             
               <td class="text_center">{{$value->nos}}</td>
               <td class="text_right"><span style="font-family: DejaVu Sans; sans-serif;">&#8377;</span> {{number_format($value->rate,2)}}</td>
               <td class="text_right">{{$sales_data->gst == 'Extra' ? 'GST ' . $value->item_gst . '%' : 'GST 0%'}}</td>
               <td class="text_right"><span style="font-family: DejaVu Sans; sans-serif;">&#8377;</span> {{number_format($value->nos * $value->rate,2)}}</td>
          </tr>
          @php
          if($sales_data->gst == 'Extra'){
          $sub = $value->nos * $value->rate;
          $subTota = $sub * $value->item_gst / 100;
          $subTotal = $sub + $subTota;
          $total += $subTotal;
          $finalsubTota +=$subTota;
          $finalsub +=$sub;
          }else{
          $subTotal = $value->nos * $value->rate;
          $total += $subTotal;
          $finalsub +=$subTotal;
          }
          $i++;
          @endphp
          @endforeach
          <tr>
               <td colspan="3"></td>
               <td colspan="2" class="text_right">Untaxed Amount</td>
               <td class="text_right"><span style="font-family: DejaVu Sans; sans-serif;">&#8377;</span> {{number_format($finalsub,2)}}</td>
          </tr>
          <tr>
               <td colspan="3"></td>
               @if($sales_data->gst == 'Extra')
               <td colspan="2" class="text_right">SGST on <span style="font-family: DejaVu Sans; sans-serif;">&#8377;</span> {{number_format($finalsub,2)}}</td>
               @else
               <td colspan="2" class="text_right">SGST</td>
               @endif
               <td class="text_right"><span style="font-family: DejaVu Sans; sans-serif;">&#8377;</span> {{$sales_data->gst == 'Extra' ? number_format($finalsubTota/2,2): '0.00'}}</td>
          </tr>
          <tr>
               <td colspan="3"></td>
               @if($sales_data->gst == 'Extra')
               <td colspan="2" class="text_right">CGST on <span style="font-family: DejaVu Sans; sans-serif;">&#8377;</span> {{number_format($finalsub,2)}}</td>
               @else
               <td colspan="2" class="text_right">CGST</td>
               @endif
               <td class="text_right"><span style="font-family: DejaVu Sans; sans-serif;">&#8377;</span> {{$sales_data->gst == 'Extra' ? number_format($finalsubTota/2,2): '0.00'}}</td>
          </tr>
          <tr>
               <td colspan="3"></td>
               <td colspan="2" class="text_right">Total</td>
               <td class="text_right"><b><span style="font-family: DejaVu Sans; sans-serif;">&#8377;</span> {{number_format($total,2)}}</b></td>
          </tr>
     </table>

     <p><b>Bank Account Details</b></p>
     <p><b>Name: -</b> {{$sales_data->holder_name}}<br /> <b>Bank Name : -</b> {{$sales_data->bank_name}}<br />
     <b>Account Number: -</b> {{$sales_data->account_number}} <br />
     <b>Rtgs / Neft Ifs Code: -</b> {{$sales_data->ifsc_number}} <br />
     <b>Bank Branch: -</b> {{$sales_data->branch}}</p>
     <div class="page-break"></div>
     <h2>Terms And Conditions</h2>
     {!! $policy_data->policy !!}
</body>

</html>