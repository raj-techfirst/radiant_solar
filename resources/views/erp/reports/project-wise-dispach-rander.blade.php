<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Project Wise Dispach Report </title>
    <style>
        @page {
            margin: 15px;
        }

        html body {
            font-family: Roboto, 'Segoe UI', Tahoma, sans-serif;
            font-size: 83%;
            line-height: 1.1rem;
            margin: 0px;
            padding: 0px;
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
    <h3>Project Wise Dispach Report</h3>

    <table style="margin-bottom: 15px;">
        <tr>
            <td style="border-bottom: 1px solid #000;width: 80%;border-right: 1px solid #000;border-spacing: 0;">
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
            <td
                style="width: 20%;padding:3px;vertical-align: middle;text-align:center;border-bottom: 1px solid #000;border-spacing: 0;">
                <img src="{{ public_path('img/logo.png') }}" class="w-100" /><br /><br /> Date : {{ date('d-m-Y') }}
            </td>

        </tr>

    </table>


    @if (count($data) > 0)
        <table class="table table-hover table-bordered" id="estimate_item">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Consumer No.</th>
                    <th>Consumer</th>
                    <th>Mobile</th>
                    <th>System Cost</th>
                    <th>Taxabale amount</th>
                    <th>GST Amount</th>
                    <th>Total Amount</th>
                </tr>
            </thead>
            <tbody>
                @php
                    // $total_value = $total_gst = $total = 0;
                    $i=1;
                @endphp
                @foreach ($data as $key => $value)
                    <tr>
                        <td>{{ $i++ }}</td>
                        <td>{{ $value->consumer_number }}</td>
                        <td>{{ $value->consumer_name }}</td>
                        <td>{{ $value->consumer_mobile }}</td>
                        <td style="text-align:right;">{{ number_format($value->system_cost, 2) }}</td>
                        <td style="text-align:right;">{{ number_format($value->taxable_amount, 2) }}</td>
                        <td style="text-align:right;">{{ number_format($value->gst_amount, 2) }}</td>
                        <td style="text-align:right;">{{ number_format($value->total_amount, 2) }}</td>
                    </tr>
                    {{-- @php
                        $total_value += $value->taxabale_amount;
                        $total_gst += $value->gst_amount;
                        $total += $value->total_amount;
                    @endphp --}}
                @endforeach
                {{-- <tr>
                    <th colspan="7" style="text-align:right;"><b>Total</b></th>
                    <th style="text-align:right;">{{ number_format($total_value, 2) }}</th>
                    <th style="text-align:right;">{{ number_format($total_gst, 2) }}</th>
                    <th style="text-align:right;">{{ number_format($total, 2) }}</th>
                </tr> --}}
            </tbody>
        </table>
    @else
        <h4>No Data!</h4>
    @endif


</body>

</html>
