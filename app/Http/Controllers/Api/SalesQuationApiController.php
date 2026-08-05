<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AgentSalesPerson;
use App\Models\CompanyProfile;
use App\Models\InveterCompany;
use App\Models\PenalCompany;
use App\Models\Policy;
use App\Models\SalesMaster;
use App\Models\SalesQuatation;
use App\Models\SalesQuatationMeta;
use App\Models\SalesQuationApi;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File;
use App\Models\SalesQuatationTechnicalSpecification;



class SalesQuationApiController extends Controller
{

    public function index(Request $request)
    {

        /* resident */

        if ($request->form_type == "resident") {
            $query = SalesQuatation::with('agentSalesPerson', 'bank', 'panelCompany', 'penalType', 'penalWatt')
                ->where('form_type', 'resident');

            $company = CompanyProfile::where('user_id', Auth::id())->first();
            if ($company->user_type == 'M') {
                $agent = AgentSalesPerson::where('user_id', Auth::id())->first();
                $agentIds = [$agent->id];
                $sales = CompanyProfile::select('company_profiles.id', 'company_profiles.user_id', 'agent_sales_people.id as agent_id')
                    ->leftJoin('agent_sales_people', 'agent_sales_people.user_id', 'company_profiles.user_id')
                    ->where('company_profiles.manager_id', $company->id)->get();
                if ($sales->count() > 0) {
                    foreach ($sales as $k => $v) :
                        array_push($agentIds, $v->agent_id);
                    endforeach;
                }
                $query->whereIn('agent_sales_person_id', $agentIds);
            }
            if ($company->user_type == 'S') {
                $agent = AgentSalesPerson::where('user_id', Auth::id())->first();
                $id = $agent->id;
                $query->where('agent_sales_person_id', $id);
            }

            if ($request->from_date != "" && $request->to_date == '') {
                $query->where('created_at', '>=', date('Y-m-d 00:00:00', strtotime($request->from_date)));
                $query->where('created_at', '<=', date('Y-m-d 23:59:59'));
            }
            if ($request->from_date != "" && $request->to_date != '') {
                $query->where('created_at', '>=', date('Y-m-d 00:00:00', strtotime($request->from_date)));
                $query->where('created_at', '<=', date('Y-m-d 23:59:59', strtotime($request->to_date)));
            }
            if ($request->from_date == "" && $request->to_date != '') {
                $query->where('created_at', '<=', date('Y-m-d 23:59:59', strtotime($request->to_date)));
            }
            if ($request->name_mobile != "") {
                $query->where(function ($query) use ($request) {
                    $query->where('name', 'like', '%' . $request->name_mobile . '%')
                        ->orWhere('mobile', 'like', '%' . $request->name_mobile . '%');
                });
            }

            if ($request->user != "") {
                $query->where('agent_sales_person_id',$request->user);
            }

            $salesQuatation = $query->orderBy('id','desc')->paginate(12);

            foreach ($salesQuatation as $value) {
                // Inveter company name
                if ($value->inveter_company_id != null) {
                    $hist = InveterCompany::whereIn('id', explode(',', $value->inveter_company_id))->get();
                    if ($hist != '') {
                        $inveter_companies_name =  "";
                        foreach ($hist as $ri) {
                            $inveter_companies_name != "" && $inveter_companies_name .= " / ";
                            $inveter_companies_name .= $ri->name;
                        }
                        $value['inveter_company_name'] = $inveter_companies_name;
                    }
                }
                // Inveter company name
                $value['penal_watt_name'] =  (!is_null($value->penalWatt) && $value->penal_watt_id != "") ? $value->penalWatt->name : '';
                $value['penal_type_name'] =  (!is_null($value->penalType) && $value->penal_type_id != "") ? $value->penalType->name : '';
                // company name
                if ($value->penal_company_id != null) {
                    $hist = PenalCompany::whereIn('id', explode(',', $value->penal_company_id))->get();
                    if ($hist != '') {
                        $penel_companies_name =  "";
                        foreach ($hist as $ri) {
                            $penel_companies_name != "" && $penel_companies_name .= " / ";
                            $penel_companies_name .= $ri->name;
                        }
                        $value['penal_company_name'] = $penel_companies_name;
                    }
                }
                // company name
                $value['bank_name'] =  (!is_null($value->bank) && $value->bank_id != "") ? $value->bank->name : '';
                $value['agent_sales_person_name'] =  (!is_null($value->agentSalesPerson) && $value->agent_sales_person_id != "") ? $value->agentSalesPerson->name : '';
                $value['created_date'] =  date('d-m-Y', strtotime($value->created_at));
                unset($value->created_at, $value->updated_at, $value->deleted_at, $value->agentSalesPerson, $value->bank, $value->panelCompany, $value->penalType, $value->penalWatt, $value->inveterCompany);
            }

            $response = ['status' => true, 'message' => 'Sales Quatation List', 'Resident With Subsidy' => $salesQuatation->items()];
            return response($response, 200);
        }
        /* END  resident */


        if ($request->form_type == "roof") {
            /* roof */
            $queryroof = SalesQuatation::with('agentSalesPerson', 'bank', 'panelCompany', 'penalType', 'penalWatt')
                ->where('form_type', 'roof');

            $company = CompanyProfile::where('user_id', Auth::id())->first();
            if ($company->user_type == 'M') {
                $agent = AgentSalesPerson::where('user_id', Auth::id())->first();
                $agentIds = [$agent->id];
                $sales = CompanyProfile::select('company_profiles.id', 'company_profiles.user_id', 'agent_sales_people.id as agent_id')
                    ->leftJoin('agent_sales_people', 'agent_sales_people.user_id', 'company_profiles.user_id')
                    ->where('company_profiles.manager_id', $company->id)->get();
                if ($sales->count() > 0) {
                    foreach ($sales as $k => $v) :
                        array_push($agentIds, $v->agent_id);
                    endforeach;
                }
                $queryroof->whereIn('agent_sales_person_id', $agentIds);
            }
            if ($company->user_type == 'S') {
                $agent = AgentSalesPerson::where('user_id', Auth::id())->first();
                $id = $agent->id;
                $queryroof->where('agent_sales_person_id', $id);
            }
            if ($request->from_date != "" && $request->to_date == '') {
                $queryroof->where('created_at', '>=', date('Y-m-d 00:00:00', strtotime($request->from_date)));
                $queryroof->where('created_at', '<=', date('Y-m-d 23:59:59'));
            }
            if ($request->from_date != "" && $request->to_date != '') {
                $queryroof->where('created_at', '>=', date('Y-m-d 00:00:00', strtotime($request->from_date)));
                $queryroof->where('created_at', '<=', date('Y-m-d 23:59:59', strtotime($request->to_date)));
            }
            if ($request->from_date == "" && $request->to_date != '') {
                $queryroof->where('created_at', '<=', date('Y-m-d 23:59:59', strtotime($request->to_date)));
            }
            if ($request->name_mobile != "") {
                $queryroof->where(function ($query) use ($request) {
                    $query->where('name', 'like', '%' . $request->name_mobile . '%')
                        ->orWhere('mobile', 'like', '%' . $request->name_mobile . '%');
                });
            }
            if ($request->user != "") {
                $queryroof->where('agent_sales_person_id',$request->user);
            }

            $salesQuatationroof = $queryroof->orderBy('id','desc')->paginate(12);

            foreach ($salesQuatationroof as $value) {
                // $value['inveter_company_name'] =  (!is_null($value->inveterCompany) && $value->inveter_company_id != "") ? $value->inveterCompany->name : '';
                // Inveter company name
                if ($value->inveter_company_id != null) {
                    $hist = InveterCompany::whereIn('id', explode(',', $value->inveter_company_id))->get();
                    if ($hist != '') {
                        $inveter_companies_name =  "";
                        foreach ($hist as $ri) {
                            $inveter_companies_name != "" && $inveter_companies_name .= " / ";
                            $inveter_companies_name .= $ri->name;
                        }
                        $value['inveter_company_name'] = $inveter_companies_name;
                    }
                }
                // Inveter company name
                $value['penal_watt_name'] =  (!is_null($value->penalWatt) && $value->penal_watt_id != "") ? $value->penalWatt->name : '';
                $value['penal_type_name'] =  (!is_null($value->penalType) && $value->penal_type_id != "") ? $value->penalType->name : '';
                // $value['penal_company_name'] =  (!is_null($value->panelCompany) && $value->penal_company_id != "") ? $value->panelCompany->name : '';
                // company name
                if ($value->penal_company_id != null) {
                    $hist = PenalCompany::whereIn('id', explode(',', $value->penal_company_id))->get();
                    if ($hist != '') {
                        $penel_companies_name =  "";
                        foreach ($hist as $ri) {
                            $penel_companies_name != "" && $penel_companies_name .= " / ";
                            $penel_companies_name .= $ri->name;
                        }
                        $value['penal_company_name'] = $penel_companies_name;
                    }
                }
                // company name
                $value['bank_name'] =  (!is_null($value->bank) && $value->bank_id != "") ? $value->bank->name : '';
                $value['agent_sales_person_name'] =  (!is_null($value->agentSalesPerson) && $value->agent_sales_person_id != "") ? $value->agentSalesPerson->name : '';
                $value['created_date'] =  date('d-m-Y', strtotime($value->created_at));
                unset($value->created_at, $value->updated_at, $value->deleted_at, $value->agentSalesPerson, $value->bank, $value->panelCompany, $value->penalType, $value->penalWatt, $value->inveterCompany);
            }

            $response = ['status' => true, 'message' => 'Sales Quatation List', 'Solar Roof Top' => $salesQuatationroof->items()];
            return response($response, 200);
        }
        /* END roof */

        if ($request->form_type == "trading") {
            ######## trading ######
            $querytrading = SalesQuatation::with('salesQuatationMeta', 'salesQuatationMeta.item', 'agentSalesPerson', 'bank', 'panelCompany', 'penalType', 'penalWatt')
                ->where('form_type', 'trading');

            $company = CompanyProfile::where('user_id', Auth::id())->first();
            if ($company->user_type == 'M') {
                $agent = AgentSalesPerson::where('user_id', Auth::id())->first();
                $agentIds = [$agent->id];
                $sales = CompanyProfile::select('company_profiles.id', 'company_profiles.user_id', 'agent_sales_people.id as agent_id')
                    ->leftJoin('agent_sales_people', 'agent_sales_people.user_id', 'company_profiles.user_id')
                    ->where('company_profiles.manager_id', $company->id)->get();
                if ($sales->count() > 0) {
                    foreach ($sales as $k => $v) :
                        array_push($agentIds, $v->agent_id);
                    endforeach;
                }
                $querytrading->whereIn('agent_sales_person_id', $agentIds);
            }
            if ($company->user_type == 'S') {
                $agent = AgentSalesPerson::where('user_id', Auth::id())->first();
                $id = $agent->id;
                $querytrading->where('agent_sales_person_id', $id);
            }
            if ($request->from_date != "" && $request->to_date == '') {
                $querytrading->where('created_at', '>=', date('Y-m-d 00:00:00', strtotime($request->from_date)));
                $querytrading->where('created_at', '<=', date('Y-m-d 23:59:59'));
            }
            if ($request->from_date != "" && $request->to_date != '') {
                $querytrading->where('created_at', '>=', date('Y-m-d 00:00:00', strtotime($request->from_date)));
                $querytrading->where('created_at', '<=', date('Y-m-d 23:59:59', strtotime($request->to_date)));
            }
            if ($request->from_date == "" && $request->to_date != '') {
                $querytrading->where('created_at', '<=', date('Y-m-d 23:59:59', strtotime($request->to_date)));
            }
            if ($request->name_mobile != "") {
                $querytrading->where(function ($query) use ($request) {
                    $query->where('name', 'like', '%' . $request->name_mobile . '%')
                        ->orWhere('mobile', 'like', '%' . $request->name_mobile . '%');
                });
            }
            if ($request->user != "") {
                $querytrading->where('agent_sales_person_id',$request->user);
            }
            $salesQuatationtrading = $querytrading->orderBy('id','desc')->paginate(12);

            foreach ($salesQuatationtrading as $value) {
                $value['inveter_company_name'] =  (!is_null($value->inveterCompany) && $value->inveter_company_id != "") ? $value->inveterCompany->name : '';
                $value['penal_watt_name'] =  (!is_null($value->penalWatt) && $value->penal_watt_id != "") ? $value->penalWatt->name : '';
                $value['penal_type_name'] =  (!is_null($value->penalType) && $value->penal_type_id != "") ? $value->penalType->name : '';
                $value['penal_company_name'] =  (!is_null($value->panelCompany) && $value->penal_company_id != "") ? $value->panelCompany->name : '';
                $value['bank_name'] =  (!is_null($value->bank) && $value->bank_id != "") ? $value->bank->name : '';
                $value['agent_sales_person_name'] =  (!is_null($value->agentSalesPerson) && $value->agent_sales_person_id != "") ? $value->agentSalesPerson->name : '';
                $value['created_date'] =  date('d-m-Y', strtotime($value->created_at));
                unset($value->created_at, $value->updated_at, $value->deleted_at, $value->agentSalesPerson, $value->bank, $value->panelCompany, $value->penalType, $value->penalWatt, $value->inveterCompany);
                foreach ($value->salesQuatationMeta as $item) {
                   if ($item->type == "Item") {
                        $item->item_name = $item->item->name ?? '';
                    } else {
                        $item->item_name = getItemGropName($item, 1);
                    }
                    unset($item->created_at, $item->updated_at, $item->deleted_at, $item->item,$item->itemGroup);
                }
            }

            $response = ['status' => true, 'message' => 'Sales Quatation List',  'Trading' => $salesQuatationtrading->items()];
            return response($response, 200);
        }
        ###### END trading #######

        $response = ['status' => false, 'message' => 'Error'];
        return response($response, 200);
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {

        if ($request->form_type == 'trading') {
            $validator = Validator::make($request->all(), [
                'mobile' => 'required',
                'name' => 'required',
                'address' => 'required',
                'ship_to' => 'required',
                // 'gst_no' => 'required',
                // 'item_id' => 'required',
                // 'nos' => 'required',
                // 'rate' => 'required',
                'agent_sales_person_id' => 'required',
            ], [
                'mobile.required' => 'Enter Mobile Number',
                'name.required' => 'Enter Name',
                'address.required' => 'Enter Address',
                'ship_to.required' => 'Enter Ship To',
                // 'gst_no.required' => 'Enter GST Number',
                // 'item_id.required' => 'Select Item',
                // 'nos.required' => 'Enter NOS',
                // 'rate.required' => 'Enter Rate',
                'agent_sales_person_id.required' => 'Select Agent/Sales Person',
            ]);
            if ($validator->fails()) {
                $response = ['status' => false, 'message' => 'Please input proper data.', 'errors' => $validator->errors()];
                return response()->json($response);
            }
        }
        if ($request->form_type == 'resident') {
            $validator = Validator::make($request->all(), [
                'res_mobile' => 'required',
                'res_name' => 'required',
                'res_address' => 'required',
                'res_penal_company_id' => 'required',
                'res_penal_type_id' => 'required',
                'res_penal_watt_id' => 'required',
                'res_penal_nos' => 'required',
                'res_pv_capacity_kw' => 'required',
                'res_inveter_company_id' => 'required',
                'res_inveter_capacity' => 'required',
                'res_no_of_inveter' => 'required',
                'res_structure' => 'required',
                'res_total_system_cost' => 'required',
                'res_meter_charges' => 'required',
                //'res_registration_fee' => 'required',
            ], [
                'res_mobile.required' => 'Enter Mobile Number',
                'res_name.required' => 'Enter name',
                'res_address.required' => 'Enter address',
                'res_penal_company_id.required' => 'Select Panel Company',
                'res_penal_type_id.required' => 'Select Panel Type',
                'res_penal_watt_id.required' => 'Select Panel Watt',
                'res_penal_nos.required' => 'Enter Panel NOS',
                'res_pv_capacity_kw.required' => 'Enter PV Capacity KW',
                'res_inveter_company_id.required' => 'Select Inveter Company Name',
                'res_inveter_capacity.required' => 'Enter Inveter Capacity',
                'res_no_of_inveter.required' => 'Enter No Of Inveter',
                'res_structure.required' => 'Choose Structure',
                'res_total_system_cost.required' => 'Enter Total System Cost',
                'res_meter_charges.required' => 'Enter Meter Charges',
                //'res_registration_fee.required' => 'Enter Registration Fee',
            ]);
            if ($validator->fails()) {
                $response = ['status' => false, 'message' => 'Please input proper data.', 'errors' => $validator->errors()];
                return response()->json($response);
            }
        }
        if ($request->form_type == 'roof') {
            $validator = Validator::make($request->all(), [
                'roof_mobile' => 'required',
                'roof_name' => 'required',
                'roof_address' => 'required',
                'penal_company_id' => 'required',
                'penal_type_id' => 'required',
                'penal_watt_id' => 'required',
                'penal_nos' => 'required',
                'pv_capacity_kw' => 'required',
                'inveter_company_id' => 'required',
                'inveter_capacity' => 'required',
                'no_of_inveter' => 'required',
                'structure' => 'required',
                'registration_fee' => 'required',
                'rate_per_kw' => 'required',
                'quatation_type' => 'required',
            ], [
                'roof_mobile.required' => 'Enter mobile',
                'roof_name.required' => 'Enter name',
                'roof_address.required' => 'Enter address',
                'penal_company_id.required' => 'Select Panel Company Name',
                'penal_type_id.required' => 'Select Panel Type',
                'penal_watt_id.required' => 'Select Panel Watt',
                'penal_nos.required' => 'Enter Panel Nos',
                'pv_capacity_kw.required' => 'Enter PV Capacity Kw',
                'inveter_company_id.required' => 'Select Inveter Company Name',
                'inveter_capacity.required' => 'Enter Inveter Capacity',
                'no_of_inveter.required' => 'Enter No Of Inveter',
                'structure.required' => 'Choose structure',
                'meter_charges.required' => 'Enter Meter Charges',
                'registration_fee.required' => 'Enter Registration Fee',
                'rate_per_kw.required' => 'Enter Rate Per KW',
                'quatation_type.required' => 'Select Quatation Type',
            ]);
            if ($validator->fails()) {
                $response = ['status' => false, 'message' => 'Please input proper data.', 'errors' => $validator->errors()];
                return response()->json($response);
            }
        }
        // if ($validator->fails()) {
        //     $response = ['status' => false, 'message' => 'Please input proper data.', 'errors' => $validator->errors()];
        //     return response()->json($response);
        // }
        DB::beginTransaction();
        try {
            if (!is_null($request->id)) {
                $SalesQuatation = SalesQuatation::where('id', $request->id)->first();
                // dd($SalesQuatation);
                $response = ['status' => true, 'message' => ' Sales Quatation updated successfully.'];
            } else {
                $SalesQuatation = new SalesQuatation();
                $response = ['status' => true, 'message' => ' Sales Quatation added successfully.'];
            }


            if ($request->form_type == 'trading') {
                $SalesQuatation->user_id = Auth::id();
                $SalesQuatation->form_type = $request->form_type;
                $SalesQuatation->lead_master_id = $request->lead_master_id;
                $SalesQuatation->name = strtoupper($request->name);
                $SalesQuatation->mobile = $request->mobile;
                $SalesQuatation->address = $request->address;
                $SalesQuatation->ship_to = $request->ship_to;
                $SalesQuatation->gst_no = $request->gst_no;
                $SalesQuatation->gst = $request->trading_gst;
                $SalesQuatation->reference = strtoupper($request->reference);
                $SalesQuatation->agent_sales_person_id = $request->agent_sales_person_id;
                $SalesQuatation->total_amount = $request->trading_total_amount;
                $SalesQuatation->bank_id = $request->trading_bank_id;
                $result = $SalesQuatation->save();
                if ($result) {
                    if (!empty($request['salesQuatationMeta']) && is_array($request['salesQuatationMeta'])) {
                        SalesQuatationMeta::where('sales_quatation_id', $request->id)->delete();
                        foreach ($request['salesQuatationMeta'] as $key => $value) {
                           if ($value['type'] == "Item") {
                                $salesQuatationMetaData = [
                                    'sales_quatation_id' => $SalesQuatation->id,
                                    'type' => "Item",
                                    'item_id' => $value['item_id'],
                                    'item_group_id' => 0,
                                    'nos' => $value['nos'],
                                    'rate' => $value['rate'],
                                    'item_gst' => $value['item_gst']
                                ];
                            }
                            if ($value['type'] == "ItemGroup") {
                                $salesQuatationMetaData = [
                                    'sales_quatation_id' => $SalesQuatation->id,
                                    'type' => "ItemGroup",
                                    'item_id' => 0,
                                    'item_group_id' => $value['item_group_id'],
                                    'nos' => $value['nos'],
                                    'rate' => $value['rate'],
                                    'item_gst' => $value['item_gst']
                                ];
                            }

                            SalesQuatationMeta::create($salesQuatationMetaData);
                        }
                    }
                }
            }
            if ($request->form_type == 'resident') {
                $SalesQuatation->user_id = Auth::id();
                $SalesQuatation->form_type = $request->form_type;
                $SalesQuatation->lead_master_id = $request->res_lead_master_id;
                $SalesQuatation->name = strtoupper($request->res_name);
                $SalesQuatation->mobile = $request->res_mobile;
                $SalesQuatation->address = $request->res_address;
                $SalesQuatation->penal_company_id = implode(',', $request['res_penal_company_id']);
                $SalesQuatation->penal_type_id = $request->res_penal_type_id;
                $SalesQuatation->penal_watt_id = $request->res_penal_watt_id;
                $SalesQuatation->penal_nos = $request->res_penal_nos;
                $SalesQuatation->pv_capacity_kw = $request->res_pv_capacity_kw;
                $SalesQuatation->inveter_company_id = implode(',', $request['res_inveter_company_id']);
                $SalesQuatation->inveter_capacity = $request->res_inveter_capacity;
                $SalesQuatation->no_of_inveter = $request->res_no_of_inveter;
                $SalesQuatation->structure = $request->res_structure;
                $SalesQuatation->common_meter = $request->res_common_meter;
                $SalesQuatation->total_system_cost = $request->res_total_system_cost;
                $SalesQuatation->meter_charges_extra = $request->res_meter_charge_extra;

                $SalesQuatation->meter_charges = $request->res_meter_charges;
                $SalesQuatation->registration_fee = $request->res_registration_fee;
                $SalesQuatation->total_amount = $request->resident_total_amount;
                $SalesQuatation->reference = strtoupper($request->reference);
                $SalesQuatation->agent_sales_person_id = $request->res_agent_sales_person_id;
                $SalesQuatation->other_charge_name = $request->res_other_charge_name;
                $SalesQuatation->other_charge_amount = $request->res_other_charge_amount;
                $SalesQuatation->subsidy = $request->res_subsidy;
                $SalesQuatation->bank_id = $request->res_bank_id;
                $result = $SalesQuatation->save();
            }
            if ($request->form_type == 'roof') {
                $SalesQuatation->user_id = Auth::id();
                $SalesQuatation->form_type = $request->form_type;
                $SalesQuatation->lead_master_id = $request->roof_lead_master_id;
                $SalesQuatation->name = strtoupper($request->roof_name);
                $SalesQuatation->mobile = $request->roof_mobile;
                $SalesQuatation->address = $request->roof_address;
                $SalesQuatation->penal_company_id = implode(',', $request->penal_company_id);
                $SalesQuatation->penal_type_id = $request->penal_type_id;
                $SalesQuatation->penal_watt_id = $request->penal_watt_id;
                $SalesQuatation->penal_nos = $request->penal_nos;
                $SalesQuatation->pv_capacity_kw = $request->pv_capacity_kw;
                $SalesQuatation->inveter_company_id = implode(',', $request->inveter_company_id);
                $SalesQuatation->inveter_capacity = $request->inveter_capacity;
                $SalesQuatation->no_of_inveter = $request->no_of_inveter;
                $SalesQuatation->structure = $request->structure;
                $SalesQuatation->common_meter = $request->common_meter;
                $SalesQuatation->meter_charges_extra = $request->meter_charge_extra;
                $SalesQuatation->meter_charges = $request->meter_charges;
                $SalesQuatation->registration_fee = $request->registration_fee;
                $SalesQuatation->rate_per_kw = $request->rate_per_kw;
                $SalesQuatation->gst = $request->gst;
                $SalesQuatation->quatation_type = $request->quatation_type;
                $SalesQuatation->total_amount = $request->roof_total_amount;
                $SalesQuatation->other_charge_name = $request->roof_other_charge_name;
                $SalesQuatation->other_charge_amount = $request->roof_other_charge_amount;
                $SalesQuatation->bank_id = $request->roof_bank_id;
                $SalesQuatation->agent_sales_person_id = $request->res_agent_sales_person_id;
                $result = $SalesQuatation->save();
            }
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

    public function show(SalesQuationApi $salesQuationApi)
    {
        //
    }

    public function edit(SalesQuationApi $salesQuationApi)
    {
        //
    }

    public function update(Request $request, SalesQuationApi $salesQuationApi)
    {
        //
    }

    public function destroy(Request $request)
    {
        try {
            $salesMaster = SalesMaster::where('sales_quatation_id', $request->id)->get();
            if ($salesMaster->count() == 0) {
                $salesQuatation = SalesQuatation::where('id', $request->id)->first();
                if (!is_null($salesQuatation)) {
                    $salesQuatation->delete();
                    $response = ['status' => true, 'message' => 'Sales Quotation deleted successfully.'];
                    return response($response, 200);
                } else {
                    $response = ['status' => false, 'message' => 'Sales Quotation not found.'];
                    return response($response, 200);
                }
            } else {
                $response = ['status' => false, 'server_error' => 'Sorry, you cannot delete this quotation because you have created an order based on it.'];
            }
        } catch (\Exception $e) {
            $response = ['status' => false, 'message' => 'Something went wrong. Please try again.'];
            return response($response, 500);
        }
    }

    public function salesQuatationList(Request $request)
    {

      //  $query = SalesQuatation::select('id', 'name', 'mobile', 'address', 'agent_sales_person_id', 'total_amount', 'pv_capacity_kw');

        $query = SalesQuatation::select('id', 'name', 'mobile', 'address', 'agent_sales_person_id', 'total_amount', 'pv_capacity_kw','penal_company_id','penal_watt_id','inveter_company_id')
        ->whereRaw('NOT EXISTS (SELECT 1 FROM sales_masters WHERE sales_masters.sales_quatation_id = sales_quatations.id)');

        $company = CompanyProfile::where('user_id', Auth::id())->first();
        if ($company->user_type == 'M') {
            $agent = AgentSalesPerson::where('user_id', Auth::id())->first();
            $agentIds = [$agent->id];
            $sales = CompanyProfile::select('company_profiles.id', 'company_profiles.user_id', 'agent_sales_people.id as agent_id')
                ->leftJoin('agent_sales_people', 'agent_sales_people.user_id', 'company_profiles.user_id')
                ->where('company_profiles.manager_id', $company->id)->get();
            if ($sales->count() > 0) {
                foreach ($sales as $k => $v) :
                    array_push($agentIds, $v->agent_id);
                endforeach;
            }
            $query->whereIn('agent_sales_person_id', $agentIds);
        }
        if ($company->user_type == 'S') {
            $agent = AgentSalesPerson::where('user_id', Auth::id())->first();
            $id = $agent->id;
            $query->where('agent_sales_person_id', $id);
        }

        if ($request->type == 'DC') {
            $query->where('form_type','=', 'trading');
        }
        else
        {
            $query->where('form_type', '!=', 'trading');
        }

        $salesQuatation = $query->orderBy('id', 'DESC')->get();

        $response = ['status' => true, 'message' => 'Sales List', 'quatation' => $salesQuatation];
        return response($response, 200);
    }

    public function salesQuatationPdfSave(Request $request)
    {

        $id = $request->id;
        // Define the directory where you want to store the PDF within the public folder
        $directory = 'pdf/';

        // Ensure the directory exists, if not, create it
        if (!File::exists(public_path($directory))) {
            File::makeDirectory(public_path($directory), 0777, true);
        }

        $sales_data = SalesQuatation::select('sales_quatations.*', 'penal_types.name as penal_type_name', 'penal_watts.name as panel_watt', 'agent_sales_people.name as agent_name', 'agent_sales_people.number as agent_mobile', 'banks.name as bank_name', 'banks.account_number', 'banks.ifsc_number', 'banks.branch', 'banks.holder_name')
            ->leftJoin('penal_watts', 'sales_quatations.penal_watt_id', '=', 'penal_watts.id')
            ->leftJoin('penal_types', 'sales_quatations.penal_type_id', '=', 'penal_types.id')
            ->leftJoin('agent_sales_people', 'sales_quatations.agent_sales_person_id', '=', 'agent_sales_people.id')
            ->leftJoin('banks', 'sales_quatations.bank_id', '=', 'banks.id')
            ->orderBy('sales_quatations.id', 'DESC')->where('sales_quatations.id', $id)->first();
        // company name
       /* if ($sales_data->penal_company_id != null) {
            $hist = PenalCompany::whereIn('id', explode(',', $sales_data->penal_company_id))->get();
            if ($hist != '') {
                $penel_companies_name =  "";
                foreach ($hist as $ri) {
                    $penel_companies_name != "" && $penel_companies_name .= " / ";
                    $penel_companies_name .= $ri->name;
                }
                $sales_data->penal_company_id = $penel_companies_name;
            }
        } */

		if ($sales_data->penal_company_id != null) {
            $hist = PenalCompany::whereIn('id', explode(',', $sales_data->penal_company_id))->get();
            if ($hist != '') {
                $company = [];
                $penel_companies_name =  "";
                foreach ($hist as $ri) {
                    $penel_companies_name != "" && $penel_companies_name .= " / ";
                    $penel_companies_name .= $ri->name;
                    $company[] = [
                        'name' => $ri->name,
                        'logo' => $ri->logo,
                    ];
                }
                $sales_data->penal_company_id = $penel_companies_name;
                $sales_data['company'] = array_values($company);
            }
        }

        // company name
        // Inveter company name
        if ($sales_data->inveter_company_id != null) {
            $hist = InveterCompany::whereIn('id', explode(',', $sales_data->inveter_company_id))->get();
            if ($hist != '') {
                $inveter_companies_name =  "";
                foreach ($hist as $ri) {
                    $inveter_companies_name != "" && $inveter_companies_name .= " / ";
                    $inveter_companies_name .= $ri->name;
                }
                $sales_data->inveter_company_id = $inveter_companies_name;
            }
        }
        // Inveter company name
        if (!is_null($sales_data)) {
            if ($sales_data->form_type == 'resident') {
                $policy_data = Policy::where('form_type', 'resident')->first();

				 $technicalSpecification = SalesQuatationTechnicalSpecification::where('sales_quatation_id', $sales_data->id)->get();
                $data = [
                    'title' => 'Quatation',
                    'sales_data' => $sales_data,
                    'policy_data' => $policy_data,
                    'technicalSpecification' => $technicalSpecification
                ];


                $pdf = Pdf::loadView('admin.sales-quatation.rooftop_pdf', $data);

                $filename = 'quatation.pdf';


                $pdf->save(public_path($directory . $filename));

                // Get the URL of the saved PDF
                $url = asset($directory . $filename);

                $response = ['status' => true, 'message' => 'Quatation PDF', 'url' => $url];
                return response($response, 200);
            } else if ($sales_data->form_type == 'trading') {
                $meta_data = SalesQuatationMeta::with('item')->where('sales_quatation_id', $sales_data->id)->get();
                $policy_data = Policy::where('form_type', 'trading')->first();
                $data = [
                    'title' => 'Trading',
                    'sales_data' => $sales_data,
                    'meta_data' => $meta_data,
                    'policy_data' => $policy_data,
                ];
                $pdf = Pdf::loadView('admin.sales-quatation.trading_pdf', $data);

                $filename = 'trading.pdf';
                $pdf->save(public_path($directory . $filename));

                // Get the URL of the saved PDF
                $url = asset($directory . $filename);

                $response = ['status' => true, 'message' => 'Quatation PDF', 'url' => $url];
                return response($response, 200);
            } else if ($sales_data->form_type == 'roof') {
                $policy_data = Policy::where('form_type', 'roof')->first();
				$technicalSpecification = SalesQuatationTechnicalSpecification::where('sales_quatation_id', $sales_data->id)->get();


                $rate_per_kw = $sales_data->rate_per_kw;
                $rate_per_kw_value = $sales_data->pv_capacity_kw * $sales_data->rate_per_kw;

                $rate_per_kw_gst = 0;
                $rate_per_kw_value_gst = 0;
                $gst = $sales_data->gst;
                $val4 = $sales_data->rate_per_kw;

                if ($gst == 'Extra') {
                    $rate_per_kw_gst = (($rate_per_kw * env("PER")) / 100);
                    $value1 = $rate_per_kw_value;
                    $rate_per_kw_value_gst = (($value1 * env("PER")) / 100);
                    $rate_per_kw_value = $sales_data->pv_capacity_kw * $val4;
                } else {
                    $a = $rate_per_kw_value * 100;
                    $b = env("PER") + 100;
                    $value1 = ($a / $b);
                    $rate_per_kw_value_gst = (($value1 * env("PER")) / 100);

                    $c = ((($rate_per_kw * 100) * env("PER")) / 100);
                    $rate_per_kw_gst = ($c / $b);
                    $val4 = ($rate_per_kw - $rate_per_kw_gst);

                    $rate_per_kw_value = $sales_data->pv_capacity_kw * $val4;
                }

                $calc['val4'] = $val4;
                $calc['rate_per_kw_value'] = $rate_per_kw_value;
                $calc['rate_per_kw_gst'] = $rate_per_kw_gst;
                $calc['rate_per_kw_value_gst'] = $rate_per_kw_value_gst;

                $data = [
                    'title' => 'Solar RoofTop',
                    'sales_data' => $sales_data,
                    'policy_data' => $policy_data,
                    'calc' => $calc,
					'technicalSpecification' => $technicalSpecification
                ];
                $pdf = Pdf::loadView('admin.sales-quatation.rooftop_industry_pdf', $data);

                $filename = 'solar-rooftop.pdf';
                $pdf->save(public_path($directory . $filename));

                // Get the URL of the saved PDF
                $url = asset($directory . $filename);

                $response = ['status' => true, 'message' => 'Quatation PDF', 'url' => $url];
                return response($response, 200);
            }
            $response = ['status' => false, 'server_error' => 'Something went wrong. Please try again.'];
            return response($response, 200);
        } else {
            $response = ['status' => false, 'server_error' => 'Something went wrong. Please try again.'];
            return response($response, 200);
        }
    }

     public function changeStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required',
            'id' => 'required'
        ], [
            'status.required' => 'Select Payment Status',
            'id.required' => 'Enter Payment id',

        ]);
        if ($validator->fails()) {
            $response = ['status' => false, 'message' => 'Please input proper data.', 'errors' => $validator->errors()];
            return response()->json($response);
        }

        try {
             $response = ['status' => false, 'message' => 'Not Found'];
            $salesQuatation = SalesQuatation::where('id', $request->id)->first();
            if(!is_null($salesQuatation)){
            $salesQuatation->current_status = $request->status;
            $res =  $salesQuatation->save();
                if ($res) {

                    $response = ['status' => true, 'message' => 'Status changed successfully.'];
                } else {
                     $response = ['status' => false, 'server_error' => 'Something went wrong. Please try again.'];
                }
            }
            return response()->json($response);
        } catch (\Exception $e) {
            DB::rollback();
            $response = ['status' => false, 'server_error' => 'Something went wrong. Please try again.'];
            return response()->json($response);
        }
    }

}
