@extends('layouts.tasks')

@section('css_scripts')
    
@endsection

@section('custom_css')
	<style type="text/css">
		.input-edit {
            background-color: #FFFFCC;
        }
	</style>
@endsection

@section('content')
@php
    $prevmonth = date('m')-1;
@endphp
<div class="row">
    <div class="col-md-12">
        <div class="hpanel" style="margin-top: 1em;margin-right: 18%;">
            <div class="panel-body" style="padding: 20px;box-shadow: none; border-radius: 0px;">
                {{ Form::open(['url' => '/kitsdeliveries', 'method' => 'post', 'class'=>'form-horizontal']) }}
                <div class="form-group">
                    <div class="form-group" style="/*display: none;" id="platformDiv">
                        <label class="col-sm-4 control-label"><center>Platform</center></label>
                        <div class="col-sm-8">
                            <select class="form-control input-sm" required name="platform" id="platform">
                                <option value="" selected>Select Platform</option>
                                <option value="1">COBAS/TAQMAN</option>
                                <option value="2">ABBOTT</option>
                            </select>
                        </div>
                    </div>                    
                </div>

                <!-- TAQMAN DIV -->
                    <div id="taqman" style="display: none;">
                        @foreach($data->testtypes as $types)
                            <div class="alert alert-warning">
                                <center><i class="fa fa-bolt"></i> Please enter {{ $types }} values below.</center>
                            </div>
                            <table class="table table-striped table-bordered table-hover data-table" style="font-size: 10px;margin-top: 1em;">
                                <thead>               
                                    <tr>
                                        <th rowspan="2">NAME OF COMMODITY</th>
                                        <th rowspan="2">UNIT OF ISSUE</th>
                                        <th rowspan="2">BEGINNING BALANCE</th>
                                        <th colspan="2">QUANTITY RECEIVED FROM CENTRAL WAREHOUSE(KEMSA/SCMS/RDC)</th>
                                        <th rowspan="2">QUANTITY USED</th>
                                        <th rowspan="2">LOSSES / WASTAGE</th>
                                        <th colspan="2">ADJUSTMENTS</th>
                                        <th rowspan="2">ENDING BALANCE (PHYSICAL COUNT)</th>
                                        <th rowspan="3">QUANTITY REQUESTED</th>
                                    </tr>
                                    <tr>
                                        <th>Quantity</th>
                                        <th>Lot No.</th>
                                        <th>Positive<br />(Received other source)</th>
                                        <th>Negative<br />(Issued Out)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($data->taqmanKits as $kits)
                                    <tr>
                                        <td>{{ $kits['name'] }}</td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @endforeach
                        <div class="col-sm-12">
                            <center>
                            <button class="btn btn-success" type="submit" name="saveAbbott" value="saveTaqman">Save Taqman Kit Consumption</button>
                            <button class="btn btn-primary" type="submit" name="discard" value="add">Discard Changes</button>
                            </center>
                        </div>
                    </div>
                <!-- TAQMAN DIV -->

                <!-- ABBOTT DIV -->
                    <div id="abbott" style="display: none;">
                        

                        <div class="col-sm-12">
                            <center>
                            <button class="btn btn-success" type="submit" name="saveAbbott" value="saveAbbott">Save Abbott Kit Consumption</button>
                            <button class="btn btn-primary" type="submit" name="discard" value="add">Discard Changes</button>
                            </center>
                        </div>
                    </div>
                <!-- ABBOTT DIV -->
                {{ Form::close() }}
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
    @component('/forms/scripts')
        @slot('js_scripts')
            
        @endslot


        @slot('val_rules')
           
        @endslot

    @endcomponent
    <script type="text/javascript">
        $(function(){
            $("#platform").change(function(){
                platform = $(this).val();

                if (platform == 1) {
                    $("#abbott").hide();
                    $("#taqman").show();
                } else if (platform == 2) {
                    $("#taqman").hide();
                    $("#abbott").show();
                }
            });
        });
    </script>
@endsection