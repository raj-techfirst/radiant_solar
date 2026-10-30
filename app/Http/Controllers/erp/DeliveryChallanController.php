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
                    //  if ($row->issue_type == "trading") {
                    $metas = DeliveryChallanMeta::where('delivery_challan_id', $row->id)->where('type', 'ItemGroup')->count();
                    if ($metas > 0) {
                        $html .= ' <a href="' . route('import-serial-number', $row->id) . '" class="avatar bg-light-primary p-50 m-0" data-bs-toggle="tooltip" data-placement="left" title="Upload Serial Number"><i class="fa fa-upload"></i></a>';
                    }
                    // }
    
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
                    if ($row->issue_type == "project" && $row->project) {
                        return '(PRO) ' . ($row->project->consumer_name ?? '');
                    } else if ($row->issue_type == "warehouse" && $row->warehouse_from) {
                        return '(WHS) ' . ($row->warehouse_from->name ?? '');
                    } else if ($row->issue_type == "trading" && $row->salesQuatation) {
                        return '(B2B) ' . ($row->salesQuatation->name ?? '');
                    } else {
                        return '(INS) ' . (($row->installer) ? ($row->installer->name . ' ' . $row->installer->last_name) : '');
                    }
                })
                ->addColumn('uploaded', function ($row) {
                    //  if ($row->issue_type == "trading") {
                    $srNos = getDeliveryChallanSerialnoUplodedOrNot($row->id);
                    return ($srNos->count() > 0 && isset($srNos) && $srNos[0]->serial_numbers_count_count == $srNos[0]->quantity) ? 'Uploaded' : 'No';
                    //}
                    //return '';
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
                    if (is_null($qry)) {
                        return number_format(0, 2);
                    }
                    return number_format(($qry->total_amount ?? 0) + ($qry->total_gst_amount ?? 0), 2);
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
            'issue_type' => 'required|in:project,installer,warehouse,trading',
            'warehouse_id' => 'required',
            'installer_id' => 'required_if:issue_type,installer',
            'project_ids' => 'required_if:issue_type,installer',
            'project_id' => 'required_if:issue_type,project',
            'warehouse_id_from' => 'required_if:issue_type,warehouse',
            'quotations_id' => 'required_if:issue_type,trading',
            'invoice' => 'required|array|min:1',
        ], [
            'issue_type.required' => 'Select Issue Type.',
            'issue_type.in' => 'Select Issue Type.',
            'warehouse_id.required' => 'Select Warehouse.',
            'installer_id.required_if' => 'Select Installer.',
            'project_ids.required_if' => 'Select at least one Project.',
            'project_id.required_if' => 'Select Project.',
            'warehouse_id_from.required_if' => 'Select Destination Warehouse.',
            'quotations_id.required_if' => 'Select Quotation.',
            'invoice.required' => 'Please add at least one item.',
            'invoice.min' => 'Please add at least one item.',
        ]);

        if ($validator->fails()) {
            $response = ['status_code' => 201, 'message' => 'Please input proper data.', 'errors' => $validator->errors()];
            return response()->json($response);
        }

        DB::beginTransaction();
        try {
            $oldDeliveryChallan = null;
            if (!is_null($request->id)) {
                $qry = DeliveryChallan::where('id', $request->id)->first();
                $oldDeliveryChallan = clone $qry; // Capture old state
                $new_year_id = $qry->year_id;
                $response = array('status_code' => 200, 'data' => route('delivery-challan.index'), 'message' => 'Delivery Challan update successfully.');
            } else {
                $new_year_id = getSelectedYear();
                $qry = new DeliveryChallan();
                $qry->challan_number = $this->getNumberOrder();
                $qry->year_id = $new_year_id;
                $response = array('status_code' => 200, 'data' => route('delivery-challan.index'), 'message' => 'Delivery Challan added successfully.');
            }
            $qry->user_id = Auth::id();
            $qry->warehouse_id = $request->warehouse_id;
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

            // Edit-time: revert + delete metas that the user removed from the form
            if (!is_null($request->id) && $oldDeliveryChallan) {
                $submittedMetaIds = [];
                if (isset($request->invoice)) {
                    foreach ($request->invoice as $rValue) {
                        if (!empty($rValue['delivery_challan_meta_id']) && isset($rValue['quantity']) && $rValue['quantity'] != 0) {
                            $submittedMetaIds[] = $rValue['delivery_challan_meta_id'];
                        }
                    }
                }
                $removedMetas = DeliveryChallanMeta::where('delivery_challan_id', $qry->id)
                    ->whereNotIn('id', $submittedMetaIds)
                    ->get();
                foreach ($removedMetas as $rMeta) {
                    // Free serial numbers
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

                    // Revert source warehouse stock (use OLD warehouse from cloned DC)
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

                    // Revert destination using OLD issue_type
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
            if (isset($request->invoice)) {
                foreach ($request->invoice as $key => $value) {
                    $quantity = $value['quantity'];
                    if ($quantity != 0) {
                        $unit_id = $product_id = $item_group_id = 0;

                        // Determine New Item details
                        if (!empty($value['item_id']) || !empty($value['item_group_id'])) {
                            if ($value['type'] == "Item") {
                                $unit_id = Product::where('id', $value['item_id'])->first()->unit_id;
                                $product_id = $value['item_id'];
                            } else {
                                $unit_id = ItemGroup::where('id', $value['item_group_id'])->first()->unit_id;
                                $item_group_id = $value['item_group_id'];
                            }
                        }

                        // Handle Stock Reversal if Update
                        if (!empty($value['delivery_challan_meta_id']) && $oldDeliveryChallan) {
                            $meta = DeliveryChallanMeta::where('id', $value['delivery_challan_meta_id'])->first();

                            // Free serial numbers if the item on this line changed
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

                            // 1. Revert Old Source Stock (Warehouse)
                            $oldWarehouseId = $oldDeliveryChallan->warehouse_id;
                            if ($meta->item_group_id == 0) {
                                $oldOutStock = WarehouseStock::where([['warehous_id', $oldWarehouseId], ['item_id', $meta->item_id]])->first();
                            } else {
                                $oldOutStock = WarehouseStock::where([['warehous_id', $oldWarehouseId], ['item_group_id', $meta->item_group_id]])->first();
                            }

                            if ($oldOutStock) {
                                $oldOutStock->quantity += $meta->quantity;
                                $oldOutStock->save();

                                $debitStockR = new WarehouseStockHistory();
                                $debitStockR->year_id = $new_year_id;
                                $debitStockR->purchase_direct_meta_id = '0';
                                $debitStockR->delivery_challan_meta_id = $meta->id;
                                $debitStockR->stock_type = 'Delivery Challan Edit';
                                $debitStockR->warehous_stock_id = $oldOutStock->id;
                                $debitStockR->quantity = $meta->quantity;
                                $debitStockR->type = 'Credit'; // Reversal is Credit
                                $debitStockR->remark = 'Edit time reverse Delivery Challan ' . $qry->challan_number . ' ' . $request->remark;
                                $debitStockR->save();
                            }

                            // 2. Revert Old Destination Stock (Project/Warehouse/Installer)
                            if ($oldDeliveryChallan->issue_type != "warehouse") {
                                // Find Old Check Stock
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
                                } else {
                                    $oldCheckStock = null; // Trading
                                }

                                if ($oldCheckStock) {
                                    $oldCheckStock->quantity -= $meta->quantity;
                                    $oldCheckStock->save();

                                    $stockTransactionR = new ProjectWiseStockHistory();
                                    $stockTransactionR->delivery_challan_meta_id = $meta->id;
                                    $stockTransactionR->project_wise_stock_id = $oldCheckStock->id;
                                    $stockTransactionR->quantity = $meta->quantity;
                                    $stockTransactionR->type = 'Debit'; // Reversal is Debit
                                    $stockTransactionR->remark = 'Edit time reverse Delivery Challan ' . $qry->challan_number . ' ' . $request->remark;
                                    $stockTransactionR->save();
                                }
                            } else {
                                // Warehouse to Warehouse Reversal
                                $oldWarehouseFromId = $oldDeliveryChallan->warehouse_from_id;
                                if ($meta->item_group_id == 0) {
                                    $oldCheckStock = WarehouseStock::where([['warehous_id', $oldWarehouseFromId], ['item_id', $meta->item_id]])->first();
                                } else {
                                    $oldCheckStock = WarehouseStock::where([['warehous_id', $oldWarehouseFromId], ['item_group_id', $meta->item_group_id]])->first();
                                }

                                if ($oldCheckStock) {
                                    $oldCheckStock->quantity -= $meta->quantity;
                                    $oldCheckStock->save();

                                    $stockTransactionR = new WarehouseStockHistory();
                                    $stockTransactionR->year_id = $new_year_id;
                                    $stockTransactionR->purchase_direct_meta_id = '0';
                                    $stockTransactionR->delivery_challan_meta_id = $meta->id;
                                    $stockTransactionR->stock_type = 'Delivery Challan Edit';
                                    $stockTransactionR->warehous_stock_id = $oldCheckStock->id;
                                    $stockTransactionR->quantity = $meta->quantity;
                                    $stockTransactionR->type = 'Debit'; // Reversal of dest credit is Debit
                                    $stockTransactionR->remark = 'Edit time reverse Delivery Challan ' . $qry->challan_number . ' ' . $request->remark;
                                    $stockTransactionR->save();
                                }
                            }
                        } else {
                            $meta = new DeliveryChallanMeta();
                        }

                        // --- NEW TRANSACTION APPLICATION ---

                        // Refetch product/item group stock logic
                        if ($value['type'] == "Item") {
                            $gst = Product::where('id', $value['item_id'])->first()->gst_rate;

                            $outStock = WarehouseStock::where([['warehous_id', $request->warehouse_id], ['item_id', $value['item_id']]])->first();

                            if ($request->issue_type == "project") {
                                $checkStock = ProjectWiseStock::where([['sales_master_id', $request->project_id], ['item_id', $value['item_id']]])->first();
                            } else if ($request->issue_type == "warehouse") {
                                $checkStock = WarehouseStock::where([['warehous_id', $request->warehouse_id_from], ['item_id', $value['item_id']]])->first();
                            } else {
                                $checkStock = ProjectWiseStock::where([['installer_id', $request->installer_id], ['item_id', $value['item_id']]])->first();
                            }

                        } else {
                            $gst = ItemGroup::where('id', $value['item_group_id'])->first()->gst_rate;

                            $outStock = WarehouseStock::where([['warehous_id', $request->warehouse_id], ['item_group_id', $value['item_group_id']]])->first();

                            if ($request->issue_type == "project") {
                                $checkStock = ProjectWiseStock::where([['sales_master_id', $request->project_id], ['item_group_id', $value['item_group_id']]])->first();
                            } else if ($request->issue_type == "warehouse") {
                                $checkStock = WarehouseStock::where([['warehous_id', $request->warehouse_id_from], ['item_group_id', $value['item_group_id']]])->first();
                            } else {
                                $checkStock = ProjectWiseStock::where([['installer_id', $request->installer_id], ['item_group_id', $value['item_group_id']]])->first();
                            }
                        }

                        if (is_null($outStock) || $outStock->quantity < $quantity) {
                            if (!$outStock) {
                                // Create if checks dont exist? Usually outstock must exist.
                                DB::rollback();
                                return response()->json(array('status_code' => 403, 'message' => 'Stock not found in warehouse.'));
                            }
                            // Note: If update, we already added back quantity, so check should be fine if net change is valid.
                            // But we saved the reversal already. So outStock has refreshed quantity (if IDs matched).
                            // If IDs matched, outStock variable is STALE because we updated via oldOutStock.
                            $outStock->refresh();
                            if ($outStock->quantity < $quantity) {
                                DB::rollback();
                                return response()->json(array('status_code' => 403, 'message' => 'Sorry, stock is insufficient.'));
                            }
                        }

                        // Apply Debit to Source
                        $outStock->quantity -= $quantity;
                        $outStock->save();

                        // Save Meta
                        $rate = $value['rate'];
                        $amount = $rate * $quantity;
                        $gst_amt = ($amount * $gst) / 100;

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
                        $savedAny = true;

                        // Log Debit Source
                        $debitStock = new WarehouseStockHistory();
                        $debitStock->year_id = $new_year_id;
                        $debitStock->purchase_direct_meta_id = '0';
                        $debitStock->delivery_challan_meta_id = $meta->id;
                        $debitStock->stock_type = 'Delivery Challan';
                        $debitStock->warehous_stock_id = $outStock->id;
                        $debitStock->quantity = $quantity;
                        $debitStock->type = 'Debit';
                        $debitStock->remark = 'Delivery Challan to ' . $qry->challan_number . ' , ' . $request->remark;
                        $debitStock->save();

                        // Apply Credit to Destination
                        if ($request->issue_type != "warehouse") {
                            if (!$checkStock) {
                                $checkStock = new ProjectWiseStock();
                                $checkStock->quantity = 0;
                                $checkStock->issue_type = $request->issue_type;
                            }
                            // Refresh just in case
                            if ($checkStock->exists)
                                $checkStock->refresh();

                            $checkStock->quantity += $quantity;
                            $checkStock->delivery_challan_id = $qry->id;

                            // Re-set fields in case logic needs it
                            if ($request->issue_type == "project") {
                                $checkStock->sales_master_id = $request->project_id;
                            } else if ($request->issue_type == "installer") {
                                $checkStock->installer_id = $request->installer_id;
                            }
                            if ($request->issue_type != "trading") {
                                $checkStock->warehouse_id = $request->warehouse_id;
                                $checkStock->type = $value['type'];
                                $checkStock->item_id = $product_id;
                                $checkStock->item_group_id = $item_group_id;
                                $checkStock->unit_id = $unit_id;
                                $checkStock->save();

                                $stockTransaction = new ProjectWiseStockHistory();
                                $stockTransaction->delivery_challan_meta_id = $meta->id;
                                $stockTransaction->project_wise_stock_id = $checkStock->id;
                                $stockTransaction->quantity = $quantity;
                                $stockTransaction->type = 'Credit';
                                $stockTransaction->remark = 'Delivery Challan from ' . $qry->challan_number . ' - ' . $outStock->warehouse->name . ', ' . $request->remark;
                                $stockTransaction->save();
                            }
                        } else {
                            if (!$checkStock) {
                                $checkStock = new WarehouseStock();
                                $checkStock->quantity = 0;
                                $checkStock->year_id = $new_year_id;
                            }
                            if ($checkStock->exists)
                                $checkStock->refresh();

                            $checkStock->quantity += $quantity;
                            $checkStock->warehous_id = $request->warehouse_id_from;
                            $checkStock->item_id = $product_id;
                            $checkStock->item_group_id = $item_group_id;
                            $checkStock->type = $value['type'];
                            $checkStock->unit_id = $unit_id;
                            $checkStock->save();

                            $creditDest = new WarehouseStockHistory();
                            $creditDest->year_id = $new_year_id;
                            $creditDest->purchase_direct_meta_id = '0';
                            $creditDest->delivery_challan_meta_id = $meta->id;
                            $creditDest->stock_type = 'Delivery Challan';
                            $creditDest->warehous_stock_id = $checkStock->id;
                            $creditDest->quantity = $quantity;
                            $creditDest->type = 'Credit';
                            $creditDest->remark = 'Delivery Challan to ' . $qry->challan_number . ' ' . $request->remark;
                            $creditDest->save();
                        }
                    }
                }

                if ($savedAny) {
                    DB::commit();
                    return response()->json($response);
                }
                DB::rollback();
                return response()->json(array('status_code' => 500, 'message' => 'Something went wrong. Please try again.'));
            } else {
                DB::rollback();
                return response()->json(array('status_code' => 500, 'message' => 'Something went wrong. Please try again.'));
            }
        } catch (\Exception $e) {
            Log::error('Delivery Challan store/update error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            DB::rollback();
            return response()->json(array('status_code' => 500, 'message' => 'Something went wrong. Please try again.'));
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

    public function show($id)
    {
        $qry = DeliveryChallan::with(
            'warehouse',
            'warehouse_from',
            'project',
            'installer',
            'salesQuatation',
            'delivery_challan_meta',
            'delivery_challan_meta.item.unit',
            'delivery_challan_meta.itemGroup.unit'
        )->where('id', $id)->first();
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

    public function update(Request $request, $id)
    {
        $request->request->add(['id' => $id]);
        return $this->store($request);
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

                        if ($item->item_id != 0) {
                            $findStock = WarehouseStock::where([['warehous_id', $query->warehouse_id], ['item_id', $item->item_id]])->first();
                        } else {
                            $findStock = WarehouseStock::where([['warehous_id', $query->warehouse_id], ['item_group_id', $item->item_group_id]])->first();
                        }
                        if (!is_null($findStock)) {
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
                                    // Warehouse Transfer? Usually creates ProjectWiseStock? No, WarehouseStock.
                                    // If issue_type is warehouse, we need to revert dest warehouse stock?
                                    // Current code structure assumes ProjectWiseStock for non-trading.
                                    // Let's stick to existing pattern but fix the ID lookup.
                                    // Actually, for warehouse transfer, destination is another warehouse.
                                    if ($item->item_id != 0) {
                                        $checkStock = WarehouseStock::where([['warehous_id', $query->warehouse_from_id], ['item_id', $item->item_id]])->first();
                                    } else {
                                        $checkStock = WarehouseStock::where([['warehous_id', $query->warehouse_from_id], ['item_group_id', $item->item_group_id]])->first();
                                    }
                                }

                                if (!is_null($checkStock)) {
                                    if ($item->quantity <= $checkStock->quantity) {
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
                                    } else {
                                        // For now, force delete even if insufficient? Or fail?
                                        // The original code had an else block that did the same thing but without rolling back.
                                        $checkStock->quantity -= $item->quantity; // Allow negative?
                                        $checkStock->save();

                                        // LOGGING as above...
                                        // Use same logic as successful case
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
                                } else {
                                    // DB::rollback();
                                    // return response()->json(['status_code' => 403, 'message' => 'Project Wise Stock Not Found.']);
                                    // If stock not found, just revert source?
                                    $findStock->quantity += $item->quantity;
                                    $findStock->save();
                                    $item->delete();
                                }
                            } else {
                                // Trading
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
                        } else {
                            // Source stock not found
                            $item->delete();
                        }
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
        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            $query = DeliveryChallanMeta::with('delivery_challan')->where('id', $request->id)->first();
            if (!is_null($query)) {
                $challan = $query->delivery_challan;
                $new_year_id = $query->year_id;

                // 1. Revert Source Stock (Warehouse)
                if ($query->item_id != 0) {
                    $findStock = WarehouseStock::where([['warehous_id', $challan->warehouse_id], ['item_id', $query->item_id]])->first();
                } else {
                    $findStock = WarehouseStock::where([['warehous_id', $challan->warehouse_id], ['item_group_id', $query->item_group_id]])->first();
                }

                if (!is_null($findStock)) {
                    // Start by assuming success for finding dest stock, OR if trading we skip dest stock.
                    $checkStock = null;
                    if ($challan->issue_type != "trading") {
                        if ($challan->issue_type == "project") {
                            if ($query->item_id != 0) {
                                $checkStock = ProjectWiseStock::where([['sales_master_id', $challan->sales_master_id], ['item_id', $query->item_id]])->first();
                            } else {
                                $checkStock = ProjectWiseStock::where([['sales_master_id', $challan->sales_master_id], ['item_group_id', $query->item_group_id]])->first();
                            }
                        } else if ($challan->issue_type == "installer") {
                            if ($query->item_id != 0) {
                                $checkStock = ProjectWiseStock::where([['installer_id', $challan->installer_id], ['item_id', $query->item_id]])->first();
                            } else {
                                $checkStock = ProjectWiseStock::where([['installer_id', $challan->installer_id], ['item_group_id', $query->item_group_id]])->first();
                            }
                        } else if ($challan->issue_type == "warehouse") {
                            if ($query->item_id != 0) {
                                $checkStock = WarehouseStock::where([['warehous_id', $challan->warehouse_from_id], ['item_id', $query->item_id]])->first();
                            } else {
                                $checkStock = WarehouseStock::where([['warehous_id', $challan->warehouse_from_id], ['item_group_id', $query->item_group_id]])->first();
                            }
                        }

                        if (!is_null($checkStock)) {
                            // Revert Dest
                            $checkStock->quantity -= $query->quantity;
                            $checkStock->save();

                            // Log Dest
                            if ($challan->issue_type == "warehouse") {
                                $stockTransactionP = new WarehouseStockHistory();
                                $stockTransactionP->year_id = $new_year_id;
                                $stockTransactionP->purchase_direct_meta_id = 0;
                                $stockTransactionP->delivery_challan_meta_id = $query->id;
                                $stockTransactionP->stock_type = 'Delivery Challan Edit';
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
                        } else {
                            \Illuminate\Support\Facades\DB::rollback();
                            return response()->json(['status_code' => 403, 'message' => 'Destination stock not found.']);
                        }
                    }

                    // Revert Source
                    $findStock->quantity += $query->quantity;
                    $findStock->save();

                    $stockTransactionW = new WarehouseStockHistory();
                    $stockTransactionW->year_id = $new_year_id;
                    $stockTransactionW->purchase_direct_meta_id = 0;
                    $stockTransactionW->delivery_challan_meta_id = $query->id;
                    $stockTransactionW->stock_type = 'Delivery Challan Edit';
                    $stockTransactionW->warehous_stock_id = $findStock->id;
                    $stockTransactionW->quantity = $query->quantity;
                    $stockTransactionW->type = 'Credit';
                    $stockTransactionW->remark = 'Edit time remove Delivery Challan than reverse stock';
                    $stockTransactionW->save();

                    // Serial Numbers
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

                    $query->delete();
                    \Illuminate\Support\Facades\DB::commit();
                    return response()->json(['status_code' => 200, 'message' => 'Deleted successfully.']);
                } else {
                    \Illuminate\Support\Facades\DB::rollback();
                    return response()->json(['status_code' => 403, 'message' => 'Warehouse stock not found.']);
                }
            } else {
                return response()->json(['status_code' => 403, 'message' => 'Delivery Challan item not available.']);
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollback();
            return response()->json(['status_code' => 500, 'message' => 'Something went wrong. Please try again.']);
        }
    }

    public function importSerialNumber($id)
    {
        try {
            $data = DeliveryChallan::where('id', $id)->with('salesQuatation', 'delivery_challan_meta', 'delivery_challan_meta.serial_numbers_count', 'project')->first();

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
                                $existingSerialNumber = SerialNumber::whereIn('status', ['available', 'transfer'])->where('item_group_id', $metas->item_group_id)->where('serial_number', $serialNumber)->whereNull('deleted_at')->exists();

                                if ($existingSerialNumber) {
                                    $nData[] = $row;
                                } else {
                                    $dupData[] = $row;
                                }
                            }
                        }
                        if ($metas->quantity == count($nData)) {

                            $snldata = SerialNumberLog::where('delivery_challan_meta_id', $request->id)->get();
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
