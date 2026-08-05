<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Commission Report - {{ $agent->user->name }} {{ $agent->user->last_name }}</title>
    <style>
        @page {
            margin: 15px 15px 35px 15px;
        }

        html body {
            font-family: Roboto, 'Segoe UI', Tahoma, sans-serif;
            font-size: 12px;
            line-height: 1.1rem;
        }

        .header {
            margin-bottom: 20px;
            border-bottom: 1px solid #333;
            padding-bottom: 10px;
        }

        .header-row {
            display: flex;
            width: 100%;
        }

        .header-left {
            width: 50%;
            text-align: left;
        }

        .header-right {
            width: 50%;
            text-align: right;
        }

        .company-name {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 3px;
        }

        .report-title {
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .agent-info {
            font-size: 11px;
            margin-bottom: 3px;
        }

        .date-range {
            color: #666;
        }

        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .summary-table th,
        .summary-table td {
            border: 1px solid #333;
            padding: 8px;
            text-align: left;
        }

        .summary-table th {
            background-color: #f5f5f5;
            font-weight: bold;
        }

        .summary-table .label {
            font-weight: bold;
            width: 30%;
        }

        .summary-table .value {
            text-align: right;
            width: 20%;
        }

        .transaction-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .transaction-table th,
        .transaction-table td {
            border: 1px solid #333;
            padding: 6px;
            padding-top: 3px;
            text-align: left;
            font-size: 10px;
        }

        .transaction-table th {
            background-color: #f5f5f5;
            font-weight: bold;
            text-align: center;
        }

        .transaction-table .date-col {
            width: 10%;
        }

        .transaction-table .particular-col {
            width: 30%;
        }

        .transaction-table .files-col {
            width: 8%;
            text-align: center;
        }

        .transaction-table .kw-col {
            width: 8%;
            text-align: center;
        }

        .transaction-table .payable-col {
            width: 12%;
            text-align: right;
        }

        .transaction-table .paid-col {
            width: 12%;
            text-align: right;
        }

        .transaction-table .outstanding-col {
            width: 12%;
            text-align: right;
        }

        .opening-balance {
            background-color: #e8f4fd;
            font-weight: bold;
        }

        .commission-row {
            background-color: #f0f8f0;
        }

        .sub-commission-row {
            background-color: #fff8e1;
        }

        .installation-row {
            background-color: #f3e5f5;
        }

        .payment-row {
            background-color: rgb(222, 243, 222);
        }

        .footer {
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ccc;
            padding-top: 10px;
        }

        .no-data {
            text-align: center;
            font-style: italic;
            color: #666;
            padding: 20px;
        }

        .page-break {
            page-break-before: always;
        }

        .files-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .files-table th, .files-table td {
            border: 1px solid #333;
            padding: 3px;
            font-size: 10px;
        }
        .files-table th {
            background-color: #f5f5f5;
            text-align: center;
        }
        .text-end { text-align: right; }
        .text-center { text-align: center; }
        .summary-table-font {
            font-size: 11px;
        }
    </style>
</head>

<body>

<script type="text/php">
if (isset($pdf)) {
    $font = $fontMetrics->get_font('Arial', 'normal');
    $size = 9;
    $text = "Page {PAGE_NUM} of {PAGE_COUNT}";
    $width = $fontMetrics->get_text_width($text, $font, $size);
    $x = ($pdf->get_width() - $width) / 2;
    $y = $pdf->get_height() - 20;
    $pdf->page_text($x, $y, $text, $font, $size, [0,0,0]);
}
</script>

    <table class="summary-table" >
        <tr>
            <td colspan="2">
                <h3 style="margin:0px;text-align: center;">PAYOUT REPORT FOR {{ $agent->user->name }}
                    {{ $agent->user->last_name }}
                </h3>
            </td>
        </tr>
        <tr>
            <td style="width: 50%;padding:3px;vertical-align: middle;text-align:center;">
                <img src="{{ public_path('img/logo.png') }}" class="w-100" />
                <br /> {!! env('APP_OWNER_ADDRESS') !!}
            </td>
            <td style="width: 50%;border-right: 1px solid #000;border-spacing: 0;">
                Name : {{ env('APP_NAME') }} <br />
                Mobile : {{ env('APP_OWNER_MOBILE') }} <br />
                Email : {{ env('APP_OWNER_EMAIL') }} <br />
                GSTIN : {{ env('APP_OWNER_GST') }}
            </td>
        </tr>

    </table>



    <table class="summary-table" style="margin-bottom: 0px;">

            <tr>
                <td style="text-align: left;">
                    @if ($from && $to)
                        <strong>Period : </strong>{{ date('d-m-Y', strtotime($from)) }} to
                        {{ date('d-m-Y', strtotime($to)) }}
                    @elseif($from)
                        <strong>From Date : </strong>{{ date('d-m-Y', strtotime($from)) }}
                    @elseif($to)
                        <strong>Till Date : </strong>{{ date('d-m-Y', strtotime($to)) }}
                        @else
                        <strong>Till Date</strong>
                    @endif
                </td>
                <td style="text-align: right;width:50%;">
                    <strong> Generated on : </strong>{{ date('d-m-Y h:i:s A') }}
                </td>
            </tr>
    </table>

    @if ($type == 'summary')
    <table class="summary-table summary-table-font">
        <thead>
            <tr>
                <th colspan="6" style="text-align: center; background-color: #333; color: white;">SUMMARY</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="label">Total Files</td>
                <td class="value">{{ $stats['no_of_file'] ?? 0 }}</td>
                <td class="label">Total KW</td>
                <td class="value">{{ $stats['kw'] ?? '0.00' }}</td>
                <td class="label">Customer Pending</td>
                <td class="value">Rs. {{ $stats['customer_payment_pending'] ?? '0.00' }}</td>
            </tr>
            <tr>
                <td class="label">Commission</td>
                <td class="value">Rs. {{ $stats['commission'] ?? '0.00' }}</td>
                <td class="label">Sub Commission</td>
                <td class="value">Rs. {{ $stats['sub_commission'] ?? '0.00' }}</td>
                <td class="label">Installation</td>
                <td class="value">Rs. {{ $stats['installation'] ?? '0.00' }}</td>
            </tr>
            <tr>
                <td class="label">Total Payable</td>
                <td class="value"><strong>Rs. {{ $stats['total_payable'] ?? '0.00' }}</strong></td>
                <td class="label">Total Paid</td>
                <td class="value"><strong>Rs. {{ $stats['total_paid'] ?? '0.00' }}</strong></td>
                <td class="label">Pending Payout</td>
                <td class="value"><strong>Rs. {{ $stats['pending_payout'] ?? '0.00' }}</strong></td>
            </tr>
        </tbody>
    </table>

    @if (!empty($stats['lines']))
        <table class="transaction-table">
            <thead>
                <tr>
                    <th class="date-col">Date</th>
                    <th class="particular-col">Particular</th>
                    <th class="files-col">Files</th>
                    <th class="kw-col">KW</th>
                    <th class="payable-col">Payable</th>
                    <th class="paid-col">Paid</th>
                    <th class="outstanding-col">Outstanding</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($stats['lines'] as $line)
                    <tr
                        class="
                        @if (strpos($line['particular'], 'Opening Balance') !== false) opening-balance
                        @elseif(strpos($line['particular'], 'Commission') !== false && strpos($line['particular'], 'Sub Commission') === false) commission-row
                        @elseif(strpos($line['particular'], 'Sub Commission') !== false) sub-commission-row
                        @elseif(strpos($line['particular'], 'Installation') !== false) installation-row
                        @elseif($line['paid'] !== '-') payment-row @endif
                    ">
                        <td class="date-col">{{ $line['date'] }}</td>
                        <td class="particular-col">{{ $line['particular'] }}</td>
                        <td class="files-col">{{ $line['files'] }}</td>
                        <td class="kw-col">{{ $line['kw'] }}</td>
                        <td class="payable-col">{{ $line['payable'] }}</td>
                        <td class="paid-col">{{ $line['paid'] }}</td>
                        <td class="outstanding-col">{{ $line['outstanding'] }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr style="background-color: #f0f0f0; font-weight: bold;">
                    <td class="date-col">TOTAL</td>
                    <td class="particular-col"></td>
                    <td class="files-col">{{ $stats['no_of_file'] ?? 0 }}</td>
                    <td class="kw-col">{{ $stats['kw'] ?? '0.00' }}</td>
                    <td class="payable-col">Rs. {{ $stats['total_payable'] ?? '0.00' }}</td>
                    <td class="paid-col">Rs. {{ $stats['total_paid'] ?? '0.00' }}</td>
                    <td class="outstanding-col">Rs. {{ $stats['pending_payout'] ?? '0.00' }}</td>
                </tr>
            </tfoot>
        </table>
    @else
        <div class="no-data">
            No transaction data available for the selected period.
        </div>
    @endif

    @endif

     @if ($type == 'files')

    @if(!empty($sales) && $sales->count() > 0)

        <h3 style="margin:8px 0 8px 0; text-align:center;">Consumer List</h3>
        <table class="files-table">
            <thead>
                <tr>
                    <th style="width:3%;">#</th>
                    <th style="width:10%;">Invoice Date</th>
                    <th style="width:25%;">Consumer</th>
                    <th style="width:5%;">KW</th>
                    <th style="width:18%;">Agent</th>
                    <th style="width:14%;" class="text-end">Commission</th>
                    <th style="width:16%;" class="text-end">Sub Commission</th>
                    <th style="width:14%;" class="text-end">Installation</th>
                    <th style="width:14%;" class="text-end">Pending Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sales as $i => $s)
                    @php
                        $kw = (float)($s->installation->total_kv ?? $s->register_kw ?? 0.0);

                        $commissionForAgent = ((int)($s->agent_sales_person_id ?? 0) === (int)($agent->id)) ? (float)($s->commission_amount ?? 0) * $kw : 0.0;
                        $subCommissionForAgent = 0.0;
                        if (isset($selectedCompanyId) && !empty($selectedCompanyId)) {
                            if ((int)($s->agent_sales_person_id ?? 0) !== (int)($agent->id)) {
                                $subCommissionForAgent = (float)($s->sub_commission_amount ?? 0) * $kw;
                            }
                        }
                        $installerUserId = (int)($s->installation_asian_person ?? 0);
                        $agentUserId = (int)($agent->user_id ?? $agent->user->id ?? 0);
                        $installationForAgent = ($installerUserId === $agentUserId && (string)($s->installation_done) === '1') ? (float)($s->installation_amount ?? 0) * $kw : 0.0;
                    @endphp
                    <tr>
                        <td class="text-center">{{ $i+1 }}</td>
                        <td class="text-center">{{ $s->invoice_date ? date('d-m-Y', strtotime($s->invoice_date)) : '-' }}</td>
                        <td>{{ ucwords(strtolower($s->consumer_name ?? '-')) }}</td>
                        <td class="text-center">{{ number_format($kw, 2) }}</td>
                        <td class="text-center">{{ ucwords(strtolower($s->agentsalesperson->name ?? '-')) }}</td>
                        <td class="text-end">Rs. {{ number_format($commissionForAgent, 2) }}</td>
                        <td class="text-end">Rs. {{ number_format($subCommissionForAgent, 2) }}</td>
                        <td class="text-end">Rs. {{ number_format($installationForAgent, 2) }}</td>
                        <td class="text-end">Rs. {{ number_format($s->pending_amonut, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    @endif

    <div class="footer">
        <p>This report was generated on {{ date('d-m-Y h:i:s A') }} by {{ Auth::user()->name ?? 'System' }}</p>
    </div>
</body>

</html>
