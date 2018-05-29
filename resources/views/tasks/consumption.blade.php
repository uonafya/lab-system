@extends('layouts.tasks')

@section('css_scripts')
    
@endsection

@section('custom_css')
	<style type="text/css">
		.input-edit {
            background-color: #FFFFCC;
        }
        .input-edit-danger {
            background-color: #f2dede;
        }
	</style>
@endsection

@section('content')
@php
    $prevmonth = date('m')-1;
    $toedit = ['losses','pos','neg'];
    $plats = ['taqman','abbott'];
@endphp
<div class="row">
    <div class="col-md-12">
        <div class="hpanel" style="margin-top: 1em;margin-right: 6%;">
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
                            <div class="alert alert-danger">
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
                                    @php
                                        $testtype = $types.'teststaq';
                                        $tests = $data->$testtype;
                                        $prevtaqman = 'prevtaqman'.$types;
                                        $taqmandeliveries = 'taqmandeliveries'.$types;
                                        $qualkitused = 0;
                                        $used = null;
                                    @endphp

                                    @foreach ($data->taqmanKits as $kits)
                                    
                                    @php
                                        $prefix = 'ending'.$kits['alias'];
                                        $received = $kits['alias'].'received';
                                        $damaged = $kits['alias'].'damaged';
                                        $lot = $kits['alias'].'lotno';

                                        if ($kits['alias'] == 'qualkit') {
                                            if ($types == 'VL') {
                                                $qualkitused = $tests / 42;
                                            } else if ($types == 'EID') {
                                                $qualkitused = $tests / 44;
                                            }
                                        } else {
                                            $used = round($qualkitused * $kits['factor']);
                                        }
                                    @endphp
                                    <tr>
                                        <td>{{ $kits['name'] }}</td>
                                        <td>{{ $kits['unit'] }}</td>
                                        <td>
                                            <input class="form-control input-edit" type="text" name="taqman{{ $types }}beginingbal{{ $kits['alias'] }}" id="taqman{{ $types }}beginingbal{{ $kits['alias'] }}" value="{{ $data->$prevtaqman->$prefix ?? 0 }}" disabled="true">
                                        </td>
                                        <td>{{ $data->$taqmandeliveries->$received ?? 0 }}</td>
                                        <td>{{ $data->$taqmandeliveries->$lot ?? '-' }}</td>
                                        <td>{{ $used ?? $qualkitused }}</td>
                                        <td>
                                            <input class="form-control input-edit" type="text" name="taqman{{ $types }}losses{{ $kits['alias'] }}" id="taqman{{ $types }}losses{{ $kits['alias'] }}" value="">
                                        </td>
                                        <td>
                                            <input class="form-control input-edit" type="text" name="taqman{{ $types }}pos{{ $kits['alias'] }}" id="taqman{{ $types }}pos{{ $kits['alias'] }}" value="">
                                        </td>
                                        <td>
                                            <input class="form-control input-edit" type="text" name="taqman{{ $types }}neg{{ $kits['alias'] }}" id="taqman{{ $types }}neg{{ $kits['alias'] }}" value="">
                                        </td>
                                        <td>
                                            @php
                                                if($kits['alias'] == 'qualkit') {
                                                    $endingbal = (@($data->$prevtaqman->$prefix+$data->$taqmandeliveries->$received) - @($qualkitused));
                                                } else {
                                                    $endingbal = (@($data->$prevtaqman->$prefix+$data->$taqmandeliveries->$received) - @($used));
                                                }
                                            @endphp
                                            
                                            <input class="form-control" type="text" id="taqman{{ $types }}neg{{ $kits['alias'] }}disabled" value="{{ $endingbal }}" disabled="true">
                                            
                                            <input type="hidden" name="taqman{{ $types }}neg{{ $kits['alias'] }}" id="taqman{{ $types }}neg{{ $kits['alias'] }}" value="{{ $endingbal }}">
                                        </td>
                                        <td>
                                            <input class="form-control input-edit-danger" type="text" name="taqman{{ $types }}request{{ $kits['alias'] }}" id="taqman{{ $types }}request{{ $kits['alias'] }}" value="">
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="col-sm-6 control-label"><center>Comments concerning Negative adjustments (e.g. where were the kits issued out/donated to and why)</center></label>
                                        <div class="col-sm-6">
                                            <textarea class="form-control input-sm input-edit" id="{{ $types }}issuedcomment" name="{{ $types }}issuedcomment" cols="300"></textarea>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="col-sm-6 control-label"><center>Comments concerning Positive adjustments (e.g. where were the kits received from)</center></label>
                                        <div class="col-sm-6">
                                            <textarea class="form-control input-sm input-edit" id="{{ $types }}receivedcomment" name="{{ $types }}receivedcomment" cols="300" disabled="true"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
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
                        @foreach($data->testtypes as $types)
                            <div class="alert alert-warning">
                                <center><i class="fa fa-bolt"></i> Please enter {{ $types }} values below.</center>
                            </div>
                            <table class="table table-striped table-bordered table-hover data-table" style="font-size: 10px;margin-top: 1em;">
                                <thead>               
                                    <tr>
                                        <th rowspan="2">NAME OF COMMODITY</th>
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
                                    @php
                                        $testtype = $types.'testsabbott';
                                        $tests = $data->$testtype;
                                        $prevabbott = 'prevabbott'.$types;
                                        $abbottdeliveries = 'abbottdeliveries'.$types;
                                        $qualkitused = 0;
                                        $used = null;
                                    @endphp

                                    @foreach ($data->abbottKits as $kits)
                                    @php
                                        $prefix = 'ending'.$kits['alias'];
                                        $received = $kits['alias'].'received';
                                        $damaged = $kits['alias'].'damaged';
                                        $lot = $kits['alias'].'lotno';
                                        $prevabbott = 'prevabbott'.$types;
                                        $abbotdeliveries = 'abbotdeliveries'.$types;

                                        if ($kits['alias'] == 'qualkit') {
                                            if ($types == 'VL') {
                                                $qualkitused = round($tests / 93);
                                            } else if ($types == 'EID') {
                                                $qualkitused = round($tests / 94);
                                            }
                                        } else {
                                            $used = round($qualkitused * $kits['factor'][$types]);
                                        }
                                    @endphp
                                    <tr>
                                        <td>{{ $kits['name'] }}</td>
                                        <td>
                                            <input class="form-control input-edit" type="text" name="abbott{{ $types }}beginingbal{{ $kits['alias'] }}" id="abbott{{ $types }}beginingbal{{ $kits['alias'] }}" value="{{ $data->$prevabbott->$prefix ?? 0 }}" disabled="true">
                                        </td>
                                        <td>{{ $data->$abbotdeliveries->$received ?? 0 }}</td>
                                        <td>{{ $data->$abbotdeliveries->$lot ?? '-' }}</td>
                                        <td>{{ $used ?? $qualkitused }}</td>
                                        <td>
                                            <input class="form-control input-edit" type="text" name="abbott{{ $types }}losses{{ $kits['alias'] }}" id="abbott{{ $types }}losses{{ $kits['alias'] }}" value="">
                                        </td>
                                        <td>
                                            <input class="form-control input-edit" type="text" name="abbott{{ $types }}pos{{ $kits['alias'] }}" id="abbott{{ $types }}pos{{ $kits['alias'] }}" value="">
                                        </td>
                                        <td>
                                            <input class="form-control input-edit" type="text" name="abbott{{ $types }}neg{{ $kits['alias'] }}" id="abbott{{ $types }}neg{{ $kits['alias'] }}" value="">
                                        </td>
                                        <td>
                                            @php
                                                if($kits['alias'] == 'qualkit') {
                                                    $endingbal = (@($data->$prevabbott->$prefix+$data->$abbottdeliveries->$received) - @($qualkitused));
                                                } else {
                                                    $endingbal = (@($data->$prevabbott->$prefix+$data->$abbottdeliveries->$received) - @($used));
                                                }
                                            @endphp
                                            <input class="form-control" type="text" id="abbott{{ $types }}neg{{ $kits['alias'] }}disabled" value="{{ $endingbal }}" disabled="true">
                                            
                                            <input type="hidden" name="abbott{{ $types }}neg{{ $kits['alias'] }}" id="abbott{{ $types }}neg{{ $kits['alias'] }}" value="{{ $endingbal }}">
                                        </td>
                                        <td>
                                            <input class="form-control input-edit-danger" type="text" name="abbott{{ $types }}request{{ $kits['alias'] }}" id="abbott{{ $types }}request{{ $kits['alias'] }}" value="">
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="col-sm-6 control-label"><center>Comments concerning Negative adjustments (e.g. where were the kits issued out/donated to and why)</center></label>
                                        <div class="col-sm-6">
                                            <textarea class="form-control input-sm input-edit" id="{{ $types }}issuedcomment" name="{{ $types }}issuedcomment" cols="300"></textarea>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="col-sm-6 control-label"><center>Comments concerning Positive adjustments (e.g. where were the kits received from)</center></label>
                                        <div class="col-sm-6">
                                            <textarea class="form-control input-sm input-edit" id="{{ $types }}receivedcomment" name="{{ $types }}receivedcomment" cols="300" disabled="true"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach

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

        @foreach($plats as $platform)
            @foreach ($data->abbottKits as $kits)
                @if($kits['alias'] == 'qualkit')
                    @foreach($data->testtypes as $types)
                        @foreach($toedit as $element)
                            $("#{{ $platform.$types.$element.$kits['alias'] }}").keyup(function(){
                               performCalculus({{ $platform.$types.$element.$kits['alias'] }}, $(this).val());
                            });
                        @endforeach
                    @endforeach
                @endif
            @endforeach
        @endforeach

        function performCalculus($platform, $ktis) {
            alert("This is it");
        }
        

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