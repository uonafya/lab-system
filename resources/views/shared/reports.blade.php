@extends('layouts.master')

@component('/forms/css')
    <link href="{{ asset('css/datapicker/datepicker3.css') }}" rel="stylesheet" type="text/css">
@endcomponent

@section('css_scripts')

@endsection

@section('custom_css')
    <style type="text/css">
        .form-horizontal .control-label {
                text-align: left;
            }
    </style>
@endsection

@section('content')
<div class="p-lg">
    <div class="content animate-panel" data-child="hpanel">
        <div class="row">
            <div class="col-lg-10 col-lg-offset-1">
                <div class="hpanel">
                    <div class="alert alert-success">
                        <center>Sample Log [ All Recevied Samples ]</center>
                    </div>
                    <div class="panel-body">
                        <div class="alert alert-warning">
                            <center>Please select a date or a date range to view the samples received at lab in that duration</center>
                        </div>
                        <div class="table-responsive" style="padding-left: 15px;padding-top: 2px;padding-bottom: 2px;padding-right: 15px;">
                            <table cellpadding="1" cellspacing="1" class="table table-condensed">
                                <tbody>
                                    <tr>
                                        {{ Form::open(['url'=>'/reports/dateselect', 'method' => 'post', 'class' => 'form-horizontal', 'id' => 'reports_dateSelect_form']) }}
                                        <td>Select Date:</td>
                                        <td>
                                            <div class="input-group date">
                                                <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                                <input type="text" id="specificDate" required class="form-control lockable" name="specificDate">
                                            </div>
                                        </td>
                                        <td>
                                            <button class="btn btn-default" id="specificDateBtn">Download Report</button>
                                        </td>
                                        {{ Form::close() }}
                                    </tr>
                                    <tr>
                                        {{ Form::open(['url'=>'/reports/dateselect', 'method' => 'post', 'id' => 'reports_dateRange_form']) }}
                                        <td>Select Date Range From: </td>
                                        <td>
                                            <div class="input-group date">
                                                <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                                <input type="text" id="fromDate" required class="form-control lockable" name="fromDate">
                                            </div>
                                        </td>
                                        <td><center>To:</center></td>
                                        <td>
                                            <div class="input-group date">
                                                <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                                <input type="text" id="toDate" required class="form-control lockable" name="toDate">
                                            </div>
                                        </td>
                                        <td>
                                            <button class="btn btn-default" id="dateRangeBtn">Download Report</button>
                                        </td>
                                        {{ Form::close() }}
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="hpanel">
                    <div class="alert alert-success">
                        <center>Test Outcome Report [ All Tested Samples ]</center>
                    </div>
                    <div class="panel-body">
                        <div class="alert alert-warning">
                            <center>Please select Overall <strong>or Province or County or District or Facility & Period To generate the report based on your criteria.</strong></center>
                        </div>
                        {{ Form::open(['url'=>'/reports', 'method' => 'post', 'class'=>'form-horizontal', 'id' => 'reports_form']) }}
                        <div class="form-group">
                            <div class="row">
                                <label class="col-sm-3 control-label">
                                    <input type="radio" name="category" class="i-checks" value="overall">Overall
                                </label>
                                <div class="col-sm-9">
                                    << For all samples tested in Lab >>
                                </div>
                            </div>
                            <div class="row">
                                <label class="col-sm-3 control-label">
                                    <input type="radio" name="category" value="province" class="i-checks">Select Province
                                </label>
                                <div class="col-sm-9">
                                    <select class="form-control" id="report_province_search" name="province"></select>
                                </div>
                            </div>
                            <div class="row">
                                <label class="col-sm-3 control-label">
                                    <input type="radio" name="category" value="county" class="i-checks">Select County
                                </label>
                                <div class="col-sm-9">
                                    <select class="form-control" id="report_county_search" name="county"></select>
                                </div>
                            </div>
                            <div class="row">
                                <label class="col-sm-3 control-label">
                                    <input type="radio" name="category" value="subcounty" class="i-checks">Select Sub County
                                </label>
                                <div class="col-sm-9">
                                    <select class="form-control" id="report_district_search" name="district"></select>
                                </div>
                            </div>
                            <div class="row">
                                <label class="col-sm-3 control-label">
                                    <input type="radio" name="category" value="facility" class="i-checks">Select Facility
                                </label>
                                <div class="col-sm-9">
                                    <select class="form-control" id="report_facility_search" name="facility"></select>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Select Period</label>
                            <div class="col-sm-10">
                                <label> <input type="radio" name="period" value="weekly" class="i-checks"> Weekly / Date Range </label>
                                <label> <input type="radio" name="period" value="monthly" class="i-checks"> Monthly </label>
                                <label> <input type="radio" name="period" value="quarterly" class="i-checks"> Quarterly </label>
                                <label> <input type="radio" name="period" value="biannually" class="i-checks"> Bi-Annually </label>
                                <label> <input type="radio" name="period" value="annually" class="i-checks"> Annually </label>
                            </div>
                        </div> 
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Select Report Type</label>
                            <div class="col-sm-9">
                                <label> <input type="radio" name="period" value="tested" class="i-checks"> Tested Samples </label>
                                <label> <input type="radio" name="period" value="rejected" class="i-checks"> Rejected Samples </label>
                            </div>
                        </div>

                        <div class="form-group">
                            <center>
                                <button type="submit" class="btn btn-default" id="generate_report">Generate Report</button>
                                <button class="btn btn-default">Reset Options</button>
                            </center>
                        </div>                  
                        {{ Form::close() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
    @component('/forms/scripts')
        @slot('js_scripts')
            <script src="{{ asset('js/datapicker/bootstrap-datepicker.js') }}"></script>
        @endslot

        $(".date").datepicker({
            startView: 0,
            todayBtn: "linked",
            keyboardNavigation: false,
            forceParse: true,
            autoclose: true,
            endDate: new Date(),
            format: "yyyy-mm-dd"
        });

        set_select_facility("report_facility_search", "{{ url('facility/search') }}", 3, "Search for facility", false);
        set_select_facility("report_district_search", "{{ url('district/search') }}", 3, "Search for Sub-County", false)
        set_select_facility("report_county_search", "{{ url('county/search') }}", 1, "Search for County", false);
        set_select_facility("report_province_search", "{{ url('province/search') }}", 1, "Search for Province", false)

    @endcomponent
    <script type="text/javascript">
        $(document).ready(function(){
            $("#generate_report").click(function(e){
                e.preventDefault();
                var selValue = $('input[name=category]:checked').val(); 
                if (selValue == 'province') {
                    prov = $("#report_province_search").val();
                    if(prov == '' || prov == null || prov == undefined) {
                        set_warning("No Province Selected</br /></br />Please Select a Province from the dropdown");
                    }
                } else if (selValue == 'county') {
                    county = $("#report_county_search").val();
                    if(county == '' || county == null || county == undefined) {
                        set_warning("No County Selected</br /></br />Please Select a County from the dropdown");
                    }
                } else if (selValue == 'subcounty') {
                    dist = $("#report_district_search").val();
                    if(dist == '' || dist == null || dist == undefined) {
                        set_warning("No Sub-County Selected</br /></br />Please Select a Sub-County from the dropdown");
                    }
                } else if (selValue == 'facility') {
                    fac = $("#report_facility_search").val();
                    if(fac == '' || fac == null || fac == undefined) {
                        set_warning("No Facility Selected</br /></br />Please Select a Facility from the dropdown");
                    }
                }
            });
        });
    </script>
@endsection