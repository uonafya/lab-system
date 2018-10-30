@extends('layouts.master')

    @component('/tables/css')
    @endcomponent

@section('content')

<div class="content">
    <div class="row">
        <div class="col-lg-12">
            <div class="hpanel">
                <div class="panel-heading">
                    <div class="panel-tools">
                        <a class="showhide"><i class="fa fa-chevron-up"></i></a>
                        <!-- <a class="closebox"><i class="fa fa-times"></i></a> -->
                    </div>
                    Batch Details
                </div>
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
                        @if($batch->high_priority)
                            <div class="col-md-4">
                                <p><strong>Date Received:</strong> {{ $batch->my_date_format('datereceived')  ?? '' }}</p>
                            </div>
                            <div class="col-md-4">
                                <p><strong>Received By:</strong> {{ $batch->receiver->full_name ?? '' }}</p>
                            </div>
                            <div class="col-md-4">
                                <span class="alert alert-warning">High Priority Batch</span>
                            </div>
                        @else
                            <div class="col-md-4">
                                <p><strong>Date Received:</strong> {{ $batch->my_date_format('datereceived')  ?? '' }}</p>
                            </div>
                            <div class="col-md-8">
                                <p><strong>Received By:</strong> {{ $batch->receiver->full_name ?? '' }}</p>
                            </div>
                        @endif                        
                    </div>
                    @if(auth()->user()->user_type_id != 5)
                        <div class="row">
                            @if(!$batch->datereceived)
                                <div class="col-md-4">
                                    <a href="{{ url('viralbatch/site_approval_group/' . $batch->id) }} ">
                                        <button class="btn btn-primary">Transfer Samples To Another Batch</button>
                                    </a>
                                </div>
                            @endif
                            <div class="col-md-4 pull-right">
                                <a href="{{ url('viralbatch/transfer/' . $batch->id) }} ">
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
                                    <th colspan="15"><center> Sample Log</center></th>
                                </tr>
                                <tr>
                                    <th colspan="6">Patient Information</th>
                                    <th colspan="4">Sample Information</th>
                                    <th colspan="5">History Information</th>
                                </tr>
                                <tr>
                                    <th>#</th>
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
                                <?php $i=1; ?>
                                @foreach($samples as $key => $sample)
                                    @continue($sample->repeatt == 1)
                                    <tr>
                                        <td> {{ $i++ }} </td>
                                        <td> {{ $sample->id }} </td>
                                        <td> {{ $sample->patient->patient }} </td>
                                        <td> {{ $sample->patient->gender }} </td>
                                        <td> {{ $sample->age }} </td>
                                        <td> {{ $sample->patient->my_date_format('dob') }} </td>
                                        <td>
                                            @foreach($sample_types as $sample_type)
                                                @if($sample->sampletype == $sample_type->id)
                                                    {{ $sample_type->name }}
                                                @endif
                                            @endforeach
                                        </td>
                                        <td> {{ $sample->datecollected }} </td>
                                        <td>
                                            @foreach($received_statuses as $received_status)
                                                @if($sample->receivedstatus == $received_status->id)
                                                    {{ $received_status->name }}
                                                @endif
                                            @endforeach
                                        </td>
                                        <td>{{ $sample->worksheet_id }} </td>
                                        <td>
                                            @foreach($prophylaxis as $proph)
                                                @if($sample->prophylaxis == $proph->id)
                                                    {{ $proph->name }}
                                                @endif
                                            @endforeach
                                        </td>
                                        <td> {{ $sample->patient->my_date_format('initiation_date') }} </td>
                                        <td>
                                            @foreach($justifications as $justification)
                                                @if($sample->justification == $justification->id)
                                                    {{ $justification->name }}
                                                @endif
                                            @endforeach
                                        </td>
                                        <td> {{ $sample->result }} </td>
                                        <td>
                                            @if($batch->batch_complete == 1)
                                                <a href="{{ url('/viralsample/print/' . $sample->id ) }} " target='_blank'>Print</a> |
                                            @endif
                                            <a href="{{ url('/viralsample/' . $sample->id . '/edit') }} ">View</a> |
                                            <a href="{{ url('/viralsample/' . $sample->id . '/edit') }} ">Edit</a> |

                                            {{ Form::open(['url' => 'viralsample/' . $sample->id, 'method' => 'delete', 'onSubmit' => "return confirm('Are you sure you want to delete the following sample?')"]) }}
                                                <button type="submit" class="btn btn-xs btn-primary">Delete</button>
                                            {{ Form::close() }}
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
                                <a href="{{ url('viralbatch/summary/' . $batch->id) }}">
                                    <button class="btn btn-primary">Download Batch Summary</button>
                                </a>
                            </div>
                            <div class="col-md-4">
                                <a href="{{ url('viralbatch/individual/' . $batch->id) }}" target="_blank">
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