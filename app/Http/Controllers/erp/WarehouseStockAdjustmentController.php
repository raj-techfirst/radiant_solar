<?php

namespace App\Http\Controllers\erp;

use App\Http\Controllers\Controller;
use App\Models\erp\Warehouse;
use App\Models\erp\WarehouseStock;
use App\Models\erp\WarehouseStockAdjustment;
use App\Models\erp\WarehouseStockHistory;
use App\Models\Year;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Yajra\DataTables\Facades\DataTables;

class WarehouseStockAdjustmentController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:warehouse-stock-adjust-list', ['only' => ['index']]);
        $this->middleware('permission:warehouse-stock-adjust-create', ['only' => ['create', 'store']]);
    }

    public function index()
    {
        $data = WarehouseStockAdjustment::select('id', 'warehouse_id', 'created_at','user_id')->with('user')->groupBy('user_id', DB::raw("DATE_FORMAT(created_at, '%Y-%m-%d %h:%i')"))->get();
        if (request()->ajax()) {
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    return '<a href="' . route('daily-stock-adujst', ['id' => $row->warehouse_id, 'date' => $row->created_at->format('Y-m-d')]) . '" class="avatar bg-light-success p-50 m-0" data-bs-toggle="tooltip" data-placement="left" title="View"><i class="fa fa-eye"></i></a>';
                })
                ->editColumn('warehouse_id', function ($row) {
                    return $row->warehouse->name;
                })
                ->addColumn('user_name', function ($row) {
                    return $row->user->name;
                })
                ->editColumn('created_at', function ($row) {
                    return $row->created_at->format('d-m-Y h:i:s A');
                })
                ->escapeColumns([])
                ->make(true);
        } else {
            return view('erp.warehouse-stock-adjust.index');
        }
    }

    public function create()
    {
        $warehouse = Warehouse::get();
        return view('erp.warehouse-stock-adjust.create', compact('warehouse'));
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $nonNullValues = array_filter($request->real_stock, function ($qty) {
                return !is_null($qty);
            });
            if ($nonNullValues != [] && count($request->real_stock) > 0) {
                $param = array();
                $stockParam = array();
                foreach ($request->real_stock as $key => $value) {
                    $warehouseStock = WarehouseStock::where('id', $request->id[$key])->first();

                    if (!is_null($request->real_stock[$key])) {

                        $diff = $warehouseStock->quantity - $request->real_stock[$key];
                        $stockParam[] = array(
                            'user_id' => Auth::id(),
                            'warehouse_id' => $request->warehouse_id,
                            'item_id' => $request->item_id[$key],
                            'type' => $request->item_type[$key],
                            'item_group_id' => $request->item_group_id[$key],
                            'current_stock' => $warehouseStock->quantity,
                            'real_stock' => number_format($request->real_stock[$key], 2, '.', ''),
                            'difference' => number_format($diff, 2, '.', ''),
                            'created_at' => Carbon::now(),
                        );
                        $warehouseStock->quantity = $request->real_stock[$key];
                        $warehouseStock->save();
                        $param[] = $warehouseStock;

                        $stockTransaction = new WarehouseStockHistory();
                        $stockTransaction->year_id = Year::select('id')->where('is_default', '1')->first()->id;
                        $stockTransaction->purchase_direct_meta_id = 0;
                        $stockTransaction->delivery_challan_meta_id = 0;
                        $stockTransaction->stock_type = 'Stock Adjustment';
                        $stockTransaction->warehous_stock_id = $warehouseStock->id;

                        if ($diff >= 0) {
                            $stockTransaction->quantity = abs($diff);
                            $stockTransaction->type = 'Debit';
                        } else {
                            $stockTransaction->quantity = abs($diff);
                            $stockTransaction->type = 'Credit';
                        }
                        $stockTransaction->remark = 'Warehouse Stock Adjustment';
                        $stockTransaction->save();
                    }
                }

                $result = WarehouseStockAdjustment::insert($stockParam);

                if (!is_null($result)) {
                    DB::commit();
                    return response()->json(array('status_code' => 200, 'data' => route('stock-adjustments.index'), 'message' => 'Warehouse stock adjustments successfully.'));
                } else {
                    DB::rollback();
                    return response()->json(array('status_code' => 403, 'message' => 'Something went wrong. Please try again.'));
                }
            } else {
                return response()->json(array('status_code' => 201, 'message' => 'Please set real stock.'));
            }
        } catch (\Exception $e) {
            DB::rollback();
            $response = ['status_code' => 500, 'message' => 'Something went wrong. Please try again.'];
            return response()->json($response);
        }
    }

    public function dailyStockAdujst(Request $request, $id)
    {
        try {
            $data = WarehouseStockAdjustment::with('item', 'warehouse', 'itemGroup')
                ->where('warehouse_id', $request->id)
                ->whereDate('created_at', $request->date)
                // ->whereRaw("DATE_FORMAT(created_at, '%Y-%m-%d %h:%i:%s') = ?", $request->date)
                ->get();

            if (count($data) > 0) {
                if (request()->ajax()) {
                    return DataTables::of($data)
                        ->addIndexColumn()
                        ->addColumn('item_name', function ($row) {
                            if ($row->type == "Item") {
                                return $row->item->name;
                            } else {
                                return getItemGropName($row,1);
                            }
                        })
                        ->editColumn('created_at', function ($row) {
                            return $row->created_at->format('d-m-Y h:i:s A');
                        })
                        ->escapeColumns([])
                        ->make(true);
                } else {
                    return view('erp.warehouse-stock-adjust.show', compact('data'));
                }
            } else {
                $response = Session::flash('status', 'warning');
                $response = Session::flash('message', 'stock not found.');
                return redirect()->back()->with($response);
            }
        } catch (\Exception $e) {
            $response = Session::flash('status', 'error');
            $response = Session::flash('message', 'Something went wrong. Please try again.');
            return redirect()->back()->with($response);
        }
    }

    public function show(WarehouseStockAdjustment $warehouseStockAdjustment)
    {
        //
    }

    public function edit(WarehouseStockAdjustment $warehouseStockAdjustment)
    {
        //
    }

    public function update(Request $request, WarehouseStockAdjustment $warehouseStockAdjustment)
    {
        //
    }

    public function destroy(WarehouseStockAdjustment $warehouseStockAdjustment)
    {
        //
    }
}
