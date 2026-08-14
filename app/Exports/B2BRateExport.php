<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Facades\DB;

class B2BRateExport implements FromCollection, WithHeadings, WithMapping, WithStyles
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
            'KW',
            'Lead Date',
            'Sales Person',
            'Item Detail',
            'Panel Watt',
            'Nos',
            'Rate',
            'Per Watt Rate',
            'GST',
            'Total Taxable'
        ];
    }
    public function map($row): array
    {
        static $prevLeadId = null;
        $repeat = ($row->id === $prevLeadId);
        $prevLeadId = $row->id;

        return [
            $repeat ? '' : $row->name,
            $repeat ? '' : $row->mobile,
            $repeat ? '' : $row->kw,
            $repeat ? '' : $row->lead_date,
            $repeat ? '' : $row->agent_name,
            $row->item_detail,
            $row->panel_watt,
            $row->nos,
            $row->rate,
            ($row->panel_watt ? round($row->rate / $row->panel_watt, 2) : ''),
            $row->item_gst,
            $row->total_taxable,
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
