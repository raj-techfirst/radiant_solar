<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class RequisitionExport implements FromCollection , WithHeadings, WithMapping
{
    private $data;
    public function __construct($data)
    {
        $this->data = $data;
    }
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return collect($this->data);
    }
    public function headings(): array
    {
        return [
            'Product',
            'Unit',
            'WHS. Stock',
            'INS. Stock',
            'CUR. Stock',
            'Req. Stock',
            'Sort Stock'
        ];
    }
    public function map($row): array
    {
        return [
            $row->item_dis_name,
            $row->unit_name,
            $row->current_stock,
            $row->installer_stock,
            $row->total_current_stock,
            $row->require_qty,
            $row->sort_stock
        ];
    }
}
