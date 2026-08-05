<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class ProjectStockHistory implements FromCollection, WithHeadings, WithColumnFormatting
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
        $balance = 0;

        if ($excel->type == "Item") {
            $display_name = $excel->item->name;
        } else {
            $display_name = getItemGropName($excel,1);
        }

        if ($excel->issue_type == "project") {
            $site_name =  $excel->project->consumer_name;
        } else {
            $site_name =  '(Ins) ' . $excel->installer->name . ' ' . $excel->installer->last_name;
        }

        foreach ($excel->project_wise_history as $item) {
            if ($item->type == 'Credit') {
                $balance += $item->quantity;
            } else {
                $balance -= $item->quantity;
            }

            $data[] = [
                'date' => \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel($item->created_at),
                'project_id' => $site_name,
                'item_id' => $display_name,
                'stock' => $excel->quantity,
                'doc_no' => ($item->delivery_challan_meta_id != 0) ? $item->delivery_challan_meta->delivery_challan->challan_number : '-',
                'doc_type' => ($item->delivery_challan_meta_id != 0) ? 'Delivery Challan' : 'Stock Adjustment',
                'remark' => $item->remark,
                'in' => ($item->type == 'Credit') ? $item->quantity : '-',
                'out' => ($item->type != 'Credit') ? $item->quantity : '-',
                'quantity' => $balance,
            ];
        }
        return collect($data);
    }

    public function headings(): array
    {
        return [
            'Date',
            'Project',
            'Item',
            'Available Stock',
            'Doc. No.',
            'Doc. Type',
            'Remark',
            'In',
            'Out',
            'Closing Balance',
        ];
    }

    public function columnFormats(): array
    {
        return [
            'A' => NumberFormat::FORMAT_DATE_DDMMYYYY,
        ];
    }
}
