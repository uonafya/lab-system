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
		                <center><i class="fa fa-bolt"></i> FACILITY INFO UPDATES</center>
		            </div>
		            <div class="panel-body no-padding">
		            	<div class="alert alert-warning" style="padding-top: 4px;padding-bottom: 4px;">
			                <p>
			                    Please ensure that the Facilities Contact(s) List is UP-TO-DATE.<br />
			                    <center><a href="{{ route('facility.index') }}">Click here to Confirm.</a></center>
			                </p>
			            </div>
		                <div class="table-responsive" style="padding-left: 15px;padding-top: 2px;padding-bottom: 2px;padding-right: 15px;">
                		<table cellpadding="1" cellspacing="1" class="table table-condensed">
		                	<thead>
		                		<tr>
		                			<th>&nbsp;</th>
			                		<th>No.</th>
			                		<th>Task</th>
			                	</tr>
		                	</thead>
		                	<tbody>
		                		<tr>
		                			<td>Served by {{ $tasks['labname'][0]->name }}</td>
		                			<td>{{ $tasks['facilityServed'] }}</td>
		                			<td><a href="{{ url('facility/served') }}">View</a></td>
		                		</tr>
		                		<tr>
		                			<td>With SMS Printers</td>
		                			<td>{{ $tasks['facilitieswithSmsPrinters'] }}</td>
		                			<td><a href="{{ url('facility/smsprinters') }}">View</a></td>
		                		</tr>
		                		<tr>
		                			<td>* Without emails</td>
		                			<td>{{ $tasks['facilitiesWithoutEmails'] }}</td>
		                			<td><a href="{{ url('facility/withoutemails') }}">Update</a></td>
		                		</tr>
		                		<tr>
		                			<td>* Without G4S Details</td>
		                			<td>{{ $tasks['facilitiesWithoutG4s'] }}</td>
		                			<td><a href="#">Update</a></td>
		                		</tr>
		                	</tbody>
		                </table>
		            	</div>
		                <div class="alert alert-default">
			                Please ensure that the Facilities Contact(s) List is UP-TO-DATE to facilitate:
			            </div>
		                <ul class="list-group" style="padding-left: 24px;">
		                    <li class="list-group-item">
		                        Samples awaiting testing
		                    </li>
		                    <li class="list-group-item ">
		                        Site entry batches awaiting approval for testing.
		                    </li>
		                </ul>
		                <div class="alert alert-warning">
			                <center><a href="#">* Click to Send Email to Facilities & Stakeholders</a></center>
			            </div>
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
		                    <li class="list-group-item">
		                        <span class="badge badge-danger">{{ $widgets['pendingSamples'] }}</span>
		                        <a href="#">Samples awaiting testing</a>
		                    </li>
		                    <li class="list-group-item ">
		                        <span class="badge badge-info">{{ $widgets['batchesForApproval'][0]->totalsamples }}</span>
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
		                </ul>
		            </div>
		        </div>
		    </div>
		</div>
    </div>
</div>
@endsection()