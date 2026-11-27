<?php

namespace App\Services;

use App\Models\SalesMaster;

class LoanStatusService
{
    public const STAGES = [
        'apply_pending' => 'Apply Pending',
        'apply' => 'Apply',
        'sanctioned' => 'Sanctioned',
        'reject' => 'Reject',
        'partial_disburse' => 'Partial Disburse',
        'disbursed' => 'Disbursed',
    ];

    public function stages(): array
    {
        return self::STAGES;
    }

    public function buildRows($saleOrders): array
    {
        $rows = [];

        foreach (self::STAGES as $key => $label) {
            $rows[$key] = [
                'key' => $key,
                'name' => $label,
                'jansamarth' => ['files' => 0, 'kw' => 0.0],
                'other_finance' => ['files' => 0, 'kw' => 0.0],
            ];
        }

        foreach ($saleOrders as $saleOrder) {
            $lender = $this->lender($saleOrder);
            $stage = $this->stageOf($saleOrder);

            if ($lender === null || $stage === null) {
                continue;
            }

            $rows[$stage][$lender]['files']++;
            $rows[$stage][$lender]['kw'] += (float) ($saleOrder->register_kw ?? 0);
        }

        return ['rows' => array_values($rows)];
    }

    public function matchingIds(string $stage, string $lender = ''): array
    {
        if (! array_key_exists($stage, self::STAGES)) {
            return [];
        }

        if ($lender !== '' && ! in_array($lender, ['jansamarth', 'other_finance'], true)) {
            return [];
        }

        $saleOrders = SalesMaster::select('id', 'register_kw', 'file_type')
            ->with('portalData', 'loandata:id,sales_master_id,loan_sanction_date,approved_amount,submitted_at', 'loandisbursements:id,sales_master_id,amount')
            ->where('file_cancel_order', '0')
            ->get();

        $matchingIds = [];

        foreach ($saleOrders as $saleOrder) {
            $orderLender = $this->lender($saleOrder);

            if ($orderLender === null || ($lender !== '' && $orderLender !== $lender)) {
                continue;
            }

            if ($this->stageOf($saleOrder) === $stage) {
                $matchingIds[] = $saleOrder->id;
            }
        }

        return $matchingIds;
    }

    public function stage(string $status): ?string
    {
        if ($status === '' || $status === 'initiated') {
            return 'apply';
        }

        if ($status === 'sanctioned') {
            return 'sanctioned';
        }

        if ($status === 'reject' || $status === 'expired') {
            return 'reject';
        }

        if ($status === 'partial disburse') {
            return 'partial_disburse';
        }

        if ($status === 'disbursed' || $status === 'completed') {
            return 'disbursed';
        }

        return null;
    }

    private function lender($saleOrder): ?string
    {
        $loanTaken = strtolower(trim((string) ($saleOrder->portalData?->loan_taken ?? '')));

        if ($loanTaken === 'yes') {
            return 'jansamarth';
        }

        if ($loanTaken === 'no' && (string) ($saleOrder->file_type ?? '') === 'L') {
            return 'other_finance';
        }

        return null;
    }

    private function stageOf($saleOrder): ?string
    {
        $isOtherFinance = $this->lender($saleOrder) === 'other_finance';

        if ($isOtherFinance && ! $this->hasDate($saleOrder->loandata?->submitted_at)) {
            return 'apply_pending';
        }

        $stage = $this->stage(strtolower(trim((string) ($saleOrder->portalData?->loan_status ?? ''))));

        if ($isOtherFinance) {
            $approvedAmount = (float) ($saleOrder->loandata?->approved_amount ?? 0);
            $disbursedAmount = 0.0;

            foreach ($saleOrder->loandisbursements as $disbursement) {
                $disbursedAmount += (float) $disbursement->amount;
            }

            $loanSanctionDate = trim((string) ($saleOrder->loandata?->loan_sanction_date ?? ''));
            $hasSanctionDate = $loanSanctionDate !== '' && ! in_array($loanSanctionDate, ['0000-00-00', '1970-01-01'], true);

            if ($approvedAmount > 0 && $disbursedAmount > 0) {
                $stage = $disbursedAmount >= $approvedAmount ? 'disbursed' : 'partial_disburse';
            } elseif ($hasSanctionDate) {
                $stage = 'sanctioned';
            }
        }

        return $stage;
    }

    private function hasDate($value): bool
    {
        $value = trim((string) $value);

        return $value !== '' && $value !== '0000-00-00' && $value !== '1970-01-01';
    }
}
