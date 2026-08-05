<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Models\Category;
use App\Models\CompanyProfile;
use App\Models\LeadMaster;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CategoryApiController extends Controller
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
            $category = Category::select('id', 'company_profile_id', 'category_name')->where('company_profile_id', $id)->orderBy('id', 'DESC')->get();
            $response = ['status' => true, 'message' => 'Category List', 'category' => $category];
            return response($response, 200);
        } catch (Exception $e) {
            $response = ['status' => false, 'message' => 'Something went wrong. Please try again.'];
            return response($response, 500);
        }
    }

    public function store(Request $request)
    {
        if (Auth::user()->roles[0]->name == 'Owner') {
            $validator = Validator::make($request->all(), [
                'category_name' => ['required', Rule::unique('categories')->where(function ($query) use ($request) {
                    if (!is_null($request->category_id)) {
                        return $query->where('user_id', Auth::id())->where('deleted_at', null)->where('id', '!=', $request->category_id);
                    } else {
                        return $query->where('user_id', Auth::id())->where('deleted_at', null);
                    }
                })],
            ], [
                'category_name.required' => 'Enter category',
                'category_name.unique' => 'The category has already been taken'
            ]);

            if ($validator->fails()) {
                $response = ['status' => false, 'message' => 'Please input proper data.', 'errors' => $validator->errors()];
                return response($response, 200);
            }
            DB::beginTransaction();
            try {
                if (!is_null($request->category_id)) {
                    $category = Category::where('id', $request->category_id)->first();
                    $response = ['status' => true, 'message' => 'Category updated successfully.'];
                } else {
                    $category = new Category();
                    $response = ['status' => true, 'message' => 'Category added successfully.'];
                }
                $companyProfile = CompanyProfile::where('user_id', Auth::id())->first();
                $category->user_id = Auth::id();
                $category->company_profile_id = $companyProfile->id;
                $category->category_name = $request->category_name;
                $result = $category->save();
                DB::commit();
                if (!is_null($result)) {
                    return response($response, 200);
                } else {
                    $response = ['status' => false, 'message' => 'Something went wrong. Please try again.'];
                    return response($response, 200);
                }
            } catch (\Exception $e) {
                DB::rollback();
                $response = ['status' => false, 'message' => 'Something went wrong. Please try again.'];
                return response($response, 500);
            }
        } else {
            $response = ['status' => false, 'message' => 'You are not authorized.'];
            return response($response, 401);
        }
    }

    public function destroy(Request $request)
    {
        if (Auth::user()->roles[0]->name == 'Owner') {
            try {
                $category = Category::where('id', $request->id)->first();
                if (!is_null($category)) {
                    $leadMaster = LeadMaster::where('category_id', $category->id)->count();
                    if ($leadMaster <= 0) {
                        $category->delete();
                        $response = ['status' => true, 'message' => 'Category deleted successfully.'];
                    } else {
                        $response = ['status' => false, 'message' => 'Also used this category.'];
                    }
                    return response($response, 200);
                } else {
                    $response = ['status' => false, 'message' => 'Category not found.'];
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
