@extends('layouts.app')
@section('title', 'Included Files')
@section('content')
    <div class="row">
        <div class="col-12 mb-1">
            <h4 class="content-header-title float-start"><b>Included Files</b> -
                {{ ucwords(strtolower($agent->user->name . ' ' . $agent->user->last_name)) }}</h4>
                  <div class="float-end">
                <a href="{{ route('commission-list.pdf', $agent->id) }}?from_date={{ urlencode($from ?? '') }}&to_date={{ urlencode($to ?? '') }}&type=files"
                  class="btn btn-sm btn-info me-2" target="_blank">
                    <i class="fas fa-print"></i> Download PDF
                </a>
                <a href="{{ route('commission-list.files-excel', $agent->id) }}?from_date={{ urlencode($from ?? '') }}&to_date={{ urlencode($to ?? '') }}"
                  class="btn btn-sm btn-success me-2" target="_blank">
                    <i class="fas fa-file-excel"></i> Download Excel
                </a>

            <a href="{{ route('commission-list.show', $agent->id) }}?from_date={{ urlencode($from ?? '') }}&to_date={{ urlencode($to ?? '') }}"
                class="btn btn-sm btn-outline-secondary float-end">Back</a>
                  </div>
        </div>

        <div class="col-12">
            <div class="card p-1">
                <table class="table table-striped table-sm">
                    <thead>
                        <tr>
                            <th>#</th>
                            {{-- <th>Status</th> --}}
                            <th>Invoice Date</th>
                            <th>Ins. KW</th>
                            <th>Consumer Name</th>
                            <th>Agent</th>
                            <th  class="text-end">comm\'n</th>
                            <th  class="text-end">SUB comm\'n</th>
                            <th  class="text-end">install</th>
                            <th class="text-end">Pending Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sales as $idx => $s)
                            @php

                                $kw = (float) ($s->installation->total_kv ?? ($s->register_kw ?? 0.0));

                                $commissionForAgent =
                                    (int) ($s->agent_sales_person_id ?? 0) === (int) $agent->id
                                        ? (float) ($s->commission_amount ?? 0) * $kw
                                        : 0.0;

                                $subCommissionForAgent = 0.0;
                                if (isset($selectedCompanyId) && !empty($selectedCompanyId)) {
                                    if ((int) ($s->agent_sales_person_id ?? 0) !== (int) $agent->id) {
                                        $subCommissionForAgent = (float) ($s->sub_commission_amount ?? 0) * $kw;
                                    }
                                }

                                $installerUserId = (int) ($s->installation_asian_person ?? 0);
                                $agentUserId = (int) ($agent->user_id ?? ($agent->user->id ?? 0));
                                $installationForAgent =
                                    $installerUserId === $agentUserId && (string) $s->installation_done === '1'
                                        ? (float) ($s->installation_amount ?? 0) * $kw
                                        : 0.0;
                            @endphp
                            <tr>
                                <td>{{ $idx + 1 }}</td>

                                {{-- <td>

                                    <span
                                        class="badge rounded-pill bg-light-secondary text-dark">{{ toGetSalesMasterLastStatus($s->id) }}</span>

                                </td> --}}
                                <td>{{ $s->invoice_date ? date('d-m-Y', strtotime($s->invoice_date)) : '-' }}</td>
                                <td>{{ number_format($kw, 3) }}</td>
                                <td>
                                    <a class="" data-bs-toggle="tooltip"
                                        data-placement="left" title="View" data-bs-original-title="View"
                                        href="{{ route('sales-master.show', $s->id) }}" target="_blank"><span
                                        class="badge rounded-pill bg-light-info text-dark">{{ $s->consumer_number ?? '-' }}</span> <br/>
                                        {{ $s->consumer_name ?? '-' }}</a>
                                    </td>
                                <td>{{ $s->agentsalesperson->name ?? '-' }}</td>
                                <td class="text-end">{{ number_format($commissionForAgent, 2) }}</td>
                                <td class="text-end">{{ number_format($subCommissionForAgent, 2) }}</td>
                                <td class="text-end">{{ number_format($installationForAgent, 2) }}</td>
                                <td class="text-end">{{ number_format((float) ($s->pending_amonut ?? 0), 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="12" class="text-center">No records found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
