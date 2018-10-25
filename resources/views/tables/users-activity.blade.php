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
                                    <th colspan="2"><center>Uploaded Worksheets (Today)</center></th>
                                    <th colspan="2"><center>Reviewed Worksheets (Today)</center></th>
                                    <th rowspan="2">Action</th>
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
                                    <td>{{ $user->uploaded(gmdate("Y-m-d"))->eid }}</td>
                                    <td>{{ $user->uploaded(gmdate("Y-m-d"))->vl }}</td>
                                    <td>{{ $user->reviewed(gmdate("Y-m-d"))->eid }}</td>
                                    <td>{{ $user->reviewed(gmdate("Y-m-d"))->vl }}</td>
                                    <td><a href='{{ url("users/activity/$user->id") }}'>View Log</a></td>
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