<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PortalData extends Model
{
    use HasFactory;

    protected $table = 'portal_data';

    protected $guarded = ['id'];

    protected $casts = [
        'sales_master_id' => 'integer',
    ];

    public static function filterableColumns(): array
    {
        return [
            'consumer_number',
            'current_status_of_application',
            'vendor_consumer_agreement_uploaded',
            'vendor_consumer_agreement_uploading_date',
            'solar_plant_installation_date',
            'loan_taken',
            'loan_status',
            'loan_rejection_date',
            'loan_disbursed_date_first_tranche',
            'loan_disbursed_amount_first_tranche',
            'loan_first_tranche_disbursal_utr',
            'loan_disbursed_date_second_tranche',
            'loan_disbursed_amount_second_tranche',
            'loan_second_tranche_disbursal_utr',
            'feasibility_applied',
            'installed_pv_module_capacity',
            'module_capacity_wp',
            'module_quantity',
            'inspection_status',
            'inspection_return_date',
            'inspection_return_comment',
            'subsidy_amount',
            'subsidy_return_date',
            'subsidy_return_to',
            'subsidy_return_comment',
        ];
    }

    public static function filterableColumnLabels(): array
    {
        $labels = [];

        foreach (self::filterableColumns() as $column) {
            $labels[$column] = ucwords(str_replace('_', ' ', $column));
        }

        return $labels;
    }

    public static function applyAdvancedFilters($query, $filters): void
    {
        $allowedColumns = self::filterableColumns();

        foreach ((array) $filters as $filter) {
            $column = $filter['column'] ?? '';
            $operator = $filter['operator'] ?? 'contains';
            $value = trim((string) ($filter['value'] ?? ''));

            if (! in_array($column, $allowedColumns, true)) {
                continue;
            }

            if ($operator === 'empty') {
                $query->where(function ($q) use ($column) {
                    $q->whereNull($column)->orWhere($column, '');
                });

                continue;
            }

            if ($operator === 'not_empty') {
                $query->where(function ($q) use ($column) {
                    $q->whereNotNull($column)->where($column, '<>', '');
                });

                continue;
            }

            if ($value === '') {
                continue;
            }

            switch ($operator) {
                case 'equals':
                    $query->where($column, $value);
                    break;
                case 'not_equals':
                    $query->where($column, '<>', $value);
                    break;
                case 'starts_with':
                    $query->where($column, 'like', $value.'%');
                    break;
                case 'ends_with':
                    $query->where($column, 'like', '%'.$value);
                    break;
                case 'contains':
                default:
                    $query->where($column, 'like', '%'.$value.'%');
                    break;
            }
        }
    }
}
