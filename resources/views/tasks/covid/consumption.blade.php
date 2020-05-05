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

<div class="row">
    <div class="col-md-12">
    {{ Form::open(['url' => '/covidkits/consumption', 'method' => 'post', 'class'=>'form-horizontal']) }}
    @php
        $kittypes = [
                'kits' => $covidkits->where('type', 'Kit'),
                'consumables' => $covidkits->where('type', 'Consumable')
            ];
    @endphp
    @foreach($kittypes as $typekey => $kits)
        <div class="hpanel" style="margin-top: 1em;margin-right: 2%;">
            <div class="panel-body" style="padding: 20px;box-shadow: none; border-radius: 0px;">
            @if($typekey == 'kits')
                <div class="alert alert-info">
                    <center><i class="fa fa-bolt"></i> Please enter values below. <strong>(Last Week Tests:{{ number_format($tests) }})</strong></center>
                </div>
            @endif
            <div class="table-responsive">
                <table class="table table-striped table-bordered table-hover data-table" style="font-size: 10px;margin-top: 1em;width: 100%">
                    <thead>               
                        <tr>
                            <th>Material Number</th>
                            <th>Product Description</th>
                            @if($typekey == 'kits')
                            <th>Pack Size</th>
                            <th>Calculated Pack Size by Number of Tests</th>
                            @endif
                            <th>{{ ucfirst($typekey) }} Used</th>
                            <th>Begining Balance</th>
                            <th>{{ ucfirst($typekey) }} Received From KEMSA</th>
                            <th>Positive Adjustments</th>
                            <th>Negative Adjustments</th>
                            <th>Losses/Wastage</th>
                            <th>Ending Balance</th>
                            <th>Requested {{ ucfirst($typekey) }}</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($kits as $kitkey => $kit)
                        <tr>
                            <td>{{ $kit->material_no }}</td>
                            <td>{{ $kit->product_description }}</td>                            
                            @if($typekey == 'kits')
                            <td>{{ $kit->pack_size }}</td>
                            <td>{{ $kit->calculated_pack_size }}</td>
                            @endif
                            @php
                                $kitsused = $kit->computekitsUsed($tests);
                            @endphp
                            <td>                            
                                <input class="form-control" type="number" name="kits_used[{{$kit->material_no}}]" value="{{$kitsused}}" min="0" disabled="false">
                                {{-- <input type="hidden" name="kits_used[{{$kit->material_no}}]" value="{{$kitsused}}"> --}}
                            </td>
                            <td>
                                <input class="form-control" type="number" name="begining_balance[{{$kit->material_no}}]" value="{{$kit->beginingbalance() ?? 0}}" min="0" disabled="false">
                                {{--<input type="hidden" name="begining_balance[{{$kit->material_no}}]" value="{{$kit->beginingbalance() ?? 10}}">--}}
                            </td>
                            <td>
                                <input class="form-control received" type="number" name="received[{{$kit->material_no}}]" id="received[{{$kit->material_no}}]" value="0" min="0" required>
                            </td>
                            <td>
                                <input class="form-control positive" type="number" name="positive[{{$kit->material_no}}]" id="positive[{{$kit->material_no}}]" value="0" min="0" required>
                            </td>
                            <td>
                                <input class="form-control negative" type="number" name="negative[{{$kit->material_no}}]" id="negative[{{$kit->material_no}}]" value="0" min="0" required>
                            </td>
                            <td>
                                <input class="form-control wastage" type="number" name="wastage[{{$kit->material_no}}]" id="wastage[{{$kit->material_no}}]" value="0" min="0" required>
                            </td>
                            <td>
                                <input class="form-control" type="number" name="ending[{{$kit->material_no}}]" id="ending[{{$kit->material_no}}]" value="{{@($kit->beginingbalance()-$kitsused)}}" min="0" disabled="true">
                                <input type="hidden" name="ending[{{$kit->material_no}}]" id="ending[{{$kit->material_no}}]" value="{{@($kit->beginingbalance()-$kitsused)}}">
                            </td>
                            <td>
                                <input class="form-control" type="number" name="requested[{{$kit->material_no}}]" id="requested[{{$kit->material_no}}]" value="0" min="0" required>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            </div>
        </div>
    @endforeach
    <div class="hpanel" style="margin-top: 1em;margin-right: 6%;">
        <div class="panel-body" style="padding: 20px;box-shadow: none; border-radius: 0px;">
            <div class="col-sm-12">
                <center>
                <button class="btn btn-success" type="submit">Submit Covid Consumption</button>
                <button class="btn btn-primary" type="reset" name="discard" value="add">Discard Changes</button>
                </center>
            </div>
        </div>
    </div>    
    {{ Form::close() }}
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
            // Observe changes on received kits
            $(".received").change(function(){
                var received = $(this).get(0).id;
                var receivedval = $(this).val();
                // console.log(receivedval);
                updateendingbalance("received", received, receivedval);
            });

            // Observe changes on the positive kits
            $(".positive").change(function(){
                var positive = $(this).get(0).id;
                var positiveval = $(this).val();
                updateendingbalance("positive", positive, positiveval);
            });

            // Observe changes on the negatiive kits
            $(".negative").change(function(){
                var negative = $(this).get(0).id;
                var negativeval = $(this).val();
                if (negativeval == '')
                    negativeval = 0;
                updateendingbalance("negative", negative, (parseInt(negativeval)*-1));
            });

            // Observe changes on the wastage kits
            $(".wastage").change(function(){
                var wastage = $(this).get(0).id;
                var wastageval = $(this).val();
                if (wastageval == '')
                    wastageval = 0;
                updateendingbalance("wastage", wastage, (parseInt(wastageval)*-1));
            });
        });

        function updateendingbalance(elementname, elementid, value) {
            if (value == '')
                value = 0;
            var ending = elementid.replace(elementname, "ending");
            var endingval = $('input[name="' + ending + '"').val();
            $('input[name="' + ending + '"').val((parseInt(endingval)+parseInt(value)));
        }
    </script>
@endsection