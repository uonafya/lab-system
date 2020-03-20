<div class="table-responsive">
	<table  class="table table-striped table-bordered table-hover data-table">
		<thead>
			<tr>
				<th>#</th>
				<th>Lab ID</th>
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
					<td> {{ $sample->patient }} </td>
					<td> {{ $sample->facility->name }} </td>
					<td> {{ $sample->run }} </td>
					@if($sample->parentid)
						<td> {{ $sample->parentid ?? null }} </td>
					@else
						<td></td>
					@endif
					
					<td> {{ $sample->my_date_format('datecollected') }} </td>
	                <td> <a href="/{{ url($sample->route_name . '/release/' . $sample->id) }}" class="confirmAction"> Release</a> </td>
	                <td> {!! $sample->edit_link !!} </td>
	                <td> {!! $sample->delete_form !!} </td>
				</tr>
			@endforeach

		</tbody>

	</table>
</div>