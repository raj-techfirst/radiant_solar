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

            $doc_no = $party_name = $party_no = '-';

            if ($item->purchase_direct_meta_id != 0) {
                $purchaseDirect = $item->purchase_direct_meta?->purchase_direct;
                $doc_no = $purchaseDirect?->grn_number ?? '-';
                $party_name = $purchaseDirect?->supplier?->name ?? '-';
                $party_no = $purchaseDirect?->supplier_number ?? '-';
            } elseif ($item->delivery_challan_meta_id != 0) {
                $challan = $item->delivery_challan_meta?->delivery_challan;
                $doc_no = $challan?->challan_number ?? '-';

                if ($challan) {
                    if ($challan->issue_type == 'project' && $challan->project) {
                        $party_name = $challan->project->consumer_name ?? '-';
                        $party_no = $challan->project->consumer_number ?? '-';
                    } elseif ($challan->issue_type == 'installer' && $challan->installer) {
                        $party_name = trim(($challan->installer->name ?? '') . ' ' . ($challan->installer->last_name ?? '')) ?: '-';
                        $party_no = $challan->installer->mobile ?? '-';
                    } elseif ($challan->issue_type == 'warehouse' && $challan->warehouse_from) {
                        $party_name = $challan->warehouse_from->name ?? '-';
                    } elseif ($challan->issue_type == 'trading' && $challan->salesQuatation) {
                        $party_name = $challan->salesQuatation->name ?? '-';
                        $party_no = $challan->salesQuatation->mobile ?? '-';
                    }
                }
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
