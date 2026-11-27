<?php

namespace App\Imports;

use App\Imports\Concerns\RepairsMalformedCsvRow;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class SalesMasterConsumerPreview implements ToCollection
{
    use RepairsMalformedCsvRow;

    private const EXCEL_FIELDS = [
        'consumer_number' => ['consumer_number', 'consumer_no', 'consumernumber', 'consumerno', 'c_no'],
        'consumer_name' => ['consumer_name', 'customername', 'name'],
        'mobile' => ['mobile_no_of_consumer', 'mobile_number_of_consumer', 'consumer_mobile', 'mobile_no', 'mobile'],
        'email' => ['email_of_consumer', 'consumer_email', 'email'],
        'address' => ['consumer_address', 'address'],
        'district_name' => ['district_name'],
        'state_name' => ['state_name'],
        'application_number' => ['consumer_registration_number', 'application_number', 'registration_number'],
        'connection_category' => ['connection_category_name_rwa_domestic', 'connection_category_name', 'connection_category'],
        'sanction_load' => ['sanction_load_kwp', 'sanction_load_kw', 'sanction_load'],
        'proposed_pv_capacity' => ['proposed_pv_capacity_kwp', 'proposed_pv_capacity_kw', 'proposed_pv_capacity'],
        'discom_name' => ['discom_name', 'discom'],
        'circle_name' => ['circle_name', 'circle'],
        'division_name' => ['division_name', 'division'],
        'sub_division_name' => ['sub_division_name', 'subdivision_name', 'sub_division', 'subdivision'],
        'loan_applied_at_bank' => ['loan_applied_at_bank', 'loan_applied_bank', 'bank_name'],
        'pv_module_make' => ['pv_module_make', 'module_make'],
        'module_capacity_wp' => ['module_capacity_wp', 'module_capacity'],
        'module_quantity' => ['module_quantity'],
        'inverter_capacity' => ['inverter_capacity_kw', 'inverter_capacity'],
        'inverter_make' => ['inverter_make'],
        'inverter_quantity' => ['inverter_quantity'],
        'subsidy_amount' => ['subsidy_amount_rs', 'subsidy_amount'],
        'last_comment' => ['last_comment'],
        'current_status' => ['status', 'current_status_of_application', 'current_status'],
        'vendor_consumer_agreement_uploaded' => ['vendor_consumer_agreement_uploaded_yes_no', 'vendor_consumer_agreement_uploaded'],
        'vendor_consumer_agreement_uploading_date' => ['vendor_consumer_agreement_uploading_date'],
        'solar_plant_installation_date' => ['solar_plant_installation_date'],
        'loan_taken' => ['loan_taken_yes_no', 'loan_taken'],
        'loan_status' => ['loan_status_pending_sanctioned_rejected', 'loan_status'],
        'loan_rejection_date' => ['loan_rejection_date'],
        'loan_disbursed_date_first_tranche' => ['loan_disbursed_date_first_tranche'],
        'loan_disbursed_amount_first_tranche' => ['loan_disbursed_amount_first_tranche'],
        'loan_first_tranche_disbursal_utr' => ['loan_first_tranche_disbursal_utr'],
        'loan_disbursed_date_second_tranche' => ['loan_disbursed_date_second_tranche'],
        'loan_disbursed_amount_second_tranche' => ['loan_disbursed_amount_second_tranche'],
        'loan_second_tranche_disbursal_utr' => ['loan_second_tranche_disbursal_utr'],
        'feasibility_applied' => ['feasibility_applied_kw', 'feasibility_applied'],
        'installed_pv_module_capacity' => ['installed_pv_module_capacity_kwp', 'installed_pv_module_capacity'],
        'inspection_status' => ['inspection_status_approved_return', 'inspection_status'],
        'inspection_return_date' => ['inspection_return_date'],
        'inspection_return_comment' => ['inspection_return_comment'],
        'subsidy_return_date' => ['subsidy_return_date'],
        'subsidy_return_to' => ['subsidy_return_to_installation_inspection', 'subsidy_return_to'],
        'subsidy_return_comment' => ['subsidy_return_comment'],
    ];

    private const RECOMMENDED_ALIASES = [
        'consumer_name' => ['consumer_name', 'customername', 'name'],
        'discom_name' => ['discom_name', 'discomname', 'discom'],
        'circle_name' => ['circle_name', 'circlename', 'circle'],
        'division_name' => ['division_name', 'divisionname', 'division'],
        'sub_division_name' => ['sub_division_name', 'subdivision_name', 'sub_division', 'subdivision'],
    ];

    private $rows = [];
    private $error = null;
    private $consumerNumberIndex = null;
    private $consumerNameIndex = null;
    private $recommendedIndexes = [];
    private $fieldIndexes = [];

    public function collection(Collection $rows)
    {
        if ($rows->isEmpty()) {
            $this->error = 'Excel file has no data.';

            return;
        }

        $header = $rows->shift();
        $headerColumnCount = $this->populatedColumnCount($header);

        foreach ($header as $index => $value) {
            $key = $this->headerKey($value);

            if (in_array($key, ['consumer_number', 'consumer_no', 'consumernumber', 'consumerno', 'c_no'])) {
                $this->consumerNumberIndex = $index;
            }

            if (in_array($key, ['consumer_name', 'customername', 'name'])) {
                $this->consumerNameIndex = $index;
            }

            foreach (self::RECOMMENDED_ALIASES as $field => $aliases) {
                if (in_array($key, $aliases, true)) {
                    $this->recommendedIndexes[$field] = $index;
                }
            }

            foreach (self::EXCEL_FIELDS as $field => $aliases) {
                if (in_array($key, $aliases, true)) {
                    $this->fieldIndexes[$field] = $index;
                }
            }
        }

        if (is_null($this->consumerNumberIndex)) {
            $this->error = 'Required column "Consumer Number" not found in Excel.';

            return;
        }

        foreach ($rows as $row) {
            $row = $this->repairMalformedRow(
                $row,
                $headerColumnCount,
                [
                    'address' => $this->fieldIndexes['address'] ?? null,
                    'district' => $this->fieldIndexes['district_name'] ?? null,
                    'state' => $this->fieldIndexes['state_name'] ?? null,
                    'consumer_number' => $this->consumerNumberIndex,
                ]
            );

            $name = $this->cell($row, $this->consumerNameIndex);
            $number = trim(str_replace(["'", '’'], '', $this->cell($row, $this->consumerNumberIndex)));

            if ($name === '' && $number === '') {
                continue;
            }

            $recommended = [];

            foreach (self::RECOMMENDED_ALIASES as $field => $aliases) {
                $recommended[$field] = $this->cell($row, $this->recommendedIndexes[$field] ?? null);
            }

            $fields = [];

            foreach (self::EXCEL_FIELDS as $field => $aliases) {
                $fields[$field] = $this->cell($row, $this->fieldIndexes[$field] ?? null);
            }

            foreach (['consumer_number', 'application_number'] as $field) {
                $fields[$field] = trim(str_replace(["'", '’'], '', $fields[$field] ?? ''));
            }

            $fields['consumer_number'] = $number;

            $this->rows[] = [
                'consumer_name' => $name,
                'consumer_number' => $number,
                'recommended' => $recommended,
                'fields' => $fields,
                'number' => self::normalizeNumber($number),
            ];
        }
    }

    public function rows(): array
    {
        return $this->rows;
    }

    public function error(): ?string
    {
        return $this->error;
    }

    public static function normalizeNumber($value): string
    {
        return preg_replace('/\D+/', '', (string) $value);
    }

    private function cell($row, $index): string
    {
        if (is_null($index)) {
            return '';
        }

        $value = $row->get($index);

        if (is_null($value)) {
            return '';
        }

        return trim((string) $value);
    }

    private function headerKey($value): string
    {
        $value = preg_replace('/^\xEF\xBB\xBF/', '', (string) $value);
        $value = strtolower(trim($value));
        $value = preg_replace('/[^a-z0-9]+/', '_', $value);

        return trim($value, '_');
    }
}
