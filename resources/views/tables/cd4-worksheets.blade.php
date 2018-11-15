@extends('layouts.master')

    @component('/tables/css')
    @endcomponent

@section('content')

<div class="content">
    <div class="row">
        <div class="col-lg-12">
            <div class="hpanel">
                <div class="panel-body">
                    <div class="row">
                    
                    </div>
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered table-hover" >
                            <thead>
                                <tr> 
                                    <th rowspan="2">Serial #</th>
                                    <th rowspan="2">Date Created</th>
                                    <th rowspan="2">Created By</th>
                                    <th rowspan="2">Status</th>
                                    <th rowspan="2"># Samples</th>
                                    <th colspan="4">Date</th>
                                    <th rowspan="2">Task</th>
                                </tr>
                                <tr>
                                    <th>Run</th>
                                    <th>Updated</th>
                                    <th>Reviewed (1st)</th>
                                    <th>Reviewed (2nd)</th>
                                </tr>
                            </thead>
                            <tbody> 
                            @forelse($data->worksheets as $key => $worksheet)
                                <tr>
                                    <td>{{ $worksheet->id ?? '' }}</td>
                                    <td>{{ gmdate('d-M-Y', strtotime($worksheet->created_at)) }}</td>
                                    <td>{{ $worksheet->creator->full_name ?? '' }}</td>
                                    <td>
                                    @foreach($data->worksheet_statuses as $worksheetstatus)
                                        @if($worksheetstatus->id == $worksheet->status_id)
                                            @if($worksheetstatus->id == 1)
                                                <label class="label label-warning">{{ $worksheetstatus->state }}</label>
                                            @elseif($worksheetstatus->id == 2)
                                                <label class="label label-primary">{{ $worksheetstatus->state }}</label>
                                            @elseif($worksheetstatus->id == 3)
                                                <label class="label label-success">{{ $worksheetstatus->state }}</label>
                                            @elseif($worksheetstatus->id == 4)
                                                <label class="label label-danger">{{ $worksheetstatus->state }}</label>
                                            @endif
                                        @endif
                                    @endforeach
                                    </td>
                                    <td>{{ $worksheet->samples->count() }}</td>
                                    <td>@if($worksheet->daterun) 
                                            {{ gmdate('d-M-Y', strtotime($worksheet->daterun)) }} 
                                        @endif</td>
                                    <td>@if($worksheet->dateuploaded) 
                                            {{ gmdate('d-M-Y', strtotime($worksheet->dateuploaded)) }} 
                                        @endif</td>
                                    <td>@if($worksheet->datereviewed) 
                                            {{ gmdate('d-M-Y', strtotime($worksheet->datereviewed)) }} 
                                        @endif</td>
                                    <td>@if($worksheet->datereviewed2) 
                                            {{ gmdate('d-M-Y', strtotime($worksheet->datereviewed2)) }} 
                                        @endif</td>
                                    <td>
                                        <a href="{{ URL::to('cd4/worksheet/'.$worksheet->id) }}">Details</a> |
                                        @if($worksheet->status_id > 1)
                                            <a href="#">Print</a> |
                                        @endif
                                        @if($worksheet->status_id == 1 ||$worksheet->status_id == 2)
                                            <a href="{{ url('cd4/sample/'.$worksheet->id.'/edit') }}">Edit</a> | <a href="#">Delete</a>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td>No worksheets available yet</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
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