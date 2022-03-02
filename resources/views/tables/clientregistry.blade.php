{{--@extends('layouts.master')--}}

{{--@component('/tables/css')--}}
{{--@endcomponent--}}

{{--@section('content')--}}

{{--<div class="main-panel">--}}
{{--    <div class="content-wrapper">--}}
{{--        <div class="row">--}}
{{--            <div class="col-lg-6 grid-margin stretch-card">--}}
{{--                <div class="card">--}}
{{--                    <div class="card-body">--}}
{{--                        <h4 class="card-title">Patient Registry</h4>--}}
{{--                        --}}{{--                        <form action="{{ route('search') }}"  method="GET">--}}
{{--                        <form>--}}
{{--                            <input class="input-group-btn fa fa-search" type="text" name="search" required placeholder="Search Patient" style="border-radius: 12px; height: 35px; padding-left: 15px;"/>--}}
{{--                            --}}{{--                            <button type="submit" class="btn btn-outline-info" >Search</button>--}}
{{--                        </form>--}}
{{--                        <p class="card-description">--}}
{{--                        </p>--}}
{{--                        <div class="table-responsive">--}}
{{--                            <table class="table">--}}
{{--                                <thead>--}}
{{--                                <tr>--}}
{{--                                    <th>CCC NO.</th>--}}
{{--                                    <th>Patient Name</th>--}}
{{--                                    <th>ID NO</th>--}}
{{--                                    <th>FACILITY</th>--}}
{{--                                    <th>STATUS</th>--}}
{{--                                    <th>TOOLS</th>--}}
{{--                                </tr>--}}
{{--                                </thead>--}}
{{--                                <tbody>--}}
{{--                                --}}{{--                                @foreach ($users as $user)--}}
{{--                                <tr style="margin-left: 20px;">--}}
{{--                                    <td>Test</td>--}}
{{--                                    <td>Test</td>--}}
{{--                                    <td>Test</td>--}}
{{--                                    <td>Test</td>--}}
{{--                                    <td>Test</td>--}}
{{--                                    --}}{{--                                        <td>{{ $user->id }}</td>--}}
{{--                                    --}}{{--                                        <td>{{ $user->fullname}}</td>--}}
{{--                                    --}}{{--                                        --}}{{----}}{{--                              <td style="color: green; font-weight: bold; font-size: 24px;" >Ksh {{ $user->accbalance}}</td>--}}
{{--                                    --}}{{--                                        --}}{{----}}{{--                              <td>{{ $user->email }}</td>--}}
{{--                                    --}}{{--                                        <td>{{ $user->phone}}</td>--}}
{{--                                    --}}{{--                                        <td>{{ $user->username}}</td>--}}
{{--                                    --}}{{--                                        <td>--}}
{{--                                    --}}{{--                                            @if($user->accstatus == 0)--}}
{{--                                    --}}{{--                                                <span class="badge badge-warning" style="font-size: 15px;">Pending</span>--}}
{{--                                    --}}{{--                                            @elseif($user->accstatus == 1)--}}
{{--                                    --}}{{--                                                <span class="badge badge-primary" style="font-size: 15px;">Active</span>--}}
{{--                                    --}}{{--                                            @else--}}
{{--                                    --}}{{--                                                <span class="badge badge-danger" style="font-size: 15px;">Invalid</span>--}}
{{--                                    --}}{{--                                            @endif--}}
{{--                                    --}}{{--                                        </td>--}}
{{--                                </tr>--}}
{{--                                --}}{{--                                @endforeach--}}
{{--                                </tbody>--}}
{{--                            </table>--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--            </div>--}}
{{--        </div>--}}
{{--    </div>--}}
{{--</div>--}}
{{--@endsection--}}

{{--@section('scripts')--}}

{{--    @component('/tables/scripts')--}}

{{--    @endcomponent--}}

{{--@endsection--}}

@extends('layouts.master')

@component('/tables/css')
@endcomponent

@section('content')

    <div class="content">
        <div class="row">
            <div class="col-sm-12">
                <div class="hpanel">
                    <div class="panel-heading">
                        <div class="panel-tools">
                            {{-- <a class="showhide"><i class="fa fa-chevron-up"></i></a> --}}
                            <!-- <a class="closebox"><i class="fa fa-times"></i></a> -->
                        </div>
                      <h4 style="font-weight: bold"> PATIENTS REGISTRY</h4>
                    </div>
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="hpanel">
                                <div class="form-group">
                                    <div class="col-sm-12">
                                        <input class="form-control" id="search" placeholder="Search for Patients Here">
                                    </div>
{{--                                    <button class="btn btn-success">Search</button>--}}
                                </div>

                            </div>
                        </div>
                    </div>
                    <br>
                    <div class="panel-body">
                        <div class="table-responsive">

                            <form  method="post" name="worksheetform"

{{--                                   @if($batch_list)--}}
                                   onSubmit="return confirm('Are you sure you want to dispatch the selected batches?');"
{{--                                    @endif--}}

                            >
                                @csrf

{{--                                @if($batch_list)--}}
                                    <input type="hidden" name="final_dispatch" value=1>
{{--                                @endif--}}


                                <table class="table table-striped table-bordered table-hover" >
                                    <thead>
                                    <tr>
                                        <th> CCC NO. </th>
{{--                                        <th> FACILITY</th>--}}
                                        <th> PATIENT NAME</th>
                                        <th>PATIENT STATUS</th>
                                        <th> FACILITY CODE</th>
                                        <th> PATIENT PHONE</th>
                                        <th>REFERRED FROM SITE</th>
                                        <th> GENDER</th>
                                        <th> DOB </th>
                                        <th> DATE INITIATED ON TREATMENT</th>
                                        {{-- <th style="color: red; "> TOOLS</th> --}}
{{--                                        <th> Redraw </th>--}}
{{--                                        <th> Failed </th>--}}
{{--                                        <th> Delay(days) </th>--}}
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($users as $batch)
                                        <tr>
                                            <td> {{ $batch->ccc_no }} </td>
                                            <td> {{ $batch->patient_name }} </td>
                                            <td> {{ $batch->patient_status }} </td>
                                            <td> {{ $batch->facility_id }} </td>
                                            <td> {{ $batch->patient_phone_no }} </td>
                                            <td> {{ $batch->referredfromsite}} </td>
                                            <td> {{ $batch->sex }} </td>
                                            <td> {{ $batch->dob }} </td>
                                            <td> {{ $batch->dateinitiatedontreatment }} </td>
                                            {{-- <td ><button class="btn btn-success fa fa-check">UPDATE</button></td> --}}
{{--                                            <td ><button class=" btn btn-danger fa fa-remove">DELETE</button></td>--}}
                                        </tr>

                                    @endforeach
{{--                                    @php--}}
{{--                                        // echo $rows;--}}
{{--                                    @endphp--}}
                                    </tbody>
                                </table>


                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


@endsection

@section('scripts')

    @component('/tables/scripts')

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