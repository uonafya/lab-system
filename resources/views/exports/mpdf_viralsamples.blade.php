
<html>
<style type="text/css">
<!--
.style1 {font-family: "Courier New", Courier, monospace}
.style4 {font-size: 12}
.style5 {font-family: "Courier New", Courier, monospace; font-size: 12; }
.style8 {font-family: "Courier New", Courier, monospace; font-size: 11; }
.style6 {
	font-size: medium;
	font-weight: bold;
}
-->
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
p.breakhere {page-break-before: always}
</style>


<body onLoad="JavaScript:window.print();">

	@foreach($samples as $key => $sample)
		<table  border="0" id='table1' align="center">
			<tr>
				<td colspan="8" align="center">
					<span class="style6 style1">
						<strong><img src="{{ asset('img/naslogo.jpg') }}" alt="NASCOP" align="absmiddle" ></strong> 
					</span>
					<span class="style1"><br />
					<span class="style7">MINISTRY OF HEALTH <br />
					NATIONAL AIDS AND STD CONTROL PROGRAM (NASCOP)<br />
					INDIVIDUAL VIRAL LOAD RESULT FORM</span></span>
				</td>
			</tr>
			<tr>
				<td colspan="3" class="comment style1 style4">
					<strong> Batch No.: {{ $sample->batch->id }} &nbsp;&nbsp; {{ $sample->batch->facility->name }} </strong> 
				</td>
				<td colspan="4" class="comment style1 style4" align="right">
					<strong>LAB: {{ $sample->batch->lab->name }}</strong>
				</td>
			</tr>

			<tr>
				<td colspan="3"  class="evenrow" align="center" >
					<span class="style1 style10">
						<strong> Viral Load Results </strong>
					</span>
				</td>
				<td colspan="4" class="evenrow" align="center">
					<span class="style1 style10">
						<strong> Historical  Information </strong>
					</span>
				</td>
			</tr>


			<tr>
				<td colspan="1" class="style4 style1 comment"><strong> Patient CCC No</strong></td>
				<td colspan="2"> <span class="style5">{{ $sample->patient->patient }}</span></td>
				<td colspan="2"  class="style4 style1 comment" ><strong> Sample Type </strong></td>
				<td colspan="2" class="comment" >
					<span class="style5" > 
	                    @foreach($sample_types as $sample_type)
	                        @if($sample->sampletype == $sample_type->id)
	                            {{ $sample_type->name }}
	                        @endif
	                    @endforeach						
					</span>
				</td>
			</tr>
			<tr >
				<td colspan="1" class="style4 style1 comment"><strong>DOB & Age (Years)</strong></td>
				<td colspan="1"  >
					<span class="style5">{{ $sample->patient->my_date_format('dob') }} {{ $sample->age }} </span>
				</td>
				<td class="style4 style1 comment" colspan="3" ><strong>Justification </strong></td>
				<td colspan="1" class="comment">
					<span class="style5">
	                    @foreach($justifications as $justification)
	                        @if($sample->justification == $justification->id)
	                            {{ $justification->name }}
	                        @endif
	                    @endforeach
					</span>
				</td>
			</tr>

			<tr >
				<td colspan="1" class="style4 style1 comment"><strong>Gender</strong></td>
				<td colspan="1"  ><span class="style5"> {{ $sample->patient->gender }} </span></td>
				<td class="style4 style1 comment" colspan="3" ><strong>PMTCT</strong></td>
				<td colspan="1" class="comment">
					<span class="style5">
	                    @foreach($pmtct_types as $pmtct_type)
	                        @if($sample->pmtct == $pmtct_type->id)
	                            {{ $pmtct_type->name }}
	                        @endif
	                    @endforeach						
					</span>
				</td>
			</tr>

			<tr >
				<td colspan="1" class="style4 style1 comment" ><strong>Date	 Collected </strong></td>
				<td  class="comment" colspan="1"> 
					<span class="style5">{{ $sample->my_date_format('datecollected') }}</span>
				</td>
				<td class="style4 style1 comment" colspan="3">
					<strong> ART Initiation Date </strong>
				</td>
				<td colspan="1">
					<span class="style5">
						{{ $sample->patient->my_date_format('initiation_date') }}
					</span>
				</td>
			</tr>

			<tr >
				<td colspan="1" class="style4 style1 comment"><strong>Date Received </strong></td>
				<td colspan="1" class="comment" ><span class="style5"></span><span class="style5">{{ $sample->batch->my_date_format('datereceived') }}</span></td>
				<td class="style4 style1 comment" colspan="3"><strong>Current ART Regimen	</strong></td>
				<td colspan="1" class="comment">
					<span class="style5">
	                    @foreach($prophylaxis as $proph)
	                        @if($sample->prophylaxis == $proph->id)
	                            {{ $proph->name }}
	                        @endif
	                    @endforeach						
					</span>
				</td>
			</tr>

			<tr >
				<td colspan="1" class="style4 style1 comment" width="220px"><strong>Date Tested </strong></td>
				<td colspan="1" class="comment" ><span class="style5">{{ $sample->my_date_format('datetested') }}</span></td>
				<td class="style4 style1 comment" colspan="3" ><strong>Date Initiated on Current Regimen </strong></td>
				<td colspan="1" class="comment"><span class="style5"><?php echo  '22-June-2016' ;?></span></td>
			</tr>

			<?php

				if($sample->receivedstatus != 2){
					$routcome = '<u>' . $sample->result . '</u> ' . $sample->units;
					$resultcomments="";
					$vlresultinlog='N/A';

					if ($sample->result == '< LDL copies/ml'){
						$resultcomments="<small>LDL:Lower Detectable Limit i.e. Below Detectable levels by machine( Roche DBS <400 copies/ml , Abbott DBS  <550 copies/ml )</small> ";
					}

					if (is_numeric($sample->result) ){
						$vlresultinlog= round(log10($sample->result),1) ;
					}
				}
				else{
					$reason = $viral_rejected_reasons->where('id', $sample->rejectedreason)->first()->name;
					$status = $received_statuses->where('id', $sample->receivedstatus)->first()->name;
					$routcome= "Sample ".$status . " Reason:  ".$reason;
				}
				$sample->prev_tests();

				$s_type = $sample_types->where('id', $sample->sampletype)->first();

				$test_no = $sample->previous_tests->count();
				$test_no++;

				if(($sample->result > 1000 && $s_type->typecode == 2)
					 || ($sample->result > 5000 && $s_type->typecode == 1))
				{
					$outcome_code = "b";
				}

				else if(($sample->result < 1000 && $s_type->typecode == 2)
					 || ($sample->result < 5000 && $s_type->typecode == 1))
				{
					$outcome_code = "a";
				}
				else{
					$outcome_code = "a";
				}

				$vlmessage='';
				if($sample->receivedstatus == 2){
					$vlmessage='';
				}
				else if($sample->receivedstatus != 2 && $sample->result == "Collect New Sample"){
					$vlmessage='Failed Test';
				}
				else{
					$guideline = $vl_result_guidelines->where('test', $test_no)->where('triagecode', $outcome_code)->where('sampletype', $s_type->typecode)->first();

					if($guideline){
						$vlmessage = $guideline->indication;
					}
				}

			?>
	
			<tr>
				<td colspan="1" class="evenrow">
					<span class="style1"><strong> Test Result </strong></span>
				</td>
				<td colspan="6" class="evenrow">
					<span class="style5">
						<strong>
							@if($sample->receivedstatus == 2)
								{{ $routcome }}
							@else
								&nbsp;&nbsp;&nbsp;&nbsp; Viral Load{{ $routcome}} &nbsp;&nbsp;&nbsp; Log 10 
								<u>{{ $vlresultinlog}} </u>
							@endif
		 
						</strong>
					</span>
				</td>
			</tr>


			<tr>
				<td colspan="2">
				  <span class="style1"><strong>Comments:</strong></span>
				</td>
				<td colspan="5" class="comment" >
					<span class="style5 "><b> {{ $vlmessage }}</b> <br> {{ $sample->labcomment }} </span>
				</td>
			</tr>

			@if($sample->previous_tests->count() > 0)
				@foreach($sample->previous_tests as $prev)

					<tr class="evenrow">
						<td colspan="1"> <span class="style1">Previous VL Results</span></td>
						<td colspan="7" class="comment style5" >
							<strong><small>Viral Load {{ $prev->result . ' ' . $prev->units }} &nbsp; Date Tested {{ $prev->my_date_format('datetested') }} </small></strong> 
						</td>
					</tr>

				@endforeach

			@else
				<tr>
					<td colspan="2">
						<span class="style1"><strong>Previous VL Results</strong></span>
					</td>
					<td colspan="5" class="comment" ><span class="style5 "> N/A </span></td>
				</tr>
			@endif
			
			<tr >
				<td colspan="12" class="style4 style1 comment">
					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					<strong>Result Reviewed By: </strong> 
					&nbsp;&nbsp;&nbsp;&nbsp; 
					<strong> {{ $sample->approver->full_name ?? '' }}</strong> 
					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					<strong>Date Reviewed:  {{ $sample->my_date_format('dateapproved') }}</strong>
				</td>
			</tr>
		</table>

		<span class="style8" > 
			If you have questions or problems regarding samples, please contact the {{ $sample->batch->lab->name }}  
			<br> 
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			at {{ $sample->batch->lab->email }}
			<br> 
			<b> To Access & Download your current and past results go to : <u> http://eid.nascop.org/login.php</u> </b>
		</span>

		<br>
		<br>
		<img src="{{ asset('img/but_cut.gif') }}">
		<br>
		<br>

		@if($key % 2 == 1)
			<p class="breakhere"></p>
			<pagebreak sheet-size='A4-L'>
		@endif

	@endforeach

</body>
</html>