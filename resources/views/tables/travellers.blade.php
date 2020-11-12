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
                                <tr class="colhead">
                                    <th rowspan="2">Lab ID</th>
                                    <th rowspan="2">Patient Name</th>
                                    <th rowspan="2">National ID/PP</th>
                                    <th rowspan="2">Sex</th>
                                    <th rowspan="2">Age</th>
                                    <th colspan="4">Date</th>
                                    <th colspan="3">Results</th>                                    
                                    <th rowspan="2">Task</th>
                                </tr>
                                <tr>
                                    <th>Collected</th>
                                    <th>Received</th>
                                    <th>Tested</th>
                                    <th>Dispatched</th>  

                                    <th>PCR</th>    
                                    <th>IgM</th>    
                                    <th>IgG/IgM</th>    
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
                'url' : "{{ url('traveller/filter/') }}",
                'type' : 'POST'
            },
            'columns' : [
                { 'data' : 'patient_name' },
                { 'data' : 'id_passport' },
                { 'data' : 'gender' },
                { 'data' : 'age' },
                { 'data' : 'datecollected' },
                { 'data' : 'datereceived' },
                { 'data' : 'datetested' },
                { 'data' : 'datedispatched' },
                { 'data' : 'result', 'orderable' : false, 'searchable' : false },
                { 'data' : 'igm_result', 'orderable' : false, 'searchable' : false },
                { 'data' : 'igg_igm_result', 'orderable' : false, 'searchable' : false },
                { 'data' : 'action', 'orderable' : false, 'searchable' : false},
            ],
            'order' : [[8, 'desc']]

        } );

    @endcomponent

@endsection