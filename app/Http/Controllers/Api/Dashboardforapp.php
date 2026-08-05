<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AgentSalesPerson;
use App\Models\LeadMaster;
use App\Models\PaymetCollection;
use App\Models\SalesMaster;
use App\Models\SalesQuatation;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use function PHPUnit\Framework\isNull;

class Dashboardforapp extends Controller
{
    public function index()
    {
         try {

            $response = ['status' => true, 'result' => []];

            $saleOrder = getSalesOrder();

            if ($saleOrder->count() > 0) {

                $salesData['forstatus']['total']['name'] = 'Total';
                $salesData['forstatus']['total']['count'] = 0;
                $salesData['forstatus']['total']['register_kw'] = 0;

                $salesData['forstatus']['application_pending']['name'] = 'Application Pending';
                $salesData['forstatus']['application_pending']['count'] = 0;
                $salesData['forstatus']['application_pending']['register_kw'] = 0;

                $salesData['forstatus']['pending_approval']['name'] = 'Pending Approval';
                $salesData['forstatus']['pending_approval']['count'] = 0;
                $salesData['forstatus']['pending_approval']['register_kw'] = 0;

                $salesData['forstatus']['document_verified']['name'] = 'Document Verified';
                $salesData['forstatus']['document_verified']['count'] = 0;
                $salesData['forstatus']['document_verified']['register_kw'] = 0;

                $salesData['forstatus']['feasibility_approved']['name'] = 'Feasibility Approved';
                $salesData['forstatus']['feasibility_approved']['count'] = 0;
                $salesData['forstatus']['feasibility_approved']['register_kw'] = 0;

                $salesData['forstatus']['payment_received']['name'] = 'Payment Received'; 
                $salesData['forstatus']['payment_received']['count'] = 0;
                $salesData['forstatus']['payment_received']['register_kw'] = 0;

                $salesData['forstatus']['dispatch_pending_list']['name'] = 'Dispatch Pending List';
                $salesData['forstatus']['dispatch_pending_list']['count'] = 0;
                $salesData['forstatus']['dispatch_pending_list']['register_kw'] = 0;

                $salesData['forstatus']['installation_pending']['name'] = 'Installation Pending';
                $salesData['forstatus']['installation_pending']['count'] = 0;
                $salesData['forstatus']['installation_pending']['register_kw'] = 0;

                $salesData['forstatus']['installation_done']['name'] = 'Installation Done';
                $salesData['forstatus']['installation_done']['count'] = 0;
                $salesData['forstatus']['installation_done']['register_kw'] = 0;

                $salesData['forstatus']['meter_application_done']['name'] = 'Meter Application Done';
                $salesData['forstatus']['meter_application_done']['count'] = 0;
                $salesData['forstatus']['meter_application_done']['register_kw'] = 0;

                $salesData['forstatus']['meter_installation']['name'] = 'Meter Installation';
                $salesData['forstatus']['meter_installation']['count'] = 0;
                $salesData['forstatus']['meter_installation']['register_kw'] = 0;

                $salesData['forstatus']['subsidy_request']['name'] = 'Subsidy Request';
                $salesData['forstatus']['subsidy_request']['count'] = 0;
                $salesData['forstatus']['subsidy_request']['register_kw'] = 0;

                $salesData['forstatus']['subsidy_disbursal']['name'] = 'Subsidy Disbursal';
                $salesData['forstatus']['subsidy_disbursal']['count'] = 0;
                $salesData['forstatus']['subsidy_disbursal']['register_kw'] = 0;

                $salesData['forstatus']['hold_/_query']['name'] = 'Hold / Query';
                $salesData['forstatus']['hold_/_query']['count'] = 0;
                $salesData['forstatus']['hold_/_query']['register_kw'] = 0;


                foreach ($saleOrder as $key => $value) :

                    $lastStatus = toGetSalesMasterLastStatus($value->id);
                    $status = strtolower(str_replace(" ", "_", $lastStatus));

                    $salesData['forstatus']['total']['name'] = "Total";
                    if (isset($salesData['forstatus']['total']['count'])) {
                        $salesData['forstatus']['total']['count'] += 1;
                        $salesData['forstatus']['total']['register_kw'] +=  $value->register_kw;
                    } else {
                        $salesData['forstatus']['total']['count'] = 1;
                        $salesData['forstatus']['total']['register_kw'] =  $value->register_kw;
                    }
                    $salesData['foragent']['total']['name'] = "Total";
                    if (isset($salesData['foragent']['total']['count'])) {
                        $salesData['foragent']['total']['count'] += 1;
                        $salesData['foragent']['total']['register_kw'] +=  $value->register_kw;
                    } else {
                        $salesData['foragent']['total']['count'] = 1;
                        $salesData['foragent']['total']['register_kw'] =  $value->register_kw;
                    }
                    $salesData['forstatus'][$status]['name'] = $lastStatus;
                    if (isset($salesData['forstatus'][$status]['count'])) {
                        $salesData['forstatus'][$status]['count'] += 1;
                        $salesData['forstatus'][$status]['register_kw'] += $value->register_kw;
                    } else {
                        $salesData['forstatus'][$status]['count'] = 1;
                        $salesData['forstatus'][$status]['register_kw'] =  $value->register_kw;
                    }
                    $salesData['foragent'][$value->agentsalesperson->id]['name'] = $value->agentsalesperson->name;
                    if (isset($salesData['foragent'][$value->agentsalesperson->id]['count'])) {
                        $salesData['foragent'][$value->agentsalesperson->id]['count'] += 1;
                        $salesData['foragent'][$value->agentsalesperson->id]['register_kw'] +=  $value->register_kw;
                    } else {
                        $salesData['foragent'][$value->agentsalesperson->id]['count'] = 1;
                        $salesData['foragent'][$value->agentsalesperson->id]['register_kw'] =  $value->register_kw;
                    }

                    $salesData['forstatus'][$status]['register_kw'] = number_format($salesData['forstatus'][$status]['register_kw'], 3, '.', '');
                    $salesData['foragent'][$value->agentsalesperson->id]['register_kw'] = number_format($salesData['foragent'][$value->agentsalesperson->id]['register_kw'], 3, '.', '');
                endforeach;

                $salesData['forstatus']['total']['register_kw'] = (string)number_format($salesData['forstatus']['total']['register_kw'], 3, '.', '');
                $salesData['foragent']['total']['register_kw'] = (string)number_format($salesData['foragent']['total']['register_kw'], 3, '.', '');
            }
            if ((isset($salesData['forstatus']))) {
                $data['salesData']['forstatus'] =  (isset($salesData['forstatus'])) ? array_values($salesData['forstatus']) : [];
                $data['salesData']['foragent'] =  (isset($salesData['foragent'])) ? array_values($salesData['foragent']) : [];
            }
            $data['payment'] = getPayments();
            $data['leads'] = getLeadCount();
            $data['salesQuatations'] = getSalesQuatations();
            $response = ['status' => true, 'result' => $data];
            
            $data['installed'] = getInstallation();
            $data['paddingInstallation'] = getPaddingInstallation();

            $data['paddingSiteVisit'] = getPaddingSiteVisit();
            $data['siteVisited'] = getSiteVisited();

            $response = ['status' => true, 'result' => $data];

            return response($response, 200);
        } catch (Exception $e) {
            $response = ['status' => false, 'message' => 'Something went wrong. Please try again.'];
            return response($response, 500);
        }
    }
}
