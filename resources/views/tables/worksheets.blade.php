@extends('layouts.master')

    @component('/tables/css')
    @endcomponent

@section('content')
 
<div class="table-responsive">
    <table class="table table-striped table-bordered table-hover data-table" >
        <thead>
            <tr class="colhead">
                <th rowspan="2">#</th>
                <th rowspan="2">Date Created</th>
                <th rowspan="2">Created By</th>
                <th rowspan="2">Type</th>
                <th rowspan="2">Status</th>
                <th colspan="6">Samples</th>
                <th colspan="3">Date</th>
                <th rowspan="2">Task</th>
            </tr>
            <tr>
                <th>POS</th>
                <th>NEG</th>
                <th>Failed</th>
                <th>Redraw</th>
                <th>No Result</th>
                <th>Total</th>
                <th>Run</th>
                <th>Updated</th>
                <th>Reviewed</th>                
            </tr>
        </thead>
        <tbody>

            @php
                echo $rows;
            @endphp 
        </tbody>
    </table>
</div>


@endsection

@section('scripts') 

    @component('/tables/scripts')

    @endcomponent

@endsection