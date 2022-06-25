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
                    @if($pre == 'viral')
                    VL
                    @else
                    EID
                    @endif
                    Patients List
                </div>
                <div class="panel-body">
                    <div class="table-responsive">
                        <table class="table table-striped data-table table-bordered table-hover" >
                            <thead>
                            <tr>
                                <th>Patient UPI</th>
                                <th>Patient CCC No	</th>
                                <th>Sex	 </th>
                                <th>DOB</th>
                                <th>Facility MFL</th>
                                <th>Verification Status</th>
                                <th>Action</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($patients as $key => $patient)
                            <tr>
                                <td>  {{ $patient->upi_no }}</td>
                                <td> {{ $patient->patient }} </td>
                                <td> {{ $patient->sex }} </td>
                                <td> {{ $patient->dob ?? '' }} </td>
                                <td> {{ $patient->facilitycode }} </td>
                                <td> verified </td>
                                <td>
                                    @if($patient->upi_no)
                                    <a href="" ><i class='fa fa-print'></i> Verified</a>
                                    @else
                                    <a href="" ><i class='fa fa-print'></i> Verify</a>
                                    @endif
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