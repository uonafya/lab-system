@extends('layouts.master')

    @component('/tables/css')
    @endcomponent

@section('content')
<div class="content">
    <div class="row">
        <div class="col-lg-12">
            <div class="hpanel">
                <div class="panel-body">
                    <table class="table table-striped table-bordered table-hover data-table" style="font-size: 10px;" >
                        <thead>
                            <tr class="colhead">
                                <th rowspan="2">MFL</th>
                                <th rowspan="2">Facility Name</th>
                                <th rowspan="2">County</th>
                                <th rowspan="2">District</th>
                                <th colspan="4">Facility</th>
                                <th colspan="4">Contact Person</th>
                                <th rowspan="2">G4S Branch</th>
                                <th rowspan="2">Task</th>
                            </tr>
                            <tr>
                                <th>Phone 1</th>
                                <th>Phone 2</th>
                                <th>Email</th>
                                <th>SMS Printer #</th>
                                <th>Names</th>
                                <th>Phone 1</th>
                                <th>Phone 2</th>
                                <th>Contact Email</th>    
                            </tr>
                        </thead>
                        <tbody>
                        	@php 
                        		echo $row 
                        	@endphp
                        </tbody>
                    </table>
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