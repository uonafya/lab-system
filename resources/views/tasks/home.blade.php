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
                    $currentmonth = date('m');
                    $prevmonth = date('m')-1;
                    $year = date('Y');
                    $prevyear = $year;
                    if ($currentmonth == 1) {
                        $prevmonth = 12;
                        $prevyear -= 1;
                    }
                @endphp
                
                <div class="panel-body" style="padding: 20px;box-shadow: none; border-radius: 0px;">
                    @if($data->covidconsumption == 0)
                        <div class="alert alert-danger spacing bottom">
                            <strong><a href="{{ url('covidkits') }}">Click to Submit Last Week`s COVID Consumptions</a></strong>
                            <p style="margin-left: 3em;"><font color="#CCCCCC" style="color: black;">Log on last week`s consumption {{ $data->time->week_start }} - {{ $data->time->week_end }}</font></p>
                        </div>
                    @else 
                        <div class="alert alert-success spacing bottom">
                            <strong><a href="#" style="color: black;">Last week`s ({{ $data->time->week_start }} - {{ $data->time->week_end }}) COVID consumptions Submitted</a></strong>
                        </div>
                    @endif
                    <!-- Kit and kits consumption -->
                    @if ($data->submittedkits == 0)
                        <div class="alert alert-warning spacing  bottom">
                        	<strong><a href="{{ url('kitsdeliveries') }}">Click to Add Kit Deliveries for  [  Quarter {{ Session('quarter') }} ({{ Session('range') }}), {{ date('Y') }}]</a></strong>
                        	<p style="margin-left: 3em;">
                                <font color="#CCCCCC">
                                    @if (($data->kits->eidtaqkits > 0) && ($data->kits->vltaqkits > 0))
                                        RECEIVED ABBOTT (EID & VL) KITS NEED TO BE ENTERED BEFORE REPORT SUBMISSION
                                    @elseif (($data->kits->eidabkits > 0) && ($data->kits->vlabkits > 0))
                                        RECEIVED ROCHE (EID & VL) KITS NEED TO BE ENTERED BEFORE REPORT SUBMISSION
                                    @elseif (($data->kits->eidtaqkits == 0) && ($data->kits->vltaqkits == 0) && ($data->kits->eidabkits == 0) && ($data->kits->vlabkits == 0))
                                        RECEIVED ABBOTT & ROCHE (EID & VL) KITS NEED TO BE ENTERED BEFORE REPORT SUBMISSION
                                    @endif
                                </font>
                            </p>
                            <p style="margin-left: 3em;">
                                @if (($data->kits->eidtaqkits > 0) && ($data->kits->vltaqkits > 0))
                                    <a href="{{ url('kitsdeliveries/abbott') }}">Click here to submit a NULL ABBOTT (EID & VL) report</a>
                                @elseif (($data->kits->eidabkits > 0) && ($data->kits->vlabkits > 0))
                                    <a href="{{ url('kitsdeliveries/roche') }}">Click here to submit a NULL ROCHE (EID & VL) report</a>
                                @elseif (($data->kits->eidtaqkits == 0) && ($data->kits->vltaqkits == 0) && ($data->kits->eidabkits == 0) && ($data->kits->vlabkits == 0))
                                    <a href="{{ url('kitsdeliveries/all') }}">Click here to submit a NULL ABBOTT & ROCHE (EID & VL) report</a><br>
                                    <a href="{{ url('kitsdeliveries/abbott') }}">Click here to submit a NULL ABBOTT (EID & VL) report</a><br>
                                    <a href="{{ url('kitsdeliveries/roche') }}">Click here to submit a NULL ROCHE (EID & VL) report</a>
                                @endif
                            </p>
                        </div>
                    @elseif ($data->submittedkits == 1)  
                        @if ((($data->consumption->eidtaqconsumption > 0) && ($data->consumption->vltaqconsumption > 0)) && (($data->consumption->eidabconsumption > 0) && ($data->consumption->vlabconsumption > 0)))
                            <div class="alert alert-success spacing bottom">
                                <strong><a href="#">{{ date("F", mktime(null, null, null, $prevmonth)) }}, {{ $prevyear }} Consumption Report Submitted</a></strong>
                            </div>
                        @else
                        <div class="alert alert-warning spacing bottom">
                            <strong><a href="{{ url('consumption') }}">Click to Submit Consumption Report for  [ {{ date("F", mktime(null, null, null, $prevmonth)) }}, {{ $prevyear }}]</a></strong>
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
                        </div>
                        @endif
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
                            <strong><a href="#">{{ date("F", mktime(null, null, null, $prevmonth)) }}, {{ $prevyear }} Lab Performance Log ( Tracker ) Submitted</a></strong>
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
                            <strong><a href="#">{{ date("F", mktime(null, null, null, $prevmonth)) }}, {{ $prevyear }} Lab Monthly Equipment Log ( Tracker ) Submitted</a></strong>
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