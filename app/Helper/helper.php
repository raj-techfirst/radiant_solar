<?php

use App\Models\AgentSalesPerson;
use App\Models\CommissionPayment;
use App\Models\CompanyProfile;
use App\Models\Discom;
use App\Models\District;
use App\Models\erp\DeliveryChallanMeta;
use App\Models\erp\PurchaseDirectMeta;
use App\Models\Installation;
use App\Models\InstallationInvater;
use App\Models\InveterCompany;
use App\Models\LeadMaster;
use App\Models\PaymetCollection;
use App\Models\PenalCompany;
use App\Models\PenalType;
use App\Models\PenalWatt;
use App\Models\SalesMaster;
use App\Models\SalesQuatation;
use App\Models\SubDivision;
use App\Models\Taluka;
use App\Models\User;
use App\Models\UserCommission;
use App\Models\Year;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;

function toChangeStatus($status, $id)
{
    $salesMaster = SalesMaster::where('id', $id)->first();
    /* Pending Approvel */
    if ($status == 'pending_approvel') {
        if ($salesMaster->ragistration_portal != '' && $salesMaster->ragistration_number != '') {
            return true;
        }
    }
    /* / Pending Approvel */

      /* Feasibility Approved */
    if ($status == 'feasibility_approved') {
        if ($salesMaster->pending_approvel == 1 && $salesMaster->feasibility_discom_sr_number != '' && $salesMaster->feasibility_amount != '') {
            return true;
        }
    }
    /* /Feasibility Approved */

    /* Meter Charge Paid */
    if ($status == 'meter_charge_paid') {
        if ($salesMaster->feasibility_approved == 1) {
            return true;
        }
    }
    /* /Meter Charge Paid */

    /* For Loan */

    /* Apply for loan */
    if ($status == 'apply_for_loan') {
        return true;
    }
    /* / Apply for loan */

    /* Login */
    // if ($status == 'login') {
    //     return true;
    // }
    /* / Login */

    /* Loan Sension */
    if ($status == 'loan_sension') {
        return true;
    }
    /* / Loan Sension */

    /* Disbursement */
    if ($status == 'disbursement') {
        return true;
    }
    /* / Disbursement */

    /* / For Loan */

    /* Document Verified */
    if ($status == 'document_verified') {
        if ($salesMaster->pending_approvel == 1) {
            return true;
        }
    }
    /* /Document Verified */


    /* Dispach Pending List */
    if ($status == 'dispach_pending_list') {
        if ($salesMaster->feasibility_approved == 1) {
            return true;
        }
    }
    /* /Dispach Pending List */
    /* Installation Pending */
    if ($status == 'installation_pending') {
        if ($salesMaster->dispach_pending_list == 1) {
            return true;
        }
    }
    /* /Installation Pending */
    /* Installation Done */
    if ($status == 'installation_done') {
        if ($salesMaster->installation_pending == 1) {
            return true;
        }
    }
    /* /Installation Done */
    /* Meter Application Done */
    if ($status == 'meter_application_done') {
        if ($salesMaster->installation_done == 1 && $salesMaster->meter_asian_person != '') {
            return true;
        }
    }
    /* /Meter Application Done */
    /* Meter Installation */
    if ($status == 'meter_installation') {
        if ($salesMaster->meter_application_done == 1) {
            //remove documnet
            if ($salesMaster->meter_application_oc != '') {
                $path = 'upload/document/'.$salesMaster->meter_application_oc;
                if (File::exists($path)) {
                    unlink($path);
                }
                $salesMaster->meter_application_oc = null;
                $salesMaster->save();
            }

            return true;
        }
    }
    /* /Meter Installation */
    /* Subsidy Claimed */
    if ($status == 'subsidy_claimed') {
        if ($salesMaster->meter_installation == 1) {
            return true;
        }
    }
    /* /Subsidy Claimed */
    /* Subsidy Receveid */
    if ($status == 'subsidy_receveid') {
        if ($salesMaster->subsidy_claimed == 1) {
            return true;
        }
    }
    /* /Subsidy Receveid */
    /* Hold / Query Order */
    if ($status == 'hold_query') {
        return true;
    }
    /* /Hold / Query Order */
    /* File Cancel Order */
    if ($status == 'file_cancel_order') {
        return true;
    }

    /* /File Cancel Order */
    return false;
}

function shorten_number($number)
{
    $suffix = '';
    if ($number >= 1000000000) {
        $number = number_format($number / 1000000000, 1);
        $suffix = 'B';
    } elseif ($number >= 1000000) {
        $number = number_format($number / 1000000, 1);
        $suffix = 'M';
    } elseif ($number >= 1000) {
        $number = number_format($number / 1000, 1);
        $suffix = 'K';
    }

    return $number.' '.$suffix;
}

function toGetSalesMasterLastStatus($id)
{
    $salesMaster = SalesMaster::where('id', $id)->first();
    $title = 'Application Pending';
    if ($salesMaster->application_pending == 1) {
        $title = 'Application Pending';
    }
    if ($salesMaster->pending_approvel == 1) {
        $title = 'Pending Approval';
    }

       if ($salesMaster->feasibility_approved == 1) {
        $title = 'Feasibility Approved';
    }
    if ($salesMaster->meter_charge_paid == 1) {
        $title = 'Meter Charge Paid';
    }

  /* For Loan */
    if ($salesMaster->apply_for_loan == 1) {
        $title = 'Apply for loan / Login';
    }

    // if ($salesMaster->login == 1) {
    //     $title = "Login";
    // }

    if ($salesMaster->loan_sension == 1) {
        $title = 'Loan Sension';
    }
    if ($salesMaster->disbursement == 1) {
        $title = 'Disbursement';
    }
    /* / For Loan */



    if ($salesMaster->payment_receveid == 1) {
        $title = 'Payment Received';
    }
    if ($salesMaster->dispach_pending_list == 1) {
        $title = 'Dispatch Pending List';
    }
    if ($salesMaster->installation_pending == 1) {
        $title = 'Installation Pending';
    }
    if ($salesMaster->installation_done == 1) {
        $title = 'Installation Done';
    }
    if ($salesMaster->meter_application_done == 1) {
        $title = 'Meter Application Done';
    }
    if ($salesMaster->meter_installation == 1) {
        $title = 'Meter Installation';
    }
    if ($salesMaster->subsidy_claimed == 1) {
        $title = 'Subsidy Request';
    }
    if ($salesMaster->subsidy_receveid == 1) {
        $title = 'Subsidy Disbursal';
    }
    if ($salesMaster->hold_query == 1) {
        $title = 'Hold / Query';
    }
    if ($salesMaster->file_cancel_order == 1) {
        $title = 'File Cancel Order';
    }

    return $title;
}

function installationAsignPerson($id)
{
    if ($id != '') {
        $user = AgentSalesPerson::where('user_id', $id)->first();
        if (! is_null($user)) {
            return $user->name;
        }
    }

    return '';
}

function getSubCommissionAgentName($salesMasterId)
{
    if (empty($salesMasterId)) {
        return '';
    }
    $sale = SalesMaster::with('agentsalesperson')->select('id', 'agent_sales_person_id')->find($salesMasterId);
    if (! is_null($sale) && ! is_null($sale->agentsalesperson)) {
        return $sale->agentsalesperson->name;
    }

    return '';
}

function getSubCommissionMainAgentName($agent_sales_person_id)
{
    if (empty($agent_sales_person_id)) {
        return '';
    }

    $subAgent = AgentSalesPerson::select('id', 'user_id')->find($agent_sales_person_id);
    if (is_null($subAgent) || empty($subAgent->user_id)) {
        return '';
    }

    $company = CompanyProfile::select('id', 'user_id')->where('user_id', $subAgent->user_id)->first();
    if (is_null($company)) {
        return '';
    }

    $uc = UserCommission::select('user_id')
        ->where('sub_agent_id', $company->id)
        ->orderBy('effective_date', 'desc')
        ->first();
    if (is_null($uc) || empty($uc->user_id)) {
        return '';
    }

    $mainAgent = AgentSalesPerson::select('name')->where('user_id', $uc->user_id)->first();
    if (! is_null($mainAgent) && ! empty($mainAgent->name)) {
        return $mainAgent->name;
    }

    $user = User::select('name')->find($uc->user_id);

    return $user->name ?? '';
}

/**
 * Recalculate and update SalesMaster commission fields for a given user based on
 * UserCommission effective date ranges.
 *
 * Behavior:
 * - Main Commission: rows where agent_sales_person_id belongs to this user, use slabs with sub_agent_id = 0 -> commission.
 * - Installation: rows where installation_asian_person equals this user, use slabs with sub_agent_id = 0 -> installation.
 * - Sub-Commission: for each slab with sub_agent_id > 0 (which points to company_profiles.id),
 *   find that sub-agent's user_id and their AgentSalesPerson id(s), then update those rows' sub_commission_amount.
 *
 * Note: We store per-kW rates in SalesMaster fields. Totals are computed as rate * KW elsewhere.
 */
function recalculateSalesMasterCommissionByEffectiveDates(int $userId): void
{
    // Fetch agent record for this user
    $agent = AgentSalesPerson::select('id', 'user_id')->where('user_id', $userId)->first();

    // Get all slabs for this user ordered by effective_date ascending
    $slabs = UserCommission::where('user_id', $userId)
        ->orderBy('effective_date', 'asc')
        ->get(['effective_date', 'commission', 'installation', 'sub_agent_id']);
    if ($slabs->isEmpty()) {
        return;
    }

    // Build ranges [current.eff, next.eff) and for last [last.eff, +inf)
    $ranges = [];
    for ($i = 0; $i < $slabs->count(); $i++) {
        $cur = $slabs[$i];
        $next = $slabs[$i + 1] ?? null;
        $start = $cur->effective_date; // inclusive
        $end = $next ? $next->effective_date : null; // exclusive
        $ranges[] = [
            'start' => $start,
            'end' => $end,
            'commission' => (float) ($cur->commission ?? 0),
            'installation' => (float) ($cur->installation ?? 0),
            'sub_agent_id' => (int) ($cur->sub_agent_id ?? 0),
        ];
    }

    foreach ($ranges as $range) {
        // MAIN COMMISSION (sub_agent_id == 0)
        if ($agent && $range['sub_agent_id'] === 0) {
            $q = SalesMaster::where('agent_sales_person_id', $agent->id)
                ->whereNotNull('invoice_date')
                ->where('invoice_date', '!=', '0000-00-00')
                ->where('file_cancel_order', '0')
                ->where('installation_done', '1')
                ->whereDate('invoice_date', '>=', $range['start']);
            if (! empty($range['end'])) {
                $q->whereDate('invoice_date', '<', $range['end']);
            }
            $q->update(['commission_amount' => $range['commission']]);
        }

        // INSTALLATION (sub_agent_id == 0)
        if ($range['sub_agent_id'] === 0) {
            $qi = SalesMaster::where('installation_asian_person', $userId)
                ->whereNotNull('invoice_date')
                ->where('invoice_date', '!=', '0000-00-00')
                ->where('file_cancel_order', '0')
                ->where('installation_done', '1')
                ->whereDate('invoice_date', '>=', $range['start']);
            if (! empty($range['end'])) {
                $qi->whereDate('invoice_date', '<', $range['end']);
            }
            $qi->update(['installation_amount' => $range['installation']]);
        }

        // SUB-COMMISSION (sub_agent_id > 0)
        if ($range['sub_agent_id'] > 0) {
            $subCompanyId = $range['sub_agent_id'];
            $subCompany = CompanyProfile::select('id', 'user_id')->find($subCompanyId);
            if (! $subCompany || empty($subCompany->user_id)) {
                continue;
            }
            // Sales rows created under this sub-agent (their agent_sales_person_id belongs to that user)
            $subAgentIds = AgentSalesPerson::where('user_id', $subCompany->user_id)->pluck('id');
            if ($subAgentIds->isEmpty()) {
                continue;
            }
            $qs = SalesMaster::whereIn('agent_sales_person_id', $subAgentIds)
                ->whereNotNull('invoice_date')
                ->where('invoice_date', '!=', '0000-00-00')
                ->where('file_cancel_order', '0')
                ->where('installation_done', '1')
                ->whereDate('invoice_date', '>=', $range['start']);
            if (! empty($range['end'])) {
                $qs->whereDate('invoice_date', '<', $range['end']);
            }
            $qs->update(['sub_commission_amount' => $range['commission']]);
        }
    }
}

/* FOR DATA IMPORT :: Sales Quatation */
function penalCompany($name)
{
    if ($name != '') {
        $res = PenalCompany::where('name', $name)->first();
        if (! is_null($res)) {
            return $res->id;
        }
    }

    return 0;
}

function penalType($name)
{
    if ($name != '') {
        $res = PenalType::where('name', $name)->first();
        if (! is_null($res)) {
            return $res->id;
        }
    }

    return 0;
}

function penalWatt($name)
{
    if ($name != '') {
        $res = PenalWatt::where('name', $name)->first();
        if (! is_null($res)) {
            return $res->id;
        }
    }

    return 0;
}

function inveterCompany($name)
{
    if ($name != '') {
        $res = InveterCompany::where('name', $name)->first();
        if (! is_null($res)) {
            return $res->id;
        }
    }

    return 0;
}

function taluka($name)
{
    if ($name != '') {
        $res = Taluka::where('name', $name)->first();
        if (! is_null($res)) {
            return $res->id;
        }
    }

    return 0;
}

function district($name)
{
    if ($name != '') {
        $res = District::where('name', $name)->first();
        if (! is_null($res)) {
            return $res->id;
        }
    }

    return 0;
}

function subDivision($name)
{
    if ($name != '') {
        $res = SubDivision::where('name', $name)->first();
        if (! is_null($res)) {
            return $res->id;
        }
    }

    return 0;
}
function installationParson($mobile)
{
    if ($mobile != '') {
        $res = User::where('mobile', $mobile)->first();
        if (! is_null($res)) {
            return $res->id;
        }
    }

    return 0;
}

function savedata($a, $row)
{
    $salesMaster = new SalesMaster();
    $salesMaster->user_id = Auth::id();
    $salesMaster->sales_quatation_id = $a;
    $salesMaster->agent_sales_person_id = 1;
    $salesMaster->consumer_number = $row['consumer_number'];
    $salesMaster->master_create_date = date('Y-m-d');
    $salesMaster->consumer_type = $row['consumer_type'];
    $salesMaster->consumer_name = $row['consumer_name'];
    $salesMaster->district_id = district($row['district']);
    $salesMaster->taluka_id = taluka($row['taluka']);
    $salesMaster->village_id = 0;
    $salesMaster->pin_code = $row['pincode'];
    $salesMaster->contact_number = $row['contact_number'];
    $salesMaster->register_kw = $row['registation_kw'];
    $salesMaster->email = $row['email'];
    $salesMaster->address = $row['address'];
    $salesMaster->aadhaar_number = $row['aadhaar_number'];
    $salesMaster->bank_name = $row['bank_name'];
    $salesMaster->bank_account = $row['bank_account'];
    $salesMaster->ifsc_code = $row['ifsc_code'];
    $salesMaster->contracted_load = $row['contracted_load'];
    $salesMaster->phase = $row['phase'];
    $salesMaster->sub_division_id = subDivision($row['sub_division']);
    $salesMaster->division = $row['division'];
    $salesMaster->circle = $row['circle'];
    $salesMaster->discom = $row['discom'];
    $salesMaster->reference = $row['reference'];
    $salesMaster->total_amount = $row['total_amount'];
    $salesMaster->pending_amonut = $row['total_amount'];
    $salesMaster->remark = $row['remark'];

    $salesMaster->ragistration_portal = $row['ragistration_portal'];
    $salesMaster->ragistration_number = $row['ragistration_numbar'];
    $salesMaster->ragistration_date = $row['date'];

    $salesMaster->feasibility_discom_sr_number = $row['discom_sr_number'];
    $salesMaster->discom_sr_numbar = $row['discom_sr_number'];
    $salesMaster->feasibility_amount = $row['feasibility_amount'];
    $salesMaster->feasibility_date = $row['discom_sr_date'];

    $salesMaster->invoice_no = $row['invoice_no'];
    $salesMaster->invoice_date = $row['date'];

    $salesMaster->installation_asian_person = installationParson($row['installation_parson']);
    $salesMaster->save();

    return true;
}
/* / FOR DATA IMPORT :: Sales Quatation */

function getLeadCount()
{
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

    return LeadMaster::with('agentSalesPerson')->whereRaw($where)->count();
}

function getPaddingSiteVisit()
{
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
    $lead = LeadMaster::with('agentsalesperson', 'company', 'followUp.image')
        ->select('id', 'company_profile_id', 'mobile', 'name', 'address', 'kw', 'reference', 'agent_sales_person_id', 'assign_id', 'status', 'site_visit_images', 'site_visit_remark', 'remark')
        ->whereRaw($where);

    $lead->where('site_visiter', null);
    $lead->where('status', '4');

    $lead = $lead->count();

    return $lead;
}

function getSiteVisited()
{
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
    $lead = LeadMaster::with('agentsalesperson', 'company')
        ->select('id', 'company_profile_id', 'mobile', 'name', 'address', 'kw', 'reference', 'agent_sales_person_id', 'assign_id', 'status', 'site_visit_images', 'site_visit_remark', 'remark')
        ->whereRaw($where);

    $user = User::where('id', Auth::id())->first();
    if ($user->roles[0]->name == 'Manager' || $user->roles[0]->name == 'Owner') {
        $lead->where('site_visiter', '!=', null);
    } else {
        $lead->where('site_visiter', Auth::id());
    }

    $lead = $lead->count();

    return $lead;
}

function getSalesQuatations()
{
    $querySalesQuatations = SalesQuatation::query();
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

    return $querySalesQuatations->count();
}

function getSalesOrder()
{
    $query = SalesMaster::select('id', 'agent_sales_person_id', 'register_kw')->with('agentsalesperson');
    $query->where('file_cancel_order', '0');
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

    return $query->get();
}

function getPayments()
{
    $paymentquery = PaymetCollection::selectRaw('status, sum(amount) as total')->with('salesMaster');

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
        $paymentquery->whereHas('salesMaster', function ($query) use ($agentIds) {
            $query->whereIn('agent_sales_person_id', $agentIds);
        });
    }
    if ($company->user_type == 'S') {
        $agent = AgentSalesPerson::where('user_id', Auth::id())->first();
        $id = $agent->id;

        $paymentquery->whereHas('salesMaster', function ($query) use ($id) {
            $query->where('agent_sales_person_id', $id);
        });
    }

    return $paymentquery->groupBy('status')->get();
}

function getPaymentStatus($status)
{
    switch ($status) {
        case 0:
            $label = 'Pending';
            $class = 'warning';
            break;
        case 1:
            $label = 'Approved';
            $class = 'success';
            break;
        case 2:
            $label = 'Hold';
            $class = 'info';
            break;
        case 3:
            $label = 'Return';
            $class = 'danger';
            break;
        default:
            $label = 'Pending';
            $class = 'warning';
            break;
    }

    return ['status' => $label, 'class' => $class];
}

function getSalesQuatationPanels($penal_company_id)
{
    $penel_companies_name = '';
    if ($penal_company_id != null) {
        $hist = PenalCompany::whereIn('id', explode(',', $penal_company_id))->get();
        if ($hist->count() > 0) {
            foreach ($hist as $ri) {
                $penel_companies_name != '' && $penel_companies_name .= ' / ';
                $penel_companies_name .= $ri->name;
            }
        }
    }

    return $penel_companies_name;
}

function getSalesQuatationInveter($inveter_company_id)
{
    $inveter_companies_name = '';
    if ($inveter_company_id != null) {
        $hist = InveterCompany::whereIn('id', explode(',', $inveter_company_id))->get();
        if ($hist != '') {
            $inveter_companies_name = '';
            foreach ($hist as $ri) {
                $inveter_companies_name != '' && $inveter_companies_name .= ' / ';
                $inveter_companies_name .= $ri->name;
            }
            $inveter_company_id = $inveter_companies_name;
        }
    }

    return $inveter_companies_name;
}

function scriptForSalesorder()
{
    $installation = Installation::get();
    if ($installation->count() > 0) {
        foreach ($installation as $key => $value) {
            $salesMaster = SalesMaster::where('id', $value->sales_master_id)->first();
            $salesMaster->penal_company_id = ($value->penal_company_id != '') ? $value->penal_company_id : 0;
            $salesMaster->penal_watt_id = ($value->penal_watt_id != '') ? $value->penal_watt_id : 0;
            $installationInvater = InstallationInvater::where('sales_master_id', $value->sales_master_id)->get();
            if ($installationInvater->count() > 0) {
                $inveter_company_id = [];
                foreach ($installationInvater as $k => $v) {
                    array_push($inveter_company_id, $v->invater_id);
                }
                $salesMaster->inveter_company_id = implode(',', $inveter_company_id);
            }
            $salesMaster->save();
        }
    }

    scriptForSalesorderquat();
}

function scriptForSalesorderquat()
{
    $SalesMaster = SalesMaster::where('penal_company_id', null)->where('penal_watt_id', null)->where('inveter_company_id', null)->get();
    if ($SalesMaster->count() > 0) {
        foreach ($SalesMaster as $key => $value) {
            $SalesQuatation = SalesQuatation::where('id', $value->sales_quatation_id)->first();

            $sales = SalesMaster::where('id', $value->id)->first();
            $sales->penal_company_id = explode(',', $SalesQuatation->penal_company_id)[0];
            $sales->penal_watt_id = $SalesQuatation->penal_watt_id;
            $sales->inveter_company_id = $SalesQuatation->inveter_company_id;
            $sales->save();

        }
    }
}

function getPaddingInstallation()
{
    $user = User::where('id', Auth::id())->first();

    $salesMasterQuery = SalesMaster::select('id', 'lead_master_id', 'sales_quatation_id', 'consumer_number', 'master_create_date', 'consumer_type', 'consumer_name', 'district_id', 'taluka_id', 'address', 'pin_code', 'contact_number', 'register_kw', 'reference', 'agent_sales_person_id', 'remark', 'installation_pending', 'installation_done', 'ragistration_portal', 'ragistration_number', 'feasibility_discom_sr_number', 'feasibility_amount', 'invoice_no', 'discom_sr_numbar', 'installation_date', 'installation_asian_person', 'sub_division_id')
        ->with('district', 'taluka', 'subDivision', 'agentsalesperson', 'salesquatation', 'salesquatation.penalType', 'salesquatation.penalWatt', 'lead');

    $company = CompanyProfile::where('user_id', Auth::id())->first();

    if ($company->user_type != 'O') {
        $salesMasterQuery->where('installation_asian_person', $user->id);
    }
    $salesMasterQuery->where('installation_pending', '1');
    $salesMasterQuery->where('installation_done', '0');

    return $salesMasterQuery->count();
}

function getInstallation()
{

    $user = User::where('id', Auth::id())->first();

    $salesMasterQuery = SalesMaster::select('id', 'lead_master_id', 'sales_quatation_id', 'consumer_number', 'master_create_date', 'consumer_type', 'consumer_name', 'district_id', 'taluka_id', 'address', 'pin_code', 'contact_number', 'register_kw', 'reference', 'agent_sales_person_id', 'remark', 'installation_pending', 'installation_done', 'ragistration_portal', 'ragistration_number', 'feasibility_discom_sr_number', 'feasibility_amount', 'invoice_no', 'discom_sr_numbar', 'installation_date', 'installation_asian_person', 'sub_division_id')
        ->with('district', 'taluka', 'subDivision', 'agentsalesperson', 'salesquatation', 'salesquatation.penalType', 'salesquatation.penalWatt', 'lead');
    $company = CompanyProfile::where('user_id', Auth::id())->first();
    if ($company->user_type != 'O') {
        $salesMasterQuery->where('installation_asian_person', $user->id);
    }
    $salesMasterQuery->where('installation_done', '1');
    $salesMasterQuery->where('meter_application_done', '0');

    return $salesMasterQuery->count();
}

function toGetSalesMasterStatusForDashboard($id)
{
    $salesMaster = SalesMaster::where('id', $id)->first();
    $title = 'Application Pending';
    if ($salesMaster->application_pending == 1) {
        $title = 'Application Pending';
        $value = 'application_pending';
        $next = 'pending_approvel';
    }
    if ($salesMaster->pending_approvel == 1) {
        $title = 'Pending Approval';
        $value = 'pending_approvel';
        $next = 'apply_for_loan';
    }

      if ($salesMaster->feasibility_approved == 1) {
        $title = 'Feasibility Approved';
        $value = 'feasibility_approved';
        $next = 'meter_charge_paid';
    }
    if ($salesMaster->meter_charge_paid == 1) {
        $title = 'Meter Charge Paid';
        $value = 'meter_charge_paid';
        $next = 'apply_for_loan';
    }


    /* For Loan */

    if ($salesMaster->apply_for_loan == 1) {
        $title = 'Apply for loan / Login';
        $value = 'apply_for_loan';
        $next = 'loan_sension';
    }



    // if ($salesMaster->login == 1) {
    //     $title = "Login";
    //     $value = "login";
    //     $next = "loan_sension";
    // }

    if ($salesMaster->loan_sension == 1) {
        $title = 'Loan Sension';
        $value = 'loan_sension';
        $next = 'disbursement';
    }

    if ($salesMaster->disbursement == 1) {
        $title = 'Disbursement';
        $value = 'disbursement';
        $next = 'feasibility_approved';
    }
    /* / For Loan */






    if ($salesMaster->payment_receveid == 1) {
        $title = 'Payment Received';
        $value = 'payment_receveid';
        $next = 'dispach_pending_list';
    }
    if ($salesMaster->dispach_pending_list == 1) {
        $title = 'Dispatch Pending List';
        $value = 'dispach_pending_list';
        $next = 'installation_pending';
    }
    if ($salesMaster->installation_pending == 1) {
        $title = 'Installation Pending';
        $value = 'installation_pending';
        $next = 'installation_done';
    }
    if ($salesMaster->installation_done == 1) {
        $title = 'Installation Done';
        $value = 'installation_done';
        $next = 'meter_application_done';
    }

    if ($salesMaster->meter_application_done == 1) {
        $title = 'Meter Application Done';
        $value = 'meter_application_done';
        $next = 'meter_installation';
    }
    if ($salesMaster->meter_installation == 1) {
        $title = 'Meter Installation';
        $value = 'meter_installation';
        $next = 'subsidy_claimed';
    }
    if ($salesMaster->subsidy_claimed == 1) {
        $title = 'Subsidy Request';
        $value = 'subsidy_claimed';
        $next = 'subsidy_receveid';
    }
    if ($salesMaster->subsidy_receveid == 1) {
        $title = 'Subsidy Disbursal';
        $value = 'subsidy_receveid';
        $next = '';
    }
    if ($salesMaster->hold_query == 1) {
        $title = 'Hold / Query';
        $value = 'hold_query';
        $next = '';
    }
    if ($salesMaster->file_cancel_order == 1) {
        $title = 'File Cancel Order';
        $value = 'file_cancel_order';
        $next = '';
    }

    return ['value' => $value, 'title' => $title, 'next' => $next];
}

function allSalesStatus()
{
    return [
        ['value' => 'application_pending', 'name' => 'Application Pending', 'is_remove' => 0, 'for_loan' => 0],
        ['value' => 'pending_approvel', 'name' => 'Pending Approval', 'is_remove' => 0, 'for_loan' => 0],

          ['value' => 'feasibility_approved', 'name' => 'Feasibility Approved', 'is_remove' => 0, 'for_loan' => 0],
        ['value' => 'meter_charge_paid', 'name' => 'Meter Charge Paid', 'is_remove' => 0, 'for_loan' => 0],


        /* For Loan */
        ['value' => 'apply_for_loan', 'name' => 'Apply for loan / Login', 'is_remove' => 0, 'for_loan' => 1],
        //   ['value' => 'login', 'name' =>  'Login','is_remove' => 0],
        ['value' => 'loan_sension', 'name' => 'Loan Sanction', 'is_remove' => 0, 'for_loan' => 1],
        ['value' => 'disbursement', 'name' => 'Disbursement', 'is_remove' => 0, 'for_loan' => 1],
        /* / For Loan */

        ['value' => 'payment_receveid', 'name' => 'Payment Received', 'is_remove' => 0, 'for_loan' => 0],
        ['value' => 'dispach_pending_list', 'name' => 'Dispatch Pending List', 'is_remove' => 1, 'for_loan' => 0],
        ['value' => 'installation_pending', 'name' => 'Installation Pending', 'is_remove' => 0, 'for_loan' => 0],
        ['value' => 'installation_done', 'name' => 'Installation Done', 'is_remove' => 0, 'for_loan' => 0],
        ['value' => 'meter_application_done', 'name' => 'Meter Application Done', 'is_remove' => 1, 'for_loan' => 0],
        ['value' => 'meter_installation', 'name' => 'Meter Installation', 'is_remove' => 1, 'for_loan' => 0],
        ['value' => 'subsidy_claimed', 'name' => 'Subsidy Request', 'is_remove' => 1, 'for_loan' => 0],
        ['value' => 'subsidy_receveid', 'name' => 'Subsidy Disbursal', 'is_remove' => 1, 'for_loan' => 0],
        ['value' => 'hold_query', 'name' => 'Hold / Query', 'is_remove' => 0, 'is_not_in_timeline' => 1, 'for_loan' => 0],
        ['value' => 'file_cancel_order', 'name' => 'File Cancel Order', 'is_remove' => 0, 'is_not_in_timeline' => 1, 'for_loan' => 0],
    ];
}

function getYear()
{
    $html = '';
    $years = Year::select('id', 'name')->orderBy('id', 'desc')->get();
    if (! is_null($years)) {
        foreach ($years as $key => $value) {
            $class = session()->get('year') == $value->name ? 'active' : '';
            $html .= '<a class="dropdown-item year-change '.$class.'" data-value="'.$value->name.'">'.$value->name.'</a>';
        }
    }

    return $html;
}

function convertNumberToWords(float $number)
{
    $decimal = round($number - ($no = floor($number)), 2) * 100;
    $hundred = null;
    $digits_length = strlen($no);
    $i = 0;
    $str = [];
    $words = [
        0 => '',
        1 => 'one',
        2 => 'two',
        3 => 'three',
        4 => 'four',
        5 => 'five',
        6 => 'six',
        7 => 'seven',
        8 => 'eight',
        9 => 'nine',
        10 => 'ten',
        11 => 'eleven',
        12 => 'twelve',
        13 => 'thirteen',
        14 => 'fourteen',
        15 => 'fifteen',
        16 => 'sixteen',
        17 => 'seventeen',
        18 => 'eighteen',
        19 => 'nineteen',
        20 => 'twenty',
        30 => 'thirty',
        40 => 'forty',
        50 => 'fifty',
        60 => 'sixty',
        70 => 'seventy',
        80 => 'eighty',
        90 => 'ninety',
    ];
    $digits = ['', 'hundred', 'thousand', 'lakh', 'crore'];
    while ($i < $digits_length) {
        $divider = ($i == 2) ? 10 : 100;
        $number = floor($no % $divider);
        $no = floor($no / $divider);
        $i += $divider == 10 ? 1 : 2;
        if ($number) {
            $plural = (($counter = count($str)) && $number > 9) ? 's' : null;
            $hundred = ($counter == 1 && $str[0]) ? ' and ' : null;
            $str[] = ($number < 21) ? $words[$number].' '.$digits[$counter].$plural.' '.$hundred : $words[floor($number / 10) * 10].' '.$words[$number % 10].' '.$digits[$counter].$plural.' '.$hundred;
        } else {
            $str[] = null;
        }
    }
    $Rupees = implode('', array_reverse($str));
    $paise = ($decimal > 0) ? ' and '.($words[$decimal / 10].' '.$words[$decimal % 10]).' Paise' : '';

    return ($Rupees ? $Rupees.'Rupees ' : '').$paise;
}

function getItemGropName($v, $dataType = 0)
{
    if ($dataType == 0) {
        $company = $type = $watt = $display_name = '';
        if ($v->group_type == 'inverter') {
            $display_name = $v->inveter_kw.' KW Inverter ('.(! is_null($v->inveter_company) ? $v->inveter_company->name : 'N/A').' | '.$v->inverter_type.')';
        } else {
            $company = ! is_null($v->panel_company) ? $v->panel_company->name : 'N/A';
            $type = ! is_null($v->panel_type) ? $v->panel_type->name : 'N/A';
            $watt = ! is_null($v->panel_watt) ? $v->panel_watt->name : 'N/A';
            $display_name = $watt.'W Solar Module ('.$company.' - '.$type.' | '.$v->p_type.')';
        }
    } else {
        if ($v->itemGroup->group_type == 'panel') {
            $company = ! is_null($v->itemGroup->panel_company) ? $v->itemGroup->panel_company->name : 'N/A';
            $type = ! is_null($v->itemGroup->panel_type) ? $v->itemGroup->panel_type->name : 'N/A';
            $watt = ! is_null($v->itemGroup->panel_watt) ? $v->itemGroup->panel_watt->name : 'N/A';
            $display_name = $watt.'W Solar Module ('.$company.' - '.$type.' | '.$v->itemGroup->p_type.')';
        } else {
            $display_name = $v->itemGroup->inveter_kw.' KW Inverter ('.(! is_null($v->itemGroup->inveter_company) ? $v->itemGroup->inveter_company->name : 'N/A').' | '.$v->itemGroup->inverter_type.')';
        }
    }

    return $display_name;
}

function getLastPrice($id, $type = 'Item')
{
    if ($id != '' && $type == 'Item') {
        $po = PurchaseDirectMeta::where('item_id', $id)->latest()->first();
        if (! is_null($po)) {
            return $po->price;
        }
    }
    if ($id != '' && $type == 'ItemGroup') {
        $po = PurchaseDirectMeta::where('item_group_id', $id)->latest()->first();
        if (! is_null($po)) {
            return $po->price;
        }
    }

    return 0;
}

function getLastPrice_augu($id, $type = 'type')
{
    if ($id != '' && $type == 'type') {
        $po = PurchaseDirectMeta::where('item_id', $id)->latest()->first();
        if (! is_null($po)) {
            return $po->price;
        }
    }
    if ($id != '' && $type == 'type') {
        $po = PurchaseDirectMeta::where('item_group_id', $id)->latest()->first();
        if (! is_null($po)) {
            return $po->price;
        }
    }

    return 0;
}

function getSalesOrderUsingIds($ids)
{
    return SalesMaster::select('id', 'sales_quatation_id', 'consumer_number', 'consumer_name', 'contact_number', 'address', 'district_id', 'taluka_id', 'pin_code', 'register_kw', 'inveter_company_id', 'penal_watt_id', 'penal_company_id')
        ->with('district', 'taluka', 'panel', 'panelwatt', 'salesquatationfull', 'salesquatationfull.penalWatt', 'salesquatationfull.penalType', 'inveter')
        ->whereIn('id', explode(',', $ids))->get();
}

function getSelectedYear()
{
    $year = Year::select('id', 'name')->where('name', session()->get('year'))->first();
    if (is_null($year)) {
        $year = Year::select('id')->where('is_default', '1')->first();
    }

    return $year->id;
}
function getLastRate($type, $id)
{
    $rate = PurchaseDirectMeta::where($type, $id)->latest()->first();

    return $rate->price ?? 0;
}

function getSelectedYearIsCurrent()
{
    $year = Year::select('is_default')->where('name', session()->get('year'))->first();
    if (! is_null($year)) {
        return ($year->is_default == '0') ? "<b><i class='fa fa-warning'></i> Attention : </b> <b class='blink-year'> You're outside the active financial year. </b>" : '';
    }

    return '';
}

function salespdfs()
{
    $data = [
        /* General For All & GJ */
        [
            'id' => 'self_certification_general',
            'name' => 'Self Certification (Recommended for GJ & ALL)',
            'display_name' => 'Self Certification',
            'value' => 'admin.sale.pdf.general.self_certification_pdf',
            'group' => 'general',
            'status_to_show' => 'installation_done',
            'route' => 'self-certification-pdf',
        ],
        [
            'id' => 'self_certification_general_sign',
            'name' => 'Self Certification With Stamp (Recommended for GJ & ALL)',
            'display_name' => 'Self Certification With Stamp ',
            'value' => 'admin.sale.pdf.general.self_certification_pdf',
            'group' => 'general',
            'status_to_show' => 'installation_done',
            'route' => 'self-certification-pdf-sign'
        ],
        [
            'id' => 'request_letter_general',
            'name' => 'Request Letter (Recommended for GJ & ALL)',
            'display_name' => 'Request Letter',
            'value' => 'admin.sale.pdf.general.request_letter_pdf',
            'group' => 'general',
            'status_to_show' => 'installation_done',
            'route' => 'request-letter-pdf',
        ],
        [
            'id' => 'model_agreement_general',
            'name' => 'Model Agreement (Recommended for GJ & ALL)',
            'display_name' => 'Model Agreement',
            'value' => 'admin.sale.pdf.general.model_aggriment_pdf',
            'group' => 'general',
            'status_to_show' => 'pending_approvel',
            'route' => 'model-agreement-pdf',
        ],
          [
            'id' => 'model_agreement_general_sign',
            'name' => 'Model Agreement With Stamp & Sign (Recommended for GJ & ALL)',
            'display_name' => 'Model Agreement With Stamp & Sign',
            'value' => 'admin.sale.pdf.general.model_aggriment_pdf',
            'group' => 'general',
            'status_to_show' => 'pending_approvel',
            'route' => 'model-agreement-pdf-sign'
        ],
        [
            'id' => 'declaration_dcr_general',
            'name' => 'Declaration DCR (Recommended for GJ & ALL)',
            'display_name' => 'Declaration DCR',
            'value' => 'admin.sale.pdf.general.declaration_dcr_pdf',
            'group' => 'general',
            'status_to_show' => 'installation_done',
            'route' => '',
        ],

        [
            'id' => 'agreement_general',
            'name' => 'Agreement (Recommended for GJ)',
            'display_name' => 'Agreement',
            'value' => 'admin.sale.pdf.general.agreement_pdf',
            'group' => 'general',
            'status_to_show' => 'pending_approvel',
            'route' => 'agreement-pdf',
        ],

         [
            'id' => 'agreement_general_sign',
            'name' => 'Agreement With Sign (Recommended for GJ)',
            'display_name' => 'Agreement With Sign',
            'value' => 'admin.sale.pdf.general.agreement_pdf',
            'group' => 'general',
            'status_to_show' => 'pending_approvel',
            'route' => 'agreement-pdf-sign',
        ],

        [
            'id' => 'geda_agreement_general',
            'name' => 'Geda Agreement (Recommended for GJ)',
            'display_name' => 'Geda Agreement',
            'value' => 'admin.sale.pdf.general.geda_agreement_pdf',
            'group' => 'general',
            'status_to_show' => 'pending_approvel',
            'route' => 'geda-agreement-pdf',
        ],
		
		 [
            'id' => 'pmsgmby_commissioning',
            'name' => 'PMSGMBY Commissioning (For PGVCL)',
            'display_name' => 'PMSGMBY Commissioning',
            'value' => 'admin.sale.pdf.general.pmsgmby_commissioning_pdf',
            'group' => 'general',
            'status_to_show' => 'pending_approvel',
            'route' => 'pmsgmby-commissioning-pdf',
        ],

        /*  / General For All & GJ */
        /*  For RJ */
        [
            'id' => 'vendor_feasibility_rajasthan',
            'name' => 'Vendor Feasibility (Recommended for RJ)',
            'display_name' => 'Vendor Feasibility',
            'value' => 'admin.sale.pdf.rajasthan.vendor_feasibility_pdf',
            'group' => 'rajasthan',
            'status_to_show' => 'feasibility_approved',
            'route' => 'vendor-feasibility-pdf',
        ],
        [
            'id' => 'net_meter_rajasthan',
            'name' => 'Net Meter (Recommended for RJ)',
            'display_name' => 'Net Meter',
            'value' => 'admin.sale.pdf.rajasthan.net_meter_pdf',
            'group' => 'rajasthan',
            'status_to_show' => 'installation_done',
            'route' => 'net-meter-pdf',
        ],
        [
            'id' => 'net_metering_inter_connection_agreement_rajasthan',
            'name' => 'Net Metering inter connection agreement (Recommended for RJ)',
            'display_name' => 'Net Metering Inter Connection Agreement',
            'value' => 'admin.sale.pdf.rajasthan.net_metering_inter_connection_agreement_pdf',
            'group' => 'rajasthan',
            'status_to_show' => 'pending_approvel',
            'route' => 'net-metering-inter-connection-pdf',
        ],
        /* / For RJ */
    ];

    return $data;
}

function getSelectedDiscom($discom)
{

    $discom = Discom::select('selected_pdfs')->where('discom_name', $discom)->first();
    if (! is_null($discom)) {
        return json_decode($discom->selected_pdfs, true);
    }

    return [];
}

function technicalSpecifications()
{
    return [
        [
            'itemDescription' => 'Module Mounting Structure<br>- Hot Dip Galvanize Iron<br>- 80- Micron Zinc Coting<br>- Ss 304 Bolt.',
            'qty' => 'Set',
            'size' => '6ft * 8ft',
            'make' => 'Reputed Make',
            'type' => 'structure',
        ],
        [
            'itemDescription' => 'AC Distribution Box',
            'qty' => 'Nos',
            'size' => 'As Per Design',
            'make' => 'L&T/Schneider Equivalent',
            'type' => 'other',
        ],
        [
            'itemDescription' => 'DC Distribution Box',
            'qty' => 'Nos',
            'size' => 'As Per Design',
            'make' => 'L&T/Schneider Equivalent',
            'type' => 'other',
        ],
        [
            'itemDescription' => 'AC Cables',
            'qty' => 'Mtr',
            'size' => 'As Per Design',
            'make' => 'Havells/Polycab/Equivalent',
            'type' => 'other',
        ],
        [
            'itemDescription' => 'DC Cables',
            'qty' => 'Mtr',
            'size' => 'As Per Design',
            'make' => 'Havells/Polycab/Equivalent',
            'type' => 'other',
        ],
        [
            'itemDescription' => 'LA Cables',
            'qty' => 'Mtr',
            'size' => 'As Per Design',
            'make' => 'Johnson/Kanbary/Equivalent',
            'type' => 'other',
        ],
        [
            'itemDescription' => 'Earthing Kit',
            'qty' => 'Nos',
            'size' => 'As Per Design',
            'make' => 'Reputed Make',
            'type' => 'other',
        ],
        [
            'itemDescription' => 'Lighting Arrester System',
            'qty' => 'Nos',
            'size' => 'As Per Design',
            'make' => 'Reputed Make',
            'type' => 'other',
        ],
        [
            'itemDescription' => 'BOS',
            'qty' => 'Nos',
            'size' => 'As Per Design',
            'make' => 'Reputed Make',
            'type' => 'other',
        ],
        [
            'itemDescription' => 'If any Condition, any Material is not available which is mentioned above for Supply, that Material will replace with Comparable Reputed Brand for Completion of Project without Prior Notice',
            'qty' => '',
            'size' => '',
            'make' => '',
            'type' => 'note',
        ],
    ];
}

function getDeliveryChallanSerialnoUplodedOrNot($id)
{

    $data = DeliveryChallanMeta::where('delivery_challan_id', $id)->withCount('serial_numbers_count')->where('item_id', 0)->get();

    return $data;
}

function getPurchaseDirectSerialnoUplodedOrNot($id)
{

    $data = PurchaseDirectMeta::where('purchase_direct_id', $id)->withCount('serial_numbers_count')->where('item_id', 0)->get();

    return $data;
}

function getDiscount($id)
{
    return PaymetCollection::selectRaw('sum(amount) as total')->where('sales_master_id', $id)->where('payment_type', 'Discount')->first()->total;
}

function getSelectedYearForApp()
{
    $year = Year::select('id')->where('is_default', '1')->first();

    return $year->id;
}

function getCommissionData($agent_id, $user_id, $fromDate, $toDate)
{

    $salesQuery = SalesMaster::query();
    if ($fromDate) {
        $salesQuery->where('invoice_date', '>=', $fromDate);
    }
    if ($toDate) {
        $salesQuery->where('invoice_date', '<=', $toDate);
    }

    $salesQuery->where(function ($query) use ($agent_id, $user_id) {
        $query->where('agent_sales_person_id', $agent_id)
            ->orWhere('installation_asian_person', $user_id);
    });

    $salesQuery->where('installation_done', '1');
    $salesQuery->where('file_cancel_order', '0');
    $salesQuery->whereNotNull('invoice_date')->where('invoice_date', '!=', '0000-00-00');
    $sales = $salesQuery->get();

    $openingBalance = [
        'payable' => 0.0,
        'paid' => 0.0,
        'outstanding' => 0.0,
    ];

    if ($fromDate) {
        $openingSalesQuery = SalesMaster::query();
        $openingSalesQuery->where('invoice_date', '<', $fromDate);
        $openingSalesQuery->where(function ($query) use ($agent_id, $user_id) {
            $query->where('agent_sales_person_id', $agent_id)
                ->orWhere('installation_asian_person', $user_id);
        });
        $openingSalesQuery->where('installation_done', '1');
        $openingSalesQuery->where('file_cancel_order', '0');
        $openingSalesQuery->whereNotNull('invoice_date')->where('invoice_date', '!=', '0000-00-00');
        $openingSales = $openingSalesQuery->get();

        $openingCommissionSum = 0.0;
        $openingSubCommissionSum = 0.0;
        $openingInstallationSum = 0.0;

        foreach ($openingSales as $sale) {
            $openingregisteredKw = $sale->installation->total_kv ?? $sale->register_kw;
            if ($agent_id == $sale->agent_sales_person_id) {
                $openingCommissionSum += (float) ($sale->commission_amount ?? 0) * $openingregisteredKw;
            }
            if ($user_id == ($sale->installation_asian_person ?? 0)) {
                $openingInstallationSum += (float) ($sale->installation_amount ?? 0) * $openingregisteredKw;
            }
        }

        $subAgents = AgentSalesPerson::where('user_id', $user_id)->get();
        foreach ($subAgents as $subAgent) {
            $salesSubAgent = SalesMaster::where('agent_sales_person_id', $subAgent->c_id)
                ->where('invoice_date', '<', $fromDate)
                ->where('installation_done', '1')
                ->where('file_cancel_order', '0')
                ->whereNotNull('invoice_date')->where('invoice_date', '!=', '0000-00-00')
                ->get();

            foreach ($salesSubAgent as $saleSubAgent) {
                $saleSubAgentRegisteredKw = $saleSubAgent->installation->total_kv ?? $saleSubAgent->register_kw;
                $commissionAmount = (float) ($saleSubAgent->sub_commission_amount ?? 0) * $saleSubAgentRegisteredKw;
                if ($commissionAmount > 0) {
                    $openingSubCommissionSum += $commissionAmount;
                }
            }
        }

        $openingBalance['payable'] = $openingCommissionSum + $openingSubCommissionSum + $openingInstallationSum;

        $openingPaidQuery = CommissionPayment::where('user_id', $user_id)
            ->where('payment_date', '<', $fromDate)
            ->where('status', 1);
        $openingPaidRows = $openingPaidQuery->get(['amount']);
        // Sum signed amounts so outstanding = payable - paid (math rules)
        $openingBalance['paid'] = (float) $openingPaidRows->sum('amount');

        $openingBalance['outstanding'] = $openingBalance['payable'] - $openingBalance['paid'];
    }

    $noOfFiles = 0;
    $kw = 0.0;
    $commissionSum = 0.0;
    $subCommissionSum = 0.0;
    $installationSum = 0.0;
    $subAgentIds = [];
    $countedSaleIds = [];
    $subCommissionFilesTotal = 0;
    $subCommissionKwTotal = 0.0;

    $lines = [];
    $mainCommissionByDate = [];
    $subCommissionByDate = [];
    $subCommissionByAgent = [];
    $installationByDate = [];

    $mainCommissionMonthly = [];
    $subCommissionMonthly = [];
    $installationMonthly = [];
    foreach ($sales as $sale) {

        $temp = false;

        $registeredKw = $sale->installation->total_kv ?? $sale->register_kw;

        if ($agent_id == $sale->agent_sales_person_id) {
            $commissionAmount = (float) ($sale->commission_amount ?? 0) * $registeredKw;
            if ($commissionAmount > 0 && ! empty($sale->invoice_date) && $sale->invoice_date != '0000-00-00') {
                $temp = true;
                $kw += (float) $registeredKw;
                $noOfFiles++;
                $countedSaleIds[$sale->id] = true;

                $commissionSum += $commissionAmount;
                $dateKey = date('Y-m-d', strtotime($sale->invoice_date));
                if (! isset($mainCommissionByDate[$dateKey])) {
                    $mainCommissionByDate[$dateKey] = ['files' => 0, 'kw' => 0.0, 'payable' => 0.0];
                }
                $mainCommissionByDate[$dateKey]['files'] += 1;
                $mainCommissionByDate[$dateKey]['kw'] += (float) $registeredKw;
                $mainCommissionByDate[$dateKey]['payable'] += $commissionAmount;
                $mKey = date('Y-m', strtotime($sale->invoice_date));
                if (! isset($mainCommissionMonthly[$mKey])) {
                    $mainCommissionMonthly[$mKey] = ['files' => 0, 'kw' => 0.0, 'payable' => 0.0];
                }
                $mainCommissionMonthly[$mKey]['files'] += 1;
                $mainCommissionMonthly[$mKey]['kw'] += (float) $registeredKw;
                $mainCommissionMonthly[$mKey]['payable'] += $commissionAmount;
            }
        }

        if ($user_id == $sale->installation_asian_person) {
            $installationAmount = (float) ($sale->installation_amount ?? 0) * $registeredKw;
            if ($installationAmount > 0 && ! empty($sale->invoice_date) && $sale->invoice_date != '0000-00-00' && (string) $sale->installation_done === '1') {
                if ($temp == false) {
                    $kw += (float) $registeredKw;
                    $noOfFiles++;
                    $countedSaleIds[$sale->id] = true;
                }
                $installationSum += $installationAmount;
                $refDate = $sale->invoice_date;
                $instKey = date('Y-m-d', strtotime($refDate));
                if (! isset($installationByDate[$instKey])) {
                    $installationByDate[$instKey] = ['files' => 0, 'kw' => 0.0, 'payable' => 0.0];
                }
                $installationByDate[$instKey]['files'] += 1;
                $installationByDate[$instKey]['kw'] += (float) $registeredKw;
                $installationByDate[$instKey]['payable'] += $installationAmount;
                $imKey = date('Y-m', strtotime($refDate));
                if (! isset($installationMonthly[$imKey])) {
                    $installationMonthly[$imKey] = ['files' => 0, 'kw' => 0.0, 'payable' => 0.0];
                }
                $installationMonthly[$imKey]['files'] += 1;
                $installationMonthly[$imKey]['kw'] += (float) $registeredKw;
                $installationMonthly[$imKey]['payable'] += $installationAmount;
            }
        }
    }

    $subAgentUserIds = UserCommission::where('user_id', $user_id)
        ->whereNotNull('sub_agent_id')
        ->where('sub_agent_id', '>', 0)
        ->pluck('sub_agent_id')
        ->unique()
        ->filter()
        ->values();

    if ($subAgentUserIds->isNotEmpty()) {
        $subAgents = AgentSalesPerson::join('company_profiles', 'company_profiles.user_id', 'agent_sales_people.user_id')->whereIn('company_profiles.id', $subAgentUserIds)->get(['agent_sales_people.id', 'agent_sales_people.user_id', 'company_profiles.id as c_id', 'agent_sales_people.name']);
        foreach ($subAgents as $subAgent) {
            $salesQuerySubAgent = SalesMaster::query();
            if ($fromDate) {
                $salesQuerySubAgent->where('invoice_date', '>=', $fromDate);
            }
            if ($toDate) {
                $salesQuerySubAgent->where('invoice_date', '<=', $toDate);
            }
            $salesQuerySubAgent->where('agent_sales_person_id', $subAgent->id);
            $salesQuerySubAgent->where('installation_done', '1');
            $salesQuerySubAgent->where('file_cancel_order', '0');
            $salesQuerySubAgent->whereNotNull('invoice_date')->where('invoice_date', '!=', '0000-00-00');
            $salesSubAgent = $salesQuerySubAgent->get();

            $subAgentFiles = 0;
            $subAgentKw = 0.0;
            $subAgentCommissionTotal = 0.0;
            $subAgentDates = [];

            foreach ($salesSubAgent as $saleSubAgent) {
                $registeredKwSubAgent = $saleSubAgent->installation->total_kv ?? $saleSubAgent->register_kw;
                $commissionAmount = (float) ($saleSubAgent->sub_commission_amount ?? 0) * $registeredKwSubAgent;
                if ($commissionAmount > 0) {
                    $countedSaleIds[$saleSubAgent->id] = true;
                    $subAgentCommissionTotal += $commissionAmount;
                    $subAgentFiles += 1;
                    $subAgentKw += (float) $saleSubAgent->register_kw;

                    $dateKey = date('Y-m-d', strtotime($saleSubAgent->invoice_date));
                    $subAgentDates[] = $dateKey;

                    if (! isset($subCommissionByDate[$dateKey])) {
                        $subCommissionByDate[$dateKey] = ['payable' => 0.0];
                    }
                    $subCommissionByDate[$dateKey]['payable'] += $commissionAmount;
                    $smKey = date('Y-m', strtotime($saleSubAgent->invoice_date));
                    if (! isset($subCommissionMonthly[$smKey])) {
                        $subCommissionMonthly[$smKey] = ['files' => 0, 'kw' => 0.0, 'payable' => 0.0];
                    }
                    $subCommissionMonthly[$smKey]['files'] += 1;
                    $subCommissionMonthly[$smKey]['kw'] += (float) $saleSubAgent->register_kw;
                    $subCommissionMonthly[$smKey]['payable'] += $commissionAmount;
                }
            }

            if ($subAgentCommissionTotal > 0) {
                $subCommissionSum += $subAgentCommissionTotal;
                $subCommissionFilesTotal += $subAgentFiles;
                $subCommissionKwTotal += $subAgentKw;

                $subAgentKey = $subAgent->c_id.'_'.$subAgent->name;
                $subCommissionByAgent[$subAgentKey] = [
                    'agent_id' => $subAgent->c_id,
                    'agent_name' => $subAgent->name,
                    'files' => $subAgentFiles,
                    'kw' => $subAgentKw,
                    'payable' => $subAgentCommissionTotal,
                    'dates' => array_unique($subAgentDates),
                ];
            }
        }
    }

    $noOfFiles += $subCommissionFilesTotal;
    $kw += $subCommissionKwTotal;

    $totalPayable = $commissionSum + $subCommissionSum + $installationSum;

    $paidQuery = CommissionPayment::where('user_id', $user_id);
    if ($fromDate) {
        $paidQuery->where('payment_date', '>=', $fromDate);
    }
    if ($toDate) {
        $paidQuery->where('payment_date', '<=', $toDate);
    }
    $paidRows = $paidQuery->where('status', 1)->get(['amount', 'payment_date', 'payment_type', 'remark']);
    // Total paid should be signed so pending_payout = total_payable - total_paid (math rules)
    $totalPaid = (float) $paidRows->sum('amount');

    if ($fromDate) {
        $totalPayable += $openingBalance['payable'];
        $totalPaid += $openingBalance['paid'];
    }

    $pendingPayout = $totalPayable - $totalPaid;

    $customerPaymentPending = 0.0;
    if (! empty($countedSaleIds)) {
        $customerPaymentPending = (float) SalesMaster::whereIn('id', array_keys($countedSaleIds))->sum('pending_amonut');
    }

    $events = [];

    if ($fromDate && ($openingBalance['payable'] > 0 || $openingBalance['paid'] > 0)) {
        $events[] = [
            'date' => date('Y-m-d', strtotime($fromDate.' -1 day')),
            'type' => 'opening_balance',
            'label' => 'Opening Balance (Before '.date('d-m-Y', strtotime($fromDate)).')',
            'files' => 0,
            'kw' => 0.0,
            'amount' => $openingBalance['outstanding'],
            'timestamp' => strtotime($fromDate.' -1 day') - 1,
        ];
    }

    $allMonths = array_unique(array_merge(array_keys($mainCommissionMonthly), array_keys($subCommissionMonthly), array_keys($installationMonthly)));
    sort($allMonths);
    foreach ($allMonths as $mKey) {
        $monthDate = date('Y-m-t', strtotime($mKey.'-01'));
        if (isset($mainCommissionMonthly[$mKey]) && ($mainCommissionMonthly[$mKey]['payable'] ?? 0) > 0) {
            $vals = $mainCommissionMonthly[$mKey];
            $events[] = [
                'date' => $monthDate,
                'type' => 'commission',
                'label' => 'Commission ('.date('M-Y', strtotime($monthDate)).')',
                'files' => (int) ($vals['files'] ?? 0),
                'kw' => (float) ($vals['kw'] ?? 0.0),
                'amount' => (float) $vals['payable'],
                'timestamp' => strtotime($monthDate.' 23:59:00'),
            ];
        }
        if (isset($subCommissionMonthly[$mKey]) && ($subCommissionMonthly[$mKey]['payable'] ?? 0) > 0) {
            $vals = $subCommissionMonthly[$mKey];
            $events[] = [
                'date' => $monthDate,
                'type' => 'sub_commission',
                'label' => 'Sub Commission ('.date('M-Y', strtotime($monthDate)).')',
                'files' => (int) ($vals['files'] ?? 0),
                'kw' => (float) ($vals['kw'] ?? 0.0),
                'amount' => (float) $vals['payable'],
                'timestamp' => strtotime($monthDate.' 23:59:01'),
            ];
        }
        if (isset($installationMonthly[$mKey]) && ($installationMonthly[$mKey]['payable'] ?? 0) > 0) {
            $vals = $installationMonthly[$mKey];
            $events[] = [
                'date' => $monthDate,
                'type' => 'installation',
                'label' => 'Installation ('.date('M-Y', strtotime($monthDate)).')',
                'files' => (int) ($vals['files'] ?? 0),
                'kw' => (float) ($vals['kw'] ?? 0.0),
                'amount' => (float) $vals['payable'],
                'timestamp' => strtotime($monthDate.' 23:59:02'),
            ];
        }
    }
    foreach ($paidRows as $pr) {
        if ((float) $pr->amount == 0) {
            continue;
        }
        $events[] = [
            'date' => $pr->payment_date ? date('Y-m-d', strtotime($pr->payment_date)) : '1970-01-01',
            'type' => 'payment',
            'label' => $pr->payment_type.($pr->remark ? ' ('.$pr->remark.')' : ''), //in_array($pr->payment_type, ['Cheque','NEFT','UPI','RTGS','IMPS','Bank']) ? $pr->payment_type : 'Customer Payment',
            'files' => 0,
            'kw' => 0.0,
            'amount' => (float) $pr->amount,
            'timestamp' => $pr->payment_date ? strtotime($pr->payment_date) : strtotime('1970-01-01'),
        ];
    }
    usort($events, function ($a, $b) {
        if ($a['timestamp'] === $b['timestamp']) {
            $order = ['opening_balance' => -1, 'commission' => 0, 'sub_commission' => 1, 'installation' => 2, 'payment' => 3];
            $ai = $order[$a['type']] ?? 9;
            $bi = $order[$b['type']] ?? 9;

            return $ai <=> $bi;
        }

        return $a['timestamp'] <=> $b['timestamp'];
    });
    $runningOutstanding = 0.0;
    foreach ($events as $ev) {
        if ($ev['type'] === 'opening_balance') {
            $runningOutstanding = $ev['amount'];
            $lines[] = [
                'date' => date('d-m-Y', strtotime($ev['date'])),
                'particular' => $ev['label'],
                'files' => '-',
                'kw' => '-',
                'payable' => 'Rs. '.number_format($openingBalance['payable'], 2),
                'paid' => 'Rs. '.number_format($openingBalance['paid'], 2),
                'outstanding' => 'Rs. '.number_format($runningOutstanding, 2),
            ];
        } elseif (in_array($ev['type'], ['commission', 'sub_commission', 'installation'])) {
            $runningOutstanding += $ev['amount'];
            $lines[] = [
                'date' => date('d-m-Y', strtotime($ev['date'])),
                'particular' => $ev['label'],
                'files' => $ev['files'] > 0 ? $ev['files'] : '-',
                'kw' => $ev['kw'] > 0 ? number_format($ev['kw'], 3) : '-',
                'payable' => 'Rs. '.number_format($ev['amount'], 2),
                'paid' => '-',
                'outstanding' => 'Rs. '.number_format($runningOutstanding, 2),
            ];
        } else {
            // Payment: subtract signed amount so (- - = +) per math rules
            $runningOutstanding -= (float) $ev['amount'];
            $lines[] = [
                'date' => date('d-m-Y', strtotime($ev['date'])),
                'particular' => $ev['label'],
                'files' => '-',
                'kw' => '-',
                'payable' => '-',
                'paid' => 'Rs. '.number_format($ev['amount'], 2),
                'outstanding' => 'Rs. '.number_format($runningOutstanding, 2),
            ];
        }
    }

    return $rows = [
        'no_of_file' => $noOfFiles,
        'kw' => number_format($kw, 3),
        'commission' => number_format($commissionSum, 2),
        'sub_commission' => number_format($subCommissionSum, 2),
        'installation' => number_format($installationSum, 2),
        'total_payable' => number_format($totalPayable, 2),
        'total_paid' => number_format($totalPaid, 2),
        'pending_payout' => number_format($pendingPayout, 2),
        'customer_payment_pending' => number_format($customerPaymentPending, 2),
        'counted_sale_ids' => array_keys($countedSaleIds),
        'opening_balance' => $openingBalance,
        'lines' => $lines,
    ];
}

function getSalesQuotationStatusClass($status)
{
    switch ($status) {
        case 'active':
            $label = 'Active';
            $class = 'warning';
            break;
        case 'accepted':
            $label = 'Accepted';
            $class = 'success';
            break;
        case 'revised':
            $label = 'Revised';
            $class = 'info';
            break;
        case 'cancelled':
            $label = 'Cancelled/Lost';
            $class = 'danger';
            break;
        default:
            $label = 'Active';
            $class = 'warning';
            break;
    }
    return ['status' => $label, 'class' => $class];
}

function salesQuotationStatus()
{
    return [
        ['id' => 'active', 'name' => 'Active'],
        ['id' => 'accepted', 'name' => 'Accepted'],
        ['id' => 'cancelled', 'name' => 'Cancelled/Lost'],
        ['id' => 'revised', 'name' => 'Revised']
    ];
}

function getServiceStatusClass($status)
{
    switch ($status) {
        case 'new_service':
            $label = 'New Service';
            $class = 'secondary';
            $icon = 'plus';
            break;
        case 'work_in_progress':
            $label = 'Work In Progress';
            $class = 'info';
            $icon = 'hourglass';
            break;
        case 'replacement':
            $label = 'Replacement';
            $class = 'warning';
            $icon = 'refresh';
            break;
        case 'out_of_warranty':
            $label = 'Out Of Warranty';
            $class = 'danger';
            $icon = 'warning';
            break;
        case 'random_visit_today':
            $label = 'Random Visit Today';
            $class = 'primary';
            $icon = 'calendar-check';
            break;
        case 'completed':
            $label = 'Completed';
            $class = 'success';
            $icon = 'check';
            break;
        default:
            $label = 'New Service';
            $class = 'secondary';
            $icon = 'plus';
            break;
    }
    return ['status' => $label, 'class' => $class,'icon' => $icon];
}

function serviceStatus()
{
    return [
        ['id' => 'new_service', 'name' => 'New Service','icon' => 'plus'],
        ['id' => 'work_in_progress', 'name' => 'Work In Progress','icon' => 'hourglass'],
        ['id' => 'replacement', 'name' => 'Replacement', 'icon' => 'refresh'],
        ['id' => 'out_of_warranty', 'name' => 'Out Of Warranty','icon' => 'warning'],
        ['id' => 'random_visit_today', 'name' => 'Random Visit Today','icon' => 'calendar-check'],
        ['id' => 'completed', 'name' => 'Completed','icon' => 'check']
    ];
}

