@extends('layouts.master')

    @component('/tables/css')
    @endcomponent

@section('content')
<style type="text/css">
    .spacing-div-form {
        margin-top: 15px;
    }
</style>
@php
    $tests = $viewdata->tests;
@endphp
<div class="content">
    <div class="row">
        <div class="col-md-6 alert alert-warning">
            <center>
                {{ $viewdata->month }}, {{ $viewdata->year }} <br>
                {{ strtoupper($viewdata->platform) }}
            </center>
        </div>
        <div class="col-md-6">
            <table class="table table-striped table-bordered">
                <tr>
                    <th>Test Type</th>
                    <th>Total No. of tests Done</th>
                </tr>
                <tr>
                    <td>{{ $viewdata->type }}</td>
                    <td>{{ $tests }}</td>
                </tr>
            </table>
        </div>
        <div class="col-lg-12">
            <div class="hpanel">
                <div class="panel-body" style="padding: 20px;box-shadow: none; border-radius: 0px;">
                	<table class="table table-striped table-bordered table-hover" style="font-size: 10px;margin-top: 1em;">
                		<thead>
                			<tr>
                				<th rowspan="2">#</th>
                                <th rowspan="2">DESCRIPTION OF GOODS</th>
                                <th rowspan="2">UNIT</th>
                				<th rowspan="2">BEGINING BALANCE</th>
                				<th colspan="2">QUANTITY RECEIVED FROM CENTRAL WAREHOUSE (KEMSA, SCMS/RDC)</th>
                				<th rowspan="2">QUANTITY USED</th>
                				<th rowspan="2">LOSSES/WASTAGES</th>
                				<th colspan="2">ADJUSTMENTS</th>
                				<th rowspan="2">ENDING BALANCE</th>
                				<th rowspan="2">QUANTITY REQUESTED</th>
                			</tr>
                			<tr>
                				<th>Quantity</th>
                				<th>Lot No.</th>
                				<th>Positive <br><font color="green">(Received other source)</font></th>
                				<th>Negative <br><font color="purple">(Issued out)</font></th>
                			</tr>
                		</thead>
                		<tbody>
            			{{-- @foreach($data->child as $key => $sub) --}}
                        @foreach($viewdata->kits as $key => $kit)
                            <tr>
                				<td>{{ $key+1 }}</td>
                                <td>{{ $kit->name ?? '' }}</td>
                                <td>{{ $kit->unit ?? '' }}</td>
                                @php
                                    $type = $viewdata->type;
                                    $recevied = $kit->alias.'received';
                                    $damaged = $kit->alias.'damaged';
                                    $lotno = $kit->alias.'lotno';
                                    $begining = 'ending'.$kit->alias;
                                    $positiveAdj = 'pos'.$kit->alias;
                                    $negativeAdj = 'issued'.$kit->alias;
                                    $wastage = 'wasted'.$kit->alias;
                                    $ending = 'ending'.$kit->alias;
                                    $requested = 'request'.$kit->alias;

                                    // Calculations
                                    $receivedQty = (($viewdata->kitsreport->$recevied ?? 0) - ($viewdata->kitsreport->$damaged ?? 0));

                                    if ($kit->alias == 'qualkit') {
                                        $testfactor = json_decode($kit->testFactor);
                                        $testfactor = $testfactor->$type ?? $testfactor;
                                        $qualkit = round(@((int) $tests / (int) $testfactor));
                                        $consumed = $qualkit;
                                    } else {
                                        $factor = json_decode($kit->factor);
                                        $factor = $factor->$type ?? $factor;
                                        $consumed = ($qualkit * $factor);
                                    }
                                @endphp
                                <td>{{ $viewdata->prevreport->$begining ?? 0 }}</td>
                                <td>{{ $receivedQty }}</td>
                                <td>{{ $viewdata->kitsreport->$lotno ?? '' }}</td>
                                <td>{{ $consumed ?? 0 }}</td>
                                <td>{{ $viewdata->reports->$wastage ?? 0 }}</td>
                                <td>{{ $viewdata->reports->$positiveAdj ?? 0 }}</td>
                                <td>{{ $viewdata->reports->$negativeAdj ?? 0 }}</td>
                                <td>{{ $viewdata->reports->$ending ?? 0 }}</td>
                                <td>{{ $viewdata->reports->$requested ?? 0 }}</td>
                                {{-- <td>
                                @if($sub->alias == 'qualkit')
                                    @php
                                        $name = $viewdata->type.'name';
                                    @endphp
                                    {{ $sub->$name }}
                                @else
                                    {{ $sub->name }}
                                @endif
                                </td>
                                <td>{{ $viewdata->prevreport['ending'.$sub->alias] ?? '' }}</td>
                				<td>{{ $viewdata->kitsreport[$sub->alias.'received'] ?? '' }}</td>
                                <td>{{ $viewdata->kitsreport[$sub->alias.'lotno'] ?? '' }}</td>
                                <td>
                                    @if($viewdata->platform == 'abbott')
                                        @if($viewdata->type == 'EID')
                                            @if($sub->alias == 'qualkit')
                                                @php
                                                    $eidAbbotqualkit = round(@((int) $tests / (int) $sub->testFactor->EID));
                                                @endphp
                                                {{ $eidAbbotqualkit }}
                                            @else
                                                {{ round($eidAbbotqualkit*$sub->testFactor->EID) }}
                                            @endif
                                        @elseif($viewdata->type == 'VL')
                                            @if($sub->alias=='qualkit')
                                                @php
                                                    $vlAbbotqualkit = round(@((int) $tests / (int) $sub->testFactor->VL));
                                                @endphp
                                                {{ $vlAbbotqualkit }}
                                            @else
                                                {{ round($vlAbbotqualkit*$sub->testFactor->VL) }}
                                            @endif
                                        @endif
                                    @elseif($viewdata->platform == 'taqman')
                                        @if($viewdata->type == 'EID')
                                            @if($sub->alias=='qualkit')
                                                @php
                                                    $eidTaqqualkit = round(@((int) $tests / (int) $sub->testFactor->EID));
                                                @endphp
                                                {{ $eidTaqqualkit }}
                                            @else
                                                {{ round($eidTaqqualkit*$sub->testFactor) }}
                                            @endif
                                        @elseif($viewdata->type == 'VL')
                                            @if($sub->alias=='qualkit')
                                                @php
                                                    $vlTaqqualkit = round(@((int) $tests / (int) $sub->testFactor->VL));
                                                @endphp
                                                {{ $vlTaqqualkit }}
                                            @else
                                                {{ round($vlTaqqualkit*$sub->testFactor) }}
                                            @endif
                                        @endif
                                    @endif
                                </td>
                                <td>{{ $viewdata->reports['wasted'.$sub->alias] }}</td>
                                <td>{{ $viewdata->reports['pos'.$sub->alias] }}</td>
                                <td>{{ $viewdata->reports['issued'.$sub->alias] }}</td>
                                <td>{{ $viewdata->reports['ending'.$sub->alias] }}</td>
                                <td>{{ $viewdata->reports['request'.$sub->alias] }}</td> --}}
                			</tr>
            			@endforeach
                		</tbody>
                	</table>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts') 

    @component('/tables/scripts')

    @endcomponent
    <script type="text/javascript">
        $(document).ready(function(){
            
        });
    </script>
@endsection