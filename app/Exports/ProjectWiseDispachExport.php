<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ProjectWiseDispachExport implements FromCollection, WithHeadings, WithMapping
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
      /// $data = DB::select(DB::raw($this->query));
        return collect($this->query);
    }
    public function headings(): array
    {
        return [
            'Consumer No.',
            'Consumer',
            'Mobile',
            'System Cost',
            'Taxabale amount',
            'GST Amount',
            'Total Amount'
        ];
    }
    public function map($row): array
    {
        return [
            $row->consumer_number,
            $row->consumer_name,
            $row->consumer_mobile,
            $row->system_cost,
            $row->taxable_amount,
            $row->gst_amount,
            $row->total_amount
        ];
    }
}
