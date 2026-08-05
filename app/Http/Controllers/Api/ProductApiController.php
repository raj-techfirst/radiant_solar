<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CompanyProfile;
use App\Models\LeadMaster;
use App\Models\Product;
use App\Models\Task;
use Exception;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ProductApiController extends Controller
{
    public function index()
    {
        try {
            $company = CompanyProfile::where('user_id', Auth::id())->first();
            if ($company->user_type == 'O') {
                $id = $company->id;
            } else if ($company->user_type == 'M') {
                $id = $company->parent_id;
            } else {
                $id = $company->parent_id;
            }

            $product = Product::select('id', 'company_profile_id', 'product_name', 'product_price', 'description')->where('company_profile_id', $id)->orderBy('id', 'DESC')->get();
            $response = ['status' => true, 'message' => 'Product List', 'product' => $product];
            return response($response, 200);
        } catch (Exception $e) {
            $response = ['status' => false, 'message' => 'Something went wrong. Please try again.'];
            return response($response, 500);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_name' => ['required', Rule::unique('products')->where(function ($query) use ($request) {
                if (!is_null($request->product_id)) {
                    return $query->where('user_id', Auth::id())->where('deleted_at', null)->where('id', '!=', $request->product_id);
                } else {
                    return $query->where('user_id', Auth::id())->where('deleted_at', null);
                }
            })],
        ], [
            'product_name.required' => 'Enter product',
            'product_name.unique' => 'The product has already been taken'
        ]);
        if ($validator->fails()) {
            $response = ['status' => false, 'message' => 'Please input proper data.', 'errors' => $validator->errors()];
            return response($response);
        }
        DB::beginTransaction();
        try {
            if (!is_null($request->product_id)) {
                $product = Product::where('id', $request->product_id)->first();
                $response = ['status' => true, 'message' => 'Product updated successfully.'];
            } else {
                $product = new Product();
                $response = ['status' => true, 'message' => 'Product added successfully.'];
            }
            $companyProfile = CompanyProfile::where('user_id', Auth::id())->first();
            $product->user_id = Auth::id();
            $product->company_profile_id = $companyProfile->id;
            $product->product_name = $request->product_name;
            $product->product_price = $request->product_price;
            $product->description = $request->description;
            $result = $product->save();
            DB::commit();
            if (!is_null($result)) {
                return response($response);
            } else {
                $response = ['status' => false, 'message' => 'Something went wrong. Please try again.'];
                return response($response);
            }
        } catch (\Exception $e) {
            DB::rollback();
            $response = ['status' => false, 'message' => 'Something went wrong. Please try again.'];
            return response($response);
        }
    }



    public function destroy(Request $request)
    {
        if (Auth::user()->roles[0]->name == 'Owner') {
            try {
                $product = Product::where('id', $request->id)->first();
                if (!is_null($product)) {
                    $leadMaster = LeadMaster::where('product_id', $product->id)->count();
                    $task = Task::where('product_id', $product->id)->count();
                    if ($leadMaster <= 0 && $task <= 0) {
                        $product->delete();
                        $response = ['status' => true, 'message' => 'Product deleted successfully.'];
                    } else {
                        $response = ['status' => false, 'message' => 'Also used this product.'];
                    }
                    return response($response, 200);
                } else {
                    $response = ['status' => false, 'message' => 'Product not found.'];
                    return response($response, 200);
                }
            } catch (\Exception $e) {
                $response = ['status' => false, 'message' => 'Something went wrong. Please try again.'];
                return response($response, 500);
            }
        } else {
            $response = ['status' => false, 'message' => 'You are not authorized'];
            return response($response, 401);
        }
    }
}
