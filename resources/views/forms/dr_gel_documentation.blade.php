@extends('layouts.master')

    @component('/forms/css')
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
                    Drug Resistance Gel Documentation
                </div>
                <div class="panel-body">
                    <div class="table-responsive">
                        {{ Form::open(['url' => '/dr_extraction_worksheet/gel_documentation/' . $worksheet->id, 'method' => 'put', 'class'=>'form-horizontal', 'target' => '_blank']) }}
                        <table class="table table-striped table-bordered table-hover" >
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th id="check_all">Check All</th>
                                    <th>Sample Type</th>
                                    <th>Sample Code / Patient ID</th>
                                    <th>Facility</th>
                                    <th>Lab ID</th>
                                    <th>Date Received</th>
                                    <th>Reason</th>
                                    <th>Patient History</th>
                                </tr>
                            </thead>
                            <tbody> 
                                @foreach($samples as $key => $dr_sample)
                                    <tr>
                                        <td> {{ $key+1 }} </td>
                                        <td>
                                            <div align='center'>
                                                <input name='samples[]' type='checkbox' class='checks' value='{{ $dr_sample->id }}' 
                                                    @if($dr_sample->passed_gel_documentation)
                                                        checked='checked'
                                                    @endif
                                                />
                                            </div>
                                        </td>
                                        <td> {{ $dr_sample->control_type }} </td>
                                        <td> {{ $dr_sample->patient ?? '' }} </td>
                                        <td> {{ $dr_sample->facilityname ?? '' }} </td>
                                        <td> {{ $dr_sample->id }} </td>
                                        <td> {{ $dr_sample->my_date_format('datereceived') }} </td>
                                        <td> {{ $drug_resistance_reasons->where('id', $dr_sample->dr_reason_id)->first()->name ?? '' }} </td>
                                        <td>
                                            <a href="{{ url('viralpatient/' . $dr_sample->patient_id) }}" target="_blank">
                                                View History 
                                            </a> 
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <button class="btn btn-success" type="submit">Proceed to Submit Gel Documentation</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


@endsection

@section('scripts') 

    @component('/forms/scripts')

        $("#check_all").on('click', function(){
            var str = $(this).html();
            if(str == "Check All"){
                $(this).html("Uncheck All");
                $(".checks").prop('checked', true);
            }
            else{
                $(this).html("Check All");
                $(".checks").prop('checked', false);           
            }
        });

    @endcomponent

@endsection