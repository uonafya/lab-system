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
                    			<td>{{ $data->worksheet->AntibodyLotno ?? '' }}</td>
                    			<th>Multicheck Low Lot #</th>
                    			<td>{{ $data->worksheet->MulticheckLowLotno ?? '' }}</td>
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
                            @forelse($data->samples as $key => $sample)
                                <tr>
                                    <td>{{ $sample->serial_no ?? '' }}</td>
                                    <td>{{ $sample->id ?? '' }}</td>
                                    <td>{{ $sample->patient->medicalrecordno ?? '' }}</td>
                                    <td>{{ $sample->patient->patient_name ?? '' }}</td>
                                    <td>{{ $sample->run ?? '' }}</td>
                                    <td>
                                        <input type="text" name="AVGCD3percentLymph[]" class="form-control" value="{{ $sample->AVGCD3percentLymph ?? '' }}" style="min-width: 60px;">
                                    </td>
                                    <td>
                                        <input type="text" name="AVGCD3AbsCnt[]" class="form-control" value="{{ $sample->AVGCD3AbsCnt ?? '' }}" style="min-width: 60px;">
                                    </td>
                                    <td>
                                        <input type="text" name="AVGCD3CD4percentLymph[]" class="form-control" value="{{ $sample->AVGCD3CD4percentLymph ?? '' }}" style="min-width: 60px;">
                                    </td>
                                    <td>
                                        <input type="text" name="AVGCD3CD4AbsCnt[]" class="form-control" value="{{ $sample->AVGCD3CD4AbsCnt ?? '' }}" style="min-width: 60px;">
                                    </td>
                                    <td>
                                        <input type="text" name="CD45AbsCnt[]" class="form-control" value="{{ $sample->CD45AbsCnt ?? '' }}" style="min-width: 60px;">
                                    </td>
                                    <td>
                                    @if(!isset($sample->dateapproved))
                                        <select name="repeatt[]" class="form-control" style="width: 104px;">
                                            <option value='0' selected style='color:#339900'>Dispatch</option>
                                            <option value='1' style='color:#FFD324'>Rerun</option>
                                        </select>
                                    @else
                                        @if($sample->repeatt == 0)
                                            <strong><font color='#FFD324'> Dispatch </font></strong>
                                        @elseif($sample->repeatt == 1)
                                            <strong><font color='#339900'> Rerun </font></strong>
                                        @endif
                                    @endif
                                    </td>
                                    <td>
                                    @if($sample->dateapproved)
                                        <center><input class="form-control input-sm" name="checkbox[]" type="checkbox" id="checkbox[]" checked disabled style="width: 16px;height: 16px;" /></center>
                                    @else
                                        <center><input class="form-control input-sm" name="checkbox[]" type="checkbox" id="checkbox[]" value="{{ $key }}" checked style="width: 16px;height: 16px;" /></center>
                                    @endif
                                    </td>
                                    <td>
                                    @isset($sample->dateapproved)
                                        {{ gmdate('d-M-Y', $sample->dateapproved) }}
                                    @endisset
                                    </td>
                                    <td>{{ $sample->first_approver->full_name ?? '' }}</td>
                                    <td></td>
                                    <td>
                                    @isset($sample->dateapproved2)
                                        {{ gmdate('d-M-Y', $sample->dateapproved2) }}
                                    @endisset
                                    </td>
                                    <td>{{ $sample->second_approver->full_name ?? '' }}</td>
                                    <td>
                                        <a href="#">Details</a> | 
                                        <a href="#">Runs</a> | 
                                        <a href="#">Release as Redraw</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="18">
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
		                          	<button class="btn btn-success" type="submit"> Confirm & Approve Results</button>
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