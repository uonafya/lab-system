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
                    Batches Awaiting Dispatch
                </div>
                <div class="panel-body">
                    <form  method="post" action="{{ url('viralbatch/complete_dispatch') }}  " name="worksheetform"  onSubmit="return confirm('Are you sure you want to dispatch the selected batches?');" >
                        {{ csrf_field() }}

                        <table class="table table-striped table-bordered table-hover data-table" >
                            <thead>
                                <tr>
                                    <th> Check </th>
                                    <th> Batch No </th>
                                    <th> Facility </th>
                                    <th> Email Address </th>
                                    <th> Date Received </th>
                                    <th> Date Entered </th>
                                    <th> Delay(days) </th>  
                                    <th> No. of Samples </th>
                                    <th> Rejected </th>
                                    <th> Results </th>
                                    <th> No Results </th>
                                    <th> Redraw </th>
                                    <th> Status </th>
                                    <th> Task </th>            
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($batches as $batch)
                                    <tr>
                                        <td>
                                            <div align='center'>
                                                <input name='batches[]' type='checkbox' id='batches[]' value='{{ $batch->id }}' />
                                            </div>
                                        </td>
                                        <td> {{ $batch->id }} </td>
                                        <td> {{ $batch->name }} </td>
                                        <td> {{ $batch->email }} </td>
                                        <td> {{ $batch->my_date_format('datereceived') }} </td>
                                        <td> {{ $batch->my_date_format('created_at') }} </td>
                                        <td> {{ $batch->tat() }} </td>
                                        <td> {{ $batch->total }} </td>
                                        <td> {{ $batch->rejected }} </td>
                                        <td> {{ $batch->result }} </td>
                                        <td> {{ $batch->failed }} </td>
                                        <td> {{ $batch->redraw }} </td>
                                        <td> {{ $batch->status }} </td>
                                        <td>
                                            <a href="{{ url('/viralbatch/'' . $batch->id) }}">View</a> </td>
                                        </td>
                                    </tr>
                                @endforeach


                                @php
                                    // echo $rows;
                                @endphp 
                            </tbody>
                        </table>

                        <input type="submit" name="Proceed to Confirm Selected Dispatch ">
                    </form>
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