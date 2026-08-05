<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Models\Category;
use App\Models\CompanyProfile;
use App\Models\LeadMaster;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    function __construct()
    {
        // $this->middleware('permission:category-list', ['only' => ['index']]);
        // $this->middleware('permission:category-create', ['only' => ['create', 'store']]);
        // $this->middleware('permission:category-edit', ['only' => ['edit', 'store']]);
        // $this->middleware('permission:category-delete', ['only' => ['destroy']]);

        $this->middleware('permission:category-list|category-create|category-edit|category-delete', ['only' => ['index', 'store']]);
        $this->middleware('permission:category-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:category-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:category-delete', ['only' => ['destroy']]);
    }

    public function index()
    {
        if (request()->ajax()) {
            //where('user_id', Auth::id())->
            return DataTables::of(Category::orderBy('id', 'DESC'))
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $html = '<td>';
                    if (Gate::check('category-edit')) {
                        $html .= '<a data-id="' . $row->id . '" href="javascript:void(0);" class="avatar bg-light-info p-50 m-0 edit" data-bs-toggle="tooltip" data-placement="left" title="Edit"><i class="fa fa-edit"></i></a>';
                    }
                    if (Gate::check('category-delete')) {
                        $html .= ' <a data-id="' . $row->id . '" href="javascript:void(0);" class="avatar bg-light-danger p-50 m-0 delete" data-bs-toggle="tooltip" data-placement="left" title="Delete"><i class="fa fa-trash"></i></a>';
                    }
                    $html .= '</td>';
                    return $html;
                })
                ->escapeColumns([])
                ->make(true);
        } else {
            return view('admin.category.view_category');
        }
    }

    public function create()
    {
        return view('admin.category.add_category');
    }

    public function store(Request $request)
    {
        if (!is_null($request->category_id)) {
            $name = [
                'required',
                Rule::unique('categories')->where(function ($query) use ($request) {
                    return $query->where('deleted_at', null);
                })->ignore($request->category_id),
            ];
        } else {
            $name = [
                'required',
                Rule::unique('categories')->where(function ($query) use ($request) {
                    return $query->where('deleted_at', null);
                }),
            ];
        }
        $validator = Validator::make($request->all(), [
            'category_name' => $name
        ], [
            'category_name.required' => 'Enter category',
            'category_name.unique' => 'The category has already been taken'
        ]);

        if ($validator->fails()) {
            $response = ['status' => false, 'message' => 'Please input proper data.', 'errors' => $validator->errors()];
            return response()->json($response);
        }
        DB::beginTransaction();
        try {
            if (!is_null($request->category_id)) {
                $category = Category::where('id', $request->category_id)->first();
                $response = ['data' => route('category.index'), 'status' => true, 'message' => ' Category updated successfully.'];
            } else {
                $category = new Category();
                $response = ['data' => route('category.index'), 'status' => true, 'message' => ' Category added successfully.'];
            }
            $companyProfile = CompanyProfile::where('user_id', Auth::id())->first();
            $category->user_id = Auth::id();
            $category->company_profile_id = $companyProfile->id;
            $category->category_name = $request->category_name;
            $result = $category->save();
            DB::commit();
            if (!is_null($result)) {
                return response()->json($response);
            } else {
                $response = ['status' => false, 'server_error' => 'Something went wrong. Please try again.'];
                return response()->json($response);
            }
        } catch (\Exception $e) {
            dd($e);
            DB::rollback();
            $response = ['status' => false, 'server_error' => 'Something went wrong. Please try again.'];
            return response()->json($response);
        }
    }

    public function show(Category $category)
    {
        //
    }

    public function edit($id)
    {
        $category = Category::where('id', $id)->first();
        if (!is_null($category)) {
            $res = array('msg_type' => 'success', 'msg_title' => 'Success!', 'result' => $category);
            header('Content-Type:application/json');
            echo json_encode($res);
        } else {
            return abort(404);
        }
    }

    public function update(Request $request, Category $category)
    {
        //
    }

    public function destroy($id)
    {
        try {
            $category = Category::where('id', $id)->first();
            $leadMaster = LeadMaster::where('category_id', $category->id)->count();
            if ($leadMaster <= 0) {
                $category->delete();
                $response = ['status' => true, 'message' => ' Deleted successfully.'];
            } else {
                $response = ['status' => false, 'message' => ' Also used this product.'];
            }
            return response()->json($response);
        } catch (\Exception $e) {
            $response = ['status' => false, 'server_error' => 'Something went wrong. Please try again.'];
            return response()->json($response);
        }
    }
}
