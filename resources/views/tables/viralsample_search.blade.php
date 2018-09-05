@extends('layouts.master')

    @component('/tables/css')
    @endcomponent

@section('content')

<div class="content">
    <div class="row">
        <div class="col-lg-12">
            <div class="hpanel">
                <div class="panel-body">
                    <div>
                        <b>Facility: {{ $samples->facility->name ?? '' }} </b> <br />
                        <b>Date Received: {{ date('d-M-Y', strtotime($samples->datereceived)) ?? '' }} </b> <br />
                        <b>Date Entered: {{ date('d-M-Y', strtotime($samples->created_at)) }} </b> <br />
                        @if($samples->high_priority)
                            <b>High Priority samples </b> <br />
                        @endif
                        <br />
                        <br />                        
                    </div>
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered table-hover" >
                            <thead>
                                <tr>
                                    <th colspan="14"><center> Samples Log</center></th>
                                </tr>
                                <tr>
                                    <th colspan="5">Patient Information</th>
                                    <th colspan="4">Sample Information</th>
                                    <th colspan="5">History Information</th>
                                </tr>
                                <tr>
                                	<th>Lab ID</th>
                                    <th>Patient CCC No</th>
                                    <th>Sex</th>
                                    <th>Age</th>
                                    <th>DOB</th>

                                    <th>Sample Type</th>
                                    <th>Collection Date</th>
                                    <th>Received Status</th>
                                    <th>Worksheet</th>

                                    <th>Current Regimen</th>
                                    <th>ART Initiation Date</th>
                                    <th>Justification</th>
                                    <th>Viral Load</th>
                                    <th>Task</th>
                                </tr>
                            </thead>
                            <tbody> 
                                <tr>
                                	<td> {{ $samples->id }} </td>
                                    <td> {{ $samples->patient }} </td>
                                    <td>
                                        @foreach($genders as $gender)
                                            @if($samples->sex == $gender->id)
                                                {{ $gender->gender_description }}
                                            @endif
                                        @endforeach
                                    </td>
                                    <td> {{ $samples->age }} </td>
                                    <td> {{ $samples->dob }} </td>
                                    <td>
                                        @foreach($sample_types as $sample_type)
                                            @if($samples->sampletype == $sample_type->id)
                                                {{ $sample_type->name }}
                                            @endif
                                        @endforeach
                                    </td>
                                    <td> {{ $samples->datecollected }} </td>
                                    <td>
                                        @foreach($received_statuses as $received_status)
                                            @if($samples->receivedstatus == $received_status->id)
                                                {{ $received_status->name }}
                                            @endif
                                        @endforeach
                                    </td>
                                    <td> {{ $samples->worksheet_id }} </td>
                                    <td>
                                        @foreach($prophylaxis as $proph)
                                            @if($samples->prophylaxis == $proph->id)
                                                {{ $proph->name }}
                                            @endif
                                        @endforeach
                                    </td>
                                    <td> {{ $samples->initiation_date }} </td>
                                    <td>
                                        @foreach($justifications as $justification)
                                            @if($samples->justification == $justification->id)
                                                {{ $justification->name }}
                                            @endif
                                        @endforeach
                                    </td>
                                    <td> {{ $samples->result }} </td>
                                    <td>
                                        @if($samples->batch_complete == 1)
                                            <a href="{{ url('/viralsample/print/' . $samples->id ) }} " target='_blank'>Print</a> |
                                        @endif
                                        <a href="{{ url('/viralsample/' . $samples->id . '/edit') }} ">View</a> |
                                        <a href="{{ url('/viralsample/' . $samples->id . '/edit') }} ">Edit</a> |

                                        {{ Form::open(['url' => 'viralsample/' . $samples->id, 'method' => 'delete', 'onSubmit' => "return confirm('Are you sure you want to delete the following samples?')"]) }}
                                            <button type="submit" class="btn btn-xs btn-primary">Delete</button>
                                        {{ Form::close() }}
                                    </td>
                                </tr>
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