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
                        <table class="table table-striped table-bordered table-hover" id="mytable">
                            <thead>
                                <tr>
                                    <th>Facility</th>
                                    <th>Patient</th>
                                    <th>Full Name</th>
                                    <th>Age</th>
                                    <th>Phone #</th>
                                    <th>Date Collected</th>
                                    <th>Date Tested</th>
                                    <th>Result</th>
                                    <th>Date Dispatched</th>
                                    <th>Date SMS Sent</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
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

        var dt = $('#mytable').DataTable( {
            'responsive' : true,
            'processing' : true,
            'serverSide' : true,
            'ajax' : {
                'url' : "{{ url('datatable/sms_log/' . $type) }}",
                'type' : 'POST'
            },
            'columns' : [
                { 'data' : 'facilityname' },
                { 'data' : 'patient' },
                { 'data' : 'patient_name' },
                { 'data' : 'age' },
                { 'data' : 'patient_phone_no' },
                { 'data' : 'datecollected' },
                { 'data' : 'datetested' },
                { 'data' : 'result' },
                { 'data' : 'datedispatched' },
                { 'data' : 'time_result_sms_sent' },
                { 'data' : 'action',  'orderable' : false, 'searchable' : false},
            ],
            'order' : [['8', 'desc']]

        } );

    @endcomponent

@endsection