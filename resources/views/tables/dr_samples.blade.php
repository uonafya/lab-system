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
                    Drug Resistance Samples
                </div>
                <div class="panel-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered table-hover" >
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Sample Code / Patient ID</th>
                                    <th>Facility</th>
                                    <th>Lab ID</th>
                                    <th>Date Collected</th>
                                    <th>Date Received</th>
                                    <th>Result</th>
                                    <th>Reason</th>
                                    <th>DR Worksheet</th>
                                    <th>Patient History</th>
                                    <th>Tasks</th>
                                </tr>
                            </thead>
                            <tbody> 
                                @foreach($dr_samples as $key => $sample)
                                    <tr>
                                        <td> {{ $key+1 }} </td>
                                        <td> {{ $sample->patient->patient ?? '' }} </td>
                                        <td> {{ $sample->patient->facility->name ?? '' }} </td>
                                        <td> {{ $sample->id }} </td>
                                        <td> {{ $sample->datecollected }} </td>
                                        <td> {{ $sample->datereceived }} </td>
                                        <td> {{ $sample->result }} </td>
                                        <td> {{ $drug_resistance_reasons->where('id', $sample->dr_reason_id)->first()->name ?? '' }} </td>
                                        <td></td>
                                        <td>
                                            <a href="{{ url('viralpatient/' . $sample->patient->id) }}" target="_blank">
                                                View History 
                                            </a> 
                                        </td>
                                        <td>
                                            <a href="{{ url('dr_sample/' . $sample->id . '/id') }}" target="_blank">
                                                Edit
                                            </a> 
                                        </td>
                                    </tr>
                                @endforeach


                            </tbody>
                        </table>
                    </div>

                    {{ $samples->links() }}
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