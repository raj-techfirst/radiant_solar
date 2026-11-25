<?php

namespace App\Imports\Concerns;

use Illuminate\Support\Collection;

trait RepairsMalformedCsvRow
{
    private function populatedColumnCount(Collection $row): int
    {
        $lastIndex = null;

        foreach (array_values($row->all()) as $index => $value) {
            if ($value !== null && trim((string) $value) !== '') {
                $lastIndex = $index;
            }
        }

        return $lastIndex === null ? 0 : $lastIndex + 1;
    }

    private function headerKeysFromRow(Collection $row): array
    {
        return $row->keys()
            ->reject(function ($key) {
                return is_int($key);
            })
            ->values()
            ->all();
    }

    private function indexesFromHeaderKeys(array $keys): array
    {
        $positions = array_flip($keys);

        $find = function (array $aliases) use ($positions) {
            foreach ($aliases as $alias) {
                if (isset($positions[$alias])) {
                    return $positions[$alias];
                }
            }

            return null;
        };

        return [
            'address' => $find(['consumer_address', 'address']),
            'district' => $find(['district_name']),
            'state' => $find(['state_name']),
            'consumer_number' => $find(['consumer_number', 'consumer_no', 'consumernumber', 'consumerno', 'c_no']),
        ];
    }

    private function repairMalformedRow(Collection $row, int $expectedColumnCount, array $indexes, array $outputKeys = []): Collection
    {
        $values = array_values($row->all());

        if ($expectedColumnCount < 1 || $this->populatedColumnCount($row) <= $expectedColumnCount) {
            return $row;
        }

        $addressIndex = $indexes['address'] ?? null;
        $districtTargetIndex = $indexes['district'] ?? null;
        $stateTargetIndex = $indexes['state'] ?? null;
        $consumerNumberIndex = $indexes['consumer_number'] ?? null;

        if ($addressIndex === null || $districtTargetIndex === null || $stateTargetIndex === null || $consumerNumberIndex === null || $consumerNumberIndex >= $expectedColumnCount) {
            return $row;
        }

        $candidateIndex = null;

        for ($index = $consumerNumberIndex; $index < count($values); $index++) {
            if ($this->looksLikeConsumerNumber($values[$index]) && $this->looksLikeDate($values[$index + 1] ?? '')) {
                $candidateIndex = $index;
                break;
            }
        }

        if ($candidateIndex === null) {
            return $row;
        }

        $districtIndex = $candidateIndex - 2;
        $stateIndex = $candidateIndex - 1;

        if ($districtIndex <= $addressIndex || $stateIndex !== $districtIndex + 1 || $stateIndex >= $expectedColumnCount) {
            return $row;
        }

        $addressParts = array_slice($values, $addressIndex, $districtIndex - $addressIndex);
        $address = implode(',', array_map(function ($part) {
            return $this->cleanCsvFragment($part);
        }, $addressParts));

        $fixed = array_fill(0, $expectedColumnCount, '');

        for ($index = 0; $index < $addressIndex; $index++) {
            $fixed[$index] = $values[$index] ?? '';
        }

        $fixed[$addressIndex] = $address;

        for ($index = $consumerNumberIndex; $index < $expectedColumnCount; $index++) {
            $fixed[$index] = $values[$candidateIndex + ($index - $consumerNumberIndex)] ?? '';
        }

        $fixed[$districtTargetIndex] = $this->cleanCsvFragment($values[$districtIndex] ?? '');
        $fixed[$stateTargetIndex] = $this->cleanCsvFragment($values[$stateIndex] ?? '');

        if (count($outputKeys) === $expectedColumnCount) {
            return collect(array_combine($outputKeys, $fixed));
        }

        return collect($fixed);
    }

    private function looksLikeConsumerNumber($value): bool
    {
        $text = str_replace(["'", '’', ' ', '-'], '', trim((string) $value));

        return preg_match('/^\d{8,16}$/', $text) === 1;
    }

    private function looksLikeDate($value): bool
    {
        $text = trim((string) $value);

        return preg_match('/^\d{1,2}[-.\/]\d{1,2}[-.\/]\d{2,4}/', $text) === 1
            || preg_match('/^\d{1,2}[-.\/][A-Za-z]{3}[-.\/]\d{2,4}/', $text) === 1;
    }

    private function cleanCsvFragment($value): string
    {
        $text = str_replace('\\"', '"', trim((string) $value));

        return trim($text, " \t\n\r\0\x0B\"");
    }
}
