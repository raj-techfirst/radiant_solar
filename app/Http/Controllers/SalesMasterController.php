<?php

namespace App\Http\Controllers;

use App\Exports\SalesOrderExport;
use App\Imports\PaymentImport;
use App\Imports\SalesMasterConsumerPreview;
use App\Imports\SalesMasterUpdateImport;
use App\Imports\Salesorder;
use App\Models\AgentSalesPerson;
use App\Models\Bank;
use App\Models\CompanyProfile;
use App\Models\ConsumerIgnore;
use App\Models\Discom;
use App\Models\District;
use App\Models\Document;
use App\Models\erp\BOM;
use App\Models\Installation;
use App\Models\InstallationInvater;
use App\Models\InstallationPenal;
use App\Models\InveterCompany;
use App\Models\LeadMaster;
use App\Models\Loandata;
use App\Models\PaymetCollection;
use App\Models\PenalCompany;
use App\Models\PortalData;
use App\Models\PenalType;
use App\Models\PenalWatt;
use App\Models\SalesMaster;
use App\Models\SalesQuatation;
use App\Models\SubDivision;
use App\Models\Taluka;
use App\Models\User;
use App\Models\UserCommission;
use App\Services\LoanStatusService;
use App\Services\PortalDataSyncService;
use App\Services\PortalStatusService;
use App\Services\UnmatchedRecordCreatorService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\HeadingRowImport;
use Yajra\DataTables\Facades\DataTables;

class SalesMasterController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:sales-master-list|sales-master-create|sales-master-edit|sales-master-delete', ['only' => ['index', 'store', 'show', 'view', 'documentView', 'statusView', 'paymentList', 'getComsumerUsingMobile', 'selfCertificationPdf', 'requestLetterPdf', 'modelAgreementPdf', 'declarationDCRPdf', 'agreementPdf', 'gedaAgreementPdf', 'pmsgmbyCommissioningPdf', 'netMeteringInterConnectionPdf', 'vendorFeasibilityPdf', 'netMeterPdf']]);
        // downloadDocument is accessible to any authenticated user (route group is auth-protected)
        $this->middleware('auth');
        $this->middleware('permission:sales-master-create', ['only' => ['create', 'import', 'createFromUnmatched', 'createFromUnmatchedPage']]);
        $this->middleware('permission:sales-master-edit', ['only' => ['edit', 'update', 'applicatonSave', 'statusSave', 'removeStatus', 'declarationUpdate', 'updateRecords']]);
        $this->middleware('permission:sales-master-delete', ['only' => ['destroy']]);
        $this->middleware('permission:sales-master-list', ['only' => ['salesOrderReport', 'consumerPreviewPage', 'consumerPreviewUpload', 'ignoreUnmatched', 'applyPreviewDefaultUpdates']]);
    }

    public function index()
    {
        //  scriptForSalesorder();

        $user = User::where('id', Auth::id())->first();

        $companyFind = CompanyProfile::where('user_id', Auth::id())->first();
        $agentWhere = '';
        if ($companyFind->user_type == 'M') {
            $id = $companyFind->id;
            $agentWhere .= 'company_profiles.user_id = '.Auth::id().' OR  company_profiles.manager_id = '.$id;
        }
        if ($companyFind->user_type == 'S') {
            $id = $companyFind->id;
            $manager_id = $companyFind->manager_id;
            $agentWhere .= 'company_profiles.id = '.$id.' OR  company_profiles.id = '.$manager_id;
        }

        $q = CompanyProfile::select('agent_sales_people.*')->leftJoin('agent_sales_people', 'agent_sales_people.user_id', 'company_profiles.user_id');
        if ($agentWhere != '') {
            $q->whereRaw($agentWhere);
        }

        $agentSalesPerson = $q->get();

        if (request()->ajax()) {
            return DataTables::of(SalesMaster::with('district', 'taluka', 'village', 'subDivision', 'agentsalesperson', 'panel', 'panelwatt', 'inveter', 'salesquatation', 'portalData')->orderBy('updated_at', 'DESC'))
                ->addIndexColumn()
                ->filter(function ($query) {
                    $company = CompanyProfile::where('user_id', Auth::id())->first();
                    if ($company->user_type == 'M') {
                        $agent = AgentSalesPerson::where('user_id', Auth::id())->first();
                        $agentIds = [$agent->id];
                        $sales = CompanyProfile::select('company_profiles.id', 'company_profiles.user_id', 'agent_sales_people.id as agent_id')
                            ->leftJoin('agent_sales_people', 'agent_sales_people.user_id', 'company_profiles.user_id')
                            ->where('company_profiles.manager_id', $company->id)->get();
                        if ($sales->count() > 0) {
                            foreach ($sales as $k => $v) {
                                array_push($agentIds, $v->agent_id);
                            }
                        }
                        $query->whereIn('agent_sales_person_id', $agentIds);
                    }
                    if ($company->user_type == 'S') {
                        $agent = AgentSalesPerson::where('user_id', Auth::id())->first();
                        $id = $agent->id;
                        $query->where('agent_sales_person_id', $id);
                    }

                    $portalStatus = trim((string) request()->input('portal_status', ''));

                    if ($portalStatus !== '') {
                        $matchingIds = app(PortalStatusService::class)->matchingIds($portalStatus);
                        $query->whereIn('sales_masters.id', $matchingIds ?: [0]);
                    }

                    $loanStatus = trim((string) request()->input('loan_status', ''));
                    $loanLender = trim((string) request()->input('loan_lender', ''));

                    if ($loanStatus !== '') {
                        $matchingIds = app(LoanStatusService::class)->matchingIds($loanStatus, $loanLender);
                        $query->whereIn('sales_masters.id', $matchingIds ?: [0]);
                    }

                    $advancedFilters = request()->input('advanced_filters', []);
                    $advancedFromDate = trim((string) (is_array($advancedFilters) ? ($advancedFilters['from_date'] ?? '') : ''));
                    $advancedToDate = trim((string) (is_array($advancedFilters) ? ($advancedFilters['to_date'] ?? '') : ''));
                    $fromDate = $advancedFromDate !== '' ? $advancedFromDate : trim((string) request()->input('from_date', ''));
                    $toDate = $advancedToDate !== '' ? $advancedToDate : trim((string) request()->input('to_date', ''));

                    if ($fromDate != '' && $toDate == '') {
                        $query->where('master_create_date', '>=', date('Y-m-d 00:00:00', strtotime($fromDate)));
                        $query->where('master_create_date', '<=', date('Y-m-d 23:59:59'));
                    }
                    if ($fromDate != '' && $toDate != '') {
                        $query->where('master_create_date', '>=', date('Y-m-d 00:00:00', strtotime($fromDate)));
                        $query->where('master_create_date', '<=', date('Y-m-d 23:59:59', strtotime($toDate)));
                    }
                    if ($fromDate == '' && $toDate != '') {
                        $query->where('master_create_date', '<=', date('Y-m-d 23:59:59', strtotime($toDate)));
                    }
                    if (request()->input('consumer') != '') {
                        $consumer = request()->input('consumer');
                        $query->where(function ($q) use ($consumer) {
                            $q->where('consumer_name', 'like', '%'.$consumer.'%')
                                ->orWhere('consumer_number', 'like', '%'.$consumer.'%')
                                ->orWhere('contact_number', 'like', '%'.$consumer.'%');
                        });
                    }
                    if (request()->input('agent_sales_person_id') != '') {
                        $query->where('agent_sales_person_id', request()->input('agent_sales_person_id'));
                    }
                    if (request()->input('file_type') != '') {
                        $query->where('file_type', request()->input('file_type'));
                    }
                    if (request()->input('status') != '') {
                        $query->where(request()->input('status'), '1');
                    }

                    if (request()->input('not_status') != '') {
                        $query->where(request()->input('not_status'), '0');
                    }

                    if (request()->input('ragistration_portal') != '') {
                        $ragistration_portal = request()->input('ragistration_portal');
                        if ($ragistration_portal == 'Other') {
                            $query->where(function ($q) use ($ragistration_portal) {
                                $q->where('ragistration_portal', 'like', '%'.$ragistration_portal.'%')
                                    ->orWhere('ragistration_portal', '')
                                    ->orWhere('ragistration_portal', null);
                            });
                        } else {
                            $query->where('ragistration_portal', 'like', '%'.$ragistration_portal.'%');
                        }
                    }

                    if (is_array($advancedFilters) && count($advancedFilters) > 0) {
                        $query->advancedFilter($advancedFilters);
                    }
                })
                ->editColumn('master_create_date', function ($row) {
                    if (! is_null($row->master_create_date)) {
                        return date('d-m-Y', strtotime($row->master_create_date));
                    } else {
                        return '';
                    }
                })
                ->addColumn('action', function ($row) {
                    $html = '<td>';
                    $html = '<a data-id="'.$row->id.'" target="_blank" href="'.route('sales-master.show', $row->id).'" class="views avatar bg-light-success p-50 m-0" data-bs-toggle="tooltip" data-placement="left" title="View"><i class="fa fa-eye"></i></a>';
                    if (Gate::check('sales-master-edit')) {
                        $html .= ' <a href="'.route('sales-master.edit', $row->id).'" class="avatar bg-light-info p-50 m-0" data-bs-toggle="tooltip" data-placement="left" title="Edit"><i class="fa fa-edit"></i></a>';
                    }
                    if (Gate::check('sales-master-delete')) {
                        $html .= ' <a data-id="'.$row->id.'" href="javascript:void(0);" class="avatar bg-light-danger p-50 m-0 delete" data-bs-toggle="tooltip" data-placement="left" title="Delete"><i class="fa fa-trash"></i></a>';
                    }
                    $html .= '<a data-id="'.$row->id.'" href="javascript:void(0)" class="status-view avatar bg-light-warning p-50 m-0" data-bs-toggle="tooltip" data-placement="left" title="View"><i class="fa fa-stack-exchange"></i></a>';
                    if ($row->pending_amonut != '' && $row->pending_amonut != '0') {
                        $html .= '<a data-id="'.$row->id.'" data-amt="'.$row->pending_amonut.'" href="javascript:void(0)" class="payment-view avatar bg-light-success p-50 m-0" data-bs-toggle="tooltip" data-placement="left" title="View"><i class="fa fa-inr"></i></a>';
                    }

                    if ($row->installation_pending == '1' && $row->meter_application_done == '0') {
                        $html .= ' <a href="'.route('installation.edit', $row->id).'" class="avatar bg-light-secondary p-25" data-bs-toggle="tooltip" data-placement="left" title="Edit"><i class="fa fa-clock"></i></a>';
                    }

                    $html .= '</td>';

                    return $html;
                })
                ->addColumn('pdf', function ($row) {

                    if ($row->pending_approvel == '1') {
                        $html = '<div class="">';
                        $btn = 'btn-outline-secondary';

                        $html .= '<div class="btn-group p-0">
                        <button type="button" class="btn-sm btn '.$btn.'">PDF</button>
                        <button type="button" class="btn-sm btn '.$btn.' dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown"   aria-expanded="false">
                        <span class="visually-hidden">Toggle Dropdown</span>
                        </button>
                        <div class="dropdown-menu" container="body">';

                        $selectedPdf = getSelectedDiscom($row->discom);

                        if (count($selectedPdf) > 0) {
                            foreach ($selectedPdf as $k => $v) {
                                $status_to_show = $v['status_to_show'];
                                if ($row->$status_to_show == '1') {
                                    if ($v['id'] == 'declaration_dcr_general') {
                                        $html .= '<a class="dropdown-item declaration-dcr-link" data-id="'.$row->id.'" data-purchase_order_number="'.$row->purchase_order_number.'" data-purchase_order_date="'.$row->purchase_order_date.'" data-cell_manufacture_name="'.$row->cell_manufacture_name.'" data-cell_gst_invoice_no="'.$row->cell_gst_invoice_no.'" href="#">'.$v['display_name'].'</a>';
                                    } elseif (isset($v['route']) && $v['route'] != '') {
                                        $html .= '<a class="dropdown-item" href="'.route($v['route'], $row->id).'" target="_blank">'.$v['display_name'].'</a>';
                                    }
                                }
                            }
                        }

                        $html .= '</div>
                    </div>';

                        return $html;
                    } else {
                        return 'N/A';
                    }
                })
                ->addColumn('application_pending', function ($row) use ($user) {
                    $html = '<div class="">';
                    $btn = 'btn-outline-secondary';

                    $title = toGetSalesMasterLastStatus($row->id);

                    $html .= '<div class="btn-group p-0">
                        <button type="button" class="btn-sm btn '.$btn.'">'.$title.'</button>
                        <button type="button" class="btn-sm btn '.$btn.' dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false">
                        <span class="visually-hidden">Toggle Dropdown</span>
                        </button>
                        <div class="dropdown-menu" container="body">';

                    // <a class="dropdown-item status application-view" href="javascript:void(0);" data-id="' . $row->id . '" data-value="document_verified">Document Verified</a>

                    if ($user->roles[0]->name != 'Manager' && $user->roles[0]->name != 'Sales') {
                        $html .= '<a class="dropdown-item status application-view dispatch-pending-list" href="javascript:void(0);"
                        data-nos="'.((isset($row->salesquatation)) ? $row->salesquatation->penal_nos : '').'"
                        data-registerkw="'.((isset($row->panel) && $row->register_kw != null) ? $row->register_kw : '').'"
                        data-penal="'.((isset($row->panel) && $row->panel->name != null) ? $row->panel->name : '').'"
                        data-watt="'.((isset($row->panelwatt) && $row->panelwatt->name != null) ? $row->panelwatt->name : '').'"
                        data-inveter="'.((isset($row->inveter) && $row->inveter->name != null) ? $row->inveter->name : '').'"
                        data-id="'.$row->id.'" data-bomid="'.$row->bom_id.'" data-value="dispach_pending_list">Dispatch Pending List</a>';
                    }

                    $mid = $row->meter_installation_date ? date('d-m-Y', strtotime($row->meter_installation_date)) : '';
                    $srd = $row->subsidy_request_date ? date('d-m-Y', strtotime($row->subsidy_request_date)) : '';
                    $sdd = $row->subsidy_disbursement_date ? date('d-m-Y', strtotime($row->subsidy_disbursement_date)) : '';
                    $sdvd = $row->subsidy_disbursement_verify_date ? date('d-m-Y', strtotime($row->subsidy_disbursement_verify_date)) : '';

                    $html .= '<a class="dropdown-item status application-view" href="javascript:void(0);" data-id="'.$row->id.'" data-date="'.$mid.'" data-img="'.$row->proforma_15.'" data-value="meter_installation">Meter Installation</a>';

                    $hideSubsidy = ($row->ragistration_portal == 'GEDA' || ($row->ragistration_portal == 'National' && $row->subsidy_giveup));
                    if (! $hideSubsidy) {
                        $html .= '<a class="dropdown-item status application-view" href="javascript:void(0);" data-id="'.$row->id.'" data-date="'.$srd.'" data-date2="'.$sdvd.'" data-remark="'.$row->subsidy_disbursal_remark.'" data-portal-subsidy-amount="'.e($row->portalData?->subsidy_amount ?: 'N/A').'" data-portal-subsidy-return-date="'.e($row->portalData?->subsidy_return_date ?: 'N/A').'" data-portal-subsidy-return-to="'.e($row->portalData?->subsidy_return_to ?: 'N/A').'" data-portal-subsidy-return-comment="'.e($row->portalData?->subsidy_return_comment ?: 'N/A').'" data-value="subsidy_claimed">Subsidy Request</a>
                            <a class="dropdown-item status application-view" href="javascript:void(0);" data-id="'.$row->id.'" data-date="'.$sdd.'" data-value="subsidy_receveid">Subsidy Disbursal</a>';
                    }

                    $pcd = ($row->project_completion_date && $row->project_completion_date != '0000-00-00') ? date('d-m-Y', strtotime($row->project_completion_date)) : '';

                    if ($user->roles[0]->name != 'Manager' && $user->roles[0]->name != 'Sales') {
                        $html .= '<a class="dropdown-item status application-view" href="javascript:void(0);" data-id="'.$row->id.'" data-date="'.$pcd.'" data-value="project_completion">Project Completion</a>';
                    }

                    if ($user->roles[0]->name != 'Manager' && $user->roles[0]->name != 'Sales') {
                        $html .= '<a class="dropdown-item status application-view" href="javascript:void(0);" data-id="'.$row->id.'" data-value="hold_query" data-remark="'.$row->remark.'">Hold / Query</a>';
                    }

                    if ($user->roles[0]->name == 'Owner' && $row->meter_installation == '0') {
                        $html .= '<a class="dropdown-item status application-view" href="javascript:void(0);" data-id="'.$row->id.'" data-value="file_cancel_order" data-remark="'.$row->remark.'">File Cancel Order</a>';
                    }

                    $html .= '</div>
                    </div>';

                    return $html;
                })
                ->escapeColumns([])
                ->make(true);
        } else {
            $boms = BOM::get();
            $banks = Bank::orderBy('name', 'ASC')->get();
            $advanceFilterOptions = [
                'discoms' => SubDivision::query()->whereNotNull('discom')->where('discom', '!=', '')->distinct()->orderBy('discom')->pluck('discom')->all(),
                'circles' => SubDivision::query()->whereNotNull('circle_name')->where('circle_name', '!=', '')->distinct()->orderBy('circle_name')->pluck('circle_name')->all(),
                'divisions' => SubDivision::query()->whereNotNull('division_name')->where('division_name', '!=', '')->distinct()->orderBy('division_name')->pluck('division_name')->all(),
                'sub_divisions' => SubDivision::query()->whereNotNull('name')->orderBy('name')->pluck('name', 'id')->all(),
                'portal_statuses' => app(PortalStatusService::class)->statuses(),
            ];

            return view('admin.sale.view', compact('agentSalesPerson', 'boms', 'banks', 'advanceFilterOptions'));
        }
    }

    public function applicatonSave(Request $request)
    {
        try {
            $a = $request->status;
            if (toChangeStatus($request->status, $request->id)) {
                $salesMaster = SalesMaster::where('id', $request->id)->first();
                if ($request->has('subsidy_giveup')) {
                    $salesMaster->subsidy_giveup = $request->subsidy_giveup ? 1 : 0;
                }
                if ($a != 'hold_query' && $a != 'file_cancel_order') {
                    $salesMaster->hold_query = '0';
                    $salesMaster->file_cancel_order = '0';
                }
                if ($a == 'file_cancel_order') {
                    $salesMaster->remark = $request->remark;
                    $salesMaster->hold_query = '0';
                }
                if ($a == 'hold_query') {
                    $salesMaster->remark = $request->remark;
                    $salesMaster->file_cancel_order = '0';
                }

                if ($a == 'meter_installation') {
                    $salesMaster->meter_installation_date = ($request->meter_installation_date != '') ? date('Y-m-d', strtotime($request->meter_installation_date)) : '';
                    if (! empty($request->proforma_15)) {
                        $temp = $this->test($request->proforma_15);
                        $salesMaster->proforma_15 = $temp;
                    }
                }

                if ($a == 'subsidy_claimed') {
                    $salesMaster->subsidy_request_date = ($request->subsidy_request_date != '') ? date('Y-m-d', strtotime($request->subsidy_request_date)) : '';
                    $salesMaster->subsidy_disbursement_verify_date = ($request->subsidy_disbursement_verify_date != '') ? date('Y-m-d', strtotime($request->subsidy_disbursement_verify_date)) : '';
                    $salesMaster->subsidy_disbursal_remark = $request->subsidy_disbursal_remark;
                }

                if ($a == 'subsidy_receveid') {
                    $salesMaster->subsidy_disbursement_date = ($request->subsidy_disbursement_date != '') ? date('Y-m-d', strtotime($request->subsidy_disbursement_date)) : '';
                }

                if ($a == 'project_completion') {
                    $salesMaster->project_completion_date = ($request->project_completion_date != '') ? date('Y-m-d', strtotime($request->project_completion_date)) : null;
                }

                if ($a == 'dispach_pending_list') {
                    $salesMaster->bom_id = $request->bom_id;
                }

                $salesMaster->$a = '1';
                $salesMaster->save();
                $response = ['status' => true, 'message' => 'Status updated successfully.', 'data' => route('sales-master.index')];
            } else {
                $response = ['status' => false, 'server_error' => 'Can`t change this status... Please Fill Require Details...'];
            }

            return response()->json($response);
        } catch (\Exception $e) {
            $response = ['status' => false, 'server_error' => 'Something went wrong. Please try again.'];

            return response()->json($response);
        }
    }

    public function statusSave(Request $request)
    {
        try {
            $salesMaster = SalesMaster::where('id', $request->id)->first();
            $response = ['data' => route('sales-master.index'), 'status' => true, 'message' => ' Status updated successfully.'];
            $salesMaster->ragistration_portal = $request->ragistration_portal;
            $salesMaster->ragistration_number = $request->ragistration_number;
            $salesMaster->ragistration_date = ($request->ragistration_date != '') ? date('Y-m-d', strtotime($request->ragistration_date)) : '';
            $salesMaster->subsidy_giveup = $request->subsidy_giveup ? 1 : 0;
            if (! empty($request->ragistration_portal) && ! empty($request->ragistration_number)) {
                $salesMaster->pending_approvel = '1';

            }

            if (! empty($request->feasibility_letter)) {
                $temp2 = $this->test($request->feasibility_letter);
                $salesMaster->feasibility_letter = $temp2;
            }

            if (empty($request->ragistration_portal) || empty($request->ragistration_number)) {
                $salesMaster->pending_approvel = '0';
            }

            $salesMaster->feasibility_discom_sr_number = $request->feasibility_discom_sr_number;
            $salesMaster->feasibility_amount = $request->feasibility_amount;

            if ($request->feasibility_amount != '' && $request->feasibility_amount != '0') {
                $sales_quatation_id = $salesMaster->sales_quatation_id;
                $quatation = SalesQuatation::where('id', $sales_quatation_id)->first();

                $total_amt = ($quatation->total_amount - $quatation->meter_charges) + $request->feasibility_amount;
                $quatation->meter_charges = $request->feasibility_amount;
                $quatation->total_amount = $total_amt;
                $quatation->save();

                $findPayment = $salesMaster->total_amount - $salesMaster->pending_amonut;
                $salesMaster->total_amount = $total_amt;
                $salesMaster->pending_amonut = $total_amt - $findPayment;
            }

            $salesMaster->feasibility_date = ($request->feasibility_date != '') ? date('Y-m-d', strtotime($request->feasibility_date)) : '';

            if (! empty($request->feasibility_discom_sr_number)) {
                $salesMaster->feasibility_approved = '1';
            }
            if (empty($request->feasibility_discom_sr_number)) {
                $salesMaster->feasibility_approved = '0';
            }

            $salesMaster->invoice_no = $request->invoice_no;
            $salesMaster->invoice_date = ($request->invoice_date != '') ? date('Y-m-d', strtotime($request->invoice_date)) : '';
            $salesMaster->discom_sr_numbar = $request->feasibility_discom_sr_number;
            $installationPerson = $request->installation_asian_person;

            if ($installationPerson === 'none' || $installationPerson === '-- Select --') {
                $installationPerson = null;
            }

            $salesMaster->installation_asian_person = $installationPerson;

            $salesMaster->installation_date = ($request->payment_date != '') ? date('Y-m-d', strtotime($request->payment_date)) : '';
            $salesMaster->payment_ref_number = $request->payment_ref_number;

			$salesMaster->meter_charge_paid = '0';
			
			
            if ($salesMaster->feasibility_approved == '1' && $request->payment_date != '') {
                $salesMaster->meter_charge_paid = '1';
            }
            if ($salesMaster->feasibility_approved == '0') {
                $salesMaster->meter_charge_paid = '0';
            }

            if ($salesMaster->pending_approvel == '1' && $salesMaster->phase == 'Single phase') {
                $salesMaster->feasibility_approved = '1';
                $salesMaster->meter_charge_paid = '1';
            }

            $salesMaster->couriar_ditails = $request->couriar_ditails;
            $salesMaster->couriar_no = $request->couriar_no;
            $salesMaster->courair_company = $request->courair_company;
            $salesMaster->meter_application_date = ($request->meter_application_date != '') ? date('Y-m-d', strtotime($request->meter_application_date)) : '';
            $salesMaster->meter_asian_person = $request->meter_asian_person;

            if (! empty($request->meter_asian_person) && $salesMaster->installation_done == '1') {
                $salesMaster->meter_application_done = '1';
            }

            if (empty($request->meter_asian_person)) {
                $salesMaster->meter_application_done = '0';
            }

            if ($salesMaster->feasibility_approved == '1' && $salesMaster->payment_receveid == '1' && $salesMaster->bom_id != null) {
                $salesMaster->dispach_pending_list = '1';
            }

            if ($salesMaster->dispach_pending_list == '0' && ($salesMaster->feasibility_approved == '0' || $salesMaster->payment_receveid == '0')) {
                $salesMaster->dispach_pending_list = '0';
            }

            if (! empty($installationPerson) && $salesMaster->dispach_pending_list == '1') {
                $salesMaster->installation_pending = '1';
                //  installation amount set
                $installerUserId = (int) $installationPerson; // users.id
                $refDate = $salesMaster->master_create_date;
                $installSlab = UserCommission::where('user_id', $installerUserId)
                    ->where('sub_agent_id', 0)
                    ->where('effective_date', '<=', $refDate)
                    ->orderBy('effective_date', 'desc')
                    ->first();
                $installRate = $installSlab ? (float) $installSlab->installation : 0.0;
                $salesMaster->installation_amount = $installRate;
            } else {
                $salesMaster->installation_pending = '0';
            }

            if (! empty($request->payment_receipt)) {
                $temp = $this->test($request->payment_receipt);
                $salesMaster->payment_receipt = $temp;
            }

            if (! empty($request->meter_application_oc)) {
                $temp1 = $this->test($request->meter_application_oc);
                $salesMaster->meter_application_oc = $temp1;
            }

            $salesMaster->commission_amount = $request->commission_amount;
            $salesMaster->sub_commission_amount = $request->sub_commission_amount;
            if ($salesMaster->installation_amount != $request->installation_amount && $request->installation_amount != '') {
                $salesMaster->installation_amount = $request->installation_amount;
            }

            $salesMaster->remark = $request->remark;

            if ($salesMaster->file_type == 'L') {
                $salesLoan = Loandata::where('sales_master_id', $request->sales_master_id)->first();
                if (is_null($salesLoan)) {
                    $salesLoan = new Loandata();
                }

                if ($request->loan_portal != '' || ($request->application_no != '' && $request->submitted_at != '')) {
                    $salesMaster->apply_for_loan = '1';
                    $salesMaster->login = '1';
                } else {
                    $salesMaster->apply_for_loan = '0';
                    $salesMaster->login = '0';
                }

                if ($request->loan_sanction_date != '' && $request->approved_amount != '') {
                    $salesMaster->loan_sension = '1';
                } else {
                    $salesMaster->loan_sension = '0';
                }

                $salesLoan->sales_master_id = $request->sales_master_id;
                $salesLoan->loan_portal = $request->loan_portal ?? '';
                $salesLoan->application_no = $request->application_no ?? '';
                $salesLoan->submitted_at = $request->submitted_at != '' ? date('Y-m-d', strtotime($request->submitted_at)) : '';
                $salesLoan->approved_amount = $request->approved_amount ?? 0;
                $salesLoan->tenure = $request->tenure ?? 0;
                $salesLoan->roi = $request->roi ?? 0;
                $salesLoan->emi = $request->emi ?? 0;
                $salesLoan->processing_fee_per = $request->processing_fee_per ?? 0;
                $salesLoan->documentation_charges_tax = $request->documentation_charges_tax ?? 0;
                $salesLoan->documentation_charges = $request->documentation_charges ?? 0;
                $salesLoan->processing_fee_tax = $request->processing_fee_tax ?? 0;
                $salesLoan->processing_fee = $request->processing_fee ?? 0;
                $salesLoan->downpayment = $request->downpayment ?? 0;
                $salesLoan->stamp_fee = $request->stamp_fee ?? 0;
                $salesLoan->margin_money_amount = $request->margin_money_amount ?? 0;
                $salesLoan->remark = $request->loan_remark ?? 0;
                $salesLoan->loan_sanction_date = $request->loan_sanction_date != '' ? date('Y-m-d', strtotime($request->loan_sanction_date)) : '';

                if (! empty($request->loan_pdf)) {
                    $temppdf = $this->test($request->loan_pdf);
                    $salesLoan->loan_pdf = $temppdf;
                }

                $salesLoan->save();

            }

            $salesMaster->save();


            DB::commit();
            $response = ['status' => true, 'message' => 'Save Successfully.'];

            return response()->json($response);
        } catch (\Exception $e) {
            DB::rollback();
            $response = ['status' => false, 'server_error' => 'Something went wrong. Please try again.'];

            return response()->json($response);
        }
    }

    public function create()
    {
        $district = District::get();
        $taluka = Taluka::get();
        $subDivision = SubDivision::get();

        $inveter_company = InveterCompany::get();
        $penal_company = PenalCompany::get();
        $penal_watt = PenalWatt::get();

        $where = '1 = 1';
        $company = CompanyProfile::where('user_id', Auth::id())->first();
        if ($company->user_type == 'M') {
            $agent = AgentSalesPerson::where('user_id', Auth::id())->first();
            $agentIds = [$agent->id];
            $assignIds = [$agent->id];
            $sales = CompanyProfile::select('id', 'user_id')->where('manager_id', $company->id)->get();
            if ($sales->count() > 0) {
                foreach ($sales as $k => $v) {
                    array_push($agentIds, $v->user_id);
                    array_push($assignIds, $v->id);
                }
            }
            $where .= ' AND (agent_sales_person_id IN('.implode(',', $agentIds).') OR assign_id IN('.implode(',', $assignIds).'))';
        }

        if ($company->user_type == 'S') {
            $agent = AgentSalesPerson::where('user_id', Auth::id())->first();
            $id = $agent->id;
            $where .= ' AND ((agent_sales_person_id = '.$id.' OR assign_id = '.$id.') OR (assign_id = '.$company->id.'))';
        }

        $querySalesQuatations = SalesQuatation::with('penalWatt')
            ->whereRaw('NOT EXISTS (SELECT 1 FROM sales_masters WHERE sales_masters.sales_quatation_id = sales_quatations.id)')
            ->where('current_status', 'accepted');
        $company = CompanyProfile::where('user_id', Auth::id())->first();
        if ($company->user_type == 'M') {
            $agent = AgentSalesPerson::where('user_id', Auth::id())->first();
            $agentIds = [$agent->id];
            $sales = CompanyProfile::select('company_profiles.id', 'company_profiles.user_id', 'agent_sales_people.id as agent_id')
                ->leftJoin('agent_sales_people', 'agent_sales_people.user_id', 'company_profiles.user_id')
                ->where('company_profiles.manager_id', $company->id)->get();
            if ($sales->count() > 0) {
                foreach ($sales as $k => $v) {
                    array_push($agentIds, $v->agent_id);
                }
            }
            $querySalesQuatations->whereIn('agent_sales_person_id', $agentIds);
        }
        if ($company->user_type == 'S') {
            $agent = AgentSalesPerson::where('user_id', Auth::id())->first();
            $id = $agent->id;
            $querySalesQuatations->where('agent_sales_person_id', $id);
        }

        $sales_quatation = $querySalesQuatations->where('form_type', '!=', 'trading')->orderBy('id', 'DESC')->get();

        $companyFind = CompanyProfile::where('user_id', Auth::id())->first();
        $agentWhere = '';
        if ($companyFind->user_type == 'M') {
            $id = $companyFind->id;
            $agentWhere .= 'company_profiles.user_id = '.Auth::id().' OR  company_profiles.manager_id = '.$id;
        }
        if ($companyFind->user_type == 'S') {
            $id = $companyFind->id;
            $manager_id = $companyFind->manager_id;
            $agentWhere .= 'company_profiles.id = '.$id.' OR  company_profiles.id = '.$manager_id;
        }

        $q = CompanyProfile::select('agent_sales_people.*')->leftJoin('agent_sales_people', 'agent_sales_people.user_id', 'company_profiles.user_id');
        if ($agentWhere != '') {
            $q->whereRaw($agentWhere);
        }

        $agentSalesPerson = $q->get();
        $boms = BOM::get();

        return view('admin.sale.add', compact('boms', 'penal_watt', 'inveter_company', 'penal_company', 'district', 'agentSalesPerson', 'taluka', 'subDivision', 'sales_quatation'));
    }

    public function store(Request $request)
    {
        if (is_null($request->sales_master_id)) {
            abort_unless(Gate::allows('sales-master-create'), 403);
        } else {
            abort_unless(Gate::allows('sales-master-edit'), 403);
        }

        if (! is_null($request->sales_master_id)) {
            $consumer_number = [
                'required',
                Rule::unique('sales_masters')->where(function ($query) {
                    return $query->where('deleted_at', null);
                })->ignore($request->sales_master_id),
            ];
        } else {
            $consumer_number = [
                'required',
                Rule::unique('sales_masters')->where(function ($query) {
                    return $query->where('deleted_at', null);
                }),
            ];
        }

        $validator = Validator::make($request->all(), [
            'penal_company_id' => 'required',
            'inveter_company_id' => 'required',
            'penal_watt_id' => 'required',
            'consumer_number' => $consumer_number,
            'sales_quatation_id' => 'required',
            'consumer_type' => 'required',
            'district_id' => 'required',
            'taluka_id' => 'required',
            'pin_code' => 'required',
            'contact_number' => 'required',
            // 'discom' => 'required',
            'agent_sales_person_id' => 'required',

        ], [
            'penal_company_id' => 'Select Panel Company Name',
            'inveter_company_id' => 'Select Inveter Company Name',
            'penal_watt_id' => 'Select Panel Watt',
            'sales_quatation_id.required' => 'Select Quotation',
            'consumer_number.required' => 'Enter Consumer Number',
            'consumer_number.unique' => 'The Consumer Number has already been taken',
            'consumer_type.required' => 'Enter Consumer Type',
            'district_id.required' => 'Enter District',
            'taluka_id.required' => 'Enter Taluka',
            'pin_code.required' => 'Enter Pincode',
            'taluka.required' => 'Enter Taluka',
            'contact_number.required' => 'Enter Contact Number',
            // 'discom.required' => 'Enter discom',
            'agent_sales_person_id' => 'Choose Agent/Sales Person Name',
        ]);

        if ($validator->fails()) {
            $response = ['status' => false, 'message' => 'Please input proper data.', 'errors' => $validator->errors()];

            return response()->json($response);
        }
        DB::beginTransaction();
        try {
            if (! is_null($request->sales_master_id)) {
                $salesMaster = SalesMaster::where('id', $request->sales_master_id)->first();
                $response = ['data' => route('sales-master.index'), 'status' => true, 'message' => ' Sales Master updated successfully.'];
            } else {
                $salesMaster = new SalesMaster();
                $response = ['data' => route('sales-master.index'), 'status' => true, 'message' => ' Sales Master added successfully.'];
				$salesMaster->master_create_date = date('Y-m-d');           
		   }
            $salesMaster->user_id = Auth::id();
            $salesMaster->penal_company_id = $request->penal_company_id;
            $salesMaster->inveter_company_id = $request->inveter_company_id;
            $salesMaster->penal_watt_id = $request->penal_watt_id;
            $salesMaster->lead_master_id = $request->lead_master_id;
            $salesMaster->sales_quatation_id = $request->sales_quatation_id;
            $salesMaster->consumer_number = $request->consumer_number;
            
            $salesMaster->consumer_type = $request->consumer_type;
            $salesMaster->consumer_name = strtoupper($request->consumer_name);
            $salesMaster->gst_number = strtoupper($request->gst_number);
            $salesMaster->district_id = $request->district_id;
            $salesMaster->taluka_id = $request->taluka_id;
            $salesMaster->village_id = 0;
            $salesMaster->pin_code = $request->pin_code;
            $salesMaster->contact_number = $request->contact_number;
            $salesMaster->register_kw = $request->register_kw;
            $salesMaster->email = $request->email;
            $salesMaster->address = $request->address;
            $salesMaster->aadhaar_number = $request->aadhaar_number;
            $salesMaster->bank_name = $request->bank_name;
            $salesMaster->bank_account = $request->bank_account;
            $salesMaster->ifsc_code = $request->ifsc_code;
            $salesMaster->contracted_load = $request->contracted_load;
            $salesMaster->phase = $request->phase;
            $salesMaster->division = $request->division;
            $salesMaster->sub_division_id = (! empty($request->sub_division_id)) ? $request->sub_division_id : 0;
            $salesMaster->circle = $request->circle;
            $salesMaster->discom = $request->discom;
            $salesMaster->reference = $request->reference;
            $salesMaster->bom_id = (! empty($request->bom_id)) ? $request->bom_id : 0;
            $salesMaster->file_type = (! empty($request->file_type)) ? $request->file_type : 'C';

            if ($salesMaster->total_amount != $salesMaster->pending_amonut) {
                $recevied = (float) $salesMaster->total_amount - (float) $salesMaster->pending_amonut;

                $salesMaster->total_amount = $request->total_amount;
                $salesMaster->pending_amonut = (float) $request->total_amount - (float) $recevied;
            } else {
                $salesMaster->total_amount = $request->total_amount;
                $salesMaster->pending_amonut = $request->total_amount;
            }

            $salesMaster->agent_sales_person_id = $request->agent_sales_person_id;
            $salesMaster->remark = $request->remark;
            $result = $salesMaster->save();

            // commission
            if (! empty($salesMaster->agent_sales_person_id) && ! empty($salesMaster->register_kw)) {
                $agentId = (int) $salesMaster->agent_sales_person_id;
                $agentUserId = AgentSalesPerson::where('id', $agentId)->value('user_id');
                $refDate = $salesMaster->master_create_date;

                // Main commission
                $mainSlab = UserCommission::where('user_id', $agentUserId)
                    ->where('sub_agent_id', 0)
                    ->where('effective_date', '<=', $refDate)
                    ->orderBy('effective_date', 'desc')
                    ->first();
                $mainRate = $mainSlab ? (float) $mainSlab->commission : 0.0;
                $salesMaster->commission_amount = $mainRate;

                // Sub-commission
                $subAgent = CompanyProfile::where('user_id', $agentUserId)->value('id');
                $subSlab = UserCommission::where('sub_agent_id', $subAgent)
                    ->where('effective_date', '<=', $refDate)
                    ->orderBy('effective_date', 'desc')
                    ->first();
                $subRate = $subSlab ? (float) $subSlab->commission : 0.0;
                $salesMaster->sub_commission_amount = $subRate;

                $salesMaster->save();
            }

            if (isset($request->document) && count($request->document) > 0) {
                foreach ($request->document as $key => $value) {
                    if (! is_null($value)) {

                        if (isset($value['document_id'])) {
                            $document = Document::where('id', $value['document_id'])->first();
                        } else {
                            $document = new Document();
                        }
                        if ($value['name'] != '') {
                            if (! empty($value['image'])) {
                                $temp = $this->test($value['image']);
                                $document->image = $temp;
                            }
                            $document->sales_master_id = $salesMaster->id;
                            $document->name = $value['name'];
                            $document->save();
                        }
                    }
                }
            }

            DB::commit();
            if (! is_null($result)) {
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

    public function test($file)
    {
        if (! empty($file)) {
            $PhotosDir = 'upload/document/';
            if (! file_exists($PhotosDir)) {
                mkdir($PhotosDir, 0777, true);
            }
            $extension = $file->getClientOriginalExtension();
            $filename = 'img-'.time().'-'.uniqid().'.'.$extension;
            $file->move('upload/document/', $filename);

            return $filename;
        }

        return false;
    }

    public function show($id)
    {
        $salesMaster = SalesMaster::with('allpanels', 'district', 'subDivisionPDF', 'agentsalesperson', 'taluka', 'document', 'lead', 'installation', 'installation.panelwatt', 'installation.panelcompany', 'installation.paneltype', 'installation.installationPenals', 'installation.invater', 'installation.invater.company', 'installation.penalImage', 'installation.invaterImages', 'panel', 'panelwatt', 'inveter')->where('id', $id)->first();
        $payment = PaymetCollection::where('sales_master_id', $salesMaster->id)->get();
        $portalData = PortalData::where('sales_master_id', $salesMaster->id)->first();

        if (! is_null($salesMaster)) {
            $percentage = 0;
            $salesloan = Loandata::where('sales_master_id', $salesMaster->id)->first();
            if ($salesMaster->file_type == 'L' && ! is_null($payment)) {
                $loanData = Loandata::where('sales_master_id', $salesMaster->id)->first();
                if (! is_null($loanData)) {
                    $amount = $loanData->approved_amount;
                    $disbursement = 0;
                    foreach ($payment as $k => $v) {
                        if ($v->payment_type == 'Disbursement') {
                            $disbursement += $v->amount;
                        }
                    }

                    if ($amount > 0) {
                        $percentage = ($disbursement / $amount) * 100;
                    }

                }
            }

            return view('admin.sale.model', compact('salesMaster', 'payment', 'percentage', 'salesloan', 'portalData'));
        } else {
            return abort(404);
        }
    }

    public function view($id)
    {
        $user = User::where('id', '!=', '1')->get();
        $salesMaster = SalesMaster::where('id', $id)->first();
        if (! is_null($salesMaster)) {
            $data['html'] = view('admin.sale.application_model', compact('salesMaster', 'user'))->render();

            return response()->json($data);
        } else {
            return abort(404);
        }
    }

    public function documentView($id)
    {
        $user = User::where('id', '!=', '1')->get();
        $salesMaster = SalesMaster::where('id', $id)->first();

        if (! is_null($salesMaster)) {
            $data['html'] = view('admin.sale.document_model', compact('salesMaster', 'user'))->render();

            return response()->json($data);
        } else {
            return abort(404);
        }
    }

    public function downloadDocument(Request $request)
    {
        // Strip any accidental directory prefix from the filename
        $filename = $request->file;

        // Folders to search for the file, in order
        $folders = [
            'upload/document',
            'uploads/site_visit_images',
            'uploads/penal',
            'uploads/invater',
        ];

        // Also try common alternate spellings so nothing breaks on the live server
        $folders[] = 'uploads/document';
        $folders[] = 'upload/site_visit_images';
        $folders[] = 'uploads/panel';
        $folders[] = 'uploads/inverter';
        $folders[] = 'uploads/inveters';

        $path = null;
        foreach ($folders as $folder) {
            $candidate = public_path($folder . '/' . $filename);
            if (is_file($candidate)) {
                $path = $candidate;
                break;
            }
        }

        if (!$path) {
            return abort(404);
        }

        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $imageExts = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];

        // Only attempt conversion for images, and only when GD is available
        if (in_array($ext, $imageExts) && function_exists('imagejpeg')) {
            try {
                $info = @getimagesize($path);
                if ($info !== false) {
                    // Pick the loader by the actual MIME type (files are often
                    // stored with the wrong extension), with an extension fallback.
                    $mimeLoaders = [
                        'image/png' => 'imagecreatefrompng',
                        'image/gif' => 'imagecreatefromgif',
                        'image/bmp' => 'imagecreatefrombmp',
                        'image/webp' => 'imagecreatefromwebp',
                        'image/jpeg' => 'imagecreatefromjpeg',
                    ];
                    $extLoaders = [
                        'png' => 'imagecreatefrompng',
                        'gif' => 'imagecreatefromgif',
                        'bmp' => 'imagecreatefrombmp',
                        'webp' => 'imagecreatefromwebp',
                        'jpg' => 'imagecreatefromjpeg',
                        'jpeg' => 'imagecreatefromjpeg',
                    ];
                    $loader = $mimeLoaders[$info['mime']] ?? ($extLoaders[$ext] ?? null);

                    $im = ($loader && function_exists($loader)) ? @$loader($path) : false;

                    if (!$im && $loader !== ($extLoaders[$ext] ?? null) && function_exists($extLoaders[$ext] ?? '')) {
                        $im = @($extLoaders[$ext])($path);
                    }

                    if ($im) {
                        // Handle transparency for PNG/GIF by filling white background
                        if (in_array($ext, ['png', 'gif']) && in_array($info['mime'], ['image/png', 'image/gif'])) {
                            $bg = imagecreatetruecolor(imagesx($im), imagesy($im));
                            $white = imagecolorallocate($bg, 255, 255, 255);
                            imagefilledrectangle($bg, 0, 0, imagesx($im), imagesy($im), $white);
                            imagecopy($bg, $im, 0, 0, 0, 0, imagesx($im), imagesy($im));
                            imagedestroy($im);
                            $im = $bg;
                        }

                        ob_start();
                        imagejpeg($im, null, 100);
                        $jpgData = ob_get_clean();
                        imagedestroy($im);

                        if ($jpgData !== false && strlen($jpgData) > 0) {
                            $baseName = pathinfo($filename, PATHINFO_FILENAME);
                            $tempPath = sys_get_temp_dir() . '/' . uniqid('doc_') . '.jpg';
                            if (file_put_contents($tempPath, $jpgData) !== false) {
                                return response()->download($tempPath, $baseName . '.jpg')->deleteFileAfterSend(true);
                            }
                        }
                    }
                }
            } catch (\Exception $e) {
                // fallthrough to original file
            } catch (\Error $e) {
                // fallthrough to original file
            }
        }

        // Fallback / non-image: serve the file as-is
        return response()->download($path, $filename);
    }

    public function statusView($id, Request $request)
    {
        $type = (isset($request->type) && $request->type != '') ? $request->type : '';
        $user = User::where('id', '!=', '1')->get();
        $salesMaster = SalesMaster::where('id', $id)->first();
        if (! is_null($salesMaster)) {
            $salesloan = Loandata::where('sales_master_id', $salesMaster->id)->first();
            $portalData = PortalData::where('sales_master_id', $salesMaster->id)->first();
            $data['html'] = view('admin.sale.status_model', compact('salesMaster', 'user', 'type', 'salesloan', 'portalData'))->render();

            return response()->json($data);
        } else {
            return abort(404);
        }
    }

    public function edit($id)
    {
        $salesMaster = SalesMaster::with('document')->where('id', $id)->first();
        $district = District::get();
        $taluka = Taluka::where('district_id', $salesMaster->district_id)->get();
        $subDivision = SubDivision::get();
        $inveter_company = InveterCompany::get();
        $penal_company = PenalCompany::get();
        $penal_watt = PenalWatt::get();
        $where = '1 = 1';
        $company = CompanyProfile::where('user_id', Auth::id())->first();
        if ($company->user_type == 'M') {
            $agent = AgentSalesPerson::where('user_id', Auth::id())->first();
            $agentIds = [$agent->id];
            $assignIds = [$agent->id];
            $sales = CompanyProfile::select('id', 'user_id')->where('manager_id', $company->id)->get();
            if ($sales->count() > 0) {
                foreach ($sales as $k => $v) {
                    array_push($agentIds, $v->user_id);
                    array_push($assignIds, $v->id);
                }
            }
            $where .= ' AND (agent_sales_person_id IN('.implode(',', $agentIds).') OR assign_id IN('.implode(',', $assignIds).'))';
        }

        if ($company->user_type == 'S') {
            $agent = AgentSalesPerson::where('user_id', Auth::id())->first();
            $id = $agent->id;
            $where .= ' AND ((agent_sales_person_id = '.$id.' OR assign_id = '.$id.') OR (assign_id = '.$company->id.'))';
        }
        $lead_complete = LeadMaster::whereRaw($where)->where('status', '1')->orderBy('id', 'DESC')->get();
        $querySalesQuatations = SalesQuatation::with('penalWatt')
            ->whereRaw('(NOT EXISTS (SELECT 1 FROM sales_masters WHERE sales_masters.sales_quatation_id = sales_quatations.id)) OR sales_quatations.id = '.$salesMaster->sales_quatation_id);
        $company = CompanyProfile::where('user_id', Auth::id())->first();
        if ($company->user_type == 'M') {
            $agent = AgentSalesPerson::where('user_id', Auth::id())->first();
            $agentIds = [$agent->id];
            $sales = CompanyProfile::select('company_profiles.id', 'company_profiles.user_id', 'agent_sales_people.id as agent_id')
                ->leftJoin('agent_sales_people', 'agent_sales_people.user_id', 'company_profiles.user_id')
                ->where('company_profiles.manager_id', $company->id)->get();
            if ($sales->count() > 0) {
                foreach ($sales as $k => $v) {
                    array_push($agentIds, $v->agent_id);
                }
            }
            $querySalesQuatations->whereIn('agent_sales_person_id', $agentIds);
        }
        if ($company->user_type == 'S') {
            $agent = AgentSalesPerson::where('user_id', Auth::id())->first();
            $id = $agent->id;
            $querySalesQuatations->where('agent_sales_person_id', $id);
        }
        $sales_quatation = $querySalesQuatations->where('form_type', '!=', 'trading')->orderBy('id', 'DESC')->get();
        $companyFind = CompanyProfile::where('user_id', Auth::id())->first();
        $agentWhere = '';
        if ($companyFind->user_type == 'M') {
            $id = $companyFind->id;
            $agentWhere .= 'company_profiles.user_id = '.Auth::id().' OR  company_profiles.manager_id = '.$id;
        }
        if ($companyFind->user_type == 'S') {
            $id = $companyFind->id;
            $manager_id = $companyFind->manager_id;
            $agentWhere .= 'company_profiles.id = '.$id.' OR  company_profiles.id = '.$manager_id;
        }

        $q = CompanyProfile::select('agent_sales_people.*')->leftJoin('agent_sales_people', 'agent_sales_people.user_id', 'company_profiles.user_id');
        if ($agentWhere != '') {
            $q->whereRaw($agentWhere);
        }

        $agentSalesPerson = $q->get();
        $boms = BOM::get();

        return view('admin.sale.add', compact('boms', 'penal_watt', 'inveter_company', 'penal_company', 'salesMaster', 'agentSalesPerson', 'district', 'taluka', 'subDivision', 'lead_complete', 'sales_quatation'));
    }

    public function update(Request $request, $id)
    {
        $document = Document::where('id', $id)->first();
        $path = 'upload/document/'.$document->image;
        if ($document->image) {
            if (File::exists($path)) {
                unlink($path);
            }
        }
        $document->delete();
        $response = ['status' => true, 'message' => 'Remove Successfully.'];

        return response()->json($response);
    }

    public function destroy($id)
    {
        try {
            $salesMaster = SalesMaster::where('id', $id)->first();
            $paymetCollection = PaymetCollection::where('sales_master_id', $id)->get();
            if ($paymetCollection->count() == 0) {
                Document::where('sales_master_id', $id)->delete();
                $salesMaster->delete();
                $response = ['status' => true, 'message' => ' Deleted successfully.'];
            } else {
                $response = ['status' => false, 'server_error' => 'Sorry, you are unable to delete this order as payment has already been received for it.'];
            }

            return response()->json($response);
        } catch (\Exception $e) {
            $response = ['status' => false, 'server_error' => 'Something went wrong. Please try again.'];

            return response()->json($response);
        }
    }

    public function selfCertificationPdf($id)
    {
        $type = request()->segment(3);

        $order_data = SalesMaster::where('id', $id)->first();
        $installation_data = Installation::with('panelwatt', 'panelcompany', 'paneltype')->where('sales_master_id', $id)->first();
        $installation_invater_data = InstallationInvater::select('installation_invaters.*', 'inveter_companies.name', 'item_groups.inverter_type')
            ->leftJoin('inveter_companies', 'installation_invaters.invater_id', '=', 'inveter_companies.id')
            ->leftJoin('item_groups', 'installation_invaters.item_group_id', '=', 'item_groups.id')
            ->where('sales_master_id', $id)
            ->groupBy('invater_id')->get();
        $installation_panel_data = InstallationPenal::where('sales_master_id', $id)->get();
        $data = [
            'order_data' => $order_data,
            'installation_data' => $installation_data,
            'installation_invater_data' => $installation_invater_data,
            'installation_panel_data' => $installation_panel_data,
            'type' => $type,
        ];
        $pdf = Pdf::loadView('admin.sale.pdf.general.self_certification_pdf', $data);

        // return $pdf->stream('self_certification.pdf');
        return $pdf->download('self_certification.pdf');
    }

    public function requestLetterPdf($id)
    {
        $order_data = SalesMaster::with('subDivisionPDF')->where('id', $id)->first();
        if ($order_data->sub_division_id != 0) {
            $installation_data = Installation::with('panelwatt', 'panelcompany', 'paneltype')->where('sales_master_id', $id)->first();

            $data = [
                'order_data' => $order_data,
                'installation_data' => $installation_data,
            ];
            $pdf = Pdf::loadView('admin.sale.pdf.general.request_letter_pdf', $data);

            // return $pdf->stream('request_letter.pdf');
            return $pdf->download('request_letter.pdf');
        } else {
            return redirect()->back()->with('message', 'Please Select Sub Division');
        }
    }

    public function modelAgreementPdf($id)
    {
        $type = request()->segment(3);

        $customerSign = [];
        if ($type == '1') {
            $customerSign = Document::where('sales_master_id', $id)->where('name', 'customer_signture')->first();
        }
        $order_data = SalesMaster::where('id', $id)->first();
        $installation_data = Installation::with('panelwatt', 'panelcompany', 'paneltype')->where('sales_master_id', $id)->first();
        $installation_invater_data = InstallationInvater::select('installation_invaters.*', 'inveter_companies.name')
            ->leftJoin('inveter_companies', 'installation_invaters.invater_id', '=', 'inveter_companies.id')
            ->where('sales_master_id', $id)
            ->groupBy('invater_id')->get();
        $data = [
            'order_data' => $order_data,
            'installation_data' => $installation_data,
            'installation_invater_data' => $installation_invater_data,
            'type' => $type,
            'customer_sign' => $customerSign,
        ];
        $pdf = Pdf::loadView('admin.sale.pdf.general.model_aggriment_pdf', $data);

        // return $pdf->stream('model_aggriment.pdf');
        return $pdf->download('model_aggriment.pdf');
    }

    public function declarationDCRPdf($id)
    {
        $order_data = SalesMaster::where('id', $id)->first();
        $installation_data = Installation::with('panelwatt', 'panelcompany', 'paneltype')->where('sales_master_id', $id)->first();
        $installation_invater_data = InstallationInvater::select('installation_invaters.*', 'inveter_companies.name')
            ->leftJoin('inveter_companies', 'installation_invaters.invater_id', '=', 'inveter_companies.id')
            ->where('sales_master_id', $id)
            ->groupBy('invater_id')->get();
        $installation_panel_data = InstallationPenal::where('sales_master_id', $id)->get();
        $data = [
            'order_data' => $order_data,
            'installation_data' => $installation_data,
            'installation_invater_data' => $installation_invater_data,
            'installation_panel_data' => $installation_panel_data,
        ];
        $pdf = Pdf::loadView('admin.sale.pdf.general.declaration_dcr_pdf', $data);

        // return $pdf->stream('declaration_dcr.pdf');
        return $pdf->download('declaration_dcr.pdf');
    }

    public function agreementPdf($id)
    {
        $order_data = SalesMaster::where('id', $id)->first();
        if ($order_data->sub_division_id != 0) {

            $discom = Discom::where('discom_name', $order_data->discom)->first();
            $discom_address = (! is_null($discom)) ? $discom->address : '___________________________________________________';

            $type = request()->segment(3);

            $customerSign = [];
            if ($type == '1') {
                $customerSign = Document::where('sales_master_id', $id)->where('name', 'customer_signture')->first();
            }

            $data = [
                'order_data' => $order_data,
                'discom_address' => $discom_address,
                'type' => $type,
                'customer_sign' => $customerSign,
            ];
            $pdf = Pdf::loadView('admin.sale.pdf.general.agreement_pdf', $data);

            // return $pdf->stream('agreement.pdf');
            return $pdf->download('agreement.pdf');
        } else {
            return redirect()->back()->with('message', 'Please Select Sub Division');
        }
    }

    public function gedaAgreementPdf($id)
    {
        $order_data = SalesMaster::where('id', $id)->first();
        if ($order_data->sub_division_id != 0) {
            $discom = Discom::where('discom_name', $order_data->discom)->first();
            $discom_address = (! is_null($discom)) ? $discom->address : '___________________________________________________';

            $data = [
                'order_data' => $order_data,
                'discom_address' => $discom_address,
            ];
            $pdf = Pdf::loadView('admin.sale.pdf.general.geda_agreement_pdf', $data);

            //return $pdf->stream('geda-agreement.pdf');
            return $pdf->download('geda-agreement.pdf');
        } else {
            return redirect()->back()->with('message', 'Please Select Sub Division');
        }
    }
	
	  public function pmsgmbyCommissioningPdf($id)
    {
        $order_data = SalesMaster::where('id', $id)->first();
        if ($order_data->sub_division_id != 0) {
            $data = [
                'order_data' => $order_data
            ];
            $pdf = Pdf::loadView('admin.sale.pdf.general.pmsgmby_commissioning_pdf', $data);

            //return $pdf->stream('geda-agreement.pdf');
            return $pdf->download('pmsgmby_commissioning.pdf');
        } else {
            return redirect()->back()->with('message', 'Please Select Sub Division');
        }
    }

    /* Net Metering inter connection agreement (Recommended for RJ) */
    public function netMeteringInterConnectionPdf($id)
    {
        $order_data = SalesMaster::where('id', $id)->first();
        if ($order_data->sub_division_id != 0) {
            $discom = Discom::where('discom_name', $order_data->discom)->first();
            $discom_address = (! is_null($discom)) ? $discom->address : '___________________________________________________';

            $data = [
                'order_data' => $order_data,
                'discom_address' => $discom_address,
            ];
            $pdf = Pdf::loadView('admin.sale.pdf.rajasthan.net_metering_inter_connectionagreement_pdf', $data);

            //return $pdf->stream('agreement.pdf');
            return $pdf->download('net-metering-inter-connection-agreement.pdf');
        } else {
            return redirect()->back()->with('message', 'Please Select Sub Division');
        }
    }
    /* / Net Metering inter connection agreement (Recommended for RJ) */

    /* Vendor Feasibility (Recommended for RJ) */
    public function vendorFeasibilityPdf($id)
    {
        $order_data = SalesMaster::with('district', 'district.state', 'taluka')->where('id', $id)->first();
        $bank_data = Bank::first();
        if ($order_data->sub_division_id != 0) {
            $loandata = Loandata::where('sales_master_id', $id)->first();
            $data = [
                'order_data' => $order_data,
                'bank_data' => $bank_data,
                'loandata' => $loandata,
            ];
            $pdf = Pdf::loadView('admin.sale.pdf.rajasthan.vendor_feasibility_pdf', $data);

            // return $pdf->stream('agreement.pdf');
            return $pdf->download('vendor-feasibility.pdf');
        } else {
            return redirect()->back()->with('message', 'Please Select Sub Division');
        }
    }
    /* / Vendor Feasibility (Recommended for RJ) */

    public function netMeterPdf($id)
    {
        $order_data = SalesMaster::where('id', $id)->first();
        $installation_data = Installation::with('panelwatt', 'panelcompany', 'paneltype')->where('sales_master_id', $id)->first();
        $installation_invater_data = InstallationInvater::select('installation_invaters.*', 'inveter_companies.name')
            ->leftJoin('inveter_companies', 'installation_invaters.invater_id', '=', 'inveter_companies.id')
            ->where('sales_master_id', $id)
            ->groupBy('invater_id')->get();
        $installation_panel_data = InstallationPenal::where('sales_master_id', $id)->get();
        $discom = Discom::where('discom_name', $order_data->discom)->first();
        $discom_address = (! is_null($discom)) ? $discom->address : '___________________________________________________';

        $data = [
            'order_data' => $order_data,
            'installation_data' => $installation_data,
            'installation_invater_data' => $installation_invater_data,
            'installation_panel_data' => $installation_panel_data,
            'discom_address' => $discom_address,
        ];

        $pdf = Pdf::loadView('admin.sale.pdf.rajasthan.net_meter_pdf', $data);

        //return $pdf->stream('net-meter.pdf');
        return $pdf->download('net-meter.pdf');
    }

    public function removeStatus(Request $request)
    {
        try {
            $salesMaster = SalesMaster::where('id', $request->id)->first();
            $status = $request->status;
            $salesMaster->$status = '0';
            if ($status == 'project_completion') {
                $salesMaster->project_completion_date = null;
            }
            $salesMaster->save();
            $response = ['status' => true, 'message' => ' Remove successfully.'];

            return response()->json($response);
        } catch (\Exception $e) {
            $response = ['status' => false, 'server_error' => 'Something went wrong. Please try again.'];

            return response()->json($response);
        }
    }

    public function salesOrderReport(Request $request)
    {
        return Excel::download(new SalesOrderExport($request), 'Sales_Orders.xlsx');
    }

    public function declarationUpdate(Request $request)
    {
        DB::beginTransaction();
        try {
            if (! is_null($request->sales_master_id)) {
                $SalesMaster = SalesMaster::where('id', $request->sales_master_id)->first();
                $response = ['data' => route('declaration-dcr-pdf', $request->sales_master_id), 'status' => true, 'message' => ' Sales Quatation updated successfully.'];
            }
            $SalesMaster->purchase_order_number = $request->purchase_order_number;
            $SalesMaster->purchase_order_date = date('Y-m-d', strtotime($request->purchase_order_date));
            $SalesMaster->cell_manufacture_name = $request->cell_manufacture_name;
            $SalesMaster->cell_gst_invoice_no = $request->cell_gst_invoice_no;
            $result = $SalesMaster->save();
            DB::commit();
            if (! is_null($result)) {
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

    public function consumerPreviewPage()
    {
        $salesByNumberForStatus = SalesMaster::query()
            ->get()
            ->filter(function ($salesMaster) {
                return SalesMasterConsumerPreview::normalizeNumber($salesMaster->consumer_number) !== '';
            })
            ->mapWithKeys(function ($salesMaster) {
                return [SalesMasterConsumerPreview::normalizeNumber($salesMaster->consumer_number) => $salesMaster];
            });

        $ignored = ConsumerIgnore::query()
            ->orderByDesc('id')
            ->get(['consumer_name', 'consumer_number', 'ignore_date'])
            ->map(function ($record) use ($salesByNumberForStatus) {
                $ignoreDate = trim((string) ($record->ignore_date ?? ''));
                $normalizedNumber = SalesMasterConsumerPreview::normalizeNumber($record->consumer_number ?? '');
                $salesMaster = $salesByNumberForStatus->get($normalizedNumber);

                return [
                    'consumer_name' => $record->consumer_name ?? '',
                    'consumer_number' => $record->consumer_number ?? '',
                    'ignore_date' => $ignoreDate !== '' ? date('d-m-Y', strtotime($ignoreDate)) : '',
                    'sales_master_id' => $salesMaster ? $salesMaster->id : null,
                    'current_status' => $salesMaster ? toGetSalesMasterLastStatus($salesMaster->id, $salesMaster) : '',
                ];
            })
            ->all();

        return view('admin.sale.consumer-preview-page', [
            'preview' => session('sales_master_consumer_preview'),
            'missingInExcel' => session('sales_master_consumer_preview.missing_in_excel', []),
            'ignored' => $ignored,
        ]);
    }

    public function createFromUnmatchedPage(Request $request)
    {
        abort_unless(Gate::allows('sales-master-create'), 403);

        $preview = $request->session()->get('sales_master_consumer_preview');
        $row = (int) $request->query('row', 0);
        $consumerNumber = (string) $request->query('consumer_number', '');
        $normalizedNumber = SalesMasterConsumerPreview::normalizeNumber($consumerNumber);
        $record = null;

        foreach (($preview['unmatched'] ?? []) as $candidate) {
            $candidateRow = (int) ($candidate['row'] ?? 0);
            $candidateNumber = SalesMasterConsumerPreview::normalizeNumber($candidate['consumer_number'] ?? '');

            if ($row > 0 && $candidateRow === $row) {
                $record = $candidate;
                break;
            }

            if ($normalizedNumber !== '' && $candidateNumber === $normalizedNumber) {
                $record = $candidate;
            }
        }

        if (! $record && $normalizedNumber !== '') {
            $ignored = ConsumerIgnore::query()
                ->get(['consumer_number', 'consumer_name'])
                ->first(function ($item) use ($normalizedNumber) {
                    return SalesMasterConsumerPreview::normalizeNumber($item->consumer_number) === $normalizedNumber;
                });

            if ($ignored) {
                $record = [
                    'row' => 0,
                    'consumer_number' => $ignored->consumer_number,
                    'consumer_name' => $ignored->consumer_name ?? '',
                    'recommended' => [],
                    'fields' => [
                        'consumer_number' => $ignored->consumer_number,
                        'consumer_name' => $ignored->consumer_name ?? '',
                    ],
                ];
            }
        }

        if (! $record) {
            return redirect()->route('sales-master-consumer-preview')
                ->with('message', 'The selected record is no longer available.');
        }

        $options = $this->unmatchedCreateOptions();

        return view('admin.sale.create-from-unmatched', [
            'unmatchedRecord' => $record,
            'unmatchedCreateOptions' => $options,
            'unmatchedPrefill' => $this->buildUnmatchedPrefill($record, $options),
        ]);
    }

    private function unmatchedCreateOptions(): array
    {
        $company = CompanyProfile::where('user_id', Auth::id())->first();
        $agent = AgentSalesPerson::where('user_id', Auth::id())->first();
        $agentSalesPerson = collect();

        if ($company && $agent) {
            $agentWhere = '';

            if ($company->user_type === 'M') {
                $agentWhere .= 'company_profiles.user_id = ' . Auth::id() . ' OR company_profiles.manager_id = ' . $company->id;
            }

            if ($company->user_type === 'S') {
                $agentWhere .= 'company_profiles.id = ' . $company->id . ' OR company_profiles.id = ' . $company->manager_id;
            }

            $query = CompanyProfile::select('agent_sales_people.*')
                ->leftJoin('agent_sales_people', 'agent_sales_people.user_id', 'company_profiles.user_id');

            if ($agentWhere !== '') {
                $query->whereRaw($agentWhere);
            }

            $agentSalesPerson = $query->get();
        } elseif (! $company) {
            $agentSalesPerson = AgentSalesPerson::get();
        }

        return [
            'agentSalesPersons' => $agentSalesPerson,
            'penalCompanies' => PenalCompany::get(),
            'penalTypes' => PenalType::get(),
            'penalWatts' => PenalWatt::get(),
            'inverterCompanies' => InveterCompany::get(),
            'banks' => Bank::get(),
            'districts' => District::get(),
            'talukas' => Taluka::get(),
            'subDivisions' => SubDivision::get(),
            'boms' => BOM::get(),
        ];
    }

    private function buildUnmatchedPrefill(array $record, array $options): array
    {
        $fields = $record['fields'] ?? [];
        $recommended = $record['recommended'] ?? [];
        $value = function (string $key) use ($fields, $recommended): string {
            $fieldValue = trim((string) ($fields[$key] ?? ''));

            if ($fieldValue !== '') {
                return $fieldValue;
            }

            return trim((string) ($recommended[$key] ?? ''));
        };

        $contactNumber = $this->unmatchedValidMobile($value('mobile'));
        $penalCompany = $this->unmatchedFindModel($options['penalCompanies'] ?? collect(), $value('pv_module_make'));
        $penalWatt = $this->unmatchedFindModel($options['penalWatts'] ?? collect(), $value('module_capacity_wp'));
        $inverterCompany = $this->unmatchedFindModel($options['inverterCompanies'] ?? collect(), $value('inverter_make'));
        $bank = $this->unmatchedFindModel($options['banks'] ?? collect(), $value('loan_applied_at_bank'));
        $district = $this->unmatchedFindModel($options['districts'] ?? collect(), $value('district_name'));
        $subDivision = $this->unmatchedFindModel($options['subDivisions'] ?? collect(), $value('sub_division_name'), true);

        if (! $subDivision && ! empty($record['sub_division_id'])) {
            $subDivision = $this->unmatchedFindModelById($options['subDivisions'] ?? collect(), $record['sub_division_id']);
        }

        $prefill = [];
        $add = function (string $key, $val) use (&$prefill): void {
            if ($val !== null && $val !== '') {
                $prefill[$key] = $val;
            }
        };

        $add('consumer_name', $value('consumer_name'));
        $add('ragistration_portal', 'National');
        $add('ragistration_number', $value('application_number'));
        $add('ragistration_date', $this->unmatchedDate($value('application_submitted_date')));
        $add('address', $value('address'));
        $add('penal_company_id', $penalCompany->id ?? null);
        $add('penal_watt_id', $penalWatt->id ?? null);
        $add('penal_nos', $this->unmatchedNumber($value('module_quantity')));
        $add('pv_capacity_kw', $this->unmatchedNumber($value('proposed_pv_capacity')));
        $add('inveter_company_id', $inverterCompany->id ?? null);
        $add('inveter_capacity', $value('inverter_capacity'));
        $add('no_of_inveter', $this->unmatchedNumber($value('inverter_quantity')));
        $add('subsidy', $this->unmatchedMoney($value('subsidy_amount')));
        $add('consumer_type', $this->unmatchedConsumerType($value('connection_category')));
        $add('contact_number', $contactNumber);
        $add('email', $value('email'));
        $add('district_id', $district->id ?? null);
        $add('bank_name', $value('loan_applied_at_bank'));
        $add('bank_id', $bank->id ?? null);
        $add('contracted_load', $this->unmatchedNumber($value('sanction_load')));
        $add('sub_division_id', $subDivision->id ?? null);
        $add('division', $value('division_name') ?: ($subDivision->division_name ?? ''));
        $add('circle', $value('circle_name') ?: ($subDivision->circle_name ?? ''));
        $add('discom', $value('discom_name') ?: ($subDivision->discom ?? ''));
        $add('remark', $value('last_comment'));

        $loanTaken = mb_strtolower(trim($value('loan_taken')));

        if (str_starts_with($loanTaken, 'y')) {
            $prefill['file_type'] = 'L';
        } elseif (str_starts_with($loanTaken, 'n')) {
            $prefill['file_type'] = 'C';
        }

        return $prefill;
    }

    private function unmatchedFindModel($collection, string $value, bool $subDivision = false)
    {
        $target = $this->unmatchedNormalizeName($value, $subDivision);

        if ($target === '') {
            return null;
        }

        foreach ($collection as $model) {
            if ($this->unmatchedNormalizeName($model->name ?? '', $subDivision) === $target) {
                return $model;
            }
        }

        return null;
    }

    private function unmatchedFindModelById($collection, $id)
    {
        foreach ($collection as $model) {
            if ((int) ($model->id ?? 0) === (int) $id) {
                return $model;
            }
        }

        return null;
    }

    private function unmatchedNormalizeName($value, bool $subDivision = false): string
    {
        $value = mb_strtolower(trim((string) $value));
        $value = str_replace(["'", '’'], '', $value);
        $value = preg_replace('/[^a-z0-9]+/', ' ', $value);
        $value = trim((string) preg_replace('/\s+/', ' ', $value));

        if ($subDivision) {
            $value = (string) preg_replace('/\b(sub division|subdivision|s d|sdn|sd)\b/', ' ', $value);
            $value = trim((string) preg_replace('/\s+/', ' ', $value));
        }

        return $value;
    }

    private function unmatchedValidMobile($value): string
    {
        $digits = preg_replace('/\D+/', '', (string) $value);

        return preg_match('/^[6-9][0-9]{9}$/', (string) $digits) ? (string) $digits : '';
    }

    private function unmatchedNumber($value): string
    {
        $value = trim((string) $value);

        if ($value === '' || ! is_numeric($value)) {
            return $value;
        }

        return rtrim(rtrim(number_format((float) $value, 3, '.', ''), '0'), '.');
    }

    private function unmatchedDate($value): string
    {
        $value = trim((string) $value);

        if ($value === '' || $value === '0000-00-00' || $value === '1970-01-01') {
            return '';
        }

        if (is_numeric($value)) {
            return date('Y-m-d', (int) (((float) $value - 25569) * 86400));
        }

        $timestamp = strtotime($value);

        return $timestamp === false ? '' : date('Y-m-d', $timestamp);
    }

    private function unmatchedMoney($value): string
    {
        $value = trim((string) $value);

        if ($value === '' || ! is_numeric(str_replace(',', '', $value))) {
            return $value;
        }

        return number_format((float) str_replace(',', '', $value), 2, '.', '');
    }

    private function unmatchedConsumerType($value): string
    {
        $value = mb_strtolower(trim((string) $value));

        if ($value === '') {
            return '';
        }

        if (strpos($value, 'industrial') !== false) {
            return 'Industrial';
        }

        if (strpos($value, 'commercial') !== false) {
            return 'Commercial';
        }

        if (strpos($value, 'resident') !== false || strpos($value, 'domestic') !== false || strpos($value, 'rwa') !== false) {
            return 'Resident';
        }

        return '';
    }

    public function consumerPreviewUpload(Request $request)
    {
        @set_time_limit(0);

        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ], [
            'excel_file.required' => 'Please select an Excel file.',
            'excel_file.file' => 'Please select a valid Excel file.',
            'excel_file.mimes' => 'Only xlsx, xls and csv files are allowed.',
            'excel_file.max' => 'Excel file may not be larger than 10 MB.',
        ]);

        try {
            $filePath = $request->file('excel_file')->store('sales-master-updates');

            $import = new SalesMasterConsumerPreview();
            Excel::import($import, Storage::path($filePath));

            if ($import->error() !== null) {
                Storage::delete($filePath);

                return redirect()->back()->withErrors(['excel_file' => $import->error()])->withInput();
            }

            $rows = $import->rows();
            $numbers = array_values(array_unique(array_filter(array_column($rows, 'number'), function ($number) {
                return $number !== '';
            })));

            $salesByNumber = SalesMaster::query()
                ->get([
                    'id',
                    'consumer_name',
                    'consumer_number',
                    'district_id',
                    'sub_division_id',
                    'discom',
                    'circle',
                    'division',
                    'application_pending',
                    'pending_approvel',
                    'feasibility_approved',
                    'meter_charge_paid',
                    'apply_for_loan',
                    'loan_sension',
                    'disbursement',
                    'payment_receveid',
                    'dispach_pending_list',
                    'installation_pending',
                    'installation_done',
                    'meter_application_done',
                    'meter_installation',
                    'ragistration_portal',
                    'subsidy_giveup',
                    'project_completion',
                    'subsidy_claimed',
                    'subsidy_receveid',
                    'hold_query',
                    'file_cancel_order',
                ])
                ->filter(function ($salesMaster) {
                    return SalesMasterConsumerPreview::normalizeNumber($salesMaster->consumer_number) !== '';
                })
                ->keyBy(function ($salesMaster) {
                    return SalesMasterConsumerPreview::normalizeNumber($salesMaster->consumer_number);
                });

            $excelNumberSet = array_fill_keys($numbers, true);
            $missingInExcel = $salesByNumber
                ->filter(function ($salesMaster, $number) use ($excelNumberSet) {
                    return ! isset($excelNumberSet[$number])
                        && (int) ($salesMaster->hold_query ?? 0) !== 1
                        && (int) ($salesMaster->file_cancel_order ?? 0) !== 1;
                })
                ->map(function ($salesMaster) {
                    return [
                        'id' => (int) $salesMaster->id,
                        'consumer_name' => $salesMaster->consumer_name ?? '',
                        'consumer_number' => $salesMaster->consumer_number ?? '',
                        'current_status' => toGetSalesMasterLastStatus($salesMaster->id, $salesMaster),
                    ];
                })
                ->values()
                ->all();

            $subDivisionIds = $salesByNumber->pluck('sub_division_id')->filter()->unique()->values()->all();
            $subDivisionNames = count($subDivisionIds) > 0
                ? SubDivision::whereIn('id', $subDivisionIds)->pluck('name', 'id')
                : collect();
            $subDivisionIdsByName = SubDivision::query()
                ->pluck('id', 'name')
                ->mapWithKeys(function ($id, $name) {
                    return [mb_strtolower(trim((string) $name)) => (int) $id];
                });

            $ignoredNumbers = ConsumerIgnore::query()
                ->pluck('consumer_number')
                ->map(function ($number) {
                    return SalesMasterConsumerPreview::normalizeNumber($number);
                })
                ->filter()
                ->all();
            $ignoredNumberSet = array_fill_keys($ignoredNumbers, true);

            $matched = [];
            $unmatched = [];

            foreach ($rows as $index => $row) {
                $preview = [
                    'row' => $index + 2,
                    'consumer_name' => $row['consumer_name'],
                    'consumer_number' => $row['consumer_number'],
                    'fields' => $row['fields'] ?? [],
                ];

                $key = $row['number'];

                if ($key !== '' && $salesByNumber->has($key)) {
                    $salesMaster = $salesByNumber->get($key);
                    $excelRecommended = $row['recommended'] ?? [];
                    $dbRecommended = [
                        'consumer_name' => $salesMaster->consumer_name,
                        'discom_name' => $salesMaster->discom,
                        'circle_name' => $salesMaster->circle,
                        'division_name' => $salesMaster->division,
                        'sub_division_name' => $subDivisionNames->get($salesMaster->sub_division_id),
                    ];

                    $preview['sales_master_id'] = $salesMaster->id;
                    $preview['current_status'] = toGetSalesMasterLastStatus($salesMaster->id, $salesMaster);
                    $preview['db_consumer_name'] = $salesMaster->consumer_name;
                    $preview['db_consumer_number'] = $salesMaster->consumer_number;
                    $preview['excel_recommended'] = $excelRecommended;
                    $preview['db_recommended'] = $dbRecommended;
                    $preview['changed_fields'] = $this->changedRecommendedFields($excelRecommended, $dbRecommended);
                    $matched[] = $preview;
                } else {
                    if ($key !== '' && isset($ignoredNumberSet[$key])) {
                        continue;
                    }

                    $recommended = $row['recommended'] ?? [];
                    $subDivisionName = mb_strtolower(trim((string) ($recommended['sub_division_name'] ?? '')));
                    $preview['recommended'] = $recommended;
                    $preview['sub_division_id'] = $subDivisionName !== ''
                        ? $subDivisionIdsByName->get($subDivisionName)
                        : null;
                    $unmatched[] = $preview;
                }
            }

            $recommendedMatched = array_values(array_filter($matched, function ($preview) {
                return ! empty($preview['changed_fields']);
            }));

            $defaultImportMap = [];

            foreach ($matched as $preview) {
                $defaultImportMap[(int) $preview['row']] = (int) $preview['sales_master_id'];
            }

            $portalDataSyncService = app(PortalDataSyncService::class);
            $portalDataSyncedCount = $portalDataSyncService->sync($rows, $salesByNumber);
            $portalSyncLabel = $portalDataSyncService->wasSkipped()
                ? 'Portal data unchanged'
                : ($portalDataSyncService->wasStatusOnly() ? 'Portal status synced' : 'Portal data synced');

            $oldFilePath = $request->session()->get('sales_master_consumer_preview.file_path');

            $request->session()->put('sales_master_consumer_preview', [
                'matched' => $recommendedMatched,
                'unmatched' => $unmatched,
                'missing_in_excel' => $missingInExcel,
                'portal_data_synced_count' => $portalDataSyncedCount,
                'file_path' => $filePath,
                'file_name' => $request->file('excel_file')->getClientOriginalName(),
                'generated_at' => now()->format('d-m-Y H:i:s'),
                'default_update' => [
                    'status' => empty($defaultImportMap) ? 'skipped' : 'pending',
                    'row_map' => $defaultImportMap,
                    'updated_count' => 0,
                    'errors' => [],
                ],
            ]);

            if ($oldFilePath && $oldFilePath !== $filePath) {
                Storage::delete($oldFilePath);
            }

            $portalSyncMessage = $portalDataSyncService->wasSkipped()
                ? $portalSyncLabel . ' (no matching portal fields or records found).'
                : $portalSyncLabel . ' for ' . $portalDataSyncedCount . ' records.';

            return redirect()->route('sales-master-consumer-preview')
                ->with('message', 'Excel preview generated. ' . $portalSyncMessage);
        } catch (\Throwable $e) {
            if (isset($filePath)) {
                Storage::delete($filePath);
            }

            return redirect()->back()
                ->withErrors(['excel_file' => 'Unable to read the Excel file. Please check the file format.'])
                ->withInput();
        }
    }

    public function ignoreUnmatched(Request $request)
    {
        $request->validate([
            'consumer_numbers' => 'required|array|min:1',
            'consumer_numbers.*' => 'required|string|max:255',
            'consumer_names' => 'nullable|array',
            'consumer_names.*' => 'nullable|string|max:255',
        ]);

        $now = now();
        $numbers = collect($request->input('consumer_numbers'))
            ->map(function ($number) {
                return SalesMasterConsumerPreview::normalizeNumber($number);
            })
            ->filter()
            ->unique()
            ->values();
        $names = collect($request->input('consumer_names', []))->values();
        $preview = $request->session()->get('sales_master_consumer_preview');
        $namesByNumber = [];

        if (is_array($preview)) {
            foreach (['matched', 'unmatched'] as $group) {
                foreach ($preview[$group] ?? [] as $row) {
                    $previewNumber = SalesMasterConsumerPreview::normalizeNumber($row['consumer_number'] ?? '');
                    $previewName = trim((string) ($row['consumer_name'] ?? ''));

                    if ($previewNumber !== '' && $previewName !== '' && ! isset($namesByNumber[$previewNumber])) {
                        $namesByNumber[$previewNumber] = $previewName;
                    }
                }
            }
        }

        if ($numbers->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No valid consumer numbers selected.',
            ], 422);
        }

        $records = $numbers->map(function ($number, $index) use ($now, $names, $namesByNumber) {
            $name = trim((string) ($names->get($index) ?? ''));

            if ($name === '') {
                $name = $namesByNumber[$number] ?? '';
            }

            return [
                'consumer_number' => $number,
                'consumer_name' => $name !== '' ? $name : null,
                'ignore_date' => $now->format('Y-m-d'),
                'timestep' => $now->format('Y-m-d H:i:s'),
                'created_at' => $now->format('Y-m-d H:i:s'),
                'updated_at' => $now->format('Y-m-d H:i:s'),
            ];
        })->all();

        ConsumerIgnore::query()->upsert(
            $records,
            ['consumer_number'],
            ['consumer_name', 'ignore_date', 'timestep', 'updated_at']
        );

        if (is_array($preview) && array_key_exists('unmatched', $preview)) {
            $preview['unmatched'] = array_values(array_filter($preview['unmatched'], function ($row) use ($numbers) {
                $number = SalesMasterConsumerPreview::normalizeNumber($row['consumer_number'] ?? '');

                return $number === '' || ! $numbers->contains($number);
            }));

            $request->session()->put('sales_master_consumer_preview', $preview);
        }

        $salesByNumberForStatus = SalesMaster::query()
            ->get()
            ->filter(function ($salesMaster) {
                return SalesMasterConsumerPreview::normalizeNumber($salesMaster->consumer_number) !== '';
            })
            ->mapWithKeys(function ($salesMaster) {
                return [SalesMasterConsumerPreview::normalizeNumber($salesMaster->consumer_number) => $salesMaster];
            });

        return response()->json([
            'success' => true,
            'ignored_count' => $numbers->count(),
            'ignored' => collect($records)->map(function ($record) use ($salesByNumberForStatus) {
                $ignoreDate = trim((string) ($record['ignore_date'] ?? ''));
                $salesMaster = $salesByNumberForStatus->get($record['consumer_number']);

                return [
                    'consumer_name' => $record['consumer_name'] ?? '',
                    'consumer_number' => $record['consumer_number'] ?? '',
                    'ignore_date' => $ignoreDate !== '' ? date('d-m-Y', strtotime($ignoreDate)) : '',
                    'current_status' => $salesMaster ? toGetSalesMasterLastStatus($salesMaster->id, $salesMaster) : '',
                ];
            })->all(),
            'message' => $numbers->count() . ' record(s) added to ignore list.',
        ]);
    }

    public function createFromUnmatched(Request $request, UnmatchedRecordCreatorService $creator)
    {
        abort_unless(Gate::allows('sales-master-create'), 403);

        $target = 'both';

        $rules = [
            'preview_row' => 'nullable|integer',
            'consumer_number' => 'required|string|max:255',
            'consumer_name' => 'required|string|max:255',
            'ragistration_portal' => 'nullable|string|max:255',
            'ragistration_number' => 'nullable|string|max:255',
            'ragistration_date' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'agent_sales_person_id' => 'required',
            'penal_company_id' => 'required',
            'penal_type_id' => 'required',
            'penal_watt_id' => 'required',
            'penal_nos' => 'required|numeric',
            'pv_capacity_kw' => 'required|numeric',
            'inveter_company_id' => 'required',
            'inveter_capacity' => 'required',
            'no_of_inveter' => 'required|numeric',
            'structure' => 'required|string',
            'common_meter' => 'nullable|in:Yes,No',
            'total_system_cost' => 'required|numeric',
            'meter_charges_extra' => 'nullable|in:Yes,No',
            'meter_charges' => 'required|numeric',
            'registration_fee' => 'nullable|numeric',
            'other_charge_name' => 'nullable|string|max:255',
            'other_charge_amount' => 'nullable|numeric',
            'subsidy' => 'nullable|numeric',
            'state_subsidy' => 'nullable|numeric',
            'state_subsidy_select' => 'required|in:general,sc_st,give_up_subsidy',
            'bank_id' => 'nullable',
            'total_amount' => 'required|numeric',
            'consumer_type' => 'required',
            'district_id' => 'required',
            'taluka_id' => 'required',
            'pin_code' => 'required|string|size:6|regex:/^[0-9]{6}$/',
            'contact_number' => 'required|string|max:10|regex:/^[6-9][0-9]{9}$/',
            'email' => 'nullable|email|max:255',
            'gst_number' => 'nullable|string|max:15',
            'aadhaar_number' => 'nullable|string|max:12',
            'bank_name' => 'nullable|string|max:255',
            'bank_account' => 'nullable|string|max:50',
            'ifsc_code' => 'nullable|string|max:20',
            'contracted_load' => 'nullable|string|max:20',
            'phase' => 'nullable|in:Single phase,Three phase',
            'sub_division_id' => 'nullable',
            'division' => 'nullable|string|max:255',
            'circle' => 'nullable|string|max:255',
            'discom' => 'nullable|string|max:255',
            'bom_id' => 'nullable',
            'file_type' => 'nullable|in:C,L',
            'remark' => 'nullable|string|max:1000',
            'res_itemDescription' => 'nullable|array',
            'res_itemDescription.*' => 'nullable|string|max:2000',
            'res_qty' => 'nullable|array',
            'res_qty.*' => 'nullable|string|max:255',
            'res_size' => 'nullable|array',
            'res_size.*' => 'nullable|string|max:255',
            'res_make' => 'nullable|array',
            'res_make.*' => 'nullable|string|max:255',
            'res_type' => 'nullable|array',
            'res_type.*' => 'nullable|string|max:255',
            'document' => 'nullable|array|max:10',
            'document.*.name' => 'nullable|string|max:255',
            'document.*.image' => 'nullable|file|max:10240',
            'document_name' => 'nullable|string|max:255',
            'document_file' => 'nullable|file|max:10240',
        ];

        $validator = Validator::make($request->all(), $rules, [
            'consumer_number.required' => 'Enter Consumer Number.',
            'consumer_name.required' => 'Enter Consumer Name.',
            'agent_sales_person_id.required' => 'Select Agent/Sales Person.',
            'penal_company_id.required' => 'Select Panel Company.',
            'penal_type_id.required' => 'Select Panel Type.',
            'penal_watt_id.required' => 'Select Panel Watt.',
            'penal_nos.required' => 'Enter Panel Nos.',
            'pv_capacity_kw.required' => 'Enter PV Capacity.',
            'inveter_company_id.required' => 'Select Inverter Company.',
            'inveter_capacity.required' => 'Enter Inverter Capacity.',
            'no_of_inveter.required' => 'Enter No. Of Inverter.',
            'structure.required' => 'Select Structure.',
            'total_system_cost.required' => 'Enter Total System Cost.',
            'meter_charges.required' => 'Enter Meter Charges.',
            'state_subsidy_select.required' => 'Select State Subsidy Type.',
            'total_amount.required' => 'Enter Total Amount.',
            'consumer_type.required' => 'Select Consumer Type.',
            'district_id.required' => 'Select District.',
            'taluka_id.required' => 'Select Taluka.',
            'pin_code.required' => 'Enter Pincode.',
            'pin_code.size' => 'Enter Valid Pincode.',
            'pin_code.regex' => 'Enter Valid Pincode.',
            'contact_number.required' => 'Enter Contact Number.',
            'contact_number.regex' => 'Enter Valid Contact Number.',
            'document.max' => 'You can upload up to 10 documents.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Please input proper data.',
                'errors' => $validator->errors()->all(),
            ], 422);
        }

        if ($creator->consumerNumberExists((string) $request->input('consumer_number'))) {
            return response()->json([
                'success' => false,
                'message' => 'The Consumer Number has already been taken.',
                'errors' => ['The Consumer Number has already been taken.'],
            ], 422);
        }

        try {
            $result = $creator->create($request->all(), $target);

            $normalizedNumber = SalesMasterConsumerPreview::normalizeNumber($request->input('consumer_number'));

            if ($normalizedNumber !== '') {
                ConsumerIgnore::query()
                    ->get(['id', 'consumer_number'])
                    ->filter(function ($item) use ($normalizedNumber) {
                        return SalesMasterConsumerPreview::normalizeNumber($item->consumer_number) === $normalizedNumber;
                    })
                    ->each(function ($item) {
                        $item->delete();
                    });
            }

            $preview = $request->session()->get('sales_master_consumer_preview');

            if (is_array($preview) && array_key_exists('unmatched', $preview)) {
                $previewRow = (int) $request->input('preview_row');

                $preview['unmatched'] = array_values(array_filter($preview['unmatched'], function ($row) use ($normalizedNumber, $previewRow) {
                    $rowNumber = SalesMasterConsumerPreview::normalizeNumber($row['consumer_number'] ?? '');

                    if ($normalizedNumber !== '' && $rowNumber === $normalizedNumber) {
                        return false;
                    }

                    return $previewRow <= 0 || (int) ($row['row'] ?? 0) !== $previewRow;
                }));

                $request->session()->put('sales_master_consumer_preview', $preview);
            }

            $messages = [
                'quotation' => 'Quotation created successfully.',
                'sales_order' => 'Sales order created successfully.',
                'both' => 'Quotation and sales order created successfully.',
            ];

            return response()->json(array_merge($result, [
                'success' => true,
                'message' => $messages[$target],
            ]));
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Unable to create the selected record(s). Please try again.',
                'errors' => ['Unable to create the selected record(s). Please try again.'],
            ], 500);
        }
    }

    public function applyPreviewDefaultUpdates(Request $request)
    {
        @set_time_limit(0);

        $preview = $request->session()->get('sales_master_consumer_preview');

        if (! is_array($preview) || empty($preview['file_path']) || ! Storage::exists($preview['file_path'])) {
            return response()->json([
                'success' => false,
                'message' => 'Preview file not found. Please upload the Excel file again.',
            ], 422);
        }

        $defaultUpdate = $preview['default_update'] ?? [];
        $status = $defaultUpdate['status'] ?? 'skipped';

        if ($status === 'done') {
            return response()->json([
                'success' => true,
                'already_done' => true,
                'message' => $defaultUpdate['message'] ?? 'Default fields already updated.',
                'updated_count' => $defaultUpdate['updated_count'] ?? 0,
                'errors' => $defaultUpdate['errors'] ?? [],
            ]);
        }

        $rowMap = $defaultUpdate['row_map'] ?? [];

        if (empty($rowMap)) {
            $preview['default_update'] = [
                'status' => 'skipped',
                'row_map' => [],
                'updated_count' => 0,
                'errors' => [],
                'message' => 'No default fields to update.',
            ];
            $request->session()->put('sales_master_consumer_preview', $preview);

            return response()->json([
                'success' => true,
                'skipped' => true,
                'message' => 'No default fields to update.',
                'updated_count' => 0,
                'errors' => [],
            ]);
        }

        try {
            $path = Storage::path($preview['file_path']);
            $headingRows = Excel::toCollection(new HeadingRowImport(), $path);
            $headings = $headingRows->isNotEmpty() && $headingRows->first()->isNotEmpty()
                ? $headingRows->first()->first()->all()
                : [];

            if (! empty(SalesMasterUpdateImport::missingHeaders($headings))) {
                $preview['default_update'] = [
                    'status' => 'skipped',
                    'row_map' => [],
                    'updated_count' => 0,
                    'errors' => [],
                    'message' => 'Default columns not found in Excel. Default fields skipped.',
                ];
                $request->session()->put('sales_master_consumer_preview', $preview);

                return response()->json([
                    'success' => true,
                    'skipped' => true,
                    'message' => 'Default columns not found in Excel. Default fields skipped.',
                    'updated_count' => 0,
                    'errors' => [],
                ]);
            }

            $import = new SalesMasterUpdateImport(array_keys($rowMap), [], $rowMap);

            DB::transaction(function () use ($import, $path) {
                Excel::import($import, $path);
            });

            $updatedCount = $import->updatedCount();
            $errors = $import->errors();
            $message = $updatedCount > 0
                ? 'Default fields updated: ' . $updatedCount . ' record(s).'
                : 'Default fields checked; no changes needed.';

            if (! empty($errors)) {
                $message .= ' Some records were skipped.';
            }

            $preview['default_update'] = [
                'status' => 'done',
                'row_map' => [],
                'updated_count' => $updatedCount,
                'errors' => $errors,
                'message' => $message,
            ];
            $request->session()->put('sales_master_consumer_preview', $preview);

            return response()->json([
                'success' => true,
                'message' => $message,
                'updated_count' => $updatedCount,
                'errors' => $errors,
            ]);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Default fields could not be updated. Please retry.',
            ], 500);
        }
    }

    private function changedRecommendedFields(array $excel, array $db): array
    {
        $changed = [];

        foreach (SalesMasterUpdateImport::RECOMMENDED_FIELDS as $field => $label) {
            $excelValue = $this->normalizeRecommendedValue($excel[$field] ?? null);

            if ($excelValue === '') {
                continue;
            }

            $dbValue = $this->normalizeRecommendedValue($db[$field] ?? null);

            if (strcasecmp($excelValue, $dbValue) !== 0) {
                $changed[] = $field;
            }
        }

        return $changed;
    }

    private function normalizeRecommendedValue($value): string
    {
        return preg_replace('/\s+/', ' ', trim((string) $value));
    }

    public function updateRecords(Request $request)
    {
        @set_time_limit(0);

        $salesMasterIds = $request->input('sales_master_ids', []);

        if (is_string($salesMasterIds)) {
            $decodedIds = json_decode($salesMasterIds, true);

            $salesMasterIds = is_array($decodedIds)
                ? $decodedIds
                : array_values(array_filter(array_map('trim', explode(',', $salesMasterIds)), function ($id) {
                    return $id !== '';
                }));
        }

        $request->merge(['sales_master_ids' => is_array($salesMasterIds) ? $salesMasterIds : []]);

        $validator = Validator::make($request->all(), [
            'sales_master_ids' => 'required|array|min:1',
            'sales_master_ids.*' => 'integer|min:1',
            'update_mode' => 'required|in:default,recommend',
            'recommended_fields' => 'required_if:update_mode,recommend|array',
            'recommended_fields.*' => Rule::in(array_keys(SalesMasterUpdateImport::RECOMMENDED_FIELDS)),
        ], [
            'sales_master_ids.required' => 'Please select at least one matched record.',
            'sales_master_ids.min' => 'Please select at least one matched record.',
            'update_mode.required' => 'Please select an update mode.',
            'recommended_fields.required_if' => 'Please select at least one recommend field.',
            'recommended_fields.min' => 'Please select at least one recommend field.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'updated_count' => 0,
                'skipped_count' => 0,
                'not_found' => [],
                'errors' => $validator->errors()->all(),
            ], 422);
        }

        $mode = $request->input('update_mode', 'default');
        $recommendedFields = $mode === 'recommend'
            ? array_values($request->input('recommended_fields', []))
            : [];

        if ($mode === 'recommend' && empty($recommendedFields)) {
            return response()->json([
                'success' => false,
                'updated_count' => 0,
                'skipped_count' => 0,
                'not_found' => [],
                'errors' => ['Please select at least one recommend field.'],
            ], 422);
        }

        $preview = $request->session()->get('sales_master_consumer_preview');

        if (! $preview || empty($preview['file_path']) || ! Storage::exists($preview['file_path'])) {
            return response()->json([
                'success' => false,
                'updated_count' => 0,
                'skipped_count' => 0,
                'not_found' => [],
                'errors' => ['Please generate an Excel preview before updating records.'],
            ], 422);
        }

        $selectedIds = array_map('intval', $request->input('sales_master_ids', []));
        $selectedIdSet = array_fill_keys($selectedIds, true);
        $salesMasterIdsByRow = collect($preview['matched'] ?? [])
            ->filter(function ($row) use ($selectedIdSet) {
                return isset($selectedIdSet[(int) ($row['sales_master_id'] ?? 0)]);
            })
            ->mapWithKeys(function ($row) {
                return [(int) $row['row'] => (int) $row['sales_master_id']];
            })
            ->filter(function ($salesMasterId, $rowNumber) {
                return $rowNumber > 0 && $salesMasterId > 0;
            })
            ->all();
        $rowNumbers = array_keys($salesMasterIdsByRow);

        if (empty($rowNumbers)) {
            return response()->json([
                'success' => false,
                'updated_count' => 0,
                'skipped_count' => 0,
                'not_found' => [],
                'errors' => ['Selected records are no longer available in the preview. Please upload the file again.'],
            ], 422);
        }

        try {
            $path = Storage::path($preview['file_path']);
            $headingRows = Excel::toCollection(new HeadingRowImport(), $path);
            $headings = $headingRows->isNotEmpty() && $headingRows->first()->isNotEmpty()
                ? $headingRows->first()->first()->all()
                : [];
            $missingHeaders = SalesMasterUpdateImport::missingHeaders($headings, $recommendedFields);

            if (! empty($missingHeaders)) {
                return response()->json([
                    'success' => false,
                    'updated_count' => 0,
                    'skipped_count' => 0,
                    'not_found' => [],
                    'errors' => ['Missing required headers: ' . implode(', ', $missingHeaders)],
                ], 422);
            }

            $import = new SalesMasterUpdateImport($rowNumbers, $recommendedFields, $salesMasterIdsByRow);
            DB::transaction(function () use ($import, $path) {
                Excel::import($import, $path);
            });

            Storage::delete($preview['file_path']);
            $request->session()->forget('sales_master_consumer_preview');

            return response()->json([
                'success' => true,
                'updated_count' => $import->updatedCount(),
                'skipped_count' => $import->skippedCount(),
                'not_found' => $import->notFound(),
                'errors' => $import->errors(),
            ]);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'updated_count' => 0,
                'skipped_count' => 0,
                'not_found' => [],
                'errors' => ['Unable to complete the update. Please generate the preview again.'],
            ], 500);
        }
    }

    public function import(Request $request)
    {
        try {
            $file = $request->file('excel_file');
            Excel::import(new Salesorder($request), $file);
            // Excel::import(new PaymentImport(), $file);
            $response = ['data' => route('lead.index'), 'status' => true, 'message' => 'Added successfully.'];

            return response()->json($response);
        } catch (\Exception $e) {
            $response = ['status' => false, 'server_error' => 'Something went wrong. Please try again.'];

            return response()->json($response);
        }
    }

    public function paymentList($id)
    {
        $payment = PaymetCollection::where('sales_master_id', $id)->get();
        $data['html'] = view('admin.sale.payment', compact('payment'))->render();

        return response()->json($data);
    }

    public function getComsumerUsingMobile(Request $request)
    {

        $salesMaster = SalesMaster::select('consumer_name','consumer_number','invoice_date')->where('contact_number', $request->contact_number)->get();
        if($salesMaster->count() > 0){
            $response = ['status' => true, 'message' => $salesMaster];
        }else {
            $response = ['status' => false, 'message' => []];
        }
        return response()->json($response);
    }
}
