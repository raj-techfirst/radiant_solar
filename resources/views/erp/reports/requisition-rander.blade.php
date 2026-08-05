@if($type == "pdf")
<!DOCTYPE html>
<html lang="en">

<head>
     <meta charset="UTF-8">
     <meta name="viewport" content="width=device-width, initial-scale=1.0">
     <title>Requisition Report</title>
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
               border-bottom: 1px solid #000;

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

     <h3>Requisition Report</h3>
     <table style="margin-bottom: 15px;">
          <tr>
               <td style="border-bottom: 1px solid #000;width: 70%;border-right: 1px solid #000;border-spacing: 0;">
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
                              <td>Address : {!! env('APP_OWNER_ADDRESS') !!} </td>
                         </tr>
                         <tr>
                              <td>GSTIN : {{ env('APP_OWNER_GST') }}</td>
                         </tr>
                    </table>
               </td>
               <td style="width: 30%;padding:3px;vertical-align: middle;text-align:center;border-bottom: 1px solid #000;border-spacing: 0;">
                    <img src="{{ public_path('img/logo.png') }}" class="w-100" /><br /><br /> Date : {{ date('d-m-Y') }}
               </td>
          </tr>
     </table>
     @endif

     <div class="card">
          <div class="row">
               <div class="col-lg-12 col-12">
                    @if(count($data) > 0)
                    <table class="table table-hover table-bordered table-sm" id="estimate_item">
                         <thead>
                              <tr>
                                   <th class="text-center">#</th>
                                   <th class="text-start">Product</th>
                                   <th class="text-center">Unit</th>
                                   <th class="text-center">WHS. Stock</th>
                                   <th class="text-center">INS. Stock</th>
                                   <th class="text-center">CUR. Stock</th>
                                   <th class="text-center">Req. Stock</th>
                                   <th class="text-center">Sort Stock</th>
                              </tr>
                         </thead>
                         <tbody>
                              @php $cat = ''; @endphp
                              @foreach($data as $key => $value)

                              @php if($value->type == 'ItemGroup'){ $ncat = $value->group_type; } else{ $ncat = $value->category_name; } @endphp

                              @if($cat != $ncat)
                              <tr>
                                   <td colspan="8" class="bg-light-warning">
                                        @if($type == "pdf") <h3 style="text-align: left;padding:5px;font-weight: 600;margin: 0px;background-color:#ef7f1bad;">@endif
                                             <b>{{ ucfirst($ncat) }}</b>
                                             @if($type == "pdf")
                                        </h3>@endif
                                   </td>
                              </tr>
                              @endif
                              <tr @if($value->sort_stock > 0) class="text-danger" @endif @if($value->sort_stock > 0 && $type == "pdf") style="color:red;" @endif>
                                   <td class="text-center">{{$key+1}}</td>
                                   <td>
                                        @if ($value->type == "Item")
                                        {{$value->item_code .' '. $value->item_name}}
                                        @elseIf ($value->group_type == "panel")
                                        {{ $value->penal_watt . 'W Solar Module (' . $value->penal_company . ' - ' . $value->penal_type . ' | '. $value->p_type .')' }}
                                        @else
                                        {{ $value->inveter_kw . ' KW Inverter (' . $value->invarter_name  . ' | ' . $value->inverter_type . ')' }}
                                        @endIf
                                   </td>
                                   <td style="text-align: center;">{{$value->unit_name}}</td>
                                   <td style="text-align: center;">{{$value->current_stock}}</td>
                                   <td style="text-align: center;">{{$value->installer_stock}}</td>
                                   <td style="text-align: center;">{{$value->total_current_stock}}</td>
                                   <td style="text-align: center;">{{$value->require_qty}}</td>
                                   <td style="text-align: center;">{{$value->sort_stock}}</td>
                              </tr>
                              @php $cat = $ncat; @endphp
                              @endforeach
                         </tbody>
                    </table>
                    @else
                    <h4>No Data!</h4>
                    @endif
               </div>
          </div>
     </div>

     @if($type == "pdf")
</body>

</html>
@endif