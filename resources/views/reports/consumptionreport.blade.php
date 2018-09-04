@extends('layouts.master')

    @component('/tables/css')
    @endcomponent

@section('content')
<style type="text/css">
    .spacing-div-form {
        margin-top: 15px;
    }
</style>
<div class="content">
    <div class="row">
        <div class="col-lg-12">
            <div class="hpanel">
                <div class="panel-body" style="padding: 20px;box-shadow: none; border-radius: 0px;">
                	<table class="table table-striped table-bordered table-hover" style="font-size: 10px;margin-top: 1em;">
                		<thead>
                			<tr>
                				<th rowspan="2">DESCRIPTION OF GOODS</th>
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
                			@foreach($data->child as $sub)
	                			<tr>
	                				<td>{{ $sub->name }}</td>
	                				<td colspan="9">Nothing really</td>
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