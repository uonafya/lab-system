<html>

<head>
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/dist/css/bootstrap.css') }}" />	
</head>


<!-- <body onLoad="JavaScript:window.print();"> -->
<body >

	<div class="container">
		<div class="row">
			<table class="table table-bordered table-hover">
				<thead>
					<tr>
						<th>Drug Class</th>
						<th>Drug</th>
						<th>Resistance</th>
						<th>Resistance Code</th>
					</tr>
				</thead>
				<tbody>
					@foreach($sample->dr_call as $dr_call)
						<tr>
							<td rowspan="{{ $dr_call->call_drug->count()+1 }}">{{ $dr_call->drug_class }}  </td>

						@foreach($dr_call->call_drug as $key => $call_drug)
							@if (!$key)
								<tr>
							@endif
								<td>{{ $call_drug->short_name }} </td>
								<td>{{ $call_drug->resistance }} </td>
								{!! $call_drug->resistance_cell !!}
							<tr/>	
						@endforeach

					@endforeach
				</tr>
			</table>
			
		</div>		
	</div>

</body>

<script src="{{ asset('vendor/bootstrap/dist/js/bootstrap.min.js') }}"></script>

</html>