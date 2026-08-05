<?php

namespace App\Http\Controllers\erp;

use App\Http\Controllers\Controller;

use App\Exports\ExportStock;
use App\Exports\ExportStockHistory;
use App\Models\Category;
use App\Models\erp\BOMMeta;
use App\Models\erp\DeliveryChallanMeta;
use App\Models\erp\WarehouseStock;
use App\Models\erp\Warehouse;
use App\Models\erp\WarehouseStockHistory;
use App\Models\ItemGroup;
use App\Models\Product;
use App\Models\SalesMaster;
use App\Models\SalesQuatationMeta;
use App\Models\Year;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class WarehouseStockController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:warehouse-stock-list', ['only' => ['index']]);
        $this->middleware('permission:warehouse-stock-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:warehouse-stock-edit', ['only' => ['edit']]);
        $this->middleware('permission:warehouse-stock-delete', ['only' => ['destroy']]);
    }

    public function index(Request $request)
    {


        $query = WarehouseStock::with('warehouse', 'item', 'itemGroup', 'unit')
            //->where('quantity','>',0)
            ->where(function ($q) use ($request) {
                if (!empty($request->warehouse_id) && $request->warehouse_id != '') {
                    $q->where('warehous_id', $request->warehouse_id);
                }
                if (!empty($request->item_id) && $request->item_id != '') {
                    $q->where('item_id', $request->item_id);
                }
                if (!empty($request->item_group_id) && $request->item_group_id != '') {
                    $q->where('item_group_id', $request->item_group_id);
                }
                if (!empty($request->fdate) && !empty($request->tdate)) {
                    $from_date = date('Y-m-d 00:00:00', strtotime($request->fdate));
                    $to_date = date('Y-m-d 23:59:59', strtotime($request->tdate));
                    $q->whereBetween('updated_at', [$from_date, $to_date]);
                }
                
            });
            if (!empty($request->category_id)) {
                $query->whereHas('item', function ($q) use ($request) {
                    $q->where('category_id', $request->category_id);
                });
            }
            if (!empty($request->type)) {
                $query->whereHas('itemGroup', function ($q) use ($request) {
                    $q->where('group_type', $request->type);
                });
            }
            

        if (request()->ajax()) {
            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('item_name', function ($row) {
                    if ($row->type == "Item") {
                        return $row->item->name ?? '';
                    } else {
                        return getItemGropName($row, 1);
                    }
                })
                ->addColumn('action', function ($row) {
                    $html = '';
                    $html .= '<a data-id="' . $row->id . '" href="javascript:void(0);" class="avatar bg-light-success p-50 m-0 view" data-bs-toggle="tooltip" data-placement="left" title="View"><i class="fa fa-eye"></i></a>';
                    $html .= ' <a href="' . route('export-stock-history', $row->id) . '" class="avatar bg-light-warning p-50 m-0 excel" data-bs-toggle="tooltip" data-placement="left" title="Excel"><i class="fa fa-download"></i></a>';
                    return $html;
                })
                ->editColumn('updated_at', function ($row) {
                    return $row->updated_at->format('d-m-Y h:i:s A');
                })
                ->escapeColumns([])
                ->make(true);
        } else {
            $warehouse = Warehouse::select('id', 'name')->get();
            $item = Product::select('id', 'name')->get();
            $itemGroup = ItemGroup::with('panel_company', 'panel_type', 'panel_watt', 'inveter_company', 'unit')->get();
            $categories = Category::get();
            return view('erp.warehouse-stock.index', compact('warehouse', 'item', 'itemGroup', 'categories'));
        }
    }

    public function exportStock(Request $request)
    {
        $query = WarehouseStock::select('id', 'warehous_id', 'type', 'item_id', 'item_group_id', 'quantity', 'updated_at')
            ->where(function ($q) use ($request) {
                if (!empty($request->warehouse_id)) {
                    $q->where('warehous_id', $request->warehouse_id);
                }
                if (!empty($request->item_no)) {
                    $q->where('item_no', $request->item_no);
                }
                if (!empty($request->fdate) && !empty($request->tdate)) {
                    $from_date = date('Y-m-d 00:00:00', strtotime($request->fdate));
                    $to_date = date('Y-m-d 23:59:59', strtotime($request->tdate));
                    $q->whereBetween('updated_at', [$from_date, $to_date]);
                }
            })
            ->with('warehouse', 'item', 'warehouse_stock_history', 'itemGroup')
            ->get();

        ob_end_clean();
        ob_start();

        if ($query->isNotEmpty()) {
            return Excel::download(new ExportStock($query), 'stock_report.xlsx');
        } else {
            return redirect()->back()->with('error', 'No data found for the specified criteria.');
        }
    }

    public function exportStockHistory($id)
    {
        $query = WarehouseStock::select('id', 'warehous_id', 'type', 'item_id', 'item_group_id', 'quantity')
            ->with('warehouse', 'item', 'warehouse_stock_history', 'itemGroup')
            ->where('id', $id)->first();

        ob_end_clean();
        ob_start();

        if (!is_null($query)) {
            return Excel::download(new ExportStockHistory($query), 'stock_report_history.xlsx');
        } else {
            return redirect()->back()->with('error', 'No data found for the specified criteria.');
        }
    }

    public function create()
    {
        $warehouse = Warehouse::get();
        return view('erp.warehouse-stock.create', compact('warehouse'));
    }

    public function getWarehouseStock(Request $request)
    {
        $warehouseStock = WarehouseStock::with('item.unit', 'itemGroup')->where('warehous_id', $request->id)->get();
        if (count($warehouseStock) > 0) {
            if (!empty($request->type) && $request->type == 'Adjust') {
                $data['html'] = view('erp.warehouse-stock-adjust.render', compact('warehouseStock'))->render();
            } else if (!empty($request->type) && $request->type == 'Challan') {
                $data = [];
                $data['itemGroup'] = [];
                $data['Item'] = [];
                $bomData = [];
                if ($request->bom_id != 0) {
                    $bomData = BOMMeta::with('product', 'unit', 'itemGroup', 'itemGroup.panel_company', 'itemGroup.panel_type', 'itemGroup.panel_watt', 'itemGroup.inveter_company')
                        ->where('boms_id', $request->bom_id)->get();
                }

                if ($request->project_ids != '') {

                    $salesOrder = SalesMaster::select('bom_id')->whereIN('id', $request->project_ids)->where('bom_id', '!=', '0')->where('bom_id', '!=', '')->pluck('bom_id')->toArray();

                    $hasDuplicates = count($salesOrder) > count(array_unique($salesOrder));

                    if (!$hasDuplicates) {
                        $bomData = BOMMeta::select('type', 'item_id', 'item_group_id', DB::raw('SUM(quantity) as quantity'), 'unit_id')->with('product', 'unit', 'itemGroup', 'itemGroup.panel_company', 'itemGroup.panel_type', 'itemGroup.panel_watt', 'itemGroup.inveter_company')
                            ->whereIN('boms_id', $salesOrder)
                            ->groupBy('item_id', 'item_group_id')
                            ->get();
                    } else {
                        $duplicateValues = array_filter(array_count_values($salesOrder), function ($count) {
                            return $count > 1;
                        });
                        $duplicateValues = array_keys($duplicateValues);

                        $bomData1 = BOMMeta::select('type', 'item_id', 'item_group_id', DB::raw('SUM(quantity) as quantity'), 'unit_id')->with('product', 'unit', 'itemGroup', 'itemGroup.panel_company', 'itemGroup.panel_type', 'itemGroup.panel_watt', 'itemGroup.inveter_company')
                            ->whereIN('boms_id', array_unique($salesOrder))
                            ->groupBy('item_id', 'item_group_id')
                            ->get();

                        $bomData2 = BOMMeta::select('type', 'item_id', 'item_group_id', DB::raw('SUM(quantity) as quantity'), 'unit_id')->with('product', 'unit', 'itemGroup', 'itemGroup.panel_company', 'itemGroup.panel_type', 'itemGroup.panel_watt', 'itemGroup.inveter_company')
                            ->whereIN('boms_id', array_unique($duplicateValues))
                            ->groupBy('item_id', 'item_group_id')
                            ->get();

                        $bomData = collect($bomData1)->merge($bomData2)
                            ->groupBy(fn($item) => $item->item_id . '-' . $item->item_group_id)
                            ->map(function ($groupedItems) {
                                $firstItem = $groupedItems->first();
                                return $firstItem->replicate()->fill([
                                    'quantity' => $groupedItems->sum('quantity') // Update only the quantity
                                ]);
                            })->values();
                    }
                }

                if ($request->quotations_id != '') {

                    $bomData = SalesQuatationMeta::selectRaw('type, item_id, item_group_id, nos as quantity')
                        ->with('product', 'product.unit')
                        ->where('sales_quatation_id', $request->quotations_id)
                        ->get();
                }

                $deliveryChallanMeta = DeliveryChallanMeta::where('delivery_challan_id', $request->delivery_challan_id)->get();
                $warehouseStock = WarehouseStock::with('item.unit', 'itemGroup')->where('warehous_id', $request->id)->get();

                if ($warehouseStock->count() > 0) {
                    foreach ($warehouseStock as $pro) {

                        if ($pro->type == "Item" && !is_null($pro->item)) {
                            $item_id = $pro->item->id;
                            $gst_rate = $pro->item->gst_rate;
                            $display_name = $pro->item->name;
                            $unit = $pro->item->unit->unit_name;

                            $data['Item'][] = [
                                'id' => $item_id,
                                'name' => $display_name,
                                'unit' => $unit,
                                'gst_rate' => $gst_rate,
                                'stock' => $pro->quantity ?? 0,
                                'price' => getLastPrice($item_id)
                            ];
                        } else if ($pro->type == "ItemGroup" && !is_null($pro->itemGroup)) {

                            $item_id = $pro->itemGroup->id;
                            $gst_rate = $pro->itemGroup->gst_rate;
                            $unit = $pro->itemGroup->unit->unit_name;

                            $data['itemGroup'][] = [
                                'id' => $item_id,
                                'name' => getItemGropName($pro, 1),
                                'unit' => $unit,
                                'gst_rate' => $gst_rate,
                                'stock' => $pro->quantity ?? 0,
                                'price' => getLastPrice($item_id)
                            ];
                        }
                    }
                }
                $warehouseStock = collect($data['Item']);
                $warehouseStockItemGroup = collect($data['itemGroup']);
                $data['bom'] = $bomData;
                $quotationsId = $request->quotations_id ?? 0;
                $data['html'] = view('erp.delivery-challan.render', compact('warehouseStock', 'deliveryChallanMeta', 'warehouseStockItemGroup', 'bomData', 'quotationsId'))->render();
            } else {
                $data['html'] = view('erp.warehouse-stock.render', compact('warehouseStock'))->render();
            }
            return response()->json($data);
        } else {
            return response()->json(['status_code' => 403, 'message' => 'Warehouse stock not found.']);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'warehouse_out' => 'required',
            'warehouse_in' => 'required',
        ], [
            'warehouse_out.required' => 'Select warehouse out',
            'warehouse_in.required' => 'Select warehouse in',
        ]);

        if ($validator->fails()) {
            $response = ['status_code' => 201, 'message' => 'Please input proper data.', 'errors' => $validator->errors()];
            return response()->json($response);
        }

        DB::beginTransaction();
        try {
            $findStock = WarehouseStock::where('warehous_id', $request->warehouse_out)->first();
            if (!is_null($findStock)) {
                foreach ($request->quantity as $key => $value) {
                    $outStock = WarehouseStock::where([['warehous_id', $request->warehouse_out], ['id', $request->id[$key]], ['item_id', $request->item_id[$key]]])->first();
                    $checkStock = WarehouseStock::where([['warehous_id', $request->warehouse_in], ['item_id', $request->item_id[$key]]])->first();
                    if (!is_null($checkStock)) {
                        $warehouseStock = $checkStock;
                        $qty = $checkStock->quantity + $request->quantity[$key];
                    } else {
                        $warehouseStock = new WarehouseStock();
                        $qty = $request->quantity[$key];
                    }
                    $warehouseStock->quantity = $qty;
                    $warehouseStock->warehous_id = $request->warehouse_in;
                    $warehouseStock->item_id = $request->item_id[$key];
                    $warehouseStock->save();

                    $stockTransaction = new WarehouseStockHistory();
                    $stockTransaction->year_id = Year::select('id')->where('is_default', '1')->first()->id;
                    $stockTransaction->purchase_direct_meta_id = 0;
                    $stockTransaction->delivery_challan_meta_id = 0;
                    $stockTransaction->stock_type = 'Stock Transfer';
                    $stockTransaction->warehous_stock_id = $warehouseStock->id;
                    $stockTransaction->quantity = $request->quantity[$key];
                    $stockTransaction->type = 'Credit';
                    $stockTransaction->remark = 'Transfer from ' . $outStock->warehouse->name . ', ' . $request->remark;
                    $stockTransaction->save();

                    $outStock->quantity -= $request->quantity[$key];
                    $result = $outStock->save();

                    $debitStock = new WarehouseStockHistory();
                    $debitStock->year_id = Year::select('id')->where('is_default', '1')->first()->id;
                    $debitStock->purchase_direct_meta_id = 0;
                    $debitStock->delivery_challan_meta_id = 0;
                    $debitStock->stock_type = 'Stock Transfer';
                    $debitStock->warehous_stock_id = $outStock->id;
                    $debitStock->quantity = $request->quantity[$key];
                    $debitStock->type = 'Debit';
                    $debitStock->remark = 'Transfer to ' . $warehouseStock->warehouse->name . ', ' . $request->remark;
                    $debitStock->save();
                }
            }

            DB::commit();
            if (!is_null($result)) {
                $response = array('status_code' => 200, 'data' => route('warehouse-stock.index'), 'message' => 'Warehouse stock transfer successfully.');
                return response()->json($response);
            } else {
                DB::rollback();
                return response()->json(array('status_code' => 403, 'message' => 'Something went wrong. Please try again.'));
            }
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(array('status_code' => 500, 'message' => 'Something went wrong. Please try again.'));
        }
    }

    public function show($id)
    {
        $warehouseStock = WarehouseStock::with('warehouse_stock_history')->where('id', $id)->first();
        if (!is_null($warehouseStock)) {
            $data['html'] = view('erp.warehouse-stock.model', compact('warehouseStock'))->render();
            return response()->json($data);
        } else {
            return response()->json(['status_code' => 403, 'message' => 'Warehouse stock not found.']);
        }
    }

    public function edit(WarehouseStock $warehouseStock)
    {
        //
    }

    public function update(Request $request, WarehouseStock $warehouseStock)
    {
        //
    }

    public function destroy(WarehouseStock $warehouseStock)
    {
        //
    }
}
