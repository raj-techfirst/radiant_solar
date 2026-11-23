<?php

namespace App\Exports;

use App\Models\SalesMaster;
use App\Models\erp\DeliveryChallanMeta;
use App\Models\erp\DeliveryChallanReturn;
use App\Models\erp\DeliveryChallanReturnMeta;
use App\Models\erp\ProjectWiseStock;
use App\Models\erp\ProjectWiseStockHistory;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class ExportStockHistory implements FromCollection, WithColumnFormatting, WithStrictNullComparison, WithEvents
{
    private $query;
    private $challanCache = [];
    private $returnCache = [];
    private $consumerCache = [];

    public function __construct($query)
    {
        $this->query = $query;
    }

    public function collection()
    {
        $excel = $this->query;

        if ($excel->type == "Item") {
            $display_name = $excel->item->name ?? '-';
        } else {
            $display_name = getItemGropName($excel, 1);
        }

        $allocation = $this->installerAllocation($excel);

        $available_stock = (int) $excel->quantity;
        $installer_stock = (int) $allocation['stock'];
        $total_stock = $available_stock + $installer_stock;

        $rows = [];
        $rows[] = ['', 'Warehouse', $excel->warehouse->name ?? '-', 'Item', $display_name, null, null, null, null];
        $rows[] = ['', 'Available Stock', $available_stock, null, null, null, null, null, null];
        $rows[] = ['', 'installer stock', $installer_stock, null, null, null, null, null, null];
        $rows[] = ['', 'total', $total_stock, null, null, null, null, null, null];
        $rows[] = array_fill(0, 9, null);
        $rows[] = array_fill(0, 9, null);
        $rows[] = ['Date', 'Doc. No.', 'Doc. Type', 'Party Name', 'Party No.', 'Remark', 'In', 'Out', 'Closing Balance'];

        $entries = array_merge($this->warehouseEntries($excel), $allocation['entries']);
        usort($entries, function ($a, $b) {
            if ($a['sort'] == $b['sort']) {
                return $a['order'] <=> $b['order'];
            }
            return $a['sort'] <=> $b['sort'];
        });

        $balance = 0;
        foreach ($entries as $entry) {
            if (!is_null($entry['in'])) {
                $balance += $entry['in'];
            }
            if (!is_null($entry['out'])) {
                $balance -= $entry['out'];
            }

            $rows[] = [
                $entry['date'] ? ExcelDate::PHPToExcel($entry['date']) : '-',
                $entry['doc_no'],
                $entry['doc_type'],
                $entry['party_name'],
                $entry['party_no'],
                $entry['remark'],
                is_null($entry['in']) ? '-' : $entry['in'],
                is_null($entry['out']) ? '-' : $entry['out'],
                $balance,
            ];
        }

        return collect($rows);
    }

    private function warehouseEntries($excel)
    {
        $entries = [];

        foreach ($excel->warehouse_stock_history as $item) {
            $issue_type = null;
            if ($item->delivery_challan_meta_id != 0) {
                $issue_type = $item->delivery_challan_meta?->delivery_challan?->issue_type;
            }

            $doc_no = $party_name = $party_no = '-';

            if ($item->purchase_direct_meta_id != 0) {
                $purchaseDirect = $item->purchase_direct_meta?->purchase_direct;
                $doc_no = $purchaseDirect?->grn_number ?? '-';
                $party_name = $purchaseDirect?->supplier?->name ?? '-';
                $party_no = $purchaseDirect?->supplier_number ?? '-';
            } elseif ($item->delivery_challan_return_meta_id != 0) {
                $return = $this->returnForMeta($item->delivery_challan_return_meta_id);
                $doc_no = $return?->challan_number ?? '-';

                if ($return && $return->issue_type == 'project') {
                    $party_name = $return->project->consumer_name ?? '-';
                    $party_no = $return->project->consumer_number ?? '-';
                } else {
                    $party_name = $this->partyName($return?->installer);
                    $party_no = $return?->installer?->mobile ?? '-';
                }
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
                    } elseif ($challan->issue_type == 'warehouse') {
                        if ((int) $excel->warehous_id === (int) $challan->warehouse_id) {
                            $party_name = $challan->warehouse_from->name ?? '-';
                        } elseif ((int) $excel->warehous_id === (int) $challan->warehouse_from_id) {
                            $party_name = $challan->warehouse->name ?? '-';
                        } else {
                            $party_name = $challan->warehouse_from->name ?? '-';
                        }
                    } elseif ($challan->issue_type == 'trading' && $challan->salesQuatation) {
                        $party_name = $challan->salesQuatation->name ?? '-';
                        $party_no = $challan->salesQuatation->mobile ?? '-';
                    }
                }
            }

            // Stock sent to an installer is not an "Out" of total stock. It is
            // represented only by the installer stock summary and installation rows.
            if ($item->type != 'Credit' && $issue_type == 'installer') {
                continue;
            }

            $entries[] = [
                'sort' => $item->created_at ? $item->created_at->timestamp : 0,
                'order' => $item->id,
                'date' => $item->created_at,
                'doc_no' => $doc_no,
                'doc_type' => $item->stock_type,
                'party_name' => $party_name,
                'party_no' => $party_no,
                'remark' => $item->remark,
                'in' => ($item->type == 'Credit') ? $item->quantity : null,
                'out' => ($item->type != 'Credit') ? $item->quantity : null,
            ];
        }

        return $entries;
    }

    /**
     * Installer stock is stored per installer+item, not per warehouse. To show
     * the stock that belongs to this warehouse we allocate every delivery
     * challan credit to its source warehouse and consume credits FIFO when an
     * installation (or return / edit reversal) happens.
     */
    private function installerAllocation($excel)
    {
        $query = ProjectWiseStock::where('issue_type', 'installer');

        if ($excel->type == "Item") {
            $query->where('item_id', $excel->item_id)->where('item_group_id', 0);
        } else {
            $query->where('item_group_id', $excel->item_group_id)->where('item_id', 0);
        }

        $report_warehouse_id = $excel->warehous_id;
        $remaining = [];
        $entries = [];

        foreach ($query->get() as $pws) {
            $history = ProjectWiseStockHistory::where('project_wise_stock_id', $pws->id)->get();
            $events = $this->installerEvents($history);
            $lots = [];

            foreach ($events as $event) {
                if ($event['kind'] == 'credit') {
                    $lots[] = [
                        'warehouse_id' => $event['warehouse_id'],
                        'dcm' => $event['dcm'],
                        'remaining' => $event['quantity'],
                    ];
                    continue;
                }

                $quantity = $event['quantity'];
                $consumed = [];

                // A challan edit/delete reversal consumes its own challan lot.
                if ($event['kind'] == 'reversal' && $event['dcm']) {
                    foreach ($lots as &$lot) {
                        if ($quantity <= 0) {
                            break;
                        }
                        if ($lot['dcm'] == $event['dcm'] && $lot['remaining'] > 0) {
                            $take = min($quantity, $lot['remaining']);
                            $lot['remaining'] -= $take;
                            $quantity -= $take;
                            $consumed[$lot['warehouse_id']] = ($consumed[$lot['warehouse_id']] ?? 0) + $take;
                        }
                    }
                    unset($lot);
                }

                // A return should preferably leave the warehouse it returns to.
                if ($quantity > 0 && $event['kind'] == 'return' && !is_null($event['warehouse_id'])) {
                    foreach ($lots as &$lot) {
                        if ($quantity <= 0) {
                            break;
                        }
                        if ($lot['warehouse_id'] == $event['warehouse_id'] && $lot['remaining'] > 0) {
                            $take = min($quantity, $lot['remaining']);
                            $lot['remaining'] -= $take;
                            $quantity -= $take;
                            $consumed[$lot['warehouse_id']] = ($consumed[$lot['warehouse_id']] ?? 0) + $take;
                        }
                    }
                    unset($lot);
                }

                if ($quantity > 0) {
                    foreach ($lots as &$lot) {
                        if ($quantity <= 0) {
                            break;
                        }
                        if ($lot['remaining'] > 0) {
                            $take = min($quantity, $lot['remaining']);
                            $lot['remaining'] -= $take;
                            $quantity -= $take;
                            $consumed[$lot['warehouse_id']] = ($consumed[$lot['warehouse_id']] ?? 0) + $take;
                        }
                    }
                    unset($lot);
                }

                $taken = $consumed[$report_warehouse_id] ?? 0;
                if ($taken > 0) {
                    $entries[] = [
                        'sort' => $event['sort'],
                        'order' => $event['order'],
                        'date' => $event['date'],
                        'doc_no' => $event['doc_no'],
                        'doc_type' => $event['doc_type'],
                        'party_name' => $event['party_name'],
                        'party_no' => $event['party_no'],
                        'remark' => $event['remark'],
                        'in' => null,
                        'out' => $taken,
                    ];
                }
            }

            foreach ($lots as $lot) {
                if ($lot['remaining'] > 0) {
                    $remaining[$lot['warehouse_id']] = ($remaining[$lot['warehouse_id']] ?? 0) + $lot['remaining'];
                }
            }
        }

        return [
            'stock' => $remaining[$report_warehouse_id] ?? 0,
            'entries' => $entries,
        ];
    }

    private function installerEvents($history)
    {
        $events = [];
        $installationGroups = [];

        foreach ($history as $row) {
            $date = $row->created_at;
            $sort = $date ? $date->timestamp : 0;

            if (!empty($row->installation_id)) {
                $installationGroups[$row->installation_id . '-' . $row->project_wise_stock_id][] = $row;
                continue;
            }

            if ($row->type == 'Credit') {
                if ($row->delivery_challan_meta_id != 0) {
                    $challan = $this->challanForMeta($row->delivery_challan_meta_id);
                    $events[] = [
                        'kind' => 'credit',
                        'warehouse_id' => $challan->warehouse_id ?? null,
                        'dcm' => $row->delivery_challan_meta_id,
                        'quantity' => $row->quantity,
                        'sort' => $sort,
                        'order' => $row->id,
                    ];
                }
                continue;
            }

            if ($row->delivery_challan_return_meta_id != 0) {
                $return = $this->returnForMeta($row->delivery_challan_return_meta_id);
                $events[] = [
                    'kind' => 'return',
                    'warehouse_id' => $return->warehouse_id ?? null,
                    'quantity' => $row->quantity,
                    'sort' => $sort,
                    'order' => $row->id,
                    'date' => $date,
                    'doc_no' => $return->challan_number ?? '-',
                    'doc_type' => 'Delivery Challan Return',
                    'party_name' => $this->partyName($return?->installer),
                    'party_no' => $return->installer->mobile ?? '-',
                    'remark' => $row->remark,
                ];
                continue;
            }

            if ($row->delivery_challan_meta_id != 0) {
                $challan = $this->challanForMeta($row->delivery_challan_meta_id);
                $events[] = [
                    'kind' => 'reversal',
                    'dcm' => $row->delivery_challan_meta_id,
                    'warehouse_id' => $challan->warehouse_id ?? null,
                    'quantity' => $row->quantity,
                    'sort' => $sort,
                    'order' => $row->id,
                    'date' => $date,
                    'doc_no' => $challan->challan_number ?? '-',
                    'doc_type' => 'Delivery Challan Edit',
                    'party_name' => $this->partyName($challan?->installer),
                    'party_no' => $challan->installer->mobile ?? '-',
                    'remark' => $row->remark,
                ];
                continue;
            }

            $events[] = [
                'kind' => 'other',
                'quantity' => $row->quantity,
                'sort' => $sort,
                'order' => $row->id,
                'date' => $date,
                'doc_no' => '-',
                'doc_type' => 'Installer Stock',
                'party_name' => '-',
                'party_no' => '-',
                'remark' => $row->remark,
            ];
        }

        foreach ($installationGroups as $group) {
            $group = collect($group);

            $quantity = $group->where('type', 'Debit')->sum('quantity')
                - $group->where('type', 'Credit')->sum('quantity');

            if ($quantity <= 0) {
                continue;
            }

            $first = $group->sortBy(function ($row) {
                return $row->created_at ? $row->created_at->timestamp : 0;
            })->first();

            $consumer = $this->consumerFor($first->sales_master_id);

            $events[] = [
                'kind' => 'installation',
                'quantity' => $quantity,
                'sort' => $first->created_at ? $first->created_at->timestamp : 0,
                'order' => $first->id,
                'date' => $first->created_at,
                'doc_no' => 'INS-' . $first->installation_id,
                'doc_type' => 'Installation',
                'party_name' => $consumer->consumer_name ?? '-',
                'party_no' => $consumer->consumer_number ?? '-',
                'remark' => $first->remark ?: 'Installed',
            ];
        }

        usort($events, function ($a, $b) {
            if ($a['sort'] == $b['sort']) {
                return $a['order'] <=> $b['order'];
            }
            return $a['sort'] <=> $b['sort'];
        });

        return $events;
    }

    private function challanForMeta($metaId)
    {
        if (!array_key_exists($metaId, $this->challanCache)) {
            $this->challanCache[$metaId] = DeliveryChallanMeta::withTrashed()
                ->with(['delivery_challan.installer', 'delivery_challan.project', 'delivery_challan.warehouse_from', 'delivery_challan.salesQuatation'])
                ->find($metaId)?->delivery_challan;
        }

        return $this->challanCache[$metaId];
    }

    private function returnForMeta($returnMetaId)
    {
        if (!array_key_exists($returnMetaId, $this->returnCache)) {
            $returnMeta = DeliveryChallanReturnMeta::withTrashed()->find($returnMetaId);
            $this->returnCache[$returnMetaId] = $returnMeta
                ? DeliveryChallanReturn::withTrashed()->with(['installer', 'project'])->find($returnMeta->delivery_challan_return_id)
                : null;
        }

        return $this->returnCache[$returnMetaId];
    }

    private function consumerFor($salesMasterId)
    {
        if (empty($salesMasterId)) {
            return null;
        }

        if (!array_key_exists($salesMasterId, $this->consumerCache)) {
            $this->consumerCache[$salesMasterId] = SalesMaster::find($salesMasterId);
        }

        return $this->consumerCache[$salesMasterId];
    }

    private function partyName($party)
    {
        if (!$party) {
            return '-';
        }

        return trim(($party->name ?? '') . ' ' . ($party->last_name ?? '')) ?: '-';
    }

    public function columnFormats(): array
    {
        return [
            'A' => NumberFormat::FORMAT_DATE_DDMMYYYY,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $summary = $sheet->getStyle('A1:E4');
                $summary->getFont()->setBold(true);
                $summary->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFDCE6F1');

                $header = $sheet->getStyle('A7:I7');
                $header->getFont()->setBold(true);
                $header->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFDCE6F1');
            },
        ];
    }
}
