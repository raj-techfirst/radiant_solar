<?php

namespace App\Services;

use App\Models\PortalData;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class PortalDataSyncService
{
    private bool $statusOnly = false;
    private bool $skipped = false;

    private const FIELD_MAP = [
        'current_status_of_application' => 'current_status',
        'vendor_consumer_agreement_uploaded' => 'vendor_consumer_agreement_uploaded',
        'vendor_consumer_agreement_uploading_date' => 'vendor_consumer_agreement_uploading_date',
        'solar_plant_installation_date' => 'solar_plant_installation_date',
        'loan_taken' => 'loan_taken',
        'loan_status' => 'loan_status',
        'loan_rejection_date' => 'loan_rejection_date',
        'loan_disbursed_date_first_tranche' => 'loan_disbursed_date_first_tranche',
        'loan_disbursed_amount_first_tranche' => 'loan_disbursed_amount_first_tranche',
        'loan_first_tranche_disbursal_utr' => 'loan_first_tranche_disbursal_utr',
        'loan_disbursed_date_second_tranche' => 'loan_disbursed_date_second_tranche',
        'loan_disbursed_amount_second_tranche' => 'loan_disbursed_amount_second_tranche',
        'loan_second_tranche_disbursal_utr' => 'loan_second_tranche_disbursal_utr',
        'feasibility_applied' => 'feasibility_applied',
        'installed_pv_module_capacity' => 'installed_pv_module_capacity',
        'module_capacity_wp' => 'module_capacity_wp',
        'module_quantity' => 'module_quantity',
        'inspection_status' => 'inspection_status',
        'inspection_return_date' => 'inspection_return_date',
        'inspection_return_comment' => 'inspection_return_comment',
        'subsidy_amount' => 'subsidy_amount',
        'subsidy_return_date' => 'subsidy_return_date',
        'subsidy_return_to' => 'subsidy_return_to',
        'subsidy_return_comment' => 'subsidy_return_comment',
    ];

    private const DATE_COLUMNS = [
        'vendor_consumer_agreement_uploading_date',
        'solar_plant_installation_date',
        'loan_rejection_date',
        'loan_disbursed_date_first_tranche',
        'loan_disbursed_date_second_tranche',
        'inspection_return_date',
        'subsidy_return_date',
    ];

    public function sync(array $rows, Collection $salesByNumber): int
    {
        $this->statusOnly = false;
        $this->skipped = false;

        if (! $this->hasMappedData($rows)) {
            $this->skipped = true;

            return 0;
        }

        $this->statusOnly = $this->isStatusOnly($rows);
        $records = [];

        foreach ($rows as $row) {
            $number = (string) ($row['number'] ?? '');

            if ($number === '' || ! $salesByNumber->has($number)) {
                continue;
            }

            $salesMaster = $salesByNumber->get($number);
            $salesMasterId = (int) $salesMaster->id;
            $fields = $row['fields'] ?? [];

            $records[$salesMasterId] = $this->attributes($salesMasterId, $salesMaster, $fields);
        }

        if ($records === []) {
            $this->skipped = true;

            return 0;
        }

        DB::transaction(function () use ($records) {
            if ($this->statusOnly) {
                foreach ($records as $attributes) {
                    $portalData = PortalData::firstOrNew(['sales_master_id' => $attributes['sales_master_id']]);

                    if (! $portalData->exists) {
                        $portalData->consumer_number = $attributes['consumer_number'];
                    }

                    $portalData->current_status_of_application = $attributes['current_status_of_application'];
                    $portalData->save();
                }

                return;
            }

            PortalData::whereNotIn('sales_master_id', array_keys($records))->delete();

            foreach ($records as $attributes) {
                PortalData::updateOrCreate(
                    ['sales_master_id' => $attributes['sales_master_id']],
                    $attributes
                );
            }
        });

        return count($records);
    }

    public function wasStatusOnly(): bool
    {
        return $this->statusOnly;
    }

    public function wasSkipped(): bool
    {
        return $this->skipped;
    }

    private function hasMappedData(array $rows): bool
    {
        foreach ($rows as $row) {
            $fields = $row['fields'] ?? [];

            foreach (self::FIELD_MAP as $field) {
                if ($this->valueOrNull($fields[$field] ?? null) !== null) {
                    return true;
                }
            }
        }

        return false;
    }

    private function isStatusOnly(array $rows): bool
    {
        $hasStatus = false;

        foreach ($rows as $row) {
            $fields = $row['fields'] ?? [];

            foreach (self::FIELD_MAP as $column => $field) {
                $value = $this->valueOrNull($fields[$field] ?? null);

                if ($value === null) {
                    continue;
                }

                if ($column === 'current_status_of_application') {
                    $hasStatus = true;
                    continue;
                }

                return false;
            }
        }

        return $hasStatus;
    }

    private function attributes(int $salesMasterId, $salesMaster, array $fields): array
    {
        $attributes = [
            'sales_master_id' => $salesMasterId,
            'consumer_number' => $this->valueOrNull($fields['consumer_number'] ?? ($salesMaster->consumer_number ?? null)),
        ];

        foreach (self::FIELD_MAP as $column => $field) {
            $attributes[$column] = $this->columnValue($fields[$field] ?? null, $column);
        }

        return $attributes;
    }

    private function columnValue($value, string $column): ?string
    {
        $value = $this->valueOrNull($value);

        if ($value === null || ! in_array($column, self::DATE_COLUMNS, true) || ! is_numeric($value)) {
            return $value;
        }

        $serial = (float) $value;

        if ($serial <= 0 || $serial > 100000) {
            return $value;
        }

        return Date::excelToDateTimeObject($serial)->format('d-M-y');
    }

    private function valueOrNull($value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
