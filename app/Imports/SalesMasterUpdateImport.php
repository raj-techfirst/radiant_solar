<?php

namespace App\Imports;

use App\Models\District;
use App\Models\Loandata;
use App\Models\SalesMaster;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\RemembersChunkOffset;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class SalesMasterUpdateImport implements ToCollection, WithHeadingRow, WithChunkReading
{
    use RemembersChunkOffset;

    private const FIELDS = [
        'consumer_number' => [
            'label' => 'Consumer Number',
            'aliases' => ['consumer_number', 'consumer_no', 'consumernumber', 'consumerno', 'c_no'],
        ],
        'application_submitted_date' => [
            'label' => 'Application Submitted Date',
            'aliases' => ['application_submitted_date', 'application_submitted_at'],
        ],
        'application_number' => [
            'label' => 'Application Number',
            'aliases' => ['application_number'],
        ],
        'consumer_address' => [
            'label' => 'Consumer Address',
            'aliases' => ['consumer_address', 'address'],
        ],
        'district_name' => [
            'label' => 'District Name',
            'aliases' => ['district_name'],
        ],
        'sanction_load' => [
            'label' => 'Sanction Load (kWp)',
            'aliases' => ['sanction_load_kwp', 'sanction_load_kw', 'sanction_load'],
        ],
        'register_kw' => [
            'label' => 'Proposed PV Capacity (kWp)',
            'aliases' => ['proposed_pv_capacity_kwp', 'proposed_pv_capacity_kw', 'proposed_pv_capacity', 'registation_kw'],
        ],
        'loan_application_date' => [
            'label' => 'Loan Application Date',
            'aliases' => ['loan_application_date'],
        ],
        'loan_portal' => [
            'label' => 'Loan Applied at Bank',
            'aliases' => ['loan_applied_at_bank', 'loan_applied_bank', 'loan_portal'],
        ],
        'loan_sanction_date' => [
            'label' => 'Loan Sanctioned date',
            'aliases' => ['loan_sanctioned_date', 'loan_sanction_date'],
        ],
        'feasibility_date' => [
            'label' => 'Feasibility Approved Date',
            'aliases' => ['feasibility_approved_date', 'feasibility_date'],
        ],
        'meter_application_date' => [
            'label' => 'Inspection Date',
            'aliases' => ['inspection_date', 'meter_application_date'],
        ],
        'subsidy_request_date' => [
            'label' => 'Subsidy Redeem Date',
            'aliases' => ['subsidy_redeem_date', 'subsidy_request_date'],
        ],
        'subsidy_disbursement_verify_date' => [
            'label' => 'Subsidy Verified Date',
            'aliases' => ['subsidy_verified_date', 'subsidy_disbursement_verify_date'],
        ],
        'subsidy_disbursement_date' => [
            'label' => 'Subsidy Disbursed Date',
            'aliases' => ['subsidy_disbursed_date', 'subsidy_disbursement_date'],
        ],
    ];

    private $updatedCount = 0;
    private $skippedCount = 0;
    private $notFound = [];
    private $errors = [];
    private $rowNumbers = [];

    public function __construct(array $rowNumbers = [])
    {
        $this->rowNumbers = array_values(array_unique(array_filter(array_map('intval', $rowNumbers))));
    }

    public function collection(Collection $rows)
    {
        $offset = $this->getChunkOffset();

        foreach ($rows as $index => $row) {
            $rowNumber = $offset !== null ? $offset + $index : $index + 2;
            $rowNumber = (int) $rowNumber;

            if (! empty($this->rowNumbers) && ! in_array($rowNumber, $this->rowNumbers, true)) {
                continue;
            }

            $this->processRow($row instanceof Collection ? $row : collect($row), $rowNumber);
        }
    }

    public function chunkSize(): int
    {
        return 100;
    }

    public static function missingHeaders(array $headers): array
    {
        $available = [];

        foreach ($headers as $header) {
            $available[] = self::normalizeHeader($header);
        }

        $missing = [];

        foreach (self::FIELDS as $field) {
            $found = false;

            foreach ($field['aliases'] as $alias) {
                if (in_array(self::normalizeHeader($alias), $available, true)) {
                    $found = true;
                    break;
                }
            }

            if (! $found) {
                $missing[] = $field['label'];
            }
        }

        return $missing;
    }

    public static function normalizeHeader($value): string
    {
        $value = preg_replace('/^\xEF\xBB\xBF/', '', (string) $value);
        $value = strtolower(trim($value));
        $value = preg_replace('/[^a-z0-9]+/', '_', $value);

        return trim($value, '_');
    }

    public function updatedCount(): int
    {
        return $this->updatedCount;
    }

    public function skippedCount(): int
    {
        return $this->skippedCount;
    }

    public function notFound(): array
    {
        return $this->notFound;
    }

    public function errors(): array
    {
        return $this->errors;
    }

    private function processRow(Collection $row, int $rowNumber): void
    {
        $consumerNumber = trim(str_replace(["'", '’'], '', (string) $this->value($row, 'consumer_number')));

        if ($consumerNumber === '') {
            $this->skippedCount++;
            $this->addError("Row {$rowNumber}: Consumer Number is empty.");

            return;
        }

        $salesMaster = SalesMaster::whereRaw('TRIM(consumer_number) = ?', [$consumerNumber])->first();

        if (! $salesMaster) {
            $this->skippedCount++;
            $this->notFound[] = [
                'row' => $rowNumber,
                'consumer_number' => $consumerNumber,
            ];

            return;
        }

        $salesUpdates = [];
        $loanUpdates = [];

        $textFields = [
            'application_number' => 'ragistration_number',
            'consumer_address' => 'address',
        ];

        foreach ($textFields as $field => $column) {
            $value = $this->value($row, $field);

            if ($this->hasValue($value)) {
                $salesUpdates[$column] = trim((string) $value);
            }
        }

        $districtName = $this->value($row, 'district_name');

        if ($this->hasValue($districtName)) {
            $districtName = trim((string) $districtName);
            $district = District::whereRaw('LOWER(TRIM(name)) = ?', [strtolower($districtName)])->first();

            if ($district) {
                $salesUpdates['district_id'] = $district->id;
            } else {
                $this->addError("Row {$rowNumber}: District \"{$districtName}\" not found. District left unchanged.");
                Log::warning("Sales master bulk update: district not found", [
                    'row' => $rowNumber,
                    'district' => $districtName,
                    'consumer_number' => $consumerNumber,
                ]);
            }
        }

        $numberFields = [
            'sanction_load' => 'contracted_load',
            'register_kw' => 'register_kw',
        ];

        foreach ($numberFields as $field => $column) {
            $value = $this->parseNumber($this->value($row, $field));

            if ($value !== null) {
                $salesUpdates[$column] = $value;
            }
        }

        $dateFields = [
            'application_submitted_date' => ['label' => 'Application Submitted Date', 'column' => 'ragistration_date', 'target' => 'sales'],
            'feasibility_date' => ['label' => 'Feasibility Approved Date', 'column' => 'feasibility_date', 'target' => 'sales'],
            'meter_application_date' => ['label' => 'Inspection Date', 'column' => 'meter_application_date', 'target' => 'sales'],
            'subsidy_request_date' => ['label' => 'Subsidy Redeem Date', 'column' => 'subsidy_request_date', 'target' => 'sales'],
            'subsidy_disbursement_verify_date' => ['label' => 'Subsidy Verified Date', 'column' => 'subsidy_disbursement_verify_date', 'target' => 'sales'],
            'subsidy_disbursement_date' => ['label' => 'Subsidy Disbursed Date', 'column' => 'subsidy_disbursement_date', 'target' => 'sales'],
            'loan_application_date' => ['label' => 'Loan Application Date', 'column' => 'submitted_at', 'target' => 'loan'],
            'loan_sanction_date' => ['label' => 'Loan Sanctioned date', 'column' => 'loan_sanction_date', 'target' => 'loan'],
        ];

        foreach ($dateFields as $field => $config) {
            [$shouldSet, $value] = $this->parseDate($this->value($row, $field), $rowNumber, $config['label']);

            if ($shouldSet) {
                if ($config['target'] === 'loan') {
                    $loanUpdates[$config['column']] = $value;
                } else {
                    $salesUpdates[$config['column']] = $value;
                }
            }
        }

        $loanPortal = $this->value($row, 'loan_portal');

        if ($this->hasValue($loanPortal)) {
            $loanUpdates['loan_portal'] = trim((string) $loanPortal);
        }

        if (empty($salesUpdates) && empty($loanUpdates)) {
            $this->skippedCount++;
            $this->addError("Row {$rowNumber}: No mapped values to update.");

            return;
        }

        try {
            DB::transaction(function () use ($salesMaster, $salesUpdates, $loanUpdates) {
                if (! empty($salesUpdates)) {
                    foreach ($salesUpdates as $column => $value) {
                        $salesMaster->{$column} = $value;
                    }

                    $salesMaster->save();
                }

                if (! empty($loanUpdates)) {
                    Loandata::updateOrCreate(
                        ['sales_master_id' => $salesMaster->id],
                        $loanUpdates
                    );
                }
            });

            $this->updatedCount++;
        } catch (\Throwable $e) {
            $this->skippedCount++;
            $this->addError("Row {$rowNumber}: Update failed. {$e->getMessage()}");
            Log::warning('Sales master bulk update failed', [
                'row' => $rowNumber,
                'consumer_number' => $consumerNumber,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function value(Collection $row, string $field)
    {
        foreach (self::FIELDS[$field]['aliases'] as $alias) {
            if ($row->has($alias)) {
                return $row->get($alias);
            }
        }

        return null;
    }

    private function hasValue($value): bool
    {
        if (is_null($value)) {
            return false;
        }

        if (is_string($value)) {
            return trim($value) !== '';
        }

        return true;
    }

    private function parseNumber($value): ?string
    {
        if (! $this->hasValue($value)) {
            return null;
        }

        $clean = preg_replace('/[^0-9.\-]/', '', (string) $value);

        if ($clean === '' || $clean === '-' || $clean === '.') {
            return null;
        }

        return number_format((float) $clean, 2, '.', '');
    }

    private function parseDate($value, int $rowNumber, string $label): array
    {
        if (! $this->hasValue($value)) {
            return [false, null];
        }

        if ($value instanceof \DateTimeInterface) {
            return [true, Carbon::instance($value)->format('Y-m-d')];
        }

        if (is_numeric($value)) {
            try {
                return [true, Carbon::createFromTimestamp((int) round(((float) $value - 25569) * 86400))->format('Y-m-d')];
            } catch (\Throwable $e) {
            }
        }

        $text = trim((string) $value);

        foreach (['d-m-Y', 'd/m/Y', 'Y-m-d', 'd.m.Y'] as $format) {
            $parsed = \DateTime::createFromFormat('!' . $format, $text);
            $errors = \DateTime::getLastErrors();

            if ($parsed && ($errors === false || ($errors['warning_count'] === 0 && $errors['error_count'] === 0))) {
                return [true, Carbon::instance($parsed)->format('Y-m-d')];
            }
        }

        try {
            return [true, Carbon::parse($text)->format('Y-m-d')];
        } catch (\Throwable $e) {
            $this->addError("Row {$rowNumber}: Invalid date in {$label} ({$text}). Value set to null.");
            Log::warning('Sales master bulk update: invalid date', [
                'row' => $rowNumber,
                'field' => $label,
                'value' => $text,
            ]);

            return [true, null];
        }
    }

    private function addError(string $message): void
    {
        $this->errors[] = $message;
    }
}
