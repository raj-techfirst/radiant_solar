<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Support\Facades\DB;

class B2BRateExport implements FromCollection, WithHeadings, WithMapping
{
    private $query;
    public function __construct($query)
    {
        $this->query = $query;
    }
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $data = DB::select(DB::raw($this->query));
        return collect($data);
    }
    public function headings(): array
    {
        return [
            'Name',
            'Mobile',
            'Address',
            'KW',
            'Lead Value',
            'Lead Date',
            'Agent'
        ];
    }
    public function map($row): array
    {
        return [
            $row->name,
            $row->mobile,
            $row->address,
            $row->kw,
            $row->lead_value,
            $row->lead_date,
            $row->agent_name,
        ];
    }
}
