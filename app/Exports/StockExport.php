<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class StockExport implements FromCollection, WithHeadings, WithMapping
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
            'Warehouse',
            'Category',
            'Product',
            'Unit',
            'Qty',
            'Total Value',
            'GST Amount',
            'Total Amount'
        ];
    }
    public function map($row): array
    {
        return [
            $row->warehouse_name,
            $row->category_name,
            $row->item_dis_name,
            $row->unit_name,
            $row->quantity,
            number_format($row->total_value, 2, '.', ''),
            number_format($row->gst_amount, 2, '.', ''),
            number_format($row->total_amount, 2, '.', '')
        ];
    }
}
