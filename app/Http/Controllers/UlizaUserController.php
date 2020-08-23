<?php

namespace App\Http\Controllers;

use App\User;
use DB;
use Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UlizaUserController extends Controller
{

    public function login(Request $request)
    {
        $user = User::where('email', $request->input('email'))->whereNotNull('email')->where('user_type_id', '>', 100)->first();
        if(!$user || $user->password != Hash::make($request->input('password'))){
            session(['toast_error' => 1, 'toast_message' => 'These credentials do not match our records.']);
            return back();
        }

        Auth::login($user);
        return redirect('uliza-review');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $users = User::where('user_type_id', '>', 100)->with(['twg', 'user_type'])->get();
        return view('uliza.tables.users', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $user_types = DB::table('user_types')->where('id', '>', 100)->get();
        $twgs = DB::table('uliza_twgs')->get();        
        return view('uliza.forms.user', compact('twgs', 'user_types'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $user = new User;
        $user->fill($request->all());
        $user->password = 'password';
        $user->save();
        session(['toast_message' => 'The user has been created']);
        return back();
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function show(User $user)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function edit(User $user)
    {
        $user_types = DB::table('user_types')->where('id', '>', 100)->get();
        $twgs = DB::table('uliza_twgs')->get();        
        return view('uliza.forms.user', compact('twgs', 'user_types', 'user'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $user)
    {
        $user->fill($request->all());
        $user->password = 'password';
        $user->save();
        session(['toast_message' => 'The user has been updated']);
        return redirect('uliza-user');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        //
    }
}
