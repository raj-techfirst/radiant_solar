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

        $saleOrders = SalesMaster::select('id', 'register_kw', 'subsidy_request_date', 'subsidy_disbursement_verify_date', 'subsidy_disbursement_date', 'subsidy_giveup')
            ->with('portalData')
            ->where('file_cancel_order', '0')
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
        $hasDate = function ($value): bool {
            $value = trim((string) $value);

            return $value !== '' && $value !== '0000-00-00' && $value !== '1970-01-01';
        };
        $portalText = function (string $column) use ($portal): string {
            return strtolower(trim((string) ($portal?->{$column} ?? '')));
        };

        $agreement = $portalText('vendor_consumer_agreement_uploaded');
        $inspection = $portalText('inspection_status');
        $redeemDate = $hasDate($saleOrder->subsidy_request_date);
        $verifiedDate = $hasDate($saleOrder->subsidy_disbursement_verify_date);
        $disbursedDate = $hasDate($saleOrder->subsidy_disbursement_date);
        $portalInstallationDate = $hasDate($portal?->solar_plant_installation_date);

        switch ($statusKey) {
            case 'vendor_selection_pending':
                return is_null($portal) || $portalText('current_status_of_application') === '';
            case 'agreement_pending':
                return ! is_null($portal) && ($agreement === '' || $agreement === 'no');
            case 'installation_pending':
                return ! is_null($portal) && $agreement === 'yes' && ! $portalInstallationDate;
            case 'inspection_pending':
                return ! is_null($portal) && $portalInstallationDate && $inspection === '';
            case 'inspection_return':
                return ! is_null($portal) && strpos($inspection, 'return') !== false;
            case 'subsidy_not_redeemed':
                return ! is_null($portal) && $inspection === 'approved' && ! $redeemDate;
            case 'subsidy_redeemed':
                return $redeemDate && ! $verifiedDate;
            case 'subsidy_return':
                return ! is_null($portal) && $hasDate($portal->subsidy_return_date) && ! $verifiedDate;
            case 'disbursal_pending':
                return $verifiedDate && ! $disbursedDate;
            case 'disbursed':
                return $disbursedDate;
            case 'give_up':
                return (string) ($saleOrder->subsidy_giveup ?? '') === '1';
        }

        return false;
    }
}
