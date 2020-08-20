@extends('uliza.main_layout')

@section('content')

<div class="col-md-12">
	<div class="card mr-2">
		<div class="card-body">
			<div class="d-flex align-items-center justify-content-center p-1 text-white bg-success rounded box-shadow">
				<div class="text-center">
					<h6 class="mb-0 text-white">Clinical Summary Cases</h6>
				</div>
			</div>
			<div class="card mt-1">
				<div class="card-body">
					<table class="table table-striped table-bordered table-hover data-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Nat No</th>
                                <th>RTWG</th>
                                <th>Facility</th>
                                <th>Status</th>
                                <th>Reporting Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody> 
                        	@foreach($forms as $key => $form)
                        		<tr>
                        			<td> {{ $key }} </td>
                                    <td> {{ $form->nat_no }} </td>
                                    <td> {{ $form->twg->twg }} </td>
                                    <td> {{ $form->facility->name }} </td>
                                    <td> </td>
                        			<td> {{ $form->created_at }} </td>
                        			<td> <a href="{{ url('uliza-review/create/' . $form->id) }} "><button class="btn btn-primary"> Process Feedback </button></a> </td>
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