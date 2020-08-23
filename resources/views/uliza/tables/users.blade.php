@extends('uliza.main_layout')

@section('content')

<div class="col-md-12">
	<div class="card mr-2">
		<div class="card-body">
			<div class="d-flex align-items-center justify-content-center p-1 text-white bg-success rounded box-shadow">
				<div class="text-center">
					<h6 class="mb-0 text-white">Users</h6>
				</div>
			</div>
			<div class="card mt-1">
				<div class="card-body">
					<table class="table table-striped table-bordered table-hover data-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>User Type</th>
                                <th>TWG</th>
                                <th>Edit</th>
                            </tr>
                        </thead>
                        <tbody> 
                        	@foreach($users as $user)
                        		<tr>
                        			<td> {{ $user->full_name }} </td>
                                    <td> {{ $user->user_type->user_type }} </td>
                                    <td> {{ $user->twg->twg }} </td>
                        			<td> <a href="{{ url('uliza-user/' . $user->id . '/edit') }} "> Edit</a> </td>
                        		</tr>
                        	@endforeach
                        </tbody>						
					</table>

				</div>
			</div>
			<br>
		</div>
	</div>
</div>

@endsection

@section('scripts')

    @component('/uliza/tables/scripts')
	@endcomponent
@endsection