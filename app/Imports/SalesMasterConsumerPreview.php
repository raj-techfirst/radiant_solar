<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class SalesMasterConsumerPreview implements ToCollection
{
    private $rows = [];
    private $error = null;
    private $consumerNumberIndex = null;
    private $consumerNameIndex = null;

    public function collection(Collection $rows)
    {
        if ($rows->isEmpty()) {
            $this->error = 'Excel file has no data.';

            return;
        }

        $header = $rows->shift();

        foreach ($header as $index => $value) {
            $key = $this->headerKey($value);

            if (in_array($key, ['consumer_number', 'consumer_no', 'consumernumber', 'consumerno', 'c_no'])) {
                $this->consumerNumberIndex = $index;
            }

            if (in_array($key, ['consumer_name', 'customername', 'name'])) {
                $this->consumerNameIndex = $index;
            }
        }

        if (is_null($this->consumerNumberIndex)) {
            $this->error = 'Required column "Consumer Number" not found in Excel.';

            return;
        }

        foreach ($rows as $row) {
            $name = $this->cell($row, $this->consumerNameIndex);
            $number = trim(str_replace(["'", '’'], '', $this->cell($row, $this->consumerNumberIndex)));

            if ($name === '' && $number === '') {
                continue;
            }

            $this->rows[] = [
                'consumer_name' => $name,
                'consumer_number' => $number,
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
