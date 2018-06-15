
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
     		font-size: 10px;
		}

		h5 {
			margin-top: 6px;
		    margin-bottom: 6px;
		}

		p {
			margin-top: 2px;
     		font-size: 6px;
		}
		* {
			font-size: 8px;
		}
	</style>
</head>
<body>

	@foreach($batches as $batch)

		<center><img src="{{ asset('img/naslogo.jpg') }}" alt="NASCOP"></center>

		<p>
			<strong> 
				<center>MINISTRY OF HEALTH</center> <br />
				<center>NATIONAL AIDS AND STD CONTROL PROGRAM (NASCOP)</center><br />
				<center>EARLY INFANT HIV DIAGNOSIS (DNA-PCR) RESULT FORM</center>
			</strong>			
		</p>
		<br />

		<strong> Batch No.: {{ $batch->id }} &nbsp;&nbsp; {{ $batch->facility->name }} </strong> 

		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;

		<strong>LAB: {{ $batch->lab->name ?? '' }}</strong>
		<br />

		<strong>NOTICE:</strong> 
		<br />
		<strong>The Viral Load Test is now available in all EID testing sites. Samples can be collected in DBS form and shipped using the A/C C00339.Call the official EID lines for more information. Thank you.</strong>			

		<br />

		<table>
			<tr>
				<td colspan='3'>Date Samples Were Dispatched :  {{ $batch->my_date_format('datedispatched')  }}</td>				
			</tr>
			<tr>
				<td>Facility Name: {{ $batch->facility->name }} </td>
				<td>Contact: {{ $batch->facility->contactperson ?? '' }} </td>
				<td>Tel(personal): {{ $batch->facility->contacttelephone ?? '' }} </td>
			</tr>
			<tr>
				<td colspan='3'>Receiving Address (via Courier): {{ $batch->facility->PostalAddress }}</td>
			</tr>
			<tr>
				<td colspan='3'>Email (optional-where provided results will be emailed and also sent by courier ):  {{ $batch->facility->email }}</td>
			</tr>
		</table>

		<br />

		<table style="width: 100%;">
			<tr>
				<td colspan="17" style="text-align: center;"><b>SAMPLE LOG </b></td>
			</tr>
			<tr>
				<td colspan="5"><b> Patient Information</b></td>
				<td colspan="4"><b>Samples Information</b></td>
				<td colspan="4"><b>Mother Information</b></td>
				<td colspan="4"><b>Lab Information</b></td>
			</tr>
			<tr>
				<td><b> No</b></td>
				<td><b> Patient ID</b></td>
				<td><b> Sex</b></td>
				<td><b> Age (mths)</b></td>
				<td><b> Prophylaxis</b></td>
				<td><b> Date Collected</b></td>
				<td><b> Date Received</b></td>
				<td><b> Status</b></td>
				<td><b> Test Type</b></td>
				<td><b> HIV Status</b></td>
				<td><b> PMTCT</b></td>
				<td><b> Feeding</b></td>
				<td><b> Entry Point</b></td>
				<td><b> Date Tested</b></td>
				<td><b> Date Dispatched</b></td>
				<td><b> Test Result</b></td>
				<td><b> TAT</b></td>
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
					<td>{{ $sample->patient->patient }} </td>
					<td>
	                    @foreach($genders as $gender)
	                        @if($sample->patient->sex == $gender->id)
	                            {{ $gender->gender }}
	                        @endif
	                    @endforeach
					</td>
					<td>{{ $sample->age }} </td>
					<td>{{ $sample->regimen }} </td>
					<td>{{ $sample->my_date_format('datecollected') }} </td>
					<td>{{ $batch->my_date_format('datereceived') }} </td>
					<td>
	                    @foreach($received_statuses as $received_status)
	                        @if($sample->receivedstatus == $received_status->id)
	                            {{ $received_status->name }}
	                        @endif
	                    @endforeach
					</td>
					<td>{{ $sample->pcrtype }} </td>
					<td>
	                    @foreach($results as $result)
	                        @if($sample->patient->mother->hiv_status == $result->id)
	                            {{ $result->name }}
	                        @endif
	                    @endforeach
					</td>
					<td>{{ $sample->mother_prophylaxis }} </td>
					<td>
	                    @foreach($feedings as $feeding)
	                        @if($sample->feeding == $feeding->id)
	                            {{ $feeding->feeding }}
	                        @endif
	                    @endforeach		
	                </td>
	                <td>{{ $sample->patient->entry_point }} </td>
					<td>{{ $sample->my_date_format('datetested') }} </td>
					<td>{{ $batch->my_date_format('datedispatched') }} </td>
					<td>
	                    @foreach($results as $result)
	                        @if($sample->result == $result->id)
	                            {{ $result->name }}
	                        @endif
	                    @endforeach
					</td>
					<td>{{ $sample->tat($batch->datedispatched) }} </td>
				</tr>
			@endforeach		
		</table>

		Result Reviewed By: {{ $sample->approver->full_name ?? '' }}  Date Reviewed: {{ $sample->my_date_format('dateapproved') }}

		@isset($rejection)
			<table>
				<tr>
					<td colspan="10" style="text-align: center;"><b> REJECTED SAMPLE(s)</b></td>
				</tr>
				<tr>
					<td><b> No</b></td>
					<td><b> Patient ID</b></td>
					<td><b> Sex</b></td>
					<td><b> Age (mths)</b></td>
					<td><b> Prophylaxis</b></td>
					<td><b> Date Collected</b></td>
					<td><b> Date Received</b></td>
					<td><b> Status</b></td>
					<td><b> Rejected Reason</b></td>
					<td><b> Date Dispatched</b></td>			
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
						<td>{{ $sample->regimen }} </td>
						<td>{{ $sample->my_date_format('datecollected') }} </td>
						<td>{{ $batch->my_date_format('datereceived') }} </td>
						<td>
		                    @foreach($received_statuses as $received_status)
		                        @if($sample->receivedstatus == $received_status->id)
		                            {{ $received_status->name }}
		                        @endif
		                    @endforeach
						</td>
						<td>
		                    @foreach($rejected_reasons as $rejected_reason)
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

		<br />
		<br />
		<br />

		<p>
			NOTE: Always provide the facility's up-to-date email address(es) and mobile number(s) on the sample requisition form so as to get alerts on the status of your samples.
			<br />
			To Access & Download your current and past results go to : http://www.nascop.org/eid/facilitylogon.php
		</p>

		<b>KEY/CODES</b>

		<table>
			<tr>
				<td><b>Test Type </b> </td>
				<td>1-1st test, &nbsp; 2-Repeat for Rejection, &nbsp; 3-Confirmatory PCR at 9mths </td>
			</tr>
			<tr>
				<td><b>Entry Point </b> </td>
				<td>
					@foreach($entry_points as $entry_point)
						{{ $entry_point->id . '-' . $entry_point->name }}

						@if($loop->last)
							@break
						@endif
						,&nbsp;
					@endforeach
				</td>
			</tr>
			<tr>
				<td><b>Infant Prophylaxis </b> </td>
				<td>
					@foreach($iprophylaxis as $iproph)
						{{ $iproph->id . '-' . $iproph->name }}

						@if($loop->last)
							@break
						@endif
						,&nbsp;
					@endforeach
				</td>				
			</tr>
			<tr>
				<td><b>Infant Feeding </b> </td>
				<td>
					@foreach($feedings as $feeding)
						{{ $feeding->feeding . ' : ' . $feeding->feeding_description }}

						@if($loop->last)
							@break
						@endif
						,&nbsp;
					@endforeach
				</td>				
			</tr>
			<tr>
				<td><b>PMTCT Intervention </b> </td>
				<td>
					@foreach($interventions as $intervention)
						{{ $intervention->id . '-' . $intervention->name }}

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


		<pagebreak sheet-size='A4-L'>

	@endforeach




</body>
</html>

