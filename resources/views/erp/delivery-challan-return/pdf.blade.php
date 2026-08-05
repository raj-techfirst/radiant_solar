<!DOCTYPE html>
<html lang="en">

<head>
     <meta charset="UTF-8">
     <meta name="viewport" content="width=device-width, initial-scale=1.0">
     <title>{{$title}}</title>
     <style>
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
               height: 120px;
          }
     </style>
</head>

<body>
     <h3>Delivery Challan Return </h3>


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
                              <td>Address : {{ env('APP_OWNER_ADDRESS') }} </td>
                         </tr>
                         <tr>
                              <td>GSTIN : {{ env('APP_OWNER_GST') }}</td>
                         </tr>
                    </table>
               </td>
               <td style="width: 50%;padding:3px;vertical-align: top;text-align:center;border-bottom: 1px solid #000;border-spacing: 0;">
                    <img src="{{ public_path('img/logo.png') }}" class="w-100" />
               </td>
          </tr>
          <tr>
               <td style="border-right: 1px solid #000;border-spacing: 0;width: 50%;">
                    <table style="border:none;">
                         <tr>
                              <td>

                              {{ $data->project->consumer_name ?? 'N/A' }} <br>
                    {{ $data->project->address ?? 'N/A' }},
                    {{ $data->project->taluka->name ?? 'N/A' }},
                    {{ $data->project->district->name ?? 'N/A' }},
                    {{ $data->project->pin_code ?? 'N/A' }} <br>
                    Mobile : {{ $data->project->contact_number ?? '' }} <br>
                    Email : {{ $data->project->email ?? '' }} <br>
                    GSTIN/UIN : {{ $data->project->gst_number ?? '' }}
                              </td>
                         </tr>

                    </table>
               </td>
               <td style="width: 50%;padding:3px;vertical-align: top;">
               Challan No. : {{ $data->challan_number }} <br>
               Date : {{ date('d-m-Y', strtotime($data->created_at)) }}
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
          @foreach($data->delivery_challan_return_meta as $key => $pom)
               <tr>
                    <td style="text-align: center;">{{ $key+1 }}</td>
                    @if($pom->type == "Item")
                    <td class="text-nowrap" colspan="4" style="text-align: left;">{{$pom->item->name}}</td>
                    <td style="text-align: center;">{{$pom->quantity}} {{$pom->item->unit->unit_name}}</td>
                    @else
                    <td class="text-nowrap" colspan="4" style="text-align: left;">{{ getItemGropName($pom,1) }}</td>
                    <td style="text-align: center;">{{$pom->quantity}} {{$pom->itemGroup->unit->unit_name}}</td>
                    @endif
               </tr>
               @endforeach
          </tbody>
     </table>
</body>

</html>