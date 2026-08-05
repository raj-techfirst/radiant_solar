<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\Exportable;

class CommissionFilesExport implements FromView
{
    use Exportable;

    protected $agent;
    protected $sales;
    protected $selectedCompanyId;
    protected $from;
    protected $to;

    public function __construct($agent, $sales, $selectedCompanyId, $from = null, $to = null)
    {
        $this->agent = $agent;
        $this->sales = $sales;
        $this->selectedCompanyId = $selectedCompanyId;
        $this->from = $from;
        $this->to = $to;
    }

    public function view(): View
    {
        return view('admin.commission-list.files-excel', [
            'agent' => $this->agent,
            'sales' => $this->sales,
            'selectedCompanyId' => $this->selectedCompanyId,
            'from' => $this->from,
            'to' => $this->to,
        ]);
    }
}


