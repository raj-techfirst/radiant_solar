<?php

namespace App\Http\Controllers;

use App\Exports\SalesOrderExport;
use App\Imports\PaymentImport;
use App\Imports\Salesorder;
use App\Models\AgentSalesPerson;
use App\Models\Bank;
use App\Models\CompanyProfile;
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
use App\Models\PenalWatt;
use App\Models\SalesMaster;
use App\Models\SalesQuatation;
use App\Models\SubDivision;
use App\Models\Taluka;
use App\Models\User;
use App\Models\UserCommission;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class SalesMasterController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:sales-master-list|sales-master-create|sales-master-edit|sales-master-delete', ['only' => ['index', 'store']]);
        $this->middleware('permission:sales-master-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:sales-master-edit', ['only' => ['edit', 'store']]);
        $this->middleware('permission:sales-master-delete', ['only' => ['destroy']]);
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
            return DataTables::of(SalesMaster::with('district', 'taluka', 'village', 'subDivision', 'agentsalesperson', 'panel', 'panelwatt', 'inveter', 'salesquatation')->orderBy('updated_at', 'DESC'))
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
                    if (request()->input('from_date') != '' && request()->input('to_date') == '') {
                        $query->where('master_create_date', '>=', date('Y-m-d 00:00:00', strtotime(request()->input('from_date'))));
                        $query->where('master_create_date', '<=', date('Y-m-d 23:59:59'));
                    }
                    if (request()->input('from_date') != '' && request()->input('to_date') != '') {
                        $query->where('master_create_date', '>=', date('Y-m-d 00:00:00', strtotime(request()->input('from_date'))));
                        $query->where('master_create_date', '<=', date('Y-m-d 23:59:59', strtotime(request()->input('to_date'))));
                    }
                    if (request()->input('from_date') == '' && request()->input('to_date') != '') {
                        $query->where('master_create_date', '<=', date('Y-m-d 23:59:59', strtotime(request()->input('to_date'))));
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

                    $html .= '<a class="dropdown-item status application-view" href="javascript:void(0);" data-id="'.$row->id.'" data-date="'.$mid.'" data-img="'.$row->proforma_15.'" data-value="meter_installation">Meter Installation</a>
                        <a class="dropdown-item status application-view" href="javascript:void(0);" data-id="'.$row->id.'" data-date="'.$srd.'" data-date2="'.$sdvd.'" data-remark="'.$row->subsidy_disbursal_remark.'" data-value="subsidy_claimed">Subsidy Request</a>
                        <a class="dropdown-item status application-view" href="javascript:void(0);" data-id="'.$row->id.'" data-date="'.$sdd.'" data-value="subsidy_receveid">Subsidy Disbursal</a>';

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

            return view('admin.sale.view', compact('agentSalesPerson', 'boms', 'banks'));
        }
    }

    public function applicatonSave(Request $request)
    {
        try {
            $a = $request->status;
            if (toChangeStatus($request->status, $request->id)) {
                $salesMaster = SalesMaster::where('id', $request->id)->first();
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
            $salesMaster->installation_asian_person = $request->installation_asian_person;

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

            if (! empty($request->installation_asian_person) && $request->installation_asian_person != '-- Select --' && $salesMaster->dispach_pending_list == '1') {
                $salesMaster->installation_pending = '1';
                //  installation amount set
                $installerUserId = (int) $request->installation_asian_person; // users.id
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

            return view('admin.sale.model', compact('salesMaster', 'payment', 'percentage', 'salesloan'));
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

    public function statusView($id, Request $request)
    {
        $type = (isset($request->type) && $request->type != '') ? $request->type : '';
        $user = User::where('id', '!=', '1')->get();
        $salesMaster = SalesMaster::where('id', $id)->first();
        if (! is_null($salesMaster)) {
            $salesloan = Loandata::where('sales_master_id', $salesMaster->id)->first();
            $data['html'] = view('admin.sale.status_model', compact('salesMaster', 'user', 'type', 'salesloan'))->render();

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
