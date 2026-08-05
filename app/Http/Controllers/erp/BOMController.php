<?php


namespace App\Http\Controllers\erp;

use App\Http\Controllers\Controller;

use App\Models\erp\BOM;
use App\Models\erp\BOMMeta;
use App\Models\ItemGroup;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class BOMController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:bom-list', ['only' => ['index']]);
        $this->middleware('permission:bom-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:bom-edit', ['only' => ['edit']]);
        $this->middleware('permission:bom-delete', ['only' => ['destroy']]);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (request()->ajax()) {
            return DataTables::of(BOM::with('meta'))
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $html = '';
                    $html .= '<a data-id="' . $row->id . '" href="javascript:void(0);" class="avatar bg-light-success p-50 m-0 view" data-bs-toggle="tooltip" data-placement="left" title="View"><i class="fa fa-eye"></i></a>';
                    if (Gate::check('bom-edit')) {
                        $html .= ' <a href="' . route('bom.edit', $row->id) . '" class="avatar bg-light-info p-50 m-0" data-bs-toggle="tooltip" data-placement="left" title="Edit"><i class="fa fa-edit"></i></a>';
                    }
                    if (Gate::check('bom-delete')) {
                        $html .= ' <a data-id="' . $row->id . '" href="javascript:void(0);" class="avatar bg-light-danger p-50 m-0 delete" data-bs-toggle="tooltip" data-placement="left" title="Delete"><i class="fa fa-trash"></i></a>';
                    }
                    if (Gate::check('bom-create')) {
                        $html .= ' <a  href="' . route('bom-clone', $row->id) . '" class="avatar bg-light-secondary p-50 m-0 clone" data-bs-toggle="tooltip" data-placement="left" title="Clone"><i class="fa fa-copy"></i></a>';
                    }
                    return $html;
                })
                ->escapeColumns([])
                ->make(true);
        } else {
            return view('erp.bom.index');
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $items = Product::with('unit')->get();
        $itemGroup = ItemGroup::with('panel_company', 'panel_type', 'panel_watt', 'inveter_company', 'unit')->get();
        return view('erp.bom.create', compact('items', 'itemGroup'));
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
            'bom_name' => [
                'required',
                Rule::unique('boms')->where(function ($query) use ($request) {
                    return $query->where('deleted_at', null);
                })
            ],
        ], [
            'bom_name.required' => 'Please Enter Name',
            'bom_name.unique' => 'This Name has already been taken'
        ]);

        if ($validator->fails()) {
            $response = ['status_code' => 201, 'message' => 'Please input proper data.', 'errors' => $validator->errors()];
            return response()->json($response);
        }

        DB::beginTransaction();
        try {
            $addPurches = array(
                'bom_name' => $request->bom_name,
                'remarks' => $request->remark
            );
            $saveBOM = BOM::create($addPurches);

            foreach ($request->invoice as $in) {
                $product_id = $item_group_id = 0;
                if (!empty($in['product_id']) || !empty($in['item_group_id'])) {
                    if ($in['type'] == "Item") {
                        $unit_id = Product::where('id', $in['product_id'])->first()->unit_id;
                        $product_id = $in['product_id'];
                    } else {
                        $unit_id = ItemGroup::where('id', $in['item_group_id'])->first()->unit_id;
                        $item_group_id = $in['item_group_id'];
                    }

                    $addBOMMeta = array(
                        'boms_id' => $saveBOM->id,
                        'type' => $in['type'],
                        'item_id' => $product_id,
                        'item_group_id' => $item_group_id,
                        'unit_id' => $unit_id,
                        'quantity' => $in['quantity'],
                    );
                    BOMMeta::create($addBOMMeta);
                }
            }
            if (!is_null($saveBOM)) {
                DB::commit();
                return response()->json(array('status_code' => 200, 'data' => route('bom.index'), 'message' => 'BOM added successfully.'));
            } else {
                DB::rollback();
                return response()->json(array('status_code' => 403, 'message' => 'BOM added failed'));
            }
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(array('status_code' => 500, 'message' => 'Something went wrong. Please try again.'));
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\erp\BOM  $bOM
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $bOM = BOM::where('id', $id)->first();
            $bOMMeta = BOMMeta::with('product', 'unit', 'itemGroup', 'itemGroup.panel_company', 'itemGroup.panel_type', 'itemGroup.panel_watt', 'itemGroup.inveter_company')
                ->where('boms_id', $id)->get();
            return view('erp.bom.view', compact('bOM', 'bOMMeta'))->render();
        } catch (\Exception $e) {
            $response = ['status_code' => 500, 'message' => 'Something went wrong. Please try again.'];
            return response()->json($response);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\erp\BOM  $bOM
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $items = Product::with('unit')->get();
        $itemGroup = ItemGroup::with('panel_company', 'panel_type', 'panel_watt', 'inveter_company', 'unit')->get();
        $bOM = BOM::where('id', $id)->first();
        $bOMMeta = BOMMeta::where('boms_id', $bOM->id)->get();
        return view('erp.bom.edit', compact('bOM', 'bOMMeta', 'items', 'itemGroup'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\erp\BOM  $bOM
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, BOM $bOM)
    {
        $validator = Validator::make($request->all(), [
            'bom_name' => [
                'required',
                Rule::unique('boms')->where(function ($query) use ($request) {
                    return $query->where('deleted_at', null);
                })->ignore($request->id)
            ],
        ], [
            'bom_name.required' => 'Please Enter Name',
            'bom_name.unique' => 'This Name has already been taken'
        ]);


        if ($validator->fails()) {
            $response = ['status_code' => 201, 'message' => 'Please input proper data.', 'errors' => $validator->errors()];
            return response()->json($response);
        }

        DB::beginTransaction();
        try {

            $bOM = BOM::where('id', $request->id)->first();
            $bOM->bom_name = $request->bom_name;
            $bOM->remarks = $request->remark;
            $bOM->save();

            $existingBOMMetas = BOMMeta::where('boms_id', $bOM->id)->get();

            foreach ($request->invoice as $in) {
                $product_id = $item_group_id = 0;
                if (!empty($in['product_id']) || !empty($in['item_group_id'])) {
                    if ($in['type'] == "Item") {
                        $unit_id = Product::where('id', $in['product_id'])->first()->unit_id;
                        $product_id = $in['product_id'];
                    } else {
                        $unit_id = ItemGroup::where('id', $in['item_group_id'])->first()->unit_id;
                        $item_group_id = $in['item_group_id'];
                    }
                    if ($in['meta_id'] != '') {
                        $addBOMMeta = BOMMeta::where('id', $in['meta_id'])->first();
                        $invoiceMetaIds[] = $in['meta_id'];
                    } else {
                        $addBOMMeta = new BOMMeta();
                    }
                    $addBOMMeta->boms_id = $bOM->id;
                    $addBOMMeta->type = $in['type'];
                    $addBOMMeta->item_id = $product_id;
                    $addBOMMeta->item_group_id = $item_group_id;
                    $addBOMMeta->unit_id = $unit_id;
                    $addBOMMeta->quantity = $in['quantity'];
                    $addBOMMeta->save();
                }
            }

            foreach ($existingBOMMetas as $existingBOMMeta) {
                if (!in_array($existingBOMMeta->id, $invoiceMetaIds)) {
                    $existingBOMMeta->delete();
                }
            }

            if (!is_null($addBOMMeta)) {
                DB::commit();
                return response()->json(array('status_code' => 200, 'data' => route('bom.index'), 'message' => 'BOM Updated successfully.'));
            } else {
                DB::rollback();
                return response()->json(array('status_code' => 403, 'message' => 'BOM Update failed'));
            }
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(array('status_code' => 500, 'message' => 'Something went wrong. Please try again.'));
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\erp\BOM  $bOM
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            BOM::where('id', $id)->delete();
            BOMMeta::where('boms_id', $id)->delete();
            return response()->json(['status_code' => 200, 'message' => 'Deleted successfully.']);
        } catch (\Exception $e) {
            return response()->json(array('status_code' => 500, 'message' => 'Something went wrong. Please try again.'));
        }
    }

    public function cloneData($id)
    {
        $items = Product::with('unit')->get();
        $itemGroup = ItemGroup::with('panel_company', 'panel_type', 'panel_watt', 'inveter_company', 'unit')->get();
        $bOM = BOM::where('id', $id)->first();
        $bOMMeta = BOMMeta::with('product', 'unit', 'itemGroup', 'itemGroup.panel_company', 'itemGroup.panel_type', 'itemGroup.panel_watt', 'itemGroup.inveter_company')
            ->where('boms_id', $id)->get();
        return view('erp.bom.cloneView', compact('bOM', 'bOMMeta', 'items', 'itemGroup'));
    }
}
