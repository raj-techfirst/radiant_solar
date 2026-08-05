<?php

namespace App\Exports;

use App\Models\erp\SerialNumber;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class SerialNumberExport implements FromCollection, WithHeadings, WithMapping
{
    private $request;
    public function __construct($request)
    {
        $this->request = $request;
    }
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return SerialNumber::where('purchase_direct_meta_id', $this->request->id)->get();
    }
    public function headings(): array
    {
        return [
            'serial_number',
            'warranty_start_date',
            'warranty_end_date',
            'guarantee_start_date',
            'guarantee_end_date'
        ];
    }
    public function map($row): array
    {
        return [
            $row->serial_number,
            ($row->warranty_start_date != '0000-00-00') ? date('d-m-Y',strtotime($row->warranty_start_date)) : '',
            ($row->warranty_end_date != '0000-00-00') ? date('d-m-Y',strtotime($row->warranty_end_date)) : '',
            ($row->guarantee_start_date != '0000-00-00') ? date('d-m-Y',strtotime($row->guarantee_start_date)) : '',
            ($row->guarantee_end_date != '0000-00-00') ? date('d-m-Y',strtotime($row->guarantee_end_date)) : '',
        ];
    }
}
