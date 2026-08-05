<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ProjectWiseStockExport implements FromCollection, WithHeadings, WithMapping
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
            'Item Name',
            'Use stock',
            'Available stock',
            'Total stock',
            'Taxabale',
            'GST',
            'Total'
        ];
    }
    public function map($row): array
    {
        return [
            $row->item_dis_name,
            $row->use_quantity,
            $row->quantity,
            $row->total_qty,
            number_format($row->taxable_amount,2,'.',''),
            number_format($row->gst_amount,2,'.',''),
            number_format($row->total_amount,2,'.','')
        ];
    }
}
