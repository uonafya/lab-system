<html>
<head>
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
	 } #table1 {
	border : solid 1px black;
	width:1000px;
	width:900px;
	}
	 .style7 {font-size: medium}
	.style10 {font-size: 16px}
	</style>

	<STYLE TYPE="text/css">
	     P.breakhere {page-break-before: always}

	}

	</STYLE> 
</head>
<body>
	<htmlpageheader name="page-header"></htmlpageheader>

	<table border="0" id='table1' align="center">
		<tr>
			<td colspan="9" align="center">
				<span class="style6 style1">
					<strong><img src="<?php echo asset('img/naslogo.jpg') ; ?>" alt="NASCOP" align="absmiddle"></strong> 
				</span>
				<span class="style1"><br>
				  <span class="style7">MINISTRY OF HEALTH <br />
				  NATIONAL AIDS AND STD CONTROL PROGRAM (NASCOP)<br />
				  EARLY INFANT HIV DIAGNOSIS (DNA-PCR) RESULT FORM</span>
				</span>
			</td>
		</tr>
		<tr>
			<td colspan="5" class="comment style1 style4">
				<strong> Batch No.: <?php echo $batch->id ; ?> &nbsp;&nbsp; <?php echo $batch->facility->name ; ?> </strong> 
			</td>
			<td colspan="4" class="comment style1 style4" align="right">
				<strong>LAB: <?php echo $batch->lab->name ; ?></strong>
			</td>
		</tr>
		<tr>
			<td colspan="9" class="comment style1 style4">
				<strong>NOTICE:</strong> 
			</td>
		</tr>
		<tr>
			<td colspan="9" class="comment style1 style4">
				<strong>The Viral Load Test is now available in all EID testing sites. Samples can be collected in DBS form and shipped using the A/C C00339.Call the official EID lines for more information. Thank you.</strong>
			</td>
		</tr>
	</table>

	<br />

	<table>
		<tr>
			<td colspan='3'>Date Samples Were Dispatched</td>				
		</tr>
		<tr>
			<td>Facility Name: <?php echo $batch->facility->name ; ?> </td>
			<td>Contact: <?php echo $batch->facility->contactperson ; ?> </td>
			<td>Tel(personal): <?php echo $batch->facility->contacttelephone ; ?> </td>
		</tr>
		<tr>
			<td colspan='3'>Receiving Address (via Courier): <?php echo $batch->facility->PostalAddress ; ?></td>
			<td colspan='3'>Email (optional-where provided results will be emailed and also sent by courier ):  <?php echo $batch->facility->email ; ?></td>
		</tr>
	</table>

	<br />

	<table>
		<tr>
			<td colspan="17">SAMPLE LOG</td>
		</tr>
		<tr>
			<td colspan="5">Patient Information</td>
			<td colspan="4">Samples Information</td>
			<td colspan="4">Mother Information</td>
			<td colspan="4">Lab Information</td>
		</tr>
		<tr>
			<td>No</td>
			<td>Patient ID</td>
			<td>Sex</td>
			<td>Age (mths)</td>
			<td>Prophylaxis</td>
			<td>Date Collected</td>
			<td>Date Received</td>
			<td>Status</td>
			<td>Test Type</td>
			<td>HIV Status</td>
			<td>PMTCT</td>
			<td>Feeding</td>
			<td>Entry Point</td>
			<td>Date Tested</td>
			<td>Date Dispatched</td>
			<td>Test Result</td>
			<td>TAT</td>
		</tr>

		@foreach($samples as $key => $sample)
			@if($sample->receivedstatus == 2)
				@php  
					$rejection = true;
					continue;
				@endphp

			@endif
			<tr>
				<td><?php echo ($key+1) ; ?> </td>
				<td><?php echo $sample->patient->patient ; ?> </td>
				<td>
                    @foreach($genders as $gender)
                        @if($sample->patient->sex == $gender->id)
                            <?php echo $gender->gender ; ?>
                        @endif
                    @endforeach
				</td>
				<td><?php echo $sample->age ; ?> </td>
				<td><?php echo $sample->regimen ; ?> </td>
				<td><?php echo $sample->datecollected ; ?> </td>
				<td><?php echo $batch->datereceived ; ?> </td>
				<td>
                    @foreach($received_statuses as $received_status)
                        @if($sample->receivedstatus == $received_status->id)
                            <?php echo $received_status->name ; ?>
                        @endif
                    @endforeach
				</td>
				<td><?php echo $sample->pcrtype ; ?> </td>
				<td>
                    @foreach($results as $result)
                        @if($sample->patient->mother->hiv_status == $result->id)
                            <?php echo $result->name ; ?>
                        @endif
                    @endforeach
				</td>
				<td><?php echo $sample->mother_prophylaxis ; ?> </td>
				<td>
                    @foreach($feedings as $feeding)
                        @if($sample->feeding == $feeding->id)
                            <?php echo $feeding->feeding ; ?>
                        @endif
                    @endforeach		
                </td>
                <td><?php echo $sample->patient->mother->entry_point ; ?> </td>
				<td><?php echo $sample->datetested ; ?> </td>
				<td><?php echo $sample->datedispatched ; ?> </td>
				<td>
                    @foreach($results as $result)
                        @if($sample->result == $result->id)
                            <?php echo $result->name ; ?>
                        @endif
                    @endforeach
				</td>
				<td></td>
			</tr>
		@endforeach		
	</table>

	<?php if(isset($rejection)){ ?>
		<table>
			<tr>
				<td colspan="10">REJECTED SAMPLE(s)</td>
			</tr>
			<tr>
				<td>No</td>
				<td>Patient ID</td>
				<td>Sex</td>
				<td>Age (mths)</td>
				<td>Prophylaxis</td>
				<td>Date Collected</td>
				<td>Date Received</td>
				<td>Status</td>
				<td>Rejected Reason</td>
				<td>Date Dispatched</td>			
			</tr>

			@foreach($samples as $key => $sample)
				@continue($sample->receivedstatus != 2)
				<tr>
					<td><?php echo ($key+1) ; ?> </td>
					<td><?php echo $sample->patient->patient ; ?> </td>
					<td>
	                    @foreach($genders as $gender)
	                        @if($sample->patient->sex == $gender->id)
	                            <?php echo $gender->gender ; ?>
	                        @endif
	                    @endforeach
					</td>
					<td><?php echo $sample->age ; ?> </td>
					<td><?php echo $sample->regimen ; ?> </td>
					<td><?php echo $sample->datecollected ; ?> </td>
					<td><?php echo $batch->datereceived ; ?> </td>
					<td>
	                    @foreach($received_statuses as $received_status)
	                        @if($sample->receivedstatus == $received_status->id)
	                            <?php echo $received_status->name ; ?>
	                        @endif
	                    @endforeach
					</td>
					<td>
	                    @foreach($rejected_reasons as $rejected_reason)
	                        @if($sample->rejectedreason == $rejected_reason->id)
	                            <?php echo $rejected_reason->name ; ?>
	                        @endif
	                    @endforeach
					</td>
					<td><?php echo $sample->datedispatched ; ?> </td>
				</tr>
			@endforeach	
		</table>
	<?php } ?>


	Result Reviewed By: <?php echo $sample->approver->full_name ; ?>  Date Reviewed: <?php echo $sample->dateapproved ; ?>



	<htmlpagefooter name="page-footer">
		Your Footer Content
	</htmlpagefooter>


</body>
</html>