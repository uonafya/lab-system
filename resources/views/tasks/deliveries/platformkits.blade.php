@extends('layouts.tasks')

@section('css_scripts')
    <link href="{{ asset('css/datapicker/datepicker3.css') }}" rel="stylesheet" type="text/css">
@endsection

@section('custom_css')
	<style type="text/css">
		.hpanel .panel-body .spacing {
			margin-bottom: 1em;
		}
        .input-edit {
            background-color: #FFFFCC;
        }
        .input-edit-empty {
            background-color: #FFFFCC;   
        }
	</style>
@endsection

@section('content')
<div class="row">
        <div class="col-md-12">
            <div class="hpanel" style="margin-top: 1em;margin-right: 18%;">
            	<div class="alert alert-default">
		                <center><i class="fa fa-bolt"></i> Please enter the Kit Delivery details to keep track of deliveries and consumption.</center>
	            </div>
                <div class="panel-body" style="padding: 20px;box-shadow: none; border-radius: 0px;">
                    <form action="/submitkitsdeliveries" method="POST" class="form-horizontal" >
                        @csrf
                        <div class="form-group">
                            <label class="col-sm-4 control-label"><center>Received for the month</center></label>
                            <div class="col-sm-8">
                                <label class="col-sm-4 control-label badge badge-info">
                                    <center>{{ date("F", mktime(null, null, null, $period->month)) }}, {{ $period->year }}</center>
                                </label>
                            </div>
                        </div>
                        @foreach($machines as $machine)
                        <input type="hidden" name="machine[]" value="{{ $machine->id }}">
                        @foreach($types as $type)
                            <div class="alert alert-warning">
                                <center><i class="fa fa-bolt"></i> 
                                    Please enter {{ $machine->machine }} {{ $type->name }} values in the yellow boxes.
                                </center>
                            </div>
                            <table class="table table-striped table-bordered table-hover data-table" style="font-size: 10px;margin-top: 1em;">
                                <thead>               
                                    <tr>
                                        <th rowspan="2">No</th>
                                        <th rowspan="2">Description of Goods</th>
                                        <th rowspan="2">Lot No</th>
                                        <th rowspan="2">Expiry Date</th>
                                        <th colspan="3"><center>Quantity</center></th>
                                    </tr>
                                    <tr>
                                        <th>Received</th>
                                        <th>Damaged</th>
                                        <th>To be Used</th>
                                    </tr>
                                </thead>
                                <tbody>
                                @foreach($machine->kits as $key => $kit)
                                    <tr>
                                        <td>{{ $key+1 }}</td>
                                        <td><strong>{{ str_replace('REPLACE', $type->type,$kit->name) }}</strong></td>
                                        <td>
                                            <input class="form-control input-sm input-edit-empty lotno" name="lotno[{{$machine->machine}}][{{$type->name}}][{{$kit->id}}]" type="text" value="">
                                        </td>
                                        <td>
                                            <div class="input-group date">
                                                <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                                <input type="text" class="form-control input-sm input-edit-empty expiry" value="" name="expiry[{{$machine->machine}}][{{$type->name}}][{{$kit->id}}]">
                                            </div>
                                        </td>
                                        <td>
                                            <input class="form-control input-sm input-edit received" name="received[{{$machine->machine}}][{{$type->name}}][{{$kit->id}}]" required type="number" min="0" value="0">
                                        </td>
                                        <td>
                                            <input class="form-control input-sm input-edit damaged" name="damaged[{{$machine->machine}}][{{$type->name}}][{{$kit->id}}]" required type="number" min="0" value="0">
                                        </td>
                                        <td>
                                            <input class="form-control input-sm input-edit used" name="used[{{$machine->machine}}][{{$type->name}}][{{$kit->id}}]" required type="number" min="0" value="0">
                                        </td>
                                    </tr>
                                    
                                @endforeach
                                </tbody>
                            </table>
                        @endforeach
                        @endforeach
                        <div class="form-group">
                            <label class="col-sm-2 control-label"><center>Received By:</center></label>
                            <div class="col-sm-4">
                                <select class="form-control input-sm" required name="receivedby" id="receivedby">
                                    <option value="" selected disabled>Select a User</option>
                                @forelse ($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->full_name }}</option>
                                @empty
                                    <option value="" disabled>No User</option>
                                @endforelse
                                </select>
                            </div>
                            <label class="col-sm-2 control-label"><center>Date Received</center></label>
                            <div class="col-sm-4">
                                <div class="input-group date">
                                    <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                    <input type="text" id="datereceived" required class="form-control input-sm" value="" name="datereceived">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-12">
                                <center>
                                <button class="btn btn-success" type="submit" name="platform" value="platform">Submit Platform Values</button>
                                <button class="btn btn-primary" type="submit" name="discard" value="add">Discard Changes</button>
                                </center>
                            </div>
                        </div>
                    </form>
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


        @slot('val_rules')
           
        @endslot
        $("select").select2();

        $(".date").datepicker({
            todayBtn: "linked",
            forceParse: true,
            autoclose: true,
            format: "yyyy-mm-dd"
        });

    @endcomponent
<script type="text/javascript">
    $().ready(function() {
        
    });
</script>
@endsection