<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Facades\DB;

class B2BDispatchExport implements FromCollection, WithHeadings, WithMapping, WithStyles
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
            'Quotation Date',
            'Sales Person',
            'Bill To Address',
            'Ship To',
            'Item Detail',
            'Nos',
            'Rate',
            'GST',
            'Total Taxable'
        ];
    }
    public function map($row): array
    {
        static $prevQuoteId = null;
        $repeat = ($row->id === $prevQuoteId);
        $prevQuoteId = $row->id;

        return [
            $repeat ? '' : $row->name,
            $repeat ? '' : $row->mobile,
            $repeat ? '' : $row->quotation_date,
            $repeat ? '' : $row->agent_name,
            $repeat ? '' : $row->bill_to_address,
            $repeat ? '' : $row->ship_to,
            $row->item_detail,
            $row->nos,
            $row->rate,
            $repeat ? '' : $row->gst,
            $repeat ? '' : $row->total_taxable,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['argb' => 'FF000000'],
                ],
                'fill' => [
                    'fillType' => 'solid',
                    'startColor' => ['argb' => 'FFD3D3D3'],
                ],
            ],
        ];
    }
}
