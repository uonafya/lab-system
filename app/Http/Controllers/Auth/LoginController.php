<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

use App\User;
use App\Batch;
use App\Viralbatch;

use DB;

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
    protected $redirectTo = '/home';

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
        $facilities = DB::table('facilitys')->select('id', 'name')->get();
        return view('auth.fac-login', ['facilities' => $facilities, 'login_error' => session()->pull('login_error')]);
    }


    public function facility_login(Request $request)
    {
        $facility_id = $request->input('facility_id');
        $batch_no = $request->input('batch_no');

        $batch = Batch::find($batch_no);

        if($batch){
            if($batch->facility_id == $facility_id){
                $user = User::where(['facility_id' => $facility_id, 'user_type_id' => 5])->get()->first();
                
                if($user){
                    Auth::login($user);
                    return redirect('/sample/create');                    
                }
            }
        }

        $batch = Viralbatch::find($batch_no);

        if($batch){
            if($batch->facility_id == $facility_id){
                $user = User::where(['facility_id' => $facility_id, 'user_type_id' => 5])->get()->first();

                if($user){
                    Auth::login($user);
                    return redirect('/viralsample/create');                    
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
}

