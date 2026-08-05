<?php

namespace App\Http\Controllers\Api\erp;

use App\Http\Controllers\Controller;
use App\Models\erp\Warehouse;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GeneralApiController extends Controller
{
    public function supplierDropdown()
    {
        try {
            $supplierList = Supplier::select('id','name','mobile')->get();
            if ($supplierList->isEmpty()) {
                $response = [
                    'status' => false,
                    'message' => 'No data found',
                    'suppliers' => []
                ];
            }
            else
            {
                $response = [
                    'status' => true,
                    'message' => 'Success',
                    'suppliers' => $supplierList
                ];
            }
            return response($response, 200);
        } catch (\Exception $e) {
            Log::error('Error deleting purchase direct: ' . $e->getMessage());
            return response([
                'status' => false,
                'message' => 'An error occurred. Please try again later.'
            ], 500);
        }
    }

    public function warehouseDropdown()
    {
        try {
            $warehouseList = Warehouse::select('id','name')->get();
            if ($warehouseList->isEmpty()) {
                $response = [
                    'status' => false,
                    'message' => 'No data found',
                    'warehouses' => []
                ];
            }
            else
            {
                $response = [
                    'status' => true,
                    'message' => 'Success',
                    'warehouses' => $warehouseList
                ];
            }
            return response($response, 200);
        } catch (\Exception $e) {
            Log::error('Error deleting purchase direct: ' . $e->getMessage());
            return response([
                'status' => false,
                'message' => 'An error occurred. Please try again later.'
            ], 500);
        }
    }
}
