<?php

namespace App\Http\Controllers;

use App\Models\erp\BOMMeta;
use App\Models\erp\ProjectWiseStock;
use App\Models\erp\ProjectWiseStockHistory;
use App\Models\erp\SerialNumber;
use App\Models\Installation;
use App\Models\InstallationInvater;
use App\Models\InstallationItems;
use App\Models\InstallationPenal;
use App\Models\InstallationPenalMaster;
use App\Models\InvaterImage;
use App\Models\ItemGroup;
use App\Models\PenalImage;
use App\Models\SalesMaster;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Facades\Image;

class InstallationController extends Controller
{
    // start web code not for api
    public function edit($id)
    {
        $query = ProjectWiseStock::with('item', 'item.category', 'itemGroup')
            ->where(function ($q) use ($id) {
                if (!empty($id) && $id != '') {
                    $q->where('sales_master_id', $id);
                }
            })
            ->orWhere(function ($q) {
                $q->where('installer_id', Auth::id());
            });
        $stockListController = $query->get();
        $salesMaster = SalesMaster::select('bom_id')->where('id', $id)->first();

        if ($stockListController->count() > 0 && $salesMaster->bom_id != null) {

            $query = ProjectWiseStock::with('item', 'item.category', 'itemGroup')
                ->where(function ($q) use ($id) {
                    $q->where('sales_master_id', $id);
                })
                ->orWhere(function ($q) {
                    $q->where('installer_id', Auth::id());
                });
            $stockListController = $query->get();
            if ($stockListController->count() > 0) {

                foreach ($stockListController as $key => $value):
                    if ($value->type == "Item") {
                        $bomMeta = BOMMeta::select('quantity')
                            ->where('item_id', $value->item->id)
                            ->where('boms_id', $salesMaster->bom_id)
                            ->first();

                        $unit = $value->item->unit->unit_name;

                        $installationItems = InstallationItems::select('use_stock', 'stock_type')->where('item_id', $value->item->id)->where('sales_master_id', $id)->get();
                        $project_usage = $installer_usage = 0;
                        if ($installationItems->count() > 0) {
                            foreach ($installationItems as $alK => $alV):
                                if ($alV->stock_type == 'project') {
                                    $project_usage = $alV->use_stock;
                                } else {
                                    $installer_usage = $alV->use_stock;
                                }
                            endforeach;
                        }

                        $itemData = [
                            'id' => $value->item->id,
                            'name' => $value->item->name,
                            'unit' => $unit,
                            'required_qty' => (!is_null($bomMeta) && !is_null($bomMeta->quantity)) ? $bomMeta->quantity : 0,
                            'project_stock_qty' => 0,
                            'installer_stock_qty' => 0,
                            'project_usage' => $project_usage,
                            'installer_usage' => $installer_usage,
                        ];

                        $itemData[$value->issue_type . '_stock_qty'] = $value->quantity ?? 0;

                        if (isset($itemStock[$value->item->category->id])) {
                            $itemExists = false;

                            foreach ($itemStock[$value->item->category->id]['items'] as &$existingItem) {
                                if ($existingItem['id'] == $value->item->id) {

                                    if (isset($existingItem[$value->issue_type . '_stock_qty'])) {
                                        $existingItem[$value->issue_type . '_stock_qty'] += $itemData[$value->issue_type . '_stock_qty'];
                                    } else {
                                        $existingItem[$value->issue_type . '_stock_qty'] = $itemData[$value->issue_type . '_stock_qty'];
                                    }
                                    $existingItem['required_qty'] = $itemData['required_qty'];
                                    $itemExists = true;
                                    break;
                                }
                            }

                            if (!$itemExists) {
                                $itemStock[$value->item->category->id]['items'][] = $itemData;
                            }
                        } else {
                            $itemStock[$value->item->category->id]['name'] = $value->item->category->category_name;
                            $itemStock[$value->item->category->id]['items'][] = $itemData;
                        }
                    } else {
                        $bomMeta = BOMMeta::select('quantity')->where('item_group_id',  $value->itemGroup->id)->where('boms_id', $salesMaster->bom_id)->first();
                        $unit = $value->itemGroup->unit->unit_name;
                        $item_g_arr = [
                            'id' => $value->itemGroup->id,
                            'issue_type' =>  $value->issue_type,
                            'name' => getItemGropName($value, 1),
                            'unit' => $unit,
                            'stock_qty' => $value->quantity ?? 0,
                            'required_qty' => (isset($bomMeta) && !is_null($bomMeta->quantity)) ? $bomMeta->quantity :  0
                        ];
                        if ($value->itemGroup->group_type == 'panel') {
                            $item_g_arr['panel_watt'] = $value->itemGroup->panel_watt->name;

                            $installationPanelsAlready = InstallationPenalMaster::select('sales_master_id', 'installation_id', 'stock_type', 'item_group_id', 'use_stock', 'model_number', 'total_kw')->where('stock_type', $value->issue_type)->where('item_group_id', $value->itemGroup->id)->where('sales_master_id', $id)->get();
                            if ($installationPanelsAlready->count() > 0) {
                                foreach ($installationPanelsAlready as $pmKey => $pmVal):
                                    $panelsData = InstallationPenal::where('installation_id', $pmVal->installation_id)->where('item_group_id', $pmVal->item_group_id)->get();
                                    $pmVal->data = $panelsData;
                                endforeach;
                                $tempItem_g_arr = $item_g_arr;
                                $tempItem_g_arr['data'] = $installationPanelsAlready;
                                $itemGroupStock[$value->itemGroup->group_type . '_already'][] = $tempItem_g_arr;
                            }
                        } else {
                            $installationInverter = InstallationInvater::select('item_group_id', 'stock_type', 'invater_id', 'invater_kw', 'model_number', 'serial_no_of_inverter', 'voltage')->where('stock_type', $value->issue_type)->where('item_group_id', $value->itemGroup->id)->where('sales_master_id', $id)->get();
                            if ($installationInverter->count() > 0) {

                                $tempItem_g_arr = $item_g_arr;
                                $tempItem_g_arr['data'] = $installationInverter;
                                $itemGroupStock[$value->itemGroup->group_type . '_already'][] = $tempItem_g_arr;
                            }
                        }
                        $itemGroupStock[$value->itemGroup->group_type][] = $item_g_arr;
                    }
                endforeach;
            }
            $itemStock = array_values($itemStock);

            $installation = Installation::select('*')->where('sales_master_id', $id)->first();
            if (!is_null($installation)) {
                return view('admin.installation.edit', compact('itemStock', 'itemGroupStock', 'installation'));
            } else {
                return view('admin.installation.create', compact('itemStock', 'itemGroupStock'));
            }
        } else {
            return view('admin.installation.create');
        }
    }

    /* WEB :: Latest with ERP  */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sales_master_id' => 'required',
        ], [
            'sales_master_id.required' => 'Select sales order',
        ]);
        if ($validator->fails()) {
            $response = ['status_code' => 200, 'message' => 'Please input proper data.', 'errors' => $validator->errors()];
            return response()->json($response);
        }
        DB::beginTransaction();
        try {
            if (isset($request->id) && $request->id != '') {
                $qry = Installation::where('id', $request->id)->first();
                $response = ['status_code' => 200, 'data' => route('sales-master.index'), 'message' => 'Installation updated successfully.'];
            } else {
                $qry = new Installation();
                $response = ['status_code' => 200, 'data' => route('sales-master.index'), 'message' => 'Installation added successfully.'];
            }
            if ($request->status == "2") {
                $salesMaster = SalesMaster::where('id', $request['sales_master_id'])->first();
                $salesMaster->installation_done = '1';
                $consumer_name = $salesMaster->consumer_name;
                $salesMaster->save();
            } else {
                $salesMaster = SalesMaster::where('id', $request['sales_master_id'])->first();
                $consumer_name = $salesMaster->consumer_name;
            }

            $qry->sales_master_id = $request->sales_master_id;
            $qry->date = date('Y-m-d');
            $qry->form_type = 'new';
            $qry->dc_side = $request->dc_side_earthing;
            $qry->ac_side = $request->ac_side_earthing;
            $qry->la_earthing = $request->la_earthing;
            $qry->phase_to_earth = $request->phase_to_earth;
            $qry->phase_to_phase = $request->phase_to_phase;
            $qry->status = !empty($request->status) ? $request->status : 0;
            $qry->save();
            $qry = Installation::where('id', $qry->id)->first();
            $item_group = [];
            if (isset($request->panel_item) && count($request->panel_item) > 0) {
                foreach ($request->panel_item as $panelKey => $panelValue):
                    /* stock reverse */
                    if (isset($request->id) && $request->id != '') {
                        $oldPanels = InstallationPenalMaster::where('installation_id', $request->id)->get();
                        if (!is_null($oldPanels)) {
                            foreach ($oldPanels as $k => $v):
                                if ($v->stock_type == 'project') {
                                    $checkStock = ProjectWiseStock::where([['sales_master_id', $request->sales_master_id], ['item_group_id', $v->item_group_id]])
                                        ->first();
                                } else {
                                    $checkStock = ProjectWiseStock::where([['installer_id', Auth::id()], ['item_group_id', $v->item_group_id]])
                                        ->first();
                                }
                                if (!is_null($checkStock)) {
                                    $checkStock->quantity += $v->use_stock;
                                    $checkStock->save();
                                    $stockTransactionP = new ProjectWiseStockHistory();
                                    $stockTransactionP->installation_id = $qry->id;
                                    $stockTransactionP->sales_master_id = $request->sales_master_id;
                                    $stockTransactionP->project_wise_stock_id = $checkStock->id;
                                    $stockTransactionP->quantity = $v->use_stock;
                                    $stockTransactionP->type = 'credit';
                                    $stockTransactionP->remark = $consumer_name . ' (Edited)';
                                    $stockTransactionP->save();

                                    /* To Delete Stock History */
                                    ProjectWiseStockHistory::where('installation_id', $qry->id)->where('sales_master_id', $request->sales_master_id)->where('project_wise_stock_id', $checkStock->id)->delete();
                                    /* / To Delete Stock History */
                                }
                            endforeach;
                            InstallationPenalMaster::where('installation_id', $request->id)->delete();
                        }

                        $oldPanelsrno = InstallationPenal::where('installation_id', $request->id)->get();
                        if (!is_null($oldPanelsrno)) {
                            foreach ($oldPanelsrno as $k => $v):
                                $existingSerialNumber = SerialNumber::where('serial_number', $v->serial_no)->whereNull('deleted_at')->first();
                                if (!is_null($existingSerialNumber)) {
                                    $existingSerialNumber->status = 'available';
                                    $existingSerialNumber->save();
                                }
                            endforeach;
                            InstallationPenal::where('installation_id', $request->id)->delete();
                        }
                    }
                    /*  / stock reverse */

                    $temp = [];
                    $p = explode('~', $panelValue);
                    if ($panelKey == 0) {
                        $item_groups = ItemGroup::where('id', $p[1])->first();
                        $qry->penal_company_id = $item_groups->panel_company_id;
                        $qry->penal_model_no = $request->panel_model_number[$panelKey];
                        $qry->penal_type_id = $item_groups->panel_type_id;
                        $qry->penal_watt_id = $item_groups->panel_watt_id;
                        $qry->penal_nos = $request->no_of_modules[$panelKey];
                        $qry->total_kv = $request->total_kw[$panelKey];
                    }
                    if (isset($request->panel_sr_module) && isset($request->panel_sr_module[$panelKey]) && count($request->panel_sr_module[$panelKey]) > 0) {
                        foreach ($request->panel_sr_module[$panelKey] as $srKey => $srValue):
                            $temp[] = $srValue;
                            $penal = new InstallationPenal();
                            $penal->sales_master_id = $request->sales_master_id;
                            $penal->installation_id = $qry->id;
                            $penal->item_group_id = $p[1];
                            $penal->serial_no = $srValue;
                            $penal->stock_type = $p[0];
                            $penal->save();

                            $existingSerialNumber = SerialNumber::where('serial_number', $srValue)->whereNull('deleted_at')->first();
                            if (!is_null($existingSerialNumber)) {
                                $existingSerialNumber->status = 'sold';
                                $existingSerialNumber->save();
                            }

                        endforeach;
                        $penalMaster = new InstallationPenalMaster();
                        $penalMaster->sales_master_id = $request->sales_master_id;
                        $penalMaster->installation_id = $qry->id;
                        $penalMaster->stock_type = $p[0];
                        $penalMaster->item_group_id = $p[1];
                        $penalMaster->use_stock = $request->no_of_modules[$panelKey];
                        $penalMaster->model_number = $request->panel_model_number[$panelKey];
                        $penalMaster->total_kw = $request->total_kw[$panelKey];
                        $penalMaster->save();
                        if ($p[0] == 'project') {
                            $checkStock = ProjectWiseStock::where([['sales_master_id', $request->sales_master_id], ['item_group_id', $p[1]]])
                                ->first();
                        } else {
                            $checkStock = ProjectWiseStock::where([['installer_id', Auth::id()], ['item_group_id', $p[1]]])
                                ->first();
                        }
                        if (!is_null($checkStock) && $request->no_of_modules[$panelKey] > 0) {

                            $checkStock->quantity -= $request->no_of_modules[$panelKey];
                            $checkStock->save();

                            $stockTransactionP = new ProjectWiseStockHistory();
                            $stockTransactionP->installation_id = $qry->id;
                            $stockTransactionP->sales_master_id = $request->sales_master_id;
                            $stockTransactionP->project_wise_stock_id = $checkStock->id;
                            $stockTransactionP->quantity = $request->no_of_modules[$panelKey];
                            $stockTransactionP->type = 'Debit';
                            $stockTransactionP->remark = $consumer_name . ' (Added)';
                            $stockTransactionP->save();
                        }
                    }
                    $item_group['panels'][] = [
                        'panel_item_id' => $p[1],
                        'panel_stock_type' => $p[0],
                        'no_of_modules' => $request->no_of_modules[$panelKey],
                        'panel_model_number' => $request->panel_model_number[$panelKey],
                        'total_kw' => $request->total_kw[$panelKey],
                        'sr_number' => $temp,
                    ];
                endforeach;
            }
            $inverter = 0;
            if (isset($request->inverter_item) && count($request->inverter_item) > 0) {
                foreach ($request->inverter_item as $inverterKey => $inverterValue):
                    $inverter += $request->no_of_inverter[$inverterKey];
                    $temp = [];
                    $inv = explode('~', $inverterValue);
                    if (isset($request->inverter_model_number) && isset($request->inverter_model_number[$inverterKey]) && count($request->inverter_model_number) > 0) {

                        /* stock reverse */
                        if (isset($request->id) && $request->id != '') {
                            $oldInv = $qry->no_of_inverter;

                            $oldInvatersForSr = InstallationInvater::where('installation_id', $request->id)->get();
                            if (!is_null($oldInvatersForSr)) {
                                foreach ($oldInvatersForSr as $k => $v):
                                    $existingSerialNumber = SerialNumber::where('serial_number', $v->serial_no_of_inverter)->whereNull('deleted_at')->first();
                                    if (!is_null($existingSerialNumber)) {
                                        $existingSerialNumber->status = 'available';
                                        $existingSerialNumber->save();
                                    }
                                endforeach;
                            }
                            $oldInvaters = InstallationInvater::where('installation_id', $request->id)->groupBy('item_group_id', 'stock_type')->get();
                            if (!is_null($oldInvaters)) {
                                foreach ($oldInvaters as $k => $v):
                                    if ($v->stock_type == 'project') {
                                        $checkStock = ProjectWiseStock::where([['sales_master_id', $request->sales_master_id], ['item_group_id', $v->item_group_id]])
                                            ->first();
                                    } else {
                                        $checkStock = ProjectWiseStock::where([['installer_id', Auth::id()], ['item_group_id', $v->item_group_id]])
                                            ->first();
                                    }
                                    if (!is_null($checkStock)) {
                                        $checkStock->quantity += $oldInv;
                                        $checkStock->save();
                                        $stockTransactionP = new ProjectWiseStockHistory();
                                        $stockTransactionP->installation_id = $qry->id;
                                        $stockTransactionP->sales_master_id = $request->sales_master_id;
                                        $stockTransactionP->project_wise_stock_id = $checkStock->id;
                                        $stockTransactionP->quantity = $oldInv;
                                        $stockTransactionP->type = 'credit';
                                        $stockTransactionP->remark = $consumer_name . ' (Edited)';
                                        $stockTransactionP->save();

                                        /* To Delete Stock History */
                                        ProjectWiseStockHistory::where('installation_id', $qry->id)->where('sales_master_id', $request->sales_master_id)->where('project_wise_stock_id', $checkStock->id)->delete();
                                        /* / To Delete Stock History */
                                    }
                                endforeach;

                                InstallationInvater::where('installation_id', $request->id)->delete();
                            }
                        }
                        /* / stock reverse */

                        foreach ($request->inverter_model_number[$inverterKey] as $invKey => $invValue):
                            $temp[] = [
                                'inverter_model_number' => $invValue,
                                'inverter_sr_number' => $request->inverter_sr_number[$inverterKey][$invKey],
                                'inverter_voltage' => $request->inverter_voltage[$inverterKey][$invKey]
                            ];
                            $item_group_inv = ItemGroup::where('id', $inv[1])->first();
                            $invater = new InstallationInvater();
                            $invater->sales_master_id = $request->sales_master_id;
                            $invater->installation_id = $qry->id;
                            $invater->item_group_id = $inv[1];
                            $invater->stock_type = $inv[0];
                            $invater->invater_id =  $item_group_inv->inveter_company_id;
                            $invater->model_number = $invValue;
                            $invater->invater_kw = $item_group_inv->inveter_kw;
                            $invater->serial_no_of_inverter = $request->inverter_sr_number[$inverterKey][$invKey];
                            $invater->voltage =  $request->inverter_voltage[$inverterKey][$invKey];
                            $invater->save();

                            $existingSerialNumber = SerialNumber::where('serial_number', $request->inverter_sr_number[$inverterKey][$invKey])->whereNull('deleted_at')->first();
                            if (!is_null($existingSerialNumber)) {
                                $existingSerialNumber->status = 'sold';
                                $existingSerialNumber->save();
                            }

                        endforeach;
                    }
                    $item_group['inverters'][] = [
                        'inverter_item_id' => $inv[1],
                        'inverter_stock_type' => $inv[0],
                        'no_of_inverter' => $request->no_of_inverter[$inverterKey],
                        'data' => $temp
                    ];
                    if ($inv[0] == 'project') {
                        $checkStock = ProjectWiseStock::where([['sales_master_id', $request->sales_master_id], ['item_group_id', $inv[1]]])
                            // ->where('quantity','>=',$request->no_of_inverter[$inverterKey])
                            ->first();
                    } else {
                        $checkStock = ProjectWiseStock::where([['installer_id', Auth::id()], ['item_group_id', $inv[1]]])
                            // ->where('quantity','>=',$request->no_of_inverter[$inverterKey])
                            ->first();
                    }

                    if (!is_null($checkStock) && $request->no_of_inverter[$inverterKey] > 0) {
                        $checkStock->quantity -= $request->no_of_inverter[$inverterKey];
                        $checkStock->save();
                        $stockTransactionP = new ProjectWiseStockHistory();
                        $stockTransactionP->installation_id = $qry->id;
                        $stockTransactionP->sales_master_id = $request->sales_master_id;
                        $stockTransactionP->project_wise_stock_id = $checkStock->id;
                        $stockTransactionP->quantity = $request->no_of_inverter[$inverterKey];
                        $stockTransactionP->type = 'Debit';
                        $stockTransactionP->remark = $consumer_name . ' (Added)';
                        $stockTransactionP->save();
                    }
                endforeach;
                $qry->no_of_inverter = $inverter;
            }
            if (isset($request->item_ids) && count($request->item_ids) > 0) {

                /* stock reverse */
                if (isset($request->id) && $request->id != '') {
                    $oldItems = InstallationItems::where('installation_id', $request->id)->get();
                    if (!is_null($oldItems)) {
                        foreach ($oldItems as $k => $v):
                            if ($v->stock_type == 'project') {
                                $checkStock = ProjectWiseStock::where([['sales_master_id', $request->sales_master_id], ['item_id', $v->item_id]])
                                    ->first();
                            } else {
                                $checkStock = ProjectWiseStock::where([['installer_id', Auth::id()], ['item_id', $v->item_id]])
                                    ->first();
                            }
                            if (!is_null($checkStock)) {
                                $checkStock->quantity += $v->use_stock;
                                $checkStock->save();
                                $stockTransactionP = new ProjectWiseStockHistory();
                                $stockTransactionP->installation_id = $qry->id;
                                $stockTransactionP->sales_master_id = $request->sales_master_id;
                                $stockTransactionP->project_wise_stock_id = $checkStock->id;
                                $stockTransactionP->quantity = $v->use_stock;
                                $stockTransactionP->type = 'credit';
                                $stockTransactionP->remark = $consumer_name . ' (Edited)';
                                $stockTransactionP->save();

                                /* To Delete Stock History */
                                ProjectWiseStockHistory::where('installation_id', $qry->id)->where('sales_master_id', $request->sales_master_id)->where('project_wise_stock_id', $checkStock->id)->delete();
                                /* / To Delete Stock History */
                            }
                        endforeach;
                        InstallationItems::where('installation_id', $request->id)->delete();
                    }
                }
                /* / stock reverse */

                foreach ($request->item_ids as $itemKey => $itemValue):
                    if (isset($request->use_project_stock) && isset($request->use_project_stock[$itemKey]) && $request->use_project_stock[$itemKey] != null) {
                        $items[] = [
                            'id' => $itemValue,
                            'issue_type' => 'project',
                            'usage' => $request->use_project_stock[$itemKey] ?? 0,
                        ];
                        $itemsSave = new InstallationItems();
                        $itemsSave->sales_master_id = $request->sales_master_id;
                        $itemsSave->installation_id = $qry->id;
                        $itemsSave->stock_type = 'project';
                        $itemsSave->item_id = $itemValue;
                        $itemsSave->use_stock = $request->use_project_stock[$itemKey] ?? 0;
                        $itemsSave->save();
                        $checkStock = ProjectWiseStock::where([['sales_master_id', $request->sales_master_id], ['item_id', $itemValue]])
                            // ->where('quantity','>=',$request->use_project_stock[$itemKey])
                            ->first();
                        if (!is_null($checkStock) && $request->use_project_stock[$itemKey] > 0) {
                            $checkStock->quantity -= $request->use_project_stock[$itemKey];
                            $checkStock->save();
                            $stockTransactionP = new ProjectWiseStockHistory();
                            $stockTransactionP->installation_id = $qry->id;
                            $stockTransactionP->sales_master_id = $request->sales_master_id;
                            $stockTransactionP->project_wise_stock_id = $checkStock->id;
                            $stockTransactionP->quantity = $request->use_project_stock[$itemKey];
                            $stockTransactionP->type = 'Debit';
                            $stockTransactionP->remark = $consumer_name . ' (Added)';
                            $stockTransactionP->save();
                        }
                    }
                    if (isset($request->use_installer_stock) && isset($request->use_installer_stock[$itemKey]) && $request->use_installer_stock[$itemKey] != null) {
                        $items[] = [
                            'id' => $itemValue,
                            'issue_type' => 'installer',
                            'usage' => $request->use_installer_stock[$itemKey] ?? 0,
                        ];
                        $itemsSave = new InstallationItems();
                        $itemsSave->sales_master_id = $request->sales_master_id;
                        $itemsSave->installation_id = $qry->id;
                        $itemsSave->stock_type = 'installer';
                        $itemsSave->item_id = $itemValue;
                        $itemsSave->use_stock = $request->use_installer_stock[$itemKey] ?? 0;
                        $itemsSave->save();
                        $checkStock = ProjectWiseStock::where([['installer_id', Auth::id()], ['item_id', $itemValue]])
                            // ->where('quantity','>=',$request->no_of_inverter[$inverterKey])
                            ->first();
                        if (!is_null($checkStock) && $request->use_installer_stock[$itemKey] > 0) {
                            $checkStock->quantity -= $request->use_installer_stock[$itemKey];
                            $checkStock->save();
                            $stockTransactionP = new ProjectWiseStockHistory();
                            $stockTransactionP->installation_id = $qry->id;
                            $stockTransactionP->sales_master_id = $request->sales_master_id;
                            $stockTransactionP->project_wise_stock_id = $checkStock->id;
                            $stockTransactionP->quantity = $request->use_installer_stock[$itemKey];
                            $stockTransactionP->type = 'Debit';
                            $stockTransactionP->remark = $consumer_name . ' (Added)';
                            $stockTransactionP->save();
                        }
                    }
                endforeach;
            }


            $qry->save();
            if (!empty($request->panel) && count($request->panel) > 0) {
                $penal_image_store = public_path('uploads/penal/');
                $penal_medium_image_store = public_path('uploads/penal/thumbnail/');
                if (!file_exists($penal_image_store)) {
                    mkdir($penal_image_store, 0777, true);
                }
                if (!file_exists($penal_medium_image_store)) {
                    mkdir($penal_medium_image_store, 0777, true);
                }
                foreach ($request->panel as $key => $value) {
                    $filename = '';
                    if (!empty($value['file'])) {
                        $image_64 = $value['file'];
                        $images = Image::make($image_64->getRealPath());
                        $filename = str_ireplace(" ", "-", 'panel') . '-' . rand(1111, 9999) . time() . '.webp';
                        $images->resize(150, 150, function ($constraint) {
                            $constraint->aspectRatio();
                        })->save($penal_medium_image_store . '/' . $filename, 85);
                        $image_64->move($penal_image_store, $filename, 100);
                        $penal_param = [
                            'installation_id' => $qry->id,
                            'image' => $filename,
                        ];
                        PenalImage::create($penal_param);
                    }
                }
            }
            if (!empty($request->inverter) && count($request->inverter) > 0) {
                $invater_image_store = public_path('uploads/invater/');
                $invater_medium_image_store = public_path('uploads/invater/thumbnail/');
                if (!file_exists($invater_image_store)) {
                    mkdir($invater_image_store, 0777, true);
                }
                if (!file_exists($invater_medium_image_store)) {
                    mkdir($invater_medium_image_store, 0777, true);
                }
                foreach ($request->inverter as $key => $value) {
                    $filename = '';
                    if (!empty($value['file'])) {
                        $image_64 = $value['file'];
                        $images = Image::make($image_64->getRealPath());
                        $filename = str_ireplace(" ", "-", 'inverter') . '-' . rand(1111, 9999) . time() . '.webp';
                        $images->resize(150, 150, function ($constraint) {
                            $constraint->aspectRatio();
                        })->save($invater_medium_image_store . '/' . $filename, 85);
                        $image_64->move($invater_image_store, $filename, 100);
                        $invater_param = [
                            'installation_id' => $qry->id,
                            'image' => $filename,
                        ];
                        InvaterImage::create($invater_param);
                    }
                }
            }

            if ($request->status == "2") {
                $salesMaster = SalesMaster::where('id', $request['sales_master_id'])->first();
                $salesMaster->installation_done = '1';
                $salesMaster->save();
            }

            DB::commit();
            $response = ['status_code' => 200, 'data' => route('sales-master.index'), 'message' => 'Installation added successfully.'];
            return response()->json($response);
        } catch (\Exception $e) {
            DB::rollback();
            $response = ['status_code' => 500, 'message' => 'Something went wrong. Please try again.'];
            return response()->json($response);
        }
    }

    /* / WEB :: Latest with ERP  */

    public function showInstallationData(Request $request)
    {
        $installation = Installation::select('id', 'sales_master_id', 'date', 'penal_company_id', 'penal_model_no', 'penal_type_id', 'penal_watt_id', 'penal_nos', 'total_kv', 'type_of_inverter', 'no_of_inverter', 'structure_40_40_2mm', 'structure_60_40_2mm', 'structure_80_40_2mm', 'structure_others', 'dc_side', 'ac_side', 'la_earthing', 'phase_to_earth', 'phase_to_phase', 'cable_dc', 'cable_ac', 'cable_la', 'cable_earthing', 'status')->with('panelwatt', 'panelcompany', 'paneltype', 'penalImage', 'invaterImages', 'installationPenals', 'invater', 'invater.company')
            ->where('sales_master_id', $request->sales_master_id)->first();

        if (!is_null($installation)) {
            $installation->date = date('d-m-Y', strtotime($installation->date));
            if (!is_null($installation)) {
                if ($installation->penalImage->count() > 0) {
                    foreach ($installation->penalImage as $key => $value) :
                        $penal_image_store = asset('uploads/penal/');
                        $penal_medium_image_store = asset('uploads/penal/thumbnail/');

                        $value->img = $penal_image_store . '/' . $value->image;
                        $value->thumbnail = $penal_medium_image_store . '/' . $value->image;
                        unset($value->image);
                        unset($value->installation_id);
                    endforeach;
                }
                if ($installation->invaterImages->count() > 0) {
                    foreach ($installation->invaterImages as $key => $value) :
                        $invater_image_store = asset('uploads/invater/');
                        $invater_medium_image_store = asset('uploads/invater/thumbnail/');

                        $value->img = $invater_image_store . '/' . $value->image;
                        $value->thumbnail = $invater_medium_image_store . '/' . $value->image;
                        unset($value->image);
                        unset($value->installation_id);
                    endforeach;
                }
            }
        }

        $response = ['status' => true, 'message' => 'installation data', 'installation' => $installation];
        return response($response, 200);
    }

    public function deleteImage(Request $request)
    {
        if ($request->type == 'panel') {
            $res = PenalImage::where('id', $request->id)->delete();
        }
        if ($request->type == 'invater') {
            $res = InvaterImage::where('id', $request->id)->delete();
        }
        if ($res) {
            $response = ['status' => true, 'message' => 'Deleted Successfully'];
            return response($response, 200);
        } else {
            $response = ['status' => false, 'message' => 'Something went wrong. Please try again.'];
            return response($response, 500);
        }
    }

    /* API :: Latest with ERP  */
    public function installationAddUpdateNew(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'sales_master_id' => 'required',
        ], [
            'sales_master_id.required' => 'Select Sales order',
        ]);

        if ($validator->fails()) {
            $response = ['status' => false, 'message' => 'Please input proper data.', 'errors' => $validator->errors()];
            return response($response, 200);
        }
        DB::beginTransaction();
        try {
            if (isset($request['id']) && $request['id'] != '' && $request['id'] != 0) {
                $installation = Installation::where('id', $request['id'])->first();
            } else {
                $installation = new Installation();
            }

            $installation->sales_master_id = $request['sales_master_id'];
            $installation->date = date('Y-m-d');
            $installation->form_type = 'new';
            $installation->dc_side = $request['earthing']['dc_earthing'];
            $installation->ac_side = $request['earthing']['ac_earthing'];
            $installation->la_earthing = $request['earthing']['la_earthing'];
            $installation->phase_to_earth = $request['earthing']['phase_to_phase_earthing'];
            $installation->phase_to_phase = $request['earthing']['phase_to_earth_earthing'];
            $installation->status = (!empty($request['status']) && $request['status'] == "1") ? '2' : '1';
            $installation->save();

            if ($request['status'] == "1") {
                $salesMaster = SalesMaster::where('id', $request['sales_master_id'])->first();
                $salesMaster->installation_done = '1';
                $consumer_name = $salesMaster->consumer_name;
                $salesMaster->save();
            } else {
                $salesMaster = SalesMaster::where('id', $request['sales_master_id'])->first();
                $consumer_name = $salesMaster->consumer_name;
            }

            $qry = Installation::where('id', $installation['id'])->first();

            if (!empty($request['images']['panel_images']) && count($request['images']['panel_images']) > 0) {

                // / Upload image photos /
                $penal_image_store = public_path('uploads/penal/');
                $penal_medium_image_store = public_path('uploads/penal/thumbnail/');

                // / Check dir is exists. if not, create new one /
                if (!file_exists($penal_image_store)) {
                    mkdir($penal_image_store, 0777, true);
                }
                if (!file_exists($penal_medium_image_store)) {
                    mkdir($penal_medium_image_store, 0777, true);
                }
                foreach ($request['images']['panel_images'] as $key => $value) {
                    $penalimg = '';
                    if (!empty($value)) {
                        $image_64 = $value;
                        $extension = explode('/', explode(':', substr($image_64, 0, strpos($image_64, ';')))[1])[1];
                        $replace = substr($image_64, 0, strpos($image_64, ',') + 1);
                        $image = str_replace($replace, '', $image_64);
                        $image = str_replace(' ', '+', $image);
                        $penalimg = sha1(time() . uniqid()) . '.' . $extension;
                        File::put($penal_image_store . '/' . $penalimg, base64_decode($image));
                        $images = Image::make($image)->insert($image);
                        $images->resize(150, 150, function ($constraint) {
                            $constraint->aspectRatio();
                        })->save($penal_medium_image_store . '/' . $penalimg, 80);
                    }
                    $penal_param = [
                        'installation_id' => $installation['id'],
                        'image' => $penalimg,
                    ];
                    PenalImage::create($penal_param);
                }
            }

            if (!empty($request['images']['inverter_images']) && count($request['images']['inverter_images']) > 0) {

                $invater_image_store = public_path('uploads/invater/');
                $invater_medium_image_store = public_path('uploads/invater/thumbnail/');

                // / Check dir is exists. if not, create new one /
                if (!file_exists($invater_image_store)) {
                    mkdir($invater_image_store, 0777, true);
                }
                if (!file_exists($invater_medium_image_store)) {
                    mkdir($invater_medium_image_store, 0777, true);
                }
                foreach ($request['images']['inverter_images'] as $key => $value) {
                    $invaterimg = '';
                    if (!empty($value)) {
                        $image_64 = $value;
                        $extension = explode('/', explode(':', substr($image_64, 0, strpos($image_64, ';')))[1])[1];
                        $replace = substr($image_64, 0, strpos($image_64, ',') + 1);
                        $image = str_replace($replace, '', $image_64);
                        $image = str_replace(' ', '+', $image);
                        $invaterimg = sha1(time() . uniqid()) . '.' . $extension;
                        File::put($invater_image_store . '/' . $invaterimg, base64_decode($image));
                        $images = Image::make($image)->insert($image);
                        $images->resize(150, 150, function ($constraint) {
                            $constraint->aspectRatio();
                        })->save($invater_medium_image_store . '/' . $invaterimg, 80);
                    }
                    $invater_param = [
                        'installation_id' => $installation['id'],
                        'image' => $invaterimg,
                    ];
                    InvaterImage::create($invater_param);
                }
            }

            $data = [];
            if (isset($request['item']) && count($request['item']) > 0) {
                /* stock reverse */
                if (isset($request['id']) && $request['id'] != '' && $request['id'] != 0) {
                    $oldItems = InstallationItems::where('installation_id', $request['id'])->get();
                    if (!is_null($oldItems)) {
                        foreach ($oldItems as $k => $v):
                            if ($v->stock_type == 'project') {
                                $checkStock = ProjectWiseStock::where([['sales_master_id', $request['sales_master_id']], ['item_id', $v->item_id]])
                                    ->first();
                            } else {
                                $checkStock = ProjectWiseStock::where([['installer_id', Auth::id()], ['item_id', $v->item_id]])
                                    ->first();
                            }
                            if (!is_null($checkStock)) {
                                $checkStock->quantity += $v->use_stock;
                                $checkStock->save();
                                $stockTransactionP = new ProjectWiseStockHistory();
                                $stockTransactionP->installation_id = $qry->id;
                                $stockTransactionP->sales_master_id = $request['sales_master_id'];
                                $stockTransactionP->project_wise_stock_id = $checkStock->id;
                                $stockTransactionP->quantity = $v->use_stock;
                                $stockTransactionP->type = 'credit';
                                $stockTransactionP->remark = $consumer_name . ' (Edited)';
                                $stockTransactionP->save();

                                /* To Delete Stock History */
                                ProjectWiseStockHistory::where('installation_id', $qry->id)->where('sales_master_id', $request['sales_master_id'])->where('project_wise_stock_id', $checkStock->id)->delete();
                                /* / To Delete Stock History */
                            }
                        endforeach;
                        InstallationItems::where('installation_id', $request['id'])->delete();
                    }
                }
                /* / stock reverse */

                foreach ($request['item'] as $catKey => $catValue):
                    foreach ($catValue['items'] as $itemKey => $itemValue):

                        $data[] = $itemValue;
                        if ($itemValue['project_usage'] != 0 && $itemValue['project_usage'] != null) {
                            $itemsSave = new InstallationItems();
                            $itemsSave->sales_master_id = $request['sales_master_id'];
                            $itemsSave->installation_id = $installation['id'];
                            $itemsSave->stock_type = 'project';
                            $itemsSave->item_id = $itemValue['id'];
                            $itemsSave->use_stock = $itemValue['project_usage'];
                            $itemsSave->save();
                            $checkStock = ProjectWiseStock::where([['sales_master_id', $request['sales_master_id']], ['item_id', $itemValue['id']]])
                                ->first();
                            if (!is_null($checkStock) && $itemValue['project_usage'] > 0) {
                                $checkStock->quantity -= $itemValue['project_usage'];
                                $checkStock->save();
                                $stockTransactionP = new ProjectWiseStockHistory();
                                $stockTransactionP->sales_master_id = $request['sales_master_id'];
                                $stockTransactionP->installation_id = $installation['id'];
                                $stockTransactionP->project_wise_stock_id = $checkStock->id;
                                $stockTransactionP->quantity = $itemValue['project_usage'];
                                $stockTransactionP->type = 'Debit';
                                $stockTransactionP->remark = $consumer_name . ' (Added)';
                                $stockTransactionP->save();
                            }
                        }
                        if ($itemValue['installer_usage'] != 0 && $itemValue['installer_usage'] != null) {
                            $itemsSave = new InstallationItems();
                            $itemsSave->sales_master_id = $request['sales_master_id'];
                            $itemsSave->installation_id = $installation['id'];
                            $itemsSave->stock_type = 'installer';
                            $itemsSave->item_id = $itemValue['id'];
                            $itemsSave->use_stock = $itemValue['installer_usage'];
                            $itemsSave->save();
                            $checkStock = ProjectWiseStock::where([['installer_id', Auth::id()], ['item_id', $itemValue['id']]])
                                ->first();
                            if (!is_null($checkStock) && $itemValue['installer_usage'] > 0) {
                                $checkStock->quantity -= $itemValue['installer_usage'];
                                $checkStock->save();
                                $stockTransactionP = new ProjectWiseStockHistory();
                                $stockTransactionP->sales_master_id = $request['sales_master_id'];
                                $stockTransactionP->installation_id = $installation['id'];
                                $stockTransactionP->project_wise_stock_id = $checkStock['id'];
                                $stockTransactionP->quantity = $itemValue['installer_usage'];
                                $stockTransactionP->type = 'Debit';
                                $stockTransactionP->remark = $consumer_name . ' (Added)';
                                $stockTransactionP->save();
                            }
                        }

                    endforeach;
                endforeach;
            }

            if (isset($request['item_group']['panels']) && count($request['item_group']['panels']) > 0) {

                /* stock reverse */
                if (isset($request['id']) && $request['id'] != '' && $request['id'] != 0) {
                    $oldPanels = InstallationPenalMaster::where('installation_id', $request['id'])->get();
                    if (!is_null($oldPanels)) {
                        foreach ($oldPanels as $k => $v):
                            if ($v->stock_type == 'project') {
                                $checkStock = ProjectWiseStock::where([['sales_master_id', $request['sales_master_id']], ['item_group_id', $v->item_group_id]])
                                    ->first();
                            } else {
                                $checkStock = ProjectWiseStock::where([['installer_id', Auth::id()], ['item_group_id', $v->item_group_id]])
                                    ->first();
                            }
                            if (!is_null($checkStock)) {
                                $checkStock->quantity += $v->use_stock;
                                $checkStock->save();
                                $stockTransactionP = new ProjectWiseStockHistory();
                                $stockTransactionP->installation_id = $qry->id;
                                $stockTransactionP->sales_master_id = $request['sales_master_id'];
                                $stockTransactionP->project_wise_stock_id = $checkStock->id;
                                $stockTransactionP->quantity = $v->use_stock;
                                $stockTransactionP->type = 'credit';
                                $stockTransactionP->remark = $consumer_name . ' (Edited)';
                                $stockTransactionP->save();

                                 /* To Delete Stock History */
                                 ProjectWiseStockHistory::where('installation_id', $qry->id)->where('sales_master_id', $request['sales_master_id'])->where('project_wise_stock_id', $checkStock->id)->delete();
                                 /* / To Delete Stock History */
                            }
                        endforeach;
                        InstallationPenalMaster::where('installation_id', $request['id'])->delete();
                    }

                    $oldPanelsrno = InstallationPenal::where('installation_id', $request['id'])->get();
                    if (!is_null($oldPanelsrno)) {
                        foreach ($oldPanelsrno as $k => $v):
                            $existingSerialNumber = SerialNumber::where('serial_number', $v->serial_no)->whereNull('deleted_at')->first();
                            if (!is_null($existingSerialNumber)) {
                                $existingSerialNumber->status = 'available';
                                $existingSerialNumber->save();
                            }
                        endforeach;
                        InstallationPenal::where('installation_id', $request['id'])->delete();
                    }
                }
                /*  / stock reverse */

                foreach ($request['item_group']['panels'] as $panelKey => $panelValue):
                    if ($panelKey == 0) {
                        $item_groups = ItemGroup::where('id', $panelValue['panel_item_id'])->first();
                        $qry->penal_company_id = $item_groups->panel_company_id;
                        $qry->penal_model_no = $panelValue['panel_model_number'];
                        $qry->penal_type_id = $item_groups->panel_type_id;
                        $qry->penal_watt_id = $item_groups->panel_watt_id;
                        $qry->penal_nos = $panelValue['no_of_modules'];
                        $qry->total_kv = $panelValue['total_kw'];
                    }

                    if (isset($panelValue['sr_number']) && isset($panelValue['sr_number']) && count($panelValue['sr_number']) > 0) {

                        foreach ($panelValue['sr_number'] as $srKey => $srValue):
                            $temp[] = $srValue;
                            $penal = new InstallationPenal();
                            $penal->sales_master_id = $request['sales_master_id'];
                            $penal->installation_id = $qry->id;
                            $penal->item_group_id = $panelValue['panel_item_id'];
                            $penal->serial_no = $srValue;
                            $penal->stock_type = $panelValue['panel_stock_type'];
                            $penal->save();

                            $existingSerialNumber = SerialNumber::where('serial_number', $srValue)->whereNull('deleted_at')->first();
                            if (!is_null($existingSerialNumber)) {
                                $existingSerialNumber->status = 'sold';
                                $existingSerialNumber->save();
                            }
                        endforeach;

                        $penalMaster = new InstallationPenalMaster();
                        $penalMaster->sales_master_id = $request['sales_master_id'];
                        $penalMaster->installation_id = $qry->id;
                        $penalMaster->stock_type = $panelValue['panel_stock_type'];
                        $penalMaster->item_group_id = $panelValue['panel_item_id'];
                        $penalMaster->use_stock = $panelValue['no_of_modules'];
                        $penalMaster->model_number = $panelValue['panel_model_number'];
                        $penalMaster->total_kw = $panelValue['total_kw'];
                        $penalMaster->save();

                        if ($panelValue['panel_stock_type'] == 'project') {
                            $checkStock = ProjectWiseStock::where([['sales_master_id', $request['sales_master_id']], ['item_group_id', $panelValue['panel_item_id']]])
                                // ->where('quantity','>=',$request->no_of_inverter[$inverterKey])
                                ->first();
                        } else {
                            $checkStock = ProjectWiseStock::where([['installer_id', Auth::id()], ['item_group_id', $panelValue['panel_item_id']]])
                                // ->where('quantity','>=',$request->no_of_inverter[$inverterKey])
                                ->first();
                        }
                        if (!is_null($checkStock) && $panelValue['no_of_modules'] > 0) {
                            $checkStock->quantity -= $panelValue['no_of_modules'];
                            $checkStock->save();
                            $stockTransactionP = new ProjectWiseStockHistory();
                            $stockTransactionP->installation_id = $qry->id;
                            $stockTransactionP->sales_master_id = $request['sales_master_id'];
                            $stockTransactionP->project_wise_stock_id = $checkStock->id;
                            $stockTransactionP->quantity = $panelValue['no_of_modules'];
                            $stockTransactionP->type = 'Debit';
                            $stockTransactionP->remark = $consumer_name . ' (Added)';
                            $stockTransactionP->save();
                        }
                    }
                endforeach;
            }

            $inverter = 0;
            if (isset($request['item_group']['inverters']) && count($request['item_group']['inverters']) > 0) {

                /* stock reverse */
                if (isset($request['id']) && $request['id'] != '' && $request['id'] != 0) {
                    $oldInv = $qry->no_of_inverter;

                    $oldInvatersForSr = InstallationInvater::where('installation_id', $request['id'])->get();
                    if (!is_null($oldInvatersForSr)) {
                        foreach ($oldInvatersForSr as $k => $v):
                            $existingSerialNumber = SerialNumber::where('serial_number', $v->serial_no_of_inverter)->whereNull('deleted_at')->first();
                            if (!is_null($existingSerialNumber)) {
                                $existingSerialNumber->status = 'available';
                                $existingSerialNumber->save();
                            }
                        endforeach;
                    }
                    $oldInvaters = InstallationInvater::where('installation_id', $request['id'])->groupBy('item_group_id', 'stock_type')->get();
                    if (!is_null($oldInvaters)) {
                        foreach ($oldInvaters as $k => $v):
                            if ($v->stock_type == 'project') {
                                $checkStock = ProjectWiseStock::where([['sales_master_id', $request['sales_master_id']], ['item_group_id', $v->item_group_id]])
                                    ->first();
                            } else {
                                $checkStock = ProjectWiseStock::where([['installer_id', Auth::id()], ['item_group_id', $v->item_group_id]])
                                    ->first();
                            }
                            if (!is_null($checkStock)) {
                                $checkStock->quantity += $oldInv;
                                $checkStock->save();

                                $stockTransactionP = new ProjectWiseStockHistory();
                                $stockTransactionP->installation_id = $qry->id;
                                $stockTransactionP->sales_master_id = $request['sales_master_id'];
                                $stockTransactionP->project_wise_stock_id = $checkStock->id;
                                $stockTransactionP->quantity = $oldInv;
                                $stockTransactionP->type = 'credit';
                                $stockTransactionP->remark = $consumer_name . ' (Edited)';
                                $stockTransactionP->save();

                                /* To Delete Stock History */
                                ProjectWiseStockHistory::where('installation_id', $qry->id)->where('sales_master_id', $request['sales_master_id'])->where('project_wise_stock_id', $checkStock->id)->delete();
                                /* / To Delete Stock History */
                            }
                        endforeach;

                        InstallationInvater::where('installation_id', $request['id'])->delete();
                    }
                }
                /* / stock reverse */

                foreach ($request['item_group']['inverters'] as $inverterKey => $inverterValue):
                    $inverter += $inverterValue['no_of_inverter'];


                    if (isset($inverterValue['data']) && isset($inverterValue['data']) && count($inverterValue['data']) > 0) {
                        foreach ($inverterValue['data'] as $invKey => $invValue):
                            $item_group_inv = ItemGroup::where('id', $inverterValue['inverter_item_id'])->first();

                            $invater = new InstallationInvater();
                            $invater->sales_master_id = $request['sales_master_id'];
                            $invater->installation_id = $qry->id;
                            $invater->item_group_id = $inverterValue['inverter_item_id'];
                            $invater->stock_type = $inverterValue['inverter_stock_type'];
                            $invater->invater_id =  $item_group_inv->inveter_company_id;
                            $invater->model_number = $invValue['inverter_model_number'];
                            $invater->invater_kw = $item_group_inv->inveter_kw;
                            $invater->serial_no_of_inverter = $invValue['inverter_sr_number'];
                            $invater->voltage = $invValue['inverter_voltage'];
                            $invater->save();

                            $existingSerialNumber = SerialNumber::where('serial_number', $invValue['inverter_sr_number'])->whereNull('deleted_at')->first();
                            if (!is_null($existingSerialNumber)) {
                                $existingSerialNumber->status = 'sold';
                                $existingSerialNumber->save();
                            }
                        endforeach;
                    }

                    if ($inverterValue['inverter_stock_type'] == 'project') {
                        $checkStock = ProjectWiseStock::where([['sales_master_id', $request['sales_master_id']], ['item_group_id', $inverterValue['inverter_item_id']]])
                            ->first();
                    } else {
                        $checkStock = ProjectWiseStock::where([['installer_id', Auth::id()], ['item_group_id', $inverterValue['inverter_item_id']]])
                            ->first();
                    }
                    if (!is_null($checkStock) && $inverterValue['no_of_inverter'] > 0) {

                        $checkStock->quantity -= $inverterValue['no_of_inverter'];
                        $checkStock->save();
                        $stockTransactionP = new ProjectWiseStockHistory();
                        $stockTransactionP->installation_id = $qry->id;
                        $stockTransactionP->sales_master_id = $request['sales_master_id'];
                        $stockTransactionP->project_wise_stock_id = $checkStock->id;
                        $stockTransactionP->quantity = $inverterValue['no_of_inverter'];
                        $stockTransactionP->type = 'Debit';
                        $stockTransactionP->remark = $consumer_name . ' (Added)';
                        $stockTransactionP->save();
                    }
                endforeach;
                $qry->no_of_inverter = $inverter;
            }
            $qry->save();
            DB::commit();
            $response = ['status' => true, 'message' => 'Installation added successfully.'];
            return response($response, 200);
        } catch (\Exception $e) {
            DB::rollback();
            $response = ['status' => false, 'message' => 'Something went wrong. Please try again.'];
            return response($response, 500);
        }
    }
    /* / API :: Latest with ERP  */
}
