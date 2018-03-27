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
                    <form  method="post" action="{{ url('batch/complete_dispatch') }}  " name="worksheetform"  onSubmit="return confirm('Are you sure you want to dispatch the selected batches?');" >
                        {{ csrf_field() }}

                        <table class="table table-striped table-bordered table-hover data-table" >
                            <thead>
                                <tr>
                                    <th> Check </th>
                                    <th> Batch No </th>
                                    <th> Facility </th>
                                    <th> Email Address </th>
                                    <th> Date Received </th>
                                    <th> No. of Samples </th>
                                    <th> Rejected </th>
                                    <th> Date Tested </th>
                                    <th> Date Updated </th>
                                    <th> Positive </th>
                                    <th> Negative </th>
                                    <th> Redraw </th>
                                    <th> Failed </th>
                                    <th> Delay(days) </th>              
                                </tr>
                            </thead>
                            <tbody>

                                @php
                                    echo $rows;
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