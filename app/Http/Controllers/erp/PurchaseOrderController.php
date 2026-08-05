<?php

namespace App\Http\Controllers\erp;

use App\Http\Controllers\Controller;
use App\Models\erp\GoodsReceiveNote;
use App\Models\erp\PurchaseOrder;
use App\Models\erp\PurchaseOrderMeta;
use App\Models\erp\PurchaseOrderReceive;
use App\Models\erp\Warehouse;
use App\Models\ItemGroup;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Year;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class PurchaseOrderController extends Controller
{

    function __construct()
    {
        $this->middleware('permission:purchase-order-list|purchase-order-create|purchase-order-edit|purchase-order-delete', ['only' => ['index', 'store']]);
        $this->middleware('permission:purchase-order-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:purchase-order-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:purchase-order-delete', ['only' => ['destroy']]);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
       
        if (request()->ajax()) {
            $year_id = getSelectedYear();
            $query = PurchaseOrder::with('supplier')->where('year_id',$year_id);
            return DataTables::of($query)
                ->addIndexColumn()
                ->editColumn('purchase_date', function ($row) {
                    return date('d-m-Y', strtotime($row->purchase_date));
                })
                ->editColumn('total_amount', function ($row) {
                    return ' ₹ ' . $row->total_amount;
                })
                ->editColumn('status', function ($row) {
                    if ($row->status == 'Pending') {
                        $html = '<div class="">';
                        $active1 =  $active2  = $active3  = $active4  = '';
                        if ($row->status == 'Pending') {
                            $btn = "btn-outline-warning";
                            $title = "Pending";
                            $active1 = "active bg-warning";
                        }
                        if ($row->status == 'Approved By Admin') {
                            $btn = "btn-outline-success";
                            $title = "Approved By Admin";
                            $active2 = "active bg-success";
                        }
                        if ($row->status == 'Cancle') {
                            $btn = "btn-outline-danger";
                            $title = "Cancel";
                            $active3 = "active bg-danger";
                        }
                        if ($row->status == 'Manualy Close') {
                            $btn = "btn-outline-primary";
                            $title = "Manually Close";
                            $active4 = "active bg-primary";
                        }
                        $html .= '<div class="btn-group my-dropdown dropdown">
                                    <button type="button" class="btn-sm btn ' . $btn . '">' . $title . '</button>
                                    <button type="button" class="btn-sm btn ' . $btn . ' dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false">
                                        <span class="visually-hidden">Toggle Dropdown</span>
                                    </button>
                                    <ul class="dropdown-menu p-0" container="body">
                                        <li><a class="dropdown-item change-status ' . $active1 . '" href="javascript:void(0);" data-id="' . $row->id . '" data-value="Pending">Pending</a></li>';

                        $html .=  '<li><a class="dropdown-item change-status ' . $active2 . '" href="javascript:void(0);" data-id="' . $row->id . '" data-value="Approved By Admin">Approved By Admin</a></li>';


                        $html .= '<li><a class="dropdown-item change-status ' . $active3 . '" href="javascript:void(0);" data-id="' . $row->id . '" data-value="Cancle">Cancel</a></li>
                                        <li><a class="dropdown-item change-status ' . $active4 . '" href="javascript:void(0);" data-id="' . $row->id . '" data-value="Manualy Close">Manually Close</a></li>
                                    </ul>
                                </div>';
                        return $html;
                    } elseif ($row->status == 'Half Receive' || $row->status == 'Approved By Admin') {
                        $active1 =  $active2  = '';
                        if ($row->status == 'Approved By Admin') {
                            $btn = "btn-outline-primary";
                            $title = "Approved By Admin";
                            $active2 = "active bg-primary";
                        }
                        if ($row->status == 'Half Receive') {
                            $btn = "btn-outline-info";
                            $title = "Half Receive";
                            $active2 = "active bg-info";
                        }
                        $html = '<div class="">';
                        $html .= '<div class="btn-group my-dropdown">
                                    <button type="button" class="btn-sm btn ' . $btn . '">' . $title . '</button>
                                    <button type="button" class="btn-sm btn ' . $btn . ' dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false">
                                        <span class="visually-hidden">Toggle Dropdown</span>
                                    </button>
                                    <ul class="dropdown-menu p-0" container="body">
                                        <li><a class="dropdown-item change-status" href="javascript:void(0);" data-id="' . $row->id . '" data-value="Manualy Close">Manually Close</a></li>
                                    </ul>
                                </div>';
                        return $html;
                    } else {
                        switch ($row->status) {
                            case 'Pending':
                                $status = '<span class="badge badge-glow bg-warning">Pending</span>';
                                break;
                            case 'Approved By Admin':
                                $status = '<span class="badge badge-glow bg-primary">Approved By Admin</span>';
                                break;
                            case 'Half Receive':
                                $status = '<span class="badge badge-glow bg-info">Half Receive</span>';
                                break;
                            case 'Receive':
                                $status = '<span class="badge badge-glow bg-success">Receive</span>';
                                break;
                            case 'Cancle':
                                $status = '<span class="badge badge-glow bg-danger">Cancel</span>';
                                break;
                            case 'Manualy Close':
                                $status = '<span class="badge badge-glow bg-secondary">Manually Close</span>';
                                break;
                            default:
                                $status = '<span class="badge badge-glow bg-warning">Pending</span>';
                        }
                        return $status;
                    }
                })
                ->addColumn('action', function ($row) {
                    $html = '';
                    $html .= ' <a data-id="' . $row->id . '" class="avatar bg-light-success p-50 m-0 view" data-bs-toggle="tooltip" data-placement="left" title="View"><i class="fa fa-eye"></i></a>';
                    if ($row->status == 'Pending' || Gate::check('purchase-order-edit')) {
                        $html .= ' <a href="' . route('purchase-order.edit', $row->id) . '" class="avatar bg-light-primary p-50 m-0" data-bs-toggle="tooltip" data-placement="left" title="Edit"><i class="fa fa-edit"></i></a>';
                    }
                    if ($row->status == 'Pending' || Gate::check('purchase-order-delete')) {
                        $html .= ' <a data-id="' . $row->id . '" class="avatar bg-light-danger p-50 m-0 delete" data-bs-toggle="tooltip" data-placement="left" title="Delete"><i class="fa fa-trash"></i></a>';
                    }
                    $html .= ' <a href="' . route('purchase-order-pdf', $row->id) . '" class="avatar bg-light-warning p-50 m-0" data-bs-toggle="tooltip" data-placement="left" title="PDF"><i class="fa fa-download"></i></a>';
                    $html .= ' <a href="' . route('purchase-order-clone', $row->id) . '" class="avatar bg-light-secondary p-50 m-0" data-bs-toggle="tooltip" data-placement="left" title="Convert To Goods Receipt"><i class="fa fa-copy"></i></a>';

                    // if ($row->status == 'Receive' || $row->status == 'Half Receive') {
                    //     $html .= ' <button data-id="' . $row->id . '" class="btn btn-sm btn-gradient-info return" data-bs-toggle="tooltip" data-placement="left" title="Return"><i class="fa fa-refresh"></i></button>';
                    // }
                    // if ($row->status != 'Pending' && $row->status != 'Manualy Close' && $row->status != 'Cancle') {
                    //     $html .= ' <button data-id="' . $row->id . '" class="btn btn-sm btn-gradient-success receive" data-bs-toggle="tooltip" data-placement="left" title="Receive"><i class="fa fa-truck"></i></button>';
                    // }
                    return $html;
                })
                ->escapeColumns([])
                ->make(true);
        } else {
            return view('erp.purchase-order.index');
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $supplierList = Supplier::get();
        $items = Product::with('unit')->get();
        $itemGroup = ItemGroup::with('panel_company', 'panel_type', 'panel_watt', 'inveter_company', 'unit')->get();
        return view('erp.purchase-order.create', compact('supplierList', 'items', 'itemGroup'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'supplier_id' => 'required',
            'purchase_date' => 'required',
        ], [
            'supplier_id .required' => 'Select supplier',
            'purchase_date .required' => 'Select date',
        ]);

        if ($validator->fails()) {
            $response = ['status_code' => 201, 'message' => 'Please input proper data.', 'errors' => $validator->errors()];
            return response()->json($response);
        }

        DB::beginTransaction();
        try {
            $new_year_id = getSelectedYear();
            $addPurches = array(
                'supplier_id' => $request->supplier_id,
                'purchase_date' => date('Y-m-d', strtotime($request->purchase_date)),
                'remark' => $request->remark,
                'shipping_name' => $request->shipping_name,
                'shipping_mobile' => $request->shipping_mobile,
                'shipping_email' => $request->shipping_email,
                'shipping_address' => $request->shipping_address,
                'shipping_gst' => $request->shipping_gst,
                'po_number' => $this->poNumber(),
                'status' => 'Pending',
                'year_id' => $new_year_id
            );
            $savePruches = PurchaseOrder::create($addPurches);
            $toalAmount = 0;
            $purchesMeta = array();
            foreach ($request->invoice as $in) {
                $product_id = $item_group_id = 0;
                if (!empty($in['product_id']) || !empty($in['item_group_id'])) {
                    if ($in['type'] == "Item") {
                        $unit_id = Product::where('id', $in['product_id'])->first()->unit_id;
                        $product_id = $in['product_id'];
                        $remarks = $in['item_remark'];
                    } else {
                        $unit_id = ItemGroup::where('id', $in['item_group_id'])->first()->unit_id;
                        $item_group_id = $in['item_group_id'];
                        $remarks = $in['item_group_remark'];
                    }
                    $basicAmount = ($in['quantity'] * $in['price']);
                    $toalAmount += $basicAmount +  $in['gst_amt'];
                    $addPurchesMeta = array(
                        'purchase_order_id' => $savePruches->id,
                        'type' => $in['type'],
                        'product_id' => $product_id,
                        'item_group_id' => $item_group_id,
                        'unit_id' => $unit_id,
                        'quantity' => $in['quantity'],
                        'price' => $in['price'],
                        'total' => $basicAmount,
                        'gst_tax' => $in['gst'],
                        'gst_amount' => $in['gst_amt'],
                        'remarks' => $remarks,
                        'year_id' => $new_year_id
                    );
                    $purchesMeta[] = $addPurchesMeta;
                    PurchaseOrderMeta::create($addPurchesMeta);
                }
            }
            $result = PurchaseOrder::where('id', $savePruches->id)->update(['total_amount' => round($toalAmount)]);
            if (!is_null($result)) {
                DB::commit();
                return response()->json(array('status_code' => 200, 'data' => route('purchase-order.index'), 'message' => 'Purchase order added successfully.'));
            } else {
                DB::rollback();
                return response()->json(array('status_code' => 403, 'message' => 'Purchase order added failed'));
            }
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(array('status_code' => 500, 'message' => 'Something went wrong. Please try again.'));
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\erp\PurchaseOrder  $purchaseOrder
     * @return \Illuminate\Http\Response
     */
    public function show(PurchaseOrder $purchaseOrder, Request $request)
    {
        try {
            $purchaseOrderMeta = PurchaseOrderMeta::with('product', 'unit', 'itemGroup', 'itemGroup.panel_company', 'itemGroup.panel_type', 'itemGroup.panel_watt', 'itemGroup.inveter_company')
                ->where('purchase_order_metas.purchase_order_id', $purchaseOrder->id)->get();
            return view('erp.purchase-order.view', compact('purchaseOrder', 'purchaseOrderMeta'))->render();
        } catch (\Exception $e) {
            $response = ['status_code' => 500, 'message' => 'Something went wrong. Please try again.'];
            return response()->json($response);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\erp\PurchaseOrder  $purchaseOrder
     * @return \Illuminate\Http\Response
     */
    public function edit(PurchaseOrder $purchaseOrder)
    {
        try {
            $supplierList = Supplier::get();
            $items = Product::with('unit')->get();
            $itemGroup = ItemGroup::with('panel_company', 'panel_type', 'panel_watt', 'inveter_company', 'unit')->get();
            $purchaseOrderMeta = purchaseOrderMeta::where('purchase_order_id', $purchaseOrder->id)->get();
            return view('erp.purchase-order.edit', compact('purchaseOrder', 'purchaseOrderMeta', 'supplierList', 'items', 'itemGroup'));
        } catch (\Exception $e) {
            return response()->json(array('status_code' => 500, 'message' => 'Something went wrong. Please try again.'));
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\erp\PurchaseOrder  $purchaseOrder
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, PurchaseOrder $purchaseOrder)
    {
        $validator = Validator::make($request->all(), [
            'supplier_id' => 'required',
            'purchase_date' => 'required',
        ], [
            'supplier_id .required' => 'Select supplier',
            'purchase_date .required' => 'Select date',
        ]);

        if ($validator->fails()) {
            $response = ['status_code' => 201, 'message' => 'Please input proper data.', 'errors' => $validator->errors()];
            return response()->json($response);
        }

        DB::beginTransaction();
        try {
            $updatePurches = array(
                'supplier_id' => $request->supplier_id,
                'purchase_date' => date('Y-m-d', strtotime($request->purchase_date)),
                'remark' => $request->remark,
                'shipping_name' => $request->shipping_name,
                'shipping_mobile' => $request->shipping_mobile,
                'shipping_email' => $request->shipping_email,
                'shipping_address' => $request->shipping_address,
                'shipping_gst' => $request->shipping_gst,
            );
            PurchaseOrder::where('id', $purchaseOrder->id)->update($updatePurches);
            $toalAmount = 0;

            foreach ($request->invoice as $in) {
                $product_id = $item_group_id = 0;
                if (!empty($in['product_id']) || !empty($in['item_group_id'])) {
                    if ($in['type'] == "Item") {
                        $unit_id = Product::where('id', $in['product_id'])->first()->unit_id;
                        $product_id = $in['product_id'];
                        $remarks = $in['item_remark'];
                    } else {
                        $unit_id = ItemGroup::where('id', $in['item_group_id'])->first()->unit_id;
                        $item_group_id = $in['item_group_id'];
                        $remarks = $in['item_group_remark'];
                    }
                    $basicAmount = ($in['quantity'] * $in['price']);
                    $toalAmount += $basicAmount + $in['gst_amt'];

                    $updatePurchesMeta = array(
                        'purchase_order_id' => $purchaseOrder->id,
                        'type' => $in['type'],
                        'product_id' => $product_id,
                        'item_group_id' => $item_group_id,
                        'unit_id' => $unit_id,
                        'quantity' => $in['quantity'],
                        'price' => $in['price'],
                        'total' => $basicAmount,
                        'gst_tax' => $in['gst'],
                        'gst_amount' => $in['gst_amt'],
                        'remarks' => $remarks,
                    );
                    if (isset($in['meta_id']) && $in['meta_id'] != "") {
                        PurchaseOrderMeta::where('id', $in['meta_id'])->update($updatePurchesMeta);
                    } else {
                        PurchaseOrderMeta::create($updatePurchesMeta);
                    }
                }
            }
            $result = PurchaseOrder::where('id', $purchaseOrder->id)->update(['total_amount' => round($toalAmount)]);
            if (!is_null($result)) {
                DB::commit();
                return response()->json(array('status_code' => 200, 'data' => route('purchase-order.index'), 'message' => 'Purchase order update successfully.'));
            } else {
                DB::rollback();
                return response()->json(array('status_code' => 403, 'message' => 'Purchase order update failed.'));
            }
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(array('status_code' => 500, 'message' => 'Something went wrong. Please try again.'));
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\erp\PurchaseOrder  $purchaseOrder
     * @return \Illuminate\Http\Response
     */
    public function destroy(PurchaseOrder $purchaseOrder)
    {
        try {
            if ($purchaseOrder->status == "Pending" || $purchaseOrder->status == 'Approved By Admin') {
                PurchaseOrderMeta::where('purchase_order_id', $purchaseOrder->id)->delete();
                $purchaseOrder->delete();
                $response = ['status_code' => 200, 'message' => 'Deleted successfully.'];
            } else {
                $response = ['status_code' => 201, 'message' => 'Order Is Under ' . $purchaseOrder->status];
            }
            return response()->json($response);
        } catch (\Exception $e) {
            $response = ['status_code' => 500, 'message' => 'Something went wrong. Please try again.'];
            return response()->json($response);
        }
    }

    public function poNumber()
    {
        $last = PurchaseOrder::latest('id')->first();
        $currentYear = date('y') . date('m');
        if (!is_null($last)) {
            $item = $last->po_number;
            $nwMsg = explode("/", $item);
            $lastYear = $nwMsg[1];
            if ($lastYear == $currentYear) {
                $inMsg = sprintf("%05d", ($nwMsg[2]) + 1);
                return $nwMsg[0] . '/' . $nwMsg[1] . '/' . $inMsg;
            } else {
                return 'PO/' . $currentYear . '/00001';
            }
        } else {
            return 'PO/' . $currentYear . '/00001';
        }
    }

    public function changePurchaseStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required',
            'id' => 'required',
        ], [
            'status.required' => 'Please Select Status',
            'id.required' => 'Order Not Found',
        ]);

        if ($validator->fails()) {
            $response = ['status' => 200, 'message' => 'Please input proper data.', 'errors' => $validator->errors()];
            return response()->json($response, 404);
        }
        DB::beginTransaction();
        try {
            $getOrder = PurchaseOrder::findOrFail($request->id);
            $getOrder->status = $request->status;
            $result = $getOrder->save();
            if ($result) {
                DB::commit();
                return response()->json(array('status_code' => 200,  'message' => 'Status change successfully.'));
            } else {
                DB::rollback();
                return response()->json(array('status_code' => 403, 'message' => 'Status change failed.'));
            }
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(array('status_code' => 500, 'message' => 'Something went wrong. Please try again.'), 404);
        }
    }

    public function purchaseOrderPdf($id)
    {
        $purchaseOrder = PurchaseOrder::with('supplier')->where('id', $id)->first();
        $purchaseList = PurchaseOrderMeta::with('product', 'unit', 'itemGroup', 'itemGroup.panel_company', 'itemGroup.panel_type', 'itemGroup.panel_watt', 'itemGroup.inveter_company')
            ->where('purchase_order_metas.purchase_order_id', $purchaseOrder->id)
            ->get();
        if (!is_null($purchaseOrder)) {
            $data = [
                'title' => 'Purchase Order',
                'purchaseOrder' => $purchaseOrder,
                'purchaseList' => $purchaseList,
            ];
            $name = $purchaseOrder->po_number;

            //return view('erp.purchase-order.pdf', $data);

            $pdf = Pdf::loadView('erp.purchase-order.pdf', $data);
            return $pdf->download($name . '-purchase.pdf');
        } else {
            return abort(404);
        }
    }

    public function purcahseReceive(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required'
        ], [
            'id.required' => 'Order Receive Not Found',
        ]);

        if ($validator->fails()) {
            $response = ['status_code' => 200, 'message' => 'Please input proper data.', 'errors' => $validator->errors()];
            return response()->json($response, 404);
        }
        try {
            $goods = GoodsReceiveNote::with('receiveProducts')->where('purchase_order_id', $request->id)->get();
            //$receiveQty = PurchaseOrderReceive::with('product', 'unit')->where('purchase_order_id', $request->id)->get();
            $purchaseOrder = PurchaseOrder::where('id', $request->id)->first();
            if ($purchaseOrder->status == 'Approved By Admin' || $purchaseOrder->status == 'Half Receive' || $purchaseOrder->status == 'Receive' || $purchaseOrder->status == 'Manualy Close') {
                $purchaseOrderMeta = PurchaseOrderMeta::with('product', 'unit')->where('purchase_order_id', $purchaseOrder->id)->get();
                return view('erp.purchase-order.recive', compact('purchaseOrder', 'purchaseOrderMeta', 'goods'))->render();
            } else {
                return response()->json(array('status_code' => 200, 'message' => 'This Purchase Not Accepted By Admin'), 404);
            }
        } catch (\Exception $e) {
            return $e;
        }
    }

    public function purcahseReceiveStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'p_id.*' => 'required',
            'invoice_number' => 'required'
        ], [
            'p_id.required' => 'Order Receive Not Found',
            'invoice_number' => 'Enter invoice number'
        ]);

        if ($validator->fails()) {
            $response = ['status_code' => 201, 'message' => 'Please input proper data.', 'errors' => $validator->errors()];
            return response()->json($response);
        }
        DB::beginTransaction();
        try {
            $result = '';
            $hasQty = !empty(array_filter($request->receive_qty));
            if ($hasQty) {
                $addGRN = array(
                    'purchase_order_id' => 5,
                    'grn_number' =>  $this->grnNumber(),
                    'invoice_number' =>  $request->invoice_number,
                    'remark' =>  $request->remark,
                    'user_id' => auth()->user()->id,
                    'status' => 'Verified',
                );
                $resultGRN = GoodsReceiveNote::create($addGRN);
                foreach ($request->meta_id as $key => $pr) {
                    $getOrderDetails = PurchaseOrderMeta::where('id', $pr)->first();
                    $receiveQty = $request->receive_qty[$key];
                    if ($request->receive_qty[$key] > 0) {
                        $poMeta = PurchaseOrderMeta::where([['purchase_order_id', $getOrderDetails->purchase_order_id], ['product_id', $getOrderDetails->product_id]])->first();
                        $poMeta->remaining_qty = $request->remaining[$key];
                        $poMeta->save();
                        $addReceive = array(
                            'goods_receive_note_id' =>  $resultGRN->id,
                            'purchase_order_id' =>  $getOrderDetails->purchase_order_id,
                            'purchase_order_meta_id' =>  $pr,
                            'product_id' =>  $getOrderDetails->product_id,
                            'receive_qty' => (isset($request->receive_qty[$key])) ? $request->receive_qty[$key] : 0,
                        );
                        $result = PurchaseOrderReceive::create($addReceive);
                    }
                    $is_receive = true;
                    $getPOMeta = PurchaseOrderMeta::where('purchase_order_id', $getOrderDetails->purchase_order_id)->get();
                    if ($getPOMeta->count() > 0) {
                        foreach ($getPOMeta as $key => $getPOMetaValue) {
                            $receiveQty = PurchaseOrderReceive::where('product_id', $getPOMetaValue->product_id)->where('purchase_order_id', $getOrderDetails->purchase_order_id)->sum('receive_qty');
                            if ($getPOMetaValue->quantity > $receiveQty) {
                                $is_receive = false;
                            }
                        }
                    }
                    if ($is_receive) {
                        PurchaseOrder::where('id', $getOrderDetails->purchase_order_id)->update(['status' => 'Receive']);
                    } else {
                        PurchaseOrder::where('id', $getOrderDetails->purchase_order_id)->update(['status' => 'Half Receive']);
                    }
                }
                if ($result) {
                    DB::commit();
                    return response()->json(array('status_code' => 200, 'data' => route('purchase-order.index'), 'message' => 'Purchase order update successfully.'));
                } else {
                    DB::rollback();
                    return response()->json(array('status_code' => 403, 'message' => 'Purchase order update failed.'));
                }
            } else {
                return response()->json(array('status_code' => 403, 'message' => 'Please input proper data'));
            }
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(array('status_code' => 500, 'message' => 'Something went wrong. Please try again.'));
        }
    }

    public function grnNumber()
    {
        $last = GoodsReceiveNote::latest('id')->first();
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

    public function cloneData($id)
    {
        try {
            $supplierList = Supplier::get();
            $items = Product::with('unit')->get();
            $itemGroup = ItemGroup::with('panel_company', 'panel_type', 'panel_watt', 'inveter_company', 'unit')->get();
            $purchaseOrder = purchaseOrder::where('id', $id)->first();
            $purchaseOrderMeta = purchaseOrderMeta::where('purchase_order_id', $id)->get();
            $warehouse = Warehouse::get();
            return view('erp.purchase-order.clone', compact('purchaseOrder', 'purchaseOrderMeta', 'supplierList', 'items', 'itemGroup', 'warehouse'));
        } catch (\Exception $e) {
            return response()->json(array('status_code' => 500, 'message' => 'Something went wrong. Please try again.'));
        }
    }
}
