@extends('layouts.tasks')

@section('css_scripts')

@endsection

@section('custom_css')
	<style type="text/css">
		.hpanel .panel-body .spacing {
			margin-bottom: 1em;
		}
	</style>
@endsection

@section('content')
<div class="row">
        <div class="col-md-12">
            <div class="hpanel" style="margin-top: 1em;margin-right: 20%;">
            	<div class="alert alert-danger">
		                <center><i class="fa fa-bolt"></i> Please note that you CANNOT access the main system until the below pending tasks have been completed.</center>
		            </div>
                <div class="panel-body" style="padding: 20px;box-shadow: none; border-radius: 0px;">
                    <div class="alert alert-warning spacing">
                    	<strong><a href="">Click to Add Kit Deliveries for  [  Quarter ]</a></strong>
                    	<p style="margin-left: 3em;"><font color="#CCCCCC">This is the other platform</font></p>
                    </div>
                    <div class="alert alert-warning spacing">
                    	<strong><a href="">Click to Add Kit Deliveries for  [  Quarter ]</a></strong>
                    	<p style="margin-left: 3em;"><font color="#CCCCCC">This is the other platform</font></p>
                    </div>
                    <div class="alert alert-warning spacing">
                    	<strong><a href="">Click to Add Kit Deliveries for  [  Quarter ]</a></strong>
                    	<p style="margin-left: 3em;"><font color="#CCCCCC">This is the other platform</font></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')

@endsection