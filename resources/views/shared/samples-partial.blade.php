<table  class="table table-striped table-bordered table-hover data-table">
	<thead>
		<tr>
			<th>#</th>
			<th>Patient</th>
			<th>Facility</th>
			<th>Spots</th>
			<th>Run</th>
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
				<td> {{ $sample->patient }} </td>
				<td> {{ $sample->name }} </td>
				<td> {{ $sample->spots }} </td>
				<td> {{ $sample->run }} </td>
				<td> {{ $sample->datecollected }} </td>

                <td> <a href="{{ url('sample/release/' . $sample->id) }}" class="confirmAction"> Release</a> </td>
                <td> <a href="{{ url('sample/' . $sample->id . '/edit') }}"> Edit</a> </td>
                <td> 
                    {{ Form::open(['url' => 'sample/' . $sample->id, 'method' => 'delete', 'onSubmit' => "return confirm('Are you sure you want to delete the following sample?');"]) }}
                        <button type="submit" class="btn btn-xs btn-primary">Delete</button>
                    {{ Form::close() }} 
                    
                </td>
			</tr>
		@endforeach

	</tbody>

</table>