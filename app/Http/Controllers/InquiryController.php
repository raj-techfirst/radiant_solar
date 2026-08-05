<?php

namespace App\Http\Controllers;

use App\Models\AgentSalesPerson;
use App\Models\Inquiry;
use App\Models\InquiryFollow;
use App\Models\PaymetCollection;
use App\Models\SalesMaster;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class InquiryController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:inquiry-list', ['only' => ['index']]);
        $this->middleware('permission:inquiry-edit', ['only' => ['edit']]);
        $this->middleware('permission:inquiry-delete', ['only' => ['destroy']]);
    }

    public function index()
    {
        if (request()->ajax()) {
            return DataTables::of(Inquiry::orderBy('id', 'DESC'))
                ->filter(function ($query) {
                    if (request()->filled('from_date') && request()->filled('to_date')) {
                        $from = date('Y-m-d', strtotime(request('from_date')));
                        $to = date('Y-m-d', strtotime(request('to_date')));
                        $query->whereDate('created_at', '>=', $from)->whereDate('created_at', '<=', $to);
                    } elseif (request()->filled('from_date')) {
                        $from = date('Y-m-d', strtotime(request('from_date')));
                        $query->whereDate('created_at', '>=', $from);
                    } elseif (request()->filled('to_date')) {
                        $to = date('Y-m-d', strtotime(request('to_date')));
                        $query->whereDate('created_at', '<=', $to);
                    }

                    if (request()->filled('consumer')) {
                        $search = request('consumer');
                        $query->where(function ($q) use ($search) {
                            $q->where('consumer_name', 'LIKE', "%{$search}%")
                              ->orWhere('consumer_number', 'LIKE', "%{$search}%")
                              ->orWhere('contact_number', 'LIKE', "%{$search}%");
                        });
                    }

                    if (request()->filled('status')) {
                        $query->where('status', request('status'));
                    }

                    if (request()->filled('consumer_flag')) {
                        $query->where('consumer_flag', request('consumer_flag'));
                    }

                    if (request()->filled('assign_person_id')) {
                        $query->where('assign_person_id', request('assign_person_id'));
                    }
                })
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $html = '<td>';
                    $html .= '<a  data-id="'.$row->id.'" class="view avatar bg-light-info p-50 m-0 text-info" data-bs-toggle="tooltip" data-placement="left" title="View"><i class="fa fa-eye"></i></a>';
                    $html .= ' <a data-id="'.$row->id.'" href="javascript:void(0);" class="avatar bg-light-danger p-50 m-0 delete" data-bs-toggle="tooltip" data-placement="left" title="Delete"><i class="fa fa-trash"></i></a>';
                    $html .= '</td>';

                    return $html;
                })

                ->editColumn('consumer_number', function ($row) {
                    // return '<span class="badge bg-light-info payment" data-id="' . $row->id . '"></span>';
                    return '<a href="'.route('inquiry-follow', $row->id).'"  data-id="'.$row->id.'" class=" badge bg-light-info p-50 m-0 text-info" target="_blank" data-bs-toggle="tooltip" data-placement="left" title="View">'.$row->consumer_number.'</a>';
                })
                ->editColumn('consumer_name', function ($row) {
                    if ($row->consumer_flag == 'old') {
                        return '<span class="badge bg-light-warning">Old</span>'.$row->consumer_name;
                    }

                    return '<span class="badge bg-light-success">New</span>'.$row->consumer_name;
                })

                ->editColumn('created_at', function ($row) {
                    return date('d-m-Y', strtotime($row->created_at));
                })
                ->editColumn('status', function ($row) {
                    $payStatus = getServiceStatusClass($row->status);

                    return '<span class="badge bg-light-'.$payStatus['class'].' w-100">'.$payStatus['status'].'</span>';
                })
                ->escapeColumns([])
                ->make(true);
        } else {
            $agentSalesPerson = AgentSalesPerson::orderBy('name')->get();
            return view('admin.inquiry.inquiry_list', compact('agentSalesPerson'));
        }
    }

    public function create()
    {
        return view('inquiry_form');
    }

    public function store(Request $request)
    {
        $consumerFlag = $request->consumer_flag ?? 'new';

        if ($consumerFlag == 'old') {
            $rules = [
                'consumer_flag' => 'required|in:new,old',
                'consumer_name' => 'required|string|max:255',
                'consumer_number' => 'required|string|max:255',
                'contact_number' => 'required|digits:10',
                'problem' => 'required|string',
                'invoice_date' => 'required|date_format:d-m-Y',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ];
            $messages = [
                'consumer_name.required' => 'Enter Consumer Name',
                'consumer_number.required' => 'Enter Consumer Number',
                'contact_number.required' => 'Enter Contact Number',
                'contact_number.digits' => 'Contact Number must be 10 digits',
                'problem.required' => 'Enter Problem',
                'image.image' => 'Uploaded file must be an image.',
                'image.mimes' => 'Only image files (jpeg, png, jpg, gif, svg) are allowed.',
                'image.max' => 'Image size must not exceed 2MB.',
            ];
        } else {
            $rules = [
                'consumer_flag' => 'required|in:new,old',
                'consumer_name' => 'required|string|max:255',
                'consumer_number' => 'required|numeric',
                'contact_number' => 'required|digits:10',
                'problem' => 'required|string',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ];
            $messages = [
                'consumer_name.required' => 'Enter Consumer Name',
                'consumer_number.required' => 'Enter Consumer Number',
                'consumer_number.numeric' => 'Enter valid Consumer Number',
                'contact_number.required' => 'Enter Contact Number',
                'contact_number.digits' => 'Contact Number must be 10 digits',
                'problem.required' => 'Enter Problem',
                'image.image' => 'Uploaded file must be an image.',
                'image.mimes' => 'Only image files (jpeg, png, jpg, gif, svg) are allowed.',
                'image.max' => 'Image size must not exceed 2MB.',
            ];
        }

        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()) {
            $response = ['status_code' => 201, 'message' => 'Please Input Proper Data !!', 'errors' => $validator->errors()];

            return response()->json($response);
        }

        DB::beginTransaction();
        try {
            if ($consumerFlag == 'old') {
                // Old consumer - manual entry, no SalesMaster lookup
                $consumerNumber = $request->consumer_number;
                $inquiries = Inquiry::where('consumer_number', $consumerNumber)->whereDate('created_at', Carbon::today())->count();
                // if ($inquiries < 2) {
                    $inquiry = new Inquiry();
                    $inquiry->consumer_flag = 'old';
                    $inquiry->consumer_name = $request->consumer_name;
                    $inquiry->consumer_number = $consumerNumber;
                    $inquiry->contact_number = $request->contact_number;
                    $inquiry->problem = $request->problem;
                    $inquiry->assign_person_id = $request->assign_person_id;
                    $invoiceDate = Carbon::createFromFormat('d-m-Y', $request->invoice_date);
                    $inquiry->invoice_date = $invoiceDate->format('Y-m-d');
                    $inquiry->warranty_status = $invoiceDate->copy()->addYears(5)->gte(Carbon::today()) ? 'in_warranty' : 'out_of_warranty';
                    if ($request->hasfile('image')) {
                        $PhotosDir = 'upload/inquiry/';
                        if (! file_exists($PhotosDir)) {
                            mkdir($PhotosDir, 0777, true);
                        }
                        $file = $request->file('image');
                        $filename = $request->title.'-'.time().rand().'.webp';
                        $file->move('upload/inquiry/', $filename);
                        $inquiry->image = $filename;
                    }
                    $result = $inquiry->save();

                    if (! is_null($result)) {
                        DB::commit();
                        $response = ['data' => route('inquiry-list'), 'status_code' => 200, 'message' => 'Successfully Sand Your Message.'];

                        return response()->json($response);
                    } else {
                        return response()->json(['status_code' => 403, 'message' => 'Something went wrong. Please try again.']);
                    }
                // } else {
                //     return response()->json(['status_code' => 403, 'message' => ' Your inquiry limit`s has been ended.']);
                // }
            } else {
                // New consumer - existing flow with SalesMaster
                $sales = SalesMaster::where('consumer_number', $request->consumer_number)->first();
                if (! is_null($sales)) {
                    $inquiries = Inquiry::where('consumer_number', $sales->consumer_number)->whereDate('created_at', Carbon::today())->count();
                    // if ($inquiries < 2) {
                        $inquiry = new Inquiry();
                        $inquiry->consumer_flag = 'new';
                        $inquiry->consumer_name = $request->consumer_name;
                        $inquiry->consumer_number = $request->consumer_number;
                        $inquiry->contact_number = $request->contact_number;
                        $inquiry->problem = $request->problem;
                        $inquiry->assign_person_id = $request->assign_person_id;
                        if ($sales->invoice_date) {
                            $inquiry->invoice_date = $sales->invoice_date;
                            $invoiceDate = Carbon::parse($sales->invoice_date);
                            $inquiry->warranty_status = $invoiceDate->copy()->addYears(5)->gte(Carbon::today()) ? 'in_warranty' : 'out_of_warranty';
                        }
                        if ($request->hasfile('image')) {
                            if ($inquiry->image) {
                                $path = 'upload/inquiry/'.$inquiry->image;
                                if (File::exists($path)) {
                                    unlink($path);
                                }
                            }
                            $PhotosDir = 'upload/inquiry/';
                            if (! file_exists($PhotosDir)) {
                                mkdir($PhotosDir, 0777, true);
                            }
                            $file = $request->file('image');
                            $filename = $request->title.'-'.time().rand().'.webp';
                            $file->move('upload/inquiry/', $filename);
                            $inquiry->image = $filename;
                        }
                        $result = $inquiry->save();

                        if (! is_null($result)) {
                            $followUp = new InquiryFollow();
                            $followUp->sales_master_id = $sales->id;
                            $followUp->remark = 'New added';
                            $followUp->status = 'new_service';
                            $followUp->follow_up_by = Auth::id();
                            $result = $followUp->save();

                            DB::commit();
                            $response = ['data' => route('inquiry-list'), 'status_code' => 200, 'message' => 'Successfully Sand Your Message.'];

                            return response()->json($response);
                        } else {
                            return response()->json(['status_code' => 403, 'message' => 'Something went wrong. Please try again.']);
                        }
                    // } else {
                    //     return response()->json(['status_code' => 403, 'message' => ' Your inquiry limit`s has been ended.']);
                    // }
                } else {
                    return response()->json(['status_code' => 403, 'message' => 'This consumer number not available.']);
                }
            }
        } catch (\Exception $e) {
            DB::rollback();
            $response = ['status_code' => 500, 'message' => 'Something went wrong. Please try again.'];

            return response()->json($response);
        }
    }

    public function show($id)
    {
        $inquiry = Inquiry::where('id', $id)->first();
        $data['html'] = view('admin.inquiry.model', compact('inquiry'))->render();

        return response()->json($data);
    }

    public function edit(Inquiry $inquiry)
    {
        //
    }

    public function follow($id)
    {
        $inquiry = Inquiry::where('id', $id)->first();

        if (is_null($inquiry)) {
            return abort(404);
        }

        $agentSalesPerson = AgentSalesPerson::orderBy('name')->get();

        if ($inquiry->consumer_flag == 'old') {
            $followUp = InquiryFollow::where('inquiry_id', $inquiry->id)->orderBy('id', 'desc')->get();
            $lastAssignPersonId = $followUp->first()->assign_person_id ?? $inquiry->assign_person_id;

            return view('admin.inquiry.inquiry_follow_old_customer', compact('inquiry', 'followUp', 'agentSalesPerson', 'lastAssignPersonId'));
        }

        $salesMaster = SalesMaster::with('district', 'subDivisionPDF', 'agentsalesperson', 'taluka', 'document', 'lead', 'installation', 'installation.panelwatt', 'installation.panelcompany', 'installation.paneltype', 'installation.installationPenals', 'installation.invater', 'installation.invater.company', 'installation.penalImage', 'installation.invaterImages', 'panel', 'panelwatt', 'inveter')->where('consumer_number', $inquiry->consumer_number)->first();

        if (! is_null($salesMaster)) {
            $payment = PaymetCollection::where('sales_master_id', $salesMaster->id)->get();
            $followUp = InquiryFollow::where('sales_master_id', $salesMaster->id)->orderBy('id', 'desc')->get();
            $lastAssignPersonId = $followUp->first()->assign_person_id ?? $inquiry->assign_person_id;

            return view('admin.inquiry.inquiry_follow', compact('salesMaster', 'payment', 'inquiry', 'followUp', 'agentSalesPerson', 'lastAssignPersonId'));
        } else {
            return abort(404);
        }
    }

    public function update(Request $request, Inquiry $inquiry)
    {
        //
    }

    public function destroy($id)
    {
        try {
            $inquiry = Inquiry::where('id', $id)->first();
            $path = 'upload/inquiry/'.$inquiry->image;
            if ($inquiry->image) {
                if (File::exists($path)) {
                    unlink($path);
                }
            }
            $inquiry->delete();
            $response = ['status' => true, 'message' => ' Deleted successfully.'];

            return response()->json($response);
        } catch (\Exception $e) {
            $response = ['status' => false, 'server_error' => 'Something went wrong. Please try again.'];

            return response()->json($response);
        }
    }

    public function dashboard()
    {
        // Total counts
        $totalComplaints = Inquiry::count();
        $todayComplaints = Inquiry::whereDate('created_at', Carbon::today())->count();
        $thisMonthComplaints = Inquiry::whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)->count();
        $newConsumer = Inquiry::where('consumer_flag', 'new')->count();
        $oldConsumer = Inquiry::where('consumer_flag', 'old')->count();

        // Status wise counts
        $statusWise = Inquiry::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')->get();

        $completedCount = Inquiry::whereIn('status', ['random_visit_today','completed'])->count();
        $randomVisitdTodayCount = Inquiry::where('status', 'random_visit_today')->count();
        $pendingCount = $totalComplaints - $completedCount;

        // Assign person wise counts
        $assignPersonWise = Inquiry::select('assign_person_id', DB::raw('count(*) as total'))
            ->whereNotNull('assign_person_id')
            ->groupBy('assign_person_id')
            ->with('assignPerson')
            ->get();

        // Monthly trend - last 6 months
        $monthlyTrend = Inquiry::select(
                DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"),
                DB::raw('count(*) as total')
            )
            ->where('created_at', '>=', Carbon::now()->subMonths(5)->startOfMonth())
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $monthLabels = [];
        $monthData = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $key = $date->format('Y-m');
            $monthLabels[] = $date->format('M Y');
            $found = $monthlyTrend->firstWhere('month', $key);
            $monthData[] = $found ? $found->total : 0;
        }

        // Status chart data
        $serviceStatus = serviceStatus();
        $statusLabels = [];
        $statusData = [];
        $statusColors = ['#82868b', '#00cfe8', '#ff9f43', '#ea5455', '#7367f0', '#28c76f'];
        foreach ($serviceStatus as $index => $s) {
            $statusLabels[] = $s['name'];
            $found = $statusWise->firstWhere('status', $s['id']);
            $statusData[] = $found ? $found->total : 0;
        }

        // Today's reminders
        $todayReminders = InquiryFollow::whereDate('reminder_date', Carbon::today())
            ->with('assignPerson', 'inquiry')
            ->orderBy('reminder_date')
            ->get();

        // For reminders linked via sales_master_id, find the related inquiry
        foreach ($todayReminders as $reminder) {
            if (!$reminder->inquiry && $reminder->sales_master_id) {
                $salesMaster = SalesMaster::find($reminder->sales_master_id);
                if ($salesMaster) {
                    $reminder->setRelation('inquiry', Inquiry::where('consumer_number', $salesMaster->consumer_number)->latest()->first());
                }
            }
        }

        return view('admin.inquiry.dashboard', compact(
            'totalComplaints', 'todayComplaints', 'thisMonthComplaints',
            'newConsumer', 'oldConsumer', 'completedCount', 'pendingCount',
            'statusWise', 'assignPersonWise', 'serviceStatus',
            'monthLabels', 'monthData',
            'statusLabels', 'statusData', 'statusColors',
            'todayReminders','randomVisitdTodayCount'
        ));
    }
}
