@extends('layouts.tasks')

@section('css_scripts')

@endsection

@section('custom_css')
	<style type="text/css">
		.hpanel .panel-body .spacing {
			margin-bottom: 1em;
		}
        .hpanel .panel-body .bottom {
            border-bottom: 1px solid #eaeaea;
        }
	</style>
@endsection

@section('content')
<div class="row">
        <div class="col-md-12">
            <div class="hpanel" style="margin-top: 1em;margin-right: 20%;margin-bottom: 3em;">
            	<div class="alert alert-danger">
	                <center><i class="fa fa-bolt"></i> Please note that you CANNOT access the main system until the below pending tasks have been completed.</center>
	            </div>

                @php
                    $currentmonth = date('m');
                    $year = date('Y');
                @endphp
                {{ Form::open(['url' => '/allocation', 'method' => 'post', 'class'=>'form-horizontal']) }}
                @foreach($data->machines as $machine)
                    @foreach($data->testtypes as $testtypeKey => $testtype)
                    <div class="panel-body" style="padding: 20px;box-shadow: none; border-radius: 0px;">
                        <div class="alert alert-info">
                            <center>Allocation for {{ $machine->machine}}, {{ $testtypeKey }}</center>
                        </div>
                        <table class="table table-striped table-bordered table-hover data-table" style="font-size: 10px;margin-top: 1em;">
                            <thead>               
                                <tr>
                                    <th>Name of Commodity</th>
                                    <th>Average Monthly Consumption(AMC)</th>
                                    <th>Months of Stock(MOS)</th>
                                    <th>Ending Balance</th>
                                    <th>Recommended Quantity to Allocate (by System)</th>
                                    <th>Quantity Allocated by Lab</th>
                                    <th>Comments</th>
                                </tr>
                            </thead>
                            <tbody>
                            @php
                                $testtypeKey = $testtypeKey;
                                $tests = $machine->testsforLast3Months()->$testtypeKey;
                                $qualamc = 0;
                            @endphp
                            @foreach($machine->kits as $kit)
                                @php
                                    $test_factor = json_decode($kit->testFactor);
                                    $factor = json_decode($kit->factor);
                                    if ($kit->alias == 'qualkit')
                                        $qualamc = (($tests / $test_factor->$testtypeKey) / 3);
                                    if ($machine->id == 2)
                                        $amc = $qualamc * $factor->$testtypeKey;
                                    else
                                        $amc = $qualamc * $factor;
                                @endphp
                                <tr>
                                    <td>{{ str_replace("REPLACE", "", $kit->name) }}</td>
                                    <td>{{ $amc }}</td>
                                    @forelse($kit->consumption as $consumption)
                                        @if($consumption->testtype == $testtype)
                                            @php
                                                $mos = @($consumption->ending / $amc);
                                            @endphp
                                            <td>
                                            @if(is_nan($mos))
                                                {{ 0 }}
                                            @else
                                                {{ $mos }}
                                            @endif
                                            </td>
                                            <td>{{ $consumption->ending }}</td>
                                            <td>{{ ($amc * 2) -$consumption->ending }}</td>
                                        @endif
                                    @empty
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                    @endforelse
                                    <td><input type="text" name="allocate-{{ $testtype }}-{{ $kit->id }}"></td>
                                    <td>
                                        <textarea name="comment-{{ $testtype }}-{{ $kit->id }}"></textarea>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endforeach
                @endforeach
                <center>
                    <button type="submit" name="kits-form" class="btn btn-primary btn-lg" value="true" style="margin-top: 2em;margin-bottom: 2em; width: 200px; height: 30px;">Allocate</button>
                </center>
                {{ Form::close() }}
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script type="text/javascript">
        $(function(){
            $("#yesBtn").click(function(){
                $("#choice").hide();
                $("#allocationForm").fadeIn();
            });
        });
    </script>
@endsection