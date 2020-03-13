@extends('layouts.master')

@component('/forms/css')
    
@endcomponent

@section('custom_css')
    <style type="text/css">
        .hpanel {
            margin-bottom: 4px;
        }
        .hpanel.panel-heading {
            padding-bottom: 2px;
            padding-top: 4px;
        }
    </style>
@endsection

@section('content')
    <div class="content">
        <div>

            @if(isset($user))
            <form action="{{ url('/user/' . $user->id) }}" class="form-horizontal" method="POST">
                @method('PUT')
            @else
            <form action="{{ url('/user') }}" class="form-horizontal" method="POST">
            @endif
                @csrf
                <div class="row">
                    <div class="col-lg-12">
                        <div class="hpanel">
                            <div class="panel-heading" style="padding-bottom: 2px;padding-top: 4px;">
                                <center>Account Information</center>
                            </div>
                            <div class="panel-body" style="padding-bottom: 6px;">
                                <div class="form-group">
                                    <label class="col-sm-4 control-label">Account Type</label>
                                    <div class="col-sm-8">
                                        <select class="form-control" required name="user_type" id="user_type">
                                        @if(!isset($user))
                                            <option value="" selected disabled>Select Account Type</option>
                                        @endif
                                        @forelse ($accounts as $account)
                                            @if(isset($user) && $account->id == $user->user_type_id)
                                                <option value="{{ $account->id }}" selected>{{ $account->user_type }}</option>
                                            @else
                                                <option value="{{ $account->id }}">{{ $account->user_type }}</option>
                                            @endif
                                        @empty
                                            <option value="" disabled="true">No Account types available</option>
                                        @endforelse
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group" id="partners">
                                    <label class="col-sm-4 control-label">Partner</label>
                                    <div class="col-sm-8">
                                        <select class="form-control" name="facility_id" id="partner_select">
                                            <option value="" selected disabled>Select Partner</option>
                                        @forelse ($partners as $partner)
                                            <option value="{{ $partner->id }}">{{ $partner->name }}</option>
                                        @empty
                                            <option value="" disabled="true">No Partners available</option>
                                        @endforelse
                                        </select>
                                    </div>
                                </div>


                                <div class="form-group">
                                    <label class="col-sm-4 control-label">Email</label>
                                    <div class="col-sm-8">
                                        <input class="form-control" name="email" id="email" type="email" value="{{ $user->email ?? '' }}">
                                    </div>
                                </div>
                                <div @isset($user) style="display: none;" @endisset>
                                    <div class="form-group">
                                        <label class="col-sm-4 control-label">Password</label>
                                        <div class="col-sm-8">
                                            <input class="form-control" name="password" id="password" type="password" value="">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-4 control-label">Confirm Password</label>
                                        <div class="col-sm-8">
                                            <input class="form-control" name="confirm-password" id="confirm-password" type="password" value="">
                                        </div>
                                    </div>
                                </div>
                                
                            </div>
                        </div>
                        <div class="hpanel">
                            <div class="panel-heading" style="padding-bottom: 2px;padding-top: 4px;">
                                <center>Personal Information</center>
                            </div>
                            <div class="panel-body" style="padding-bottom: 6px;">
                                <div class="form-group">
                                    <label class="col-sm-4 control-label">Surname</label>
                                    <div class="col-sm-8">
                                        <input class="form-control" name="surname" id="surname" type="text" value="{{ $user->surname ?? '' }}">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-4 control-label">Other Name(s)</label>
                                    <div class="col-sm-8">
                                        <input class="form-control" name="oname" id="oname" type="text" value="{{ $user->oname ?? '' }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="hpanel">
                            <div class="panel-heading" style="padding-bottom: 2px;padding-top: 4px;">
                                <center>Contact Details</center>
                            </div>
                            <div class="panel-body" style="padding-bottom: 6px;">
                                <div class="form-group">
                                    <label class="col-sm-4 control-label">Telephone No.</label>
                                    <div class="col-sm-8">
                                        <div class="input-group">
                                            <span class="input-group-addon"><i class="fa fa-phone"></i></span>
                                            <input class="form-control" name="telephone" id="telephone" type="text" value="{{ $user->telephone ?? '' }}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="hr-line-dashed"></div>
                        <div class="form-group">
                            <center>
                            @if(isset($user))
                                <div class="col-sm-10 col-sm-offset-1">
                                    <button class="btn btn-success submit" type="submit" name="submit_type">Update User</button>
                                </div>
                            @else
                                <div class="col-sm-10 col-sm-offset-1">
                                    <button class="btn btn-success submit" type="submit" name="submit_type" value="release">Save User</button>
                                    <button class="btn btn-primary submit" type="submit" name="submit_type" value="add">Save & Add User</button>
                                    <button class="btn btn-danger" type="reset" formnovalidate name="submit_type" value="cancel">Reset</button>
                                </div>
                            @endif
                            </center>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')

    @component('/forms/scripts')
        @slot('js_scripts')
            
        @endslot

        @slot('val_rules')
            ,
            rules:{
                confirm-password: {
                    equalTo: '#password'
                },   
            }            
        @endslot

    @endcomponent


    <script type="text/javascript">
        $(document).ready(function(){
            $("#partners").hide();

            $("#user_type").change(function(){
                val = $(this).val();
                if(val == 10){
                    $("#partners").show();
                    $('#partner_select').attr("required", "required");
                    $('#partner_select').removeAttr("disabled");  
                } 
                else{
                    $("#partners").hide();
                    $('#partner_select').removeAttr("required");                    
                }
            });

            /*$(".submit").click(function(e){
                password = $("#password").val();
                confirm = $("#confirm-password").val();
                if (password !== confirm) {
                    e.preventDefault();
                    set_warning("Passwords do not match");
                    $("#confirm-password").val("");
                    $("#confirm-password").focus();
                }
            });*/
        });
    </script>
@endsection
