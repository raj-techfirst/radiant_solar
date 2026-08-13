<?php

namespace App\Http\Controllers\erp;

use App\Exports\SerialNumberDcExport;
use App\Http\Controllers\Controller;
use App\Imports\ItemGroupSerialnumberDC;
use App\Models\CompanyProfile;
use App\Models\erp\BOM;
use App\Models\erp\DeliveryChallanMeta;
use App\Models\erp\DeliveryChallan;
use App\Models\erp\ProjectWiseStock;
use App\Models\erp\ProjectWiseStockHistory;
use App\Models\erp\SerialNumber;
use App\Models\erp\Warehouse;
use App\Models\erp\WarehouseStock;
use App\Models\erp\WarehouseStockHistory;
use App\Models\ItemGroup;
use App\Models\Product;
use App\Models\SalesMaster;
use App\Models\SalesQuatation;
use App\Models\SerialNumberLog;
use App\Models\Year;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class DeliveryChallanController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:delivery-challan-list', ['only' => ['index']]);
        $this->middleware('permission:delivery-challan-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:delivery-challan-edit', ['only' => ['edit']]);
        $this->middleware('permission:delivery-challan-delete', ['only' => ['destroy']]);
    }

    public function index()
    {

        if (request()->ajax()) {
            $year_id = getSelectedYear();
            return DataTables::of(DeliveryChallan::with('warehouse', 'warehouse_from', 'project', 'salesQuatation')->where('year_id', $year_id))
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $html = '';
                    $html .= '<a data-id="' . $row->id . '" href="javascript:void(0);" class="avatar bg-light-success p-50 m-0 view" data-bs-toggle="tooltip" data-placement="left" title="View"><i class="fa fa-eye"></i></a>';
                    if (Gate::check('delivery-challan-edit')) {
                        $html .= ' <a href="' . route('delivery-challan.edit', $row->id) . '" class="avatar bg-light-info p-50 m-0" data-bs-toggle="tooltip" data-placement="left" title="Edit"><i class="fa fa-edit"></i></a>';
                    }
                    if ($row->issue_type == "trading" || $row->issue_type == "warehouse") {
                        $metas = DeliveryChallanMeta::where('delivery_challan_id', $row->id)->where('type', 'ItemGroup')->count();
                        if ($metas > 0) {
                            $html .= ' <a href="' . route('import-serial-number', $row->id) . '" class="avatar bg-light-primary p-50 m-0" data-bs-toggle="tooltip" data-placement="left" title="Upload Serial Number"><i class="fa fa-upload"></i></a>';
                        }
                    }

                    if (Gate::check('delivery-challan-delete')) {
                        $html .= ' <a data-id="' . $row->id . '" href="javascript:void(0);" class="avatar bg-light-danger p-50 m-0 delete" data-bs-toggle="tooltip" data-placement="left" title="Delete"><i class="fa fa-trash"></i></a>';
                    }
                    $html .= '<a href="' . route('delivery-challan-pdf', $row->id) . '" role="button" class="avatar bg-light-warning p-50 m-0" data-bs-toggle="tooltip" data-placement="left" title="PDF"><i class="fa fa-download"></i></a>';

                    return $html;
                })
                ->editColumn('challan_date', function ($row) {
                    return date("d-m-Y", strtotime($row->challan_date));
                })
                ->editColumn('project_id', function ($row) {
                    if ($row->issue_type == "project") {
                        return '(PRO) ' . $row->project->consumer_name ?? '';
                    } else  if ($row->issue_type == "warehouse") {
                        return '(WHS) '. $row->warehouse_from->name ?? '';
                    } else  if ($row->issue_type == "trading") {
                        return '(B2B) '. $row->salesQuatation->name ?? '';
                    } else {
						if($row->installer){
                        return '(INS) ' . $row->installer->name  . ' ' . $row->installer->last_name;
						}
						return '(INS)';
                    }
                })
                ->addColumn('uploaded', function ($row) {
                    if ($row->issue_type == "trading") {
                        $srNos = getDeliveryChallanSerialnoUplodedOrNot($row->id);
                        return ($srNos->count() > 0 && isset($srNos) && $srNos[0]->serial_numbers_count_count == $srNos[0]->quantity) ? 'Uploaded' : 'No';
                    }
                    return '';
                })
                ->addColumn('project_names', function ($row) {
                    $names = '';
                    if ($row->issue_type == "installer") {
                        $salesOrders = getSalesOrderUsingIds($row->sales_master_id);
                        if (count($salesOrders) > 0) {
                            foreach ($salesOrders as $k => $v):
                                $names .= ($k != 0) ? '<br/>' : '';
                                $names .= $v->consumer_name ?? '';
                            endforeach;
                        }
                    }
                    return $names;
                })
                ->addColumn('total', function ($row) {
                    $qry = DeliveryChallanMeta::where('delivery_challan_id', $row->id)
                        ->select('delivery_challan_id')
                        ->selectRaw('SUM(amount) AS total_amount, SUM(gst_amount) AS total_gst_amount')
                        ->groupBy('delivery_challan_id')
                        ->first();
                    return number_format($qry->total_amount + $qry->total_gst_amount, 2);
                })
                ->escapeColumns([])
                ->make(true);
        } else {
            return view('erp.delivery-challan.index');
        }
    }

    public function pdf(Request $request, $id)
    {
        $data = DeliveryChallan::with('warehouse', 'project', 'delivery_challan_meta', 'delivery_challan_meta.serial_numbers_count', 'delivery_challan_meta.serial_numbers_count.serialNumbers')->where('id', $id)->first();
        if (!is_null($data)) {
            $data_array = [
                'title' => 'Delivery Challan',
                'data' => $data,
            ];
            $name = $data->challan_number;
            $pdf = Pdf::loadView('erp.delivery-challan.pdf', $data_array);
            return $pdf->download($name . '.pdf');
        } else {
            return abort(404);
        }
    }

    public function create()
    {
        $project = SalesMaster::where('dispach_pending_list', '1')->get();
        $warehouse = Warehouse::select('id', 'name')->get();
        $installer = CompanyProfile::with('user')->get();
        $quotations = SalesQuatation::where('form_type', 'trading')->where('current_status', 'accepted')->orderBy('id', 'desc')->get();
        $boms = BOM::get();
        return view('erp.delivery-challan.create', compact('warehouse', 'project', 'installer', 'boms', 'quotations'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'warehouse_id' => 'required',
        ], [
            'warehouse_id.required' => 'Select Warehouse',
        ]);

        if ($validator->fails()) {
            $response = ['status_code' => 201, 'message' => 'Please input proper data.', 'errors' => $validator->errors()];
            return response()->json($response);
        }

        DB::beginTransaction();
        try {
            if (!is_null($request->id)) {
                $qry = DeliveryChallan::where('id', $request->id)->first();
                $oldDeliveryChallan = clone $qry;
                $new_year_id = $qry->year_id;
                $response = array('status_code' => 200, 'data' => route('delivery-challan.index'), 'message' => 'Delivery Challan update successfully.');
            } else {
                $oldDeliveryChallan = null;
                $new_year_id = getSelectedYear();
                $qry = new DeliveryChallan();
                $qry->challan_number = $this->getNumberOrder();
                $qry->year_id = $new_year_id;
                $response = array('status_code' => 200, 'data' => route('delivery-challan.index'), 'message' => 'Delivery Challan added successfully.');
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

            if (!is_null($request->id) && $oldDeliveryChallan) {
                $submittedMetaIds = [];
                if (isset($request->invoice)) {
                    foreach ($request->invoice as $rValue) {
                        if (!empty($rValue['delivery_challan_meta_id'])) {
                            $submittedMetaIds[] = $rValue['delivery_challan_meta_id'];
                        }
                    }
                }
                $removedMetas = DeliveryChallanMeta::where('delivery_challan_id', $qry->id)
                    ->whereNotIn('id', $submittedMetaIds)
                    ->get();
                foreach ($removedMetas as $rMeta) {
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
                        $rSrcLog->stock_type = 'Delivery Challan';
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
                                $rDestLog->stock_type = 'Delivery Challan';
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

            $amount = 0;
			if(isset($request->invoice)){
            $processedMetaIds = [];
            foreach ($request->invoice as $key => $value) {
                $quantity = $value['quantity'];
                if ($quantity != 0) {
                    if (!empty($value['delivery_challan_meta_id']) && in_array($value['delivery_challan_meta_id'], $processedMetaIds)) {
                        continue;
                    }
                    $unit_id = $product_id = $item_group_id = 0;
                    $checkStock = null;
                    $outStock = null;
                    $gst = 0;
                    if (!empty($value['item_id']) || !empty($value['item_group_id'])) {
                        if ($value['type'] == "Item") {
                            $unit_id = Product::where('id', $value['item_id'])->first()->unit_id;
                            $product_id = $value['item_id'];
                            if ($request->issue_type == "project") {
                                /* Project Wise  */
                                $outStock = WarehouseStock::where([['warehous_id', $request->warehouse_id], ['item_id', $value['item_id']], ['quantity', '>=', $quantity]])->first();
                                $checkStock = ProjectWiseStock::where([['sales_master_id', $request->project_id], ['item_id', $value['item_id']]])->first();
                                /* / Project Wise  */
                            } else if ($request->issue_type == "warehouse") {
                                /* warehouse Wise  */
                                $outStock = WarehouseStock::where([['warehous_id', $request->warehouse_id], ['item_id', $value['item_id']], ['quantity', '>=', $quantity]])->first();
                                $checkStock = WarehouseStock::where([['warehous_id', $request->warehouse_id_from], ['item_id', $value['item_id']]])->first();
                                /* / warehouse Wise  */
                            } else {
                                /* Installer Wise */
                                $outStock = WarehouseStock::where([['warehous_id', $request->warehouse_id], ['item_id', $value['item_id']], ['quantity', '>=', $quantity]])->first();
                                $checkStock = ProjectWiseStock::where([['installer_id', $request->installer_id], ['item_id', $value['item_id']]])->first();
                                /* / Installer Wise */
                            }
                            if (is_null($outStock)) {
                                DB::rollback();
                                return response()->json(array('status_code' => 403, 'message' => 'Sorry, stock is insufficient.'));
                            }
                            $gst = $outStock->item->gst_rate;
                        } else {
                            $unit_id = ItemGroup::where('id', $value['item_group_id'])->first()->unit_id;
                            $item_group_id = $value['item_group_id'];
                            $outStock = WarehouseStock::where([['warehous_id', $request->warehouse_id], ['item_group_id', $value['item_group_id']], ['quantity', '>=', $quantity]])->first();
                            if (is_null($outStock)) {
                                DB::rollback();
                                return response()->json(array('status_code' => 403, 'message' => 'Sorry, stock is insufficient.'));
                            }
                            $gst = $outStock->itemGroup->gst_rate;
                            if ($request->issue_type == "project") {
                                /* Project Wise  */
                                $checkStock = ProjectWiseStock::where([['sales_master_id', $request->project_id], ['item_group_id', $value['item_group_id']]])->first();
                                /* / Project Wise  */
                            } else if ($request->issue_type == "warehouse") {
                                /* warehouse Wise  */
                                $checkStock = WarehouseStock::where([['warehous_id', $request->warehouse_id_from], ['item_group_id', $value['item_group_id']]])->first();
                                /* / warehouse Wise  */
                            } else if ($request->issue_type == "trading") {
                                /* trading Wise  */

                                /* / trading Wise  */
                            } else {
                                /* Installer Wise */
                                $checkStock = ProjectWiseStock::where([['installer_id', $request->installer_id], ['item_group_id', $value['item_group_id']]])->first();
                                /* / Installer Wise */
                            }
                        }
                    }
                    if (is_null($outStock)) {
                        continue;
                    }
                    $qtyR = 0;
                    $rate = $value['rate'];
                    $amount = $rate * $quantity;
                    $gst_amt = ($amount * $gst) / 100;
                    if (!empty($value['delivery_challan_meta_id'])) {
                        $meta = DeliveryChallanMeta::where('id', $value['delivery_challan_meta_id'])->first();
                        $qtyR = $meta->quantity;
                        if ($request->issue_type != "warehouse") {
                            if (!is_null($checkStock)) {
                                $stockTransactionR = new ProjectWiseStockHistory();
                                $stockTransactionR->delivery_challan_meta_id = $meta->id;
                                $stockTransactionR->project_wise_stock_id = $checkStock->id;
                                $stockTransactionR->quantity = $meta->quantity;
                                $stockTransactionR->type = 'Debit';
                                $stockTransactionR->remark = 'Edit time Delivery Challan from ' . $qry->challan_number . ' - ' . $outStock->warehouse->name . ', ' . $request->remark;
                                $stockTransactionR->save();
                            }
                        } else {
                            if (!is_null($checkStock)) {
                                $reversalStock = new WarehouseStockHistory();
                                $reversalStock->year_id = $new_year_id;
                                $reversalStock->purchase_direct_meta_id = '0';
                                $reversalStock->delivery_challan_meta_id = $meta->id;
                                $reversalStock->stock_type = 'Delivery Challan';
                                $reversalStock->warehous_stock_id = $checkStock->id;
                                $reversalStock->quantity = $meta->quantity;
                                $reversalStock->type = 'Debit';
                                $reversalStock->remark = 'Edit time revers Delivery Challan ' . $qry->challan_number . ' from ' . $outStock->warehouse->name . ', ' . $request->remark;
                                $reversalStock->save();
                            }
                        }

                        $reversalRemark = ($request->issue_type == "warehouse")
                            ? (!is_null($checkStock) && !is_null($checkStock->warehouse) ? $checkStock->warehouse->name : 'Warehouse')
                            : (!is_null($checkStock) && !is_null($checkStock->delivery_challan) && !is_null($checkStock->delivery_challan->project) ? $checkStock->delivery_challan->project->consumer_name : 'Project');
                        $debitStockR = new WarehouseStockHistory();
                        $debitStockR->year_id = $new_year_id;
                        $debitStockR->purchase_direct_meta_id = '0';
                        $debitStockR->delivery_challan_meta_id = $meta->id;
                        $debitStockR->stock_type = 'Delivery Challan';
                        $debitStockR->warehous_stock_id = $outStock->id;
                        $debitStockR->quantity = $meta->quantity;
                        $debitStockR->type = 'Credit';
                        $debitStockR->remark = 'Edit time revers Delivery Challan to ' . $qry->challan_number . ' - ' . $reversalRemark . ', ' . $request->remark;
                        $debitStockR->save();

                        $freshOutStock = WarehouseStock::where('id', $outStock->id)->first();
                        $freshOutStock->quantity = ($freshOutStock->quantity + $meta->quantity) - $value['quantity'];
                        $outStock = $freshOutStock;
                    } else {
                        $meta = new DeliveryChallanMeta();
                        $freshOutStock = WarehouseStock::where('id', $outStock->id)->first();
                        $freshOutStock->quantity -= $value['quantity'];
                        $outStock = $freshOutStock;
                    }
                    $result = $outStock->save();

                    $meta->year_id = $new_year_id;
                    $meta->delivery_challan_id = $qry->id;
                    $meta->quantity = $value['quantity'];
                    $meta->type = $value['type'];
                    $meta->item_id = $product_id;
                    $meta->item_group_id = $item_group_id;
                    $meta->unit_id = $unit_id;
                    $meta->rate = $value['rate'];
                    $meta->gst_amount = $gst_amt;
                    $meta->amount = $amount;
                    $meta->save();
                    $processedMetaIds[] = $meta->id;

                    if ($request->issue_type != "warehouse") {
                        if (isset($checkStock) && !is_null($checkStock)) {
                            $projectStock = $checkStock;
                            $qty = ($checkStock->quantity - $qtyR) + $value['quantity'];
                        } else {
                            $projectStock = new ProjectWiseStock();
                            $qty = $value['quantity'];
                            $projectStock->issue_type = $request->issue_type;
                        }

                        $projectStock->quantity = $qty;
                        $projectStock->delivery_challan_id = $qry->id;
                        if ($request->issue_type == "project") {
                            /* Project Wise  */
                            $projectStock->sales_master_id = $request->project_id;
                            /* / Project Wise  */
                        } else if ($request->issue_type == "trading") {
                            /* trading Wise  */

                            /* / trading Wise  */
                        } else {
                            /* Installer Wise */
                            $projectStock->installer_id = $request->installer_id;
                            /* / Installer Wise */
                        }

                        if ($request->issue_type != "trading") {

                            $projectStock->warehouse_id = $request->warehouse_id;
                            $projectStock->type = $value['type'];
                            $projectStock->item_id = $product_id;
                            $projectStock->item_group_id = $item_group_id;
                            $projectStock->unit_id = $unit_id;
                            $projectStock->save();

                            $stockTransaction = new ProjectWiseStockHistory();
                            $stockTransaction->delivery_challan_meta_id = $meta->id;
                            $stockTransaction->project_wise_stock_id = $projectStock->id;
                            $stockTransaction->quantity = $value['quantity'];
                            $stockTransaction->type = 'Credit';
                            $stockTransaction->remark = 'Delivery Challan from ' . $qry->challan_number . ' - ' . $outStock->warehouse->name . ', ' . $request->remark;
                            $stockTransaction->save();
                        }
                    } else {
                        if (!is_null($checkStock)) {
                            $warehouseStock = $checkStock;
                            $qty = ($checkStock->quantity - $qtyR) + $value['quantity'];
                        } else {
                            $warehouseStock = new WarehouseStock();
                            $qty = $value['quantity'];
                        }
                        $warehouseStock->quantity = $qty;
                        $warehouseStock->warehous_id = $request->warehouse_id_from;
                        $warehouseStock->item_id = $product_id;
                        $warehouseStock->item_group_id = $item_group_id;
                        $warehouseStock->type = $value['type'];
                        $warehouseStock->unit_id = $unit_id;
                        $warehouseStock->save();

                        $debitStock = new WarehouseStockHistory();
                        $debitStock->year_id = $new_year_id;
                        $debitStock->purchase_direct_meta_id = '0';
                        $debitStock->delivery_challan_meta_id = $meta->id;
                        $debitStock->stock_type = 'Delivery Challan';
                        $debitStock->warehous_stock_id = $warehouseStock->id;
                        $debitStock->quantity = $value['quantity'];
                        $debitStock->type = 'Credit';
                        $debitStock->remark = 'Delivery Challan to ' . $qry->challan_number . ' ' . $request->remark;
                        $debitStock->save();
                    }
                    $debitStock = new WarehouseStockHistory();
                    $debitStock->year_id = $new_year_id;
                    $debitStock->purchase_direct_meta_id = '0';
                    $debitStock->delivery_challan_meta_id = $meta->id;
                    $debitStock->stock_type = 'Delivery Challan';
                    $debitStock->warehous_stock_id = $outStock->id;
                    $debitStock->quantity = $value['quantity'];
                    $debitStock->type = 'Debit';
                    $debitStock->remark = 'Delivery Challan to ' . $qry->challan_number . ' , ' . $request->remark;
                    $debitStock->save();
                }
            }

            DB::commit();
            if (!is_null($result)) {
                return response()->json($response);
            } else {
                DB::rollback();
                return response()->json(array('status_code' => 403, 'message' => 'Something went wrong. Please try again.'));
            }
			}
			else 
			{
				            DB::rollback();
            return response()->json(array('status_code' => 500, 'message' => 'Something went wrong. Please try again.'));
			}
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Delivery Challan store/update error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(array('status_code' => 500, 'message' => 'Something went wrong. Please try again.'));
        }
    }

    public function getNumberOrder()
    {
        $last = DeliveryChallan::latest('id')->first();
        $currentYear = date('y') . date('m');
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

    public function show($id)
    {
        $qry = DeliveryChallan::with('delivery_challan_meta')->where('id', $id)->first();
        $data['html'] = view('erp.delivery-challan.model', compact('qry'))->render();
        return response()->json($data);
    }

    public function edit($id)
    {
        $data = DeliveryChallan::where('id', $id)->first();
        $project = SalesMaster::where('dispach_pending_list', '1')->get();
        $warehouse = Warehouse::select('id', 'name')->get();
        $installer = CompanyProfile::with('user')->get();
        $boms = BOM::get();
        $quotations = SalesQuatation::where('form_type', 'trading')->where('current_status', 'accepted')->orderBy('id', 'desc')->get();
        if (!is_null($data->quotations_id) && !$quotations->contains('id', $data->quotations_id)) {
            $currentQuotation = SalesQuatation::where('id', $data->quotations_id)->first();
            if (!is_null($currentQuotation)) {
                $quotations->push($currentQuotation);
            }
        }
        return view('erp.delivery-challan.edit', compact('warehouse', 'project', 'data', 'installer', 'boms', 'quotations'));
    }

    public function update(Request $request, DeliveryChallan $deliveryChallan)
    {
        //
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $query = DeliveryChallan::with('delivery_challan_meta')->where('id', $id)->first();
            if (!is_null($query)) {
                $new_year_id = $query->year_id;
                if (count($query->delivery_challan_meta) > 0) {
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

                        if($item->item_id != 0){
                         $findStock = WarehouseStock::where([['warehous_id', $query->warehouse_id], ['item_id', $item->item_id]])->first();
                        }
                        else
                        {
                            $findStock = WarehouseStock::where([['warehous_id', $query->warehouse_id], ['item_group_id', $item->item_group_id]])->first();
                        }
                        if (!is_null($findStock)) {
                            if ($query->issue_type == "warehouse") {
                                if ($item->item_id != 0) {
                                    $findDestStock = WarehouseStock::where([['warehous_id', $query->warehouse_from_id], ['item_id', $item->item_id]])->first();
                                } else {
                                    $findDestStock = WarehouseStock::where([['warehous_id', $query->warehouse_from_id], ['item_group_id', $item->item_group_id]])->first();
                                }
                                if (is_null($findDestStock)) {
                                    DB::rollback();
                                    return response()->json(['status_code' => 403, 'message' => 'Warehouse Wise Stock Not Found.']);
                                }
                                if ($item->quantity > $findDestStock->quantity) {
                                    DB::rollback();
                                    return response()->json(['status_code' => 403, 'message' => 'Insufficient stock in destination warehouse.']);
                                }
                                $findDestStock->quantity -= $item->quantity;
                                $findDestStock->save();

                                $stockTransactionD = new WarehouseStockHistory();
                                $stockTransactionD->year_id = $new_year_id;
                                $stockTransactionD->purchase_direct_meta_id = 0;
                                $stockTransactionD->delivery_challan_meta_id = $item->id;
                                $stockTransactionD->stock_type = 'Delivery Challan';
                                $stockTransactionD->warehous_stock_id = $findDestStock->id;
                                $stockTransactionD->quantity = $item->quantity;
                                $stockTransactionD->type = 'Debit';
                                $stockTransactionD->remark = 'Delete time remove Delivery Challan than reverse stock';
                                $stockTransactionD->save();

                                $findStock->quantity += $item->quantity;
                                $findStock->save();

                                $stockTransactionW = new WarehouseStockHistory();
                                $stockTransactionW->year_id = $new_year_id;
                                $stockTransactionW->purchase_direct_meta_id = 0;
                                $stockTransactionW->delivery_challan_meta_id = $item->id;
                                $stockTransactionW->stock_type = 'Delivery Challan';
                                $stockTransactionW->warehous_stock_id = $findStock->id;
                                $stockTransactionW->quantity = $item->quantity;
                                $stockTransactionW->type = 'Credit';
                                $stockTransactionW->remark = 'Delete time remove Delivery Challan than reverse stock';
                                $stockTransactionW->save();
                                $item->delete();
                            } else if ($query->issue_type != "trading") {
                                $checkStock = null;
                                if ($query->issue_type == "project") {
                                    $checkStock = ($item->item_id != 0)
                                        ? ProjectWiseStock::where([['sales_master_id', $query->sales_master_id], ['item_id', $item->item_id]])->first()
                                        : ProjectWiseStock::where([['sales_master_id', $query->sales_master_id], ['item_group_id', $item->item_group_id]])->first();
                                } else if ($query->issue_type == "installer") {
                                    $checkStock = ($item->item_id != 0)
                                        ? ProjectWiseStock::where([['installer_id', $query->installer_id], ['item_id', $item->item_id]])->first()
                                        : ProjectWiseStock::where([['installer_id', $query->installer_id], ['item_group_id', $item->item_group_id]])->first();
                                }
                                if (!is_null($checkStock)) {
                                    if ($item->quantity <= $checkStock->quantity) {
                                        $checkStock->quantity -= $item->quantity;
                                        $checkStock->save();

                                        $stockTransactionP = new ProjectWiseStockHistory();
                                        $stockTransactionP->delivery_challan_meta_id = $item->id;
                                        $stockTransactionP->project_wise_stock_id = $checkStock->id;
                                        $stockTransactionP->quantity = $item->quantity;
                                        $stockTransactionP->type = 'Debit';
                                        $stockTransactionP->remark = 'Edit time remove Delivery Challan than reverse stock';
                                        $stockTransactionP->save();

                                        $findStock->quantity += $item->quantity;
                                        $findStock->save();

                                        $stockTransactionW = new WarehouseStockHistory();
                                        $stockTransactionW->year_id = $new_year_id;
                                        $stockTransactionW->purchase_direct_meta_id = 0;
                                        $stockTransactionW->delivery_challan_meta_id = $item->id;
                                        $stockTransactionW->stock_type = 'Delivery Challan';
                                        $stockTransactionW->warehous_stock_id = $findStock->id;
                                        $stockTransactionW->quantity = $item->quantity;
                                        $stockTransactionW->type = 'Credit';
                                        $stockTransactionW->remark = 'Delete time remove Delivery Challan than reverse stock';
                                        $stockTransactionW->save();
                                        $item->delete();
                                    } else {
                                        // DB::rollback();
                                        // return response()->json(['status_code' => 403, 'message' => 'Insufficient stock.']);

                                        $findStock->quantity += $item->quantity;
                                        $findStock->save();

                                        $stockTransactionW = new WarehouseStockHistory();
                                        $stockTransactionW->year_id = $new_year_id;
                                        $stockTransactionW->purchase_direct_meta_id = 0;
                                        $stockTransactionW->delivery_challan_meta_id = $item->id;
                                        $stockTransactionW->stock_type = 'Delivery Challan';
                                        $stockTransactionW->warehous_stock_id = $findStock->id;
                                        $stockTransactionW->quantity = $item->quantity;
                                        $stockTransactionW->type = 'Credit';
                                        $stockTransactionW->remark = 'Delete time remove Delivery Challan than reverse stock';
                                        $stockTransactionW->save();
                                        $item->delete();
                                    }
                                } else {
                                    DB::rollback();
                                    return response()->json(['status_code' => 403, 'message' => 'Project Wise Stock Not Found.']);
                                }
                            } else {

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
                                $stockTransactionW->remark = 'Delete time remove Delivery Challan than reverse stock';
                                $stockTransactionW->save();
                                $item->delete();
                            }
                        } else {
                            DB::rollback();
                            return response()->json(['status_code' => 403, 'message' => 'Warehouse Wise Stock Not Found.']);
                        }
                    }
                    DB::commit();
                    if ($query->issue_type == "trading" && !empty($query->quotations_id)) {
                        $tradingQuatation = SalesQuatation::where('id', $query->quotations_id)->first();
                        if (!is_null($tradingQuatation)) {
                            $tradingQuatation->current_status = 'accepted';
                            $tradingQuatation->save();
                        }
                    }
                    $query->delete();
                    return response()->json(['status_code' => 200, 'message' => 'Deleted successfully.']);
                } else {
                    DB::rollback();
                    return response()->json(['status_code' => 403, 'message' => 'Delivery challan items available.']);
                }
            } else {
                DB::rollback();
                return response()->json(['status_code' => 403, 'message' => 'Delivery challan not available.']);
            }
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['status_code' => 500, 'message' => 'Something went wrong. Please try again.']);
        }
    }

    public function deliveryChallanRemove(Request $request)
    {
        DB::beginTransaction();
        try {
            $query = DeliveryChallanMeta::with('delivery_challan')->where('id', $request->id)->first();
            if (!is_null($query)) {
                $challan = $query->delivery_challan;
                $new_year_id = $query->year_id;

                $findStock = ($query->item_id != 0)
                    ? WarehouseStock::where([['warehous_id', $challan->warehouse_id], ['item_id', $query->item_id]])->first()
                    : WarehouseStock::where([['warehous_id', $challan->warehouse_id], ['item_group_id', $query->item_group_id]])->first();
                if (!is_null($findStock)) {
                    $checkStock = null;
                    if ($challan->issue_type != "trading") {
                        if ($challan->issue_type == "project") {
                            $checkStock = ($query->item_id != 0)
                                ? ProjectWiseStock::where([['sales_master_id', $challan->sales_master_id], ['item_id', $query->item_id]])->first()
                                : ProjectWiseStock::where([['sales_master_id', $challan->sales_master_id], ['item_group_id', $query->item_group_id]])->first();
                        } else if ($challan->issue_type == "installer") {
                            $checkStock = ($query->item_id != 0)
                                ? ProjectWiseStock::where([['installer_id', $challan->installer_id], ['item_id', $query->item_id]])->first()
                                : ProjectWiseStock::where([['installer_id', $challan->installer_id], ['item_group_id', $query->item_group_id]])->first();
                        } else if ($challan->issue_type == "warehouse") {
                            $checkStock = ($query->item_id != 0)
                                ? WarehouseStock::where([['warehous_id', $challan->warehouse_from_id], ['item_id', $query->item_id]])->first()
                                : WarehouseStock::where([['warehous_id', $challan->warehouse_from_id], ['item_group_id', $query->item_group_id]])->first();
                        }

                        if (!is_null($checkStock)) {
                            if ($query->quantity <= $checkStock->quantity) {
                                $checkStock->quantity -= $query->quantity;
                                $checkStock->save();

                                if ($challan->issue_type == "warehouse") {
                                    $stockTransactionP = new WarehouseStockHistory();
                                    $stockTransactionP->year_id = $new_year_id;
                                    $stockTransactionP->purchase_direct_meta_id = 0;
                                    $stockTransactionP->delivery_challan_meta_id = $query->id;
                                    $stockTransactionP->stock_type = 'Delivery Challan';
                                    $stockTransactionP->warehous_stock_id = $checkStock->id;
                                    $stockTransactionP->quantity = $query->quantity;
                                    $stockTransactionP->type = 'Debit';
                                    $stockTransactionP->remark = 'Edit time remove Delivery Challan than reverse stock';
                                    $stockTransactionP->save();
                                } else {
                                    $stockTransactionP = new ProjectWiseStockHistory();
                                    $stockTransactionP->delivery_challan_meta_id = $query->id;
                                    $stockTransactionP->project_wise_stock_id = $checkStock->id;
                                    $stockTransactionP->quantity = $query->quantity;
                                    $stockTransactionP->type = 'Debit';
                                    $stockTransactionP->remark = 'Edit time remove Delivery Challan than reverse stock';
                                    $stockTransactionP->save();
                                }

                                $findStock->quantity += $query->quantity;
                                $findStock->save();

                                $stockTransactionW = new WarehouseStockHistory();
                                $stockTransactionW->year_id = $new_year_id;
                                $stockTransactionW->purchase_direct_meta_id = 0;
                                $stockTransactionW->delivery_challan_meta_id = $query->id;
                                $stockTransactionW->stock_type = 'Delivery Challan';
                                $stockTransactionW->warehous_stock_id = $findStock->id;
                                $stockTransactionW->quantity = $query->quantity;
                                $stockTransactionW->type = 'Credit';
                                $stockTransactionW->remark = 'Edit time remove Delivery Challan than reverse stock';
                                $stockTransactionW->save();

                                $snldata = SerialNumberLog::where('delivery_challan_meta_id', $query->id)->get();
                                if ($snldata->count() > 0) {
                                    foreach ($snldata as $snlValue) {
                                        $checkSerialNumber = SerialNumber::where('id', $snlValue->serial_number_id)->first();
                                        if ($checkSerialNumber) {
                                            $checkSerialNumber->status = "available";
                                            $checkSerialNumber->save();
                                        }
                                    }
                                    SerialNumberLog::where('delivery_challan_meta_id', $query->id)->delete();
                                }

                                DB::commit();
                                $query->delete();
                                return response()->json(['status_code' => 200, 'message' => 'Deleted successfully.']);
                            } else {
                                DB::rollback();
                                return response()->json(['status_code' => 403, 'message' => 'Insufficient destination stock.']);
                            }
                        } else {
                            DB::rollback();
                            return response()->json(['status_code' => 403, 'message' => 'Destination stock not found.']);
                        }
                    } else {
                        // Trading: skip destination, just revert source
                        $findStock->quantity += $query->quantity;
                        $findStock->save();

                        $stockTransactionW = new WarehouseStockHistory();
                        $stockTransactionW->year_id = $new_year_id;
                        $stockTransactionW->purchase_direct_meta_id = 0;
                        $stockTransactionW->delivery_challan_meta_id = $query->id;
                        $stockTransactionW->stock_type = 'Delivery Challan';
                        $stockTransactionW->warehous_stock_id = $findStock->id;
                        $stockTransactionW->quantity = $query->quantity;
                        $stockTransactionW->type = 'Credit';
                        $stockTransactionW->remark = 'Edit time remove Delivery Challan than reverse stock';
                        $stockTransactionW->save();

                        $snldata = SerialNumberLog::where('delivery_challan_meta_id', $query->id)->get();
                        if ($snldata->count() > 0) {
                            foreach ($snldata as $snlValue) {
                                $checkSerialNumber = SerialNumber::where('id', $snlValue->serial_number_id)->first();
                                if ($checkSerialNumber) {
                                    $checkSerialNumber->status = "available";
                                    $checkSerialNumber->save();
                                }
                            }
                            SerialNumberLog::where('delivery_challan_meta_id', $query->id)->delete();
                        }

                        DB::commit();
                        $query->delete();
                        return response()->json(['status_code' => 200, 'message' => 'Deleted successfully.']);
                    }
                } else {
                    DB::rollback();
                    return response()->json(['status_code' => 403, 'message' => 'Warehouse stock not found.']);
                }
            } else {
                DB::rollback();
                return response()->json(['status_code' => 403, 'message' => 'Delivery Challan item not available.']);
            }
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['status_code' => 500, 'message' => 'Something went wrong. Please try again.']);
        }
    }


    public function importSerialNumber($id)
    {
        try {
            $data = DeliveryChallan::where('id', $id)->with('salesQuatation', 'delivery_challan_meta', 'delivery_challan_meta.serial_numbers_count')->first();

            return view('erp.delivery-challan.importSerialNumber', compact('data'));
        } catch (\Exception $e) {
            return response()->json(array('status_code' => 500, 'message' => 'Something went wrong. Please try again.'));
        }
    }

    public function importSerialNumberStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'uploadExcel' => ['required', 'file', 'mimes:xlsx,xls', 'max:1024'],
            'id' => 'required'
        ], [
            'uploadExcel.required' => 'Please upload a file.',
            'uploadExcel.mimes' => 'Only Excel files (xlsx, xls) are allowed.',
            'uploadExcel.max' => 'The file size must be less than 1 MB.',
            'id.required' => 'ID is Required'
        ]);

        if ($validator->fails()) {
            $response = ['status_code' => 201, 'message' => 'Please input proper data.', 'errors' => $validator->errors()];
            return response()->json($response);
        }

        DB::beginTransaction();
        try {
            $metas = DeliveryChallanMeta::with('delivery_challan')->where('id', $request->id)->first();
            if (!is_null($metas)) {
                $file = $request->file('uploadExcel');

                $data = Excel::toArray([], $file);
                $rowCount = count($data[0]);

                if ($rowCount > 1) {
                    $nData = [];
                    $dupData = [];

                    $headers = $data[0][0];
                    $serialNumberColumnIndex = array_search('serial_number', $headers);

                    if ($serialNumberColumnIndex !== false) {
                        foreach ($data[0] as $index => $row) {
                            if ($index == 0) {
                                continue;
                            }
                            if (isset($row[$serialNumberColumnIndex]) && $row[$serialNumberColumnIndex] != null && $row[$serialNumberColumnIndex] != "") {
                                $serialNumber = $row[$serialNumberColumnIndex];
                                $issueType = $metas->delivery_challan->issue_type;
                                $allowedStatuses = $issueType === 'warehouse' ? ['available'] : ['available', 'transfer'];
                                $existingSerialNumber = SerialNumber::whereIn('status', $allowedStatuses)->where('serial_number', $serialNumber)->whereNull('deleted_at')->exists();
                                if ($existingSerialNumber) {
                                    $nData[] = $row;
                                } else {
                                    $dupData[] = $row;
                                }
                            }
                        }
                        if ($metas->quantity == count($nData)) {

                            $snldata =  SerialNumberLog::where('delivery_challan_meta_id', $request->id)->get();
                            if ($snldata->count() > 0) {
                                foreach ($snldata as $snlK => $snlValue):

                                    $checkSerialNumber = SerialNumber::where('id', $snlValue->serial_number_id)->first();
                                    $checkSerialNumber->status = "available";
                                    $checkSerialNumber->save();

                                endforeach;
                                SerialNumberLog::where('delivery_challan_meta_id', $request->id)->delete();
                            }


                            Excel::import(new ItemGroupSerialnumberDC($metas), $file);
                            DB::commit();
                            return response()->json(array('status_code' => 200, 'message' => 'Upload Successfully'));
                        } else {
                            $response = array('status_code' => 201, 'message' => 'There is a mismatch between the quantity(' . number_format($metas->quantity, 0, '', '') . ') and serial number(' . count($nData) . ').');
                            if (count($dupData) > 0) {
                                $response['html'] = view('erp.delivery-challan.importSerialNumberError', compact('nData', 'dupData'))->render();
                            }
                            return response()->json($response);
                        }
                    } else {
                        return response()->json([
                            'status_code' => 400,
                            'message' => 'serial_number column not found in the uploaded file.'
                        ]);
                    }
                } else {
                    return response()->json(array('status_code' => 201, 'message' => 'The uploaded file is empty.'));
                }
            } else {
                DB::rollBack();
                return response()->json(array('status_code' => 201, 'message' => 'Something went wrong.'));
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(array('status_code' => 201, 'message' => 'Something went wrong. Please try again.'));
        }
    }
    public function importSerialNumberShow(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required'
        ], [
            'id.required' => 'ID is Required'
        ]);
        if ($validator->fails()) {
            $response = ['status_code' => 201, 'message' => 'Please input proper data.', 'errors' => $validator->errors()];
            return response()->json($response);
        }
        try {
            $serialNumber = SerialNumberLog::with('serialNumbers')->where('delivery_challan_meta_id', $request->id)->get();
            $response['html'] = view('erp.delivery-challan.importSerialNumberView', compact('serialNumber'))->render();
            return response()->json($response);
        } catch (\Exception $e) {
            return response()->json(array('status_code' => 201, 'message' => 'Something went wrong. Please try again.'));
        }
    }

    public function downloadSerialNumberdc(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required'
        ], [
            'id.required' => 'ID is Required'
        ]);

        if ($validator->fails()) {
            $response = ['status_code' => 201, 'message' => 'Please input proper data.', 'errors' => $validator->errors()];
            return response()->json($response);
        }
        DB::beginTransaction();
        try {
            return Excel::download(new SerialNumberDcExport($request), 'serial-number.xlsx');
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(array('status_code' => 201, 'message' => 'Something went wrong. Please try again.'));
        }
    }
}
