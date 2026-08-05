<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class ProjectStock implements FromCollection, WithHeadings, WithColumnFormatting
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
        foreach ($excel as $item) {
            if ($item->issue_type == "project") {
                $site_name =  $item->project->consumer_name;
            } else {
                $site_name =  '(Ins) '. $item->installer->name . ' ' . $item->installer->last_name;
            }

            if ($item->type == "Item") {
                $display_name = $item->item->name;
            } else {
                $display_name = getItemGropName($item,1);
            }

            $data[] = [
                'project_id' => $site_name,
                'item_id' => $display_name,
                'stock' => $item->quantity,
                'updated_at' => \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel($item->updated_at),
            ];
        }
        return collect($data);
    }

    public function headings(): array
    {
        return [
            'Project',
            'Item',
            'Available Stock',
            'Updated At'
        ];
    }

    public function columnFormats(): array
    {
        return [
            'D' => NumberFormat::FORMAT_DATE_DDMMYYYY,
        ];
    }
}
