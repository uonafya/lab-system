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
<div class="p-lg">
    <div class="content animate-panel" data-child="hpanel">
        <div class="row">
            <div class="col-lg-10 col-lg-offset-1">
                <div class="hpanel">
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