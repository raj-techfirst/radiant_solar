<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\PermissionsController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\StateController;
use App\Http\Controllers\CityController;
use App\Http\Controllers\SourceController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\StatusMasterController;
use App\Http\Controllers\Admin\CompanyProfileController;
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\Admin\EstimateController;
use App\Http\Controllers\Admin\FollowUpController;
use App\Http\Controllers\Admin\LeadMasterController;
use App\Http\Controllers\Admin\TaskController;
use App\Http\Controllers\AgentSalesPersonController;
use App\Http\Controllers\erp\PurchaseOrderController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\BankController;
use App\Http\Controllers\DiscomController;
use App\Http\Controllers\DistrictController;
use App\Http\Controllers\erp\BOMController;
use App\Http\Controllers\erp\DeliveryChallanController;
use App\Http\Controllers\erp\DeliveryChallanReturnController;
use App\Http\Controllers\erp\ProjectWiseStockAdjustController;
use App\Http\Controllers\erp\ProjectWiseStockController;

use App\Http\Controllers\ForgetPasswordController;
use App\Http\Controllers\InquiryController;
use App\Http\Controllers\InquiryFollowController;
use App\Http\Controllers\InstallationController;
use App\Http\Controllers\InveterCompanyController;
use App\Http\Controllers\ItemGroupController;
use App\Http\Controllers\Manager\SalesController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PaymetCollectionController;
use App\Http\Controllers\CommissionPaymentController;
use App\Http\Controllers\PenalCompanyController;
use App\Http\Controllers\PenalTypeController;
use App\Http\Controllers\PenalWattController;
use App\Http\Controllers\PolicyController;
use App\Http\Controllers\erp\PurchaseDirectController;
use App\Http\Controllers\erp\ReportsController;
use App\Http\Controllers\Reports;
use App\Http\Controllers\SalesMasterController;

use App\Http\Controllers\TalukaController;
use App\Http\Controllers\SalesQuatationController;
use App\Http\Controllers\SubDivisionController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\VillageController;
use App\Http\Controllers\erp\WarehouseController;
use App\Http\Controllers\erp\WarehouseStockAdjustmentController;
use App\Http\Controllers\erp\WarehouseStockController;
use App\Http\Controllers\RateCalculator;
use App\Http\Controllers\CommissionListController;
use App\Http\Controllers\LeadSourceController;
use App\Http\Controllers\YearController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return redirect()->route('login');
})->name('welcome');

Route::get('/clear-cache', function () {
    Artisan::call('cache:clear');
    Artisan::call('route:clear');
    Artisan::call('optimize:clear');
    Artisan::call('view:clear');
    Artisan::call('config:clear');
    return 'Application cache has been cleared';
});

Route::get('inquiry', [InquiryController::class, 'create'])->name('inquiry');
Route::get('inquiry-list', [InquiryController::class, 'index'])->name('inquiry-list');
Route::get('inquiry-show/{id}', [InquiryController::class, 'show'])->name('inquiry-show');
Route::delete('inquiry-delete/{id}', [InquiryController::class, 'destroy'])->name('inquiry-delete');
Route::post('save-inquiry', [InquiryController::class, 'store'])->name('save-inquiry');
Route::get('inquiry-follow/{id}', [InquiryController::class, 'follow'])->name('inquiry-follow');
Route::get('inquiry-dashboard', [InquiryController::class, 'dashboard'])->name('inquiry-dashboard');

Route::get('register', [RegisterController::class, 'index'])->name('register');
Route::post('register', [RegisterController::class, 'create'])->name('register');
Route::get('otp', [RegisterController::class, 'otp'])->name('otp');
Route::post('otp', [RegisterController::class, 'otpVerify'])->name('otp');
Route::get('resend-otp', [RegisterController::class, 'resendotp'])->name('resend-otp');

Route::get('forget', [ForgetPasswordController::class, 'forget'])->name('forget');
Route::post('forget-now', [ForgetPasswordController::class, 'forgetNow'])->name('forget-now');
Route::get('verify', [ForgetPasswordController::class, 'verifySearch'])->name('verify');
Route::post('otp-verify', [ForgetPasswordController::class, 'otpVerify'])->name('otp-verify');
Route::get('reset-password', [ForgetPasswordController::class, 'resetPassword'])->name('reset-password');
Route::post('confirm-password', [ForgetPasswordController::class, 'confirmPassword'])->name('confirm-password');

Route::group(['middleware' => ['auth']], function () {
    Route::get('home', [HomeController::class, 'index'])->name('home');
    Route::get('change/lang', [HomeController::class, 'language'])->name('language');
    Route::get('soft/lang', [HomeController::class, 'softChange'])->name('soft');
    Route::get('year/lang', [HomeController::class, 'yearChange'])->name('years');
    Route::resource('roles', RoleController::class);
    Route::resource('permissions', PermissionsController::class);

    Route::get('user', [HomeController::class, 'user'])->name('user-list');
    Route::post('user-status', [HomeController::class, 'userStatus'])->name('user-status');
    Route::delete('user/{id}', [HomeController::class, 'userDelete']);
    Route::resource('role', RoleController::class);
    Route::resource('permission', PermissionsController::class);
    Route::resource('state', StateController::class);
    Route::resource('city', CityController::class);
    Route::resource('source', SourceController::class);
    Route::resource('unit', UnitController::class);
    Route::resource('bank', BankController::class);
    Route::resource('policy', PolicyController::class);

    Route::resource('status-master', StatusMasterController::class);
    Route::resource('sub-division', SubDivisionController::class);
    Route::resource('category', CategoryController::class);
    Route::resource('product', ProductController::class);
    Route::resource('employee', EmployeeController::class);
    Route::resource('sales-master', SalesMasterController::class);
    Route::resource('penal-company', PenalCompanyController::class);
    Route::resource('penal-type', PenalTypeController::class);
    Route::resource('penal-watt', PenalWattController::class);
    Route::resource('payment-collection', PaymetCollectionController::class);
    Route::resource('commission-payment', CommissionPaymentController::class);
    Route::resource('district', DistrictController::class);
    Route::resource('taluka', TalukaController::class);
    Route::resource('village', VillageController::class);
    Route::resource('agent-sales-person', AgentSalesPersonController::class);
    Route::get('payment-view/{id}', [SalesMasterController::class, 'paymentView'])->name('payment-view');
    Route::get('application-view/{id}', [SalesMasterController::class, 'view'])->name('application-view');
    Route::get('document-view/{id}', [SalesMasterController::class, 'documentView'])->name('document-view');
    Route::post('status-view/{id}', [SalesMasterController::class, 'statusView'])->name('status-view');
    Route::post('status-save/{id}', [SalesMasterController::class, 'statusSave'])->name('status-save');
    Route::post('application-save', [SalesMasterController::class, 'applicatonSave'])->name('application-save');
    Route::get('taluka-view', [TalukaController::class, 'view'])->name('taluka-view');
    Route::get('village-view', [VillageController::class, 'view'])->name('village-view');
    Route::get('division-view', [SubDivisionController::class, 'view'])->name('division-view');
    Route::resource('lead-source', LeadSourceController::class);

    //indiamart
    Route::get('lead-api', [LeadMasterController::class, 'leadApi'])->name('lead-api');

    Route::resource('sales', SalesController::class);

    Route::resource('task', TaskController::class);
    Route::resource('lead', LeadMasterController::class);
    Route::get('lead-complet', [LeadMasterController::class, 'update'])->name('lead-complet');

    Route::resource('follow-up', FollowUpController::class);
    Route::resource('inquiry-follow-up', InquiryFollowController::class);
    Route::resource('profile', CompanyProfileController::class);
    Route::resource('estimate', EstimateController::class);

    Route::resource('message', MessageController::class);
    Route::get('view-message', [MessageController::class, 'show'])->name('view-message');

    Route::post('get-notification', [NotificationController::class, 'getNotification'])->name('get-notification');
    Route::post('read-notification', [NotificationController::class, 'readAllNotification'])->name('read-notification');
    Route::post('old-notification', [NotificationController::class, 'oldNotification'])->name('old-notification');

    Route::get('estimate-pdf/{id}', [EstimateController::class, 'estimatePDF'])->name('estimate-pdf');
    Route::post('remove-single-item', [EstimateController::class, 'removeItem'])->name('remove-single-item');

    Route::get('change-password', [HomeController::class, 'changePassword'])->name('change-password');
    Route::post('update-password', [HomeController::class, 'updatePassword'])->name('update-password');

    Route::post('import', [LeadMasterController::class, 'import'])->name('import');
    Route::get('lead-sample-download', [LeadMasterController::class, 'sampleDownload'])->name('lead-sample-download');
    Route::post('export', [LeadMasterController::class, 'export'])->name('export');
    Route::post('task-export', [TaskController::class, 'export'])->name('task-export');

    Route::post('add-product', [ProductController::class, 'store'])->name('add-product');

    Route::get('change-city', [CityController::class, 'show'])->name('change-city');
    Route::resource('sales-quatation', SalesQuatationController::class);
    Route::get('search-lead', [LeadMasterController::class, 'searchlead'])->name('search-lead');
    Route::get('get-panel-watt', [SalesQuatationController::class, 'getPanelsAndWatts'])->name('get-panel-watt');
    Route::get('sales-quatation-pdf/{id}', [SalesQuatationController::class, 'salesQuatationPdf'])->name('sales-quatation-pdf');
    Route::resource('inveter-company', InveterCompanyController::class);

    Route::post('change-payment-status', [PaymetCollectionController::class, 'changeStatus'])->name('change-payment-status');
    Route::post('change-commission-payment-status', [CommissionPaymentController::class, 'changeStatus'])->name('change-commission-payment-status');
  //  Route::get('self-certification-pdf/{id}', [SalesMasterController::class, 'selfCertificationPdf'])->name('self-certification-pdf');

      Route::get('self-certification-pdf/{id}/0', [SalesMasterController::class, 'selfCertificationPdf'])->name('self-certification-pdf');
    Route::get('self-certification-pdf/{id}/1', [SalesMasterController::class, 'selfCertificationPdf'])->name('self-certification-pdf-sign');

    Route::get('request-letter-pdf/{id}', [SalesMasterController::class, 'requestLetterPdf'])->name('request-letter-pdf');
  //  Route::get('model-agreement-pdf/{id}', [SalesMasterController::class, 'modelAgreementPdf'])->name('model-agreement-pdf');
      Route::get('model-agreement-pdf/{id}/0', [SalesMasterController::class, 'modelAgreementPdf'])->name('model-agreement-pdf');
    Route::get('model-agreement-pdf/{id}/1', [SalesMasterController::class, 'modelAgreementPdf'])->name('model-agreement-pdf-sign');
    Route::get('declaration-dcr-pdf/{id}', [SalesMasterController::class, 'declarationDCRPdf'])->name('declaration-dcr-pdf');

   // Route::get('agreement-pdf/{id}', [SalesMasterController::class, 'agreementPdf'])->name('agreement-pdf');

    Route::get('agreement-pdf/{id}/0', [SalesMasterController::class, 'agreementPdf'])->name('agreement-pdf');
    Route::get('agreement-pdf/{id}/1', [SalesMasterController::class, 'agreementPdf'])->name('agreement-pdf-sign');



	Route::get('geda-agreement-pdf/{id}', [SalesMasterController::class, 'gedaAgreementPdf'])->name('geda-agreement-pdf');
		Route::get('pmsgmby-commissioning-pdf/{id}', [SalesMasterController::class, 'pmsgmbyCommissioningPdf'])->name('pmsgmby-commissioning-pdf');

    Route::post('sales-order-remove-status', [SalesMasterController::class, 'removeStatus'])->name('sales-order-remove-status');

    Route::post('payment-report', [PaymetCollectionController::class, 'paymentReport'])->name('payment-report');
    Route::post('sales-order-report', [SalesMasterController::class, 'salesOrderReport'])->name('sales-order-report');

    Route::get('reports', [Reports::class, 'index'])->name('reports');

    Route::get('total-collection-reports', [Reports::class, 'totalcollection'])->name('total-collection-reports');
    Route::post('total-collection-download', [Reports::class, 'totalcollectionDownload'])->name('total-collection-download');

    Route::get('payment-pending-reports', [Reports::class, 'paymentpending'])->name('payment-pending-reports');
    Route::post('payment-pending-download', [Reports::class, 'paymentpendingDownload'])->name('payment-pending-download');

    Route::get('meter-charges-reports', [Reports::class, 'meterCharges'])->name('meter-charges-reports');
    Route::post('meter-charges-download', [Reports::class, 'meterChargesDownload'])->name('meter-charges-download');

    Route::get('dispach-reports', [Reports::class, 'dispach'])->name('dispach-reports');
    Route::post('dispach-download', [Reports::class, 'dispachDownload'])->name('dispach-download');

    Route::get('installation-reports', [Reports::class, 'installation'])->name('installation-reports');
    Route::post('installation-download', [Reports::class, 'installationDownload'])->name('installation-download');

    Route::get('installation-new-reports', [Reports::class, 'installationNew'])->name('installation-new-reports');
    Route::post('installation-new-download', [Reports::class, 'installationNewDownload'])->name('installation-new-download');

    Route::get('meter-application-reports', [Reports::class, 'meterApplication'])->name('meter-application-reports');
    Route::post('meter-application-download', [Reports::class, 'meterApplicationDownload'])->name('meter-application-download');

    Route::get('final-reports', [Reports::class, 'finalReport'])->name('final-reports');
    Route::post('final-report-download', [Reports::class, 'finalReportDownload'])->name('final-report-download');
    Route::post('declaration-update', [SalesMasterController::class, 'declarationUpdate'])->name('declaration-update');

    Route::post('sales-order-import', [SalesMasterController::class, 'import'])->name('sales-order-import');
    Route::resource('discom', DiscomController::class);

    Route::get('invoice-reports', [Reports::class, 'invoice'])->name('invoice-reports');
    Route::post('invoice-download', [Reports::class, 'invoiceDownload'])->name('invoice-download');

    Route::get('panels-required-reports', [Reports::class, 'panelsRequired'])->name('panels-required-reports');
    Route::post('panels-required-download', [Reports::class, 'panelsRequiredDownload'])->name('panels-required-download');

    Route::get('inverters-required-reports', [Reports::class, 'invertersRequired'])->name('inverters-required-reports');
    Route::post('inverters-required-download', [Reports::class, 'inverterRequiredDownload'])->name('inverters-required-download');

    Route::post('sales-order-payment/{id}', [SalesMasterController::class, 'paymentList'])->name('sales-order-payment');
    Route::post('sales-quatation-get-details', [SalesQuatationController::class, 'getDetails'])->name('sales-quatation-get-details');
     Route::post('sales-quatation-status', [SalesQuatationController::class, 'changeStatus'])->name('sales-quatation-status');

    Route::resource('installation', InstallationController::class);
    Route::post('installation-image', [InstallationController::class, 'installationImageRemove'])->name('installation-image');

    Route::get('subsidy-claim-reports', [Reports::class, 'subsidyClaimReports'])->name('subsidy-claim-reports');
    Route::post('subsidy-claim-download', [Reports::class, 'subsidyClaimDownload'])->name('subsidy-claim-download');

    Route::resource('year', YearController::class);
    Route::resource('item-group', ItemGroupController::class);
    Route::resource('supplier', SupplierController::class);
    Route::resource('warehouse', WarehouseController::class);

    Route::resource('warehouse-stock', WarehouseStockController::class);
    Route::get('get-warehouse-stock', [WarehouseStockController::class, 'getWarehouseStock'])->name('get-warehouse-stock');
    Route::post('export-stock', [WarehouseStockController::class, 'exportStock'])->name('export-stock');
    Route::get('export-stock-history/{id}', [WarehouseStockController::class, 'exportStockHistory'])->name('export-stock-history');

    Route::resource('purchase-direct', PurchaseDirectController::class);
    Route::post('puchase-direct-remove', [PurchaseDirectController::class, 'deletePurchaseDirectMeta'])->name('puchase-direct-remove');

    Route::resource('stock-adjustments', WarehouseStockAdjustmentController::class);
    Route::get('daily-stock-adujst/{id}', [WarehouseStockAdjustmentController::class, 'dailyStockAdujst'])->name('daily-stock-adujst');

    Route::resource('delivery-challan', DeliveryChallanController::class);
    Route::post('delivery-challan-remove', [DeliveryChallanController::class, 'deliveryChallanRemove'])->name('delivery-challan-remove');
    Route::get('delivery-challan-pdf/{id}', [DeliveryChallanController::class, 'pdf'])->name('delivery-challan-pdf');

    // Route::get('get-warehouse-data', [WarehouseStockController::class, 'getWarehouseStock'])->name('get-warehouse-stock');

    Route::resource('project-wise-stock', ProjectWiseStockController::class);
    Route::get('get-project-stock', [ProjectWiseStockController::class, 'getProjectStock'])->name('get-project-stock');
    Route::post('export-project-stock', [ProjectWiseStockController::class, 'exportProjectStock'])->name('export-project-stock');
    Route::get('export-project-stock-history/{id}', [ProjectWiseStockController::class, 'exportProjectStockHistory'])->name('export-project-stock-history');

    Route::resource('project-stock-adjustments', ProjectWiseStockAdjustController::class);
    Route::get('project-daily-stock-adjust/{id}', [ProjectWiseStockAdjustController::class, 'projectDailyStockAdujst'])->name('project-daily-stock-adjust');

    Route::resource('purchase-order', PurchaseOrderController::class);
    Route::post('purchase-status-change', [PurchaseOrderController::class, 'changePurchaseStatus'])->name('purchase-status-change');
    Route::get('purchase-order-pdf/{id}', [PurchaseOrderController::class, 'purchaseOrderPdf'])->name('purchase-order-pdf');
    Route::post('purchase-receive', [PurchaseOrderController::class, 'purcahseReceive'])->name('purchase-receive');
    Route::post('purchase-receive-store', [PurchaseOrderController::class, 'purcahseReceiveStore'])->name('purchase-receive-store');

    Route::get('import-item-group-serial-number/{id}', [PurchaseDirectController::class, 'importItemGroupSerialNumber'])->name('import-item-group-serial-number');
    Route::post('import-item-group-serial-number-store', [PurchaseDirectController::class, 'importItemGroupSerialNumberStore'])->name('import-item-group-serial-number-store');

    Route::resource('delivery-challan-return', DeliveryChallanReturnController::class);
    Route::get('delivery-challan-return-pdf/{id}', [DeliveryChallanReturnController::class, 'pdf'])->name('delivery-challan-return-pdf');

    Route::get('get-return-stock', [DeliveryChallanReturnController::class, 'getReturnStock'])->name('get-return-stock');

    Route::resource('bom', BOMController::class);

    Route::get('reports/home', [ReportsController::class, 'index'])->name('erp-reports');
    Route::get('reports/project-wise-dispach', [ReportsController::class, 'projectWiseDispach'])->name('project-wise-dispach');
    Route::get('reports/project-wise-stock-report', [ReportsController::class, 'projectWiseStockReport'])->name('project-wise-stock-report');
    Route::get('reports/required-stock', [ReportsController::class, 'requiredStock'])->name('required-stock-report');
    Route::post('reports/get-required-stock', [ReportsController::class, 'getRequiredStock'])->name('get-required-stock-report');

    Route::post('reports/track-serial-number', [ReportsController::class, 'trackSerialNumber'])->name('track-serial-number');
    Route::post('reports/download-serial-number', [PurchaseDirectController::class, 'downloadSerialNumber'])->name('download-serial-number');
    Route::post('reports/view-serial-number', [PurchaseDirectController::class, 'importItemGroupSerialNumberShow'])->name('view-serial-number');
    Route::post('update-serial-number', [PurchaseDirectController::class, 'updateSerialNumber'])->name('update-serial-number');

    Route::get('reports/stock-report', [ReportsController::class, 'stockReport'])->name('stock-report');

    Route::get('reports/get-serial-numbers', [ReportsController::class, 'getSerialNumbers'])->name('get-serial-numbers');

    Route::get('bom-clone/{id}', [BOMController::class, 'cloneData'])->name('bom-clone');
    Route::get('purchase-order-clone/{id}', [PurchaseOrderController::class, 'cloneData'])->name('purchase-order-clone');

    Route::get('purchase-direct-clone/{id}', [PurchaseDirectController::class, 'cloneData'])->name('purchase-direct-clone');

    Route::get('reports/b2b-dispach', [ReportsController::class, 'bbDispach'])->name('b2b-dispach');

    Route::get('import-serial-number/{id}', [DeliveryChallanController::class, 'importSerialNumber'])->name('import-serial-number');
    Route::post('import-serial-number-store', [DeliveryChallanController::class, 'importSerialNumberStore'])->name('import-serial-number-store');

    Route::post('reports/download-serial-number-dc', [DeliveryChallanController::class, 'downloadSerialNumberdc'])->name('download-serial-number-dc');
    Route::post('reports/view-serial-number-dc', [DeliveryChallanController::class, 'importSerialNumberShow'])->name('view-serial-number-dc');

    Route::resource('rate-calculator', RateCalculator::class);
    Route::post('get-rate-calc-bom-data', [RateCalculator::class, 'getBomData'])->name('get-rate-calc-bom-data');
    Route::post('get-rate-calc-data', [RateCalculator::class, 'getEditData'])->name('get-rate-calc-data');

    Route::get('rate-calculator/clone/{id}', [RateCalculator::class, 'clone'])->name('rate-calculator-clone');

    Route::get('net-metering-inter-connection-pdf/{id}', [SalesMasterController::class, 'netMeteringInterConnectionPdf'])->name('net-metering-inter-connection-pdf');
    Route::get('vendor-feasibility-pdf/{id}', [SalesMasterController::class, 'vendorFeasibilityPdf'])->name('vendor-feasibility-pdf');
    Route::get('net-meter-pdf/{id}', [SalesMasterController::class, 'netMeterPdf'])->name('net-meter-pdf');

    // Commission List - static datatable page
    Route::get('commission-list', [CommissionListController::class, 'index'])->name('commission-list.index');
    Route::get('commission-list/{agent}', [CommissionListController::class, 'show'])->name('commission-list.show');
    Route::get('commission-list/{agent}/files', [CommissionListController::class, 'files'])->name('commission-list.files');
    Route::get('commission-list/{agent}/pdf', [CommissionListController::class, 'downloadPdf'])->name('commission-list.pdf');
    Route::get('commission-list/{agent}/excel', [CommissionListController::class, 'downloadAgentExcel'])->name('commission-list.agent-excel');
    Route::get('commission-list/{agent}/files-excel', [CommissionListController::class, 'downloadFilesExcel'])->name('commission-list.files-excel');
    Route::get('commission-list-excel', [CommissionListController::class, 'downloadExcel'])->name('commission-list.excel');

    Route::post('get-consumer-using-mobile', [SalesMasterController::class, 'getComsumerUsingMobile'])->name('get-consumer-using-mobile');

});
