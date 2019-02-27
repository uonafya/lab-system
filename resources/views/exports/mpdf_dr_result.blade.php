<html>

<head>
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/dist/css/bootstrap.css') }}" />	

	<style type="text/css">
		table, th, td {
			padding: 6px;
		}

	</style>
</head>


<body >

	<div class="container">

		<div class="row" style="text-align: center;">
			@if(env('APP_LAB') == 7)
				<img src="http://lab-2.test.nascop.org/img/Result_Print_Out_Logo_NHRL.png" alt="NASCOP"> <br />
			@else
				<img src="http://lab-2.test.nascop.org/img/naslogo.jpg" alt="NASCOP"> <br />
				<b>
					MINISTRY OF HEALTH <br />
					NATIONAL AIDS AND STD CONTROL PROGRAM (NASCOP)<br />
				</b>
			@endif

			<h2>HIV-1 Drug Resistance Genotype Report</h2>
			<br />
			<br />
		</div>

		<div class="row">
			<table class="table">
				<tr>			
					<td><b>Specimen ID:</b> {{ $sample->patient->patient ?? '' }} </td>
					<td><b>Collection Date:</b> {{ $sample->my_date_format('datecollected') }} </td>
					<td><b>Sample Type:</b> {{ $sample->sample_type_output }} </td>					
				</tr>
				<tr>
					<td><b>NHRL ID:</b> {{ $sample->mid }} </td>
					<td><b>Received Date:</b> {{ $sample->my_date_format('datereceived') }} </td>
					<td><b>Report Date:</b> {{ date('d-M-Y') }} </td>					
				</tr>
				<tr>
					<td><b>Tested By:</b> {{ $sample->worksheet->creator->full_name ?? '' }} </td>
					<td><b></b> </td>
					<td><b>Report ID:</b> </td>					
				</tr>
			</table>			
		</div>

		<br />
		<br />

		<div class="row">
			@if($sample->receivedstatus == 2)
				<p>
					Sample Unfit For Testing For the following reason: <br />
					{{ $dr_rejected_reasons->where('id', $sample->id)->first()->name ?? '' }}
				</p>
			@elseif($sample->collect_new_sample)
				<p>
					Sample Test Has Failed. Please collect a new sample.
				</p>
			@else
				<table class="table table-bordered table-hover">
					<thead>
						<tr>
							<th>Drug Class</th>
							<th>Detected Mutations</th>
							<th>Drug</th>
							<th>Susceptability</th>
							<th>Susceptability Code</th>
						</tr>
					</thead>
					<tbody>
						@foreach($sample->dr_call as $dr_call)
							@foreach($dr_call->call_drug as $key => $call_drug)
								<tr>
								@if ($key)
									<td></td>
								@else
									<td>{{ $dr_call->drug_class }}  </td>
								@endif
									<td>{{ $dr_call->mutations_array[$key] ?? '' }}  </td>
									<td>{{ $call_drug->short_name }} </td>
									<td>{{ $call_drug->resistance }} </td>
									{!! $call_drug->resistance_cell_two !!}
								<tr/>	
							@endforeach

						@endforeach
				</table>
				<br />

				 <div class="hr-line-dashed"></div>

				<h4><b> Notes on Susceptability </b> </h4>

				<table class="table table-bordered">
					<tr>
						<td> Colour </td>
						<td> Degree </td>
						<td> Explanation </td>
					</tr>
					<tr>
						<td style="background-color:{{ $resistance_colours['R']['resistance_colour'] }};" bgcolor="{{ $resistance_colours['R']['resistance_colour'] }}"> </td>
						<td>High-Level </td>
						<td>Mutations detected constitute a high level of genetic evidence for viral resistance.</td>
					</tr>	
					<tr>
						<td style="background-color:{{ $resistance_colours['I']['resistance_colour'] }};" bgcolor="{{ $resistance_colours['I']['resistance_colour'] }}"> </td>
						<td>Intermediate </td>
						<td>Mutations detected constitute an intermediate level of genetic evidence for viral resistance.</td>
					</tr>			
					<tr>
						<td style="background-color:{{ $resistance_colours['S']['resistance_colour'] }};" bgcolor="{{ $resistance_colours['S']['resistance_colour'] }}"> </td>
						<td>Susceptible </td>
						<td>Insufficient evidence for viral resistance.</td>
					</tr>			
					<tr>
						<td style="background-color:{{ $resistance_colours['LC']['resistance_colour'] }};" bgcolor="{{ $resistance_colours['LC']['resistance_colour'] }}"> </td>
						<td>Low Coverage </td>
						<td>Inconclusive.</td>
					</tr>							
				</table>

			@endif
			
		</div>	

		<div class="row">
			<b>Approved By: </b> {{ $sample->approver->full_name ?? '' }}
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<b>Date Approved: </b> {{ $sample->my_date_format('dateapproved') }}
		</div>	
	</div>

</body>

<script src="{{ asset('vendor/bootstrap/dist/js/bootstrap.min.js') }}"></script>

</html>