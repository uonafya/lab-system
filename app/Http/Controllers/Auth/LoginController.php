<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

use App\User;
use App\Batch;
use App\Viralbatch;
use App\LabEquipmentTracker;
use App\LabPerformanceTracker;
use App\Taqmanprocurement;
use App\Abbotprocurement;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    // protected $redirectTo = '/sample/create';
    // protected $redirectTo = '/home';

    protected function redirectTo()
    {
        return $this->set_session();
    }

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function fac_login()
    {
        return view('auth.fac-login', ['login_error' => session()->pull('login_error')]);
    }


    public function facility_login(Request $request)
    {
        $facility_id = $request->input('facility_id');
        $batch_no = $request->input('batch_no');

        $batch = Batch::find($batch_no);

        if($batch){
            if($batch->outdated()) return $this->failed_facility_login(); 
            if($batch->facility_id == $facility_id){
                $user = User::where(['facility_id' => $facility_id, 'user_type_id' => 5])->get()->first();
                
                if($user){
                    Auth::login($user);
                    return redirect($this->set_session(true));                    
                }
            }
        }

        $batch = Viralbatch::find($batch_no);

        if($batch){
            if($batch->outdated()) return $this->failed_facility_login(); 
            if($batch->facility_id == $facility_id){
                $user = User::where(['facility_id' => $facility_id, 'user_type_id' => 5])->get()->first();

                if($user){
                    Auth::login($user);
                    return redirect($this->set_session(true));                    
                }

                // if(Auth::attempt(['email' => $user->email, 'password' => 'password'])){
                //     return redirect('/viralsample/create');
                // }

            }
        }
        return $this->failed_facility_login(); 
    }

    public function failed_facility_login()
    {
        session(['login_error' => 'There was no batch for that facility']);
        return redirect('/login/facility');
    }

    private function set_session($facility = false)
    {
        // Checking for pending tasks if user is Lab user before redirecting to the respective page
        if (Auth()->user()->user_type_id)
        {
            $month = date('m')-1;
            $equipment = LabEquipmentTracker::where('year', date('Y'))->where('month', $month)->count();
            $performance = LabPerformanceTracker::where('year', date('Y'))->where('month', $month)->count();

            $labtracker = 0;
            if ($performance > 0 &&  $equipment > 0) {
                $labtracker=1;
                session(['LabTracker'=>true, 'EquipTracker'=>true]);
            }

            $abbot = \App\Lab::select('abbott')->where('id', Auth()->user()->lab_id)->get();

            $testype = [1,2];
            $taqman = [];
            $abbot = [];
            
            foreach ($testype as $key => $value) {
                if ($abbot == 1) {//Check for both abbot and taqman
                    $abbot[] = Abbotprocurement::where('month', $month)->where('year', date('Y'))->where('lab_id', Auth()->user()->lab_id)->where('testtype', $value)->count();
                    session(['abbotkits' => true]);
                }
                                   
                $taqman[] = Taqmanprocurement::where('month', $month)->where('year', date('Y'))->where('lab_id', Auth()->user()->lab_id)->where('testtype', $value)->count();
                session(['taqmankits' => true]);
                
            }

            if ($abbot == 1) {
                if ( ($taqman[0] > 0 && $taqman[1] >0 ) && ($abbot[0] > 0 && $abbot[1]>0) )//..if both taqman and abbott have been submitted; set $submittedstatus > 0
                    $submittedstatus = 1;

                if ( ($taqman[0] > 0 && $taqman[1] >0) && ($abbot[0] == 0 || $abbot[1]==0 ) )//..if only taqman has been submitted and not abbott; set $submittedstatus = 0; and only show the abbott link **********
                    $submittedstatus = 0;
                if ( ($taqman[0] == 0 || $taqman[1] ==0) && ($abbot[0] > 0 || $abbot[1]>0) )//..if only abbott has been submitted and not taqman; set $submittedstatus = 0; and only show the taqman link **********
                    $submittedstatus = 0;

                if ( ($taqman[0] == 0 && $taqman[1] ==0) && ($abbot[0] > 0 || $abbot[1]>0) )//..if only abbott has been submitted and not taqman; set $submittedstatus = 0; and only show the taqman link **********
                    $submittedstatus = 0;

                if ( ($taqman[0] == 0 || $taqman[1] ==0 ) && ($abbot[0] == 0  || $abbot[1]==0 ) )//..if none has been submitted; set $submittedstatus = 0; and only show the main link that requests both platforms to be submitted ***but also check whether lab has abbott machine*****
                    $submittedstatus = 0;
            } else {
                $submittedstatus = 1;
                if ($taqman[0] == 0 || $taqman[1] ==0)
                    $submittedstatus = 0;
            }
            session(['pendingTasks' => true]);            
            if ($submittedstatus == 0 OR $labtracker ==0)  
                return '/pending';
        }
        // Checking for pending tasks if user is Lab user before redirecting to the respective page

        $batch = Batch::editing()->withCount(['sample'])->get()->first();
        if($batch){
            if($batch->sample_count > 9){
                $batch->full_batch();
            }
            else{
                $fac = \App\Facility::find($batch->id);
                session(['batch' => $batch, 'facility_name' => $fac->name]);
                session(['toast_message' => "The batch {$batch->id} is still awaiting release. You can add more samples or release it."]);
                return '/sample/create';
            }
        }

        $viralbatch = Viralbatch::editing()->withCount(['sample'])->get()->first();
        if($viralbatch){
            if($viralbatch->sample_count > 9){
                $viralbatch->full_batch();
            }
            else{
                $fac = \App\Facility::find($viralbatch->id);
                session(['viral_batch' => $viralbatch, 'viral_facility_name' => $fac->name]);
                session(['toast_message' => "The batch {$viralbatch->id} is still awaiting release. You can add more samples or release it."]);
                return '/viralsample/create';
            }
        }
        if($facility) return '/sample/create';
        return '/home';        
    }
}

