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
                @if(Auth::user()->user_type_id != 5)
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
                                        <input type="hidden" name="samples_log" value="1">
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
                                        <input type="hidden" name="samples_log" value="1">
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
                @endif

                <div class="hpanel">
                    <div class="alert alert-success">
                        <center>Test Outcome Report [ All Tested Samples ]</center>
                    </div>
                    <div class="panel-body">
                        @if(Auth::user()->user_type_id != 5)
                        <div class="alert alert-warning">
                            <center>Please select Overall <strong>or Province or County or District or Facility & Period To generate the report based on your criteria.</strong></center>
                        </div>
                        @endif
                        {{ Form::open(['url'=>'/reports', 'method' => 'post', 'class'=>'form-horizontal', 'id' => 'reports_form']) }}
                        <div class="form-group">
                            <div class="row">
                                <label class="col-sm-3 control-label">
                                    <input type="radio" name="category" class="i-checks" value="overall">Overall
                                </label>
                                <div class="col-sm-9">
                                    << For all samples tested @if(Auth::user()->user_type_id == 5) for {{ session('logged_facility')->name ?? ''  }} @else in Lab @endif >>
                                </div>
                            </div>
                            @if(Auth::user()->user_type_id != 5)
                            <div class="row">
                                <label class="col-sm-3 control-label">
                                    <input type="radio" name="category" value="county" class="i-checks">Select County
                                </label>
                                <div class="col-sm-9">
                                    <select class="form-control" id="report_county_search" name="county[]" multiple="multiple"></select>
                                </div>
                            </div>
                            <div class="row">
                                <label class="col-sm-3 control-label">
                                    <input type="radio" name="category" value="partner" class="i-checks">Select Partner
                                </label>
                                <div class="col-sm-9">
                                    <select class="form-control" id="report_partner_search" name="partner"></select>
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
                            @endif
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Select Period</label>
                            <div class="col-sm-10">
                                <!-- <select class="form-control" id="period">
                                    <option selected="true" disabled="true">Select Time Frame</option>
                                    <option value="weekly">Date Range</option>
                                    <option value="monthly">Monthly</option>
                                    <option value="quarterly">Quarterly</option>
                                    <option value="annually">Annually</option>
                                </select> -->
                                <label> <input type="radio" name="period" value="range"> Date Range </label>
                                <label> <input type="radio" name="period" value="monthly"> Monthly </label>
                                <label> <input type="radio" name="period" value="quarterly"> Quarterly </label>
                                <label> <input type="radio" name="period" value="annually"> Annually </label>
                            </div>
                            <div class="row" id="periodSelection" style="display: none;">
                                <div class="col-md-9  col-md-offset-3" id="rangeSelection">
                                    <table cellpadding="1" cellspacing="1" class="table table-condensed">
                                        <tbody>
                                            <tr>
                                                <th>Select Date Range From: </th>
                                                <td>
                                                    <div class="input-group date">
                                                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                                        <input type="text" id="fromDateCat" class="form-control lockable" name="fromDate">
                                                    </div>
                                                </td>
                                                <td><center>To:</center></td>
                                                <td>
                                                    <div class="input-group date">
                                                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                                        <input type="text" id="toDateCat" class="form-control lockable" name="toDate">
                                                    </div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="col-md-9  col-md-offset-3" id="monthSelection">
                                    <table cellpadding="1" cellspacing="1" class="table table-condensed">
                                        <tbody>
                                            <tr>
                                                <th>Select Year and Month </th>
                                                <td>
                                                    <select class="form-control" id="year" name="year" style="width: 100%;">
                                                        <option selected="true" disabled="true">Select a Year</option>
                                                        @for ($i = 0; $i <= 6; $i++)
                                                            @php
                                                                $year=gmdate('Y')-$i
                                                            @endphp
                                                        <option value="{{ $year }}">{{ $year }}</option>
                                                        @endfor
                                                    </select>
                                                </td>
                                                <td>
                                                    <select class="form-control" id="month" name="month" style="width: 100%;">
                                                        <option selected="true" disabled="true">Select a Month</option>
                                                        @for($i = 1; $i <= 12; ++$i)
                                                            <option value="{{ $i }}">{{ date("F", strtotime(date("Y") ."-". $i ."-01")) }}</option>
                                                        @endfor
                                                    </select>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>    
                                </div>
                                <div class="col-md-9  col-md-offset-3" id="quarterSelection">
                                    <table cellpadding="1" cellspacing="1" class="table table-condensed">
                                        <tbody>
                                            <tr>
                                                <th>Select Year and Quarter </th>
                                                <td>
                                                    <select class="form-control" id="year" name="year" style="width: 100%;">
                                                        <option selected="true" disabled="true">Select a Year</option>
                                                        @for ($i = 0; $i <= 6; $i++)
                                                            @php
                                                                $year=gmdate('Y')-$i
                                                            @endphp
                                                        <option value="{{ $year }}">{{ $year }}</option>
                                                        @endfor
                                                    </select>
                                                </td>
                                                <td>
                                                    <select class="form-control" id="quarter" name="quarter" style="width: 100%;">
                                                        <option selected="true" disabled="true">Select a Quarter</option>
                                                        @for ($i = 1; $i <= 4; $i++)
                                                            <option value="Q{{ $i }}">Q{{ $i }}</option>
                                                        @endfor
                                                    </select>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>    
                                </div>
                                <div class="col-md-9  col-md-offset-3" id="yearSelection">
                                    <table cellpadding="1" cellspacing="1" class="table table-condensed">
                                        <tbody>
                                            <tr>
                                                <th>Select Year </th>
                                                <td>
                                                    <select class="form-control" id="year" name="year" style="width: 100%;">
                                                        <option selected="true" disabled="true">Select a Year</option>
                                                       @for ($i = 0; $i <= 6; $i++)
                                                            @php
                                                                $year=gmdate('Y')-$i
                                                            @endphp
                                                        <option value="{{ $year }}">{{ $year }}</option>
                                                        @endfor
                                                    </select>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>    
                                </div>
                            </div>
                        </div> 
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Select Report Type</label>
                            <div class="col-sm-9">
                                <label> <input type="radio" name="types" value="tested" class="i-checks" required> All Samples Tested </label>
                                <label> <input type="radio" name="types" value="awaitingtesting" class="i-checks" required> All Samples Awaiting Testing </label>
                                @if(Auth::user()->user_type_id != 5)
                                @if(Session('testingSystem') == 'EID')
                                <label> <input type="radio" name="types" value="positives" class="i-checks" required> Positives </label>
                                @endif
                                @endif
                                <!-- <label> <input type="radio" name="types" value="worksheetsrun" class="i-checks" required> Worksheets Run </label> -->
                                <label> <input type="radio" name="types" value="rejected" class="i-checks" required> Rejected Samples </label>
                                @if(Auth::user()->user_type_id == 5)
                                <label> <input type="radio" name="types" value="poc" class="i-checks" required> All POC Samples Tested </label>
                                @else
                                <label> <input type="radio" name="types" value="remoteentry" class="i-checks" required> @if(Session('testingSystem') == 'EID') EID @elseif(Session('testingSystem') == 'Viralload') VL @endif Site Entry Samples </label>
                                <label> <input type="radio" name="types" value="remoteentrydoing" class="i-checks" required> @if(Session('testingSystem') == 'EID') EID @elseif(Session('testingSystem') == 'Viralload') VL @endif Sites Doing Remote Entry </label>
                                <label> <input type="radio" name="types" value="sitessupported" class="i-checks" required> @if(Session('testingSystem') == 'EID') EID @elseif(Session('testingSystem') == 'Viralload') VL @endif Sites Sending Samples to Lab </label>
                                @endif                                
                                <label> <input type="radio" name="types" value="tat" class="i-checks" required> TAT Report </label>
                                <label><input type="radio" name="types" value="failed" class="i-checks" required> Failed Tests</label>
                                @if(Auth::user()->user_type_id == 5)
                                <label><input type="radio" name="types" value="manifest" class="i-checks" required> Print/Generate Sample Manifest</label>
                                @endif
                            </div>
                        </div>
                        @if(Auth::user()->user_type_id == 5)
                        <input type="hidden" name="testtype" value="{{ $testtype }}">
                        @endif
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

        $(".date").datepicker({
            startView: 0,
            todayBtn: "linked",
            keyboardNavigation: false,
            forceParse: true,
            autoclose: true,
            endDate: new Date(),
            dateFormat: 'MM yy'
        });

        set_select_facility("report_facility_search", "{{ url('facility/search') }}", 3, "Search for facility", false);
        set_select_facility("report_district_search", "{{ url('district/search') }}", 3, "Search for Sub-County", false);
        set_select_facility("report_county_search", "{{ url('county/search') }}", 1, "Search for County", false);
        set_select_facility("report_partner_search", "{{ url('partner/search') }}", 1, "Search for Partner", false);

    @endcomponent
    <script type="text/javascript">
        $(document).ready(function(){
            // $('.period').click(function(){
            $('input[name="period"]').change(function(){
                period = $(this).val();
                $('#periodSelection').show();
                $('#rangeSelection').hide();
                $('#monthSelection').hide();
                $('#quarterSelection').hide();
                $('#yearSelection').hide();
                if (period == 'range') {
                    $('#rangeSelection').show();
                } else if (period == 'monthly') {
                    $('#monthSelection').show();
                } else if (period == 'quarterly') {
                    $('#quarterSelection').show();
                } else if (period == 'annually') {
                    $('#yearSelection').show();
                }
            });

            $("#generate_report").click(function(e){
                var selValue = $('input[name=category]:checked').val();
                if (selValue == 'county') {
                    category = $("#report_county_search").val();
                    cat = 'County';
                } else if (selValue == 'subcounty') {
                    category = $("#report_district_search").val();
                    cat = 'Sub-County';
                } else if (selValue == 'partner') {
                    category = $("#report_partner_search").val();
                    cat = 'Partner';
                } else if (selValue == 'facility') {
                    category = $("#report_facility_search").val();
                    cat = 'Facility';
                }

                if(category == '' || category == null || category == undefined) {
                    e.preventDefault();
                    set_warning("No "+cat+" Selected</br /></br />Please Select a "+cat+" from the dropdown");
                }

                // var perValue = $('input[name=period]:checked').val();
                // alert(perValue);
                // var $radios = $('input[name="period"]');
            });
        });
    </script>
@endsection