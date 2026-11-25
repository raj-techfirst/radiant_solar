<?php

namespace App\Imports;

use App\Imports\Concerns\RepairsMalformedCsvRow;
use App\Models\District;
use App\Models\Loandata;
use App\Models\SalesMaster;
use App\Models\SubDivision;
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
    use RepairsMalformedCsvRow;

    public const RECOMMENDED_FIELDS = [
        'consumer_name' => 'Consumer Name',
        'discom_name' => 'DISCOM',
        'circle_name' => 'Circle',
        'division_name' => 'Division',
        'sub_division_name' => 'Sub Division',
    ];

    private const DISTRICT_ALIASES = [
        'devbhoomi dwarka' => 'Devbhumi Dwarka',
    ];

    private const FIELDS = [
        'consumer_number' => [
            'label' => 'Consumer Number',
            'aliases' => ['consumer_number', 'consumer_no', 'consumernumber', 'consumerno', 'c_no'],
        ],
        'consumer_name' => [
            'label' => 'Consumer Name',
            'aliases' => ['consumer_name', 'customername', 'name'],
        ],
        'discom_name' => [
            'label' => 'Discom Name',
            'aliases' => ['discom_name', 'discomname', 'discom'],
        ],
        'circle_name' => [
            'label' => 'Circle Name',
            'aliases' => ['circle_name', 'circlename', 'circle'],
        ],
        'division_name' => [
            'label' => 'Division Name',
            'aliases' => ['division_name', 'divisionname', 'division'],
        ],
        'sub_division_name' => [
            'label' => 'Sub Division Name',
            'aliases' => ['sub_division_name', 'subdivision_name', 'sub_division', 'subdivision'],
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
    private $rowNumberSet = [];
    private $recommendedFields = [];
    private $salesMasterIdsByRow = [];
    private $salesMastersById = null;
    private $districtsByName = null;
    private $subDivisionsByName = null;
    private $loansBySalesMasterId = null;
    private $pendingLoanInserts = [];
    private $districtNotFound = [];
    private $subDivisionNotFound = [];
    private $ignoredDateTimes = [];

    public function __construct(array $rowNumbers = [], array $recommendedFields = [], array $salesMasterIdsByRow = [])
    {
        $this->rowNumbers = array_values(array_unique(array_filter(array_map('intval', $rowNumbers))));
        $this->rowNumberSet = array_fill_keys($this->rowNumbers, true);
        $this->recommendedFields = array_values(array_unique(array_intersect(
            $recommendedFields,
            array_keys(self::RECOMMENDED_FIELDS)
        )));

        foreach ($salesMasterIdsByRow as $rowNumber => $salesMasterId) {
            $rowNumber = (int) $rowNumber;
            $salesMasterId = (int) $salesMasterId;

            if ($rowNumber > 0 && $salesMasterId > 0) {
                $this->salesMasterIdsByRow[$rowNumber] = $salesMasterId;
            }
        }
    }

    public function collection(Collection $rows)
    {
        $offset = $this->getChunkOffset();

        foreach ($rows as $index => $row) {
            $rowNumber = $offset !== null ? $offset + $index : $index + 2;
            $rowNumber = (int) $rowNumber;

            if (! empty($this->rowNumberSet) && ! isset($this->rowNumberSet[$rowNumber])) {
                continue;
            }

            $row = $row instanceof Collection ? $row : collect($row);
            $headerKeys = $this->headerKeysFromRow($row);
            $row = $this->repairMalformedRow(
                $row,
                count($headerKeys),
                $this->indexesFromHeaderKeys($headerKeys),
                $headerKeys
            );

            $this->processRow($row, $rowNumber);
        }

        $this->flushPendingLoanInserts();
    }

    public function chunkSize(): int
    {
        return 1000;
    }

    public static function missingHeaders(array $headers, array $fieldKeys = []): array
    {
        $available = [];

        foreach ($headers as $header) {
            $available[] = self::normalizeHeader($header);
        }

        $defaultKeys = array_diff(array_keys(self::FIELDS), array_keys(self::RECOMMENDED_FIELDS));
        $fields = empty($fieldKeys)
            ? array_intersect_key(self::FIELDS, array_flip($defaultKeys))
            : array_intersect_key(self::FIELDS, array_flip(array_merge(['consumer_number'], $fieldKeys)));
        $missing = [];

        foreach ($fields as $field) {
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
        $errors = $this->errors;

        if (! empty($this->districtNotFound)) {
            $errors[] = $this->notFoundSummary('District', $this->districtNotFound, 'Districts left unchanged.');
        }

        if (! empty($this->subDivisionNotFound)) {
            $errors[] = $this->notFoundSummary('Sub Division', $this->subDivisionNotFound, 'Sub Divisions left unchanged.');
        }

        foreach ($this->ignoredDateTimes as $label => $count) {
            $errors[] = "Ignored time-only value in {$label} in {$count} row(s). Value set to null.";
        }

        return $errors;
    }

    private function processRow(Collection $row, int $rowNumber): void
    {
        $consumerNumber = trim(str_replace(["'", '’'], '', (string) $this->value($row, 'consumer_number')));

        if ($consumerNumber === '') {
            $this->skippedCount++;
            $this->addError("Row {$rowNumber}: Consumer Number is empty.");

            return;
        }

        $salesMaster = $this->resolveSalesMaster($rowNumber, $consumerNumber);

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

        if (! empty($this->recommendedFields)) {
            $this->applyRecommendedFields($row, $rowNumber, $salesUpdates, $consumerNumber);
        } else {
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
                $districtId = $this->districtIdByName($districtName);

                if ($districtId) {
                    $salesUpdates['district_id'] = $districtId;
                } else {
                    $this->districtNotFound[$districtName] = ($this->districtNotFound[$districtName] ?? 0) + 1;
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
        }

        if (empty($salesUpdates) && empty($loanUpdates)) {
            $this->skippedCount++;
            $this->addError("Row {$rowNumber}: No mapped values to update.");

            return;
        }

        try {
            if (! empty($salesUpdates)) {
                foreach ($salesUpdates as $column => $value) {
                    $salesMaster->{$column} = $value;
                }

                $salesMaster->save();
            }

            if (! empty($loanUpdates)) {
                $this->saveLoanUpdates($salesMaster, $loanUpdates);
            }

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

    private function applyRecommendedFields(Collection $row, int $rowNumber, array &$salesUpdates, string $consumerNumber): void
    {
        $textColumns = [
            'consumer_name' => 'consumer_name',
            'discom_name' => 'discom',
            'circle_name' => 'circle',
            'division_name' => 'division',
        ];

        foreach ($this->recommendedFields as $field) {
            $value = $this->value($row, $field);

            if (! $this->hasValue($value)) {
                continue;
            }

            $value = trim((string) $value);

            if (isset($textColumns[$field])) {
                $salesUpdates[$textColumns[$field]] = $value;
                continue;
            }

            if ($field === 'sub_division_name') {
                $subDivisionId = $this->subDivisionIdByName($value);

                if ($subDivisionId) {
                    $salesUpdates['sub_division_id'] = $subDivisionId;
                } else {
                    $this->subDivisionNotFound[$value] = ($this->subDivisionNotFound[$value] ?? 0) + 1;
                    Log::warning('Sales master bulk update: sub division not found', [
                        'row' => $rowNumber,
                        'sub_division' => $value,
                        'consumer_number' => $consumerNumber,
                    ]);
                }
            }
        }
    }

    private function resolveSalesMaster(int $rowNumber, string $consumerNumber): ?SalesMaster
    {
        if (! empty($this->salesMasterIdsByRow)) {
            $salesMasterId = $this->salesMasterIdsByRow[$rowNumber] ?? null;

            if (! $salesMasterId) {
                return null;
            }

            return $this->salesMastersById()->get($salesMasterId);
        }

        return SalesMaster::whereRaw('TRIM(consumer_number) = ?', [$consumerNumber])->first();
    }

    private function salesMastersById(): Collection
    {
        if ($this->salesMastersById === null) {
            $this->salesMastersById = SalesMaster::whereIn('id', array_values($this->salesMasterIdsByRow))
                ->get()
                ->keyBy('id');
        }

        return $this->salesMastersById;
    }

    private function districtIdByName(string $name): ?int
    {
        if ($this->districtsByName === null) {
            $this->districtsByName = [];

            foreach (District::withTrashed()->select('id', 'name')->orderBy('id')->get() as $district) {
                $key = $this->normalizedName($district->name);

                if ($key !== '' && ! isset($this->districtsByName[$key])) {
                    $this->districtsByName[$key] = (int) $district->id;
                }
            }
        }

        $name = self::DISTRICT_ALIASES[$this->normalizedName($name)] ?? $name;

        return $this->districtsByName[$this->normalizedName($name)] ?? null;
    }

    private function subDivisionIdByName(string $name): ?int
    {
        if ($this->subDivisionsByName === null) {
            $this->subDivisionsByName = [];

            foreach (SubDivision::query()->select('id', 'name')->orderBy('id')->get() as $subDivision) {
                $key = $this->normalizedName($subDivision->name);

                if ($key !== '' && ! isset($this->subDivisionsByName[$key])) {
                    $this->subDivisionsByName[$key] = (int) $subDivision->id;
                }
            }
        }

        return $this->subDivisionsByName[$this->normalizedName($name)] ?? null;
    }

    private function saveLoanUpdates(SalesMaster $salesMaster, array $loanUpdates): void
    {
        if (empty($this->salesMasterIdsByRow)) {
            Loandata::updateOrCreate(
                ['sales_master_id' => $salesMaster->id],
                $loanUpdates
            );

            return;
        }

        $salesMasterId = (int) $salesMaster->id;

        if (isset($this->pendingLoanInserts[$salesMasterId])) {
            $this->pendingLoanInserts[$salesMasterId] = array_merge(
                $this->pendingLoanInserts[$salesMasterId],
                $loanUpdates
            );

            return;
        }

        $loan = $this->loansBySalesMasterId()->get($salesMasterId);

        if ($loan) {
            foreach ($loanUpdates as $column => $value) {
                $loan->{$column} = $value;
            }

            $loan->save();

            return;
        }

        $this->pendingLoanInserts[$salesMasterId] = array_merge(
            ['sales_master_id' => $salesMasterId],
            $loanUpdates
        );
    }

    private function loansBySalesMasterId(): Collection
    {
        if ($this->loansBySalesMasterId === null) {
            $this->loansBySalesMasterId = Loandata::whereIn(
                'sales_master_id',
                array_values($this->salesMasterIdsByRow)
            )->get()->keyBy('sales_master_id');
        }

        return $this->loansBySalesMasterId;
    }

    private function flushPendingLoanInserts(): void
    {
        if (empty($this->pendingLoanInserts)) {
            return;
        }

        $now = now();
        $dynamicColumns = [];
        $reservedColumns = ['sales_master_id', 'created_at', 'updated_at'];

        foreach ($this->pendingLoanInserts as $loan) {
            foreach ($loan as $column => $value) {
                if (! in_array($column, $reservedColumns, true) && ! in_array($column, $dynamicColumns, true)) {
                    $dynamicColumns[] = $column;
                }
            }
        }

        sort($dynamicColumns);
        $columns = array_merge(['sales_master_id'], $dynamicColumns, ['created_at', 'updated_at']);
        $rows = [];

        foreach ($this->pendingLoanInserts as $loan) {
            $row = [];

            foreach ($columns as $column) {
                if ($column === 'created_at' || $column === 'updated_at') {
                    $row[$column] = $now;
                } else {
                    $row[$column] = array_key_exists($column, $loan) ? $loan[$column] : null;
                }
            }

            $rows[] = $row;
        }

        try {
            DB::table('loandatas')->insert($rows);
        } catch (\Throwable $e) {
            $this->addError('Loan details could not be saved for ' . count($rows) . ' record(s).');
            Log::warning('Sales master bulk update: loan insert failed', [
                'count' => count($rows),
                'error' => $e->getMessage(),
            ]);
        }

        $this->pendingLoanInserts = [];
    }

    private function normalizedName($value): string
    {
        return strtolower(trim((string) $value));
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

        if (preg_match('/^\d{1,3}:\d{1,2}(?:\.\d+)?$/', $text)) {
            $this->ignoredDateTimes[$label] = ($this->ignoredDateTimes[$label] ?? 0) + 1;

            return [true, null];
        }

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

    private function notFoundSummary(string $label, array $counts, string $suffix): string
    {
        arsort($counts);
        $parts = [];

        foreach ($counts as $name => $count) {
            $parts[] = $name . ' (' . $count . ')';
        }

        return $label . ' not found in ' . array_sum($counts) . ' row(s): ' . implode(', ', $parts) . '. ' . $suffix;
    }
}
