<?php

namespace App\Imports;

use App\Models\erp\SerialNumber;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class ItemGroupSerialnumber implements ToModel, WithHeadingRow
{
    private $request;

    public function __construct($request)
    {
        $this->request = $request;
    }
    public function model(array $row)
    {
        if (isset($row['serial_number']) && $row['serial_number'] != "") {
            $validator = Validator::make($row, [
                //'serial_number' => 'unique:serial_numbers,serial_number'
                'serial_number' => [
                    'required',
                    Rule::unique('serial_numbers')->whereNull('deleted_at')
                ]
            ]);
            if ($validator->fails()) {
                Log::warning('Duplicate serial number found', [
                    'serial_number' => $row['serial_number'],
                    'item_group_id' => $this->request->item_group_id,
                    'purchase_direct_id' => $this->request->purchase_direct_id,
                ]);
                return null;
            }
            $serialNumber = new SerialNumber();
            $serialNumber->item_group_id = $this->request->item_group_id;
            $serialNumber->status = 'available';
            $serialNumber->location_id = $this->request->purchase_direct->warehous_id;
            $serialNumber->purchase_direct_id = $this->request->purchase_direct_id;
            $serialNumber->purchase_direct_meta_id = $this->request->id;
            $serialNumber->purchase_date = $this->request->purchase_direct->date;
            $serialNumber->serial_number = strtoupper($row['serial_number']);

            // $serialNumber->warranty_start_date = ($row['warranty_start_date'] != "") ? date('Y-m-d', ((trim($row['warranty_start_date']) - 25569) * 86400)) : '';
            // $serialNumber->warranty_end_date = ($row['warranty_end_date'] != "") ? date('Y-m-d', ((trim($row['warranty_end_date']) - 25569) * 86400)) : '';
            // $serialNumber->guarantee_start_date = ($row['guarantee_start_date'] != "") ? date('Y-m-d', ((trim($row['guarantee_start_date']) - 25569) * 86400)) : '';
            // $serialNumber->guarantee_end_date = ($row['guarantee_end_date'] != "") ? date('Y-m-d', ((trim($row['guarantee_end_date']) - 25569) * 86400)) : '';

            $serialNumber->warranty_start_date = ($row['warranty_start_date'] != "" && strtotime($row['warranty_start_date']) !== false)
                ? date('Y-m-d', strtotime($row['warranty_start_date']))
                : '';

            $serialNumber->warranty_end_date = ($row['warranty_end_date'] != "" && strtotime($row['warranty_end_date']) !== false)
                ? date('Y-m-d', strtotime($row['warranty_end_date']))
                : '';

            $serialNumber->guarantee_start_date = ($row['guarantee_start_date'] != "" && strtotime($row['guarantee_start_date']) !== false)
                ? date('Y-m-d', strtotime($row['guarantee_start_date']))
                : '';

            $serialNumber->guarantee_end_date = ($row['guarantee_end_date'] != "" && strtotime($row['guarantee_end_date']) !== false)
                ? date('Y-m-d', strtotime($row['guarantee_end_date']))
                : '';


            $serialNumber->save();
        }
        return array();
    }
}
