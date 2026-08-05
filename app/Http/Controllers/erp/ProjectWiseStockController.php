<?php

namespace App\Http\Controllers\erp;

use App\Exports\ProjectStock;
use App\Exports\ProjectStockHistory;
use App\Http\Controllers\Controller;
use App\Models\CompanyProfile;
use App\Models\erp\DeliveryChallan;
use App\Models\erp\ProjectWiseStock;
use App\Models\erp\ProjectWiseStockHistory;
use App\Models\erp\Warehouse;
use App\Models\erp\WarehouseStock;
use App\Models\erp\WarehouseStockHistory;
use App\Models\Product;
use App\Models\SalesMaster;
use App\Models\Year;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class ProjectWiseStockController extends Controller
{
    public function index(Request $request)
    {
        $query = ProjectWiseStock::with('warehouse', 'project', 'item', 'itemGroup')->where(function ($q) use ($request) {
            if (!empty($request->project_id) && $request->project_id != '') {
                $q->where('sales_master_id', $request->project_id);
            }
            if (!empty($request->item_id) && $request->item_id != '') {
                $q->where('item_id', $request->item_id);
            }
            if (!empty($request->installer_id) && $request->installer_id != '') {
                $q->where('installer_id', $request->installer_id);
            }
            if (!empty($request->fdate) && !empty($request->tdate)) {
                $from_date = date('Y-m-d 00:00:00', strtotime($request->fdate));
                $to_date = date('Y-m-d 23:59:59', strtotime($request->tdate));
                $q->whereBetween('updated_at', [$from_date, $to_date]);
            }
        });

        if (request()->ajax()) {
            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $html = '';
                    $html .= '<a data-id="' . $row->id . '" href="javascript:void(0);" class="avatar bg-light-success p-50 m-0 view" data-bs-toggle="tooltip" data-placement="left" title="View"><i class="fa fa-eye"></i></a>';
                    $html .= ' <a href="' . route('export-project-stock-history', $row->id) . '" class="avatar bg-light-warning p-50 m-0 excel" data-bs-toggle="tooltip" data-placement="left" title="Excel"><i class="fa fa-download"></i></a>';
                    return $html;
                })
                ->addColumn('item_name', function ($row) {
                    if ($row->type == "Item") {
                        return $row->item->name;
                    } else {
                        return getItemGropName($row, 1);
                    }
                })
                ->editColumn('project_id', function ($row) {
                    if ($row->issue_type == "project") {
                        return $row->project->consumer_name ?? '';
                    } else {
						$a = $row->installer->name ?? '';
						$a .= $row->installer->last_name ?? '';
                        return '(Ins) ' . $a;
                    }
                })
                ->editColumn('updated_at', function ($row) {
                    return $row->updated_at->format('d-m-Y h:i:s A');
                })
                ->escapeColumns([])
                ->make(true);
        } else {
            $warehouse = Warehouse::select('id', 'name')->get();
            $item = Product::select('id', 'name')->get();
            $project = SalesMaster::where('dispach_pending_list', '1')->get();
            $installer = CompanyProfile::with('user')->get();
            return view('erp.project-stock.index', compact('warehouse', 'item', 'project', 'installer'));
        }
    }

    public function exportProjectStock(Request $request)
    {
        $query = ProjectWiseStock::with('warehouse', 'project', 'item', 'itemGroup', 'installer')->where(function ($q) use ($request) {
            if (!empty($request->project_id) && $request->project_id != '') {
                $q->where('sales_master_id', $request->project_id);
            }
            if (!empty($request->item_id) && $request->item_id != '') {
                $q->where('item_id', $request->item_id);
            }
            if (!empty($request->installer_id) && $request->installer_id != '') {
                $q->where('installer_id', $request->installer_id);
            }
            if (!empty($request->fdate) && !empty($request->tdate)) {
                $from_date = date('Y-m-d 00:00:00', strtotime($request->fdate));
                $to_date = date('Y-m-d 23:59:59', strtotime($request->tdate));
                $q->whereBetween('updated_at', [$from_date, $to_date]);
            }
        })->get();

        ob_end_clean();
        ob_start();

        if ($query->isNotEmpty()) {
            return Excel::download(new ProjectStock($query), 'project_stock.xlsx');
        } else {
            return redirect()->back()->with('error', 'No data found for the specified criteria.');
        }
    }

    public function exportProjectStockHistory($id)
    {
        $query = ProjectWiseStock::with('project', 'project_wise_history', 'installer')->where('id', $id)->first();

        ob_end_clean();
        ob_start();

        if (!is_null($query)) {
            return Excel::download(new ProjectStockHistory($query), 'project_stock_history.xlsx');
        } else {
            return redirect()->back()->with('error', 'No data found for the specified criteria.');
        }
    }

    public function create()
    {
        $warehouse = Warehouse::get();
        $project = SalesMaster::where('dispach_pending_list', '1')->get();
        $deliveryChallan = DeliveryChallan::select('id', 'challan_number')->get();
        return view('erp.project-stock.create', compact('warehouse', 'project', 'deliveryChallan'));
    }

    public function getProjectStock(Request $request)
    {
        if (!empty($request->type)) {
            if (!empty($request->issue_type) && $request->issue_type == 'project') {
                $warehouseStock = ProjectWiseStock::where('sales_master_id', $request->id)->get();
            }
            if (!empty($request->issue_type) && $request->issue_type == 'installer') {
                $warehouseStock = ProjectWiseStock::where('installer_id', $request->id)->get();
            }
            if (count($warehouseStock) > 0) {
                $data['html'] = view('erp.project-stock-adjust.render', compact('warehouseStock'))->render();
            } else {
                return response()->json(['status_code' => 403, 'message' => 'Stock not available.']);
            }
        } else {
            $warehouseStock = ProjectWiseStock::where('sales_master_id', $request->id)->get();
            if (count($warehouseStock) > 0) {
                $data['html'] = view('erp.project-stock.render', compact('warehouseStock'))->render();
            } else {
                return response()->json(['status_code' => 403, 'message' => 'Stock not available.']);
            }
        }
        return response()->json($data);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'project_id' => 'required',
            'warehouse_id' => 'required',
            'remark' => 'required',
        ], [
            'project_id.required' => 'Select project out',
            'warehouse_id.required' => 'Select project in',
            'remark.required' => 'Enter remark',
        ]);

        if ($validator->fails()) {
            $response = ['status_code' => 201, 'message' => 'Please input proper data.', 'errors' => $validator->errors()];
            return response()->json($response);
        }

        DB::beginTransaction();
        try {
            $findStock = WarehouseStock::where('warehous_id', $request->warehouse_id)->first();
            if (!is_null($findStock)) {
                foreach ($request->quantity as $key => $value) {
                    $outStock = WarehouseStock::where([['id', $request->id[$key]], ['item_id', $request->item_id[$key]]])->first();
                    $checkStock = ProjectWiseStock::where([['warehouse_id', $request->warehouse_id], ['item_id', $request->item_id[$key]]])->first();
                    if (!is_null($checkStock)) {
                        $projectStock = $checkStock;
                        $qty = $checkStock->quantity + $request->quantity[$key];
                    } else {
                        $projectStock = new ProjectWiseStock();
                        $qty = $request->quantity[$key];
                    }
                    $projectStock->quantity = $qty;
                    $projectStock->warehouse_id = $request->warehouse_id;
                    $projectStock->project_id = $request->project_id;
                    $projectStock->item_id = $request->item_id[$key];
                    $projectStock->save();

                    $stockTransaction = new ProjectWiseStockHistory();
                    $stockTransaction->project_wise_stock_id = $projectStock->id;
                    $stockTransaction->quantity = $request->quantity[$key];
                    $stockTransaction->type = 'Credit';
                    $stockTransaction->remark = 'Delivery Challan from ' . $outStock->warehouse->name . ', ' . $request->remark;
                    $stockTransaction->save();

                    $outStock->quantity -= $request->quantity[$key];
                    $result = $outStock->save();

                    $debitStock = new WarehouseStockHistory();
                    $debitStock->year_id = Year::select('id')->where('is_default', '1')->first()->id;
                    $debitStock->warehous_stock_id = $outStock->id;
                    $debitStock->quantity = $request->quantity[$key];
                    $debitStock->type = 'Debit';
                    $debitStock->remark = 'Delivery Challan to ' . $projectStock->project->consumer_name . ', ' . $request->remark;
                    $debitStock->save();
                }
            }
            if (!is_null($result)) {
                DB::commit();
                $response = array('status_code' => 200, 'data' => route('project-wise-stock.index'), 'message' => 'Project stock transfer successfully.');
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
        $projectWiseStock = ProjectWiseStock::with('project', 'project_wise_history', 'project_wise_history.delivery_challan_meta')->where('id', $id)->first();
        $data['html'] = view('erp.project-stock.model', compact('projectWiseStock'))->render();
        return response()->json($data);
    }

    public function edit(ProjectWiseStock $projectWiseStock)
    {
        //
    }

    public function update(Request $request, ProjectWiseStock $projectWiseStock)
    {
        //
    }

    public function destroy(ProjectWiseStock $projectWiseStock)
    {
        //
    }
}
