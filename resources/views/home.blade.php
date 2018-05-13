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
<div class="p-lg">
	<div class="content animate-panel" data-child="hpanel">
	<!-- <div class="animate-panel"  data-child="hpanel" data-effect="fadeInDown"> -->
        <div class="row">
		    <div class="col-lg-6">
		        <div class="hpanel">
		            <div class="alert alert-success">
		                <center><i class="fa fa-bolt"></i> DAILY SAMPLES PROGRESS</center>
		            </div>
		            <div class="panel-body no-padding">
		            	<div id="dailyprogress"></div>
		            </div>
		        </div>
		    </div>
		    <div class="col-lg-6">
		        <div class="hpanel">
		            <!-- <div class="panel-heading hbuilt">
		                <center>Pending Tasks</center>
		            </div> -->
		            <div class="alert alert-warning">
		                <center><i class="fa fa-bolt"></i> <strong>PENDING TASKS</strong></center>
		            </div>
		            <div class="panel-body no-padding">
		            	<ul class="list-group">
		                @if(session('testingSystem') == 'Viralload')
		                	<li class="list-group-item">
		                        <span class="badge badge-danger">{{ $widgets['pendingSamples']['plasma'] }}</span>
		                        <a href="#">Frozen Plasma Samples Awaiting Testing</a>
		                    </li>
		                    <li class="list-group-item">
		                        <span class="badge badge-danger">{{ $widgets['pendingSamples']['EDTA'] }}</span>
		                        <a href="#">Venous Blood (EDTA) Samples Awaiting Testing</a>
		                    </li>
		                    <li class="list-group-item">
		                        <span class="badge badge-danger">{{ $widgets['pendingSamples']['DBS'] }}</span>
		                        <a href="#">DBS Samples Awaiting Testing</a>
		                    </li>
		                    <li class="list-group-item">
		                        <span class="badge badge-warning">{{ $widgets['batchesForApproval'] }}</span>
		                        <a href="#">Site Entry Batches Awaiting Approval for Testing</a>
		                    </li>
		                    <li class="list-group-item">
		                        <span class="badge badge-warning">{{ $widgets['batchesNotReceived'] }}</span>
		                        <a href="#">Batches Marked as Not Received at Lab</a>
		                    </li>
		                    <li class="list-group-item">
		                        <span class="badge badge-success">{{ $widgets['batchesForDispatch'] }}</span>
		                        <a href="#">Complete Batches Awaiting Dispatch</a>
		                    </li>
		                    <li class="list-group-item">
		                        <span class="badge badge-danger">{{ $widgets['samplesForRepeat'] }}</span>
		                        <a href="#">Invalid/Failed Samples from Previous Runs to be Rerun</a>
		                    </li>
		                    <li class="list-group-item">
		                        <span class="badge badge-info">{{ $widgets['rejectedForDispatch'] }}</span>
		                        <a href="#">Rejected Samples Awaiting Dispatch</a>
		                    </li>

		            	@else
	            		    <li class="list-group-item">
		                        <span class="badge badge-danger">{{ $widgets['pendingSamples'] }}</span>
		                        <a href="#">Samples awaiting testing</a>
		                    </li>
		                    <li class="list-group-item ">
		                        <span class="badge badge-info">{{ $widgets['batchesForApproval'] }}</span>
		                        <a href="#">Site entry batches awaiting approval for testing.</a>
		                    </li>
		                    <li class="list-group-item">
		                        <span class="badge badge-primary">{{ $widgets['batchesForDispatch'] }}</span>
		                        <a href="#">Complete batches awaiting dispatch.</a>
		                    </li>
		                    <li class="list-group-item">
		                        <span class="badge badge-success">{{ $widgets['samplesForRepeat'] }}</span>
		                        <a href="#">Samples to be repeated.</a>
		                    </li>
		                    <li class="list-group-item">
		                        <span class="badge badge-warning">{{ $widgets['rejectedForDispatch'][0]->rejectfordispatch }}</span>
		                        <a href="#">Rejected samples awaiting dispatch.</a>
		                    </li>
		            	@endif
		            	</ul>
		            </div>
		        </div>
		    </div>
		</div>
    </div>
</div>
@endsection()

@section('scripts')
<script src="{{ asset('vendor/highcharts/highcharts.js' )}}"></script>
<script src="{{ asset('vendor/highcharts/modules/data.js' )}}"></script>
<script src="{{ asset('vendor/highcharts/modules/series-label.js' )}}"></script>
<script src="{{ asset('vendor/highcharts/modules/exporting.js' )}}"></script>

<script type="text/javascript">
	Highcharts.chart('dailyprogress', {
    chart: {
        type: 'column'
    },
    title: {
        text: ''
    },
    xAxis: {
        categories: @php
                        echo json_encode($chart['categories'])
                    @endphp,
        crosshair: true
    },
    yAxis: {
        min: 0,
        title: {
            text: 'No. of Samples'
        }
    },
    tooltip: {
        headerFormat: '<span style="font-size:10px">{point.key}</span><table>',
        pointFormat: '<tr><td style="color:{series.color};padding:0">{series.name}: </td>' +
            '<td style="padding:0"><b>{point.y:.1f} </b></td></tr>',
        footerFormat: '</table>',
        shared: true,
        useHTML: true
    },
    plotOptions: {
        column: {
            pointPadding: 0.2,
            borderWidth: 0
        }
    },
    series: [
    		@php
                echo json_encode($chart['series'])
            @endphp
            ]
});
</script>
@endsection