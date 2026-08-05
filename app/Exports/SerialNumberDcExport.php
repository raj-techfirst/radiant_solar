<?php

namespace App\Exports;

use App\Models\SerialNumberLog;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class SerialNumberDcExport implements FromCollection, WithHeadings, WithMapping
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
        return SerialNumberLog::with('serialNumbers')->where('delivery_challan_meta_id', $this->request->id)->get();
    }
    public function headings(): array
    {
        return [
            'serial_number'
        ];
    }
    public function map($row): array
    {
        return [
            $row->serialNumbers->serial_number
        ];
    }
}
