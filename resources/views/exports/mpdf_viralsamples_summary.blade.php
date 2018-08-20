
<!DOCTYPE html>
<html>
<head>

	<style type="text/css">
		body {
			font-weight: 1px;
		}

		table {
			border-collapse: collapse;
			margin-bottom: .5em;
		}

		table, th, td {
			border: 1px solid black;
			border-style: solid;
     		font-size: 8px;
		}


		h5 {
			margin-top: 6px;
		    margin-bottom: 6px;
		}

		p {
			margin-top: 2px;
     		font-size: 8px;
		}
		* {
			font-size: 8px;
		}
	</style>
</head>
<body>

	@foreach($batches as $batch)

		<table border="0" style="border: 0px; width: 100%;">
			<tr>
				<td colspan="9" align="center">
					<img src="{{ asset('img/naslogo.jpg') }}" alt="NASCOP">
				</td>
			</tr>
			<tr>
				<td colspan="9" align="center">
					<h5>MINISTRY OF HEALTH</h5>
					<h5>NATIONAL AIDS AND STD CONTROL PROGRAM (NASCOP)</h5>
					<h5>VIRAL LOAD TEST RESULTS SUMMARY</h5>
				</td>
			</tr>
			<tr>
				<td colspan="3">
					<strong> Batch No.: {{ $batch->id }} &nbsp;&nbsp; {{ $batch->facility->name ?? ''}} </strong> 
				</td>
				<td colspan="3" align="right">
					<strong>LAB: {{ $batch->lab->name ?? '' }}</strong>
				</td>
			</tr>
			<tr>
				<td colspan="9">
					<strong>NOTICE:</strong>
					<strong>The Viral Load Test is now available in all EID testing sites. Samples can be collected in DBS form and shipped using the A/C C00339.Call the official EID lines for more information. Thank you.</strong>
				</td>
			</tr>
		</table>

		<table style="width: 100%;">
			<tr>
				<td colspan='3'>Date Samples Were Dispatched :  {{ $batch->my_date_format('datedispatched')  }}</td>		
			</tr>
			<tr>
				<td>Facility Name: {{ $batch->facility->name ?? '' }} </td>
				<td>Contact: {{ $batch->facility->contactperson ?? '' }} </td>
				<td>Tel(personal): {{ $batch->facility->contacttelephone ?? '' }} </td>
			</tr>
			<tr>
				<td colspan='3'>Receiving Address (via Courier): {{ $batch->facility->PostalAddress ?? '' }}</td>
			</tr>
			<tr>
				<td colspan='3'>Email (optional-where provided results will be emailed and also sent by courier ):  {{ $batch->facility->email ?? '' }}</td>
			</tr>
		</table>

		<br />

		<table style="width: 100%;">
			<tr>
				<td colspan="15" style="text-align: center;"><b>SAMPLE LOG</b></td>
			</tr>
			<tr>
				<td colspan="5"><b> Patient Information</b></td>
				<td colspan="4"><b>Samples Information</b></td>
				<td colspan="2"><b>History Information</b></td>
				<td colspan="4"><b>Lab Information</b></td>
			</tr>
			<tr>
				<th><b>No </b></th>
				<th><b>Patient CCC No </b></th>
				<th><b>Sex </b></th>
				<th><b>Age (yrs) </b></th>
				<th><b>ART Initiation Date </b></th>

				<th><b>Date Collected </b></th>
				<th><b>Date Received </b></th>
				<th><b>Status </b></th>
				<th><b>Sample Type </b></th>

				<th><b>Current Regimen </b></th>
				<th><b>Justification </b></th>

				<th><b>Date Tested </b></th>
				<th><b>Date Dispatched </b></th>
				<th><b>Test Result </b></th>
				<th><b>TAT</b></th>
			</tr>
			@foreach($batch->sample as $key => $sample)
				@if($sample->receivedstatus == 2)
					@php  
						$rejection = true;
						continue;
					@endphp
				@endif
				@continue($sample->repeatt == 1)
				<tr>
					<td>{{ ($key+1) }} </td>
					<td>{{ $sample->patient->patient ?? '' }} </td>
					<td> {{ $sample->patient->gender ?? '' }} </td>
					<td>{{ $sample->age }} </td>
					<td>{{ $sample->patient->my_date_format('initiation_date') }} </td>
					<td>{{ $sample->my_date_format('datecollected') }} </td>
					<td>{{ $batch->my_date_format('datereceived') }} </td>
					<td>
	                    @foreach($received_statuses as $received_status)
	                        @if($sample->receivedstatus == $received_status->id)
	                            {{ $received_status->name ?? '' }}
	                        @endif
	                    @endforeach
					</td>
					<td>{{ $sample->sampletype }} </td>
					<td>
	                    @foreach($prophylaxis as $proph)
	                        @if($sample->prophylaxis == $proph->id)
	                            {{ $proph->name }}
	                        @endif
	                    @endforeach
	                </td>
					<td>{{ $sample->justification }} </td>
					<td>{{ $sample->my_date_format('datetested') }} </td>
					<td>{{ $batch->my_date_format('datedispatched') }} </td>
					<td>{{ $sample->result }} </td>
					<td>{{ $sample->tat($batch->datedispatched) }} </td>
				</tr>
			@endforeach		
		</table>

		<p>Result Reviewed By: {{ $sample->approver->full_name ?? '' }}  Date Reviewed: {{ $sample->my_date_format('dateapproved') }}</p>

		@isset($rejection)
			<table>
				<tr>
					<td colspan="12" style="text-align: center;"><b>REJECTED SAMPLE(s)</b></td>
				</tr>
				<tr>
					<td><b> No </b></td>
					<td><b> Patient CCC no </b></td>
					<td><b> Sex </b></td>
					<td><b> Age (yrs) </b></td>
					<td><b> ART Initiation Date </b></td>
					<td><b> Date Collected </b></td>
					<td><b> Date Received </b></td>
					<td><b> Sample Type </b></td>
					<td><b> Current Regimen </b></td>
					<td><b> Justification </b></td>
					<td><b> Rejected Reason </b></td>
					<td><b> Date Dispatched </b></td>			
				</tr>

				@foreach($batch->sample as $key => $sample)
					@continue($sample->receivedstatus != 2)
					<tr>
						<td>{{ ($key+1) }} </td>
						<td>{{ $sample->patient->patient }} </td>
						<td>
		                    @foreach($genders as $gender)
		                        @if($sample->patient->sex == $gender->id)
		                            {{ $gender->gender }}
		                        @endif
		                    @endforeach
						</td>
						<td>{{ $sample->age }} </td>
						<td>{{ $sample->patient->my_date_format('initiation_date') }} </td>
						<td>{{ $sample->my_date_format('datecollected') }} </td>
						<td>{{ $batch->my_date_format('datereceived') }} </td>

						<td>{{ $sample->sampletype }} </td>
						<td>
		                    @foreach($prophylaxis as $proph)
		                        @if($sample->prophylaxis == $proph->id)
		                            {{ $proph->name }}
		                        @endif
		                    @endforeach
		                </td>
						<td>{{ $sample->justification }} </td>

						<td>
		                    @foreach($viral_rejected_reasons as $rejected_reason)
		                        @if($sample->rejectedreason == $rejected_reason->id)
		                            {{ $rejected_reason->name }}
		                        @endif
		                    @endforeach
						</td>
						<td>{{ $batch->my_date_format('datedispatched') }} </td>
					</tr>
				@endforeach	
			</table>
		@endisset

		<p>
			<strong>NOTE:</strong> Always provide the facility's up-to-date email address(es) and mobile number(s) on the sample requisition form so as to get alerts on the status of your samples.
			<br />
			To Access & Download your current and past results go to : http://www.nascop.org/eid/facilitylogon.php
		</p>

		<h5>KEY/CODES</h5>

		<table>
			<tr>
				<td><b>Codes for Sample Type </b> </td>
				<td>
					@foreach($sample_types as $sampletype)
						{{ $sampletype->id . '-' . $sampletype->name }}

						@if($loop->last)
							@break
						@endif
						,&nbsp;
					@endforeach
				</td>
			</tr>
			<tr>
				<td><b>Codes for Justification </b> </td>
				<td>
					@foreach($justifications as $justification)
						{{ $justification->id . '-' . $justification->name }}

						@if($loop->last)
							@break
						@endif
						,&nbsp;
					@endforeach
				</td>				
			</tr>
		</table>

		@if($loop->last)
			@break
		@endif

		<!-- <div class="page-break"></div> -->

		<!-- <pagebreak orientation='L' sheet-size='A4-L'> -->
		<pagebreak sheet-size='A4-L'>

	@endforeach




</body>
</html>

