<table>
    <thead>
        <tr>
            <th colspan="9" style="font-weight:bold; font-size:14px;">COMMISSION INCLUDED FILES</th>
        </tr>
        <tr>
            <th colspan="9">Agent: {{ $agent->user->name }} {{ $agent->user->last_name }}</th>
        </tr>
        <tr>
            <th colspan="9">
                @if(!empty($from) && !empty($to))
                    Period: {{ date('d-m-Y', strtotime($from)) }} to {{ date('d-m-Y', strtotime($to)) }}
                @elseif(!empty($from))
                    From: {{ date('d-m-Y', strtotime($from)) }}
                @elseif(!empty($to))
                    Till: {{ date('d-m-Y', strtotime($to)) }}
                @else
                    All Time
                @endif
            </th>
        </tr>
        <tr></tr>
        <tr>
            <th>#</th>
            <th>Invoice Date</th>
            <th>Ins. KW</th>
            <th>Consumer Name</th>
            <th>Agent</th>
            <th>comm'n</th>
            <th>SUB comm'n</th>
            <th>install</th>
            <th>Pending Amount</th>
        </tr>
    </thead>
    <tbody>
        @forelse($sales as $idx => $s)
            @php
                $kw = (float) ($s->installation->total_kv ?? ($s->register_kw ?? 0.0));

                $commissionForAgent = ((int) ($s->agent_sales_person_id ?? 0) === (int) $agent->id)
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
                $installationForAgent = ($installerUserId === $agentUserId && (string) $s->installation_done === '1')
                    ? (float) ($s->installation_amount ?? 0) * $kw
                    : 0.0;
            @endphp
            <tr>
                <td>{{ $idx + 1 }}</td>
                <td>{{ $s->invoice_date ? date('d-m-Y', strtotime($s->invoice_date)) : '-' }}</td>
                <td>{{ number_format($kw, 3) }}</td>
                <td>{{ $s->consumer_name ?? '-' }} ({{ $s->consumer_number ?? '-' }})</td>
                <td>{{ $s->agentsalesperson->name ?? '-' }}</td>
                <td>{{ number_format($commissionForAgent, 2) }}</td>
                <td>{{ number_format($subCommissionForAgent, 2) }}</td>
                <td>{{ number_format($installationForAgent, 2) }}</td>
                <td>{{ number_format((float) ($s->pending_amonut ?? 0), 2) }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="9">No records found.</td>
            </tr>
        @endforelse
    </tbody>
</table>


