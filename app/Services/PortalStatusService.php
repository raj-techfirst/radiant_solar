<?php

namespace App\Services;

use App\Models\SalesMaster;

class PortalStatusService
{
    public const STATUSES = [
        'vendor_selection_pending' => 'Vendar Selection (Pending)',
        'agreement_pending' => 'Upload Agreement (Pending)',
        'installation_pending' => 'Installation (Pending)',
        'inspection_pending' => 'Inspection (Pending)',
        'inspection_return' => 'Inspection (Return)',
        'subsidy_not_redeemed' => 'Subsidy Request (Not Redeemed)',
        'subsidy_redeemed' => 'Subsidy Request (Redeemed)',
        'subsidy_return' => 'Subsidy Request (Return)',
        'disbursal_pending' => 'Subsidy Disbursal (Pending)',
        'disbursed' => 'Subsidy Disbursal (Disbursed)',
        'give_up' => 'Subsidy Request (Give Up)',
    ];

    public function statuses(): array
    {
        return self::STATUSES;
    }

    public function matchingIds(string $statusKey): array
    {
        if (! array_key_exists($statusKey, self::STATUSES)) {
            return [];
        }

        $saleOrders = SalesMaster::select('id', 'register_kw', 'consumer_type', 'file_cancel_order', 'subsidy_request_date', 'subsidy_disbursement_verify_date', 'subsidy_disbursement_date', 'subsidy_giveup')
            ->with('portalData')
            ->get();

        $assignedIds = [];
        $matchingIds = [];

        foreach (self::STATUSES as $key => $label) {
            foreach ($saleOrders as $saleOrder) {
                if (isset($assignedIds[$saleOrder->id])) {
                    continue;
                }

                if (! $this->matches($key, $saleOrder)) {
                    continue;
                }

                $assignedIds[$saleOrder->id] = true;

                if ($key === $statusKey) {
                    $matchingIds[] = $saleOrder->id;
                }
            }

            if ($key === $statusKey) {
                break;
            }
        }

        return $matchingIds;
    }

    public function matches(string $statusKey, $saleOrder): bool
    {
        $portal = $saleOrder->portalData;
        $isCancelled = (string) ($saleOrder->file_cancel_order ?? '') === '1';
        $portalText = function (string $column) use ($portal): string {
            return strtolower(trim((string) ($portal?->{$column} ?? '')));
        };

        if ($statusKey === 'vendor_selection_pending') {
            return ! $isCancelled
                && strtolower(trim((string) ($saleOrder->consumer_type ?? ''))) === 'resident'
                && (is_null($portal) || $portalText('current_status_of_application') === '');
        }

        if ($statusKey === 'inspection_return') {
            return ! $isCancelled
                && ! is_null($portal)
                && strpos($portalText('inspection_status'), 'return') !== false;
        }

        $label = strtolower(trim((string) (self::STATUSES[$statusKey] ?? '')));

        if ($label === '') {
            return false;
        }

        return $portalText('current_status_of_application') === $label;
    }
}
