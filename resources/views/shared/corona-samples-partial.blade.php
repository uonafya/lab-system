<div class="table-responsive">
	<table  class="table table-striped table-bordered table-hover data-table">
		<thead>
			<tr>
				<th>#</th>
				<th>Lab ID</th>
				<th>Type</th>
				<th>Patient</th>
				<th>Facility</th>
				<th>Run</th>
				<th>Original ID</th>
				<th>Date Collected</th>
				<th>Release as Redraw</th>
				<th>Update</th>
				<th>Delete</th>
			</tr>
		</thead>
		<tbody>
			@foreach($samples as $key => $sample)
				<tr>
					<td> {{ ($key+1) }} </td>
					<td> {{ $sample->id }} </td>
					<td>
						@if($sample->route_name == 'covid_sample')
							Covid-19
						@elseif($sample->route_name == 'viralsample')
							Viralload
						@elseif($sample->route_name == 'sample')
							EID
						@endif
					</td>
					<td> {{ $sample->identifier ?? $sample->patient }} </td>
					<td> {{ $sample->facilityname }} </td>
					<td> {{ $sample->run }} </td>
					
					<td>
						@if($sample->parentid) 
							{{ $sample->parentid }} 
						@endif
					</td>
					
					<td> {{ $sample->my_date_format('datecollected') }} </td>
	                <td> <a href="{{ url($sample->route_name . '/release/' . $sample->id) }}" class="confirmAction"> Release</a> </td>
	                <td> {!! $sample->edit_link !!} </td>
	                <td> {!! $sample->delete_form !!} </td>
				</tr>
			@endforeach

		</tbody>

	</table>
</div>