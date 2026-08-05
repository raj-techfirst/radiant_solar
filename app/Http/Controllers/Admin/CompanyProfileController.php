<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\CompanyProfile;
use App\Models\Source;
use App\Models\State;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CompanyProfileController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:profile-view-edit', ['only' => ['create', 'store']]);
    }

    public function index()
    {
        return abort(404);
    }

    public function create()
    {
        $company = CompanyProfile::with('user')->where('user_id', Auth::id())->first();
        $state = State::get();
        $city = City::where('state_id', $company->state_id)->get();
        return view('admin.company.view_edit_profile', compact('company', 'city', 'state'));
    }

    public function store(Request $request)
    {
        if (Auth::user()->roles[0]->name == 'Owner') {
            $temp = 'required';
        } else {
            $temp = 'nullable';
        }
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'last_name' => 'required',
            // 'mobile' => 'required',
            'company_name' => $temp,
            'state_id' => 'required',
            'city_id' => 'required',
        ], [
            'name.required' => 'Enter first name',
            'last_name.required' => 'Enter last name',
            // 'mobile.required' => 'Enter mobile number',
            'company_name.required' => 'Enter company name',
            'state_id.required' => 'Select state',
            'city_id.required' => 'Select city',
        ]);
        if ($validator->fails()) {
            $response = ['status' => false, 'message' => 'Please input proper data.', 'errors' => $validator->errors()];
            return response()->json($response);
        }
        DB::beginTransaction();
        try {
            $user = User::where('id', Auth::id())->first();
            $user->name = $request->name;
            $user->last_name = $request->last_name;
            $user->company_name = $request->company_name;
            $user->mobile = $request->mobile;
            // $user->email = $request->email;
            $result = $user->save();
            DB::commit();
            if (!is_null($result)) {
                $companyProfile = CompanyProfile::where('user_id', $user->id)->first();

                // dd($request->terms_conditions);
                $companyProfile->state_id = $request->state_id;
                $companyProfile->city_id = $request->city_id;
                $companyProfile->address = $request->address;
                $companyProfile->business_name = $request->business_name;
                $companyProfile->terms_conditions = $request->terms_conditions;
                $companyProfile->indiamart_key = $request->indiamart_key;
                $companyProfile->justdial_key = $request->justdial_key;
                if (!is_null($request->indiamart_key)) {
                    $source_name = 'IndiaMART';
                    $source = Source::where('source_name', $source_name)->first();
                    if (!is_null($source)) {
                        $end_date = date("d-M-Y");
                        $start_date = date("d-M-Y", strtotime("-7 day"));
                        $link = $source->source_link . $request->indiamart_key . '&start_time=' . $start_date . '&end_time=' . $end_date;
                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($ch, CURLOPT_URL, $link);
                        $result = curl_exec($ch);
                        curl_close($ch);
                        $data = json_decode($result, true);
                        if ($data['CODE'] == 401) {
                            $companyProfile->is_indiamart = (!empty($request->is_indiamart)) ? 0 : 0;
                            $companyProfile->is_justdial = (!empty($request->is_justdial)) ? 0 : 0;
                            $response = ['data' => route('profile.create'), 'status' => 'FAILURE', 'message' => 'CRM key that you are using is incorrect. Kindly use the correct CRM key as provided in the email.'];
                        } else {
                            $companyProfile->is_indiamart = (!empty($request->is_indiamart)) ? $request->is_indiamart : 0;
                            $companyProfile->is_justdial = (!empty($request->is_justdial)) ? $request->is_indiamart : 0;
                            $response = ['data' => route('profile.create'), 'status' => true, 'message' => ' Profile updated successfully.'];
                        }
                    } else {
                        $response = ['status' => false, 'code' => '404', 'message' => 'Source not found.'];
                    }
                } else {
                    $response = ['data' => route('profile.create'), 'status' => true, 'message' => ' Profile updated successfully.'];
                }
                $companyProfile->save();

                // if (count($request->invoice) > 0) {
                //     foreach ($request->invoice as $key => $value) {
                //         if (!is_null($value['title'])  && !is_null($value['message']) && $value['title'] > 0 && $value['message'] > 0) {
                           
                //         } else {
                //             $response = ['status' => false, 'message' => 'Please input proper data.'];
                //             return response()->json($response);
                //         }
                //     }
                // }


                return response()->json($response);
            } else {
                DB::rollback();
                $response = ['status' => false, 'server_error' => 'Something went wrong. Please try again.'];
                return response()->json($response);
            }
        } catch (\Exception $e) {
            DB::rollback();
            dd($e);
            $response = ['status' => false, 'server_error' => 'Something went wrong. Please try again.'];
            return response()->json($response);
        }
    }

    public function show(CompanyProfile $companyProfile)
    {
        return abort(404);
    }

    public function edit(CompanyProfile $companyProfile)
    {
        return abort(404);
    }

    public function update(Request $request, CompanyProfile $companyProfile)
    {
        //
    }

    public function destroy(CompanyProfile $companyProfile)
    {
        //
    }
}
