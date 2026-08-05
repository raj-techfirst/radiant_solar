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

            $display_name  = 'ss';
            if ($value->type == "Item") {
                $display_name  = $value->item->name;
            } else {
                $display_name = getItemGropName($value);
            }

            foreach ($value->warehouse_stock_history as $item) {
                $data[] = [
                    'warehouse_id' => !is_null($value->warehouse) ? $value->warehouse->name : '',
                    'item_id' => $display_name,
                    'stock' => $value->quantity,
                    'quantity' => $item->quantity,
                    'type' => $item->type,
                    'remark' => $item->remark,
                    'created_at' => \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel($item->created_at),
                ];
            }
        }
        return collect($data);
    }

    public function headings(): array
    {
        return [
            'Warehouse',
            'Item',
            'Available Stock',
            'Quantity',
            'type',
            'Remark',
            'Date'
        ];
    }

    public function columnFormats(): array
    {
        return [
            'G' => NumberFormat::FORMAT_DATE_DDMMYYYY,
        ];
    }
}
