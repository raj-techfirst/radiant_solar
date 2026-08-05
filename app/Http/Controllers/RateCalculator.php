<?php

namespace App\Http\Controllers;

use App\Models\erp\BOM;
use App\Models\erp\BOMMeta;
use App\Models\RateCalculatorMeta;
use App\Models\RateCalculatorModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class RateCalculator extends Controller
{

    function __construct()
    {
        $this->middleware('permission:rate-calculator-list', ['only' => ['index']]);
        $this->middleware('permission:rate-calculator-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:rate-calculator-edit', ['only' => ['edit', 'store']]);
        $this->middleware('permission:rate-calculator-delete', ['only' => ['destroy']]);
    }

    public function index()
    {
        if (request()->ajax()) {
            return DataTables::of(RateCalculatorModel::orderBy('id', 'DESC'))
                ->addIndexColumn()
                ->filter(function ($query) {
                    if (request()->input('from_date') != "" && request()->input('to_date') == '') {
                        $query->where('created_at', '>=', date('Y-m-d 00:00:00', strtotime(request()->input('from_date'))));
                        $query->where('created_at', '<=', date('Y-m-d 23:59:59'));
                    }
                    if (request()->input('from_date') != "" && request()->input('to_date') != '') {
                        $query->where('created_at', '>=', date('Y-m-d 00:00:00', strtotime(request()->input('from_date'))));
                        $query->where('created_at', '<=', date('Y-m-d 23:59:59', strtotime(request()->input('to_date'))));
                    }
                    if (request()->input('from_date') == "" && request()->input('to_date') != '') {
                        $query->where('created_at', '<=', date('Y-m-d 23:59:59', strtotime(request()->input('to_date'))));
                    }
                    if (request()->input('name') != "") {
                        $query->where(function ($q) {
                            $name = request()->input('name');
                            $q->where('name', 'like', '%' . $name . '%')
                                ->orWhere('mobile', 'like', '%' . $name . '%');
                        });
                    }
                })
                ->editColumn('created_at', function ($row) {
                    if (!is_null($row->created_at)) {
                        return date('d-m-Y', strtotime($row->created_at));
                    } else {
                        return '';
                    }
                })
                ->editColumn('name', function ($row) {
                    return $row->name .'<br/>'.$row->mobile;
                 })
                /*->editColumn('totalRate', function ($row) {
                   return '₹ '.number_format($row->totalRate,2) .' <br/><span class="text-success"><i>₹ '.number_format($row->profit_totalRate,2).'</i></span>';
                })
                ->editColumn('gst_amount', function ($row) {
                    return '₹ '.number_format($row->gst_amount,2) .' <br/><span class="text-success"><i>₹ '.number_format($row->profit_gst_amount,2).'</i></span>';
                 })
                 ->editColumn('total_amount', function ($row) {
                    return '₹ '.number_format($row->total_amount,2) .' <br/><span class="text-success"><i>₹ '.number_format($row->profit_total_amount,2).'</i></span>';
                 })
                 ->editColumn('per_watt', function ($row) {
                    return '₹ '.number_format($row->per_watt,2) .' <br/><span class="text-success"><i>₹ '.number_format($row->profit_per_watt,2).'</i></span>';
                 }) */

                 ->editColumn('totalRate', function ($row) {
                    return '₹ '.number_format($row->totalRate,2);
                 })
                 ->editColumn('gst_amount', function ($row) {
                     return '₹ '.number_format($row->gst_amount,2);
                  })
                  ->editColumn('total_amount', function ($row) {
                     return '₹ '.number_format($row->total_amount,2);
                  })
                  ->editColumn('per_watt', function ($row) {
                     return '₹ '.number_format($row->per_watt,2);
                  })

                ->addColumn('action', function ($row) {
                    $html = '<div>';
                    $html = '<a data-id="' . $row->id . '" href="javascript:void(0)" class="view avatar bg-light-success p-50 m-0" data-bs-toggle="tooltip" data-placement="left" title="View"><i class="fa fa-eye"></i></a>';
                    if (Gate::allows('rate-calculator-edit')) {
                        $html .= ' <a href="' . route('rate-calculator.edit', $row->id) . '" class="avatar bg-light-info p-50 m-0" data-bs-toggle="tooltip" data-placement="left" title="Edit"><i class="fa fa-edit"></i></a>';
                    }
                    if (Gate::allows('rate-calculator-delete')) {
                        $html .= ' <a data-id="' . $row->id . '" href="javascript:void(0);" class="delete avatar bg-light-danger p-50 m-0" data-bs-toggle="tooltip" data-placement="left" title="Delete"><i class="fa fa-trash"></i></a>';
                    }
                    if (Gate::allows('rate-calculator-create')) {
                        $html .= ' <a href="' . route('rate-calculator-clone', $row->id) . '" class="avatar bg-light-secondary p-50 m-0" data-bs-toggle="tooltip" data-placement="left" title="Copy As New"><i class="fa fa-copy"></i></a>';
                    }
                    $html .= '</div>';
                    return $html;
                })
                ->escapeColumns([])
                ->make(true);
        } else {
            return view('admin.rate-calculator.index');
        }
    }
    public function create()
    {
        $boms = BOM::get();
        return view('admin.rate-calculator.create', compact('boms'));
    }
    public function store(Request $request)
    {
        $name = [
            'required',
            Rule::unique('rate_calculators')->where(function ($query) use ($request) {
                return $query->where('deleted_at', null);
            }),
        ];

        $validator = Validator::make($request->all(), [
            'name' => $name,
            'res_pv_capacity_kw' => 'required',
        ], [
            'name.required' => 'Enter name',
            'res_pv_capacity_kw.required' => 'Enter PV Capacity KW',
        ]);

        if ($validator->fails()) {
            $response = ['status' => false, 'message' => 'Please input proper data.', 'errors' => $validator->errors()];
            return response()->json($response);
        }

        DB::beginTransaction();
        try {
            if (count($request->item) > 0) {

                $rateCalculator = new RateCalculatorModel();
                $rateCalculator->user_id = Auth::id();
                $rateCalculator->name = $request->name;
                $rateCalculator->mobile = $request->mobile;
                $rateCalculator->pv_capacity_kw = $request->res_pv_capacity_kw;

                $rateCalculator->gst_amount = 0;
                $rateCalculator->total_amount = 0;
                $rateCalculator->per_watt = 0;

                $rateCalculator->remarks = $request->remarks;
                $rateCalculator->save();

                $subtotal = $gst = $total = $per_watt = 0;
                $saleSubtotal = $salegst = $saletotal = $saleper_watt = 0;
                foreach ($request->item as  $key => $value):
                    $rateCalculatorMeta = new RateCalculatorMeta();
                    $rateCalculatorMeta->rate_calculator_id = $rateCalculator->id;
                    $rateCalculatorMeta->item = $value;
                    $rateCalculatorMeta->category = $request->category[$key];
                    $rateCalculatorMeta->brand = $request->brand[$key] ?? '';
                    $rateCalculatorMeta->type = $request->type[$key] ?? '';
                    $rateCalculatorMeta->unit = $request->unit[$key] ?? '';
                    $rateCalculatorMeta->wpk = $request->wpk[$key] ?? 0;
                    $rateCalculatorMeta->qty = $request->qty[$key] ?? 0;
                    $rateCalculatorMeta->rate = $request->rate[$key] ?? 0;
                    $rateCalculatorMeta->subtotal = $request->subtotal[$key] ?? 0;
                    $rateCalculatorMeta->taxRate = $request->taxRate[$key] ?? 0;
                    $rateCalculatorMeta->totalTax = $request->totalTax[$key] ?? 0;
                    $rateCalculatorMeta->total = $request->total[$key] ?? 0;
                    $rateCalculatorMeta->per_watt = $request->per_watt[$key] ?? 0;
                    $rateCalculatorMeta->is_special = $request->is_special[$key] ?? '';
                    $rateCalculatorMeta->autoKw = $request->autoKw[$key] ?? '';
                    $rateCalculatorMeta->save();

                    if ($request->category[$key] == "sales") {
                        $saleSubtotal += $request->subtotal[$key];
                        $salegst += $request->totalTax[$key];
                        $saletotal += $request->total[$key];
                        $saleper_watt += $request->per_watt[$key];
                    } else {
                        $subtotal += $request->subtotal[$key];
                        $gst += $request->totalTax[$key];
                        $total += $request->total[$key];
                        $per_watt += $request->per_watt[$key];
                    }
                endforeach;

                $rateCalculator = RateCalculatorModel::where('id', $rateCalculator->id)->first();
                $rateCalculator->totalRate = $subtotal;
                $rateCalculator->gst_amount = $gst;
                $rateCalculator->total_amount = $total;
                $rateCalculator->per_watt = $per_watt;

                $rateCalculator->profit_totalRate = $saleSubtotal - $subtotal;
                $rateCalculator->profit_gst_amount = $salegst - $gst;
                $rateCalculator->profit_total_amount = $saletotal - $total;
                $rateCalculator->profit_per_watt = $saleper_watt - $per_watt;

                $rateCalculator->save();

                if (!is_null($rateCalculator)) {
                    DB::commit();
                    $response = ['data' => route('rate-calculator.index'), 'status' => true, 'message' => 'Rate Calculator added successfully.'];
                    return response()->json($response);
                } else {
                    DB::rollback();
                    $response = ['status' => false, 'server_error' => 'Something went wrong. Please try again.'];
                    return response()->json($response);
                }
            }
        } catch (\Exception $e) {
            DB::rollback();
            $response = ['status' => false, 'server_error' => 'Something went wrong. Please try again.'];
            return response()->json($response);
        }
    }
    public function show($id)
    {
        $rateCalculator = RateCalculatorModel::where('id', $id)->first();
        $rateCalculatorMeta = RateCalculatorMeta::where('rate_calculator_id', $id)->get();
        if (!is_null($rateCalculator)) {
            $data['html'] = view('admin.rate-calculator.view', compact('rateCalculator', 'rateCalculatorMeta'))->render();
            return response()->json($data);
        } else {
            return abort(404);
        }
    }
    public function edit($id)
    {
        try {
            $rateCalculatorModel = RateCalculatorModel::where('id', $id)->first();
            if ($rateCalculatorModel) {
                $rateCalculatorMeta = RateCalculatorMeta::where('rate_calculator_id', $id)->get();
                return view('admin.rate-calculator.edit', compact('rateCalculatorModel', 'rateCalculatorMeta'));
            } else {
                return abort(404);
            }
        } catch (\Exception $e) {
        }
    }

    public function update(Request $request, $id)
    {

      
        $name = [
            'required',
            Rule::unique('rate_calculators')->where(function ($query) use ($request) {
                return $query->where('deleted_at', null);
            })->ignore($id),
        ];
        
        $validator = Validator::make($request->all(), [
            'name' => $name,
            'res_pv_capacity_kw' => 'required',
        ], [
            'name.required' => 'Enter name',
            'res_pv_capacity_kw.required' => 'Enter PV Capacity KW',
        ]);

        if ($validator->fails()) {
            $response = ['status' => false, 'message' => 'Please input proper data.', 'errors' => $validator->errors()];
            return response()->json($response);
        }

        DB::beginTransaction();
        try {
            if (count($request->item) > 0) {


                $rateCalculator = RateCalculatorModel::where('id', $id)->delete();

                $rateCalculator = new RateCalculatorModel();
                $rateCalculator->user_id = Auth::id();
                $rateCalculator->name = $request->name;
                $rateCalculator->mobile = $request->mobile;
                $rateCalculator->pv_capacity_kw = $request->res_pv_capacity_kw;

                $rateCalculator->gst_amount = 0;
                $rateCalculator->total_amount = 0;
                $rateCalculator->per_watt = 0;

                $rateCalculator->remarks = $request->remarks;
                $rateCalculator->save();

                $subtotal = $gst = $total = $per_watt = 0;
                $saleSubtotal = $salegst = $saletotal = $saleper_watt = 0;
                foreach ($request->item as  $key => $value):
                    $rateCalculatorMeta = new RateCalculatorMeta();
                    $rateCalculatorMeta->rate_calculator_id = $rateCalculator->id;
                    $rateCalculatorMeta->item = $value;
                    $rateCalculatorMeta->category = $request->category[$key];
                    $rateCalculatorMeta->brand = $request->brand[$key] ?? '';
                    $rateCalculatorMeta->type = $request->type[$key] ?? '';
                    $rateCalculatorMeta->unit = $request->unit[$key] ?? '';
                    $rateCalculatorMeta->wpk = $request->wpk[$key] ?? 0;
                    $rateCalculatorMeta->qty = $request->qty[$key] ?? 0;
                    $rateCalculatorMeta->rate = $request->rate[$key] ?? 0;
                    $rateCalculatorMeta->subtotal = $request->subtotal[$key] ?? 0;
                    $rateCalculatorMeta->taxRate = $request->taxRate[$key] ?? 0;
                    $rateCalculatorMeta->totalTax = $request->totalTax[$key] ?? 0;
                    $rateCalculatorMeta->total = $request->total[$key] ?? 0;
                    $rateCalculatorMeta->per_watt = $request->per_watt[$key] ?? 0;
                    $rateCalculatorMeta->is_special = $request->is_special[$key] ?? '';
                    $rateCalculatorMeta->autoKw = $request->autoKw[$key] ?? '';
                    $rateCalculatorMeta->save();

                    if ($request->category[$key] == "sales") {
                        $saleSubtotal += $request->subtotal[$key];
                        $salegst += $request->totalTax[$key];
                        $saletotal += $request->total[$key];
                        $saleper_watt += $request->per_watt[$key];
                    } else {
                        $subtotal += $request->subtotal[$key];
                        $gst += $request->totalTax[$key];
                        $total += $request->total[$key];
                        $per_watt += $request->per_watt[$key];
                    }
                endforeach;

                $rateCalculator = RateCalculatorModel::where('id', $rateCalculator->id)->first();
                $rateCalculator->totalRate = $subtotal;
                $rateCalculator->gst_amount = $gst;
                $rateCalculator->total_amount = $total;
                $rateCalculator->per_watt = $per_watt;

                $rateCalculator->profit_totalRate = $saleSubtotal - $subtotal;
                $rateCalculator->profit_gst_amount = $salegst - $gst;
                $rateCalculator->profit_total_amount = $saletotal - $total;
                $rateCalculator->profit_per_watt = $saleper_watt - $per_watt;

                $rateCalculator->save();

                if (!is_null($rateCalculator)) {
                    DB::commit();
                    $response = ['data' => route('rate-calculator.index'), 'status' => true, 'message' => 'Rate Calculator updated successfully.'];
                    return response()->json($response);
                } else {
                    DB::rollback();
                    $response = ['status' => false, 'server_error' => 'Something went wrong. Please try again.'];
                    return response()->json($response);
                }
            }
        } catch (\Exception $e) {
            DB::rollback();
            $response = ['status' => false, 'server_error' => 'Something went wrong. Please try again.'];
            return response()->json($response);
        }
    }
    public function update_old(Request $request, $id)
    {
        $name = [
            'required',
            Rule::unique('rate_calculators')->where(function ($query) use ($request) {
                return $query->where('deleted_at', null);
            })->ignore($id),
        ];

        $validator = Validator::make($request->all(), [
            'name' => $name,
            'res_pv_capacity_kw' => 'required',
        ], [
            'name.required' => 'Enter name',
            'res_pv_capacity_kw.required' => 'Enter PV Capacity KW',
        ]);

        if ($validator->fails()) {
            $response = ['status' => false, 'message' => 'Please input proper data.', 'errors' => $validator->errors()];
            return response()->json($response);
        }

        DB::beginTransaction();
        try {
            if (count($request->item) > 0) {

                $rateCalculator = RateCalculatorModel::where('id', $id)->first();

                $rateCalculator->user_id = Auth::id();
                $rateCalculator->name = $request->name;
                $rateCalculator->mobile = $request->mobile;
                $rateCalculator->pv_capacity_kw = $request->res_pv_capacity_kw;

                $rateCalculator->gst_amount = 0;
                $rateCalculator->total_amount = 0;
                $rateCalculator->per_watt = 0;

                $rateCalculator->remarks = $request->remarks;
                $rateCalculator->save();

                $subtotal = $gst = $total = $per_watt = 0;
                $saleSubtotal = $salegst = $saletotal = $saleper_watt = 0;
                foreach ($request->item as  $key => $value):
                    $rateCalculatorMeta = RateCalculatorMeta::where('rate_calculator_id', $rateCalculator->id)->where('item', $value)->first();
                    if (is_null($rateCalculatorMeta)) {
                        $rateCalculatorMeta = new RateCalculatorMeta();
                    }

                    $rateCalculatorMeta->rate_calculator_id = $rateCalculator->id;
                    $rateCalculatorMeta->item = $value;
                    $rateCalculatorMeta->category = $request->category[$key];
                    $rateCalculatorMeta->brand = $request->brand[$key] ?? '';
                    $rateCalculatorMeta->type = $request->type[$key] ?? '';
                    $rateCalculatorMeta->unit = $request->unit[$key] ?? '';
                    $rateCalculatorMeta->wpk = $request->wpk[$key] ?? 0;
                    $rateCalculatorMeta->qty = $request->qty[$key] ?? 0;
                    $rateCalculatorMeta->rate = $request->rate[$key] ?? 0;
                    $rateCalculatorMeta->subtotal = $request->subtotal[$key] ?? 0;
                    $rateCalculatorMeta->taxRate = $request->taxRate[$key] ?? 0;
                    $rateCalculatorMeta->totalTax = $request->totalTax[$key] ?? 0;
                    $rateCalculatorMeta->total = $request->total[$key] ?? 0;
                    $rateCalculatorMeta->per_watt = $request->per_watt[$key] ?? 0;
                    $rateCalculatorMeta->is_special = $request->is_special[$key] ?? '';
                    $rateCalculatorMeta->autoKw = $request->autoKw[$key] ?? '';
                    $rateCalculatorMeta->save();

                    if ($request->category[$key] == "sales") {
                        $saleSubtotal += $request->subtotal[$key];
                        $salegst += $request->totalTax[$key];
                        $saletotal += $request->total[$key];
                        $saleper_watt += $request->per_watt[$key];
                    } else {
                        $subtotal += $request->subtotal[$key];
                        $gst += $request->totalTax[$key];
                        $total += $request->total[$key];
                        $per_watt += $request->per_watt[$key];
                    }
                endforeach;

                $rateCalculator = RateCalculatorModel::where('id', $rateCalculator->id)->first();
                $rateCalculator->totalRate = $subtotal;
                $rateCalculator->gst_amount = $gst;
                $rateCalculator->total_amount = $total;
                $rateCalculator->per_watt = $per_watt;

                $rateCalculator->profit_totalRate = $saleSubtotal - $subtotal;

                $rateCalculator->profit_gst_amount = $salegst - $gst;
                $rateCalculator->profit_total_amount = $saletotal - $total;
                $rateCalculator->profit_per_watt = $saleper_watt - $per_watt;

                $rateCalculator->save();

                if (!is_null($rateCalculator)) {
                    DB::commit();
                    $response = ['data' => route('rate-calculator.index'), 'status' => true, 'message' => ' Rate Calculator updated successfully.'];
                    return response()->json($response);
                } else {
                    DB::rollback();
                    $response = ['status' => false, 'server_error' => 'Something went wrong. Please try again.'];
                    return response()->json($response);
                }
            }
        } catch (\Exception $e) {
            DB::rollback();
            $response = ['status' => false, 'server_error' => 'Something went wrong. Please try again.'];
            return response()->json($response);
        }
    }

    public function destroy($id)
    {
        try {
            $rateCalculatorModel = RateCalculatorModel::where('id', $id)->first();
            if ($rateCalculatorModel) {
                RateCalculatorMeta::where('rate_calculator_id', $id)->delete();
                $rateCalculatorModel->delete();
                $response = ['status' => true, 'message' => ' Deleted successfully.'];
            } else {
                $response = ['status' => false, 'message' => ' Also used this Calculator.'];
            }
            return response()->json($response);
        } catch (\Exception $e) {
            $response = ['status' => false, 'server_error' => 'Something went wrong. Please try again.'];
            return response()->json($response);
        }
    }

    public function getBomData(Request $request)
    {
        try {
            $otheritemsOriginal = [
                [
                    'item' => 'Registration Charges',
                    'wpk' => '',
                    'qty' => 1,
                    'rate' => 13500,
                    'taxRate' => 18,
                    'is_special' => '',
                    'is_auto_kw' => '',
                    'category' => 'Others & Documentation Charge',
                    'is_bom' => 0
                ],
                [
                    'item' => 'Meter Charge Pay To DISCOM Portal',
                    'wpk' => '',
                    'qty' => 1,
                    'rate' => 0,
                    'taxRate' => 0,
                    'is_special' => '',
                    'is_auto_kw' => '',
                    'category' => 'Others & Documentation Charge',
                    'is_bom' => 0
                ],
                [
                    'item' => 'Installation Charges',
                    'wpk' => '',
                    'qty' => 0,
                    'rate' => 2000,
                    'taxRate' => 0,
                    'is_special' => '',
                    'is_auto_kw' => 1,
                    'category' => 'Others & Documentation Charge',
                    'is_bom' => 0
                ],
                [
                    'item' => 'Commission',
                    'wpk' => '',
                    'qty' => 0,
                    'rate' => 2500,
                    'taxRate' => 0,
                    'is_special' => '',
                    'is_auto_kw' => 1,
                    'category' => 'Others & Documentation Charge',
                    'is_bom' => 0
                ],
                [
                    'item' => 'Transportation Charges',
                    'wpk' => '',
                    'qty' => 0,
                    'rate' => 1000,
                    'taxRate' => 0,
                    'is_special' => '',
                    'is_auto_kw' => 1,
                    'category' => 'Others & Documentation Charge',
                    'is_bom' => 0
                ],
                [
                    'item' => 'Agreement on Stamp Paper',
                    'wpk' => '',
                    'qty' => 1,
                    'rate' => 300,
                    'taxRate' => 0,
                    'is_special' => '',
                    'is_auto_kw' => '',
                    'category' => 'Others & Documentation Charge',
                    'is_bom' => 0
                ],
                [
                    'item' => 'Shadow Analysis Report With Drowing',
                    'wpk' => '',
                    'qty' => 1,
                    'rate' => 1500,
                    'taxRate' => 18,
                    'is_special' => '',
                    'is_auto_kw' => '',
                    'category' => 'Others & Documentation Charge',
                    'is_bom' => 0
                ],
                [
                    'item' => 'Stracture Engineer Certificate Charge',
                    'wpk' => '',
                    'qty' => 1,
                    'rate' => 5000,
                    'taxRate' => 18,
                    'is_special' => '',
                    'is_auto_kw' => '',
                    'category' => 'Others & Documentation Charge',
                    'is_bom' => 0
                ]
            ];


            $items = [];
            $bomItem = BOMMeta::with('product', 'product.category', 'itemGroup', 'unit')->where('boms_id', $request->id)->get();
            if ($bomItem->count() > 0) {
                foreach ($bomItem as $key => $value):
                    if ($value->type == "Item") {
                        $display_name = $value->product->name;
                        $unit = $value->product->unit->unit_name;
                        $items[$value->product->category->category_name][] = [
                            'item' => $display_name,
                            'brand' => '',
                            'type' => '',
                            'unit' => $unit,
                            'wpk' => '',
                            'qty' => $value->quantity ?? 0,
                            'rate' => getLastRate('item_id', $value->item_id),
                            'taxRate' => $value->product->gst_rate,
                            'is_special' => $value->product->category->category_name,
                            'is_auto_kw' => '',
                            'category' => $value->product->category->category_name,
                            'is_bom' => 1
                        ];
                    } else {

                        $unit = $value->itemGroup->unit->unit_name;
                        $rate = getLastRate('item_group_id', $value->item_group_id);
                        if($value->itemGroup->group_type == "panel")
                        {
                            $rate = number_format(($rate / $value->itemGroup->panel_watt->name),2,'.','');
                        }

                        $items[$value->itemGroup->group_type][] = [
                            'item' => getItemGropName($value, 1),
                            'brand' => '',
                            'type' => '',
                            'unit' => $unit,
                            'wpk' => ($value->itemGroup->group_type == "panel") ? $value->itemGroup->panel_watt->name : '',
                            'qty' => $value->quantity ?? 0,
                            'rate' => $rate,
                            'taxRate' => $value->itemGroup->gst_rate,
                            'is_special' => $value->itemGroup->group_type,
                            'is_auto_kw' => '',
                            'category' => $value->itemGroup->group_type,
                            'is_bom' => 1
                        ];
                    }
                endforeach;
            }
            $flattenedItems = [];
            foreach ($items as $category => $categoryItems) {
                $flattenedItems = array_merge($flattenedItems, $categoryItems);
            }

            $response = ['status' => true, 'data' => ['otheritemsOriginal' => $otheritemsOriginal, 'itemsOriginal' => $flattenedItems]];
            return response()->json($response);
        } catch (\Exception $e) {
            $response = ['status' => false, 'server_error' => 'Something went wrong. Please try again.'];
            return response()->json($response);
        }
    }

    public function getEditData(Request $request)
    {
        try {
            $rateCalculatorModel = RateCalculatorModel::where('id', $request->id)->first();
            if ($rateCalculatorModel) {
                $flattenedItems = RateCalculatorMeta::select('item', 'brand', 'type', 'unit', 'wpk', 'qty', 'rate', 'taxRate', 'is_special', 'autoKw as is_auto_kw', 'category')->where('rate_calculator_id', $request->id)->where('category', '!=', 'Others & Documentation Charge')->where('category', '!=', 'sales')->get();
                $otheritemsOriginal = RateCalculatorMeta::select('item', 'brand', 'type', 'unit', 'wpk', 'qty', 'rate', 'taxRate', 'is_special', 'autoKw as is_auto_kw', 'category')->where('rate_calculator_id', $request->id)->where('category', 'Others & Documentation Charge')->get();
                $salesitems = RateCalculatorMeta::select('item', 'brand', 'type', 'unit', 'wpk', 'qty', 'rate', 'taxRate', 'is_special', 'autoKw as is_auto_kw', 'category')->where('rate_calculator_id', $request->id)->where('category', 'sales')->get();
                $response = ['status' => true, 'rateCalculatorModel' => $rateCalculatorModel, 'data' => ['otheritemsOriginal' => $otheritemsOriginal, 'itemsOriginal' => $flattenedItems, 'salesitems' => $salesitems,]];
                return response()->json($response);
            } else {
                $response = ['status' => false, 'message' => 'Do Data Found!'];
                return response()->json($response);
            }
        } catch (\Exception $e) {
            $response = ['status' => false, 'server_error' => 'Something went wrong. Please try again.'];
            return response()->json($response);
        }
    }

    public function clone($id)
    {
        try {
            $rateCalculatorModel = RateCalculatorModel::where('id', $id)->first();
            if ($rateCalculatorModel) {
                $rateCalculatorMeta = RateCalculatorMeta::where('rate_calculator_id', $id)->get();
                return view('admin.rate-calculator.clone', compact('rateCalculatorModel', 'rateCalculatorMeta'));
            } else {
                return abort(404);
            }
        } catch (\Exception $e) {
        }
    }
}
