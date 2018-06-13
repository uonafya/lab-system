@extends('layouts.tasks')

@section('css_scripts')

@endsection

@section('custom_css')
	<style type="text/css">
		.hpanel .panel-body .spacing {
			margin-bottom: 1em;
		}
        .hpanel .panel-body .bottom {
            border-bottom: 1px solid #eaeaea;
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

                @php
                    $prevmonth = date('m')-1;
                @endphp
                
                <div class="panel-body" style="padding: 20px;box-shadow: none; border-radius: 0px;">
                    <!-- Kit and kits consumption -->
                    @if ($data->submittedkits == 0)
                        <div class="alert alert-warning spacing  bottom">
                        	<strong><a href="{{ url('kitsdeliveries') }}">Click to Add Kit Deliveries for  [  Quarter {{ Session('quarter') }} ({{ Session('range') }}), {{ date('Y') }}]</a></strong>
                        	<p style="margin-left: 3em;">
                                <font color="#CCCCCC">
                                    @if (($data->kits->eidtaqkits > 0) && ($data->kits->vltaqkits > 0))
                                        RECEIVED ABBOTT EID & VL KITS NEED TO BE ENTERED BEFORE REPORT SUBMISSION
                                    @elseif (($data->kits->eidabkits > 0) && ($data->kits->vlabkits > 0))
                                        RECEIVED ROCHE EID & VL KITS NEED TO BE ENTERED BEFORE REPORT SUBMISSION
                                    @elseif (($data->kits->eidtaqkits == 0) && ($data->kits->vltaqkits == 0) && ($data->kits->eidabkits == 0) && ($data->kits->vlabkits == 0))
                                        RECEIVED ABBOTT & ROCHE EID & VL KITS NEED TO BE ENTERED BEFORE REPORT SUBMISSION
                                    @endif
                                </font>
                            </p>
                        </div>
                    @elseif ($data->submittedkits == 1)
                        <div class="alert alert-warning spacing bottom">
                            @if ((($data->consumption->eidtaqconsumption > 0) && ($data->consumption->vltaqconsumption > 0)) && (($data->consumption->eidabconsumption > 0) && ($data->consumption->vlabconsumption > 0)))
                                <div class="alert alert-success spacing bottom">
                                    <strong><a href="#">{{ date("F", mktime(null, null, null, $prevmonth)) }}, {{ date('Y') }} Consumption Report Submitted</a></strong>
                                </div>
                            @else
                                <strong><a href="{{ url('consumption') }}">Click to Submit Consumption Report for  [ {{ date("F", mktime(null, null, null, date('m'))) }}, {{ date('Y') }}]</a></strong>
                                <p style="margin-left: 3em;">
                                    <font color="#CCCCCC">
                                        @if ((($data->consumption->eidtaqconsumption > 0) && ($data->consumption->vltaqconsumption > 0)) && (($data->consumption->eidabconsumption == 0) && ($data->consumption->vlabconsumption == 0)))
                                            ABBOTT
                                        @elseif ((($data->consumption->eidabconsumption > 0) && ($data->consumption->vlabconsumption > 0)) && (($data->consumption->eidtaqconsumption == 0) && ($data->consumption->vltaqconsumption == 0)))
                                            TAQMAN
                                        @elseif (($data->consumption->eidtaqconsumption == 0) && ($data->consumption->vltaqconsumption == 0) && ($data->consumption->eidabconsumption == 0) && ($data->consumption->vlabconsumption == 0))
                                            TAQMAN & ABBOTT
                                        @endif
                                    </font>
                                </p>
                                <div class="alert alert-default bottom">
                                    <strong><a href="{{ url('consumption/report') }}"><font color="#CCCCCC">Kits Consumption Reporting Guide</font></a></strong>
                                </div>
                            @endif
                        </div>
                    @endif
                    <!-- Kit and kits consumption -->

                    <!-- Lab performance Report -->
                    @if ($data->performance == 0)
                        <div class="alert alert-warning spacing bottom">
                        	<strong><a href="{{ url('performancelog') }}">Click to Submit Monthly Lab Performance Log ( Tracker )</a></strong>
                        	<p style="margin-left: 3em;"><font color="#CCCCCC">Log on any Back logs and Reasons for the previous month</font></p>
                        </div>
                    @else 
                        <div class="alert alert-success spacing bottom">
                            <strong><a href="#">{{ date("F", mktime(null, null, null, $prevmonth)) }}, {{ date('Y') }} Lab Performance Log ( Tracker ) Submitted</a></strong>
                        </div>
                    @endif
                    <!-- Lab performance Report -->

                    <!-- Lab equipment Report -->
                    @if ($data->equipment == 0)
                        <div class="alert alert-warning spacing bottom">
                            <strong><a href="{{ url('equipmentlog') }}">Click to Submit Monthly Equipment Log ( Tracker )</a></strong>
                            <p style="margin-left: 3em;"><font color="#CCCCCC"> Log on any Equipment Breakdown in Previous Month</font></p>
                        </div>
                    @else
                        <div class="alert alert-success spacing bottom">
                            <strong><a href="#">{{ date("F", mktime(null, null, null, $prevmonth)) }}, {{ date('Y') }} Lab Monthly Equipment Log ( Tracker ) Submitted</a></strong>
                        </div>
                    @endif
                    <!-- Lab equipment Report -->
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')

@endsection