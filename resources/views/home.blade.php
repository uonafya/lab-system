@extends('layouts.master')

@section('content')
    <div class="p-lg">
	<div class="content animate-panel" data-child="hpanel">
	<!-- <div class="animate-panel"  data-child="hpanel" data-effect="fadeInDown"> -->
        <div class="row">
        	<div class="col-md-6">
	            <div class="col-md-6">
	                <div class="hpanel hbggreen">
	                    <div class="panel-body">
	                        <div class="text-center">
	                            <h3>Sample Testing</h3>
	                            <p class="text-big font-light">
	                                {{ $widgets['pendingSamples'] }}
	                            </p>
	                            <small>
	                                Samples awaiting testing
	                            </small>
	                        </div>
	                    </div>
	                </div>
	            </div>
	            <div class="col-md-6">
	                <div class="hpanel hbgblue">
	                    <div class="panel-body">
	                        <div class="text-center">
	                            <h3>Batch Approval</h3>
	                            <p class="text-big font-light">
	                                {{ $widgets['batchesForApproval'][0]->totalsamples }}
	                            </p>
	                            <small>
	                                Site entry batches awaiting approval for testing.
	                            </small>
	                        </div>
	                    </div>
	                </div>
	            </div>
	            <div class="col-md-6">
	                <div class="hpanel hbgyellow">
	                    <div class="panel-body">
	                        <div class="text-center">
	                            <h3>Batch Dispatch</h3>
	                            <p class="text-big font-light">
	                                {{ $widgets['batchesForDispatch'] }}
	                            </p>
	                            <small>
	                                Complete batches awaiting dispatch.
	                            </small>
	                        </div>
	                    </div>
	                </div>
	            </div>
	            <div class="col-md-6">
	                <div class="hpanel hbgred">
	                    <div class="panel-body">
	                        <div class="text-center">
	                            <h3>Repeat Samples</h3>
	                            <p class="text-big font-light">
	                                {{ $widgets['samplesForRepeat'] }}
	                            </p>
	                            <small>
	                                Samples to be repeated.
	                            </small>
	                        </div>
	                    </div>
	                </div>
	            </div>

	            <div class="col-md-6">
	                <div class="hpanel hbgred">
	                    <div class="panel-body">
	                        <div class="text-center">
	                            <h3>Rejected For Dispatch</h3>
	                            <p class="text-big font-light">
	                                {{ $widgets['rejectedForDispatch'][0]->rejectfordispatch }}
	                            </p>
	                            <small>
	                                Rejected samples awaiting dispatch.
	                            </small>
	                        </div>
	                    </div>
	                </div>
	            </div>
            </div>
        
        	<div class="col-md-6">
	            <div class="col-md-12">
	                <div class="hpanel">
	                    <div class="panel-body">
	                        <div class="table-responsive">
	                            <table class="table table-striped">
	                                <thead>
	                                <tr>
	                                    <th>Task</th>
	                                    <th>Date</th>
	                                </tr>
	                                </thead>
	                                <tbody>
	                                <tr>
	                                    <td>
	                                        <span class="text-success font-bold">Lorem ipsum</span>
	                                    </td>
	                                    <td>Jul 14, 2013</td>
	                                </tr>
	                                <tr>
	                                    <td>
	                                        <span class="text-success font-bold">Lorem ipsum</span>
	                                    </td>
	                                    <td>Jul 09, 2015</td>
	                                </tr>
	                                <tr>
	                                    <td>
	                                        <span class="text-success font-bold">Lorem ipsum</span>
	                                    </td>
	                                    <td>Jul 24, 2014</td>
	                                </tr>
	                                </tbody>
	                            </table>
	                        </div>
	                    </div>

	                </div>
	            </div>
        	</div>
        </div>
    </div>
</div>
@endsection()