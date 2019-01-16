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
            <div class="hpanel" style="margin-top: 1em;margin-right: 20%;">
            	<div class="alert alert-danger">
	                <center><i class="fa fa-bolt"></i> Please note that you CANNOT access the main system until the below pending tasks have been completed.</center>
	            </div>

                @php
                    $currentmonth = date('m');
                    $year = date('Y');
                @endphp
                
                <div class="panel-body" style="padding: 20px;box-shadow: none; border-radius: 0px;">
                    <div class="alert alert-info">
                        <center>Allocation for {{ $machine->machine}}</center>
                    </div>
                {{ Form::open(['url' => '/allocation', 'method' => 'post', 'class'=>'form-horizontal']) }}
                    <table class="table table-striped table-bordered table-hover data-table" style="font-size: 10px;margin-top: 1em;">
                        <thead>               
                            <tr>
                                <th>Name of Commodity</th>
                                <th>Average Monthly Consumption</th>
                                <th>Months of Stock</th>
                                <th>Ending Balance</th>
                                <th>Recommended Quantity to Allocate (by System)</th>
                                <th>Quantity Allocated by Lab</th>
                                <th>Comments</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($machine->kits as $kit)
                            <tr>
                                <td>{{ str_replace("REPLACE", "", $kit->name) }}</td>
                                <td>Once</td>
                                <td>Once</td>
                                <td>Once</td>
                                <td>Once</td>
                                <td><input type="text" name=""></td>
                                <td>
                                    <textarea></textarea>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                    <center><button type="submit" name="kits-form" class="btn btn-primary btn-lg" value="true">Allocate</button></center>
                {{ Form::close() }}
                </div>
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