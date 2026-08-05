<?php

namespace App\Http\Controllers\erp;

use App\Http\Controllers\Controller;
use App\Models\CompanyProfile;
use App\Models\erp\DeliveryChallanReturn;
use App\Models\erp\DeliveryChallanReturnMeta;
use App\Models\erp\ProjectWiseStock;
use App\Models\erp\ProjectWiseStockHistory;
use App\Models\erp\Warehouse;
use App\Models\erp\WarehouseStock;
use App\Models\erp\WarehouseStockHistory;
use App\Models\ItemGroup;
use App\Models\Product;
use App\Models\SalesMaster;
use App\Models\Year;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Barryvdh\DomPDF\Facade\Pdf;

class DeliveryChallanReturnController extends Controller
{
    public function index()
    {
        if (request()->ajax()) {
            $year_id = getSelectedYear();
            return DataTables::of(DeliveryChallanReturn::with('warehouse', 'project')->where('year_id',$year_id))
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $html = '';
                    $html .= '<a data-id="' . $row->id . '" href="javascript:void(0);" class="avatar bg-light-success p-50 m-0 view" data-bs-toggle="tooltip" data-placement="left" title="View"><i class="fa fa-eye"></i></a>';
                    // if (Gate::check('delivery-challan-return-edit')) {
                    //     $html .= ' <a href="' . route('delivery-challan-return.edit', $row->id) . '" class="avatar bg-light-info p-50 m-0" data-bs-toggle="tooltip" data-placement="left" title="Edit"><i class="fa fa-edit"></i></a>';
                    // }
                    // if (Gate::check('delivery-challan-return-delete')) {
                    //     $html .= ' <a data-id="' . $row->id . '" href="javascript:void(0);" class="avatar bg-light-danger p-50 m-0 delete" data-bs-toggle="tooltip" data-placement="left" title="Delete"><i class="fa fa-trash"></i></a>';
                    // }
                    $html .= '<a href="' . route('delivery-challan-return-pdf', $row->id) . '" role="button" class="avatar bg-light-warning p-50 m-0" data-bs-toggle="tooltip" data-placement="left" title="PDF"><i class="fa fa-download"></i></a>';

                    return $html;
                })
                ->editColumn('challan_date', function ($row) {
                    return date("d-m-Y", strtotime($row->challan_date));
                })
                ->editColumn('project_id', function ($row) {
                    if ($row->issue_type == "project") {
                        return $row->project->consumer_name;
                    } else {
                        return '(Ins) ' . $row->installer->name . ' ' . $row->installer->last_name;
                    }
                })
                ->escapeColumns([])
                ->make(true);
        } else {
            return view('erp.delivery-challan-return.index');
        }
    }

    public function pdf(Request $request, $id)
    {
        $data = DeliveryChallanReturn::with('warehouse', 'project', 'delivery_challan_return_meta')->where('id', $id)->first();
        if (!is_null($data)) {
            $data_array = [
                'title' => 'Delivery Challan',
                'data' => $data,
            ];
            $name = $data->challan_number;
            $pdf = Pdf::loadView('erp.delivery-challan-return.pdf', $data_array);
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
        return view('erp.delivery-challan-return.create', compact('warehouse', 'project', 'installer'));
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

            $new_year_id = getSelectedYear();
            $qry = new DeliveryChallanReturn();
            $qry->year_id = $new_year_id;
            $qry->challan_number = $this->getNumberOrder();
            $response = array('status_code' => 200, 'data' => route('delivery-challan-return.index'), 'message' => 'Delivery Challan Return added successfully.');

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
                            return response()->json(array('status_code' => 403, 'message' => 'Sorry, stock is insufficient.'));
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
                            return response()->json(array('status_code' => 403, 'message' => 'Sorry, stock is insufficient.'));
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

    public function show($id)
    {
        $qry = DeliveryChallanReturn::with('delivery_challan_return_meta')->where('id', $id)->first();
        $data['html'] = view('erp.delivery-challan-return.model', compact('qry'))->render();
        return response()->json($data);
    }

    public function edit($id)
    {
        $data = DeliveryChallanReturn::where('id', $id)->first();
        $project = SalesMaster::where('dispach_pending_list', '1')->get();
        $warehouse = Warehouse::select('id', 'name')->get();
        $installer = CompanyProfile::with('user')->get();
        return view('erp.delivery-challan-return.create', compact('warehouse', 'project', 'data', 'installer'));
    }

    public function update(Request $request, DeliveryChallanReturn $deliveryChallan)
    {
        //
    }

    public function destroy($id)
    {
        try {
            $query = DeliveryChallanReturn::with('delivery_challan_return_meta')->where('id', $id)->first();
            $new_year_id = $query->year_id;
            if (!is_null($query)) {
                if (count($query->delivery_challan_meta) > 0) {
                    foreach ($query->delivery_challan_meta as $item) {
                        $findStock = WarehouseStock::where([['warehous_id', $query->warehouse_id], ['item_id', $item->item_id]])->first();
                        if (!is_null($findStock)) {
                            $checkStock = ProjectWiseStock::where([['sales_master_id', $query->sales_master_id], ['item_id', $item->item_id]])->first();
                            if (!is_null($checkStock)) {
                                if ($item->quantity <= $checkStock->quantity) {
                                    $checkStock->quantity -= $item->quantity;
                                    $checkStock->save();

                                    $stockTransactionP = new ProjectWiseStockHistory();
                                    $stockTransactionP->delivery_challan_meta_id = $item->id;
                                    $stockTransactionP->project_wise_stock_id = $checkStock->id;
                                    $stockTransactionP->quantity = $item->quantity;
                                    $stockTransactionP->type = 'Debit';
                                    $stockTransactionP->remark = 'Edit time remove Delivery Challan Return than reverse stock';
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
                                    $stockTransactionW->remark = 'Edit time remove Delivery Challan Return than reverse stock';
                                    $stockTransactionW->save();
                                    $item->delete();
                                } else {
                                    return response()->json(['status_code' => 403, 'message' => 'Insufficient stock.']);
                                }
                            } else {
                                return response()->json(['status_code' => 403, 'message' => 'Project wise stock not found.']);
                            }
                        } else {
                            return response()->json(['status_code' => 403, 'message' => 'Warehouse stock not found.']);
                        }
                    }
                    $query->delete();
                    return response()->json(['status_code' => 200, 'message' => 'Deleted successfully.']);
                } else {
                    return response()->json(['status_code' => 403, 'message' => 'Delivery challan return item not available.']);
                }
            } else {
                return response()->json(['status_code' => 403, 'message' => 'Delivery challan return not available.']);
            }
        } catch (\Exception $e) {
            return response()->json(['status_code' => 500, 'message' => 'Something went wrong. Please try again.']);
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
            $response = ['status_code' => 201, 'message' => 'Please input proper data.', 'errors' => $validator->errors()];
            return response()->json($response);
        }

         try {
            if ($request->type == "installer") {
                $field = 'installer_id';
            } else {
                $field = "sales_master_id";
            }
            $projectWiseStock = ProjectWiseStock::with('item.unit', 'itemGroup')->where('issue_type', $request->type)->where($field, $request->id)->get();

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
                $data['html'] = view('erp.delivery-challan-return.render', compact('warehouseStock', 'warehouseStockItemGroup'))->render();

                return response()->json($data);
            } else {
                return response()->json(['status_code' => 403, 'message' => 'Project wise stock not found.']);
            }
        } catch (\Exception $e) {
            return response()->json(['status_code' => 500, 'message' => 'Something went wrong. Please try again.']);
        }
    }
}
