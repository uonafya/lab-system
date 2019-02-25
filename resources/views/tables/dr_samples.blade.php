@extends('layouts.master')

    @component('/tables/css')
    @endcomponent

@section('content')

 
<div class="content">

    <div class="row">
        <div class="col-md-12">
            Click To View:
            @foreach($dr_sample_statuses as $dr_sample_status)
                <a href="{{ $myurl2 . '/' . $dr_sample_status->id }}"> {{ $dr_sample_status->name }} samples</a> | 
            @endforeach
        </div>
    </div>

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
                                    <th>Reason</th>
                                    <th>Extraction Worksheet</th>
                                    <th>Sequencing Worksheet</th>
                                    <th>Has Errors</th>
                                    <th>Has Warnings</th>
                                    <th>Has Drug Data</th>
                                    <th>Has Genotypes</th>
                                    <th>Tasks</th>
                                </tr>
                            </thead>
                            <tbody> 
                                @foreach($dr_samples as $key => $sample)
                                    <tr>
                                        <td> {{ $key+1 }} </td>
                                        <td> {!! $sample->patient->hyper_link !!} </td>
                                        <td> {{ $sample->patient->facility->name ?? '' }} </td>
                                        <td> {{ $sample->id }} </td>
                                        <td> {{ $sample->datecollected }} </td>
                                        <td> {{ $sample->datereceived }} </td>
                                        <td> {{ $drug_resistance_reasons->where('id', $sample->dr_reason_id)->first()->name ?? '' }} </td>
                                        <td> {!! $sample->get_link('extraction_worksheet_id') !!} </td>
                                        <td> {!! $sample->get_link('worksheet_id') !!} </td>
                                        <td> {{ $sample->my_boolean_format('has_errors') }} </td>
                                        <td> {{ $sample->my_boolean_format('has_warnings') }} </td>
                                        <td> {{ $sample->my_boolean_format('has_calls') }} </td>
                                        <td> {{ $sample->my_boolean_format('has_genotypes') }} </td>
                                        <td>
                                            <a href="{{ url('dr_sample/' . $sample->id) }}" target="_blank"> View Details </a> | 
                                            <a href="{{ url('dr_sample/' . $sample->id . '/edit') }}" target="_blank"> Edit </a> | 
                                            <a href="{{ url('dr_sample/results/' . $sample->id ) }}" target="_blank"> Print </a> | 
                                            <a href="{{ url('dr_sample/download_results/' . $sample->id) }}"> Download </a> | 
                                        </td>
                                    </tr>
                                @endforeach


                            </tbody>
                        </table>
                    </div>

                    {{ $dr_samples->links() }}
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