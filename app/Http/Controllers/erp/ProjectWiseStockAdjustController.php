<?php

namespace App\Http\Controllers\erp;

use App\Http\Controllers\Controller;
use App\Models\CompanyProfile;
use App\Models\erp\ProjectWiseStock;
use App\Models\erp\ProjectWiseStockAdjust;
use App\Models\erp\ProjectWiseStockHistory;
use App\Models\erp\Warehouse;
use App\Models\SalesMaster;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Yajra\DataTables\Facades\DataTables;

class ProjectWiseStockAdjustController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:project-stock-adjust-list', ['only' => ['index']]);
        $this->middleware('permission:project-stock-adjust-create', ['only' => ['create', 'store']]);
    }

    public function index()
    {
        $data = ProjectWiseStockAdjust::select('id', 'issue_type', 'user_id', 'sales_master_id', 'created_at', 'installer_id')
            ->with('user', 'project', 'installer')
            ->groupBy('sales_master_id', 'installer_id', DB::raw("DATE_FORMAT(created_at, '%Y-%m-%d')"))->get();
        if (request()->ajax()) {
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    if ($row->issue_type == "project") {
                        return '<a href="' . route('project-daily-stock-adjust', ['id' => $row->sales_master_id, 'column_name' =>  'sales_master_id', 'date' => $row->created_at->format('Y-m-d')]) . '" class="avatar bg-light-success p-50 m-0" data-bs-toggle="tooltip" data-placement="left" title="View"><i class="fa fa-eye"></i></a>';
                    } else {
                        return '<a href="' . route('project-daily-stock-adjust', ['id' => $row->installer_id, 'column_name' => 'installer_id', 'date' => $row->created_at->format('Y-m-d')]) . '" class="avatar bg-light-success p-50 m-0" data-bs-toggle="tooltip" data-placement="left" title="View"><i class="fa fa-eye"></i></a>';
                    }
                })
                ->editColumn('project_name', function ($row) {
                    if ($row->issue_type == "project") {
                        return $row->project->consumer_name;
                    } else {
                        return '(Ins) ' . $row->installer->name . ' ' . $row->installer->last_name;
                    }
                })
                ->editColumn('created_at', function ($row) {
                    return $row->created_at->format('d-m-Y h:i:s A');
                })
                ->escapeColumns([])
                ->make(true);
        } else {
            return view('erp.project-stock-adjust.index');
        }
    }

    public function create()
    {
        $warehouse = Warehouse::get();
        $project = SalesMaster::where('dispach_pending_list', '1')->get();
        $installer = CompanyProfile::with('user')->get();
        return view('erp.project-stock-adjust.create', compact('warehouse', 'project', 'installer'));
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $nonNullValues = array_filter($request->real_stock, function ($qty) {
                return !is_null($qty);
            });
            if ($nonNullValues != [] && count($request->real_stock) > 0) {
                $stockParam = array();
                foreach ($request->real_stock as $key => $value) {
                    $projectStock = ProjectWiseStock::where('id', $request->id[$key])->first();

                    if (!is_null($request->real_stock[$key])) {

                        $diff = $projectStock->quantity - $request->real_stock[$key];
                        $stockParam[] = array(
                            'user_id' => Auth::id(),
                            'warehouse_id' => $projectStock->warehouse_id,
                            'sales_master_id' => (!empty($request->project_id)) ? $request->project_id : null,

                            'issue_type' => (!empty($request->project_id)) ? 'project' : 'installer',
                            'installer_id' => (!empty($request->installer_id)) ? $request->installer_id : null,

                            'type' => (!empty($request->item_id[$key])) ? 'Item' : 'ItemGroup',
                            'item_id' => (!empty($request->item_id[$key])) ? $request->item_id[$key] : 0,
                            'item_group_id' => (!empty($request->item_group_id[$key])) ? $request->item_group_id[$key] : 0,
                            'current_stock' => $projectStock->quantity,
                            'unit_id' => $projectStock->unit_id,
                            'real_stock' => number_format($request->real_stock[$key], 2, '.', ''),
                            'difference' => number_format($diff, 2, '.', ''),
                            'created_at' => Carbon::now(),
                        );

                        $projectStock->quantity = $request->real_stock[$key];
                        $projectStock->save();

                        $stockTransaction = new ProjectWiseStockHistory();
                        $stockTransaction->delivery_challan_meta_id = 0;
                        $stockTransaction->project_wise_stock_id = $projectStock->id;

                        if ($diff >= 0) {
                            $stockTransaction->quantity = abs($diff);
                            $stockTransaction->type = 'Debit';
                        } else {
                            $stockTransaction->quantity = abs($diff);
                            $stockTransaction->type = 'Credit';
                        }

                        $stockTransaction->remark = 'Project Wise Stock Adjustment';
                        $stockTransaction->save();
                    }
                }

                $result = ProjectWiseStockAdjust::insert($stockParam);

                if (!is_null($result)) {
                    DB::commit();
                    return response()->json(array('status_code' => 200, 'data' => route('project-stock-adjustments.index'), 'message' => 'Project wise stock adjustments successfully.'));
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

    public function projectDailyStockAdujst(Request $request, $id)
    {
        try {
            $data = ProjectWiseStockAdjust::with('item', 'project', 'itemGroup', 'unit', 'installer')
                ->where($request->column_name, $request->id)
                ->whereDate('created_at', $request->date)
                // ->whereRaw("DATE_FORMAT(created_at, '%Y-%m-%d %h:%i:%s') = ?", $request->date)
                ->get();

            if (count($data) > 0) {
                if (request()->ajax()) {
                    return DataTables::of($data)
                        ->addIndexColumn()
                        ->editColumn('item_name', function ($row) {
                            if ($row->type == "Item") {
                                return $row->item->name;
                            } else {
                                return getItemGropName($row, 1);
                            }
                        })
                        ->editColumn('project_name', function ($row) {
                            if ($row->issue_type == "project") {
                                return $row->project->consumer_name;
                            } else {
                                return '(Ins) ' . $row->installer->name . ' ' . $row->installer->last_name;
                            }
                        })
                        ->editColumn('created_at', function ($row) {
                            return $row->created_at->format('d-m-Y h:i:s A');
                        })
                        ->escapeColumns([])
                        ->make(true);
                } else {
                    return view('erp.project-stock-adjust.show', compact('data'));
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

    public function show(ProjectWiseStockAdjust $projectWiseStockAdjust)
    {
        //
    }

    public function edit(ProjectWiseStockAdjust $projectWiseStockAdjust)
    {
        //
    }

    public function update(Request $request, ProjectWiseStockAdjust $projectWiseStockAdjust)
    {
        //
    }

    public function destroy(ProjectWiseStockAdjust $projectWiseStockAdjust)
    {
        //
    }
}
