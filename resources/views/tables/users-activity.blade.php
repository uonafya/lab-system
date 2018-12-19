@extends('layouts.master')

    @component('/tables/css')
    @endcomponent

@section('content')

<div class="content">
    <div class="row">
        <div class="col-lg-12">
            <div class="row" style="margin-bottom: 1em;">
                <!-- Year -->
                <div class="col-md-6">
                    <center><h5>Year Filter</h5></center>
                    @for ($i = 0; $i <= 9; $i++)
                        @php
                            $year=Date('Y')-$i
                        @endphp
                        <a href='{{ url("user/activity/null/$year") }}'>{{ Date('Y')-$i }}</a> |
                    @endfor
                </div>
                <!-- Year -->
                <!-- Month -->
                <div class="col-md-6">
                    <center><h5>Month Filter</h5></center>
                    @for ($i = 1; $i <= 12; $i++)
                        <a href='{{ url("user/activity/null/null/$i") }}'>{{ date("F", strtotime(date("Y") ."-". $i ."-01")) }}</a> |
                    @endfor
                </div>
                <!-- Month -->
            </div>
            <div class="hpanel">
                <div class="panel-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered table-hover" >
                            <thead>
                                <tr>
                                    <th rowspan="2">#</th>
                                    <th rowspan="2">Full Names</th>
                                    <th colspan="2"><center>Samples Entered ({{ $data->year }}, {{ $data->monthName }})</center></th>
                                    <th colspan="2"><center>Site Samples Approved ({{ $data->year }}, {{ $data->monthName }})</center></th>
                                    <!-- <th rowspan="2">Action</th> -->
                                </tr>
                                <tr>
                                    <th><center>EID</center></th>
                                    <th><center>VL</center></th>
                                    <th><center>EID</center></th>
                                    <th><center>VL</center></th>
                                </tr>
                            </thead>
                            <tbody>
                            @foreach($users as $key => $user)
                                <tr>
                                    <td>{{ $key+1 }}</td>
                                    <td>{{ $user->full_name }}</td>
                                    <td>{{ $user->samples_entered('EID', $data->year, $data->month) }}</td>
                                    <td>{{ $user->samples_entered('VL', $data->year, $data->month) }}</td>
                                    <td>{{ $user->sitesamplesapproved('EID', $data->year, $data->month) }}</td>
                                    <td>{{ $user->sitesamplesapproved('VL', $data->year, $data->month) }}</td>
                                    <!-- <td><a href='{{-- url("users/activity/$user->id") --}}'>View Log</a></td> -->
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