<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class SalesAgentWiseExport implements FromCollection, WithStyles, WithColumnWidths, WithEvents
{
    protected $months;
    protected $agents;
    protected $data;
    protected $from;
    protected $to;

    public function __construct($months, $agents, $data, $from = null, $to = null)
    {
        $this->months = $months;
        $this->agents = $agents;
        $this->data = $data;
        $this->from = $from;
        $this->to = $to;
    }

    public function collection()
    {
        return collect();
    }

    public function columnWidths(): array
    {
        $widths = ['A' => 12];
        foreach ($this->agents as $i => $agent) {
            $col = Coordinate::stringFromColumnIndex($i + 2);
            $name = strlen($agent['name']) > 15 ? 18 : 12;
            $widths[$col] = max($name, strlen($agent['name']) + 4);
        }
        $totalCol = Coordinate::stringFromColumnIndex(count($this->agents) + 2);
        $widths[$totalCol] = 14;
        return $widths;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            'A' => ['font' => ['name' => 'Arial']],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $agentCount = count($this->agents);
                $lastColIdx = $agentCount + 2; // A + agents + Total
                $lastCol = Coordinate::stringFromColumnIndex($lastColIdx);
                $totalCol = Coordinate::stringFromColumnIndex($lastColIdx);
                $firstAgentCol = 'B';
                $lastAgentCol = Coordinate::stringFromColumnIndex($agentCount + 1);
                $finalRow = 4 + count($this->months);
                $sheet->getStyle("A1:{$lastCol}{$finalRow}")->getFont()->setName('Arial')->setSize(10);

                // ---- Row 1: Period ----
                $period = 'KW Report — Period: ' . date('M-Y', strtotime($this->from)) . ' to ' . date('M-Y', strtotime($this->to));
                $sheet->mergeCells("A1:{$lastCol}1");
                $sheet->setCellValue('A1', $period);
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(11)->setName('Arial');
                $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
                $sheet->getStyle('A1')->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFF8E8DC');
                $sheet->getRowDimension(1)->setRowHeight(20);

                // ---- Row 2: Group header ----
                $sheet->setCellValue('A2', 'Months');
                $sheet->mergeCells("{$firstAgentCol}2:{$lastAgentCol}2");
                $sheet->setCellValue('B2', 'Sales Person');
                $sheet->setCellValue("{$totalCol}2", 'Total KW');
                $headerStyle = $sheet->getStyle("A2:{$lastCol}2");
                $headerStyle->getFont()->setBold(true)->setName('Arial');
                $headerStyle->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
                $headerStyle->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFDCE6F1');
                $sheet->getRowDimension(2)->setRowHeight(20);

                // ---- Row 3: Sales person sub-headers ----
                $sheet->setCellValue('A3', '');
                foreach ($this->agents as $i => $agent) {
                    $col = Coordinate::stringFromColumnIndex($i + 2);
                    $sheet->setCellValue("{$col}3", $agent['name']);
                }
                $sheet->setCellValue("{$totalCol}3", 'Total KW');
                $subStyle = $sheet->getStyle("A3:{$lastCol}3");
                $subStyle->getFont()->setBold(true)->setName('Arial');
                $subStyle->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
                $subStyle->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFEAF1F8');
                $sheet->getRowDimension(3)->setRowHeight(18);

                // ---- Rows 4+: Months data ----
                $startRow = 4;
                foreach ($this->months as $mi => $month) {
                    $r = $startRow + $mi;
                    $sheet->setCellValue("A{$r}", $month);
                    $sheet->getStyle("A{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
                    foreach ($this->agents as $i => $agent) {
                        $col = Coordinate::stringFromColumnIndex($i + 2);
                        $addKey = $month . '|' . $agent['id'];
                        $val = isset($this->data[$addKey]) ? $this->data[$addKey] : 0;
                        $sheet->setCellValue("{$col}{$r}", $val);
                    }
                    $sheet->setCellValue("{$totalCol}{$r}", "=SUM({$firstAgentCol}{$r}:{$lastAgentCol}{$r})");
                }

                // ---- Total row ----
                $totalRow = $startRow + count($this->months);
                $sheet->setCellValue("A{$totalRow}", 'Total');
                foreach ($this->agents as $i => $agent) {
                    $col = Coordinate::stringFromColumnIndex($i + 2);
                    $sheet->setCellValue("{$col}{$totalRow}", "=SUM({$col}{$startRow}:{$col}" . ($startRow + count($this->months) - 1) . ")");
                }
                $sheet->setCellValue("{$totalCol}{$totalRow}", "=SUM({$totalCol}{$startRow}:{$totalCol}" . ($startRow + count($this->months) - 1) . ")");

                // Total row style
                $totStyle = $sheet->getStyle("A{$totalRow}:{$lastCol}{$totalRow}");
                $totStyle->getFont()->setBold(true)->setName('Arial');
                $totStyle->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
                $totStyle->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFDCE6F1');

                // ---- Styles: values ----
                $valueStyle = $sheet->getStyle("{$firstAgentCol}{$startRow}:{$lastCol}{$totalRow}");
                $valueStyle->getNumberFormat()->setFormatCode('#,##0.000;-0.000;"-"');
                $valueStyle->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT)->setVertical(Alignment::VERTICAL_CENTER);

                // ---- Borders ----
                $sheet->getStyle("A1:{$lastCol}{$totalRow}")->getBorders()->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN);
            },
        ];
    }
}