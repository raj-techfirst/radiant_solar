<?php

namespace App\Http\Controllers\Api\erp;

use App\Http\Controllers\Controller;
use App\Models\erp\BOMMeta;
use App\Models\erp\ProjectWiseStock;
use App\Models\Installation;
use App\Models\InstallationInvater;
use App\Models\InstallationItems;
use App\Models\InstallationPenal;
use App\Models\InstallationPenalMaster;
use App\Models\SalesMaster;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class StockListController extends Controller
{
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => ['required'],
            'project_id' => 'required_if:type,project',
        ], [
            'type' => 'Enter Type',
            'project_id.required' => 'Select project'
        ]);
        if ($validator->fails()) {
            $response = ['status' => false, 'message' => 'Please input proper data.', 'errors' => $validator->errors()];
            return response($response, 200);
        }
        try {
		
            $query = ProjectWiseStock::with('project', 'item', 'itemGroup')->where(function ($q) use ($request) {
                if (!empty($request->project_id) && $request->project_id != '') {
                    $q->where('sales_master_id', $request->project_id);
                }
                if (!empty($request->item_id) && $request->item_id != '') {
                    $q->where('item_id', $request->item_id);
                }
                if (!empty($request->fdate) && !empty($request->tdate)) {
                    $from_date = date('Y-m-d 00:00:00', strtotime($request->fdate));
                    $to_date = date('Y-m-d 23:59:59', strtotime($request->tdate));
                    $q->whereBetween('updated_at', [$from_date, $to_date]);
                }
            });
            $stockListController = $query->get();
            if ($stockListController->count() > 0) {
                foreach ($stockListController as $key => $value):
                    if ($value->type == "Item") {
                        $value->item_name = $value->item->name;
                    } else {
                        $value->item_name = getItemGropName($value, 1);
                    }

                    if ($value->issue_type == "project") {
                        $value->Project_name =  $value->project->consumer_name;
                    } else {
                        $value->installer_name =  '(Ins) ' . $value->installer->name . ' ' . $value->installer->last_name;
                    }

                    unset(
                        $value->user_id,
                        $value->delivery_challan_id,
                        $value->warehouse_id,
                        $value->sales_master_id,
                        $value->item_id,
                        $value->item_group_id,
                        $value->unit_id,
                        $value->installer_id,
                        $value->created_at,
                        $value->updated_at,
                        $value->deleted_at,
                        $value->project,
                        $value->itemGroup,
                        $value->item,
                        $value->installer
                    );
                endforeach;
            }

            $response = ['status' => true, 'data' => $stockListController];
            return response($response, 200);
        } catch (\Exception $e) {
            $response = ['status' => false, 'message' => 'Something went wrong. Please try again.'];
            return response($response, 500);
        }
    }

    public function getAvailableStock(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required',
            'id' => 'required',
        ], [
            'type.required' => 'Select Type',
            'id.required' => 'Select Id',
        ]);

        if ($validator->fails()) {
            $response = ['status' => false, 'message' => 'Please input proper data.', 'errors' => $validator->errors()];
            return response()->json($response);
        }

        try {
            if ($request->type == "installer") {
                $field = 'installer_id';
            } else {
                $field = "sales_master_id";
            }
            $projectWiseStock = ProjectWiseStock::with('item.unit', 'itemGroup')->where('issue_type', $request->type)->where($field, $request->id)->get();

            if (count($projectWiseStock) > 0) {

                $data = [];
                $data['Item'] = [];
                $data['itemGroup'] = [];
                foreach ($projectWiseStock as $pro) {

                    if ($pro->type == "Item") {
                        $item_id = $pro->item->id;
                        $display_name = $pro->item->name;
                        $unit = $pro->item->unit->unit_name;

                        $data['Item'][] = [
                            'id' => $item_id,
                            'name' => $display_name,
                            'unit' => $unit,
                            'stock' => $pro->quantity ?? 0,
                        ];
                    } else {
                        $item_id = $pro->itemGroup->id;
                        $unit = $pro->itemGroup->unit->unit_name;
                        $data['itemGroup'][] = [
                            'id' => $item_id,
                            'name' => getItemGropName($pro, 1),
                            'unit' => $unit,
                            'stock' => $pro->quantity ?? 0,
                        ];
                    }
                }
                $warehouseStock = collect($data['Item']);
                $warehouseStockItemGroup = collect($data['itemGroup']);

                $response = ['status' => true, 'data' => ['item' => $warehouseStock, 'itemGroup' => $warehouseStockItemGroup]];
                return response($response, 200);
            } else {
                $response = ['status' => false, 'message' => 'No Data!'];
                return response($response, 200);
            }
        } catch (\Exception $e) {
            $response = ['status' => false, 'message' => 'Something went wrong. Please try again.'];
            return response($response, 200);
        }
    }

    public function getBom(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'bom_id' => 'required',
        ], [
            'bom_id.required' => 'Select BOM',
        ]);
        if ($validator->fails()) {
            $response = ['status' => false, 'message' => 'Please input proper data.', 'errors' => $validator->errors()];
            return response()->json($response);
        }
        try {
            if ($request->bom_id != 0) {
                $bomData = BOMMeta::with('product', 'unit', 'itemGroup', 'itemGroup.panel_company', 'itemGroup.panel_type', 'itemGroup.panel_watt', 'itemGroup.inveter_company', 'product.category')->where('boms_id', $request->bom_id)->get();
                if ($bomData->count() > 0) {
                    foreach ($bomData as $pro) {
                        if ($pro->type == "Item") {
                            $item_id = $pro->product->id;
                            $projectWiseStock = ProjectWiseStock::select('quantity')->with('item.unit', 'itemGroup')
                                ->where('issue_type', 'project')
                                ->where('sales_master_id', $request->sales_master_id)
                                ->where('type', 'Item')
                                ->where('item_id', $item_id)
                                ->first();
                            $installer_wise_stock = ProjectWiseStock::select('quantity')->with('item.unit', 'itemGroup')
                                ->where('issue_type', 'installer')
                                ->where('installer_id', $request->installer_id)
                                ->where('type', 'Item')
                                ->where('item_id', $item_id)
                                ->first();
                            $display_name = $pro->product->name;
                            $unit = $pro->product->unit->unit_name;
                            $data['item'][$pro->product->category->id]['name'] =  $pro->product->category->category_name;
                            $data['item'][$pro->product->category->id]['items'][] =  [
                                'id' => $item_id,
                                'category_name' => $pro->product->category->category_name,
                                'name' => $display_name,
                                'unit' => $unit,
                                'required_qty' => $pro->quantity ?? 0,
                                'project_wise_stock' => $projectWiseStock->quantity ?? 0,
                                'installer_wise_stock' => $installer_wise_stock->quantity ?? 0,
                            ];
                        } else {
                            $item_id = $pro->itemGroup->id;
                            $projectWiseStock = ProjectWiseStock::select('quantity')->with('item.unit', 'itemGroup')
                                ->where('issue_type', 'project')
                                ->where('sales_master_id', $request->sales_master_id)
                                ->where('type', 'ItemGroup')
                                ->where('item_group_id', $item_id)
                                ->first();
                            $installer_wise_stock = ProjectWiseStock::select('quantity')->with('item.unit', 'itemGroup')
                                ->where('issue_type', 'installer')
                                ->where('installer_id', $request->installer_id)
                                ->where('type', 'ItemGroup')
                                ->where('item_group_id', $item_id)
                                ->first();
                            $unit = $pro->itemGroup->unit->unit_name;

                            $item_g_arr = [
                                'id' => $item_id,
                                'category_name' => $pro->itemGroup->group_type,
                                'name' => getItemGropName($pro, 1),
                                'unit' => $unit,
                                'required_qty' => $pro->quantity ?? 0,
                                'project_wise_stock' => $projectWiseStock->quantity ?? 0,
                                'installer_wise_stock' => $installer_wise_stock->quantity ?? 0,
                            ];
                            if ($pro->itemGroup->group_type == 'panel') {
                                $item_g_arr['panel_watt'] = $pro->itemGroup->panel_watt->name;
                            }
                            $data['itemGroup'][$pro->itemGroup->group_type][] = $item_g_arr;
                        }
                    }
                    $warehouseStock = collect(array_values($data['item']));
                    if (isset($data['itemGroup'])) {
                        $warehouseStockItemGroup = collect($data['itemGroup']);
                    } else {
                        $warehouseStockItemGroup = [];
                    }
                }

                $response = ['status' => true, 'data' => ['item' => $warehouseStock, 'itemGroup' => $warehouseStockItemGroup]];
                return response($response, 200);
            } else {
                $response = ['status' => false, 'message' => 'No Data!'];
                return response($response, 200);
            }
        } catch (\Exception $e) {
            $response = ['status' => false, 'message' => 'Something went wrong. Please try again.'];
            return response($response, 200);
        }
    }

    public function getInstallationStock(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'project_id' => 'required_if:type,project',
        ], [
            'project_id.required' => 'Select project'
        ]);
        if ($validator->fails()) {
            $response = ['status' => false, 'message' => 'Please input proper data.', 'errors' => $validator->errors()];
            return response($response, 200);
        }
        try {
				 $itemStock = $itemGroupStock = [];
            $query = ProjectWiseStock::with('item', 'item.category', 'itemGroup')
                ->where(function ($q) use ($request) {
                    if (!empty($request->project_id) && $request->project_id != '') {
                        $q->where('sales_master_id', $request->project_id);
                    }
                })
                ->orWhere(function ($q) use ($request) {
                    $q->where('installer_id', Auth::id());
                });
            $stockListController = $query->get();

            $salesMaster = SalesMaster::select('bom_id')->where('id', $request->project_id)->first();


            if ($stockListController->count() > 0 && $salesMaster->bom_id != null) {
                foreach ($stockListController as $key => $value):
				
				
				
                    if ($value->type == "Item") {
                        // $bomMeta = BOMMeta::select('quantity')->where('item_id',  $value->item->id)->where('boms_id', $salesMaster->bom_id)->first();
                        // $unit = $value->item->unit->unit_name;
                        // $itemStock[$value->item->category->id]['name'] =  $value->item->category->category_name;
                        // $itemStock[$value->item->category->id]['items'][] =  [
                        //     'id' => $value->item->id,
                        //     'issue_type' =>  $value->issue_type,
                        //     'name' => $value->item->name,
                        //     'unit' => $unit,
                        //     $value->issue_type.'_stock_qty' => $value->quantity ?? 0,
                        //     'required_qty' => (!is_null($bomMeta->quantity)) ? $bomMeta->quantity :  0
                        // ];

                        $bomMeta = BOMMeta::select('quantity')
                            ->where('item_id', $value->item->id)
                            ->where('boms_id', $salesMaster->bom_id)
                            ->first();

                        $unit = $value->item->unit->unit_name;


                        $installationItems = InstallationItems::select('use_stock', 'stock_type')->where('item_id', $value->item->id)->where('sales_master_id', $request->project_id)->get();
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
                            'issue_type' => $value->issue_type,
                            'name' => $value->item->name,
                            'unit' => $unit,
                            'required_qty' => (!is_null($bomMeta)) ? $bomMeta->quantity : 0,
                            'project_stock_qty' => 0,
                            'installer_stock_qty' => 0,
                            'project_usage' => (int)$project_usage,
                            'installer_usage' => (int)$installer_usage
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

                                $itemData['project_stock_qty'] += $itemData['project_usage'];
                                $itemData['installer_stock_qty'] += $itemData['installer_usage'];
                                $itemStock[$value->item->category->id]['items'][] = $itemData;
                            }
                        } else {

                            $itemData['project_stock_qty'] += $itemData['project_usage'];
                            $itemData['installer_stock_qty'] += $itemData['installer_usage'];

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
                            'required_qty' => (!is_null($bomMeta)) ? $bomMeta->quantity :  0
                        ];
                        if ($value->itemGroup->group_type == 'panel') {
                            $item_g_arr['panel_watt'] = $value->itemGroup->panel_watt->name;
                            $installationPanelsAlready = InstallationPenalMaster::select('sales_master_id', 'installation_id', 'stock_type', 'item_group_id', 'use_stock', 'model_number', 'total_kw')->where('stock_type', $value->issue_type)->where('item_group_id', $value->itemGroup->id)->where('sales_master_id', $request->project_id)->get();
                            if ($installationPanelsAlready->count() > 0) {
                                foreach ($installationPanelsAlready as $pmKey => $pmVal):
                                    $panelsData = InstallationPenal::select('serial_no')->where('installation_id', $pmVal->installation_id)->where('item_group_id', $pmVal->item_group_id)->get();
                                    $pmVal->data = $panelsData;
                                endforeach;
                                $item_g_arr['data'] = $installationPanelsAlready;
                            }
                        } else {
                            $installationInverter = InstallationInvater::select('item_group_id', 'stock_type', 'invater_id', 'invater_kw', 'model_number', 'serial_no_of_inverter', 'voltage')->where('stock_type', $value->issue_type)->where('item_group_id', $value->itemGroup->id)->where('sales_master_id', $request->project_id)->get();
                            if ($installationInverter->count() > 0) {

                                $item_g_arr['data'] = $installationInverter;
                            }
                        }
                        $itemGroupStock[$value->itemGroup->group_type][] = $item_g_arr;
                    }
				             
			   endforeach;

                $installation = Installation::select('id', 'date', 'dc_side', 'ac_side', 'la_earthing', 'phase_to_earth', 'phase_to_phase')->where('sales_master_id', $request->project_id)->first();
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
                $isInstallation = (!is_null($installation)) ? $installation->id : 0;
                $response = ['status' => true, 'message' => 'success', 'installation' => $isInstallation, 'data' => ['item' => array_values($itemStock), 'itemGroup' => $itemGroupStock], 'installation_old' => $installation];
                return response($response, 200);
            } else {
                $response = ['status' => false, 'message' => 'Either BOM not selected or stock unavailable.'];
                return response($response, 200);
            }
        } catch (\Exception $e) {
            $response = ['status' => false, 'message' => 'Something went wrong. Please try again.'];
            return response($response, 500);
        }
    }
    public function installationSave(Request $request)
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
        try {
            $response = ['status_code' => 200, 'data' => route('sales-master.index'), 'message' => 'Installation added successfully.'];
        } catch (\Exception $e) {
            $response = ['status' => false, 'message' => 'Something went wrong. Please try again.'];
            return response($response, 500);
        }
    }
}
