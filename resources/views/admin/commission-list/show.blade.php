@extends('layouts.app')
@section('title', 'Commission Payout Report')
@section('content')
    <div class="row">
        <div class="col-12 mb-1">
            <h4 class="content-header-title float-start"><b>Payout Report</b> of
                {{ ucwords(strtolower($agent->user->name . ' ' . $agent->user->last_name)) }}</h4>
            <div class="float-end">
                <a href="{{ route('commission-list.pdf', $agent->id) }}?from_date={{ urlencode($from ?? '') }}&to_date={{ urlencode($to ?? '') }}&type=summary"
                   class="btn btn-sm btn-info" target="_blank">
                    <i class="fas fa-print"></i> Download PDF
                </a>
                <a href="{{ route('commission-list.agent-excel', $agent->id) }}?from_date={{ urlencode($from ?? '') }}&to_date={{ urlencode($to ?? '') }}"
                   class="btn btn-sm btn-success me-2" target="_blank">
                    <i class="fas fa-file-excel"></i> Download Excel
                </a>
                <a href="{{ route('commission-list.index') }}" class="btn btn-sm btn-outline-secondary">Back</a>
            </div>
        </div>

        <div class="col-12">
            <div class="card p-1">
                <table class="table table-striped table-sm">
                    <thead>
                        <tr>
                            <th>DATE</th>
                            <th>PARTICULAR</th>
                            <th class="text-end">NO. OF FILES</th>
                            <th class="text-end">TOTAL KW</th>
                            <th class="text-end">PAYABLE AMOUNT</th>
                            <th class="text-end">PAID AMOUNT</th>
                            <th class="text-end">OUTSTANDING</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($stats['lines'] ?? [] as $line)
                            <tr>
                                <td>{{ $line['date'] ?? '-' }}</td>
                                <td>{{ $line['particular'] ?? '-' }}</td>
                                <td class="text-end">{{ $line['files'] ?? '-' }}</td>
                                <td class="text-end">{{ $line['kw'] ?? '-' }}</td>
                                <td class="text-end">{{ $line['payable'] ?? '-' }}</td>
                                <td class="text-end">{{ $line['paid'] ?? '-' }}</td>
                                <td class="text-end">{{ $line['outstanding'] ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th>Total</th>
                            <th></th>
                            <th class="text-end">
                                @if ($stats['no_of_file'] > 0)
                                    <a href="{{ route('commission-list.files', $agent->id) }}?from_date={{ urlencode($from ?? '') }}&to_date={{ urlencode($to ?? '') }}"
                                        class="fw-bold text-warning">{{ $stats['no_of_file'] ?? 0 }}</a>
                                @else
                                    {{ $stats['no_of_file'] ?? 0 }}
                                @endif
                            </th>
                            <th class="text-end">{{ $stats['kw'] ?? 0 }}</th>
                            <th class="text-end">{{ number_format((float)preg_replace('/[^0-9\-.]/', '', (string)($stats['total_payable'] ?? '0')), 2) }}</th>
                            <th class="text-end">{{ number_format((float)preg_replace('/[^0-9\-.]/', '', (string)($stats['total_paid'] ?? '0')), 2) }}</th>
                            <th class="text-end">{{ number_format((float)preg_replace('/[^0-9\-.]/', '', (string)(($stats['total_payable'] ?? '0'))) - (float)preg_replace('/[^0-9\-.]/', '', (string)($stats['total_paid'] ?? '0')), 2) }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
@endsection
