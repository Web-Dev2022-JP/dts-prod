<?php

namespace App\Http\Controllers;

use App\Models\Log;
use App\Models\User;
use App\Events\NotifyEvent;
use App\Models\RequestedDocument;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AdministratorController extends Controller
{
    public function dashboard(){
        return view('admin.components.contents.home')->with(['logs'=>$this->LogsInfo()]);
    }

    // logs return
    public function LogsInfo(){
        // Retrieve the logged-in user's ID
        $userId = Auth::user()->id;


        // Retrieve logs for the logged-in user
        $logs = Log::where('requested_document_id', $userId)->get();

        // Retrieve the user's department based on their ID
        // $userDepartment = User::where('id', $logs['requested_to'])->value('department');

        // Format the created_at timestamps as "year-month-day"
        $logs = $logs->map(function ($log) {
            $log->formatted_created_at = $log->created_at->format('Y-m-d');
            $log->formatted_time = date('h:i A', strtotime($log->created_at)); // Format the timestamp
            return $log;
        });

        // Add the user's department to each log entry im using roles 
        $logs = $logs->map(function ($log){
            $log->user_department = User::where('id', $log->requested_by)->value('department');;
            return $log;
        });

        return $logs;
    }

    // departments return
    public function availableDepartments(){
        $firstDepartment = User::where('role', 1)
                            ->orderBy('id', 'asc') // You can adjust the ordering as needed
                            ->first();

        $departments = User::where('department', '<>', $firstDepartment->department)
                        ->pluck('department')
                        ->toArray();

        // Insert the first department at the beginning of the array
        array_unshift($departments, $firstDepartment->department);

        return $departments;
    }

    // manage Account
    public function accounts(Request $request){
        // dd($request);
        $id = $request->input('id');
        $nP = $request->input('p');
        $req = $request->input('req');
        $user = null; // Initialize $user outside the switch statement
        $status = null;
        $message = null;
        switch ($req) {
            case 'archived':
                // Find the user by ID
                $user = User::find($id);
                // Check in logs if this user has pending documents
                $logsuser = Log::where(function ($query) use ($id) {
                    $query->where('forwarded_to', $id)
                        ->orWhere('destination', $id);
                })
                ->whereIn('status', ['forwarded', 'pending'])
                ->latest('created_at') // Order by the latest created_at timestamp
                ->first();

                // Check in logs if this user has pending documents
                $requestedsuser = RequestedDocument::where(function ($query) use ($id) {
                    $query->where('requestor_user', $id)
                        ->orWhere('forwarded_to', $id);
                })
                ->where('status', 'pending')
                ->latest('created_at') // Order by the latest created_at timestamp
                ->first();

                if($logsuser || $requestedsuser){
                     // User has the latest pending document
                     return response()->json(['status'=>'error','message'=>"This user's cant be archive."],200);
                }
                // Mark the user as unverified (set email_verified_at to null)
                // $user->email_verified_at = null;
                $user->status = 'archived';
                $user->password = Hash::make('archived');
                $status = 'success';
                $message = 'Account is successfully set to archived!';
                break;
            case 'activate':
                // Find the user by ID
                $user = User::find($id);
                // Mark the user as unverified (set email_verified_at to null)
                $user->email_verified_at = Carbon::now();
                $user->status = 'active';
                // $user->password = Hash::make('password');
                $status = 'success';
                $message = 'Account is successfully activated, user can login to the system!';
                break;
            case 'reset-password':
                // Find the user by ID
                $user = User::find($id);
                // Mark the user as unverified (set email_verified_at to null)
                $user->email_verified_at = null;
                $user->status = 'deactivated';
                $user->password = Hash::make($nP);//default its password
                $status = 'success';
                $message = 'Account is successfully reset the password, user can activate its email to login!';
                break;
            
            default:
                # code...
                break;
        }
        if ($user) {
            $user->save();
            event(new NotifyEvent(["user_id"=>$id,"refresh"=>true]));
        }
        return response()->json(['status'=>$status,'message'=>$message],200);
    }

    //history logs
    public function history(){
        $logs = DB::table('logs')
        ->join('requested_documents', 'logs.requested_document_id', '=', 'requested_documents.id')
        ->join('users', 'requested_documents.requestor_user', '=' ,'users.id')
        ->select('logs.*', 
            'requested_documents.requestor_user', 'requested_documents.status', 'requested_documents.purpose',
            'users.name'
            )
            ->where('users.id', Auth::user()->id)
        ->get();


        // dd($logs);
        return view('admin.components.contents.history')->with(['history'=>$logs]);
    }


    public function reportsPdf(){
        $trackingNos = DB::table('requested_documents')->select('trk_id','id')->get();
        $offices = DB::table('offices')->select('office_name', 'id')->get();
        $users = DB::table('users')->select('name','id')->where('status','!=','deactivated')->get();

        $groupedData = [
            'trackingNos' => $trackingNos,
            'offices' => $offices,
            'users' => $users,
        ];
        
        // dd($groupedData);
        return view('admin.components.contents.report',['creds'=>$groupedData]);
    }
}
