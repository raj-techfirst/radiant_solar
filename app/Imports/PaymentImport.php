<?php

namespace App\Imports;

use App\Models\PaymetCollection;
use App\Models\SalesMaster;
use App\Models\SalesQuatation;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class PaymentImport implements ToModel, WithHeadingRow
{
    /**
     * @param Collection $collection
     */
    public function model(array $row)
    {
   
        if (isset($row['c_no']) && $row['c_no'] != "") {
            $salesMaster = SalesMaster::where('consumer_number', $row['c_no'])->first();
            if (!is_null($salesMaster)) {
                $paymetCollection = new PaymetCollection();
                $paymetCollection->sales_master_id  = $salesMaster->id;
                $paymetCollection->payment_type = $row['type'];
                $paymetCollection->amount = $row['amount'];
                $paymetCollection->payment_date = ($row['date'] != "") ? date('Y-m-d', (($row['date'] - 25569) * 86400)) : '';

                if ($row['type'] == 'Cheque') {
                    $paymetCollection->cheque_number = $row['nos'];
                } else if ($row['type'] == 'NEFT') {
                    $paymetCollection->utr_number = $row['nos'];
                } else if ($row['type'] == 'UPI') {
                    $paymetCollection->upi_id = $row['nos'];
                } else {
                    $paymetCollection->upi_id = $row['nos'];
                }

                $paymetCollection->bank_name = $row['bank_name'];
                $paymetCollection->branch_name = $row['branch_name'];

                $paymetCollection->remark = $row['remark'];

                if ($row['status'] == 'Approved') {
                    $paymetCollection->status = 1;
                }

                $result = $paymetCollection->save();
              
            }
        }
        return $row;
    }
}
