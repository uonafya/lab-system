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
                        <b>Facility: {{ $samples->facility_id ?? '' }} </b> <br />
                        <b>Date Received: {{ date('d-M-Y', strtotime($samples->datereceived)) }} </b> <br />
                        <b>Date Entered: {{ date('d-M-Y', strtotime($samples->created_at)) }} </b> <br />
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
                                    <th colspan="3">Mother Information</th>
                                    <th colspan="5">Samples Information</th>
                                    <th rowspan="2">Task</th>
                                </tr>
                                <tr>
                                    <th>Lab ID</th>
                                    <th>Patient ID</th>
                                    <th>Sex</th>
                                    <th>Age (Months)</th>
                                    <th>Infant Prophylaxis</th>

                                    <th>Entry Point</th>
                                    <th>Feeding Type</th>
                                    <th>PMTCT Intervention</th>

                                    <th>Date Collected</th>
                                    <th>Status</th>
                                    <th>Spots</th>
                                    <th>Worksheet</th>
                                    <th>Result</th>
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
                                    <td>
                                        @foreach($iprophylaxis as $iproph)
                                            @if($samples->regimen == $iproph->id)
                                                {{ $iproph->name }}
                                            @endif
                                        @endforeach
                                    </td>
                                    <td>
                                        @foreach($entry_points as $entry_point)
                                            @if($samples->entry_point == $entry_point->id)
                                                {{ $entry_point->name }}
                                            @endif
                                        @endforeach
                                    </td>
                                    <td>
                                        @foreach($feedings as $feeding)
                                            @if($samples->feeding == $feeding->id)
                                                {{ $feeding->feeding }}
                                            @endif
                                        @endforeach
                                    </td>
                                    <td>
                                        @foreach($interventions as $intervention)
                                            @if($samples->mother_prophylaxis == $intervention->id)
                                                {{ $intervention->name }}
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
                                    <td> {{ $samples->spots }} </td>
                                    <td> {{ $samples->worksheet_id }} </td>
                                    <td>
                                        @foreach($results as $result)
                                            @if($samples->result == $result->id)
                                                {{ $result->name }}
                                            @endif
                                        @endforeach
                                    </td>
                                    <td>
                                        @if($samples->batch_complete == 1)
                                            <a href="{{ url('/sample/print/' . $samples->id ) }} " target='_blank'>Print</a> |
                                        @endif
                                        <a href="{{ url('/sample/' . $samples->id . '/edit') }} ">View</a> |
                                        <a href="{{ url('/sample/' . $samples->id . '/edit') }} ">Edit</a> |

                                        {{ Form::open(['url' => 'samples/' . $samples->id, 'method' => 'delete', 'onSubmit' => "return confirm('Are you sure you want to delete the following samples?')"]) }}
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