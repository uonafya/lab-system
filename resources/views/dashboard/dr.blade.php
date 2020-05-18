@extends('layouts.master')

@section('content')
<style type="text/css">
    .list-group {
        margin-top: 10px;
        margin-bottom: 10px;
    }
    .list-group-item {
        margin-bottom: 4px;
        margin-top: 4px;
    }
</style>
<div class="p-lg">
    <div class="content animate-panel" data-child="hpanel" style="background-color: white;">
        <div class="row">
            <div class="col-lg-12">
                <div class="hpanel">
                    <div class="panel-body no-padding">

                        <select class="form-control" id="county_id">
                            <option></option>
                            @foreach($counties as $county)
                                <option value="{{ $county->id }}"> {{ $county->name }} </option>
                            @endforeach                            
                        </select>

                        <select class="form-control" id="subcounty_id">
                            <option></option>
                            @foreach($subcounties as $subcounty)
                                <option value="{{ $subcounty->id }}"> {{ $subcounty->name }} </option>
                            @endforeach                            
                        </select>

                    </div>
                </div>
            </div>
        </div>
    <!-- <div class="animate-panel"  data-child="hpanel" data-effect="fadeInDown"> -->
        <div class="row">
            <div class="col-lg-12">
                <div class="hpanel">
                    <div class="alert alert-success">
                        <center> Resistance by Drug </center>
                    </div>
                    <div class="panel-body no-padding">
                        <div id="resistance_by_drug"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection()

@section('scripts')
<script src="{{ asset('vendor/highcharts/highcharts.js' )}}"></script>
<script src="{{ asset('vendor/highcharts/modules/data.js' )}}"></script>
<script src="{{ asset('vendor/highcharts/modules/series-label.js' )}}"></script>
<script src="{{ asset('vendor/highcharts/modules/exporting.js' )}}"></script>

<script type="text/javascript">

    function reload_page()
    {
        $("#resistance_by_drug").html("<center><div class='loader'></div></center>");

        $("#resistance_by_drug").load("{{ url('dr_dashboard/drug_resistance') }}");

    }

    $().ready(function(){

        $("#county_id").select2({
            placeholder: "Select County",
            allowClear: true
        }); 

        $("#subcounty_id").select2({
            placeholder: "Select Subcounty",
            allowClear: true
        }); 
        
        reload_page();

    });
    
</script>
@endsection