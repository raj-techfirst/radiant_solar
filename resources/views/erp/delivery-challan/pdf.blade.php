<!DOCTYPE html>
<html lang="en">

<head>
     <meta charset="UTF-8">
     <meta name="viewport" content="width=device-width, initial-scale=1.0">
     <title>{{$title}}</title>
     <style>
          @page {
               margin: 15px;
          }

          html body {
               font-family: Roboto, 'Segoe UI', Tahoma, sans-serif;
               font-size: 83%;
               line-height: 1.1rem;
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
               height: 130px;
          }

          .srno tr td {
               padding: 5px;
               border: 1px solid #000 !important;
               border-spacing: 0;
          }
     </style>
</head>

<body>
     <h3>Delivery Challan</h3>
     <table>
          <tr>
               <td style="border-bottom: 1px solid #000;width: 50%;border-right: 1px solid #000;border-spacing: 0;">
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
                              <td>Address : {{ env('APP_OWNER_ADDRESS_NO_BR') }} </td>
                         </tr>
                         <tr>
                              <td>GSTIN : {{ env('APP_OWNER_GST') }}</td>
                         </tr>
                    </table>
               </td>
               <td style="width: 50%;padding:3px;vertical-align: top;text-align:center;border-bottom: 1px solid #000;border-spacing: 0;">
                   <br/> <img src="{{ public_path('img/logo.png') }}" class="w-100" />
               </td>
          </tr>
          <tr>
               <td style="border-right: 1px solid #000;border-spacing: 0;width: 50%;">
                    <table style="border:none;">
                         <tr>
                              <td>
                                   @php if ($data->issue_type == "project") {
                                   $type = 'Project';
                                   $dis_name = $data->project->consumer_name;
                                   } else if ($data->issue_type == "warehouse") {
                                   $type = 'Warehouse From';
                                   $dis_name = $data->warehouse_from->name;
                                   } else if ($data->issue_type == "trading") {
                                   $type = 'B2B';
                                   $dis_name = $data->salesQuatation->name;
                                   }
                                   else {
                                   $type = 'Installer';
                                   $dis_name = $data->installer->name . ' ' . $data->installer->last_name;
                                   }
                                   @endphp

                                   {{ $dis_name ?? '' }} <br>

                                   @if ($data->issue_type == "project")
                                   {{ $data->project->address ?? '' }},
                                   {{ $data->project->taluka->name ?? '' }},
                                   {{ $data->project->district->name ?? '' }},
                                   {{ $data->project->pin_code ?? '' }} <br>
                                   Mobile : {{ $data->project->contact_number ?? '' }} <br>
                                   Email : {{ $data->project->email ?? '' }} <br>
                                   GSTIN/UIN : {{ $data->project->gst_number ?? '' }}

                                   @elseif($data->issue_type == "warehouse")
                                   {{ $data->warehouse_from->address }}<br />
                                   {{ $data->warehouse_from->contact_person }}<br />
                                   {{ $data->warehouse_from->contact_person_no }}<br />

                                   @elseif($data->issue_type == "trading")
                                   {{ $data->salesQuatation->address }}<br />
                                   Mobile : {{ $data->salesQuatation->mobile }}<br />
                                   GSTIN/UIN : {{ $data->salesQuatation->gst_no ?? '' }}
                                   @else

                                   {{ $data->installer->mobile }} <br />
                                   {{ $data->installer->email }}
                                   @endif
                              </td>
                         </tr>
                    </table>
               </td>
               <td style="width: 50%;padding:3px;vertical-align: top;line-height:1.6;">
                    Challan No. : {{ $data->challan_number }} <br />
                    Date : {{ date('d-m-Y', strtotime($data->created_at)) }}<br />
                    Vehicle No. : {{ $data->vehicle_no }}
               </td>

          </tr>
     </table>

     <table style="width: 100%;margin-top:15px;" id="estimate_item">
          <thead class="bg-head">
               <tr>
                    <th style="width: 5%; text-align: center; ">#</th>
                    <th colspan="4" style="text-align: center;">Description of Goods</th>
                    <th style="width: 15%; text-align: center;">Qty.</th>
               </tr>
          </thead>
          <tbody>
               @foreach($data->delivery_challan_meta as $key => $pom)
               <tr>
                    <td style="text-align: center;">{{ $key+1 }}</td>
                    @if($pom->type == "Item")
                    <td class="text-nowrap" colspan="4" style="text-align: left;">{{$pom->item->name}}</td>
                    <td style="text-align: center;">{{$pom->quantity}} {{$pom->item->unit->unit_name}}</td>
                    @else
                    <td class="text-nowrap" colspan="4" style="text-align: left;">
                         {{ getItemGropName($pom,1) }}
                    </td>
                    <td style="text-align: center;">{{$pom->quantity}} {{$pom->itemGroup->unit->unit_name}}</td>
                    @endif
               </tr>
               @endforeach
          </tbody>
     </table>

     @if($type == 'Installer')

     @php $salesOrders = getSalesOrderUsingIds($data->sales_master_id); @endphp
     @if(count($salesOrders) > 0)
     <br /><br />
     @foreach($salesOrders as $k => $v)
     <table style="width: 100%;margin-top:15px;" id="estimate_item">
          <thead class="bg-head">
               <tr>
                    <td style="border-bottom: 1px solid #000;border-spacing: 0;">
                         <b>Consumer Name : </b> {{ $v->consumer_name  }}
                    </td>
                    <td style="width:180px;text-align:center;border-bottom: 1px solid #000;border-spacing: 0;">
                         <b>Mobile : </b> {{ $v->contact_number }}
                    </td>
                    <td style="width:100px;text-align:center;border-bottom: 1px solid #000;border-spacing: 0;">
                         <b>K/W : </b> {{ $v->register_kw  }}
                    </td>
               </tr>
               <tr>
                    <td colspan="3">
                         <b>Address : </b> {{ $v->address }}
                    </td>
               </tr>
          </thead>
     </table>
     <table style="width: 100%;" id="estimate_item">
          <tr>
               <th style="width: 5%; text-align: center; ">#</th>
               <th style="text-align: center;">Item</th>
               <th style="width: 15%; text-align: center;">Qty.</th>
          </tr>
          <tr>
               <td style="text-align: center;">1</td>
               <td>{{ $v->salesquatationfull->penalWatt->name }}W Solar Module ({{ $v->panel->name }} - {{$v->salesquatationfull->penalType->name}})</td>
               <td style="text-align: center;">{{ $v->salesquatationfull->penal_nos }} Nos</td>
          </tr>
          <tr>
               <td style="text-align: center;">2</td>
               <td>{{ $v->salesquatationfull->inveter_capacity }} KW Inverter ({{ $v->inveter->name }})</td>
               <td style="text-align: center;">{{ $v->salesquatationfull->no_of_inveter }} Nos</td>
          </tr>
     </table>
     @endforeach
     @endif
     @endif

     @if($type == 'B2B')
     <h3 style="margin-top: 35px;margin-bottom: 0px;">Serial Numbers</h3>
     <table style="width: 100%;border:0px !important">
          <tbody>
               @foreach($data->delivery_challan_meta as $key => $pom)
               @if($pom->type != "Item" && $pom->serial_numbers_count->count() > 0)
               <tr>
                    <td class="text-nowrap bg-head" colspan="4" style="text-align: left;">
                        <h4 style="margin-bottom: 0px;text-align:left;" >{{ getItemGropName($pom,1) }}</h4>
                         <table style="width: 100%;padding:0px;margin-top:5px;" class="srno">
                              <tr>
                                   @php $k = 1; @endphp
                                   @foreach($pom->serial_numbers_count as $sk => $sv)
                                   <td style="text-align:center">{{ $sv->serialNumbers->serial_number }}</td>
                                   @if($k%4 == 0 )
                              </tr>
                              <tr>
                                   @endif
                                   @php $k++; @endphp
                                   @endforeach
                              </tr>
                         </table>
                    </td>
               </tr>
               @endif
               @endforeach
          </tbody>
     </table>
     @endif



     <footer>
          <div style="padding: 0px 5px 0px 5px;">
               <div style="text-align:right;">
                    <p><b>For, {{ env('APP_NAME') }}</b></p>
                    <br />
                    <br />
                    <p><b>(Authorised Signatory)</b></p>
               </div>
               <p style="text-align: center">*This is computer generated document hense authorized signature is not required.</p>
          </div>
     </footer>

</body>

</html>