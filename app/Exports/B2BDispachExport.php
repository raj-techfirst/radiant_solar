<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Support\Facades\DB;

class B2BDispachExport implements FromCollection, WithHeadings, WithMapping
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
            'Goods Issue Date',
            'Goods Issue No',
            'Taxabale amount',
            'GST Amount',
            'Total Amount'
        ];
    }
    public function map($row): array
    {
        return [
            $row->name,
            $row->mobile,
            $row->goods_issue_date,
            $row->goods_issue_no,
            $row->taxabale_amount,
            $row->gst_amount,
            $row->total_amount
        ];
    }
}
