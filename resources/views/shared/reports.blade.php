@extends('layouts.master')

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
                                        <td>Select Date:</td>
                                        <td>
                                            <input type="" name="">
                                            <button class="btn btn-default">Download Report</button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Select Date Range From: </td>
                                        <td><input type="" name=""></td>
                                        <td><center>To:</center></td>
                                        <td><input type="" name=""></td>
                                        <td>Download Report</td>
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
                        {{ Form::open(['url'=>'/report', 'method' => 'post', 'class'=>'form-horizontal', 'id' => 'reports_form']) }}
                        <div class="form-group">
                            <div class="row">
                                <label class="col-sm-3 control-label">
                                    <input type="radio" name="category" class="i-checks" required>Overall
                                </label>
                                <div class="col-sm-9">
                                    << For all samples tested in Lab >>
                                </div>
                            </div>
                            <div class="row">
                                <label class="col-sm-3 control-label">
                                    <input type="radio" name="category" class="i-checks">Select Province
                                </label>
                                <div class="col-sm-9">
                                    <select class="form-control" id="report_province_search" name="province"></select>
                                </div>
                            </div>
                            <div class="row">
                                <label class="col-sm-3 control-label">
                                    <input type="radio" name="category" class="i-checks">Select County
                                </label>
                                <div class="col-sm-9">
                                    <select class="form-control" id="report_county_search" name="county"></select>
                                </div>
                            </div>
                            <div class="row">
                                <label class="col-sm-3 control-label">
                                    <input type="radio" name="category" class="i-checks">Select Sub County
                                </label>
                                <div class="col-sm-9">
                                    <select class="form-control" id="report_district_search" name="district"></select>
                                </div>
                            </div>
                            <div class="row">
                                <label class="col-sm-3 control-label">
                                    <input type="radio" name="category" class="i-checks">Select Facility
                                </label>
                                <div class="col-sm-9">
                                    <select class="form-control" id="report_facility_search" name="facility"></select>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Select Period</label>
                            <div class="col-sm-10">
                                <label> <input type="radio" name="period" value="weekly" class="i-checks" checked> Weekly / Date Range </label>
                                <label> <input type="radio" name="period" value="monthly" class="i-checks"> Monthly </label>
                                <label> <input type="radio" name="period" value="quarterly" class="i-checks"> Quarterly </label>
                                <label> <input type="radio" name="period" value="biannually" class="i-checks"> Bi-Annually </label>
                                <label> <input type="radio" name="period" value="annually" class="i-checks"> Annually </label>
                            </div>
                        </div> 
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Select Report Type</label>
                            <div class="col-sm-9">
                                <label> <input type="radio" name="period" value="tested" class="i-checks" checked> Tested Samples </label>
                                <label> <input type="radio" name="period" value="rejected" class="i-checks" checked> Rejected Samples </label>
                            </div>
                        </div>

                        <div class="form-group">
                            <center>
                                <button type="submit" class="btn btn-default">Generate Report</button>
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

@endsection