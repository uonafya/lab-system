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
    	<div class="alert alert-success">
            <center>
            	<strong>PLEASE CONFIRM THAT YOU RECEIVED THE KITS BELOW IN THE WEEK YOU WISH TO REPORT FOR</strong>
            </center>
        </div>
    	<form action="/covidkits/allocation" method="POST" class="form-horizontal" id="covid_allocation" >
		    @csrf
		    @foreach($allocations as $machinekey => $kits)
		        @php
		            $machine = null;
		            if ($machinekey != ""){
		               $machine = \App\Machine::find($machinekey);
		               $machinename = $machine->machine . ' Kits';
		            } else {
		               $machinename = 'Consumables'; 
		            }
		        @endphp
		        <div class="hpanel" style="margin-top: 1em;margin-right: 2%;">
		            <div class="panel-body" style="padding: 20px;box-shadow: none; border-radius: 0px;">
		                <div class="alert alert-info">
		                    <center><i class="fa fa-bolt"></i> Please enter <strong>{{ ucfirst($machinename) }}</strong> allocations received from KEMSA.
		                    @if($machine)
		                        <input type="hidden" name="machine[]" value="{{ $machine->id }}">   
		                    @endif
		                    </center>
		                </div>
		            	<div class="table-responsive">
			            	<table class="table table-striped table-bordered table-hover data-table" style="font-size: 10px;margin-top: 1em;width: 100%">
		                        <thead>               
		                            <tr>
		                                <th>Material Number</th>
		                                <th>Product Description</th>
		                                <th>Date Allocated</th>
		                                <th>{{ ucfirst($machinename) }} Allocated by KEMSA</th>
		                                <th>{{ ucfirst($machinename) }} Received From KEMSA</th>
		                            </tr>
		                        </thead>
		                        <tbody>
		                        @foreach($kits as $kitkey => $kit)
		                        	<tr>
		                        		<td>{{ $kit->kit->material_no }}</td>
		                        		<td>{{ $kit->kit->product_description }}</td>
		                        		<td>{{ $kit->allocation_date }}</td>
		                        		<td>{{ $kit->allocated_kits }}</td>
		                        		<td>
		                        			<input class="form-control received" type="number" name="received[{{$kit->id}}]" id="received[{{$kit->id}}]" value="{{ $kit->allocated_kits ?? o }}" min="0" required>
		                        		</td>
		                        	</tr>
		                        @endforeach
		                        </tbody>
		                    </table>
			            </div>
		            </div>
		            {{--<div class="panel-footer">
		            	<div class="col-sm-12">
		                	<center>
		                		<input type="date" name="datereceived">
		                	</center>
		                </div>
		            </div>--}}
		        </div>
		    @endforeach
		    <div class="hpanel" style="margin-top: 1em;margin-right: 6%;">
		        <div class="panel-body" style="padding: 20px;box-shadow: none; border-radius: 0px;">
		            <div class="col-sm-12">
		                <center>
		                <button class="btn btn-success" type="submit" name="response" value="YES">YES</button>
		                <button class="btn btn-primary" type="submit" name="response" value="NO">NO</button>
		                </center>
		            </div>
		        </div>
		    </div>  
	    </form>
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

        });
    </script>
@endsection