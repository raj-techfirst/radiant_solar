<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\CompanyProfile;
use App\Models\Estimate;
use App\Models\EstimateItem;
use App\Models\LeadMaster;
use App\Models\Product;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Gate;
use Yajra\DataTables\Facades\DataTables;

class EstimateController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:estimate-list|estimate-create|estimate-edit|estimate-delete', ['only' => ['index', 'store']]);
        $this->middleware('permission:estimate-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:estimate-edit', ['only' => ['edit', 'store']]);
        $this->middleware('permission:estimate-delete', ['only' => ['destroy']]);
    }

    public function index()
    {
        $where = "1 = 1";
        $company = CompanyProfile::where('user_id', Auth::id())->first();
        if ($company->user_type == 'O') {
            $where .= ' AND estimates.company_profile_id =' . $company->id;
        } else if ($company->user_type == 'M') {
            $where .= ' AND estimates.company_profile_id = ' . $company->parent_id . ' AND estimates.manager_id = ' . $company->id;
            // $where .= ' AND estimates.assign_id !=' . $company->parent_id . ' AND estimates.company_profile_id =' . $company->parent_id;
        } else {
            $where .= ' AND estimates.assign_id =' . $company->id . ' AND estimates.company_profile_id =' . $company->parent_id;
        }

        if (request()->ajax()) {
            return DataTables::of(Estimate::with('leadMaster')->whereRaw($where)->orderBy('id', 'DESC'))
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $html = '<td>';
                    $html .= '<a href="' . route('estimate-pdf', $row->id) . '" class="avatar bg-light p-50 m-0" data-bs-toggle="tooltip" data-placement="left" title="Download"><i class="fa fa-file-pdf text-dark"></i></a>';
                    if (Gate::allows('estimate-edit')) {
                        $html .= ' <a href="' . route('estimate.edit', $row->id) . '" class="avatar bg-light-info p-50 m-0 " data-bs-toggle="tooltip" data-placement="left" title="Edit"><i class="fa fa-edit"></i></a>';
                    }
                    if (Gate::allows('estimate-delete')) {
                        $html .= ' <a data-id="' . $row->id . '" href="javascript:void(0);" id="confirm-text" class="avatar bg-light-danger p-50 m-0 text-danger delete" data-bs-toggle="tooltip" data-placement="left" title="Delete"><i class="fa fa-trash"></i></a>';
                    }
                    $html .= '</td>';
                    return $html;
                })
                ->editColumn('estimate_date', function ($row) {
                    if (!is_null($row->estimate_date)) {
                        return date('d-m-Y', strtotime($row->estimate_date));
                    } else {
                        return '';
                    }
                })
                ->editColumn('expiry_date', function ($row) {
                    if (!is_null($row->expiry_date)) {
                        return date('d-m-Y', strtotime($row->expiry_date));
                    } else {
                        return '';
                    }
                })
                ->editColumn('name', function ($row) {
                    return $row->leadMaster->name . ' ' . $row->leadMaster->last_name;
                })
                ->rawColumns(['action', 'update_status', 'expiry_date', 'estimate_date', 'name'])
                ->make(true);
        } else {
            return view('admin.estimate.view_estimate');
        }
    }

    public function create()
    {
        $where = "1 = 1";
        $company = CompanyProfile::where('user_id', Auth::id())->first();
        // dd($company);
        if ($company->user_type == 'O') {
            $id = $company->id;
            $where .= ' AND lead_masters.company_profile_id =' . $company->id;
        } else if ($company->user_type == 'M') {
            $id = $company->parent_id;
            // $where .= ' AND lead_masters.assign_id !=' . $company->parent_id . ' AND lead_masters.company_profile_id =' . $company->parent_id;
            $where .= ' AND lead_masters.company_profile_id = ' . $company->parent_id . ' AND lead_masters.manager_id = ' . $company->id;
        } else {
            $id = $company->parent_id;
            $where .= ' AND lead_masters.assign_id =' . $company->id . ' AND lead_masters.company_profile_id =' . $company->parent_id;
        }
        $category = Category::where('user_id', Auth::id())->get();
        $product = Product::where('company_profile_id', $id)->get();
        $unit = Unit::get();
        $leadMaster = LeadMaster::whereRaw($where)->where('status', '!=', '2')->get();
        $companyProfile = CompanyProfile::with('user', 'state', 'city')->where('id', $id)->first();
        return view('admin.estimate.add_estimate', compact('leadMaster', 'company','category', 'unit', 'product', 'companyProfile'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'lead_id' => 'required',
            'estimate_title' => 'required',
            'estimate_date' => 'required',
            'expiry_date' => 'required',
        ], [
            'lead_id.required' => 'Select client',
            'estimate_title.required' => 'Enter estimate title',
            'estimate_date.required' => 'Enter estimate date',
            'expiry_date.required' => 'Enter expiry date',
        ]);
        if ($validator->fails()) {
            $response = ['status' => false, 'message' => 'Please input proper data.', 'errors' => $validator->errors()];
            return response()->json($response);
        }
        DB::beginTransaction();
        try {
            if (!is_null($request->estimate_id)) {
                $estimate = Estimate::where('id', $request->estimate_id)->first();
                $response = ['data' => route('estimate.index'), 'status' => true, 'message' => ' Estimate updated successfully.'];
            } else {
                $estimate = new Estimate();
                $response = ['data' => route('estimate.index'), 'status' => true, 'message' => ' Estimate added successfully.'];
            }
            $companyFind = CompanyProfile::where('user_id', Auth::id())->first();
            if ($companyFind->user_type == 'O') {
                $id = $companyFind->id;
            } else {
                $id = $companyFind->parent_id;
            }
            $leadMaster = LeadMaster::where('id', $request->lead_id)->first();
            $estimate->user_id = Auth::id();
            $estimate->lead_master_id = $request->lead_id;
            $estimate->company_profile_id = $id;
            $estimate->assign_id = $leadMaster->assign_id;
            $estimate->manager_id = $leadMaster->manager_id;
            $estimate->estimate_title = $request->estimate_title;
            $estimate->remark = $request->remark;
            if ($request->estimate_date) {
                $estimate->estimate_date = date("Y-m-d", strtotime($request->estimate_date));
            } else {
                $estimate->estimate_date = null;
            }
            if ($request->expiry_date) {
                $estimate->expiry_date = date("Y-m-d", strtotime($request->expiry_date));
            } else {
                $estimate->expiry_date = null;
            }
            $total_amount = 0;
            if (count($request->invoice) > 0) {
                foreach ($request->invoice as $key => $value) {
                    // if (!is_null($value['unit_id'])  && !is_null($value['quantity']) &&  !is_null($value['rate']) && !is_null($value['product_id']) && $value['quantity'] > 0 && $value['rate'] > 0) {
                        if ( !is_null($value['quantity']) &&  !is_null($value['rate']) && !is_null($value['product_id']) && $value['quantity'] > 0 && $value['rate'] > 0) {
                        $total_amount += $value['rate'] * $value['quantity'];
                    } else {
                        $response = ['status' => false, 'message' => 'Please input proper data.'];
                        return response()->json($response);
                    }
                }
            }
            if(!is_null($estimate->discount)){
                $estimate->discount = $request->discount;
            }else{
                $estimate->discount = '0';
            }
            
            $estimate->subtotal = $total_amount;
            $estimate->total = ($total_amount - ($total_amount * $request->discount / 100));
            $result = $estimate->save();
            DB::commit();
            if (!is_null($result)) {
                foreach ($request->invoice as $key => $value) {
                    if (isset($value['estimateItem_id']) && !is_null($value['estimateItem_id'])) {
                        $estimateitem = EstimateItem::where('id', $value['estimateItem_id'])->first();
                    } else {
                        $estimateitem = new EstimateItem();
                    }
                    $estimateitem->estimate_id = $estimate->id;
                    $estimateitem->category_id = $value['category_id'];
                    $estimateitem->product_id = $value['product_id'];
                    if(!is_null($value['unit_id'])){
                        $estimateitem->unit_id = $value['unit_id'];
                    }else{
                        $estimateitem->unit_id = 0;
                    }
                    $estimateitem->quantity = $value['quantity'];
                    $estimateitem->rate = $value['rate'];
                    $estimateitem->total = $value['rate'] * $value['quantity'];
                    $estimateitem->save();
                }
                return response()->json($response);
            } else {
                $response = ['status' => false, 'server_error' => 'Something went wrong. Please try again.'];
                return response()->json($response);
            }
        } catch (\Exception $e) {
            dd($e);
            DB::rollback();
            $response = ['status' => false, 'server_error' => 'Something went wrong. Please try again.'];
            return response()->json($response, 200, [], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
        }
    }

    public function show($id)
    {
        $estimate = LeadMaster::with('state', 'city')->where('id', $id)->first();
        if (!is_null($estimate)) {
            return response()->json($estimate);
        } else {
            return abort(404);
        }
    }

    public function edit($id)
    {
        $estimate = Estimate::with('estimateItem','company')->where('id', $id)->first();
        if (!is_null($estimate)) {
            $where = "1 = 1";
            $company = CompanyProfile::where('user_id', Auth::id())->first();
            if ($company->user_type == 'O') {
                $ids = $company->id;
                $where .= ' AND lead_masters.company_profile_id =' . $company->id;
            } else if ($company->user_type == 'M') {
                $ids = $company->parent_id;
                $where .= ' AND lead_masters.company_profile_id = ' . $company->parent_id . ' AND lead_masters.manager_id = ' . $company->id;
                // $where .= ' AND lead_masters.assign_id !=' . $company->parent_id . ' AND lead_masters.company_profile_id =' . $company->parent_id;
            } else {
                $ids = $company->parent_id;
                $where .= ' AND lead_masters.assign_id =' . $company->id . ' AND lead_masters.company_profile_id =' . $company->parent_id;
            }
            $category = Category::where('user_id', Auth::id())->get();
            $product = Product::where('company_profile_id', $ids)->get();
            $unit = Unit::get();
            $leadMaster = LeadMaster::whereRaw($where)->where('status', '!=', '2')->get();
            $companyProfile = CompanyProfile::with('user', 'state', 'city')->where('id', $ids)->first();
            return view('admin.estimate.add_estimate', compact('estimate', 'company','category','leadMaster', 'unit', 'product', 'companyProfile'));
        } else {
            return abort(404);
        }
    }

    public function update(Request $request, $id)
    {
        $category = Category::where('user_id', Auth::id())->get();
        $product = Product::where('id', $id)->first();
        if (!is_null($product->product_price)) {
            $products['rate'] = $product->product_price;
        } else {
            $products['rate'] = 0;
        }
        return response()->json($products);
    }

    public function estimatePDF($id)
    {
        $estimate = Estimate::with('leadMaster', 'estimateItem', 'estimateItem.product', 'estimateItem.unit', 'company', 'user', 'assign')->where('id', $id)->first();
        if (!is_null($estimate)) {
            $data = [
                'title' => 'Estimate',
                'estimate' => $estimate,
            ];
            $name = $estimate->leadMaster->name;
            $pdf = Pdf::loadView('admin.estimate.pdf_estimate', $data);
            return $pdf->download($name . '-estimate.pdf');
        } else {
            return abort(404);
        }
    }

    public function removeItem(Request $request)
    {
        $estimateitem = EstimateItem::find($request->id);
        if (!is_null($estimateitem)) {
            $estimate = Estimate::where('id', $estimateitem->estimate_id)->first();
            $total = $estimateitem->total;
            $subtotal = $estimate->subtotal - $total;
            $estimate->subtotal = $subtotal;
            $estimate->total = ($subtotal - ($subtotal * $estimate->discount / 100));
            $estimate->save();
            $estimateitem->delete();
            $response = ['status' => true, 'message' => ' Remove successfully.'];
            return response()->json($response);
        } else {
            $response = ['status' => false, 'message' => 'This record does not exist.'];
            return response()->json($response);
        }
    }

    public function destroy($id)
    {
        try {
            $estimate = Estimate::where('id', $id)->first();
            $estimateitem = EstimateItem::where('estimate_id', $estimate->id)->get();
            if (count($estimateitem) > 0) {
                foreach ($estimateitem as $item) {
                    $item->delete();
                }
            }
            $estimate->delete();
            $response = ['status' => true, 'message' => ' Deleted successfully.'];
            return response()->json($response);
        } catch (\Exception $e) {
            $response = ['status' => false, 'server_error' => 'Something went wrong. Please try again.'];
            return response()->json($response);
        }
    }
}
