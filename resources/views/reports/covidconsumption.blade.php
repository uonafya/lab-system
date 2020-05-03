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
    @forelse($consumptions as $consumptionKey => $consumption)
        <div class="col-lg-12">
            <div class="alert alert-info">
                COVID-19 Tests Done: {{$consumption->start_of_week}} - {{$consumption->end_of_week}}
            </div>
            <div class="hpanel">
                <div class="panel-body" style="padding: 20px;box-shadow: none; border-radius: 0px;">
                	<table class="table table-striped table-bordered table-hover" style="font-size: 10px;margin-top: 1em;">
                		<thead>
                			<tr>
                				<th>#</th>
                                <th>Material No</th>
                                <th>Product Description</th>
                                <th>Begining Balance</th>
                                <th>Received</th>
                                <th>Used</th>
                                <th>Positive Adjustment</th>
                                <th>Negative Adjustment</th>
                                <th>Losses/Wastage</th>
                                <th>Ending Balance</th>
                                <th>Requested</th>
                			</tr>
                		</thead>
                		<tbody>
                        @foreach($consumption->details as $key => $detail)
                            <tr>
                				<td>{{ $key+1 }}</td>
                                <td>{{ $detail->kit->material_no ?? '' }}</td>
                                <td>{{ $detail->kit->product_description ?? '' }}</td>
                                <td>{{ $detail->begining_balance ?? '' }}</td>
                                <td>{{ $detail->received ?? '' }}</td>
                                <td>{{ $detail->kits_used ?? '' }}</td>
                                <td>{{ $detail->positive ?? '' }}</td>
                                <td>{{ $detail->negative ?? '' }}</td>
                                <td>{{ $detail->wastage ?? '' }}</td>
                                <td>{{ $detail->ending ?? '' }}</td>
                                <td>{{ $detail->requested ?? '' }}</td>
                			</tr>
            			@endforeach
                		</tbody>
                	</table>
                </div>
            </div>
        </div>
    @empty
        <div class="alert alert-info">
            No Covid Kits consumption submitted.
        </div>
    @endforelse
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