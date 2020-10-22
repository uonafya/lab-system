<html>

<head>
    <!-- <link rel="stylesheet" href="{{ public_path('vendor/bootstrap/dist/css/bootstrap.css') }}" />	 -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
</head>


<body >
	

	<div class="col-md-12" id="my-vue-instance">
		<div class="card border-secondary mb-4">
			<img class="rounded mx-auto d-block img-responsive mt-1" height="161" src="{{ public_path('uliza_nascop/logo.jpg') }}" width="160">
			<div class="card-body text-secondary">
				<h5 class="card-title text-center">MINISTRY OF HEALTH</h5>
				<h5 class="card-subtitle mb-2 text-muted text-center">NATIONAL AIDS AND STI CONTROL PROGRAMME</h5>
				<p class="card-text text-center">CLINICAL SUMMARY FORM</p>
			
				<form autocomplete="off" @submit.prevent="update" novalidate="" id="myClinicalForm">
				  
					<div class="form-row mb-3">
						<div class="col-md-2 input-group required">
							<div class="input-group-prepend">
								<span class="input-group-text" id="facility_name">
									Facility:
									<div style='color: #ff0000; display: inline;'>*</div>
								</span>
							</div>
						</div>
						<div class="col-md-10">
							<input type="text" name="" value="{{ $ulizaClinicalForm->view_facility->facilitycode ?? '' }} - {{ $ulizaClinicalForm->view_facility->name ?? '' }}">
						</div>
				    </div>
				  
					<div class="form-row mb-3">
						<div class="col-md-7 input-group required">
						    <div class="input-group-prepend">
								<span class="input-group-text" id="cccno">Patient’s CCC No:
									<small>(Do not write name)</small>
									<div style='color: #ff0000; display: inline;'>*</div>
								</span>
						    </div>
						    <input aria-describedby="cccno" class="form-control initial_fields" v-model="myForm.cccno" maxlength="10" minlength="10" name="cccno" required type="text" value="{{ $ulizaClinicalForm->cccno ?? '' }}">
						</div>
						<div class="col-md-5 input-group required">
							<div class="input-group-prepend">
								<span class="input-group-text" id="reporting_date">
									Case Reporting Date:
									<div style='color: #ff0000; display: inline;'>*</div>
								</span>
							</div>
							<input class="form-control date initial_fields" v-model="myForm.reporting_date" name="reporting_date" required value="{{ $ulizaClinicalForm->reporting_date ?? '' }}">
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
										<span class="input-group-text" id="dob">
											Date of Birth:
											<div style='color: #ff0000; display: inline;'>*</div>
										</span>
									</div>
									<input class="form-control date initial_fields" v-model="myForm.dob" name="dob" required value="{{ $ulizaClinicalForm->dob ?? '' }}">
								</div>
								<div class="col-md-6 input-group required">
									<div class="input-group-prepend">
										<span class="input-group-text" id="artstart_date">
											ART Start Date:
											<div style='color: #ff0000; display: inline;'>*</div>
										</span>
									</div>
									<input class="form-control " v-model="myForm.artstart_date" name="artstart_date" required value="{{ $ulizaClinicalForm->artstart_date ?? '' }}">
								</div>
							</div>
					  
							<div class="form-row">
								<div class="col-md-4 input-group required">
									<div class="input-group-prepend">
										<span class="input-group-text" for="gender">
											Gender:
											<div style='color: #ff0000; display: inline;'>*</div>
										</span>
									</div>
									<input class="form-control " name="gender" required value="{{ $ulizaClinicalForm->gender ?? '' }}">
								</div>
						
								<div class="col-md-4 input-group required">
									<div class="input-group-prepend">
										<span class="input-group-text" id="curr_weight">
											Current Weight (Kg):
											<div style='color: #ff0000; display: inline;'>*</div>
										</span>
									</div>
									<input aria-describedby="curr_weight" v-model="myForm.curr_weight" class="form-control" maxlength="3" name="curr_weight"  type="number" value="{{ $ulizaClinicalForm->curr_weight ?? '' }}">
								</div>
								<div class="col-md-4 input-group required">
									<div class="input-group-prepend">
										<span class="input-group-text" id="height">
											Height (cm):
											<div style='color: #ff0000; display: inline;'>*</div>
										</span>
									</div>
									<input aria-describedby="height" class="form-control" v-model="myForm.height" maxlength="3" name="height" required="required" type="number" value="{{ $ulizaClinicalForm->height ?? '' }}">
								</div>
							</div>
						</div>
					</div>
				  
					<div class="form-row mb-3">
						<div class="col-md-12 input-group required">
							<div class="input-group-prepend">
								<span class="input-group-text text-left" id="clinician_name">
									Clinician’s Name:
									<div style='color: #ff0000; display: inline;'>*</div>
								</span>
							</div>
							<input aria-describedby="clinician_name" class="form-control initial_fields" v-model="myForm.clinician_name" maxlength="75" name="clinician_name" required="required" type="text"  value="{{ $ulizaClinicalForm->clinician_name ?? '' }}">
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
								<span class="input-group-text" id="facility_email">
									Facility Email Address:
									<div style='color: #ff0000; display: inline;'>*</div>
								</span>
							</div>
							<input aria-describedby="facility_email" class="form-control initial_fields" v-model="myForm.facility_email" maxlength="75" name="facility_email" required="required" type="text" value="{{ $ulizaClinicalForm->facility_email ?? '' }}">
						</div>
					</div>
				  
					<div class="form-row mb-3">
						<div class="col-md-5 input-group required">
							<div class="input-group-prepend">
								<span class="input-group-text" id="facility_tel">
									Facility Tel No:
									<div style='color: #ff0000; display: inline;'>*</div>
								</span>
							</div>
							<input aria-describedby="facility_tel" class="form-control initial_fields" v-model="myForm.facility_tel" maxlength="45" name="facility_tel" required="required" type="text" value="{{ $ulizaClinicalForm->facility_tel ?? '' }}">
						</div>
					</div>
				  
				  
					<div class="form-row mb-3 required">
						<label class="col-md-12">
							What is the primary reason for this consultation:
							<div style='color: #ff0000; display: inline;'>*</div>
						</label>
					</div>

					<div class="form-row mb-3">
						{{ $ulizaClinicalForm->get_prop_name($reasons, 'primary_reason') }}
					</div>
				  
					<div class="form-group row">
						<label class="col-md-4 col-form-label">
							Clinical Evaluation: history, physical, diagnostics, working diagnosis:(excluding the information in the table below)
						</label>
						<div class="col-md-8">
							<textarea class="form-control" v-model="myForm.clinical_eval" name="clinical_eval" rows="3"></textarea>
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
										<!-- <button class="btn btn-warning float-right" @click.prevent="displayModal()" type="button"> -->
										<button class="btn btn-warning float-right" data-toggle="modal" data-target="#clinical_visit_modal" type="button">
											Add Clinical Visit
										</button>
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
												<tr v-for="(clinical_visit, clinical_visit_index) in myForm.clinical_visits">
													<td> @{{ clinical_visit_index+1 }} </td>
													<td> @{{ clinical_visit.clinicvisitdate }} </td>
													<td> @{{ clinical_visit.cd4 }} </td>
													<td> @{{ clinical_visit.hb }} </td>
													<td> @{{ clinical_visit.crclegfr }} </td>
													<td> @{{ clinical_visit.viral_load }} </td>
													<td> @{{ clinical_visit.weight_bmi }} </td>
													<td> @{{ clinical_visit.arv_regimen }} </td>
													<td> @{{ clinical_visit.reason_switch }} </td>
													<td> @{{ clinical_visit.new_oi }} </td>
													<td>
														<div class="btn-group" role="group">
															<button class="btn btn-sm btn-warning">Edit</button>
															<button class="btn btn-sm btn-danger" data-placement="top" data-toggle="tooltip" title="Delete selected record" @click.prevent="delVisit(clinical_visit_index)">Del</button>
															
														</div> 
													</td>
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
							<div style='color: #ff0000; display: inline;'>*</div>
						</label>
						<div class="col-md-8">
							<input class="form-control requirable" v-model="myForm.no_adherance_counseling" name="no_adherance_counseling" required="required" type="number">
						</div>
					</div>
				  
				  
					<div class="form-group row">
						<label class="col-md-4 col-form-label">
							Number of home visits conducted in last 3-6 months, and findings:
						</label>
						<div class="col-md-8">
						  <input class="form-control" v-model="myForm.no_homevisits" name="no_homevisits" type="number">
						</div>
					</div>
				  
					<div class="form-group row">
						<label class="col-md-4 col-form-label">
							Support structures (e.g. treatment buddy, support group attendance, caregivers) in place for this patient?
						</label>
						<div class="col-md-8">
							<textarea class="form-control" v-model="myForm.support_structures" name="support_structures" rows="4"></textarea>
						</div>
					</div>
				  
					<div class="form-group row">
						<label class="col-md-4 col-form-label">
							Evidence of adherence concerns (e.g. missed appointments, pill counts?):
						</label>
						<div class="col-md-8">
							<textarea class="form-control" v-model="myForm.adherence_concerns" name="adherence_concerns" rows="4"></textarea>
						</div>
					</div>
				  
					<div class="form-group row">
						<label class="col-md-4 col-form-label">Number of DOTS done in last 3-6 months:</label>
						<div class="col-md-8">
							<input class="form-control" v-model="myForm.no_dotsdone" name="no_dotsdone"  type="number">
						</div>
					</div>
				  
					<div class="form-group row">
						<label class="col-md-4 col-form-label">
							Likely root cause/s of poor adherence, for this patient (e.g. stigma, disclosure, side effects, alcohol or other drugs, mental health issues, caregiver changes, religious beliefs, inadequate preparation, etc):
						</label>
						<div class="col-md-8">
						  <textarea class="form-control" v-model="myForm.likely_rootcauses" name="likely_rootcauses"  rows="4"></textarea>
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
							<textarea class="form-control" v-model="myForm.inadequate_dosing" name="inadequate_dosing"  rows="4"></textarea>
						</div>
					</div>
				  
					<div class="form-group row">
						<label class="col-md-4 col-form-label">• Drug-drug interactions:</label>
						<div class="col-md-8">
							<textarea class="form-control" v-model="myForm.drug_interactions" name="drug_interactions"  rows="4"></textarea>
						</div>
					</div>
				  
					<div class="form-group row">
						<label class="col-md-4 col-form-label">• Drug-food interactions:</label>
						<div class="col-md-8">
							<textarea class="form-control" v-model="myForm.food_interactions" name="food_interactions"  rows="4"></textarea>
						</div>
					</div>
				  
					<div class="form-group row">
						<label class="col-md-4 col-form-label">• Impaired absorption (e.g. chronic severe diarrhea):</label>
						<div class="col-md-8">
							<textarea class="form-control" v-model="myForm.impaired_absorption" name="impaired_absorption"  rows="4"></textarea>
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
							<textarea class="form-control" v-model="myForm.treatment_interruptions" name="treatment_interruptions"  rows="4"></textarea>
						</div>
					</div>
				  
					<div class="form-group row">
						<label class="col-md-4 col-form-label">
							Has Drug Resistance/Sensitivity Testing been done for this patient? If yes, state date done and attach the detailed results.
						</label>
						<div class="col-md-8">
							<textarea class="form-control" v-model="myForm.drt_testing" name="drt_testing"  rows="4"></textarea>
						</div>
					</div>
				  
					<div class="form-group row">
						<label class="col-md-4 col-form-label">
							Has facility multidisciplinary team discussed the patient’s case?. If yes, comment on date, deliberations and recommendations.
							<br>
							(indicate how treatment failure was established and confirmed, proposed regimen and dosage, current source of drugs if patient already on 3rd line). If yes, state date done and attach the detailed results:
						</label>
						<div class="col-md-8">
							<textarea class="form-control" v-model="myForm.mdt_discussions" name="mdt_discussions"  rows="6"></textarea>
						</div>
					</div>
				  
					<div class="form-group row">
						<label class="col-md-4 col-form-label">
							MDT members who participated in the case discussion (names and titles)
						</label>
						<div class="col-md-8">
							<textarea class="form-control" v-model="myForm.mdt_members" maxlength="255" name="mdt_members"  rows="6"></textarea>
						</div>
					</div>

					<div v-if="successful_validation" class="row alert alert-success">
						You have successfully submitted your request.
					</div>

					<div v-if="successful_validation === false" class="row alert alert-warning">
						You have validation errors.
					</div>
				  
					<div class="mb-3 float-right">
						<button class="btn btn-warning" type="submit" >Submit</button>
					</div>
				  
					<div class="mb-3 float-centre">
						<button class="btn btn-default" type="button"  @click.prevent="saveDraft()">Save As Draft</button>
					</div>
				  
					<div class="mb-3 float-centre"></div>
				</form>
			</div>
		</div>

		@include('uliza.clinical_visit')
		
	</div>
</body>

<!-- <script src="{{ public_path('vendor/bootstrap/dist/js/bootstrap.min.js') }}"></script> -->
<!-- <script src="{{ public_path('vendor/jquery/dist/jquery.min.js') }}"></script> -->

<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>

</html>