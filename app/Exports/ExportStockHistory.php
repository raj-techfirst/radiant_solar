<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class ExportStockHistory implements FromCollection, WithHeadings, WithColumnFormatting
{
    private $query;

    public function __construct($query)
    {
        $this->query = $query;
    }

    public function collection()
    {
        $excel = $this->query;

        $display_name  = 'ss';
        if ($excel->type == "Item") {
            $display_name  = $excel->item->name;
        } else {

            $display_name = getItemGropName($excel,1);
        }

        $data = [];
        $balance = 0;
        foreach ($excel->warehouse_stock_history as $item) {
            if ($item->type == 'Credit') {
                $balance += $item->quantity;
            } else {
                $balance -= $item->quantity;
            }

            if ($item->purchase_direct_meta_id == 0 && $item->delivery_challan_meta_id == 0) {
                $doc_no = $party_name = $party_no = '-';
            } elseif ($item->purchase_direct_meta_id != 0) {
                $doc_no = $item->purchase_direct_meta->purchase_direct->grn_number;
                $party_name = $item->purchase_direct_meta->purchase_direct->supplier->name;
                $party_no = $item->purchase_direct_meta->purchase_direct->supplier_number;
            } elseif ($item->delivery_challan_meta_id != 0) {
                $doc_no = $item->delivery_challan_meta->delivery_challan->challan_number;
                $party_name = $item->delivery_challan_meta->delivery_challan->project->consumer_name;
                $party_no = $item->delivery_challan_meta->delivery_challan->project->consumer_number;
            }

            $data[] = [
                'created_at' => \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel($item->created_at),
                'warehouse_id' => !is_null($excel->warehouse) ? $excel->warehouse->name : '',
                'item_id' => $display_name,
                'stock' => $excel->quantity,
                'doc_no' => $doc_no,
                'doc_type' => $item->stock_type,
                'party_name' => $party_name,
                'party_no' => $party_no,
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
            'Warehouse',
            'Item',
            'Available Stock',
            'Doc. No.',
            'Doc. Type',
            'Party Name',
            'Party No.',
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
