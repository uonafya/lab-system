@extends('layouts.master')

    @component('/tables/css')
    @endcomponent

@section('content')
@php
	$disabled = "";
@endphp
@isset($data->view)
	@php
		$disabled = "disabled";
	@endphp
@endisset
<div class="content">
    <div class="row">
        <div class="col-lg-12">
            <div class="hpanel">
                <div class="panel-body">
                    <div class="row">
                                              
                    </div>
                    <div class="table-responsive">
                    @if(!isset($data->view))
                    	{{ Form::open(['url' => '/cd4/worksheet', 'method' => 'post', 'class'=>'form-horizontal', 'id' => 'worksheet_form']) }}
                    @endif
                    	<table class="table table-striped table-bordered table-hover">
                    		<tr>
                    			<th rowspan="2"><br>Worksheet No</th>
                    			<td rowspan="2"><br>{{ $data->worksheet->id }}</td>
                    			<th>Created By</th>
                    			<td>{{ $data->worksheet->creator->full_name }}</td>
                    			<th>Tru Count Lot #</th>
                    			<td><input type="text" name="TruCountLotno" class="form-control" value="{{ $data->worksheet->TruCountLotno }}" required {{ $disabled }}></td>
                    			<th>Multicheck Normal Lot #	</th>
                    			<td><input type="text" name="MulticheckNormalLotno" class="form-control" value="{{ $data->worksheet->MulticheckNormalLotno }}" required {{ $disabled }}></td>
                    		</tr>
                    		<tr>
                    			<th>Date Created</th>
                    			<td>{{ gmdate('d-M-Y') }}</td>
                    			<th>Antibody Lot #</th>
                    			<td><input type="text" name="AntibodyLotno" class="form-control" value="{{ $data->worksheet->AntibodyLotno }}" required {{ $disabled }}></td>
                    			<th>Multicheck Low Lot #</th>
                    			<td><input type="text" name="MulticheckLowLotno" class="form-control" value="{{ $data->worksheet->MulticheckLowLotno }}" required {{ $disabled }}></td>
                    		</tr>
                    	</table>
                    	<center><h5>{{ $data->samples->count() }} WORKSHEET SAMPLES [2 Controls]</h5></center>
                        <table class="table table-striped table-bordered table-hover" >
                            <thead>
                                <tr> 
                                   <th> SR.No</th>
                                   <th> Acc.No </th>
                                   <th> Acc.No Bar Code</th>
                                   <th> Ampath No </th>
                                   <th> Study No </th>
                                   <th> Patient Names </th>
                                   <th> Received Dt. </th>
                                   <th> Reg Dt. </th>
                                   <th> Sampl Dt. </th>
                                   <th> Tests </th>
                                </tr>
                            </thead>
                            <tbody> 
                            @if(!isset($data->view))
                            <input type="hidden" name="limit" value="{{ $data->limit }}">
                            @endif
                            @forelse($data->samples as $key => $sample)
                                <tr>
                                    <td>{{ $sample->serial_no ?? '' }}</td>
                                    <td>{{ $sample->id ?? '' }}</td>
                                    <td>
                                    	<img src="data:image/png;base64,{{ DNS1D::getBarcodePNG($sample->id, 'C128') }}" alt="barcode" height="30" width="80"  />
                                    </td>
                                    <td>{{ $sample->medicalrecordno ?? '' }}</td>
                                    <td>{{ __(' ') }}</td>
                                    <td>
                                    	{{ $sample->patient_name ?? '' }} 
                                    	/ {{ $sample->age ?? '' }} 
                                    	/ {{ $sample->gender ?? '' }}
                                    </td>
                                    <td>
                                        @if($sample->datereceived) 
                                            {{ gmdate('d-M-Y', strtotime($sample->datereceived)) }} 
                                        @endif
                                    </td>
                                    <td>{{ gmdate('d-M-Y', strtotime($sample->created_at)) }}</td>
                                    <td>
                                        @if($sample->datetested) 
                                            {{ gmdate('d-M-Y', strtotime($sample->datetested)) }} 
                                        @endif
                                    </td>
                                    <td>{{ __('CD3/CD4') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td>No Samples available yet</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                        <div class="hr-line-dashed"></div>
		                <div class="form-group">
		                    <center>
		                        <div class="col-sm-10 col-sm-offset-1">
		                        	@if(isset($data->view))
		                            	<a href="{{ url('cd4/worksheet/print/'.$data->worksheet->id) }}"><button class="btn btn-success">Print Worksheet</button></a>
		                            @else
		                            	<button class="btn btn-success" type="submit"> Save & Print Worksheet</button>
		                            @endif
		                        </div>
		                    </center>
		                </div>
		            @if(!isset($data->view))
	                    {{ Form::close() }}
	                @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


@endsection

@section('scripts') 

    @component('/tables/scripts')

    @endcomponent

@endsection