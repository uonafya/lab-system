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
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered table-hover" >
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Patient</th>
                                    <th>Facility</th>
                                    <th>Batch No</th>
                                    <th>Received Status</th>
                                    <th>Spots</th>
                                    <th>Date Collected</th>
                                    <th>Date Received</th>
                                    <th>Worksheet</th>
                                    <th>Date Tested</th>
                                    <th>Date Modified</th>
                                    <th>Date Dispatched</th>
                                    <th>Run</th>
                                    <th>Result</th>
                                    <th>Task</th>
                                </tr>
                            </thead>
                            <tbody> 
                                @foreach($samples as $key => $sample)
                                    <tr>
                                        <td> {{ $key+1 }} </td>
                                        <td> {{ $patient->patient }} </td>
                                        <td> {{ $patient->facility->name }} </td>
                                        <td> {{ $sample->batch_id }} </td>
                                        <td>
                                            @foreach($received_statuses as $received_status)
                                                @if($sample->receivedstatus == $received_status->id)
                                                    {{ $received_status->name }}
                                                @endif
                                            @endforeach
                                        </td>
                                        <td> {{ $sample->spots }} </td>
                                        <td> {{ $sample->datecollected }} </td>
                                        <td> {{ $sample->batch->datereceived }} </td>
                                        <td> {{ $sample->worksheet_id }} </td>
                                        <td> {{ $sample->datetested }} </td>
                                        <td> {{ $sample->datemodified }} </td>
                                        <td> {{ $sample->batch->datedispatched }} </td>
                                        <td> {{ $sample->run }} </td>
                                        <td>
                                            @foreach($results as $result)
                                                @if($sample->result == $result->id)
                                                    {{ $result->name }}
                                                @endif
                                            @endforeach
                                        </td>
                                        <td>
                                            <a href="{{ url('/sample/print/' . $sample->id ) }} " target='_blank'>Print</a>
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