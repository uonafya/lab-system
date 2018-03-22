@extends('layouts.master')

    @component('/tables/css')
    @endcomponent

@section('content')

<div class="normalheader ">
    <div class="hpanel">
        <div class="panel-body">
            <a class="small-header-action" href="#">
                <div class="clip-header">
                    <i class="fa fa-arrow-up"></i>
                </div>
            </a>

            <div id="hbreadcrumb" class="pull-right m-t-lg">
                <ol class="hbreadcrumb breadcrumb">
                    <li><a href="index-2.html">Dashboard</a></li>
                    <li>
                        <span>Tables</span>
                    </li>
                    <li class="active">
                        <span>DataTables</span>
                    </li>
                </ol>
            </div>
            <h2 class="font-light m-b-xs">
                DataTables
            </h2>
            <small>Advanced interaction controls to any HTML table</small>
        </div>
    </div>
</div>
 
<div class="content">
    <div class="row">
        <div class="col-lg-12">
            <div class="hpanel">
                <div class="panel-heading">
                    <div class="panel-tools">
                        <a class="showhide"><i class="fa fa-chevron-up"></i></a>
                        <a class="closebox"><i class="fa fa-times"></i></a>
                    </div>
                    Standard table
                </div>
                <div class="panel-body">
                    <div>
                        <b>Facility: {{ $batch->facility->name or '' }} </b> <br />
                        <b>Date Received: {{ $batch->datereceived or '' }} </b> <br />
                        <b>Date Entered: {{ $batch->created_at->toDateString() }} </b> <br />
                        @if($batch->high_priority)
                            <b>High Priority Batch </b> <br />
                        @endif
                        <br />
                        <br />                        
                    </div>
                    <table class="table table-striped table-bordered table-hover table-responsive" >
                        <thead>
                            <tr>
                                <th colspan="14"><center> Sample Log</center></th>
                            </tr>
                            <tr>
                                <th colspan="5">Patient Information</th>
                                <th colspan="4">Sample Information</th>
                                <th colspan="5">History Information</th>
                            </tr>
                            <tr>
                                <th>#</th>
                                <th>Patient CCC No</th>
                                <th>Sex</th>
                                <th>Age</th>
                                <th>DOB</th>
                                <th>Sample Type</th>
                                <th>Collection Date</th>
                                <th>Received Status</th>
                                <th>High Priority</th>
                                <th>Current Regimen</th>
                                <th>ART Initiation Date</th>
                                <th>Justification</th>
                                <th>Viral Load</th>
                                <th>Task</th>
                            </tr>
                        </thead>
                        <tbody> 
                            @foreach($samples as $key => $sample)
                                <tr>
                                    <td> {{ $key+1 }} </td>
                                    <td> {{ $sample->patient->patient }} </td>
                                    <td>
                                        @foreach($genders as $gender)
                                            @if($sample->patient->sex == $gender->id)
                                                {{ $gender->gender_description }}
                                            @endif
                                        @endforeach
                                    </td>
                                    <td> {{ $sample->age }} </td>
                                    <td> {{ $sample->patient->dob }} </td>
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
                                    <td></td>
                                    <td>
                                        @foreach($prophylaxis as $proph)
                                            @if($sample->prophylaxis == $proph->id)
                                                {{ $proph->name }}
                                            @endif
                                        @endforeach
                                    </td>
                                    <td> {{ $sample->patient->initiation_date }} </td>
                                    <td>
                                        @foreach($justifications as $justification)
                                            @if($sample->justification == $justification->id)
                                                {{ $justification->name }}
                                            @endif
                                        @endforeach
                                    </td>
                                    <td> {{ $sample->result }} </td>
                                    <td>
                                        <a href="{{ url('/viralsample/' . $sample->id . '/edit') }} ">View</a> |
                                        <a href="{{ url('/viralsample/' . $sample->id . '/edit') }} ">Edit</a> |

                                        {{ Form::open(['url' => 'viralsample/' . $sample->id, 'method' => 'delete']) }}
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


@endsection

@section('scripts') 

    @component('/tables/scripts')

    @endcomponent

@endsection