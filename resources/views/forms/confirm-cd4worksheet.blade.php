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
                   	@if(!isset($data->view) && $data->samples->count() == 0)
                   		<center><div class="alert alert-warning">No samples availabe to run a worksheet</div></center>
                   	@else
                    	<table class="table table-striped table-bordered table-hover">
                    		<tr>
                    			<th rowspan="2"><br>Worksheet No</th>
                    			<td rowspan="2"><br>{{ $data->worksheet->id }}</td>
                    			<th>Created By</th>
                    			<td>{{ $data->worksheet->creator->full_name ?? '' }}</td>
                    			<th>Tru Count Lot #</th>
                    			<td>{{ $data->worksheet->TruCountLotno ?? '' }}</td>
                    			<th>Multicheck Normal Lot #	</th>
                    			<td>{{ $data->worksheet->MulticheckNormalLotno ?? '' }}</td>
                    		</tr>
                    		<tr>
                    			<th>Date Created</th>
                    			<td>{{ gmdate('d-M-Y', strtotime($data->worksheet->created_at)) }}</td>
                    			<th>Antibody Lot #</th>
                    			<td><input type="text" name="AntibodyLotno" class="form-control" value="{{ $data->worksheet->AntibodyLotno ?? '' }}" required {{ $disabled }}></td>
                    			<th>Multicheck Low Lot #</th>
                    			<td><input type="text" name="MulticheckLowLotno" class="form-control" value="{{ $data->worksheet->MulticheckLowLotno ?? '' }}" required {{ $disabled }}></td>
                    		</tr>
                            <tr>
                                <th>Date Run</th>
                                <td>{{ gmdate('d-M-Y', strtotime($data->worksheet->daterun)) }}</td>
                                <th>Date Updated</th>
                                <td>{{ gmdate('d-M-Y', strtotime($data->worksheet->dateuploaded)) }}</td>
                                <th>Date Reviewed (1st)</th>
                                <td>{{ gmdate('d-M-Y', strtotime($data->worksheet->datereviewed)) }}</td>
                                <th>Date Reviewed (2nd)</th>
                                <td>{{ gmdate('d-M-Y', strtotime($data->worksheet->datereviewed2)) }}</td>
                            </tr>
                            <tr>
                                <th>Status</th>
                                <td></td>
                                <th>Updated By</th>
                                <td>{{ $data->worksheet->uploader->full_name ?? '' }}</td>
                                <th>Reveiwed By (1st)</th>
                                <td>{{ $data->worksheet->first_reviewer->full_name ?? '' }}</td>
                                <th>Reviewed By (2nd)</th>
                                <td>{{ $data->worksheet->second_reviewer->full_name ?? '' }}</td>
                            </tr>
                    	</table>
                    	<table class="table table-striped table-bordered table-hover" >
                            <thead>
                                <tr> 
                                   <th> Serial #</th>
                                   <th> Lab ID </th>
                                   <th> Ampath #</th>
                                   <th> Patient Names </th>
                                   <th> Run </th>
                                   <th> CD3+ %Lymph </th>
                                   <th> CD3+ Abs Cnt </th>
                                   <th> CD3+CD4+ %Lymph </th>
                                   <th> CD3+CD4+ Abs Cnt </th>                                   
                                   <th> Lymphocytes </th>
                                   <th> Action </th>
                                   <th> Reviewed (1st) </th>

                                   <th> Date Reviewed (1st) </th>
                                   <th> Reviewed By (1st) </th>
                                   <th> Reviewed (2nd) </th>

                                   
                                   <th> Date Reviewed (2nd) </th>
                                   <th> Reviewed By (2nd) </th>
                                   <th> Task </th>
                                </tr>
                            </thead>
                            <tbody> 
                            @if(!isset($data->view))
                            <input type="hidden" name="limit" value="{{ $data->limit }}">
                            @endif
                            @forelse($data->samples as $key => $sample)
                                <tr>
                                    <td>{{ $sample->serial_no ?? '' }}</td>
                                    <td>
                                        {{ $sample->id ?? '' }}
                                        @if($sample->parentid > 0)
                                            <div align='right'>
                                                <table>
                                                    <tr>
                                                        <td style='background-color:#FAF156'><small> R </small></td>
                                                    </tr>
                                                </table>
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                    	<img src="data:image/png;base64,{{ DNS1D::getBarcodePNG($sample->id, 'C128') }}" alt="barcode" height="30" width="80"  />
                                    </td>
                                    <td>{{ $sample->medicalrecordno ?? $sample->patient->medicalrecordno ?? '' }}</td>
                                    <td>{{ __(' ') }}</td>
                                    <td>
                                    	{{ $sample->patient_name ?? $sample->patient->patient_name ?? '' }} 
                                    	/ {{ $sample->age ?? $sample->patient->age ?? '' }} 
                                    	/ {{ $sample->gender ?? $sample->patient->gender ?? '' }}
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
                                    <td colspan="10">
                                    	<center>
                                    		<div class="alert alert-warning">
                                    			All the samples that were in this worksheet have been released back into the queue for selection in next worksheet.
                                    		</div>
                                    	</center>
                                    </td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                        <div class="hr-line-dashed"></div>
		                <div class="form-group">
		                    <center>
		                        <div class="col-sm-10 col-sm-offset-1">
		                        @if($data->worksheet->status_id != 4)
		                        	@if(isset($data->view))
		                            	<a href="{{ url('cd4/worksheet/print/'.$data->worksheet->id) }}"><button class="btn btn-success">Print Worksheet</button></a>
		                            @else
		                            	<button class="btn btn-success" type="submit"> Save & Print Worksheet</button>
		                            @endif
		                        @endif
		                        </div>
		                    </center>
		                </div>
		            @endif
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