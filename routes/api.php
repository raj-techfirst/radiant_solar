<?php

use Illuminate\Http\Request;
use App\Http\Controllers\Api\CategoryApiController;
use App\Http\Controllers\Api\CompanyApiController;
use App\Http\Controllers\Api\Dashboardforapp;
use App\Http\Controllers\Api\LoginApiController;
use App\Http\Controllers\Api\EmployeeApiController;
use App\Http\Controllers\Api\erp\DeliveryChallanApiController;
use App\Http\Controllers\Api\erp\DeliveryChallanReturnApiController;
use App\Http\Controllers\Api\erp\GeneralApiController;
use App\Http\Controllers\Api\erp\PurchaseDirectApiController;
use App\Http\Controllers\Api\erp\StockListController;
use App\Http\Controllers\Api\EstimateApiController;
use App\Http\Controllers\Api\FollowUpApiController;
use App\Http\Controllers\API\ForgetPasswordApiController;
use App\Http\Controllers\Api\LeadApiController;
use App\Http\Controllers\Api\ListApiController;
use App\Http\Controllers\Api\ProductApiController;
use App\Http\Controllers\Api\SalesApiController;
use App\Http\Controllers\Api\SalesMasterAPIController;
use App\Http\Controllers\Api\SalesQuationApiController;
use App\Http\Controllers\Api\TaskApiController;
use App\Http\Controllers\DistrictController;
use App\Http\Controllers\InstallationController;
use App\Http\Controllers\PaymetCollectionApiController;
use App\Http\Controllers\TalukaController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('login', [LoginApiController::class, 'login']);
Route::post('register', [LoginApiController::class, 'register']);
Route::post('register-otp-verify', [LoginApiController::class, 'otpVerify']);
Route::post('register-otp-resend', [LoginApiController::class, 'otpResend']);

Route::post('api-forget', [ForgetPasswordApiController::class, 'apiForget']);
Route::post('api-otp-verify', [ForgetPasswordApiController::class, 'apiOtpVerify']);
Route::post('api-confirm-password', [ForgetPasswordApiController::class, 'apiConfirmPassword']);

Route::group(['prefix' => 'admin', 'middleware' => ['auth:sanctum', 'role:Owner']], function () {

    Route::post('category-add-update', [CategoryApiController::class, 'store']);
    Route::post('category-delete', [CategoryApiController::class, 'destroy']);

    Route::post('product-add-update', [ProductApiController::class, 'store']);
    Route::post('product-delete', [ProductApiController::class, 'destroy']);

    Route::post('employee-list', [EmployeeApiController::class, 'index']);
    Route::post('employee-add-update', [EmployeeApiController::class, 'store']);
    Route::post('employee-delete', [EmployeeApiController::class, 'destroy']);
});

Route::group(['prefix' => 'manager', 'middleware' => ['auth:sanctum', 'role:Manager']], function () {
    Route::post('sales-list', [SalesApiController::class, 'index']);
    Route::post('sales-add-update', [SalesApiController::class, 'store']);
    Route::post('sales-delete', [SalesApiController::class, 'destroy']);
});

Route::group(['middleware' => ['auth:sanctum']], function () { //'role:Owner|Manager|Sales|Installer|Site Visitor'

    Route::post('profile-update', [CompanyApiController::class, 'store']);
    Route::post('profile-delete', [CompanyApiController::class, 'destroy']);

    Route::post('change-password', [LoginApiController::class, 'changePassword']);

    Route::post('category-list', [CategoryApiController::class, 'index']);
    Route::post('product-list', [ProductApiController::class, 'index']);
    Route::post('status-update', [EmployeeApiController::class, 'show']);

    Route::post('dashboard', [ListApiController::class, 'dashboard']);
    Route::post('source-list', [ListApiController::class, 'sourceList']);
    Route::post('state-city-list', [ListApiController::class, 'stateList']);
    Route::post('assign-list', [ListApiController::class, 'assignList']);
    Route::post('unit-list', [ListApiController::class, 'unitList']);
    Route::post('client-list', [ListApiController::class, 'clientList']);
    Route::post('manager-list', [ListApiController::class, 'managerList']);
    Route::post('sales-master-list', [ListApiController::class, 'salesMaster']);
    // Route::post('sales-quatation-list', [ListApiController::class, 'salesQuatation']);
    Route::post('agent-sales-person-list', [ListApiController::class, 'agentSales']);
    Route::post('assign-person-list', [ListApiController::class, 'assign']);
    Route::post('lead-drop-down-list', [ListApiController::class, 'leadList']);
    Route::post('item-list', [ListApiController::class, 'item']);
    Route::post('item-group-list', [ListApiController::class, 'itemGroup']);
    Route::post('bom-list', [ListApiController::class, 'bom']);
    Route::post('past-lead-list', [ListApiController::class, 'pastLead']);
    Route::post('bank-list', [ListApiController::class, 'bankList']);

    Route::post('sales-quatation-list', [SalesQuationApiController::class, 'index']);
    Route::post('sales-quatation-add-update', [SalesQuationApiController::class, 'store']);
    Route::post('sales-quatation-delete', [SalesQuationApiController::class, 'destroy']);
    Route::post('sales-quatation-pdf', [SalesQuationApiController::class, 'salesQuatationPdfSave']);

    Route::post('lead-list', [LeadApiController::class, 'index']);
    Route::post('lead-add-update', [LeadApiController::class, 'store']);
    Route::post('lead-excel-import', [LeadApiController::class, 'import']);
    Route::post('lead-delete', [LeadApiController::class, 'destroy']);
    Route::post('lead-sync', [LeadApiController::class, 'leadSync']);
    Route::post('site-visited', [LeadApiController::class, 'siteVisited']);
     Route::post('lead-details', [LeadApiController::class, 'details']);

    Route::post('follow-up', [FollowUpApiController::class, 'store']);

    Route::post('estimate-list', [EstimateApiController::class, 'index']);
    Route::post('estimate-add-update', [EstimateApiController::class, 'store']);
    Route::post('estimate-delete', [EstimateApiController::class, 'destroy']);
    Route::post('estimate-item-remove', [EstimateApiController::class, 'removeItem']);

    Route::post('task-list', [TaskApiController::class, 'index']);
    Route::post('task-add-update', [TaskApiController::class, 'store']);
    Route::post('task-delete', [TaskApiController::class, 'destroy']);

    Route::post('penal-company-list', [ListApiController::class, 'penalCompany']);
    Route::post('penal-type-list', [ListApiController::class, 'penalType']);
    Route::post('penal-watt-list', [ListApiController::class, 'penalWatt']);
    Route::post('penal-image-list', [ListApiController::class, 'penalImage']);
    Route::post('installation-image-list', [ListApiController::class, 'installationImage']);
    Route::post('invater-image-list', [ListApiController::class, 'invaterImage']);
    Route::post('installation-invater-list', [ListApiController::class, 'installationInvater']);
    Route::post('installation-penal-list', [ListApiController::class, 'installationPenal']);
    Route::post('invater-list', [ListApiController::class, 'invater']);

    Route::post('installation-list', [InstallationController::class, 'index']);
    Route::post('installation-add-update', [InstallationController::class, 'installationAddUpdate']);

    Route::post('installation-save', [InstallationController::class, 'installationAddUpdateNew']);

    Route::get('sales-quatation-dropdown', [SalesQuationApiController::class, 'salesQuatationList']);
    Route::get('district-dropdown', [DistrictController::class, 'districtListDropdown']);
    Route::post('taluka-dropdown', [TalukaController::class, 'talukaDropdown']);

    Route::post('sales-master-save', [SalesMasterAPIController::class, 'index']);

    Route::post('payment-list', [PaymetCollectionApiController::class, 'index']);
    Route::post('payment-add', [PaymetCollectionApiController::class, 'store']);
    Route::post('payment-update', [PaymetCollectionApiController::class, 'update']);
    Route::post('payment-delete', [PaymetCollectionApiController::class, 'destroy']);
    Route::post('payment-change-status', [PaymetCollectionApiController::class, 'changeStatus']);
    Route::get('payment-sales-order', [PaymetCollectionApiController::class, 'salesOrderPayment']);

    Route::get('employee-dropdown', [EmployeeApiController::class, 'employeeDropDown']);
    Route::get('sub-division-dropdown', [SalesMasterAPIController::class, 'subDivision']);
    Route::post('show-installation', [InstallationController::class, 'showInstallationData']);
    Route::post('delete-image', [InstallationController::class, 'deleteImage']);

    Route::post('stock-list', [StockListController::class, 'index']);
    Route::post('get-available-stock', [StockListController::class, 'getAvailableStock']);
    Route::post('get-bom-items', [StockListController::class, 'getBom']);
    Route::post('get-installation-stock', [StockListController::class, 'getInstallationStock']);
    Route::post('get-installation-save', [StockListController::class, 'installationSave']);
});

Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::get('dashboard', [Dashboardforapp::class, 'index']);

 /* For ERP APP */
    Route::get('purchase-direct-list', [PurchaseDirectApiController::class, 'index']);
    Route::get('purchase-direct-show', [PurchaseDirectApiController::class, 'show']);
    Route::get('purchase-direct-delete', [PurchaseDirectApiController::class, 'destroy']);
    Route::get('purchase-direct-item-delete', [PurchaseDirectApiController::class, 'deletePurchaseDirectMeta']);

    Route::post('purchase-direct-store', [PurchaseDirectApiController::class, 'store']);

    Route::get('supplier-dropdown', [GeneralApiController::class, 'supplierDropdown']);
    Route::get('warehouse-dropdown', [GeneralApiController::class, 'warehouseDropdown']);

    Route::get('delivery-challan-list', [DeliveryChallanApiController::class, 'index']);
    Route::get('delivery-challan-download', [DeliveryChallanApiController::class, 'pdf']);
    Route::get('delivery-challan-delete', [DeliveryChallanApiController::class, 'destroy']);
    Route::get('delivery-challan-show', [DeliveryChallanApiController::class, 'show']);
    Route::get('sales-master-dropdown', [DeliveryChallanApiController::class, 'getSalesMaster']);
    Route::get('installer-dropdown', [DeliveryChallanApiController::class, 'getInstallers']);
    Route::post('delivery-challan-get-boms', [DeliveryChallanApiController::class, 'getWarehouseStock']);
    Route::post('delivery-challan-store', [DeliveryChallanApiController::class, 'store']);

    Route::get('delivery-challan-return-list', [DeliveryChallanReturnApiController::class, 'index']);
    Route::get('delivery-challan-return-show', [DeliveryChallanReturnApiController::class, 'show']);
    Route::get('delivery-challan-return-download', [DeliveryChallanReturnApiController::class, 'pdf']);
    Route::get('delivery-challan-return-delete', [DeliveryChallanReturnApiController::class, 'destroy']);
    Route::post('delivery-challan-return-get-return-stock', [DeliveryChallanReturnApiController::class, 'getReturnStock']);
    Route::post('delivery-challan-return-store', [DeliveryChallanReturnApiController::class, 'store']);

    /* For ERP APP */

    Route::get('get-sales-quotation-status', [ListApiController::class, 'getSalesQuotationStatus']);
    Route::get('get-lead-status', [ListApiController::class, 'getLeadStatus']);
    Route::post('sales-quotation-status-change', [SalesQuationApiController::class, 'changeStatus']);


});

Route::get('get-date-time', [CompanyApiController::class, 'getdatetime']);
