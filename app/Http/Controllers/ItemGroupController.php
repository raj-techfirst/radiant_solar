<?php

namespace App\Http\Controllers;

use App\Models\InveterCompany;
use App\Models\ItemGroup;
use App\Models\PenalCompany;
use App\Models\PenalType;
use App\Models\PenalWatt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class ItemGroupController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:item-group-list', ['only' => ['index']]);
        $this->middleware('permission:item-group-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:item-group-edit', ['only' => ['edit']]);
        $this->middleware('permission:item-group-delete', ['only' => ['destroy']]);
    }

    public function index()
    {
        if (request()->ajax()) {
            return DataTables::of(ItemGroup::with('panel_company', 'panel_type', 'panel_watt', 'inveter_company'))
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $html = '';
                    if (Gate::check('item-group-edit')) {
                        $html .= '<a data-id="' . $row->id . '" href="javascript:void(0);" class="avatar bg-light-info p-50 m-0 edit" data-bs-toggle="tooltip" data-placement="left" title="Edit"><i class="fa fa-edit"></i></a>';
                    }
                    if (Gate::check('item-group-delete')) {
                        $html .= ' <a data-id="' . $row->id . '" href="javascript:void(0);" class="avatar bg-light-danger p-50 m-0 delete" data-bs-toggle="tooltip" data-placement="left" title="Delete"><i class="fa fa-trash"></i></a>';
                    }
                    return $html;
                })
                ->addColumn('item_group', function ($row) {
                    return getItemGropName($row);
                })
                ->escapeColumns([])
                ->make(true);
        } else {
            $penalCompany = PenalCompany::select('id', 'name')->get();
            $penalType = PenalType::select('id', 'name')->get();
            $penalWatt = PenalWatt::select('id', 'name')->get();
            $inveterCompany = InveterCompany::select('id', 'name')->get();
            return view('admin.item-group.index', compact('penalCompany', 'penalType', 'penalWatt', 'inveterCompany'));
        }
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'group_type' => 'required',
            'inveter_company_id' => 'required_if:group_type,inverter',
            'inveter_kw' => 'required_if:group_type,inverter',
            'inveter_phase' => 'required_if:group_type,inverter',
            'panel_company_id' => 'required_if:group_type,panel',
            'panel_type_id' => 'required_if:group_type,panel',
            'panel_watt_id' => 'required_if:group_type,panel',
            //'item_code' => 'required',
            'gst_rate' => 'required',
            'p_type' => 'required_if:group_type,panel' // For DCR / NON DCr
        ], [
            'group_type.required' => 'Select Group Type',
            'inveter_company_id.required_if' => 'Select Inverter Company',
            'inveter_kw.required_if' => 'Enter Inverter KW',
            'inveter_phase.required_if' => 'Select Inverter Phase',
            'panel_company_id.required_if' => 'Select Panel Company',
            'panel_type_id.required_if' => 'Select Panel Type',
            'panel_watt_id.required_if' => 'Select Panel Watt',
            //'item_code.required' => 'Enter Item Code',
            'gst_rate.required' => 'Enter GST Rate',
            'p_type' => 'required_if:group_type,panel' // For DCR / NON DCr
        ]);

        if ($validator->fails()) {
            $response = ['status' => false, 'message' => 'Please input proper data.', 'errors' => $validator->errors()];
            return response()->json($response);
        }
        DB::beginTransaction();
        try {
            if (!is_null($request->id)) {
                $qry = ItemGroup::where('id', $request->id)->first();
                $response = ['data' => route('item-group.index'), 'status' => true, 'message' => 'Item Group updated successfully.'];
            } else {
                $qry = new ItemGroup();
                $response = ['data' => route('item-group.index'), 'status' => true, 'message' => 'Item Group added successfully.'];
            }
            $qry->group_type = $request->group_type;
            if ($request->group_type == "inverter") {
                if (!is_null($request->id)) {
                    $already = ItemGroup::where('id', '!=', $request->id)
                    ->where('inveter_company_id', $request->inveter_company_id)
                    ->where('inveter_kw', $request->inveter_kw)
                    ->where('inverter_type', $request->inveter_phase)
                    ->first();
                    if (!is_null($already)) {
                        return response()->json(['status' => false, 'server_error' => 'This Inverter already exists.']);
                    }
                }
                else
                {
                    $already = ItemGroup::where('inveter_company_id', $request->inveter_company_id)
                    ->where('inveter_kw', $request->inveter_kw)
                    ->where('inverter_type', $request->inveter_phase)
                    ->first();

                    if (!is_null($already)) {
                        return response()->json(['status' => false, 'server_error' => 'This Inverter already exists.']);
                    }
                }

                $qry->inveter_company_id = $request->inveter_company_id;
                $qry->inveter_kw = $request->inveter_kw;
                $qry->inverter_type = $request->inveter_phase;
                $qry->panel_company_id = 0;
                $qry->panel_type_id = 0;
                $qry->panel_watt_id = 0;
                $qry->unit_id = 2;
                $qry->p_type = '';
            } else {

                if (!is_null($request->id)) {
                    $already = ItemGroup::where('id', '!=', $request->id)
                    ->where('panel_company_id', $request->panel_company_id)
                    ->where('panel_type_id', $request->panel_type_id)
                    ->where('panel_watt_id', $request->panel_watt_id)
                    ->where('p_type', $request->p_type)
                    ->first();
                    if (!is_null($already)) {
                        return response()->json(['status' => false, 'server_error' => 'This Panel already exists.']);
                    }
                }
                else
                {
                    $already = ItemGroup::where('panel_company_id', $request->panel_company_id)
                    ->where('panel_type_id', $request->panel_type_id)
                    ->where('panel_watt_id', $request->panel_watt_id)
                    ->where('p_type', $request->p_type)
                    ->first();
                    if (!is_null($already)) {
                        return response()->json(['status' => false, 'server_error' => 'This Panel already exists.']);
                    }
                }

                $qry->panel_company_id = $request->panel_company_id;
                $qry->panel_type_id = $request->panel_type_id;
                $qry->panel_watt_id = $request->panel_watt_id;
                $qry->p_type = $request->p_type; // For DCR / NON DCr
                $qry->inveter_company_id = 0;
                $qry->inveter_kw = 0;
                $qry->inverter_type = '';
                $qry->unit_id = 2;
            }
            $qry->item_code = $request->item_code;
            $qry->hsn_code = $request->hsn_code;
            $qry->gst_rate = $request->gst_rate;
            $qry->moq_level = $request->moq_level;
            $result = $qry->save();
            DB::commit();
            if (!is_null($result)) {
                return response()->json($response);
            } else {
                return response()->json(['status' => false, 'server_error' => 'Something went wrong. Please try again.']);
            }
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['status' => false, 'server_error' => 'Something went wrong. Please try again.']);
        }
    }

    public function show(ItemGroup $itemGroup)
    {
        //
    }

    public function edit($id)
    {
        $qry = ItemGroup::where('id', $id)->first();
        if (!is_null($qry)) {
            $res = array('msg_type' => 'success', 'msg_title' => 'Success!', 'result' => $qry);
            header('Content-Type:application/json');
            echo json_encode($res);
        } else {
            return abort(404);
        }
    }

    public function update(Request $request, ItemGroup $itemGroup)
    {
        //
    }

    public function destroy(ItemGroup $itemGroup)
    {
        //
    }
}
