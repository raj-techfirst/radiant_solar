<?php

namespace App\Http\Controllers\erp;

use App\Exports\SerialNumberExport;
use App\Http\Controllers\Controller;
use App\Imports\ItemGroupSerialnumber;
use App\Models\CompanyProfile;
use App\Models\Product;
use App\Models\erp\PurchaseDirect;
use App\Models\erp\PurchaseDirectMeta;
use App\Models\erp\SerialNumber;
use App\Models\Supplier;
use App\Models\erp\Warehouse;
use App\Models\erp\WarehouseStock;
use App\Models\erp\WarehouseStockHistory;
use App\Models\ItemGroup;
use App\Models\SalesMaster;
use App\Models\Year;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Validation\Rule;

class PurchaseDirectController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:purchase-direct-list', ['only' => ['index']]);
        $this->middleware('permission:purchase-direct-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:purchase-direct-edit', ['only' => ['edit']]);
        $this->middleware('permission:purchase-direct-delete', ['only' => ['destroy']]);
    }

    public function index()
    {
        if (request()->ajax()) {
            $year_id = getSelectedYear();
            return DataTables::of(PurchaseDirect::with('warehouse', 'supplier')->where('year_id', $year_id))
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $html = '';
                    $html .= '<a data-id="' . $row->id . '" href="javascript:void(0);" class="avatar bg-light-success p-50 m-0 view" data-bs-toggle="tooltip" data-placement="left" title="View"><i class="fa fa-eye"></i></a>';
                    if (Gate::check('purchase-direct-edit')) {
                        $html .= ' <a href="' . route('purchase-direct.edit', $row->id) . '" class="avatar bg-light-info p-50 m-0" data-bs-toggle="tooltip" data-placement="left" title="Edit"><i class="fa fa-edit"></i></a>';
                    }
                    if (Gate::check('purchase-direct-delete')) {
                        $html .= ' <a data-id="' . $row->id . '" href="javascript:void(0);" class="avatar bg-light-danger p-50 m-0 delete" data-bs-toggle="tooltip" data-placement="left" title="Delete"><i class="fa fa-trash"></i></a>';
                    }
                    $metas = PurchaseDirectMeta::where('purchase_direct_id', $row->id)->where('type', 'ItemGroup')->count();
                    if ($metas > 0) {
                        $html .= ' <a href="' . route('import-item-group-serial-number', $row->id) . '" class="avatar bg-light-primary p-50 m-0" data-bs-toggle="tooltip" data-placement="left" title="Upload Serial Number"><i class="fa fa-upload"></i></a>';
                    }

                    $html .= ' <a href="' . route('purchase-direct-clone', $row->id) . '" class="avatar bg-light-secondary p-50 m-0" data-bs-toggle="tooltip" data-placement="left" title="Convert To Goods Issue"><i class="fa fa-copy"></i></a>';


                    return $html;
                })
                ->editColumn('date', function ($row) {
                    return date("d-m-Y", strtotime($row->date));
                })
                ->addColumn('uploaded', function ($row) {
                    $srNos = getPurchaseDirectSerialnoUplodedOrNot($row->id);
                    return ($srNos->count() > 0 && isset($srNos) && $srNos[0]->serial_numbers_count_count == $srNos[0]->quantity) ? 'Uploaded' : 'No';
                })
                ->escapeColumns([])
                ->make(true);
        } else {
            return view('erp.purchase-direct.index');
        }
    }

    public function create()
    {
        $supplierList = Supplier::get();
        $items = Product::with('unit')->get();
        $itemGroup = ItemGroup::with('panel_company', 'panel_type', 'panel_watt', 'inveter_company', 'unit')->get();
        $warehouse = Warehouse::get();
        return view('erp.purchase-direct.create', compact('warehouse', 'supplierList', 'items', 'itemGroup'));
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
            'date' => 'required'
        ], [
            'warehouse_id .required' => 'Select warehouse',
            'supplier_id .required' => 'Select supplier',
            'supplier_number .required' => 'Enter invoice no.',
            'supplier_number.unique' => 'Goods Receipt has already been added',
            'date .required' => 'Select date'
        ]);

        if ($validator->fails()) {
            $response = ['status_code' => 201, 'message' => 'Please input proper data.', 'errors' => $validator->errors()];
            return response()->json($response);
        }

        DB::beginTransaction();
        try {
            if (!is_null($request->id)) {
                $qry = PurchaseDirect::where('id', $request->id)->first();
                $new_year_id = $qry->year_id;
                $response = array('status_code' => 200, 'data' => route('purchase-direct.index'), 'message' => 'Purchase direct update successfully.');
            } else {
                $new_year_id = getSelectedYear();
                $qry = new PurchaseDirect();
                $qry->grn_number = $this->getNumberOrder();
                $qry->year_id = $new_year_id;
                $response = array('status_code' => 200, 'data' => route('purchase-direct.index'), 'message' => 'Purchase direct added successfully.');
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
            foreach ($request->invoice as $key => $value) {
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
                return response()->json($response);
            } else {
                DB::rollback();
                return response()->json(array('status_code' => 403, 'message' => 'Goods Receipt added failed'));
            }
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(array('status_code' => 500, 'message' => 'Something went wrong. Please try again.'));
        }
    }

    public function getNumberOrder()
    {
        $last = PurchaseDirect::latest('id')->first();
        $currentYear = date('Y');
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

    public function show($id)
    {
        try {
            $qry = PurchaseDirect::where('id', $id)->with('purchase_direct_meta')->first();
            return view('erp.purchase-direct.view', compact('qry'))->render();
        } catch (\Exception $e) {
            $response = ['status_code' => 500, 'message' => 'Something went wrong. Please try again.'];
            return response()->json($response);
        }
    }

    public function edit($id)
    {
        try {
            $supplierList = Supplier::get();
            $items = Product::with('unit')->get();
            $itemGroup = ItemGroup::with('panel_company', 'panel_type', 'panel_watt', 'inveter_company', 'unit')->get();
            $warehouse = Warehouse::get();
            $data = PurchaseDirect::where('id', $id)->with('purchase_direct_meta')->first();
            return view('erp.purchase-direct.create', compact('data', 'supplierList', 'items', 'warehouse', 'itemGroup'));
        } catch (\Exception $e) {
            return response()->json(array('status_code' => 500, 'message' => 'Something went wrong. Please try again.'));
        }
    }

    public function update(Request $request, PurchaseDirect $purchaseDirect)
    {
        //
    }

    public function destroy($id)
    {
        try {
            $query = PurchaseDirect::with('purchase_direct_meta')->where('id', $id)->first();
            $new_year_id = $query->year_id;
            if (!is_null($query)) {
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
                        return response()->json(['status_code' => 403, 'message' => 'Insufficient stock.']);
                    }
                }
                $query->delete();
                return response()->json(['status_code' => 200, 'message' => 'Deleted successfully.']);
            } else {
                return response()->json(['status_code' => 403, 'message' => 'Goods Receipt not available.']);
            }
        } catch (\Exception $e) {
            return response()->json(['status_code' => 500, 'message' => 'Something went wrong. Please try again.']);
        }
    }

    public function deletePurchaseDirectMeta(Request $request)
    {
        try {
            $query = PurchaseDirectMeta::where('id', $request->id)->first();
            $new_year_id = $query->year_id;
            if (!is_null($query)) {
                $checkStock = WarehouseStock::where([['warehous_id', $request->warehouse_id], ['item_id', $request->item_id]])->first();
                if ($query->quantity <= $checkStock->quantity) {
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
                    return response()->json(['status_code' => 200, 'message' => 'Deleted successfully.']);
                } else {
                    return response()->json(['status_code' => 403, 'message' => 'Insufficient stock.']);
                }
            } else {
                return response()->json(['status_code' => 403, 'message' => 'Goods Receipt items not available.']);
            }
        } catch (\Exception $e) {
            return response()->json(['status_code' => 500, 'message' => 'Something went wrong. Please try again.']);
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

    public function importItemGroupSerialNumber($id)
    {
        try {
            $data = PurchaseDirect::where('id', $id)->with('warehouse', 'supplier', 'purchase_direct_meta', 'purchase_direct_meta.itemGroup', 'purchase_direct_meta.unit', 'purchase_direct_meta.serial_numbers_count')->first();

            return view('erp.purchase-direct.importSerialNumber', compact('data'));
        } catch (\Exception $e) {
            return response()->json(array('status_code' => 500, 'message' => 'Something went wrong. Please try again.'));
        }
    }

    public function importItemGroupSerialNumberStore(Request $request)
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
        // try {
        $metas = PurchaseDirectMeta::with('purchase_direct')->where('id', $request->id)->first();
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
                            $existingSerialNumber = SerialNumber::where('purchase_direct_meta_id', '!=', $request->id)->where('serial_number', $serialNumber)->whereNull('deleted_at')->exists();
                            if (!$existingSerialNumber) {
                                $nData[] = $row;
                            } else {
                                $dupData[] = $row;
                            }
                        }
                    }
                    if ($metas->quantity == count($nData)) {

                        SerialNumber::where('purchase_direct_meta_id', $request->id)->delete();

                        Excel::import(new ItemGroupSerialnumber($metas), $file);
                        DB::commit();
                        return response()->json(array('status_code' => 200, 'message' => 'Upload Successfully'));
                    } else {
                        $response = array('status_code' => 201, 'message' => 'There is a mismatch between the quantity(' . number_format($metas->quantity, 0, '', '') . ') and serial number(' . count($nData) . ').');
                        if (count($dupData) > 0) {
                            $response['html'] = view('erp.purchase-direct.importSerialNumberError', compact('nData', 'dupData'))->render();
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
        /* } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(array('status_code' => 201, 'message' => 'Something went wrong. Please try again.'));
        } */
    }

    public function cloneData($id)
    {
        try {
            $data = PurchaseDirect::where('id', $id)->with('purchase_direct_meta')->first();
            $project = SalesMaster::where('dispach_pending_list', '1')->get();
            $installer = CompanyProfile::with('user')->get();

            $temp['itemGroup'] = [];
            $temp['Item'] = [];
            $warehouseStock = WarehouseStock::with('item.unit', 'itemGroup')->where('warehous_id', $data->warehous_id)->get();
            foreach ($warehouseStock as $pro) {

                if ($pro->type == "Item") {
                    $item_id = $pro->item->id;
                    $gst_rate = $pro->item->gst_rate;
                    $display_name = $pro->item->name;
                    $unit = $pro->item->unit->unit_name;

                    $temp['Item'][] = [
                        'id' => $item_id,
                        'name' => $display_name,
                        'unit' => $unit,
                        'gst_rate' => $gst_rate,
                        'stock' => $pro->quantity ?? 0,
                    ];
                } else {

                    $item_id = $pro->itemGroup->id;
                    $gst_rate = $pro->itemGroup->gst_rate;
                    $unit = $pro->itemGroup->unit->unit_name;

                    $temp['itemGroup'][] = [
                        'id' => $item_id,
                        'name' => getItemGropName($pro, 1),
                        'unit' => $unit,
                        'gst_rate' => $gst_rate,
                        'stock' => $pro->quantity ?? 0,
                    ];
                }
            }
            $warehouseStock = collect($temp['Item']);
            $warehouseStockItemGroup = collect($temp['itemGroup']);

            return view('erp.purchase-direct.clone', compact('data', 'warehouseStock', 'warehouseStockItemGroup', 'project', 'installer'));
        } catch (\Exception $e) {
            return response()->json(array('status_code' => 500, 'message' => 'Something went wrong. Please try again.'));
        }
    }

    public function downloadSerialNumber(Request $request)
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
            return Excel::download(new SerialNumberExport($request), 'project_wise_dispach.xlsx');
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(array('status_code' => 201, 'message' => 'Something went wrong. Please try again.'));
        }
    }

    public function importItemGroupSerialNumberShow(Request $request)
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
            $serialNumber = SerialNumber::where('purchase_direct_meta_id', $request->id)->get();
            $response['html'] = view('erp.purchase-direct.importSerialNumberView', compact('serialNumber'))->render();
            return response()->json($response);
        } catch (\Exception $e) {
            return response()->json(array('status_code' => 201, 'message' => 'Something went wrong. Please try again.'));
        }
    }

    public function updateSerialNumber(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'serial_number' => [
                'required',
                Rule::unique('serial_numbers')->where(function ($query) use ($request) {
                    return $query->where('deleted_at', null)->where('id', '!=', $request->id);
                })
            ],
        ], [
            'id.required' => 'ID is Required',
            'serial_number.required' => 'Please Enter Serial no.',
            'serial_number.unique' => 'This Serial no. has already been taken'
        ]);
        if ($validator->fails()) {
            $response = ['status_code' => 201, 'message' => 'Please input proper data.', 'errors' => $validator->errors()];
            return response()->json($response);
        }
        DB::beginTransaction();
        try {
            $serialNumber = SerialNumber::where('id', $request->id)->first();
            $serialNumber->serial_number = strtoupper($request->serial_number);
            $serialNumber->warranty_start_date = ($request->warranty_start_date != "") ? date('Y-m-d', strtotime($request->warranty_start_date)) : '';
            $serialNumber->warranty_end_date = ($request->warranty_end_date != "") ? date('Y-m-d', strtotime($request->warranty_end_date)) : '';
            $serialNumber->guarantee_start_date = ($request->guarantee_start_date != "") ? date('Y-m-d', strtotime($request->guarantee_start_date)) : '';
            $serialNumber->guarantee_end_date = ($request->guarantee_end_date != "") ? date('Y-m-d', strtotime($request->guarantee_end_date)) : '';
            $serialNumber->save();
            if (!is_null($serialNumber)) {
                DB::commit();
                $data = [
                    'warranty_start_date' => ($request->warranty_start_date != "") ? date('d-m-Y', strtotime($request->warranty_start_date)) : '',
                    'warranty_end_date' => ($request->warranty_end_date != "") ? date('d-m-Y', strtotime($request->warranty_end_date)) : '',
                    'guarantee_start_date' => ($request->guarantee_start_date != "") ? date('d-m-Y', strtotime($request->guarantee_start_date)) : '',
                    'guarantee_end_date' => ($request->guarantee_end_date != "") ? date('d-m-Y', strtotime($request->guarantee_end_date)) : '',
                ];
                return response()->json(array('status_code' => 200,  'message' => 'Updated successfully.', 'data' => $data));
            } else {
                DB::rollback();
                return response()->json(array('status_code' => 403, 'message' => 'Update failed'));
            }
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(array('status_code' => 500, 'message' => 'Something went wrong. Please try again.'));
        }
    }
}
