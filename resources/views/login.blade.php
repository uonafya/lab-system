@extends('layouts.auth')

@section('content')
<div class="row">
        <div class="col-md-12">
            <div class="text-center m-b-md">
                <img src="{{ asset('img/nascoplogo.png') }}">
            </div>
            <div class="hpanel">
                <div class="panel-body">
                    <form action="#" id="loginForm">
                        <div class="form-group">
                            <label class="control-label" for="username">Username</label>
                            <input type="text" placeholder="Username" title="Please enter you username" required="" value="" name="username" id="username" class="form-control">
                        </div>
                        <div class="form-group">
                            <label class="control-label" for="password">Password</label>
                            <input type="password" title="Please enter your password" placeholder="Password" required="" value="" name="password" id="password" class="form-control">
                        </div>
                        <button class="btn btn-primary btn-block">Login</button>
                    </form>
                    <div class="text-center m-b-md facility-login">
                        <a href="#"><p>Click <strong class="font-extra-bold font-uppercase">here</strong> for facility login</p></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection