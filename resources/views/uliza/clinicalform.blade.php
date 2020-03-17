@extends('uliza.uliza_layout')

@section('content')

	<div class="col-md-12">
		<div class="card my-2">
			<div class="card-header">
				<div class="row">
					<div class="col-md-4">
						<img src="{{ asset('uliza_nascop/uliza-logo.png') }}">						
					</div>
					<div class="col-md-8">
						<b>All Fields are Mandatory i.e. Don't Leave Blanks; Fill N/A where Response is not applicable! <br></b>
						<b>To Add a Clinical Visit, click the button labelled "Add Clinical Visit" then fill in the details and submit - <em>Repeat the same to add another visit</em>.</b>
						<b>In-Case you encounter any problem while filling the Clinical Summary Form Online: Call the Toll-Free Number 0800724848 for Assistance.</b>
					</div>					
				</div>
				<div class="row">
					<div class="col-md-12">
						<div class="alert alert-warning" role="alert">
							To be able to save your work as a draft, you must at least fill in all values from the beginning to the variable requesting for the facility email.
						</div>
					</div>
				</div>				
			</div>			
		</div>
	</div>

	<div class="col-md-12">
		<div class="card border-secondary mb-4">
			<img class="rounded mx-auto d-block img-responsive mt-1" height="161" src="{{ asset('uliza_nascop/logo.jpg') }}" width="160">
			<div class="card-body text-secondary">
				<h5 class="card-title text-center">MINISTRY OF HEALTH</h5>
				<h6 class="card-subtitle mb-2 text-muted text-center">NATIONAL AIDS AND STI CONTROL PROGRAMME</h6>
				<p class="card-text text-center">CLINICAL SUMMARY FORM</p>
			
				<form autocomplete="off" novalidate="" class="ng-invalid ng-dirty ng-touched">
				  
					<div class="form-row mb-3">
						<div class="col-md-2 input-group required">
							<div class="input-group-prepend">
								<span class="input-group-text" id="facility_name">Facility:</span>
							</div>
						</div>
						<div class="col-md-10">
	                        <select class="form-control" required name="facility_id" id="facility_id">
	                            @isset($sample)
	                                <option value="{{ $sample->batch->facility->id }}" selected>{{ $sample->facility->facilitycode }} {{ $sample->facility->name }}</option>
	                            @endisset
	                        </select>						
						</div>
				    </div>
				  
					<div class="form-row mb-3">
						<div class="col-md-7 input-group required">
						    <div class="input-group-prepend">
								<span class="input-group-text" id="cccno">Patient’s CCC No:
									<br>
									<small>(Do not write name)</small>
								</span>
						    </div>
						    <input aria-describedby="cccno" class="form-control" maxlength="10" minlength="10" name="cccno" required type="text">
						</div>
						<div class="col-md-5 input-group required">
							<div class="input-group-prepend">
								<span class="input-group-text" id="reporting_date">Case Reporting Date:</span>
							</div>
							<input class="form-control date" name="reporting_date" required>
						</div>
				    </div>
				  
					<div class="form-row mb-3">
						<div class="col-md-2">
							Patient Details
						</div>
						<div class="col-md-10">
						  
							<div class="form-row mb-3">
								<div class="col-md-6 input-group required">
									<div class="input-group-prepend">
										<span class="input-group-text" id="dob">Date of Birth:</span>
									</div>
									<input class="form-control date" name="dob" required>
								</div>
								<div class="col-md-6 input-group required">
									<div class="input-group-prepend">
										<span class="input-group-text" id="artstart_date">ART Start Date:</span>
									</div>
									<input class="form-control date" name="artstart_date" required>
								</div>
							</div>
					  
							<div class="form-row">
								<div class="col-md-4 input-group required">
									<div class="input-group-prepend">
										<span class="input-group-text" for="gender">Gender:</span>
									</div>
									<select class="custom-select" id="gender" name="gender" required="">
										<option selected="">Choose...</option>
										<option value="Male">Male</option>
										<option value="Female">Female</option>
									</select>
								</div>
						
								<div class="col-md-4 input-group required">
									<div class="input-group-prepend">
										<span class="input-group-text" id="curr_weight">Current Weight (Kg):</span>
									</div>
									<input aria-describedby="curr_weight" class="form-control" maxlength="3" name="curr_weight" required="" type="number">
								</div>
								<div class="col-md-4 input-group required">
									<div class="input-group-prepend">
										<span class="input-group-text" id="height">Height (cm):</span>
									</div>
									<input aria-describedby="height" class="form-control" maxlength="3" name="height" required="" type="number">
								</div>
							</div>
						</div>
					</div>
				  
					<div class="form-row mb-3">
						<div class="col-md-12 input-group required">
							<div class="input-group-prepend">
								<span class="input-group-text text-left" id="clinician_name">Clinician’s Name:</span>
							</div>
							<input aria-describedby="clinician_name" class="form-control" maxlength="75" name="clinician_name" required="" type="text">
						</div>
					</div>
				  
					<div class="form-row">
						<div class="col-md-12">
							You can add three (3) email addresses by separating them with either the space bar, comma, semi-colon, tab key or return/enter key.
						</div>
					</div>
				  
					<div class="form-row mb-3">
						<div class="col-md-12 input-group required">
							<div class="input-group-prepend">
								<span class="input-group-text" id="facility_email">Facility Email Address:</span>
							</div>
							<input aria-describedby="clinician_name" class="form-control" maxlength="75" name="clinician_name" required="" type="text">
						</div>
					</div>
				  
					<div class="form-row mb-3">
						<div class="col-md-5 input-group required">
							<div class="input-group-prepend">
								<span class="input-group-text" id="facility_tel">Facility Tel No:</span>
							</div>
							<input aria-describedby="facility_tel" class="form-control" maxlength="45" name="facility_tel" required="" type="text">
						</div>
					</div>
				  
				  
					<div class="form-row mb-3 required">
						<label class="col-md-12">What is the primary reason for this consultation:</label>
					</div>

					<div class="form-row mb-3">
						@foreach($reasons as $reason)
							<div class="form-group col-md-4">
								<input class="form-check-input ml-1" name="primary_reason" required="" type="radio" id="primary_reason_A{{ $reason->id }}">
								<label class="form-check-label ml-5" for="primary_reason_A{{ $reason->id }}">{{ $reason->name }}</label>
							</div>
						@endforeach
					</div>
				  
					<div class="form-group row">
						<label class="col-md-4 col-form-label">
							Clinical Evaluation: history, physical, diagnostics, working diagnosis:(excluding the information in the table below)
						</label>
						<div class="col-md-8">
							<textarea class="form-control" name="clinical_eval" required="" rows="3"></textarea>
						</div>
					</div>
				  
					<div class="form-row mb-3">
						<div class="col-md-12 card">
							<div class="card-header">
								Clinical Evaluation: history, physical, diagnostics, working diagnosis (excluding the information in the table below Complete the table below chronologically, including all ART regimens and laboratory results (and any previous history available for transfer-in patients)
							</div>
							<div class="card-body">
								<div class="row">
									<div class="col-md-12 mb-3">
										<button class="btn btn-warning float-right">Add Clinical Visit</button>
									</div>
								</div>
								<div class="row">
									<div class="col-md-12">
										<table class="table data-table">
											<thead>
												<tr>
													<th class="index-column-header" scope="col">#</th>
													<th class="column-header" scope="col"> Date (yyyy-mm-dd) </th>
													<th class="column-header" scope="col"> CD4 </th>
													<th class="column-header" scope="col"> HB </th>
													<th class="column-header" scope="col"> CrCl/ eGFR </th>
													<th class="column-header" scope="col"> Viral Load </th>
													<th class="column-header" scope="col"> Weight </th>
													<th class="column-header" scope="col"> ARV Regimen </th>
													<th class="column-header" scope="col"> Reason for Switch </th>
													<th class="column-header" scope="col"> New OI </th>
													<th class="column-header" scope="col"> Actions </th>
												</tr>
											</thead>
											<tbody>
												<tr>
													<td colspan="11">No available data</td>
												</tr>
											</tbody>
										</table>
									</div>
								</div>
							</div>
						</div>
					</div>
				  
					<div class="form-row mb-3">
						<div class="col-md-12 input-group required">
							<p class="font-weight-bold"> Adherence and Treatment Failure Evaluation </p>
						</div>
					</div>
				  
				  
					<div class="form-group required row">
						<label class="col-md-4 col-form-label">
							Number of adherence counseling/assessment sessions done in the last 3-6 months:
						</label>
						<div class="col-md-8">
							<input class="form-control" name="no_adhearance_counseling" required="" type="number">
						</div>
					</div>
				  
				  
					<div class="form-group row">
						<label class="col-md-4 col-form-label">
							Number of home visits conducted in last 3-6 months, and findings:
						</label>
						<div class="col-md-8">
						  <input class="form-control" name="no_homevisits" required="" type="number">
						</div>
					</div>
				  
					<div class="form-group row">
						<label class="col-md-4 col-form-label">
							Support structures (e.g. treatment buddy, support group attendance, caregivers) in place for this patient?
						</label>
						<div class="col-md-8">
							<textarea class="form-control" name="support_structures" required="" rows="4"></textarea>
						</div>
					</div>
				  
					<div class="form-group row">
						<label class="col-md-4 col-form-label">
							Evidence of adherence concerns (e.g. missed appointments, pill counts?):
						</label>
						<div class="col-md-8">
							<textarea class="form-control" name="adherence_concerns" required="" rows="4"></textarea>
						</div>
					</div>
				  
					<div class="form-group row">
						<label class="col-md-4 col-form-label">Number of DOTS done in last 3-6 months:</label>
						<div class="col-md-8">
							<input class="form-control" name="no_dotsdone" required="" type="number">
						</div>
					</div>
				  
					<div class="form-group row">
						<label class="col-md-4 col-form-label">
							Likely root cause/s of poor adherence, for this patient (e.g. stigma, disclosure, side effects, alcohol or other drugs, mental health issues, caregiver changes, religious beliefs, inadequate preparation, etc):
						</label>
						<div class="col-md-8">
						  <textarea class="form-control" name="likely_rootcauses" required="" rows="4"></textarea>
						</div>
					</div>
				  
					<div class="form-row mb-3">
						<div class="col-md-12 input-group required">
							<p class="font-weight-bold"> Evaluation for other causes of treatment failure, e.g. </p>
						</div>
					</div>
				  
					<div class="form-group row">
						<label class="col-md-4 col-form-label">• Inadequate dosing/dose adjustments (particularly for children)::</label>
						<div class="col-md-8">
							<textarea class="form-control" name="inadequate_dosing" required="" rows="4"></textarea>
						</div>
					</div>
				  
					<div class="form-group row">
						<label class="col-md-4 col-form-label">• Drug-drug interactions:</label>
						<div class="col-md-8">
							<textarea class="form-control" name="drug_interactions" required="" rows="4"></textarea>
						</div>
					</div>
				  
					<div class="form-group row">
						<label class="col-md-4 col-form-label">• Drug-food interactions:</label>
						<div class="col-md-8">
							<textarea class="form-control" name="food_interactions" required="" rows="4"></textarea>
						</div>
					</div>
				  
					<div class="form-group row">
						<label class="col-md-4 col-form-label">• Impaired absorption (e.g. chronic severe diarrhea):</label>
						<div class="col-md-8">
							<textarea class="form-control" name="impaired_absorption" required="" rows="4"></textarea>
						</div>
					</div>
				  
					<div class="form-row mb-3">
						<div class="col-md-12 input-group required">
							<p class="font-weight-bold"> Other Relevant ART History. </p>
						</div>
					</div>
				  
					<div class="form-group row">
						<label class="col-md-4 col-form-label">Comment on treatment interruptions, if any:</label>
						<div class="col-md-8">
							<textarea class="form-control" name="treatment_interruptions" required="" rows="4"></textarea>
						</div>
					</div>
				  
					<div class="form-group row">
						<label class="col-md-4 col-form-label">
							Has Drug Resistance/Sensitivity Testing been done for this patient? If yes, state date done and attach the detailed results.
						</label>
						<div class="col-md-8">
							<textarea class="form-control" name="drt_testing" required="" rows="4"></textarea>
						</div>
					</div>
				  
					<div class="form-group row">
						<label class="col-md-4 col-form-label">
							Has facility multidisciplinary team discussed the patient’s case?. If yes, comment on date, deliberations and recommendations.
							<br>
							(indicate how treatment failure was established and confirmed, proposed regimen and dosage, current source of drugs if patient already on 3rd line). If yes, state date done and attach the detailed results:
						</label>
						<div class="col-md-8">
							<textarea class="form-control" name="mdt_discussions" required="" rows="6"></textarea>
						</div>
					</div>
				  
					<div class="form-group row">
						<label class="col-md-4 col-form-label">
							MDT members who participated in the case discussion (names and titles)
						</label>
						<div class="col-md-8">
							<textarea class="form-control" maxlength="255" name="mdt_members" required="" rows="6"></textarea>
						</div>
					</div>
				  
					<div class="mb-3 float-right">
						<button class="btn btn-warning" type="button" disabled="">Submit</button>
					</div>
				  
					<div class="mb-3 float-centre">
						<button class="btn btn-default" type="button">Save As Draft</button>
					</div>
				  
					<div class="mb-3 float-centre"></div>
				</form>
			</div>
		</div>
		
	</div>



@endsection

@section('scripts')
<script type="text/javascript">
	$(document).ready(function(){
		set_select_facility("facility_id", "{{ url('/facility/search') }}", 3, "Search for facility", false);

        $(".form-horizontal").validate({
            errorPlacement: function (error, element)
            {
                element.before(error);
            }
        });

        $(".date-normal").datepicker({
            startView: 2,
            todayBtn: "linked",
            keyboardNavigation: false,
            forceParse: true,
            autoclose: true,
            // startDate: "-6m",
            // endDate: new Date(),
            format: "yyyy-mm-dd"
        });
	});
</script>
@endsection