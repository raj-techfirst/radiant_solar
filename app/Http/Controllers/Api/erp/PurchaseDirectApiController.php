<?php

namespace App\Http\Controllers\Api\erp;

use App\Http\Controllers\Controller;
use App\Models\erp\PurchaseDirect;
use App\Models\erp\PurchaseDirectMeta;
use App\Models\erp\WarehouseStock;
use App\Models\erp\WarehouseStockHistory;
use App\Models\ItemGroup;
use App\Models\Product;
use App\Models\Year;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class PurchaseDirectApiController extends Controller
{
    public function index()
    {
        try {
            $year_id = getSelectedYearForApp();
            $purchaseDirect = PurchaseDirect::select('purchase_directs.id', 'purchase_directs.supplier_number', 'purchase_directs.grn_number', 'purchase_directs.date', 'purchase_directs.total_amount', 'purchase_directs.remark', 'warehouses.name as warehouse_name', 'suppliers.name as supplier_name')
                ->leftJoin('warehouses', 'warehouses.id', 'purchase_directs.warehous_id')
                ->leftJoin('suppliers', 'suppliers.id', 'purchase_directs.supplier_id')
                ->where('purchase_directs.year_id', $year_id)->orderBy('purchase_directs.id', 'DESC')->paginate(12);
            $items = $purchaseDirect->items();
            if ($purchaseDirect->isEmpty()) {
                $response = [
                    'status' => false,
                    'message' => 'No data found',
                    'purchase_direct' => [],
                    'pagination' => [
                        'current_page' => $purchaseDirect->currentPage(),
                        'total_pages' => $purchaseDirect->lastPage(),
                        'per_page' => $purchaseDirect->perPage(),
                        'total_items' => $purchaseDirect->total(),
                    ]
                ];
            } else {
                $response = [
                    'status' => true,
                    'message' => 'Success',
                    'purchase_direct' => $items,
                    'pagination' => [
                        'current_page' => $purchaseDirect->currentPage(),
                        'total_pages' => $purchaseDirect->lastPage(),
                        'per_page' => $purchaseDirect->perPage(),
                        'total_items' => $purchaseDirect->total(),
                    ]
                ];
            }
            return response($response, 200);
        } catch (\Exception $e) {
            Log::error('Error fetching: ' . $e->getMessage());

            return response([
                'status' => false,
                'message' => 'An error occurred. Please try again later.'
            ], 500);
        }
    }

    public function show(Request $request)
    {
        try {
            $year_id = getSelectedYearForApp();
            $purchaseDirect = PurchaseDirect::select('purchase_directs.id', 'purchase_directs.supplier_number', 'purchase_directs.grn_number', 'purchase_directs.date', 'purchase_directs.total_amount', 'purchase_directs.remark', 'purchase_directs.warehous_id', 'warehouses.name as warehouse_name', 'purchase_directs.supplier_id', 'suppliers.name as supplier_name')
                ->with('purchase_direct_meta', 'purchase_direct_meta.item', 'purchase_direct_meta.itemGroup')
                ->leftJoin('warehouses', 'warehouses.id', 'purchase_directs.warehous_id')
                ->leftJoin('suppliers', 'suppliers.id', 'purchase_directs.supplier_id')
                ->where('purchase_directs.year_id', $year_id)
                ->where('purchase_directs.id', $request->id)
                ->first();
            if (!$purchaseDirect) {
                return response([
                    'status' => false,
                    'message' => 'Goods Receipt not found'
                ], 404);
            } else {
                if ($purchaseDirect->purchase_direct_meta->count() > 0) {
                    foreach ($purchaseDirect->purchase_direct_meta as $key => $value) {
                        unset($purchaseDirect->purchase_direct_meta[$key]->purchase_direct_id, $purchaseDirect->purchase_direct_meta[$key]->year_id, $purchaseDirect->purchase_direct_meta[$key]->created_at, $purchaseDirect->purchase_direct_meta[$key]->updated_at, $purchaseDirect->purchase_direct_meta[$key]->deleted_at);
                        $item_name = $item_group_name = "";
                        if ($purchaseDirect->purchase_direct_meta[$key]->type == "ItemGroup") {
                            $item_group_name = getItemGropName($purchaseDirect->purchase_direct_meta[$key]->itemGroup, 0);
                        }
                        if ($purchaseDirect->purchase_direct_meta[$key]->type == "Item" && !is_null($purchaseDirect->purchase_direct_meta[$key]->item)) {
                            $item_name = $purchaseDirect->purchase_direct_meta[$key]->item->name;
                        }
                        $purchaseDirect->purchase_direct_meta[$key]->unit_name = $value->unit->unit_name;
                        $purchaseDirect->purchase_direct_meta[$key]->item_name = $item_name;
                        $purchaseDirect->purchase_direct_meta[$key]->item_group_name = $item_group_name;
                        unset($purchaseDirect->purchase_direct_meta[$key]->item, $purchaseDirect->purchase_direct_meta[$key]->itemGroup,$purchaseDirect->purchase_direct_meta[$key]->unit);
                    }
                }
                return response([
                    'status' => true,
                    'message' => 'Success',
                    'purchase_direct' => $purchaseDirect
                ], 200);
            }
        } catch (\Exception $e) {
            Log::error('Error fetching: ' . $e->getMessage());
            return response([
                'status' => false,
                'message' => 'An error occurred. Please try again later.'
            ], 500);
        }
    }

    public function destroy(Request $request)
    {
        try {
            $query = PurchaseDirect::with('purchase_direct_meta')->where('id', $request->id)->first();
            if (!is_null($query)) {
                $new_year_id = $query->year_id;
                foreach ($query->purchase_direct_meta as $item) {
                    $checkStock = WarehouseStock::where([['warehous_id', $query->warehous_id], ['item_id', $item->item_id]])->first();
                    if ($query->quantity <= $checkStock->quantity) {
                        $checkStock->quantity -= $item->quantity;
                        $checkStock->save();
                        $stockTransactionEdit = new WarehouseStockHistory();
                        $stockTransactionEdit->year_id = $new_year_id;
                        $stockTransactionEdit->warehous_stock_id = $checkStock->id;
                        $stockTransactionEdit->quantity = $item->quantity;
                        $stockTransactionEdit->type = 'Debit';
                        $stockTransactionEdit->remark = 'Delete Goods Receipt';
                        $stockTransactionEdit->save();
                        $item->delete();
                    } else {
                        return response([
                            'status' => false,
                            'message' => 'Insufficient stock',
                        ], 404);
                    }
                }
                $query->delete();
                return response([
                    'status' => true,
                    'message' => 'Deleted successfully',
                ], 200);
            } else {
                return response([
                    'status' => false,
                    'message' => 'Goods Receipt not available'
                ], 404);
            }
        } catch (\Exception $e) {
            Log::error('Error deleting purchase direct: ' . $e->getMessage());
            return response([
                'status' => false,
                'message' => 'An error occurred. Please try again later.'
            ], 500);
        }
    }

    public function deletePurchaseDirectMeta(Request $request)
    {
        try {
            $query = PurchaseDirectMeta::where('id', $request->id)->first();
            if (!is_null($query)) {
                $new_year_id = $query->year_id;
                $purchaseDirect = PurchaseDirect::where('id', $query->purchase_direct_id)->first();

                if ($query->item_id != 0) {
                    $checkStock = WarehouseStock::where([['warehous_id', $purchaseDirect->warehous_id], ['item_id', $query->item_id]])->first();
                } else {
                    $checkStock = WarehouseStock::where([['warehous_id', $purchaseDirect->warehous_id], ['item_group_id', $query->item_group_id]])->first();
                }

                if (!is_null($checkStock) && $query->quantity <= $checkStock->quantity) {
                    $checkStock->quantity -= $query->quantity;
                    $checkStock->save();
                    $stockTransactionEdit = new WarehouseStockHistory();
                    $stockTransactionEdit->year_id = $new_year_id;
                    $stockTransactionEdit->purchase_direct_meta_id = $request->id;
                    $stockTransactionEdit->delivery_challan_meta_id = 0;
                    $stockTransactionEdit->stock_type = 'Delete';

                    $stockTransactionEdit->warehous_stock_id = $checkStock->id;
                    $stockTransactionEdit->quantity = $query->quantity;
                    $stockTransactionEdit->type = 'Debit';
                    $stockTransactionEdit->remark = 'Edit time remove Goods Receipt';
                    $stockTransactionEdit->save();

                    $query->delete();
                    return response([
                        'status' => true,
                        'message' => 'Deleted successfully',
                    ], 200);
                } else {
                    return response([
                        'status' => false,
                        'message' => 'Insufficient stock',
                    ], 404);
                }
            } else {
                return response([
                    'status' => false,
                    'message' => 'Goods Receipt items not available'
                ], 404);
            }
        } catch (\Exception $e) {
            Log::error('Error deleting purchase direct: ' . $e->getMessage());
            return response([
                'status' => false,
                'message' => 'An error occurred. Please try again later.'
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'warehouse_id' => 'required',
            'supplier_id' => 'required',
            'supplier_number' => [
                'required',
                Rule::unique('purchase_directs')->where(function ($query) use ($request) {

                    $query->where('supplier_id', $request->supplier_id)
                        ->where('supplier_number', $request->supplier_number)
                        ->where('deleted_at', null);
                    if (!is_null($request->id)) {
                        $query->where('id', '!=', $request->id);
                    }

                    return $query;
                })
            ],
            'date' => 'required|date'
        ], [
            'warehouse_id.required' => 'Select warehouse',
            'supplier_id.required' => 'Select supplier',
            'supplier_number.required' => 'Enter invoice no.',
            'supplier_number.unique' => 'Goods Receipt has already been added',
            'date.required' => 'Select date'
        ]);

        if ($validator->fails()) {

            $errors = $validator->errors()->toArray();
            $flatErrors = [];
            foreach ($errors as $field => $messages) {
                $flatErrors[$field] = $messages[0];
            }

            return response([
                'status' => false,
                'message' => 'Please input proper data.',
                'errors' => $flatErrors
            ], 422);
        }

        DB::beginTransaction();
        try {
            if (!is_null($request->id)) {
                $qry = PurchaseDirect::where('id', $request->id)->first();
                $new_year_id = $qry->year_id;
                $response = array('status' => true, 'message' => 'Goods Receipt update successfully.');
            } else {
                $new_year_id = getSelectedYearForApp();
                $qry = new PurchaseDirect();
                $qry->grn_number = $this->getNumberOrder();
                $qry->year_id = $new_year_id;
                $response = array('status' => true, 'message' => 'Goods Receipt added successfully.');
            }

            $qry->user_id  = Auth::id();
            $qry->warehous_id  = $request->warehouse_id;
            $qry->supplier_id = $request->supplier_id;
            $qry->supplier_number = $request->supplier_number;
            $qry->date = date('Y-m-d', strtotime($request->date));
            $qry->remark = $request->remark;
            $qry->status = 'Verified';
            $qry->save();

            $toalAmount = 0;
            foreach ($request->items as $key => $value) {
                $unit_id = $product_id = $item_group_id = 0;
                if (!empty($value['item_id']) || !empty($value['item_group_id'])) {
                    if ($value['type'] == "Item") {
                        $unit_id = Product::where('id', $value['item_id'])->first()->unit_id;
                        $product_id = $value['item_id'];
                        $remarks = $value['item_remark'];
                    } else {
                        $unit_id = ItemGroup::where('id', $value['item_group_id'])->first()->unit_id;
                        $item_group_id = $value['item_group_id'];
                        $remarks = $value['item_group_remark'];
                    }
                }
                if (isset($value['purchase_direct_meta_id']) && !is_null($value['purchase_direct_meta_id'])) {
                    $purchaseMeta = PurchaseDirectMeta::where('id', $value['purchase_direct_meta_id'])->first();
                    $remark = 'Edit time Goods Receipt from ' . $qry->supplier->name . ', ' . $qry->grn_number;
                    $this->stockManage($value['type'], $request->warehouse_id, $purchaseMeta->item_id, $purchaseMeta->item_group_id, $purchaseMeta->quantity, $unit_id, $purchaseMeta->id, 0, 'Goods Receipt', $remark, 'Debit');
                } else {
                    $purchaseMeta = new PurchaseDirectMeta();
                    $purchaseMeta->year_id = $new_year_id;
                }

                $quantity = $value['quantity'];
                $price = $value['price'];
                $total = ($price * $quantity);
                $toalAmount += $total;

                $purchaseMeta->purchase_direct_id = $qry->id;
                $purchaseMeta->type = $value['type'];
                $purchaseMeta->item_group_id = $item_group_id;
                $purchaseMeta->item_id = $product_id;
                $purchaseMeta->unit_id = $unit_id;
                $purchaseMeta->quantity = $quantity;
                $purchaseMeta->price = $price;
                $purchaseMeta->total = $total;
                $purchaseMeta->gst_tax = $value['gst'];
                $purchaseMeta->gst_amount = $value['gst_amt'];
                $purchaseMeta->remarks = $remarks;

                $purchaseMeta->save();

                $purchase_direct_meta_id = $purchaseMeta->id;
                $stock_type = 'Goods Receipt';
                $remark = 'Add Goods Receipt from ' . $qry->supplier->name . ', ' . $request->remark;

                $this->stockManage($value['type'], $request->warehouse_id, $product_id, $item_group_id, $quantity, $unit_id, $purchase_direct_meta_id, 0, $stock_type, $remark, 'Credit', $price, $total, $value['gst'], $value['gst_amt']);
            }

            $result = PurchaseDirect::where('id', $qry->id)->update(['total_amount' => $toalAmount]);
            if (!is_null($result)) {
                DB::commit();
                if (!is_null($request->id)) {
                    return response($response, 200);
                }
                return response($response, 201);
            } else {
                DB::rollback();
                return response()->json(array('status_code' => 403, 'message' => 'Goods Receipt added failed'));
            }
        } catch (\Exception $e) {
            Log::error('Error fetching: ' . $e->getMessage());
            return response([
                'status' => false,
                'message' => 'An error occurred. Please try again later.'
            ], 500);
        }
    }

    public function getNumberOrder()
    {
        $last = PurchaseDirect::latest('id')->first();
        $currentYear = date('y') . date('m');
        if (!is_null($last)) {
            $item = $last->grn_number;
            $nwMsg = explode("/", $item);
            $lastYear = $nwMsg[1];
            if ($lastYear == $currentYear) {
                $inMsg = sprintf("%05d", ($nwMsg[2]) + 1);
                return $nwMsg[0] . '/' . $nwMsg[1] . '/' . $inMsg;
            } else {
                return 'GRN/' . $currentYear . '/00001';
            }
        } else {
            return 'GRN/' . $currentYear . '/00001';
        }
    }

    public function stockManage($itemType = "Item", $warehouse_id = 0, $item_id = 0, $item_group_id = 0, $quantity = 0, $unit_id = 0, $purchase_direct_meta_id = 0, $delivery_challan_meta_id = 0, $stock_type = 'Goods Receipt', $remark = '', $oprationType = 'Credit', $price = 0, $total = 0, $gst_tax = 0, $gst_amount = 0)
    {
        if ($itemType == "Item") {
            $checkStock = WarehouseStock::where([['warehous_id', $warehouse_id], ['item_id', $item_id]])->first();
        }
        if ($itemType == "ItemGroup") {
            $checkStock = WarehouseStock::where([['warehous_id', $warehouse_id], ['item_group_id', $item_group_id]])->first();
        }
        if (!is_null($checkStock)) {
            $warehouseStock = $checkStock;
            if ($oprationType == 'Debit') {
                $warehouseStock->quantity = $checkStock->quantity - $quantity;
            } else {
                $warehouseStock->quantity = $checkStock->quantity + $quantity;
            }
        } else {
            $warehouseStock = new WarehouseStock();
            $warehouseStock->quantity = $quantity;
        }
        $warehouseStock->warehous_id = $warehouse_id;
        $warehouseStock->type = $itemType;
        $warehouseStock->item_id = $item_id;
        $warehouseStock->item_group_id = $item_group_id;
        $warehouseStock->unit_id = $unit_id;
        $warehouseStock->save();

        $stockTransaction = new WarehouseStockHistory();
        $stockTransaction->purchase_direct_meta_id = $purchase_direct_meta_id;
        $stockTransaction->delivery_challan_meta_id = $delivery_challan_meta_id;
        $stockTransaction->stock_type = $stock_type;
        $stockTransaction->warehous_stock_id = $warehouseStock->id;
        $stockTransaction->quantity = $quantity;

        $stockTransaction->price = $price;
        $stockTransaction->total = $total;
        $stockTransaction->gst_tax = $gst_tax;
        $stockTransaction->gst_amount = $gst_amount;
        $stockTransaction->year_id = Year::select('id')->where('is_default', '1')->first()->id;

        $stockTransaction->type = $oprationType;
        $stockTransaction->remark = $remark;
        $stockTransaction->save();

        return true;
    }
}
