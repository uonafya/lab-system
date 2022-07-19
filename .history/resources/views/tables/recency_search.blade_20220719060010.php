@extends('layouts.master')

    @component('/tables/css')
    @endcomponent

@section('content')

<div class="content">
    <div class="row">
        <div class="col-lg-12">
            <div class="hpanel">
                <div class="panel-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered table-hover" >
                            <thead>
                                <tr>
                                    <th colspan="15"><center> Sample Log</center></th>
                                </tr>
                                <tr>
                                    <th colspan="5">Patient Information</th>
                                    <th colspan="3">Mother Information</th>
                                    <th colspan="6">Sample Information</th>
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
                                    <th>Batch</th>
                                    <th>Worksheet</th>
                                    <th>Result</th>
                                </tr>
                            </thead>
                            <!-- {{ $sample->patient }} -->
                            <tbody> 
                                <tr>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(auth()->user()->user_type_id != 5)
        <div class="row">
            <div class="col-lg-12">
                <div class="hpanel">
                    <div class="panel-heading">
                        <div class="panel-tools">
                            <a class="showhide"><i class="fa fa-chevron-up"></i></a>
                            <!-- <a class="closebox"><i class="fa fa-times"></i></a> -->
                        </div>
                        Sample Runs
                    </div>
                    <div class="panel-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered table-hover data-table" >
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Sample Code / Patient ID</th>
                                        <th>Lab ID</th>
                                        <th>Original Lab ID</th>
                                        <th>Run</th>
                                        <th>Date Sample Drawn</th>
                                        <th>Date Tested</th>
                                        <th>Worksheet</th>
                                        <th>Interpretation</th>
                                        <th>Result</th>
                                    </tr>
                                </thead>
                                <tbody> 
                                    @foreach($samples as $key => $samp)
                                        <tr>
                                            <td> {{ $key+1 }} </td>
                                            <td> {{ $patient->patient }} </td>
                                            <td> {{ $samp->id }} </td>
                                            <td> {{ $samp->parentid }} </td>
                                            <td> {{ $samp->run }} </td>
                                            <td> {{ $samp->datecollected }} </td>
                                            <td> {{ $samp->datetested }} </td>
                                            <td> {{ $samp->worksheet_id }} </td>
                                            <td> {{ $samp->interpretation }} </td>
                                            <td> {{ $samp->result_name }} </td>
                                        </tr>
                                    @endforeach


                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    @endif
</div>


@endsection

@section('scripts') 

    @component('/tables/scripts')

    @endcomponent

@endsection