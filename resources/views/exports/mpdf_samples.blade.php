
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
width:1000px;
}
 .style7 {font-size: medium}
.style10 {font-size: 16px}
.emph {
	font-size: 16px;
	font-weight: bold;
}
p.breakhere {page-break-before: always}
</style>


<body onLoad="JavaScript:window.print();">

	@foreach($samples as $key => $sample)
		<table  border="0" id='table1' align="center">
			<tr>
				<td colspan="9" align="center">
					<span class="style6 style1">
						<strong><img src="{{ asset('img/naslogo.jpg') }}" alt="NASCOP" align="absmiddle" ></strong> 
					</span>
					<span class="style1"><br>
					  <span class="style7">MINISTRY OF HEALTH <br />
					  NATIONAL AIDS AND STD CONTROL PROGRAM (NASCOP)<br />
					  EARLY INFANT HIV DIAGNOSIS (DNA-PCR) RESULT FORM</span>
					</span>
				</td>
			</tr>
			<tr>
				<td colspan="4" class="comment style1 style4">
					<strong> Batch No.: {{ $sample->batch->id }} &nbsp;&nbsp; {{ $sample->batch->facility->name }} </strong> 
				</td>
				<td colspan="4" class="comment style1 style4" align="right">
					<strong>LAB: {{ $sample->batch->lab->name }}</strong>
				</td>
			</tr>

			<tr>
				<td colspan="3" class="style4 style1 comment">
					<strong>Email:</strong> &nbsp; {{ $sample->batch->facility->email }}
				</td>
				<td colspan="3" class="style4 style1 comment">
					<strong>Telephones:</strong> &nbsp; {{ $sample->batch->facility->facility_contacts }}
				</td>				
			</tr>

			<tr>
				<td colspan="2" class="style4 style1 comment">
					<strong>Contact:</strong> &nbsp; {{ $sample->batch->facility->contactperson }}
				</td>
				<td colspan="3" class="style4 style1 comment">
					<strong>Contact Telephones:</strong> &nbsp; {{ $sample->batch->facility->contacts }}
				</td>
				<td colspan="2" class="style4 style1 comment">
					<strong>Contact Email:</strong> &nbsp; {{ $sample->batch->facility->contact_email }}
				</td>				
			</tr>

			<tr>
				<td colspan="3"  class="evenrow" align="center" >
					<span class="style1 style10">
						<strong> DNA PCR TEST RESULTS </strong>
					</span>
				</td>
				<td colspan="4" class="evenrow" align="center">
					<span class="style1 style10">
						<strong> Mother & Infant Information </strong>
					</span>
				</td>
			</tr>

			<tr>
				<td colspan="2" class="style4 style1 comment"><strong> HEI Number</strong></td>
				<td colspan="1"> <span class="style5">{{ $sample->patient->patient }}</span></td>
				<td class="style4 style1 comment" colspan="3"><strong> Infant Prophylaxis </strong></td>
				<td colspan="1" class="comment">
					<span class="style5">
                        @foreach($iprophylaxis as $iproph)
                            @if($sample->regimen == $iproph->id)
                                {{ $iproph->name }}
                            @endif
                        @endforeach						
					</span>
				</td>
			</tr>

			<tr>
				<td colspan="2" class="style4 style1 comment"><strong> DOB & Age (Months)</strong></td>
				<td colspan="1"  ><span class="style5">{{ $sample->patient->my_date_format('dob') }} ({{ $sample->age }})</span></td>
				<td class="style4 style1 comment" colspan="3" ><strong>Infant Feeding </strong></td>
				<td colspan="1" class="comment">
					<span class="style5">
                        @foreach($feedings as $feeding)
                            @if($sample->feeding == $feeding->id)
                                {{ $feeding->feeding }}
                            @endif
                        @endforeach					
					</span>
				</td>
			</tr>

			<tr>
				<td colspan="2" class="style4 style1 comment"><strong> Gender</strong></td>
				<td colspan="1"  ><span class="style5"> {{ $sample->patient->gender }} </span></td>
				<td class="style4 style1 comment" colspan="3" ><strong> Entry Point	</strong></td>
				<td colspan="1" class="comment">
					<span class="style5">
                        @foreach($entry_points as $entry_point)
                            @if($sample->patient->entry_point == $entry_point->id)
                                {{ $entry_point->name }}
                            @endif
                        @endforeach
					</span>
				</td>
			</tr>

			<tr>
				<td colspan="2" class="style4 style1 comment"><strong> PCR Type</strong></td>
				<td colspan="1">
					<span class="style5">
                        @foreach($pcrtypes as $pcrtype)
                            @if($sample->pcrtype == $pcrtype->id)
                                {!! $pcrtype->name !!}
                            @endif
                        @endforeach	
					</span>
				</td>
				<td class="style4 style1 comment" colspan="3" ><strong> Mother CCC #</strong></td>
				<td colspan="1" class="comment">
					<span class="style5"> {{ $sample->patient->mother->ccc_no ?? '' }} </span>
				</td>
			</tr>

			<tr>
				<td colspan="2" class="style4 style1 comment" ><strong>Date	Collected </strong></td>
				<td class="comment" colspan="1">
					<span class="style5">{{ $sample->my_date_format('datecollected') }}</span>
				</td>
				<td class="style4 style1 comment" colspan="3"><strong> Age (Yrs) </strong></td>
				<td colspan="1" > <span class="style5">{{ $sample->mother_age }}</span></td>
			</tr>

			<tr>
				<td colspan="2" class="style4 style1 comment"><strong>Date Received </strong></td>
				<td colspan="1" class="comment" >
					<span class="style5">
						{{ $sample->batch->my_date_format('datereceived') }} 
					</span>
				</td>
				<td class="style4 style1 comment" colspan="3"><strong>PMTCT Intervention </strong></td>
				<td colspan="1" >
					<span class="style5">
	                    @foreach($interventions as $intervention)
	                        @if($sample->mother_prophylaxis == $intervention->id)
	                            {{ $intervention->name }}
	                        @endif
	                    @endforeach
						
					</span>
				</td>
			</tr>

			<tr>
				<td colspan="2" class="style4 style1 comment"><strong>Date Test Performed </strong></td>
				<td colspan="1" class="comment" >
					<span class="style5">{{ $sample->my_date_format('datetested') }}</span>
				</td>
				<td class="style4 style1 comment" colspan="3"><strong> Mother Last VL </strong></td>
				<td colspan="1" ><span class="style5">{{ $sample->mother_last_result }}
					@if($sample->mother_last_result && is_integer($sample->mother_last_result))
						cp/ml
					@endif
				</span></td>
			</tr>

			<tr>
				<td colspan="2" class="style4 style1 comment"><strong>Test Result</strong></td>
				<td colspan="1" class="style4 style1 comment">
					<strong> 
	                    @foreach($results as $result)
	                        @if($sample->result == $result->id)
	                            {{ $result->name }}
	                        @endif
	                    @endforeach
					</strong>
				</td>
				<td colspan="5" class="style4 style1 comment"><strong>Machine:</strong>&nbsp;
					@if($sample->worksheet->machine_type == 1)
						HIV-1 DNA qualitative  assay on CAPCTM system
					@elseif($sample->worksheet->machine_type == 2)
						HIV-1 DNA qualitative  assay on Abbott M2000 system
					@endif
				</td>
			</tr>

			<tr>
				<td colspan="2">
				  <span class="style4 style1 comment"><strong>Comments:</strong></span>
				</td>
				<td colspan="7" class="comment" >
					<span class="style5 ">{{ $sample->comments }} &nbsp; {{ $sample->labcomment }} </span>
				</td>
			</tr>

			@if(env('APP_LAB') != 1)

				<tr>
					{{--<td colspan="12" class="style4 style1 comment">
						&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
						<strong>Result Reviewed By: </strong> 
						&nbsp;&nbsp;&nbsp;&nbsp; 
						<strong> {{ $sample->approver->full_name ?? '' }}</strong> 
						&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
						<strong>Date Reviewed:  {{ $sample->my_date_format('dateapproved') }}</strong>
					</td>--}}

					<td colspan="6" class="style4 style1 comment">
						<center>
							<strong>Result Reviewed By: </strong>
							&nbsp;&nbsp;
							<strong> {{ $sample->approver->full_name ?? '' }}</strong> 
						</center>					
					</td>
					<td colspan="3" class="style4 style1 comment">
						<strong>Date Reviewed:  {{ $sample->my_date_format('dateapproved') }}</strong>
					</td>
					<td colspan="3" class="style4 style1 comment">
						<strong>Date Dispatched:  {{ $sample->batch->my_date_format('datedispatched') }}</strong>
					</td>
				</tr>

			@endif

			<?php $sample->prev_tests();  ?>

			@if($sample->previous_tests->count() > 0)
				@foreach($sample->previous_tests as $prev)

					<tr class="evenrow">
						<td colspan="1"> <span class="style1">Previous EID Results</span></td>
						<td colspan="7" class="comment style5" >
							<strong><small>
								&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
			                    @foreach($results as $result)
			                        @if($prev->result == $result->id)
			                            {{ $result->name }}
			                        @endif
			                    @endforeach
			                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp;
			                    Date Test Performed
			                    {{ $prev->my_date_format('datetested') }}

							</small></strong> 
						</td>
					</tr>

				@endforeach

			@else
				<tr class="evenrow">
					<td colspan="2">
						<span class="style1"><strong>Previous EID Results</strong></span>
					</td>
					<td colspan="5" class="comment" ><span class="style5 "> N/A </span></td>
				</tr>
			@endif

		</table>

		<span class="style8" > 

			@if(env('APP_LAB') == 1)
				If you have questions or problems regarding samples, please contact the KEMRI-NAIROBI Lab at eid-nairobi@googlegroups.com <br />
			@elseif(env('APP_LAB') == 3)
				If you have questions or problems regarding samples, please contact the KEMRI ALUPE HIV Laboratory through 0726156679 or eid-alupe@googlegroups.com <br />
			@else
			@endif

			<b> To Access & Download your current and past results go to : <u> http://eid.nascop.org/login.php</u> </b>
		</span>

		<br>
		<img src="{{ asset('img/but_cut.gif') }}">
		<br>

		@if($key % 2 == 1)
			<p class="breakhere"></p>
			<pagebreak sheet-size='A4-L'>
		@endif



	@endforeach

</body>
</html>