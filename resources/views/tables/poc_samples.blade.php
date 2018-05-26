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
                        <a class="closebox"><i class="fa fa-times"></i></a>
                    </div>
                    EID POC Samples List
                </div>
                <div class="panel-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered table-hover data-table" >
                            <thead>
                                <tr>
                                    <th>Batch No</th>
                                    <th>Facility</th>
                                    <th>Patient #</th>
                                    <th>Age </th>
                                    <th>Gender</th>
                                    <th>Date Drawn</th>
                                    <th>Date Tested</th>
                                    <th>Result</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody> 
                                @foreach($samples as $key => $sample)
                                    <tr>
                                        <td> {{ $sample->batch_id }} </td>
                                        <td> {{ $sample->facility->name }} </td>
                                        <td> {{ $sample->age }} </td>
                                        <td> {{ $genders->where('id', $sample->gender)->first()->gender ?? '' }} </td>
                                        <td> {{ $sample->my_date_format('datecollected') }} </td>
                                        <td> {{ $sample->my_date_format('datetested') }} </td>
                                        <td> 
                                            @if($pre == '')
                                                {{ $results->where('id', $sample->result)->first()->name ?? '' }}
                                            @else
                                                {{ $sample->result }}
                                            @endif 
                                        </td>
                                        <td> 
                                            @if($sample->datedispatched)
                                                <a href="{{ url($pre . 'batch/summary/' . $sample->batch_id) }}" target="_blank"><i class='fa fa-print'></i> Print</a> |
                                            @endif
                                            <a href="{{ url($pre . 'sample/' . $sample->id . '/edit/') }}" target="_blank">Edit</a> |
                                            <a href="{{ url($pre . 'sample/' . $sample->id . '/edit_result/') }}" target="_blank">Edit Result</a> |

                                            {{ Form::open(['url' => $pre . 'sample/' . $sample->id, 'method' => 'delete', 'onSubmit' => "return confirm('Are you sure you want to delete the following sample?')"]) }}
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
</div>


@endsection

@section('scripts') 

    @component('/tables/scripts')

    @endcomponent

@endsection