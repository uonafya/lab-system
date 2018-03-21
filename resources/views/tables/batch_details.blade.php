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
                        <br />
                        <br />                        
                    </div>
                    <table class="table table-striped table-bordered table-hover" >
                        <thead>
                            <tr>
                                <th colspan="14"><center> Sample Log</center></th>
                            </tr>
                            <tr>
                                <th colspan="5">Patient Information</th>
                                <th colspan="3">Sample Information</th>
                                <th colspan="6">Mother Information</th>
                            </tr>
                            <tr>
                                <th>No</th>
                                <th>Patient ID</th>
                                <th>Sex</th>
                                <th>Age (Months)</th>
                                <th>Infant Prophylaxis</th>
                                <th>Date Collected</th>
                                <th>Status</th>
                                <th>Spots</th>
                                <th>HIV Status</th>
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
                                    <td>
                                        @foreach($genders as $gender)
                                            @if($sample->patient->sex == $gender->id)
                                                {{ $gender->gender_description }}
                                            @endif
                                        @endforeach
                                    </td>
                                    <td> {{ $sample->age }} </td>
                                    <td>
                                        @foreach($iprophylaxis as $iproph)
                                            @if($sample->regimen == $iproph->id)
                                                {{ $iproph->name }}
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
                                    <td> {{ $sample->spots }} </td>
                                    <td>
                                        @foreach($results as $result)
                                            @if($sample->patient->mother->hiv_status == $result->id)
                                                {{ $result->name }}
                                            @endif
                                        @endforeach
                                    </td>
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
                                            @if($sample->patient->mother->entry_point == $entry_point->id)
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
                                        <a href="{{ url('/sample/' . $sample->id . '/edit') }} ">View</a> |
                                        <a href="{{ url('/sample/' . $sample->id . '/edit') }} ">Edit</a> |

                                        {{ Form::open(['url' => 'sample/' . $sample->id, 'method' => 'delete']) }}
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