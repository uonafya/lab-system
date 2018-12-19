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
                                    <th rowspan="2">#</th>
                                    <th rowspan="2">Full Names</th>
                                    <th colspan="2"><center>Saples Entered (2018, Nov)</center></th>
                                    <th colspan="2"><center>Site Sampes Approved (2018, Nov)</center></th>
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
                                    <td>{{ $user->samples_entered('EID', 2018, 11) }}</td>
                                    <td>{{ $user->samples_entered('VL', 2018, 11) }}</td>
                                    <td>{{ $user->sitesamplesapproved('EID', 2018, 11) }}</td>
                                    <td>{{ $user->sitesamplesapproved('VL', 2018, 11) }}</td>
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