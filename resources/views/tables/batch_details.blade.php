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
                                @if($batch->creator)
                                    @if($batch->creator->full_name != ' ')
                                        {{ $batch->creator->full_name }}
                                    @else
                                        {{ $batch->creator->facility->name ?? '' }} {{ $batch->entered_by ?? '' }}
                                    @endif
                                @else
                                    {{ $batch->entered_by ?? '' }}
                                @endif
                            </p>
                        </div>
                        <div class="col-md-4">
                            <p><strong>Date Received:</strong> {{ $batch->my_date_format('datereceived')  ?? '' }}</p>
                        </div>
                        <div class="col-md-4">
                            <p><strong>Received By:</strong> {{ $batch->receiver->full_name ?? '' }}</p>
                        </div>  
                        <div class="col-md-4">
                            <p><strong>Date Dispatched:</strong> {{ $batch->my_date_format('datedispatched')  ?? '' }}</p>
                        </div>                     
                    </div>
                    @if(auth()->user()->user_type_id != 5)
                        <div class="row">
                            @if(($batch->site_entry == 1 || ($batch->site_entry == 0 && $batch->user_id == 0 && !$batch->batch_complete )) && (!$batch->datereceived || 
                            ($batch->datereceived && $samples->where('receivedstatus', null)->first())
                            ) )
                                <div class="col-md-4">
                                    <a href="{{ url('batch/site_approval_group/' . $batch->id) }} ">
                                        <button class="btn btn-primary">Approve Site Entry</button>
                                    </a>
                                </div>
                            @endif

                            @if($batch->site_entry == 2)
                                <div class="col-md-4">
                                    <a href="{{ url('batch/convert_from_poc/' . $batch->id) }} ">
                                        <button class="btn btn-primary">Convert to Site Entry</button>
                                    </a>
                                </div>
                            @endif



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
                                    <th colspan="20"><center> Sample Log</center></th>
                                </tr>
                                <tr>
                                    <th colspan="7">Patient Information</th>
                                    <th colspan="4">Sample Information</th>
                                    <th colspan="9">Mother Information</th>
                                </tr>
                                <tr> 
                                    <th>No</th>
                                    <th>Lab ID</th>
                                    <th>Patient ID</th>
                                    <th>Sex</th>
                                    <th>DOB</th>
                                    <th>Age (Months)</th>
                                    <th>Infant Prophylaxis</th>

                                    <th>Worksheet</th>
                                    <th>Date Collected</th>
                                    <th>Received Status</th>
                                    <th>Spots</th>

                                    <th>CCC #</th>
                                    <th>Age</th>
                                    <th>Last Vl</th>
                                    <th>PMTCT Intervention</th>
                                    <th>Feeding Type</th>
                                    <th>Entry Point</th>
                                    <th>Result</th>
                                    <th>Task</th>
                                    <th>Delete</th>
                                </tr>
                            </thead>
                            <tbody> 
                                <?php $i=1; ?>
                                @foreach($samples as $key => $sample)
                                    @continue($sample->repeatt == 1)
                                    <tr>
                                        <td> {{ $i++ }} </td>
                                        <td> {{ $sample->id }} </td>
                                        <td> {!! $sample->patient->hyperlink !!} </td>
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

                                        <td> {!! $sample->get_link('worksheet_id') !!} </td>
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
                                            @if(auth()->user()->user_type_id != 5 && $batch->batch_complete == 0 && env('APP_LAB') == 3 && !$sample->worksheet_id && $sample->receivedstatus == 1 && (($sample->sample_received_by && $sample->sample_received_by != auth()->user()->id) || 
                                            (!$sample->sample_received_by && $batch->received_by != auth()->user()->id) ))
                                                <a href="{{ url('/sample/transfer/' . $sample->id ) }}">Transfer To My Account</a> |
                                            @endif

                                            @if($batch->batch_complete == 1)
                                                <a href="{{ url('/sample/print/' . $sample->id ) }} " target='_blank'>Print</a> |
                                            @endif
                                            <a href="{{ url('/sample/' . $sample->id ) }} ">View</a> |
                                            <a href="{{ url('/sample/' . $sample->id . '/edit') }} ">Edit</a>

                                            @if($batch->batch_complete == 0 && $sample->receivedstatus == 1 && !$sample->worksheet_id && !$sample->result)
                                                | <a href="{{ url('/sample/release/' . $sample->id ) }} ">Release As Redraw</a> 
                                            @endif
                                        </td>

                                        <td>
                                            @if($batch->batch_complete == 0 && $sample->result == null && $sample->worksheet_id == null && $sample->run < 2 && $sample->receivedstatus != 2)

                                            
                                                {{ Form::open(['url' => 'sample/' . $sample->id, 'method' => 'delete', 'onSubmit' => "return confirm('Are you sure you want to delete the following sample?')"]) }}
                                                    <button type="submit" class="btn btn-xs btn-primary">Delete</button> 
                                                {{ Form::close() }} 

                                            @endif                                           
                                        </td>
                                    </tr>
                                @endforeach


                            </tbody>
                        </table>
                    </div>

                    @if($batch->batch_complete == 1)
                        <br />
                        <div class="row">
                            <div class="col-md-4">
                                <a href="{{ url('batch/summary/' . $batch->id) }}">
                                    <button class="btn btn-primary">Download Batch Summary</button>
                                </a>
                            </div>
                            <div class="col-md-4">
                                <a href="{{ url('batch/individual/' . $batch->id) }}" target="_blank">
                                    <button class="btn btn-primary">Print Individual Results</button>
                                </a>
                            </div>
                        </div>
                    @endif

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