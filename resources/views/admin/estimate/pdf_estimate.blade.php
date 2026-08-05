<!DOCTYPE html>
<html>

<head>
     <title>{{$title}}</title>
     <style>
          html body {
               font-family: Roboto, 'Segoe UI', Tahoma, sans-serif;
               font-size: 83%;
               line-height: 1.1rem;
          }

          h4 {
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
               text-align: right !important;
               padding-right: 2px;
          }

          #estimate_item .bg-head {
               background-color: #d7d7d7;
               border: none;
          }

          a {
               text-decoration: none;
               color: #000;
          }

          footer {
               position: fixed;
               bottom: -60px;
               left: 0px;
               right: 0px;
               height: 120px;
          }
     </style>
</head>

<body>
     <h4>Estimate</h4>
     <table>
          <tr>
               <td colspan="3">
                    <table id="estimate" style="border:none">
                         <tr>
                              <th style="text-align: left;">{{$estimate->company->user->company_name}}</th>
                         </tr>
                         <tr>
                              <td>{{$estimate->company->user->name}} {{$estimate->company->user->last_name}}</td>
                         </tr>
                         @if(!is_null($estimate->company->state_id) && $estimate->company->state_id != 0)
                         <tr>
                              <td>{{$estimate->company->state->state_name}}</td>
                         </tr>
                         @endif
                         @if(!is_null($estimate->company->city_id) && $estimate->company->city_id != 0)
                         <tr>
                              <td>{{$estimate->company->city->city_name}}</td>
                         </tr>
                         @endif
                         <tr>
                              <td>{{$estimate->company->address}}</td>
                         </tr>
                         @if(!is_null($estimate->company->user->mobile))
                         <tr>
                              <td><b>Mo.No. : </b>{{$estimate->company->user->mobile}}</td>
                         </tr>
                         @endif
                    </table>
               </td>
               <td colspan="3">
                    <table id="detail" style="border:none;">
                         @if(!is_null($estimate->leadMaster->company_name))
                         <tr>
                              <th style="text-align: right;">{{$estimate->leadMaster->company_name}}</th>
                         </tr>
                         @endif
                         <tr>
                              <td>{{$estimate->leadMaster->name}} {{$estimate->leadMaster->last_name}}</td>
                         </tr>
                         @if($estimate->leadMaster->state_id != '' && $estimate->leadMaster->state_id != 0)
                         <tr>
                              <td>{{$estimate->leadMaster->state->state_name}}</td>
                         </tr>
                         @endif
                         @if($estimate->leadMaster->city_id != '' && $estimate->leadMaster->city_id != 0)
                         <tr>
                              <td>{{$estimate->leadMaster->city->city_name}}</td>
                         </tr>
                         @endif
                         @if(!is_null($estimate->leadMaster->pincode))
                         <tr>
                              <td>{{$estimate->leadMaster->pincode}}</td>
                         </tr>
                         @endif
                         @if(!is_null($estimate->leadMaster->mobile))
                         <tr>
                              <td><b>Mo.No. : </b>{{$estimate->leadMaster->mobile}}</td>
                         </tr>
                         @endif
                    </table>
               </td>
          <tr>
          <tr>
               <td colspan="6" style="border-bottom: 1px solid #000;"></td>
          </tr>
          <tr>
               <td colspan="3">
                    <table style="border:none; text-align: left; padding-left: 3px;">
                         <tr>
                              <td><b>Estimate Title : </b>{{$estimate->estimate_title}}</td>
                         </tr>
                         <tr>
                              <td><b>Estimate Date : </b>{{date('d-m-Y', strtotime($estimate->estimate_date))}}</td>
                         </tr>
                         <tr>
                              <td><b>Expiry Date : </b>{{date('d-m-Y', strtotime($estimate->expiry_date))}}</td>
                         </tr>
                    </table>
               </td>
               <td colspan="3">
                    <table style="border:none; text-align: right; padding-right: 3px;">
                         <tr>
                              <td><b>Assign User : </b>{{$estimate->assign->user->name}} {{$estimate->assign->user->last_name}}</td>
                         </tr>
                         <tr>
                              <td><b>Estimate by : </b>{{$estimate->user->name}} {{$estimate->user->last_name}}</td>
                         </tr>
                         <tr>
                              <td><b>Generated Date : </b>{{$estimate->created_at->format('d-m-Y')}}</td>
                         </tr>
                    </table>
               </td>
          </tr>
     </table>
     <table style="margin-top: 5px;" id="estimate_item">
          <thead class="bg-head">
               <tr>
                    <th colspan="2">#</th>
                    <th>Product</th>
                    <th>Quantity</th>
                    <th>Unit</th>
                    <th>Rate</th>
                    <th style="text-align: right; padding-right: 5px;">Total</th>
               </tr>
          </thead>
          <tbody style="text-align: center;">
               @foreach($estimate->estimateItem as $key => $value)
               <tr>
                    <td colspan="2">{{$key+1}}</td>
                    <td>{{$value->product->product_name}}</td>
                    <td>{{$value->quantity}}</td>
                    <td>{{$value->unit->unit_name}}</td>
                    <td>{{number_format($value->rate,2)}}</td>
                    <td style="text-align: right; padding-right: 5px;">{{number_format($value->total,2)}}</td>
               </tr>
               @endforeach
               <tr>
                    <td colspan="7" style="border-top: 1px solid #000000;"></td>
               </tr>
               <tr>
                    <td colspan="5"></td>
                    <th>SUB TOTAL</th>
                    <td style="text-align: right; padding-right: 5px;">{{number_format($estimate->subtotal,2)}}</td>
               </tr>
               <tr>
                    <td colspan="5"></td>
                    <th>DISCOUNT({{$estimate->discount}}%)</th>
                    <td style="text-align: right; padding-right: 5px;">{{number_format(($estimate->subtotal) - ($estimate->total),2) }}</td>
               </tr>
               <tr>
                    <td colspan="5"></td>
                    <th>TOTAL</th>
                    <td style="text-align: right; padding-right: 5px; border-top: 1px solid #000000;">{{number_format($estimate->total,2)}}</td>
               </tr>
               <tr>
                    <td colspan="7" style="border-bottom: 1px solid #000000;"></td>
               </tr>
               <tr>
                    <td colspan="7" style="text-align: left; padding-left: 5px; border-bottom: 1px solid #000000;"><b>Notes : </b>{{$estimate->remark}}</td>
               </tr>

               <tr>
                    <td colspan="7" style="text-align: left; padding-left: 5px;"><b>Terms & Conditions : </b></td>
               </tr>
               <tr>
                    <td colspan="7" style="text-align: left; padding-left: 5px;">{!! $estimate->company->terms_conditions !!}</td>
               </tr>
               
          </tbody>
     </table>

     <footer>
          <div style="padding: 0px 5px 0px 5px;">
               <p style="text-align:right;">
                    For <b>Solar Solutions</b>
                    <br />
                    <br />
                    Authorised Signatory

               </p>
               <p style="text-align: center"> *This is a Computer Generated Estimate </p>
          </div>
     </footer>
</body>

</html>