<?php

namespace App\Http\Controllers\Api\erp;

use App\Http\Controllers\Controller;
use App\Models\CompanyProfile;
use App\Models\erp\BOMMeta;
use App\Models\erp\DeliveryChallan;
use App\Models\erp\DeliveryChallanMeta;
use App\Models\erp\ProjectWiseStock;
use App\Models\erp\ProjectWiseStockHistory;
use App\Models\erp\SerialNumber;
use App\Models\erp\SerialNumberLog;
use App\Models\erp\WarehouseStock;
use App\Models\erp\WarehouseStockHistory;
use App\Models\ItemGroup;
use App\Models\Product;
use App\Models\SalesMaster;
use App\Models\SalesQuatation;
use App\Models\SalesQuatationMeta;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class DeliveryChallanApiController extends Controller
{
    public function index()
    {
        try {
            $year_id = getSelectedYearForApp();

            $deliveryChallan = DeliveryChallan::select(
                'delivery_challans.id',
                'delivery_challans.issue_type',
                'delivery_challans.challan_number',
                'delivery_challans.challan_date',
                'delivery_challans.remark',
                'warehouses.name as warehouse_name',
                'wdn.name as warehouse_from_name',
                'sales_masters.consumer_name as project_consumer_name',
                'sales_quatations.name as sales_quotation_name',
                'users.name as installer_name',
                'users.last_name as installer_last_name',
                'delivery_challans.sales_master_id'
            )
                ->leftJoin('warehouses', 'warehouses.id', '=', 'delivery_challans.warehouse_id')
                ->leftJoin('warehouses as wdn', 'wdn.id', '=', 'delivery_challans.warehouse_from_id')
                ->leftJoin('sales_masters', 'sales_masters.id', '=', 'delivery_challans.sales_master_id')
                ->leftJoin('sales_quatations', 'sales_quatations.id', '=', 'delivery_challans.quotations_id')
                ->leftJoin('users', 'users.id', '=', 'delivery_challans.installer_id')
                ->where('delivery_challans.year_id', $year_id)
                ->orderBy('delivery_challans.id', 'DESC')
                ->paginate(12);

            $deliveryChallan->getCollection()->transform(function ($row) {
                if ($row->issue_type == "project") {
                    $row->display_name = '(PRO) ' . $row->project_consumer_name;
                } elseif ($row->issue_type == "warehouse") {
                    $row->display_name = '(WHS) ' . $row->warehouse_from_name;
                } elseif ($row->issue_type == "trading") {
                    $row->display_name = '(B2B) ' . $row->sales_quotation_name;
                } else {
                    $row->display_name = '(INS) ' . $row->installer_name . ' ' . $row->installer_last_name;
                    $names = '';
                    $salesOrders = getSalesOrderUsingIds($row->sales_master_id);
                    if (count($salesOrders) > 0) {
                        foreach ($salesOrders as $k => $v):
                            $names .= ($k != 0) ? ', ' : '';
                            $names .= $v->consumer_name;
                        endforeach;
                    }
                    $row->project_consumer_name = $names;
                }
                unset($row->sales_master_id);
                return $row;
            });

            $items = $deliveryChallan->items();
            if ($deliveryChallan->isEmpty()) {
                $response = [
                    'status' => false,
                    'message' => 'No data found',
                    'delivery_challans' => [],
                    'pagination' => [
                        'current_page' => $deliveryChallan->currentPage(),
                        'total_pages' => $deliveryChallan->lastPage(),
                        'per_page' => $deliveryChallan->perPage(),
                        'total_items' => $deliveryChallan->total(),
                    ]
                ];
            } else {
                $response = [
                    'status' => true,
                    'message' => 'Success',
                    'delivery_challans' => $items,
                    'pagination' => [
                        'current_page' => $deliveryChallan->currentPage(),
                        'total_pages' => $deliveryChallan->lastPage(),
                        'per_page' => $deliveryChallan->perPage(),
                        'total_items' => $deliveryChallan->total(),
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

    public function pdf(Request $request)
    {

        try {
            $data = DeliveryChallan::with('warehouse', 'project', 'delivery_challan_meta', 'delivery_challan_meta.serial_numbers_count', 'delivery_challan_meta.serial_numbers_count.serialNumbers')->where('id', $request->id)->first();
            if (!is_null($data)) {
                $data_array = [
                    'title' => 'Delivery Challan',
                    'data' => $data,
                ];
                $name = str_replace('/', '-', $data->challan_number);


                $directory = 'pdf/erp/';

                if (!File::exists(public_path($directory))) {
                    File::makeDirectory(public_path($directory), 0777, true);
                }

                $filename = $name . '.pdf';

                $pdf = Pdf::loadView('erp.delivery-challan.pdf', $data_array);
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
                    'message' => 'Delivery Challan not found'
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

    public function destroy(Request $request)
    {
        DB::beginTransaction();
        try {
            $query = DeliveryChallan::with('delivery_challan_meta')->where('id', $request->id)->first();
            if (is_null($query)) {
                DB::rollback();
                return response([
                    'status' => false,
                    'message' => 'Delivery challan not available'
                ], 404);
            }
            if (count($query->delivery_challan_meta) == 0) {
                DB::rollback();
                return response([
                    'status' => false,
                    'message' => 'Delivery challan items not available'
                ], 404);
            }

            $new_year_id = $query->year_id;
            foreach ($query->delivery_challan_meta as $item) {
                $snldata = SerialNumberLog::where('delivery_challan_meta_id', $item->id)->get();
                if ($snldata->count() > 0) {
                    foreach ($snldata as $snlValue) {
                        $checkSerialNumber = SerialNumber::where('id', $snlValue->serial_number_id)->first();
                        if ($checkSerialNumber) {
                            $checkSerialNumber->status = "available";
                            $checkSerialNumber->save();
                        }
                    }
                    SerialNumberLog::where('delivery_challan_meta_id', $item->id)->delete();
                }

                if ($item->item_id != 0) {
                    $findStock = WarehouseStock::where([['warehous_id', $query->warehouse_id], ['item_id', $item->item_id]])->first();
                } else {
                    $findStock = WarehouseStock::where([['warehous_id', $query->warehouse_id], ['item_group_id', $item->item_group_id]])->first();
                }

                if (is_null($findStock)) {
                    $item->delete();
                    continue;
                }

                if ($query->issue_type != "trading") {
                    if ($query->issue_type == "project") {
                        if ($item->item_id != 0) {
                            $checkStock = ProjectWiseStock::where([['sales_master_id', $query->sales_master_id], ['item_id', $item->item_id]])->first();
                        } else {
                            $checkStock = ProjectWiseStock::where([['sales_master_id', $query->sales_master_id], ['item_group_id', $item->item_group_id]])->first();
                        }
                    } else if ($query->issue_type == "installer") {
                        if ($item->item_id != 0) {
                            $checkStock = ProjectWiseStock::where([['installer_id', $query->installer_id], ['item_id', $item->item_id]])->first();
                        } else {
                            $checkStock = ProjectWiseStock::where([['installer_id', $query->installer_id], ['item_group_id', $item->item_group_id]])->first();
                        }
                    } else {
                        if ($item->item_id != 0) {
                            $checkStock = WarehouseStock::where([['warehous_id', $query->warehouse_from_id], ['item_id', $item->item_id]])->first();
                        } else {
                            $checkStock = WarehouseStock::where([['warehous_id', $query->warehouse_from_id], ['item_group_id', $item->item_group_id]])->first();
                        }
                    }

                    if (!is_null($checkStock)) {
                        $checkStock->quantity -= $item->quantity;
                        $checkStock->save();

                        if ($query->issue_type == "warehouse") {
                            $stockTransactionP = new WarehouseStockHistory();
                            $stockTransactionP->year_id = $new_year_id;
                            $stockTransactionP->purchase_direct_meta_id = 0;
                            $stockTransactionP->delivery_challan_meta_id = $item->id;
                            $stockTransactionP->stock_type = 'Delivery Challan Delete';
                            $stockTransactionP->warehous_stock_id = $checkStock->id;
                            $stockTransactionP->quantity = $item->quantity;
                            $stockTransactionP->type = 'Debit';
                            $stockTransactionP->remark = 'Delivery Challan deleted - stock reversed';
                            $stockTransactionP->save();
                        } else {
                            $stockTransactionP = new ProjectWiseStockHistory();
                            $stockTransactionP->delivery_challan_meta_id = $item->id;
                            $stockTransactionP->project_wise_stock_id = $checkStock->id;
                            $stockTransactionP->quantity = $item->quantity;
                            $stockTransactionP->type = 'Debit';
                            $stockTransactionP->remark = 'Delivery Challan deleted - stock reversed';
                            $stockTransactionP->save();
                        }
                    }
                }

                $findStock->quantity += $item->quantity;
                $findStock->save();

                $stockTransactionW = new WarehouseStockHistory();
                $stockTransactionW->year_id = $new_year_id;
                $stockTransactionW->purchase_direct_meta_id = 0;
                $stockTransactionW->delivery_challan_meta_id = $item->id;
                $stockTransactionW->stock_type = 'Delivery Challan Delete';
                $stockTransactionW->warehous_stock_id = $findStock->id;
                $stockTransactionW->quantity = $item->quantity;
                $stockTransactionW->type = 'Credit';
                $stockTransactionW->remark = 'Delivery Challan deleted - stock reversed';
                $stockTransactionW->save();
                $item->delete();
            }

            if ($query->issue_type == "trading" && !empty($query->quotations_id)) {
                $tradingQuatation = SalesQuatation::where('id', $query->quotations_id)->first();
                if (!is_null($tradingQuatation)) {
                    $tradingQuatation->current_status = 'accepted';
                    $tradingQuatation->save();
                }
            }

            $query->delete();
            DB::commit();
            return response([
                'status' => true,
                'message' => 'Deleted successfully',
            ], 200);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error deleting delivery challan: ' . $e->getMessage());
            return response([
                'status' => false,
                'message' => 'An error occurred. Please try again later.'
            ], 500);
        }
    }

    public function show(Request $request)
    {
        try {
            $deliveryChallanMeta = DeliveryChallanMeta::where('delivery_challan_id', $request->id)->get();
            if ($deliveryChallanMeta->count() > 0) {
                foreach ($deliveryChallanMeta as $key => $value) {
					
                    unset($deliveryChallanMeta[$key]->purchase_direct_id, $deliveryChallanMeta[$key]->year_id, $deliveryChallanMeta[$key]->created_at, $deliveryChallanMeta[$key]->updated_at, $deliveryChallanMeta[$key]->deleted_at);
                    $item_name = $item_group_name = "";
                    if ($deliveryChallanMeta[$key]->type == "ItemGroup") {
                        $item_group_name = getItemGropName($deliveryChallanMeta[$key]->itemGroup, 0);
						$deliveryChallanMeta[$key]->unit_name = 'Nos';
                    }
                    if ($deliveryChallanMeta[$key]->type == "Item" && !is_null($deliveryChallanMeta[$key]->item)) {
                        $item_name = $deliveryChallanMeta[$key]->item->name;
						$deliveryChallanMeta[$key]->unit_name = $deliveryChallanMeta[$key]->item->unit->unit_name;
                    }
					
					$deliveryChallanMeta[$key]->rate = (double)$deliveryChallanMeta[$key]->rate;
					$deliveryChallanMeta[$key]->gst_amount = (double)$deliveryChallanMeta[$key]->gst_amount;
					$deliveryChallanMeta[$key]->amount = (double)$deliveryChallanMeta[$key]->amount;
					
                    $deliveryChallanMeta[$key]->item_name = $item_name;
                    $deliveryChallanMeta[$key]->item_group_name = $item_group_name;
                    unset($deliveryChallanMeta[$key]->item, $deliveryChallanMeta[$key]->itemGroup);
                }

                $salesOrdersData = [];
                $deliveryChallan = DeliveryChallan::where('id', $request->id)->first();

                if ($deliveryChallan->issue_type == 'installer') {
                    $salesOrders = getSalesOrderUsingIds($deliveryChallan->sales_master_id);
                    if (count($salesOrders) > 0) {
                        foreach ($salesOrders as $key => $value):
                            $temp = [];
                            $temp['consumer_name'] = $value->consumer_name;
                            $temp['contact_number'] = $value->contact_number;
                            $temp['register_kw'] = $value->register_kw;
                            $temp['address'] = $value->address;
                            $temp['item'][0]['name'] = $value->salesquatationfull->penalWatt->name . ' W Solar Module (' . $value->panel->name . ' - ' . $value->salesquatationfull->penalType->name . ')';
                            $temp['item'][0]['qty'] = (int)$value->salesquatationfull->penal_nos;
                            $temp['item'][1]['name'] = $value->salesquatationfull->inveter_capacity . ' KW Inverter (' . $value->inveter->name . ')';
                            $temp['item'][1]['qty'] = (int)$value->salesquatationfull->no_of_inveter;
                            $salesOrdersData[$key] = $temp;
                        endforeach;
                    }
                }
                return response([
                    'status' => true,
                    'message' => 'Success',
                    'delivery_challan_items' => $deliveryChallanMeta,
                    'salesOrders' => $salesOrdersData
                ], 200);
            } else {
                return response([
                    'status' => false,
                    'message' => 'Delivery Challan not available'
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

    public function getSalesMaster()
    {
        try {
            $salesMasters = SalesMaster::select('id', 'consumer_name', 'consumer_number')->where('dispach_pending_list', '1')->get();
            if ($salesMasters->isEmpty()) {
                $response = [
                    'status' => false,
                    'message' => 'No data found',
                    'salesMasters' => []
                ];
            } else {
                $response = [
                    'status' => true,
                    'message' => 'Success',
                    'salesMasters' => $salesMasters
                ];
            }
            return response($response, 200);
        } catch (\Exception $e) {
            Log::error('Error deleting : ' . $e->getMessage());
            return response([
                'status' => false,
                'message' => 'An error occurred. Please try again later.'
            ], 500);
        }
    }

    public function getWarehouseStock(Request $request)
    {
        try {
            $warehouseStock = WarehouseStock::with('item.unit', 'itemGroup')->where('warehous_id', $request->warehous_id)->get();
            if (count($warehouseStock) > 0) {
                if (!empty($request->type) && $request->type == 'Challan') {
                    $data = [];
                    $data['itemGroup'] = [];
                    $data['Item'] = [];
                    $bomData = [];
                    if (count($request->project_ids) > 0) {
                        $salesOrder = SalesMaster::select(
                            'bom_id',
                            DB::raw('COUNT(bom_id) AS total_boms')
                        )
                            ->whereIn('id', $request->project_ids)
                            ->where('bom_id', '!=', '0')
                            ->where('bom_id', '!=', '')
                            ->groupBy('bom_id')
                            ->pluck('total_boms', 'bom_id')
                            ->toArray();

                        $data['all_boms'] = $salesOrder;

                        $bomData = [];
                        foreach ($salesOrder as $bomId => $bomCount) {
                            $bomDataLocal = BOMMeta::select('type', 'item_id', 'item_group_id', DB::raw('SUM(quantity) * ' . $bomCount . ' as quantity'), 'unit_id')
                           ->with('product', 'unit', 'itemGroup', 'itemGroup.panel_company', 'itemGroup.panel_type', 'itemGroup.panel_watt', 'itemGroup.inveter_company')
                            ->where('boms_id', $bomId)
                                ->orderBy('type', 'DESC')
                                ->orderBy('item_group_id', 'ASC')
                                ->groupBy('item_id', 'item_group_id', 'boms_id')
                                ->get();

                            $bomData = collect($bomData)->merge($bomDataLocal)
                                ->groupBy(fn($item) => $item->item_id . '-' . $item->item_group_id)
                                ->map(function ($groupedItems) {
                                    $firstItem = $groupedItems->first();
                                    return $firstItem->replicate()->fill([
                                        'quantity' => $groupedItems->sum('quantity')
                                    ]);
                                })
                                ->sortBy([
                                    ['item_id', 'asc']
                                ])->values();
                        }
                    }

                    if ($request->quotations_id != '') {
                        $bomData = SalesQuatationMeta::selectRaw('type, item_id, item_group_id, nos as quantity')
                            ->with('product', 'product.unit')
                            ->where('sales_quatation_id', $request->quotations_id)
                            ->get();
                    }

                    if(count($bomData) > 0){
                        foreach ($bomData as $nameKey => $nameValue) {
                            if($nameValue->type == "ItemGroup"){
                                $bomData[$nameKey]->name = getItemGropName($nameValue, 1);
								$bomData[$nameKey]->unit_name = $nameValue->unit->unit_name ?? '';
								$bomData[$nameKey]->quantity = (double)$nameValue->quantity;
                                unset($nameValue->product,$nameValue->unit,$nameValue->itemGroup);
							}
                            else
                            {
								$bomData[$nameKey]->unit_name = $nameValue->unit->unit_name ?? '';
                                $bomData[$nameKey]->name = $nameValue->product->name;
								$bomData[$nameKey]->quantity = (double)$nameValue->quantity;
                                unset($nameValue->product,$nameValue->unit,$nameValue->itemGroup);
                            }
                        }
                    }

                    $warehouseStock = WarehouseStock::with('item.unit', 'itemGroup')->where('warehous_id', $request->warehous_id)->get();

                    if ($warehouseStock->count() > 0) {
                        foreach ($warehouseStock as $pro) {

                            if ($pro->type == "Item" && !is_null($pro->item)) {
                                $item_id = $pro->item->id;
                                $gst_rate = $pro->item->gst_rate ?? 0;
                                $display_name = $pro->item->name ?? '';
                                $unit = $pro->item->unit->unit_name ?? '';

                                $data['Item'][] = [
                                    'id' => $item_id,
                                    'name' => $display_name,
                                    'unit_name' => $unit,
                                    'gst_rate' => $gst_rate,
                                    'stock' => $pro->quantity ?? 0,
                                    'price' => (double) getLastPrice($item_id)
                                ];
                            } else if ($pro->type == "ItemGroup" && !is_null($pro->itemGroup)) {

                                $item_id = $pro->itemGroup->id;
                                $gst_rate = $pro->itemGroup->gst_rate;
                                $unit = $pro->itemGroup->unit->unit_name ?? '';

                                $data['itemGroup'][] = [
                                    'id' => $item_id,
                                    'name' => getItemGropName($pro, 1),
                                    'unit_name' => $unit,
                                    'gst_rate' => $gst_rate,
                                    'stock' => $pro->quantity ?? 0,
                                    'price' => (double) getLastPrice($item_id)
                                ];
                            }
                        }
                    }
                    return response([
                        'status' => true,
                        'message' => 'Success',
                        'item_list' => collect($data['Item']),
                        'itemGroup_list' => collect($data['itemGroup']),
                        'bom' => $bomData,
                        'quotationsId' => $request->quotations_id ?? 0
                    ], 200);
                }
            } else {
                return response([
                    'status' => false,
                    'message' => 'Warehouse stock not found'
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


    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'issue_type' => 'required|in:project,installer,warehouse,trading',
            'warehouse_id' => 'required',
            'installer_id' => 'required_if:issue_type,installer',
            'project_ids' => 'required_if:issue_type,installer',
            'project_id' => 'required_if:issue_type,project',
            'warehouse_id_from' => 'required_if:issue_type,warehouse',
            'quotations_id' => 'required_if:issue_type,trading',
            'invoice' => 'required|array|min:1',
        ], [
            'issue_type.required' => 'Select Issue Type',
            'issue_type.in' => 'Select Issue Type',
            'warehouse_id.required' => 'Select Warehouse',
            'installer_id.required_if' => 'Select Installer',
            'project_ids.required_if' => 'Select at least one Project',
            'project_id.required_if' => 'Select Project',
            'warehouse_id_from.required_if' => 'Select Destination Warehouse',
            'quotations_id.required_if' => 'Select Quotation',
            'invoice.required' => 'Please add at least one item',
            'invoice.array' => 'Please add at least one item',
            'invoice.min' => 'Please add at least one item',
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
            $oldDeliveryChallan = null;
            if (!empty($request->id)) {
                $qry = DeliveryChallan::where('id', $request->id)->first();
                if (is_null($qry)) {
                    DB::rollback();
                    return response([
                        'status' => false,
                        'message' => 'Goods Issue not found'
                    ], 404);
                }
                $oldDeliveryChallan = clone $qry;
                $new_year_id = $qry->year_id;
                $response = array('status' => true, 'message' => 'Goods Issue update successfully.');
            } else {
                $new_year_id = getSelectedYear();
                $qry = new DeliveryChallan();
                $qry->challan_number = $this->getNumberOrder();
                $qry->year_id = $new_year_id;
                $response = array('status' => true, 'message' => 'Goods Issue added successfully.');
            }
            $qry->user_id  = Auth::id();
            $qry->warehouse_id  = $request->warehouse_id;
            $qry->issue_type = $request->issue_type;
            $qry->challan_date = date('Y-m-d', strtotime($request->challan_date));
            if ($request->issue_type == "project") {
                $qry->sales_master_id = $request->project_id;
                $qry->installer_id = null;
            } else if ($request->issue_type == "warehouse") {
                $qry->warehouse_from_id = $request->warehouse_id_from;
            } else if ($request->issue_type == "trading") {
                $qry->quotations_id = $request->quotations_id;
            } else {
                $qry->sales_master_id = (isset($request->project_ids)) ? implode(',', $request->project_ids) : '';
                $qry->installer_id = $request->installer_id;
            }
            $qry->remark = $request->remark;
            $qry->vehicle_no = $request->vehicle_no;
            $qry->save();

            if ($request->issue_type == "trading" && !empty($qry->quotations_id)) {
                $tradingQuatation = SalesQuatation::where('id', $qry->quotations_id)->first();
                if (!is_null($tradingQuatation)) {
                    $tradingQuatation->current_status = 'dispatch';
                    $tradingQuatation->save();
                }
            }

            if (!is_null($oldDeliveryChallan) && $oldDeliveryChallan->issue_type == "trading" && !empty($oldDeliveryChallan->quotations_id) && $oldDeliveryChallan->quotations_id != $qry->quotations_id) {
                $oldTradingQuatation = SalesQuatation::where('id', $oldDeliveryChallan->quotations_id)->first();
                if (!is_null($oldTradingQuatation)) {
                    $oldTradingQuatation->current_status = 'accepted';
                    $oldTradingQuatation->save();
                }
            }

            if (!empty($request->id) && $oldDeliveryChallan) {
                $submittedMetaIds = [];
                foreach ($request->invoice as $rValue) {
                    if (!empty($rValue['delivery_challan_meta_id']) && isset($rValue['quantity']) && $rValue['quantity'] != 0) {
                        $submittedMetaIds[] = $rValue['delivery_challan_meta_id'];
                    }
                }
                $removedMetas = DeliveryChallanMeta::where('delivery_challan_id', $qry->id)
                    ->whereNotIn('id', $submittedMetaIds)
                    ->get();
                foreach ($removedMetas as $rMeta) {
                    $rSnls = SerialNumberLog::where('delivery_challan_meta_id', $rMeta->id)->get();
                    if ($rSnls->count() > 0) {
                        foreach ($rSnls as $rSnl) {
                            $rSn = SerialNumber::where('id', $rSnl->serial_number_id)->first();
                            if ($rSn) {
                                $rSn->status = "available";
                                $rSn->save();
                            }
                        }
                        SerialNumberLog::where('delivery_challan_meta_id', $rMeta->id)->delete();
                    }

                    $rOldWarehouseId = $oldDeliveryChallan->warehouse_id;
                    if ($rMeta->item_group_id == 0) {
                        $rOutStock = WarehouseStock::where([['warehous_id', $rOldWarehouseId], ['item_id', $rMeta->item_id]])->first();
                    } else {
                        $rOutStock = WarehouseStock::where([['warehous_id', $rOldWarehouseId], ['item_group_id', $rMeta->item_group_id]])->first();
                    }
                    if ($rOutStock) {
                        $rOutStock->quantity += $rMeta->quantity;
                        $rOutStock->save();

                        $rSrcLog = new WarehouseStockHistory();
                        $rSrcLog->year_id = $new_year_id;
                        $rSrcLog->purchase_direct_meta_id = '0';
                        $rSrcLog->delivery_challan_meta_id = $rMeta->id;
                        $rSrcLog->stock_type = 'Delivery Challan Edit';
                        $rSrcLog->warehous_stock_id = $rOutStock->id;
                        $rSrcLog->quantity = $rMeta->quantity;
                        $rSrcLog->type = 'Credit';
                        $rSrcLog->remark = 'Edit time remove Delivery Challan line ' . $qry->challan_number;
                        $rSrcLog->save();
                    }

                    if ($oldDeliveryChallan->issue_type != "trading") {
                        $rCheckStock = null;
                        if ($oldDeliveryChallan->issue_type == "project") {
                            if ($rMeta->item_group_id == 0) {
                                $rCheckStock = ProjectWiseStock::where([['sales_master_id', $oldDeliveryChallan->sales_master_id], ['item_id', $rMeta->item_id]])->first();
                            } else {
                                $rCheckStock = ProjectWiseStock::where([['sales_master_id', $oldDeliveryChallan->sales_master_id], ['item_group_id', $rMeta->item_group_id]])->first();
                            }
                        } else if ($oldDeliveryChallan->issue_type == "installer") {
                            if ($rMeta->item_group_id == 0) {
                                $rCheckStock = ProjectWiseStock::where([['installer_id', $oldDeliveryChallan->installer_id], ['item_id', $rMeta->item_id]])->first();
                            } else {
                                $rCheckStock = ProjectWiseStock::where([['installer_id', $oldDeliveryChallan->installer_id], ['item_group_id', $rMeta->item_group_id]])->first();
                            }
                        } else if ($oldDeliveryChallan->issue_type == "warehouse") {
                            if ($rMeta->item_group_id == 0) {
                                $rCheckStock = WarehouseStock::where([['warehous_id', $oldDeliveryChallan->warehouse_from_id], ['item_id', $rMeta->item_id]])->first();
                            } else {
                                $rCheckStock = WarehouseStock::where([['warehous_id', $oldDeliveryChallan->warehouse_from_id], ['item_group_id', $rMeta->item_group_id]])->first();
                            }
                        }

                        if ($rCheckStock) {
                            $rCheckStock->quantity -= $rMeta->quantity;
                            $rCheckStock->save();

                            if ($oldDeliveryChallan->issue_type == "warehouse") {
                                $rDestLog = new WarehouseStockHistory();
                                $rDestLog->year_id = $new_year_id;
                                $rDestLog->purchase_direct_meta_id = '0';
                                $rDestLog->delivery_challan_meta_id = $rMeta->id;
                                $rDestLog->stock_type = 'Delivery Challan Edit';
                                $rDestLog->warehous_stock_id = $rCheckStock->id;
                                $rDestLog->quantity = $rMeta->quantity;
                                $rDestLog->type = 'Debit';
                                $rDestLog->remark = 'Edit time remove Delivery Challan line ' . $qry->challan_number;
                                $rDestLog->save();
                            } else {
                                $rDestLog = new ProjectWiseStockHistory();
                                $rDestLog->delivery_challan_meta_id = $rMeta->id;
                                $rDestLog->project_wise_stock_id = $rCheckStock->id;
                                $rDestLog->quantity = $rMeta->quantity;
                                $rDestLog->type = 'Debit';
                                $rDestLog->remark = 'Edit time remove Delivery Challan line ' . $qry->challan_number;
                                $rDestLog->save();
                            }
                        }
                    }

                    $rMeta->delete();
                }
            }

            $savedAny = false;
            foreach ($request->invoice as $key => $value) {
                $quantity = $value['quantity'];
                if ($quantity == 0) {
                    continue;
                }
                if (empty($value['item_id']) && empty($value['item_group_id'])) {
                    continue;
                }

                $unit_id = $product_id = $item_group_id = 0;
                $product = null;
                $itemGroup = null;
                if ($value['type'] == "Item") {
                    $product = Product::where('id', $value['item_id'])->first();
                    if (is_null($product)) {
                        DB::rollback();
                        return response(['status' => false, 'message' => 'Item not found'], 404);
                    }
                    $unit_id = $product->unit_id;
                    $product_id = $value['item_id'];
                } else {
                    $itemGroup = ItemGroup::where('id', $value['item_group_id'])->first();
                    if (is_null($itemGroup)) {
                        DB::rollback();
                        return response(['status' => false, 'message' => 'Item group not found'], 404);
                    }
                    $unit_id = $itemGroup->unit_id;
                    $item_group_id = $value['item_group_id'];
                }

                // 1. Reverse the old line (edit only)
                if (!empty($value['delivery_challan_meta_id']) && $oldDeliveryChallan) {
                    $meta = DeliveryChallanMeta::where('id', $value['delivery_challan_meta_id'])->first();
                    if ($meta && $meta->delivery_challan_id == $qry->id) {
                        if (($meta->item_id != $product_id) || ($meta->item_group_id != $item_group_id)) {
                            $oldSnls = SerialNumberLog::where('delivery_challan_meta_id', $meta->id)->get();
                            if ($oldSnls->count() > 0) {
                                foreach ($oldSnls as $oldSnl) {
                                    $oldSn = SerialNumber::where('id', $oldSnl->serial_number_id)->first();
                                    if ($oldSn) {
                                        $oldSn->status = "available";
                                        $oldSn->save();
                                    }
                                }
                                SerialNumberLog::where('delivery_challan_meta_id', $meta->id)->delete();
                            }
                        }

                        if ($meta->item_group_id == 0) {
                            $oldOutStock = WarehouseStock::where([['warehous_id', $oldDeliveryChallan->warehouse_id], ['item_id', $meta->item_id]])->first();
                        } else {
                            $oldOutStock = WarehouseStock::where([['warehous_id', $oldDeliveryChallan->warehouse_id], ['item_group_id', $meta->item_group_id]])->first();
                        }
                        if ($oldOutStock) {
                            $oldOutStock->quantity += $meta->quantity;
                            $oldOutStock->save();

                            $oldSourceLog = new WarehouseStockHistory();
                            $oldSourceLog->year_id = $new_year_id;
                            $oldSourceLog->purchase_direct_meta_id = '0';
                            $oldSourceLog->delivery_challan_meta_id = $meta->id;
                            $oldSourceLog->stock_type = 'Delivery Challan Edit';
                            $oldSourceLog->warehous_stock_id = $oldOutStock->id;
                            $oldSourceLog->quantity = $meta->quantity;
                            $oldSourceLog->type = 'Credit';
                            $oldSourceLog->remark = 'Edit time reverse Delivery Challan ' . $qry->challan_number . ' ' . $request->remark;
                            $oldSourceLog->save();
                        }

                        if ($oldDeliveryChallan->issue_type != "trading") {
                            $oldCheckStock = null;
                            if ($oldDeliveryChallan->issue_type == "project") {
                                if ($meta->item_group_id == 0) {
                                    $oldCheckStock = ProjectWiseStock::where([['sales_master_id', $oldDeliveryChallan->sales_master_id], ['item_id', $meta->item_id]])->first();
                                } else {
                                    $oldCheckStock = ProjectWiseStock::where([['sales_master_id', $oldDeliveryChallan->sales_master_id], ['item_group_id', $meta->item_group_id]])->first();
                                }
                            } else if ($oldDeliveryChallan->issue_type == "installer") {
                                if ($meta->item_group_id == 0) {
                                    $oldCheckStock = ProjectWiseStock::where([['installer_id', $oldDeliveryChallan->installer_id], ['item_id', $meta->item_id]])->first();
                                } else {
                                    $oldCheckStock = ProjectWiseStock::where([['installer_id', $oldDeliveryChallan->installer_id], ['item_group_id', $meta->item_group_id]])->first();
                                }
                            } else if ($oldDeliveryChallan->issue_type == "warehouse") {
                                if ($meta->item_group_id == 0) {
                                    $oldCheckStock = WarehouseStock::where([['warehous_id', $oldDeliveryChallan->warehouse_from_id], ['item_id', $meta->item_id]])->first();
                                } else {
                                    $oldCheckStock = WarehouseStock::where([['warehous_id', $oldDeliveryChallan->warehouse_from_id], ['item_group_id', $meta->item_group_id]])->first();
                                }
                            }

                            if ($oldCheckStock) {
                                $oldCheckStock->quantity -= $meta->quantity;
                                $oldCheckStock->save();

                                if ($oldDeliveryChallan->issue_type == "warehouse") {
                                    $oldDestLog = new WarehouseStockHistory();
                                    $oldDestLog->year_id = $new_year_id;
                                    $oldDestLog->purchase_direct_meta_id = '0';
                                    $oldDestLog->delivery_challan_meta_id = $meta->id;
                                    $oldDestLog->stock_type = 'Delivery Challan Edit';
                                    $oldDestLog->warehous_stock_id = $oldCheckStock->id;
                                    $oldDestLog->quantity = $meta->quantity;
                                    $oldDestLog->type = 'Debit';
                                    $oldDestLog->remark = 'Edit time reverse Delivery Challan ' . $qry->challan_number . ' ' . $request->remark;
                                    $oldDestLog->save();
                                } else {
                                    $oldDestLog = new ProjectWiseStockHistory();
                                    $oldDestLog->delivery_challan_meta_id = $meta->id;
                                    $oldDestLog->project_wise_stock_id = $oldCheckStock->id;
                                    $oldDestLog->quantity = $meta->quantity;
                                    $oldDestLog->type = 'Debit';
                                    $oldDestLog->remark = 'Edit time reverse Delivery Challan ' . $qry->challan_number . ' ' . $request->remark;
                                    $oldDestLog->save();
                                }
                            }
                        }
                    } else {
                        $meta = new DeliveryChallanMeta();
                    }
                } else {
                    $meta = new DeliveryChallanMeta();
                }

                // 2. Apply the new line
                if ($value['type'] == "Item") {
                    $outStock = WarehouseStock::where([['warehous_id', $request->warehouse_id], ['item_id', $product_id]])->first();
                    $gst = $product->gst_rate;
                } else {
                    $outStock = WarehouseStock::where([['warehous_id', $request->warehouse_id], ['item_group_id', $item_group_id]])->first();
                    $gst = $itemGroup->gst_rate;
                }

                if (is_null($outStock)) {
                    DB::rollback();
                    return response(['status' => false, 'message' => 'Stock not found in warehouse.'], 404);
                }
                $outStock->refresh();
                if ($outStock->quantity < $quantity) {
                    DB::rollback();
                    return response(['status' => false, 'message' => 'Sorry, stock is insufficient.'], 404);
                }

                $outStock->quantity -= $quantity;
                $outStock->save();

                $rate = isset($value['rate']) ? $value['rate'] : 0;
                $amount = $rate * $quantity;
                $gst_amt = ($amount * $gst) / 100;

                $meta->year_id = $new_year_id;
                $meta->delivery_challan_id = $qry->id;
                $meta->quantity = $quantity;
                $meta->type = $value['type'];
                $meta->item_id = $product_id;
                $meta->item_group_id = $item_group_id;
                $meta->unit_id = $unit_id;
                $meta->rate = $rate;
                $meta->gst_amount = $gst_amt;
                $meta->amount = $amount;
                $meta->save();
                $savedAny = true;

                $sourceLog = new WarehouseStockHistory();
                $sourceLog->year_id = $new_year_id;
                $sourceLog->purchase_direct_meta_id = '0';
                $sourceLog->delivery_challan_meta_id = $meta->id;
                $sourceLog->stock_type = 'Delivery Challan';
                $sourceLog->warehous_stock_id = $outStock->id;
                $sourceLog->quantity = $quantity;
                $sourceLog->type = 'Debit';
                $sourceLog->remark = 'Delivery Challan to ' . $qry->challan_number . ' , ' . $request->remark;
                $sourceLog->save();

                if ($request->issue_type == "warehouse") {
                    if ($value['type'] == "Item") {
                        $warehouseStock = WarehouseStock::where([['warehous_id', $request->warehouse_id_from], ['item_id', $product_id]])->first();
                    } else {
                        $warehouseStock = WarehouseStock::where([['warehous_id', $request->warehouse_id_from], ['item_group_id', $item_group_id]])->first();
                    }
                    if (is_null($warehouseStock)) {
                        $warehouseStock = new WarehouseStock();
                        $warehouseStock->quantity = 0;
                        $warehouseStock->year_id = $new_year_id;
                    }
                    $warehouseStock->quantity += $quantity;
                    $warehouseStock->warehous_id = $request->warehouse_id_from;
                    $warehouseStock->item_id = $product_id;
                    $warehouseStock->item_group_id = $item_group_id;
                    $warehouseStock->type = $value['type'];
                    $warehouseStock->unit_id = $unit_id;
                    $warehouseStock->save();

                    $destLog = new WarehouseStockHistory();
                    $destLog->year_id = $new_year_id;
                    $destLog->purchase_direct_meta_id = '0';
                    $destLog->delivery_challan_meta_id = $meta->id;
                    $destLog->stock_type = 'Delivery Challan';
                    $destLog->warehous_stock_id = $warehouseStock->id;
                    $destLog->quantity = $quantity;
                    $destLog->type = 'Credit';
                    $destLog->remark = 'Delivery Challan to ' . $qry->challan_number . ' ' . $request->remark;
                    $destLog->save();
                } else if ($request->issue_type != "trading") {
                    if ($request->issue_type == "project") {
                        if ($value['type'] == "Item") {
                            $projectStock = ProjectWiseStock::where([['sales_master_id', $request->project_id], ['item_id', $product_id]])->first();
                        } else {
                            $projectStock = ProjectWiseStock::where([['sales_master_id', $request->project_id], ['item_group_id', $item_group_id]])->first();
                        }
                    } else {
                        if ($value['type'] == "Item") {
                            $projectStock = ProjectWiseStock::where([['installer_id', $request->installer_id], ['item_id', $product_id]])->first();
                        } else {
                            $projectStock = ProjectWiseStock::where([['installer_id', $request->installer_id], ['item_group_id', $item_group_id]])->first();
                        }
                    }
                    if (is_null($projectStock)) {
                        $projectStock = new ProjectWiseStock();
                        $projectStock->quantity = 0;
                        $projectStock->issue_type = $request->issue_type;
                    }
                    $projectStock->quantity += $quantity;
                    $projectStock->delivery_challan_id = $qry->id;
                    $projectStock->warehouse_id = $request->warehouse_id;
                    $projectStock->type = $value['type'];
                    $projectStock->item_id = $product_id;
                    $projectStock->item_group_id = $item_group_id;
                    $projectStock->unit_id = $unit_id;
                    if ($request->issue_type == "project") {
                        $projectStock->sales_master_id = $request->project_id;
                    } else {
                        $projectStock->installer_id = $request->installer_id;
                    }
                    $projectStock->save();

                    $destLog = new ProjectWiseStockHistory();
                    $destLog->delivery_challan_meta_id = $meta->id;
                    $destLog->project_wise_stock_id = $projectStock->id;
                    $destLog->quantity = $quantity;
                    $destLog->type = 'Credit';
                    $destLog->remark = 'Delivery Challan from ' . $qry->challan_number . ' - ' . (optional($outStock->warehouse)->name ?? '') . ', ' . $request->remark;
                    $destLog->save();
                }
            }
         
            if ($savedAny) {
                DB::commit();
                if (!empty($request->id)) {
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
            Log::error('Error saving delivery challan: ' . $e->getMessage());
            return response([
                'status' => false,
                'message' => 'An error occurred. Please try again later.'
            ], 500);
        }
    }

    public function getNumberOrder()
    {
        $last = DeliveryChallan::latest('id')->first();
        $currentYear = date('Y');
        if (!is_null($last)) {
            $item = $last->challan_number;
            $nwMsg = explode("/", $item);
            $lastYear = $nwMsg[1];
            if ($lastYear == $currentYear) {
                $inMsg = sprintf("%05d", ($nwMsg[2]) + 1);
                return $nwMsg[0] . '/' . $nwMsg[1] . '/' . $inMsg;
            } else {
                return 'DC/' . $currentYear . '/00001';
            }
        } else {
            return 'DC/' . $currentYear . '/00001';
        }
    }

}
