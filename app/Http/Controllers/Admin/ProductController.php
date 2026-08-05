<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\CompanyProfile;
use App\Models\LeadMaster;
use App\Models\Product;
use App\Models\Task;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:product-list|product-add|product-edit|product-delete', ['only' => ['index']]);
        $this->middleware('permission:product-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:product-edit', ['only' => ['edit']]);
        $this->middleware('permission:product-delete', ['only' => ['destroy']]);
    }

    public function index()
    {
        if (request()->ajax()) {
            return DataTables::of(Product::with('unit', 'category'))
                ->addIndexColumn()
                ->addColumn('cat_name', function ($row) {
                    return ($row->category_id != 0) ? $row->category->category_name : '-';
                })
                ->addColumn('action', function ($row) {
                    $html = '<td>';
                    if (Gate::check('product-edit')) {
                        $html .= '<a data-id="' . $row->id . '" href="javascript:void(0);" class="avatar bg-light-info p-50 m-0 edit" data-bs-toggle="tooltip" data-placement="left" title="Edit"><i class="fa fa-edit"></i></a>';
                    }
                    if (Gate::check('product-delete')) {
                        $html .= ' <a data-id="' . $row->id . '" href="javascript:void(0);" class="avatar bg-light-danger p-50 m-0 delete" data-bs-toggle="tooltip" data-placement="left" title="Delete"><i class="fa fa-trash"></i></a>';
                    }
                    $html .= '</td>';
                    return $html;
                })
                ->escapeColumns([])
                ->make(true);
        } else {
            $units = Unit::get();
            $categories = Category::get();
            return view('admin.product.index', compact('units', 'categories'));
        }
    }

    public function create()
    {
        // return view('admin.product.index');
    }

    public function store(Request $request)
    {
        if (!is_null($request->product_id)) {
            $name = [
                'required',
                Rule::unique('products')->where(function ($query) use ($request) {
                    return $query->where('deleted_at', null);
                })->ignore($request->product_id),
            ];
        } else {
            $name = [
                'required',
                Rule::unique('products')->where(function ($query) use ($request) {
                    return $query->where('deleted_at', null);
                }),
            ];
        }
        $validator = Validator::make($request->all(), [
            'name' => $name,
            // 'item_code' => 'required',
            'gst_rate' => 'required',
        ], [
            'name.required' => 'Enter Item Name',
            // 'item_code.required' => 'Enter Item Code',
            'gst_rate.required' => 'Enter GST Rate',
        ]);

        if ($validator->fails()) {
            $response = ['status' => false, 'message' => 'Please input proper data.', 'errors' => $validator->errors()];
            return response()->json($response);
        }
        DB::beginTransaction();
        try {
            if (!is_null($request->product_id)) {
                $product = Product::where('id', $request->product_id)->first();
                $response = ['data' => route('product.index'), 'status' => true, 'message' => 'Item updated successfully.'];
            } else {
                $product = new Product();
                $response = ['data' => route('product.index'), 'status' => true, 'message' => 'Item added successfully.'];
            }
            $product->user_id = Auth::id();
            $product->category_id = $request->category_id;
            $product->name = $request->name;
            $product->item_code = $request->item_code;
            $product->hsn_code = $request->hsn_code;
            $product->gst_rate = $request->gst_rate;
            $product->unit_id = $request->unit_id;
            $product->moq_level = $request->moq_level;
            $result = $product->save();
            DB::commit();
            if (!is_null($result)) {
                return response()->json($response);
            } else {
                $response = ['status' => false, 'server_error' => 'Something went wrong. Please try again.'];
                return response()->json($response);
            }
        } catch (\Exception $e) {
            DB::rollback();
            $response = ['status' => false, 'server_error' => 'Something went wrong. Please try again.'];
            return response()->json($response);
        }
    }

    public function show(Product $product)
    {
        //
    }

    public function edit($id)
    {
        $product = Product::where('id', $id)->first();
        if (!is_null($product)) {
            $res = array('msg_type' => 'success', 'msg_title' => 'Success!', 'result' => $product);
            header('Content-Type:application/json');
            echo json_encode($res);
        } else {
            return abort(404);
        }
    }

    public function update(Request $request, Product $product)
    {
        //
    }

    public function destroy($id)
    {
        try {
            $product = Product::where('id', $id)->first();
            $product->delete();
            $response = ['status' => true, 'message' => ' Deleted successfully.'];
            return response()->json($response);
        } catch (\Exception $e) {
            $response = ['status' => false, 'server_error' => 'Something went wrong. Please try again.'];
            return response()->json($response);
        }
    }
}
