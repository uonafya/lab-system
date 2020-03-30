
<html>
<style type="text/css">
.style1 {font-family: "Courier New", Courier, monospace}
.style4 {font-size: 12}
.style5 {font-family: "Courier New", Courier, monospace; font-size: 12; }
.style8 {font-family: "Courier New", Courier, monospace; font-size: 11; }
.style6 {
	font-size: medium;
	font-weight: bold;
}
</style>
<style>

 td
 {

 }
 /*@page{
 	size: portrait;
 }*/
 .oddrow
 {
 background-color : #CCCCCC;
 }
 .evenrow
 {
 background-color : #F0F0F0;
 } 
#table1 {
border : solid 1px black;
width:1000px;
}
 /*.style7 {font-size: medium}*/
 .style7 {font-size: 13px}
.style10 {font-size: 16px}
.emph {
	font-size: 16px;
	font-weight: bold;
}
p.breakhere {page-break-before: always}
</style>

<!-- Naslogo dimensions height=64 width=80 -->
<body onLoad="JavaScript:window.print();">

	<?php $count = 0; ?>

	@foreach($samples as $key => $sample)
		@continue($sample->repeatt == 1)

		<table id="table1" align="center">

			<tr>
				<td colspan="7" align="center">
					<strong><img src="https://eiddash.nascop.org/img/naslogo.jpg" alt="NASCOP"></strong> 
					<span class="style1"><br>
					  <span class="style7">MINISTRY OF HEALTH <br />
					  COVID 19 RESULT FORM</span>
					</span>
				</td>
			</tr>
			<tr>
				<td colspan="4" class="comment style1 style4">
					<strong> Batch No.: {{ $sample->patient->facility->county ?? '' }} &nbsp;&nbsp; {{ $sample->patient->facility->name ?? '' }} </strong> 
				</td>
				<td colspan="3" class="comment style1 style4" align="right">
					<strong>Testing Lab: {{ $sample->lab->name ?? '' }}</strong>
				</td>
			</tr>

			@if(env('APP_LAB') == 1)

				<tr>
					<td colspan="7" class="style4 style1 comment">
						<strong>Contact/Facility Telephone:</strong>
						{{ $sample->facility->contacts }} &nbsp;&nbsp;
						{{ $sample->facility->facility_contacts }}
					</td>		
				</tr>			

				<tr>
					<td colspan="7" class="style4 style1 comment">
						<strong>Contact/Facility Email:</strong> &nbsp; {{ $sample->facility->email_string }}
					</td>					
				</tr>

			@endif

			@if(env('APP_LAB') == 5 && $sample->amrs_location)			

				<tr>
					<td colspan="7" class="style4 style1 comment">
						<strong>AMRS Location:</strong> &nbsp; {{ $amrs_locations->where('id', $sample->amrs_location)->first()->name ?? ''  }}
					</td>					
				</tr>

			@endif

			<tr>
				<td colspan="7"  class="evenrow" align="center" >
					<span class="style1 style10">
						<strong> Covid-19 Test Results </strong>
					</span>
				</td>
			</tr>

			<tr>
				<td colspan="2" class="style4 style1 comment"><strong> Unique Case Identifier</strong></td>
				<td colspan="1"> <span class="style5">{{ $sample->patient->identifier }}</span></td>
				<td class="style4 style1 comment" colspan="3"><strong> Citizenship </strong></td>
				<td colspan="1" class="comment">
					<span class="style5">
						{{ $sample->patient->get_prop_name($nationalities, 'nationality') }}
					</span>
				</td>
			</tr>

			<tr>
				<td colspan="2" class="style4 style1 comment"><strong> DOB & Age (Months)</strong></td>
				<td colspan="1"  ><span class="style5">{{ $sample->patient->my_date_format('dob') }} ({{ $sample->age }})</span></td>
				<td class="style4 style1 comment" colspan="3" ><strong>Area of Residence </strong></td>
				<td colspan="1" class="comment">
					<span class="style5">
						{{ $sample->patient->residence }}			
					</span>
				</td>
			</tr>

			<tr>
				<td colspan="2" class="style4 style1 comment"><strong>Gender </strong></td>
				<td colspan="1"  ><span class="style5"> {{ $sample->patient->gender }} </span></td>
				<td class="style4 style1 comment" colspan="3" ><strong> Health Status at time of reporting	</strong></td>
				<td colspan="1" class="comment">
					<span class="style5">
						{{ $sample->get_prop_name($health_statuses, 'health_status') }}
					</span>
				</td>
			</tr>

			<tr>
				<td colspan="2" class="style4 style1 comment" ><strong>Date	Collected </strong></td>
				<td class="comment" colspan="1">
					<span class="style5">{{ $sample->my_date_format('datecollected') }}</span>
				</td>
				<td class="style4 style1 comment" colspan="3"><strong> Sample Type </strong></td>
				<td colspan="1" > <span class="style5">{{ $sample->get_prop_name($covid_sample_types, 'sampletype') }}</span></td>
			</tr>

			<tr>
				<td colspan="2" class="style4 style1 comment"><strong>Date Received </strong></td>
				<td colspan="1" class="comment" >
					<span class="style5">
						{{ $sample->my_date_format('datereceived') }} 
					</span>
				</td>
				<td class="style4 style1 comment" colspan="3"><strong>Reason for Test </strong></td>
				<td colspan="1" >
					<span class="style5">
						{{ $sample->patient->get_prop_name($covid_justifications, 'justification') }}					
					</span>
				</td>
			</tr>

			<tr>
				<td colspan="2" class="style4 style1 comment"><strong>Date Test Performed </strong></td>
				<td colspan="1" class="comment" >
					<span class="style5">{{ $sample->my_date_format('datetested') }}</span>
				</td>
				<td class="style4 style1 comment" colspan="3"><strong> </strong></td>
				<td colspan="1" ><span class="style5"> </span></td>
			</tr>

			<tr>
				

				@if($sample->receivedstatus == 2)
					<td colspan="2" class="style4 style1 comment"><strong>Sample Rejected. Reason:</strong></td>

					<td colspan="4" class="style4 style1 comment">
						 {{ $rejected_reasons->where('id', $sample->rejectedreason)->first()->name ?? '' }}
					</td>


				@else
					<td colspan="3" class="style4 style1 comment"><strong>Test Result</strong></td>

					<td colspan="1" class="style4 style1 comment">
						<strong> 
		                    @foreach($results as $result)
		                        @if($sample->result == $result->id)
		                        	<span
		                        		@if($result->id == 2)
		                        			class="emph"
		                        		@endif

		                        	> {{ $result->name }} </span>
		                            
		                        @endif
		                    @endforeach
						</strong>
					</td>
					<td colspan="3"></td>

				@endif
			</tr>

			
			@if($sample->worksheet)
				<tr>
					<td colspan="2"></td>
					<td colspan="5" class="style4 style1 comment">					
						@if($sample->worksheet->machine_type == 1)
							HIV-1 DNA qualitative  assay on Roche CAP/CTM system
						@elseif($sample->worksheet->machine_type == 2)
							HIV-1 DNA qualitative  assay on Abbott M2000 system
						@endif					
					</td>				
				</tr>
			@endif
			

			<tr>
				<td colspan="2">
				  <span class="style4 style1 comment"><strong>Comments:</strong></span>
				</td>
				<td colspan="5" class="comment" >
					<span class="style5 ">{{ $sample->comments }} &nbsp; {{ $sample->labcomment }}
						@if($sample->result == 2 && $sample->pcrtype < 4)
							&nbsp; Initiate on ART, Collect samples for Confirmatory Testing & Baseline Viral Load
						@endif

					</span>
				</td>
			</tr>

			@if(env('APP_LAB') != 1)

				<tr>
					<td colspan="7" class="style4 style1 comment">
						<center>
							<strong>Result Reviewed By: </strong>
							&nbsp;&nbsp;
							<strong> {{ $sample->approver->full_name ?? '' }}</strong> 
						</center>					
					</td>
				</tr>
				<tr>
					<td colspan="3" class="style4 style1 comment">
						<strong>Date Reviewed:  {{ $sample->my_date_format('dateapproved') }}</strong>
					</td>
					<td colspan="4" class="style4 style1 comment">
						<strong>Date Dispatched:  {{ $sample->my_date_format('datedispatched') }}</strong>
					</td>
				</tr>

			@else

				<tr>
					<td colspan="2" class="style4 style1 comment">
						<strong>Date Dispatched:  </strong>
					</td>
					<td colspan="5" class="style4 style1 comment">
						{{ $sample->my_date_format('datedispatched') }}
					</td>
				</tr>

			@endif

		</table>

		@if($sample->site_entry != 2)

			<span class="style8" > 

				@if(env('APP_LAB') == 1)
					If you have questions or problems regarding samples, please contact the KEMRI-NAIROBI Lab at eid-nairobi@googlegroups.com <br />
				@elseif(env('APP_LAB') == 3)
					If you have questions or problems regarding samples, please contact the KEMRI ALUPE HIV Laboratory through 0726156679 or eid-alupe@googlegroups.com <br />
				@else
					If you have questions or problems regarding samples, please contact the {{ $sample->lab->name }} at {{ $sample->lab->email }}
				@endif

			</span>

		@endif

		@if($count % 2 == 0)
			<p class="breakhere"></p>
			<pagebreak sheet-size='A4'>
		@else
			<br/> <br/> <img src="https://eiddash.nascop.org/img/but_cut.gif"> <br/><br/> 
		@endif



	@endforeach

</body>
</html>