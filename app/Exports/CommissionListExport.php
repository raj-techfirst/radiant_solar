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
use App\Models\User;
use App\Models\AgentSalesPerson;

class CommissionListExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithEvents
{
    protected $fromDate;
    protected $toDate;
    protected $agentFilter;

    public function __construct($fromDate = null, $toDate = null, $agentFilter = null)
    {
        $this->fromDate = $fromDate ? date('Y-m-d', strtotime($fromDate)) : null;
        $this->toDate = $toDate ? date('Y-m-d', strtotime($toDate)) : null;
        $this->agentFilter = $agentFilter;
    }

    public function collection()
    {
        $companyRows = User::select('users.*','agent_sales_people.id as agent_id')
            ->join('agent_sales_people', 'agent_sales_people.user_id', '=', 'users.id');

        if (!empty($this->agentFilter)) {
            $companyRows->where('agent_sales_people.id', $this->agentFilter);
        }

        return $companyRows->get();
    }

    public function headings(): array
    {
        return [
            'SR',
            'Agent Sales Person',
            'No of File',
            'KW',
            'Commission',
            'Sub Commission',
            'Installation',
            'Total Payable',
            'Total Paid',
            'Pending Payout',
            'Customer Payment Pending'
        ];
    }

    public function map($row): array
    {
        static $index = 0;
        $index++;

        $stats = getCommissionData($row->agent_id, $row->id, $this->fromDate, $this->toDate);

        return [
            $index,
            $row->name . ' ' . $row->last_name,
            (int)($stats['no_of_file'] ?? 0),
            $stats['kw'] ?? 0,
            $stats['commission'] ?? 0,
            $stats['sub_commission'] ?? 0,
            $stats['installation'] ?? 0,
            $stats['total_payable'] ?? 0,
            $stats['total_paid'] ?? 0,
            $stats['pending_payout'] ?? 0,
            $stats['customer_payment_pending'] ?? 0
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,   // SR
            'B' => 25,  // Agent Sales Person
            'C' => 12,  // No of File
            'D' => 10,  // KW
            'E' => 15,  // Commission
            'F' => 15,  // Sub Commission
            'G' => 15,  // Installation
            'H' => 15,  // Total Payable
            'I' => 15,  // Total Paid
            'J' => 15,  // Pending Payout
            'K' => 20,  // Customer Payment Pending
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
                $event->sheet->insertNewRowBefore(1, 2);
                $event->sheet->mergeCells('A1:K1');
                $event->sheet->setCellValue('A1', 'COMMISSION LIST REPORT');
                $event->sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
                $event->sheet->getStyle('A1')->getAlignment()->setHorizontal('center');

                // Add date range info
                $dateRange = '';
                if ($this->fromDate && $this->toDate) {
                    $dateRange = 'Period: ' . date('d-m-Y', strtotime($this->fromDate)) . ' to ' . date('d-m-Y', strtotime($this->toDate));
                } elseif ($this->fromDate) {
                    $dateRange = 'From Date: ' . date('d-m-Y', strtotime($this->fromDate));
                } elseif ($this->toDate) {
                    $dateRange = 'Till Date: ' . date('d-m-Y', strtotime($this->toDate));
                }

                if ($dateRange) {
                    $event->sheet->mergeCells('A2:K2');
                    $event->sheet->setCellValue('A2', $dateRange);
                    $event->sheet->getStyle('A2')->getAlignment()->setHorizontal('center');
                }

                // Style the header row (now row 3)
                $event->sheet->getStyle('A3:K3')->getFont()->setBold(true);
                $event->sheet->getStyle('A3:K3')->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFE0E0E0');

                // Add borders to all data cells
                $lastRow = $event->sheet->getHighestRow();
                $event->sheet->getStyle('A1:K' . $lastRow)->getBorders()->getAllBorders()
                    ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            },
        ];
    }
}
