<?php

namespace App\Imports;

use App\Models\erp\SerialNumber;
use App\Models\SerialNumberLog;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ItemGroupSerialnumberDC implements ToModel, WithHeadingRow
{
    private $request;

    public function __construct($request)
    {
        $this->request = $request;
    }
    public function model(array $row)
    {
        if (isset($row['serial_number']) && $row['serial_number'] != "") {

            $checkSerialNumber = SerialNumber::where('serial_number', $row['serial_number'])->first();
            if (!is_null($checkSerialNumber)) {
                $serialNumber = new SerialNumberLog();
                $serialNumber->serial_number_id = $checkSerialNumber->id;
                $serialNumber->delivery_challan_id = $this->request->delivery_challan_id;
                $serialNumber->delivery_challan_meta_id = $this->request->id;
                $serialNumber->save();

                $checkSerialNumber->status = "sold";
                $checkSerialNumber->save();
            }

        }
        return array();
    }
}
