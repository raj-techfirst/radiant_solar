<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class ExportStock implements FromCollection, WithHeadings, WithColumnFormatting
{
    private $query;

    public function __construct($query)
    {
        $this->query = $query;
    }

    public function collection()
    {
        $excel = $this->query;

        $data = [];
        foreach ($excel as $value) {

            $display_name  = '';
            if ($value->type == "Item") {
                $display_name  = $value->item->name;
            } else {

                $display_name = getItemGropName($value,1);
            }

            $data[] = [
                'updated_at' => \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel($value->updated_at),
                'warehouse_id' => !is_null($value->warehouse) ? $value->warehouse->name : '',
                'item_id' => $display_name,
                'quantity' => $value->quantity,
            ];
        }
        return collect($data);
    }

    public function headings(): array
    {
        return [
            'Date',
            'Warehouse',
            'Item',
            'Quantity',
        ];
    }

    public function columnFormats(): array
    {
        return [
            'A' => NumberFormat::FORMAT_DATE_DDMMYYYY,
        ];
    }
}
