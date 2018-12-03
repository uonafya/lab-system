@extends('layouts.master')

@section('css_scripts')
    
@endsection

@section('custom_css')
	<style type="text/css">
		.input-edit {
            background-color: #FFFFCC;
        }
	</style>
@endsection

@section('content')
@php
    $prevmonth = date('m')-1;
@endphp

<div class="content">
    <div class="row">
        <div class="col-lg-12">
            <div class="hpanel">
                <ul class="nav nav-tabs">
                    <li class="active"><a data-toggle="tab" href="#labs-performance"><strong>A.) Lab Performance Report ({{ date("F", mktime(null, null, null, $prevmonth)) }}, {{ date('Y') }})</strong></a></li>
                    <li class=""><a data-toggle="tab" href="#labs-equipment"><strong>B.) Lab Equipment Reports ({{ date("F", mktime(null, null, null, $prevmonth)) }}, {{ date('Y') }})</strong></a></li>
                </ul>
                <div class="tab-content">
                    <div id="labs-performance" class="tab-pane active">
                    @foreach($data->performance as $performance)
			            <div class="alert alert-warning">
			                <center>
			                    <font color="#4183D7">
			                    @if($performance->testtype == 1)
			                    	EID
			                    @else
			                    	VL - (@if($performance->sampletype == 1) Plasma @else DBS @endif)
			                    @endif
			                    </font>
			                </center>
			            </div>
	                    <div class="panel-body">
	                        <table class="table table-striped table-bordered table-hover data-table" style="font-size: 10px;margin-top: 1em;">
			                    <thead>
			                        <tr>
			                            <th>Period</th>
			                            <th>#Received Samples</th>
			                            <th>#Rejected Samples</th>
			                            <th># Logged in System</th>
			                            <th># NOT Logged in System</th>
			                            <th># Tested</th>
			                            <th>Reasons for Backlog</th>
			                        </tr>
			                    </thead>
			                    <tbody>
			                        <tr>
			                            <th>
			                                {{ date("F", mktime(null, null, null, $performance->month)) }}, {{ $performance->year }}
			                            </th>
			                            <td>
			                                {{ $performance->received }}
			                            </td>
			                            <td>
			                                {{ $performance->rejected }}
			                            </td>
			                            <td>
			                                {{ $performance->loggedin }}
			                            </td>
			                            <td>
			                                {{ $performance->notlogged }}
			                            </td>
			                            <td>
			                                {{ $performance->tested }}
			                            </td>
			                            <td>
			                                {{ $performance->reasonforbacklog }}
			                            </td>
			                        </tr>
			                    </tbody>
			                </table>
	                    </div>
	                @endforeach
                    </div>
                    <div id="labs-equipment" class="tab-pane">
                        <div class="panel-body">
                            <table class="table table-striped table-bordered table-hover data-table" style="font-size: 10px;margin-top: 1em;">
			                    <thead>
			                        <tr>
			                            <th>#</th>
			                            <th>Equipment</th>
			                            <th>Date of breakdown</th>
			                            <th>Date Reported</th>
			                            <th>Date Fixed</th>
			                            <th>Downtime (Days)</th>
			                            <th># of samples Not Run</th>
			                            <th># of failed Runs</th>
			                            <th># of reagents wasted</th>
			                            <th>Breakdown Reason</th>
			                        </tr>
			                    </thead>
			                    <tbody>
			                    @forelse($data->equipments as $key => $equipment)
			                        <tr>
			                            <td>{{ $key+1 }}</td>
			                            <td>{{ $equipment->equipment->name ?? '' }}</td>
			                            <td>{{ date('d M, Y', strtotime($equipment->datebrokendown)) ?? '' }}</td>
			                            <td>{{ date('d M, Y', strtotime($equipment->datereported)) ?? '' }}</td>
			                            <td>{{ date('d M, Y', strtotime($equipment->datefixed)) ?? '' }}</td>
			                            <td>{{ $equipment->downtime ?? '' }}</td>
			                            <td>{{ $equipment->samplesnorun ?? '' }}</td>
			                            <td>{{ $equipment->failedruns ?? '' }}</td>
			                            <td>{{ $equipment->reagentswasted ?? '' }}</td>
			                            <td>{{ $equipment->breakdownreason ?? '' }}</td>
			                        </tr>
			                    @empty
			                    	<tr><td colspan="">No Data Available</td></tr>
			                    @endforelse
			                    </tbody>
			                </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
    @component('/forms/scripts')
        @slot('js_scripts')
            
        @endslot


        @slot('val_rules')
           
        @endslot

    @endcomponent
@endsection