<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CommissionDetailsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithEvents
{
    protected $agent;
    protected $stats;
    protected $from;
    protected $to;

    public function __construct($agent, $stats, $from = null, $to = null)
    {
        $this->agent = $agent;
        $this->stats = $stats;
        $this->from = $from;
        $this->to = $to;
    }

    public function collection()
    {
        $lines = $this->stats['lines'] ?? [];
        $collection = collect();

        foreach ($lines as $line) {
            $collection->push((object) $line);
        }

        return $collection;
    }

    public function headings(): array
    {
        return [
            'Date',
            'Particular',
            'No. of Files',
            'Total KW',
            'Payable Amount',
            'Paid Amount',
            'Outstanding'
        ];
    }

    public function map($row): array
    {
        return [
            $row->date ?? '-',
            $row->particular ?? '-',
            $row->files ?? '-',
            $row->kw ?? '-',
            str_replace('Rs. ', '', $row->payable ?? '-'),
            str_replace('Rs. ', '', $row->paid ?? '-'),
            str_replace('Rs. ', '', $row->outstanding ?? '-'),
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 12,  // Date
            'B' => 35,  // Particular
            'C' => 12,  // No. of Files
            'D' => 12,  // Total KW
            'E' => 15,  // Payable Amount
            'F' => 15,  // Paid Amount
            'G' => 15,  // Outstanding
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold
            1 => ['font' => ['bold' => true]],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                // Add title row
                $event->sheet->insertNewRowBefore(1, 3);
                $event->sheet->mergeCells('A1:G1');
                $event->sheet->setCellValue('A1', 'PAYOUT DETAILS REPORT');
                $event->sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
                $event->sheet->getStyle('A1')->getAlignment()->setHorizontal('center');

                // Add agent name
                $agentName = $this->agent->user->name . ' ' . $this->agent->user->last_name;
                $event->sheet->mergeCells('A2:G2');
                $event->sheet->setCellValue('A2', 'Agent: ' . $agentName);
                $event->sheet->getStyle('A2')->getFont()->setBold(true)->setSize(12);
                $event->sheet->getStyle('A2')->getAlignment()->setHorizontal('center');

                // Add date range info
                $dateRange = '';
                if ($this->from && $this->to) {
                    $dateRange = 'Period: ' . date('d-m-Y', strtotime($this->from)) . ' to ' . date('d-m-Y', strtotime($this->to));
                } elseif ($this->from) {
                    $dateRange = 'From Date: ' . date('d-m-Y', strtotime($this->from));
                } elseif ($this->to) {
                    $dateRange = 'Till Date: ' . date('d-m-Y', strtotime($this->to));
                } else {
                    $dateRange = 'All Time';
                }

                $event->sheet->mergeCells('A3:G3');
                $event->sheet->setCellValue('A3', $dateRange);
                $event->sheet->getStyle('A3')->getAlignment()->setHorizontal('center');

                // Style the header row (now row 4)
                $event->sheet->getStyle('A4:G4')->getFont()->setBold(true);
                $event->sheet->getStyle('A4:G4')->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFE0E0E0');


                // Add borders to all data cells
                $finalRow = $event->sheet->getHighestRow();
                $event->sheet->getStyle('A1:G' . $finalRow)->getBorders()->getAllBorders()
                    ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            },
        ];
    }
}
