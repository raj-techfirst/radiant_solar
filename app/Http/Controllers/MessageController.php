<?php

namespace App\Http\Controllers;

use App\Models\CompanyProfile;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class MessageController extends Controller
{
    public function index()
    {
        // $hello = Message::with('company')->orderBy('id', 'ASC')->get();
        // dd($hello);
        if (request()->ajax()) {
            return DataTables::of(Message::with('company')->orderBy('id', 'ASC'))
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $html = '<td>';
                    $html .= '<a href="' . route('message.edit', $row->id) . '" class="avatar bg-light-info p-50 m-0" data-bs-toggle="tooltip" data-placement="left" title="Edit"><i class="fa fa-edit"></i></a>';
                    $html .= ' <a data-id="' . $row->id . '" href="javascript:void(0);" id="confirm-text" class="avatar bg-light-danger p-50 m-0 text-danger delete" data-bs-toggle="tooltip" data-placement="left" title="Delete"><i class="fa fa-trash"></i></a>';
                    $html .= '</td>';
                    return $html;
                })
                ->escapeColumns([])
                ->make(true);
        } else {
            return view('message.view_message');
        }
    }

    public function create()
    {
        return view('message.add_message');
    }

    public function store(Request $request)
    {
        if (!is_null($request->message_id)) {
            $name = 'require' . $request->message_id;
        } else {
            $name = 'required';
        }
        $validator = Validator::make($request->all(), [
            'welcome' => $name
        ], [
            'welcome.required' => 'Enter welcome Message',
            'welcome.unique' => 'The welcome Message has already been taken'
        ]);
        if ($validator->fails()) {
            $response = ['status' => false, 'message' => 'Please input proper data.', 'errors' => $validator->errors()];
            return response()->json($response);
        }
        DB::beginTransaction();
        try {
            if (!is_null($request->message_id)) {
                $message = Message::where('id', $request->message_id)->first();
                $response = ['data' => route('message.index'), 'status' => true, 'message' => ' Message updated successfully.'];
            } else {
                $message = new Message();
                $response = ['data' => route('message.index'), 'status' => true, 'message' => ' Message added successfully.'];
            }
            $companyProfile = CompanyProfile::where('user_id', Auth::id())->first();
            $message->company_profile_id = $companyProfile->id;

            $message->welcome = $request->welcome;
            $message->follow_up = $request->follow_up;
            $message->not_interested = $request->not_interested;
            $result = $message->save();
            DB::commit();
            if (!is_null($result)) {
                return response()->json($response);
            } else {
                $response = ['status' => false, 'server_error' => 'Something went wrong. Please try again.'];
                return response()->json($response);
            }
        } catch (\Exception $e) {
            dd($e);
            DB::rollback();
            $response = ['status' => false, 'server_error' => 'Something went wrong. Please try again.'];
            return response()->json($response);
        }
    }

    public function show(Message $message)
    {
        $companyProfile = CompanyProfile::where('user_id', Auth::id())->first();
        $message = Message::where('company_profile_id', $companyProfile->id)->get();
        // dd($message);
        return view('message.message',compact('message'));
    }

    public function edit($id)
    {
        $message = Message::where('id', $id)->first();
        return view('message.add_message', compact('message'));
    }

    public function update(Request $request, Message $message)
    {
        //
    }

    public function destroy($id)
    {
        try {
            $message = Message::where('id', $id)->first();
            $message->delete();
            $response = ['status' => true, 'message' => ' Message deleted successfully.'];
            return response()->json($response);
        } catch (\Exception $e) {
            $response = ['status' => false, 'server_error' => 'Something went wrong. Please try again.'];
            return response()->json($response);
        }
    }
}
