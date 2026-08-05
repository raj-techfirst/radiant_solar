<?php

namespace App\Http\Controllers\Api\erp;

use App\Http\Controllers\Controller;
use App\Models\erp\DeliveryChallanReturn;
use App\Models\erp\DeliveryChallanReturnMeta;
use App\Models\erp\ProjectWiseStock;
use App\Models\erp\ProjectWiseStockHistory;
use App\Models\erp\WarehouseStock;
use App\Models\erp\WarehouseStockHistory;
use App\Models\ItemGroup;
use App\Models\Product;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class DeliveryChallanReturnApiController extends Controller
{
    public function index()
    {
        try {
            $year_id = getSelectedYearForApp();

            $deliveryChallanReturn = DeliveryChallanReturn::select(
                'delivery_challan_returns.id',
                'delivery_challan_returns.issue_type',
                'delivery_challan_returns.challan_number',
                'delivery_challan_returns.challan_date',
                'delivery_challan_returns.remark',
                'warehouses.name as warehouse_name',
                'sales_masters.consumer_name as project_consumer_name',
                'users.name as installer_name',
                'users.last_name as installer_last_name',
                'delivery_challan_returns.sales_master_id'
            )
                ->leftJoin('warehouses', 'warehouses.id', '=', 'delivery_challan_returns.warehouse_id')
                ->leftJoin('sales_masters', 'sales_masters.id', '=', 'delivery_challan_returns.sales_master_id')
                ->leftJoin('users', 'users.id', '=', 'delivery_challan_returns.installer_id')
                ->where('delivery_challan_returns.year_id', $year_id)
                ->orderBy('delivery_challan_returns.id', 'DESC')
                ->paginate(12);

            $deliveryChallanReturn->getCollection()->transform(function ($row) {
                if ($row->issue_type == "project") {
                    $row->display_name = '(PRO) ' . $row->project_consumer_name;
                } else {
                    $row->display_name = '(INS) ' . $row->installer_name . ' ' . $row->installer_last_name;
                }
                unset($row->sales_master_id);
                return $row;
            });

            $items = $deliveryChallanReturn->items();
            if ($deliveryChallanReturn->isEmpty()) {
                $response = [
                    'status' => false,
                    'message' => 'No data found',
                    'delivery_challan_returns' => [],
                    'pagination' => [
                        'current_page' => $deliveryChallanReturn->currentPage(),
                        'total_pages' => $deliveryChallanReturn->lastPage(),
                        'per_page' => $deliveryChallanReturn->perPage(),
                        'total_items' => $deliveryChallanReturn->total(),
                    ]
                ];
            } else {
                $response = [
                    'status' => true,
                    'message' => 'Success',
                    'delivery_challan_returns' => $items,
                    'pagination' => [
                        'current_page' => $deliveryChallanReturn->currentPage(),
                        'total_pages' => $deliveryChallanReturn->lastPage(),
                        'per_page' => $deliveryChallanReturn->perPage(),
                        'total_items' => $deliveryChallanReturn->total(),
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
            $deliveryChallanReturnMeta = DeliveryChallanReturnMeta::where('delivery_challan_return_id', $request->id)->get();
            if ($deliveryChallanReturnMeta->count() > 0) {
                foreach ($deliveryChallanReturnMeta as $key => $value) {
                    unset($deliveryChallanReturnMeta[$key]->purchase_direct_id, $deliveryChallanReturnMeta[$key]->year_id, $deliveryChallanReturnMeta[$key]->created_at, $deliveryChallanReturnMeta[$key]->updated_at, $deliveryChallanReturnMeta[$key]->deleted_at);
                    $item_name = $item_group_name = "";
                    if ($deliveryChallanReturnMeta[$key]->type == "ItemGroup") {
                        $item_group_name = getItemGropName($deliveryChallanReturnMeta[$key]->itemGroup, 0);
						$deliveryChallanReturnMeta[$key]->unit_name =  $deliveryChallanReturnMeta[$key]->itemGroup->unit->unit_name ?? '';
                    }
                    if ($deliveryChallanReturnMeta[$key]->type == "Item" && !is_null($deliveryChallanReturnMeta[$key]->item)) {
                        $item_name = $deliveryChallanReturnMeta[$key]->item->name;
						$deliveryChallanReturnMeta[$key]->unit_name =  $deliveryChallanReturnMeta[$key]->item->unit->unit_name ?? '';
                    }
                    $deliveryChallanReturnMeta[$key]->item_name = $item_name;
                    $deliveryChallanReturnMeta[$key]->item_group_name = $item_group_name;
                    unset($deliveryChallanReturnMeta[$key]->item, $deliveryChallanReturnMeta[$key]->itemGroup);
                }
                return response([
                    'status' => true,
                    'message' => 'Success',
                    'delivery_challan_return_items' => $deliveryChallanReturnMeta
                ], 200);
            } else {
                return response([
                    'status' => false,
                    'message' => 'Delivery Challan Return not available'
                ], 404);
            }
        } catch (\Exception $e) {
            Log::error('Error fetching : ' . $e->getMessage());
            return response([
                'status' => false,
                'message' => 'An error occurred. Please try again later.'
            ], 500);
        }
    }


    public function pdf(Request $request)
    {

        try {
            $data = DeliveryChallanReturn::with('warehouse', 'project', 'delivery_challan_return_meta')->where('id', $request->id)->first();
            if (!is_null($data)) {
                $data_array = [
                    'title' => 'Delivery Challan Return',
                    'data' => $data,
                ];
                $name = str_replace('/', '-', $data->challan_number);


                $directory = 'pdf/erp/';

                if (!File::exists(public_path($directory))) {
                    File::makeDirectory(public_path($directory), 0777, true);
                }

                $filename = $name . '.pdf';

                $pdf = Pdf::loadView('erp.delivery-challan-return.pdf', $data_array);
                $pdf->save(public_path($directory . $filename));

                // Get the URL of the saved PDF
                $url = asset($directory . $filename);

                return response([
                    'status' => true,
                    'message' => 'Success',
                    'url' => $url
                ], 200);
            } else {
                return response([
                    'status' => false,
                    'message' => 'Delivery Challan Return not found'
                ], 404);
            }
        } catch (\Exception $e) {
            Log::error('Error fetching: ' . $e->getMessage());

            return response([
                'status' => false,
                'message' => 'An error occurred. Please try again later.'
            ], 500);
        }
    }


    public function getReturnStock(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required',
            'id' => 'required',
        ], [
            'type.required' => 'Select Type',
            'id.required' => 'Select Id',
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

        try {
            if ($request->type == "installer") {
                $field = 'installer_id';
            } else {
                $field = "sales_master_id";
            }
            $projectWiseStock = ProjectWiseStock::with('item.unit', 'itemGroup')->where('quantity', '>', '0')->where('issue_type', $request->type)->where($field, $request->id)->get();

            if (count($projectWiseStock) > 0) {

                $data = [];
                $data['Item'] = [];
                $data['itemGroup'] = [];
                foreach ($projectWiseStock as $pro) {

                    if ($pro->type == "Item") {
                        $item_id = $pro->item->id;
                        $display_name = $pro->item->name;
                        $unit = $pro->item->unit->unit_name;

                        $data['Item'][] = [
                            'id' => $item_id,
                            'name' => $display_name,
                            'unit' => $unit,
                            'stock' => $pro->quantity ?? 0,
                        ];
                    } else {
                        $item_id = $pro->itemGroup->id;
                        $unit = $pro->itemGroup->unit->unit_name;
                        $data['itemGroup'][] = [
                            'id' => $item_id,
                            'name' => getItemGropName($pro, 1),
                            'unit' => $unit,
                            'stock' => $pro->quantity ?? 0,
                        ];
                    }
                }
                $warehouseStock = collect($data['Item']);
                $warehouseStockItemGroup = collect($data['itemGroup']);

                return response([
                    'status' => true,
                    'message' => 'Success',
                    'items' => $warehouseStock,
                    'itemGroup' => $warehouseStockItemGroup
                ], 200);

                return response()->json($data);
            } else {
                return response([
                    'status' => false,
                    'message' => 'Project wise stock not found'
                ], 403);
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
            $query = DeliveryChallanReturn::with('delivery_challan_return_meta')->where('id', $request->id)->first();
            if (!is_null($query)) {
                $new_year_id = $query->year_id;
                if (count($query->delivery_challan_return_meta) > 0) {
                    foreach ($query->delivery_challan_return_meta as $item) {
                        $findStock = WarehouseStock::where([['warehous_id', $query->warehouse_id], ['item_id', $item->item_id]])->first();
                        if (!is_null($findStock)) {
                            $checkStock = ProjectWiseStock::where([['sales_master_id', $query->sales_master_id], ['item_id', $item->item_id]])->first();
                            if (!is_null($checkStock)) {

                                $checkStock->quantity += $item->quantity;
                                $checkStock->save();

                                $stockTransactionP = new ProjectWiseStockHistory();
                                $stockTransactionP->delivery_challan_return_meta_id = $item->id;
                                $stockTransactionP->project_wise_stock_id = $checkStock->id;
                                $stockTransactionP->quantity = $item->quantity;
                                $stockTransactionP->type = 'Credit';
                                $stockTransactionP->remark = 'Edit time remove Delivery Challan Return than reverse stock';
                                $stockTransactionP->save();

                                $findStock->quantity -= $item->quantity;
                                $findStock->save();

                                $stockTransactionW = new WarehouseStockHistory();
                                $stockTransactionW->year_id = $new_year_id;
                                $stockTransactionW->purchase_direct_meta_id = 0;
                                $stockTransactionW->delivery_challan_return_meta_id = $item->id;
                                $stockTransactionW->stock_type = 'Delivery Challan';
                                $stockTransactionW->warehous_stock_id = $findStock->id;
                                $stockTransactionW->quantity = $item->quantity;
                                $stockTransactionW->type = 'Debit';
                                $stockTransactionW->remark = 'Edit time remove Delivery Challan Return than reverse stock';
                                $stockTransactionW->save();
                                $item->delete();
                            } else {
                                return response([
                                    'status' => false,
                                    'message' => 'Project wise stock not available'
                                ], 404);
                            }
                        } else {
                            return response([
                                'status' => false,
                                'message' => 'Warehouse stock not available'
                            ], 404);
                        }
                    }
                    $query->delete();
                    return response([
                        'status' => true,
                        'message' => 'Deleted successfully'
                    ], 200);
                } else {
                    return response([
                        'status' => false,
                        'message' => 'Delivery challan return item not available'
                    ], 404);
                }
            } else {
                return response([
                    'status' => false,
                    'message' => 'Delivery challan return not available'
                ], 404);
            }
        } catch (\Exception $e) {
            Log::error('Error fetching: ' . $e->getMessage());

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
        ], [
            'warehouse_id.required' => 'Select Warehouse',
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

            $new_year_id = getSelectedYear();
            $qry = new DeliveryChallanReturn();
            $qry->year_id = $new_year_id;
            $qry->challan_number = $this->getNumberOrder();
            $response = array('status' => true, 'message' => 'Goods Issue Return added successfully.');

            $qry->user_id  = Auth::id();
            $qry->warehouse_id  = $request->warehouse_id;
            $qry->issue_type = $request->issue_type;
            $qry->challan_date = date('Y-m-d', strtotime($request->challan_date));
            if ($request->issue_type == "project") {
                $qry->sales_master_id = $request->project_id;
                $qry->installer_id = null;
            } else {
                $qry->sales_master_id = '';
                $qry->installer_id = $request->installer_id;
            }
            $qry->remark = $request->remark;
            $qry->save();
            foreach ($request->invoice as $key => $value) {
                $quantity = $value['quantity'];
                $unit_id = $product_id = $item_group_id = 0;
                if (!empty($value['item_id']) || !empty($value['item_group_id'])) {
                    /* Item Wise */
                    if ($value['type'] == "Item") {
                        $unit_id = Product::where('id', $value['item_id'])->first()->unit_id;
                        $product_id = $value['item_id'];
                        if ($request->issue_type == "project") {
                            /* Project Wise  */
                            $outStock = ProjectWiseStock::where([['sales_master_id', $request->project_id], ['item_id', $value['item_id']], ['quantity', '>=', $quantity]])->first();
                            /* / Project Wise  */
                        } else {
                            /* Installer Wise */
                            $outStock = ProjectWiseStock::where([['installer_id', $request->installer_id], ['item_id', $value['item_id']], ['quantity', '>=', $quantity]])->first();
                            /* / Installer Wise */
                        }
                        if (is_null($outStock)) {
                            DB::rollback();
                            return response([
                                'status' => false,
                                'message' => 'Sorry, stock is insufficient.'
                            ], 404);
                        }
                        $warehouseStock = WarehouseStock::where([['warehous_id', $request->warehouse_id], ['item_id', $value['item_id']]])->first();
                    } else {
                        $unit_id = ItemGroup::where('id', $value['item_group_id'])->first()->unit_id;
                        $item_group_id = $value['item_group_id'];
                        if ($request->issue_type == "project") {
                            /* Project Wise  */
                            $outStock = ProjectWiseStock::where([['sales_master_id', $request->project_id], ['item_group_id', $value['item_group_id']], ['quantity', '>=', $quantity]])->first();
                            /* / Project Wise  */
                        } else {
                            /* Installer Wise */
                            $outStock = ProjectWiseStock::where([['installer_id', $request->installer_id], ['item_group_id', $value['item_group_id']], ['quantity', '>=', $quantity]])->first();
                            /* / Installer Wise */
                        }
                        if (is_null($outStock)) {
                            DB::rollback();
                            return response([
                                'status' => false,
                                'message' => 'Sorry, stock is insufficient.'
                            ], 404);
                        }
                        $warehouseStock = WarehouseStock::where([['warehous_id', $request->warehouse_id], ['item_group_id', $value['item_group_id']]])->first();
                    }
                }

                $meta = new DeliveryChallanReturnMeta();
                $meta->delivery_challan_return_id = $qry->id;
                $meta->quantity = $value['quantity'];
                $meta->type = $value['type'];
                $meta->item_id = $product_id;
                $meta->item_group_id = $item_group_id;
                $meta->unit_id = $unit_id;
                $meta->year_id = $new_year_id;
                $meta->save();

                $outStock->quantity -= $value['quantity'];
                $result = $outStock->save();

                if (!is_null($warehouseStock)) {
                    $warehouseStock->quantity += $value['quantity'];
                } else {
                    $warehouseStock = new WarehouseStock();
                    $warehouseStock->warehous_id = $request->warehouse_id;
                    $warehouseStock->quantity = $value['quantity'];
                    $warehouseStock->type = $value['type'];
                    $warehouseStock->item_id = $product_id;
                    $warehouseStock->item_group_id = $item_group_id;
                    $warehouseStock->unit_id = $unit_id;
                }
                $warehouseStock->save();

                $projectWiseStockHistory = new ProjectWiseStockHistory();
                $projectWiseStockHistory->delivery_challan_return_meta_id = $meta->id;
                $projectWiseStockHistory->project_wise_stock_id = $outStock->id;
                $projectWiseStockHistory->quantity = $value['quantity'];
                $projectWiseStockHistory->type = 'Debit';
                $projectWiseStockHistory->remark = 'Delivery Challan Return from ' . $qry->challan_number . ' - ' . $request->remark;
                $projectWiseStockHistory->save();

                $stockTransaction = new WarehouseStockHistory();
                $stockTransaction->year_id = $new_year_id;
                $stockTransaction->purchase_direct_meta_id = 0;
                $stockTransaction->delivery_challan_meta_id = 0;
                $stockTransaction->delivery_challan_return_meta_id = $meta->id;
                $stockTransaction->stock_type = 'Delivery Challan Return';
                $stockTransaction->warehous_stock_id = $warehouseStock->id;
                $stockTransaction->quantity = $value['quantity'];
                $stockTransaction->type = 'Credit';
                $stockTransaction->remark = 'Delivery Challan Return from ' . $qry->challan_number . ' - ' . $request->remark;
                $stockTransaction->save();
            }

            DB::commit();
            if (!is_null($result)) {
                if (!is_null($request->id)) {
                    return response($response, 200);
                }
                return response($response, 201);
            } else {
                DB::rollback();
                return response([
                    'status' => false,
                    'message' => 'An error occurred. Please try again later.'
                ], 500);
            }
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error fetching: ' . $e->getMessage());
            return response([
                'status' => false,
                'message' => 'An error occurred. Please try again later.'
            ], 500);
        }
    }

    public function getNumberOrder()
    {
        $last = DeliveryChallanReturn::latest('id')->first();
        $currentYear = date('y') . date('m');
        if (!is_null($last)) {
            $item = $last->challan_number;
            $nwMsg = explode("/", $item);
            $lastYear = $nwMsg[1];
            if ($lastYear == $currentYear) {
                $inMsg = sprintf("%05d", ($nwMsg[2]) + 1);
                return $nwMsg[0] . '/' . $nwMsg[1] . '/' . $inMsg;
            } else {
                return 'DCR/' . $currentYear . '/00001';
            }
        } else {
            return 'DCR/' . $currentYear . '/00001';
        }
    }
}
