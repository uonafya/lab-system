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
                    <table class="table table-striped table-bordered table-hover" >
                        <thead>
                            <tr class="colhead">
                                <th rowspan="2">Batch No</th>
                                <th rowspan="2">Facility</th>
                                <th colspan="2">Date</th>
                                <th rowspan="1">TAT</th>
                                <th rowspan="2">Entered By</th>
                                <th colspan="4"># of samples</th>
                                <th rowspan="2">Status</th>
                                <th rowspan="2">Task</th>
                            </tr>
                            <tr>
                                <th>Received</th>
                                <th>Entered</th>
                                <th>(Dys)</th>
                                <th>Received</th>
                                <th>Rejected</th>
                                <th>Results</th>
                                <th>No Result</th>              
                            </tr>
                        </thead>
                        <tbody>

                            @php
                                echo $rows;
                            @endphp 
                        </tbody>
                    </table>

                    {!!  $links !!}
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