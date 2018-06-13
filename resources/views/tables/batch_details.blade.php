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
                        <div class="col-md-4">
                            <p><strong>Batch:</strong> {{ $batch->id  ?? '' }}</p>
                        </div>
                        <div class="col-md-8">
                            <p><strong>Facility:</strong> {{ ($batch->view_facility->facilitycode . ' - ' . $batch->view_facility->name . ' (' . $batch->view_facility->county . ')') ?? '' }}</p>
                        </div>
                        <div class="col-md-4">
                            <p>
                                <strong>Entry Type: </strong>
                                @switch($batch->site_entry)
                                    @case(0)
                                        {{ 'Lab Entry' }}
                                        @break
                                    @case(1)
                                        {{ 'Site Entry' }}
                                        @break
                                    @case(2)
                                        {{ 'POC Entry' }}
                                        @break
                                    @default
                                        @break
                                @endswitch
                            </p>
                        </div>
                        <div class="col-md-4">
                            <p><strong>Date Entered:</strong> {{ $batch->my_date_format('created_at') }}</p>
                        </div>
                        <div class="col-md-4">
                            <p><strong>Entered By:</strong> 
                                @if($batch->creator->full_name != ' ')
                                    {{ $batch->creator->full_name }}
                                @else
                                    {{ $batch->creator->facility->name ?? '' }}
                                @endif
                            </p>
                        </div>
                        <div class="col-md-4">
                            <p><strong>Date Received:</strong> {{ $batch->my_date_format('datereceived')  ?? '' }}</p>
                        </div>
                        <div class="col-md-8">
                            <p><strong>Received By:</strong> {{ $batch->receiver->full_name ?? '' }}</p>
                        </div>                       
                    </div>
                    @if(auth()->user()->user_type_id != 5)
                        <div class="row">
                            <div class="col-md-4 pull-right">
                                <a href="{{ url('batch/transfer/' . $batch->id) }} ">
                                    <button class="btn btn-primary">Transfer Samples To Another Batch</button>
                                </a>
                            </div>
                        </div>
                        <br />
                    @endif
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered table-hover" >
                            <thead>
                                <tr>
                                    <th colspan="17"><center> Sample Log</center></th>
                                </tr>
                                <tr>
                                    <th colspan="6">Patient Information</th>
                                    <th colspan="3">Sample Information</th>
                                    <th colspan="8">Mother Information</th>
                                </tr>
                                <tr> 
                                    <th>No</th>
                                    <th>Patient ID</th>
                                    <th>Sex</th>
                                    <th>DOB</th>
                                    <th>Age (Months)</th>
                                    <th>Infant Prophylaxis</th>

                                    <th>Date Collected</th>
                                    <th>Status</th>
                                    <th>Spots</th>

                                    <th>CCC #</th>
                                    <th>Age</th>
                                    <th>Last Vl</th>
                                    <th>PMTCT Intervention</th>
                                    <th>Feeding Type</th>
                                    <th>Entry Point</th>
                                    <th>Result</th>
                                    <th>Task</th>
                                </tr>
                            </thead>
                            <tbody> 
                                @foreach($samples as $key => $sample)
                                    <tr>
                                        <td> {{ $key+1 }} </td>
                                        <td> {{ $sample->patient->patient }} </td>
                                        <td> {{ $sample->patient->gender }} </td>
                                        <td> {{ $sample->patient->my_date_format('dob') }} </td>
                                        <td> {{ $sample->age }} </td>
                                        <td>
                                            @foreach($iprophylaxis as $iproph)
                                                @if($sample->regimen == $iproph->id)
                                                    {{ $iproph->name }}
                                                @endif
                                            @endforeach
                                        </td>

                                        <td> {{ $sample->my_date_format('datecollected') }} </td>
                                        <td>
                                            @foreach($received_statuses as $received_status)
                                                @if($sample->receivedstatus == $received_status->id)
                                                    {{ $received_status->name }}
                                                @endif
                                            @endforeach
                                        </td>
                                        <td> {{ $sample->spots }} </td>

                                        <td> {{ $sample->patient->mother->ccc_no ?? '' }} </td>
                                        <td> {{ $sample->mother_age }} </td>
                                        <td> {{ $sample->mother_last_result }} </td>
                                        <td>
                                            @foreach($interventions as $intervention)
                                                @if($sample->mother_prophylaxis == $intervention->id)
                                                    {{ $intervention->name }}
                                                @endif
                                            @endforeach
                                        </td>
                                        <td>
                                            @foreach($feedings as $feeding)
                                                @if($sample->feeding == $feeding->id)
                                                    {{ $feeding->feeding }}
                                                @endif
                                            @endforeach
                                        </td>
                                        <td>
                                            @foreach($entry_points as $entry_point)
                                                @if($sample->patient->entry_point == $entry_point->id)
                                                    {{ $entry_point->name }}
                                                @endif
                                            @endforeach
                                        </td>
                                        <td>
                                            @foreach($results as $result)
                                                @if($sample->result == $result->id)
                                                    {{ $result->name }}
                                                @endif
                                            @endforeach
                                        </td>
                                        <td>
                                            @if($batch->batch_complete == 1)
                                                <a href="{{ url('/sample/print/' . $sample->id ) }} " target='_blank'>Print</a> |
                                            @endif
                                            <a href="{{ url('/sample/' . $sample->id . '/edit') }} ">View</a> |
                                            <a href="{{ url('/sample/' . $sample->id . '/edit') }} ">Edit</a> |

                                            {{ Form::open(['url' => 'sample/' . $sample->id, 'method' => 'delete', 'onSubmit' => "return confirm('Are you sure you want to delete the following sample?')"]) }}
                                                <button type="submit" class="btn btn-xs btn-primary">Delete</button> 
                                            {{ Form::close() }}
                                        </td>
                                    </tr>
                                @endforeach


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