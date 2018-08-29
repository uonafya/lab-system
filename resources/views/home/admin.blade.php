@extends('layouts.master')

@section('content')
<style type="text/css">
	.list-group {
		margin-top: 10px;
    	margin-bottom: 10px;
	}
	.list-group-item {
		margin-bottom: 4px;
    	margin-top: 4px;
	}
</style>
@php
	// $userstoday = $data->users->today ?? 0;
	// $usersanotherday = $data->users->another_day ?? 0;
	// $totalusers = $userstoday + $usersanotherday;
	// $userspercentage = ($userstoday == 0) ? 0 : round(($userstoday/$totalusers)*100, 2);
@endphp
<div class="p-lg">
	<div class="content animate-panel" data-child="hpanel">
		<!-- EID Widgets -->
	    <div class="row">
		    <!-- <div class="col-lg-4">
                <div class="hpanel stats">
                    <div class="panel-body h-200">
                        <div class="stats-title pull-left">
                            <h4>Users Activity Today</h4>
                        </div>
                        <div class="stats-icon pull-right">
                            <i class="pe-7s-share fa-4x"></i>
                        </div>
                        <div class="m-t-xl">
                            <h3 class="m-b-xs">{{-- $userstoday + $usersanotherday --}}</h3>
                            <div class="progress m-t-xs full progress-small">
                                <div style="width: {{-- $userspercentage --}}%" aria-valuemax="100" aria-valuemin="0" aria-valuenow="{{-- $userspercentage --}}" role="progressbar" class=" progress-bar progress-bar-success">
                                    <span class="sr-only">{{-- $userspercentage --}}% Complete (success)</span>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-xs-6">
                                    <small class="stats-label">Logged In</small>
                                    <h4>{{-- $userstoday --}}</h4>
                                </div>

                                <div class="col-xs-6">
                                    <small class="stats-label">% Logged In</small>
                                    <h4>{{-- $userspercentage --}}%</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div> -->
            <div class="col-lg-4">
                <div class="hpanel stats">
                    <div class="panel-body h-200">
                        <div class="stats-title pull-left">
                            <h4>EID Samples Activity</h4>
                        </div>
                        <div class="stats-icon pull-right">
                            <i class="pe-7s-graph1 fa-4x"></i>
                        </div>
                        <div class="m-t-xl">
                            <h3 class="m-b-xs">4 210,160</h3>
		                    <span class="font-bold no-margins">
		                        Samples Today
		                    </span>
                            <div class="progress m-t-xs full progress-small">
                                <div style="width: 55%" aria-valuemax="100" aria-valuemin="0" aria-valuenow="55" role="progressbar" class=" progress-bar progress-bar-success">
                                    <span class="sr-only">35% Complete (success)</span>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-xs-6">
                                    <small class="stats-label">Pages / Visit</small>
                                    <h4>7.80</h4>
                                </div>

                                <div class="col-xs-6">
                                    <small class="stats-label">% New Visits</small>
                                    <h4>76.43%</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="hpanel stats">
                    <div class="panel-body h-200">
                        <div class="stats-title pull-left">
                            <h4>EID Batches History</h4>
                        </div>
                        <div class="stats-icon pull-right">
                            <i class="pe-7s-battery fa-4x"></i>
                        </div>
                        <div class="clearfix"></div>
                        <div class="m-t-xs">

                            <div class="row">
                                <div class="col-xs-6">
                                    <small class="stat-label">Lab Pending Dispatch</small>
                                    <h4>$170,20 <i class="fa fa-level-up text-success"></i></h4>
                                </div>
                                <div class="col-xs-6">
                                    <small class="stat-label">Site Pending Dispatch</small>
                                    <h4>$580,90 <i class="fa fa-level-up text-success"></i></h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="hpanel stats">
                    <div class="panel-body h-200">
                        <div class="stats-title pull-left">
                            <h4>EID Worksheets History</h4>
                        </div>
                        <div class="stats-icon pull-right">
                            <i class="pe-7s-cash fa-4x"></i>
                        </div>
                        <div class="m-t-xl">
                            <h3 class="m-b-xs">4 210,160</h3>
		                    <span class="font-bold no-margins">
		                        Workksheet History
		                    </span>
                            <div class="progress m-t-xs full progress-small">
                                <div style="width: 55%" aria-valuemax="100" aria-valuemin="0" aria-valuenow="55" role="progressbar" class=" progress-bar progress-bar-success">
                                    <span class="sr-only">35% Complete (success)</span>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-xs-6">
                                    <small class="stats-label">Pages / Visit</small>
                                    <h4>7.80</h4>
                                </div>

                                <div class="col-xs-6">
                                    <small class="stats-label">% New Visits</small>
                                    <h4>76.43%</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
		</div>
		<!-- EID Widgets -->
		<!-- VL Widgets -->
		<div class="row">
		    <div class="col-lg-4">
                <div class="hpanel stats">
                    <div class="panel-body h-200">
                        <div class="stats-title pull-left">
                            <h4>Worksheet Activity</h4>
                        </div>
                        <div class="stats-icon pull-right">
                            <i class="pe-7s-share fa-4x"></i>
                        </div>
                        <div class="m-t-xl">
                            <h3 class="m-b-xs">4 210,160</h3>
                            <div class="progress m-t-xs full progress-small">
                                <div style="width: 55%" aria-valuemax="100" aria-valuemin="0" aria-valuenow="55" role="progressbar" class=" progress-bar progress-bar-success">
                                    <span class="sr-only">35% Complete (success)</span>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-xs-6">
                                    <small class="stats-label">Pages / Visit</small>
                                    <h4>7.80</h4>
                                </div>

                                <div class="col-xs-6">
                                    <small class="stats-label">% New Visits</small>
                                    <h4>76.43%</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="hpanel stats">
                    <div class="panel-body h-200">
                        <div class="stats-title pull-left">
                            <h4>Users Activity</h4>
                        </div>
                        <div class="stats-icon pull-right">
                            <i class="pe-7s-share fa-4x"></i>
                        </div>
                        <div class="m-t-xl">
                            <h3 class="m-b-xs">4 210,160</h3>
		                    <span class="font-bold no-margins">
		                        Social users
		                    </span>
                            <div class="progress m-t-xs full progress-small">
                                <div style="width: 55%" aria-valuemax="100" aria-valuemin="0" aria-valuenow="55" role="progressbar" class=" progress-bar progress-bar-success">
                                    <span class="sr-only">35% Complete (success)</span>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-xs-6">
                                    <small class="stats-label">Pages / Visit</small>
                                    <h4>7.80</h4>
                                </div>

                                <div class="col-xs-6">
                                    <small class="stats-label">% New Visits</small>
                                    <h4>76.43%</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="hpanel stats">
                    <div class="panel-body h-200">
                        <div class="stats-title pull-left">
                            <h4>Users Activity</h4>
                        </div>
                        <div class="stats-icon pull-right">
                            <i class="pe-7s-share fa-4x"></i>
                        </div>
                        <div class="m-t-xl">
                            <h3 class="m-b-xs">4 210,160</h3>
		                    <span class="font-bold no-margins">
		                        Social users
		                    </span>
                            <div class="progress m-t-xs full progress-small">
                                <div style="width: 55%" aria-valuemax="100" aria-valuemin="0" aria-valuenow="55" role="progressbar" class=" progress-bar progress-bar-success">
                                    <span class="sr-only">35% Complete (success)</span>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-xs-6">
                                    <small class="stats-label">Pages / Visit</small>
                                    <h4>7.80</h4>
                                </div>

                                <div class="col-xs-6">
                                    <small class="stats-label">% New Visits</small>
                                    <h4>76.43%</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
		</div>
		<!-- VL Widgets -->
    </div>
</div>
@endsection()

@section('scripts')

@endsection