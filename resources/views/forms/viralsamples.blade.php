@extends('layouts.master')

@component('/forms/css')
    <link href="{{ asset('css/datapicker/datepicker3.css') }}" rel="stylesheet" type="text/css">
@endcomponent

@section('content')

    <div class="small-header">
        <div class="hpanel">
            <div class="panel-body">
                <h2 class="font-light m-b-xs">
                    ADD VIRAL LOAD SAMPLE
                </h2>
            </div>
        </div>
    </div>


   <div class="content">
        <div>


        @if (isset($viralsample))
            {{ Form::open(['url' => '/viralsample/' . $viralsample->id, 'method' => 'put', 'class'=>'form-horizontal']) }}
        @else
            {{ Form::open(['url'=>'/viralsample', 'method' => 'post', 'class'=>'form-horizontal', 'id' => 'samples_form']) }}
        @endif

        <input type="hidden" value=0 name="new_patient" id="new_patient">

        <div class="row">
            <div class="col-lg-7 col-lg-offset-2">
                <div class="hpanel">
                    <div class="panel-body">

                        @if($facility_id == 0)    
                          <div class="form-group">
                              <label class="col-sm-4 control-label">Facility</label>
                              <div class="col-sm-8">
                                <select class="form-control" required name="facility_id" id="facility_id">

                                  <option value=""> Select One </option>
                                  @foreach ($facilities as $facility)
                                      <option value="{{ $facility->id }}"

                                      @if (isset($viralsample) && $viralsample->patient->facility_id == $facility->id)
                                          selected
                                      @endif

                                      > {{ $facility->facilitycode . ' - ' . $facility->name }}
                                      </option>
                                  @endforeach

                                </select>
                              </div>
                          </div>
                        @else
                            <p>Facility - {{ $facility_name }}  Batch {{ $batch_no }} </p>
                            <input type="hidden" name="facility_id" id="facility_id" value="{{$facility_id}}">
                        @endif

                      <div class="form-group">
                          <label class="col-sm-4 control-label">(*for Ampath Sites only) AMRS Location</label>
                          <div class="col-sm-8"><select class="form-control ampath-only" name="amrs_location">

                              <option value=""> Select One </option>
                              @foreach ($amrs_locations as $amrs_location)
                                  <option value="{{ $amrs_location->id }}"

                                  @if (isset($viralsample) && $viralsample->amrs_location == $amrs_location->id)
                                      selected
                                  @endif

                                  > {{ $amrs_location->name }}
                                  </option>
                              @endforeach

                          </select></div>
                      </div>

                    </div>
                </div>
            </div>
        </div>


        <div class="row">
            <div class="col-lg-7 col-lg-offset-2">
                <div class="hpanel">
                    <div class="panel-heading">
                        <center>Patient Information</center>
                    </div>
                    <div class="panel-body">

                        <div class="form-group">
                            <label class="col-sm-4 control-label">Patient / Sample ID</label>
                            <div class="col-sm-8">
                                <input class="form-control" required name="patient" type="text" value="{{ $viralsample->patient->patient or '' }}" id="patient">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-4 control-label">(*for Ampath Sites only) AMRS Provider Identifier</label>
                            <div class="col-sm-8">
                                <input class="form-control ampath-only" name="provider_identifier" type="text" value="{{ $viralsample->provider_identifier or '' }}">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-4 control-label">(*for Ampath Sites only) Patient Names</label>
                            <div class="col-sm-8">
                                <input class="form-control ampath-only" name="patient_name" type="text" value="{{ $viralsample->patient_name or '' }}">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-4 control-label">Sex</label>
                            <div class="col-sm-8">
                                <select class="form-control lockable" required name="sex" id="sex">

                                    <option value=""> Select One </option>
                                    @foreach ($genders as $gender)
                                        <option value="{{ $gender->id }}"

                                        @if (isset($viralsample) && $viralsample->patient->sex == $gender->id)
                                            selected
                                        @endif

                                        > {{ $gender->gender_description }}
                                        </option>
                                    @endforeach


                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-4 control-label">PMTCT(If Female)</label>
                            <div class="col-sm-8">
                                <select class="form-control" name="pmtct" id="pmtct">

                                    <option value=""> Select One </option>
                                    @foreach ($pmtct_types as $pmtct)
                                        <option value="{{ $pmtct->id }}"

                                        @if (isset($viralsample) && $viralsample->pmtct == $pmtct->id)
                                            selected
                                        @endif

                                        > {{ $pmtct->name }}
                                        </option>
                                    @endforeach


                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-4 control-label">Date of Birth</label>
                            <div class="col-sm-8">
                                <div class="input-group date">
                                    <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                    <input type="text" id="dob" required class="form-control lockable" value="{{ $viralsample->patient->dob or '' }}" name="dob">
                                </div>
                            </div>                            
                        </div>

                        <div class="hr-line-dashed"></div>

                        <!-- <div class="form-group">
                            <label class="col-sm-4 control-label">Age</label>
                            <div class="col-sm-8">
                                <input class="form-control" type="text" required name="sample_months" placeholder="Months" value="{{ $months or '' }}">
                            </div>
                            <div class="col-sm-8 col-sm-offset-4 input-sm" style="margin-top: 1em;">
                                <input class="form-control" type="text" required name="sample_weeks" placeholder="Weeks" value="{{ $weeks or '' }}">
                            </div>
                        </div> -->

                        <div class="form-group">
                            <label class="col-sm-4 control-label">ART Inititation Date</label>
                            <div class="col-sm-8">
                                <div class="input-group date">
                                    <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                    <input type="text" id="initiation_date" required class="form-control lockable" value="{{ $viralsample->patient->initiation_date or '' }}" name="initiation_date">
                                </div>
                            </div>                            
                        </div>

                        <div class="form-group">
                            <label class="col-sm-4 control-label">Caregiver Phone No</label>
                            <div class="col-sm-8"><input class="form-control" name="caregiver_phone" type="text" value="{{ $viralsample->patient->caregiver_phone or '' }}"></div>
                        </div>

                    </div>
                </div>
            </div>
        </div>



        <div class="row">
            <div class="col-lg-7 col-lg-offset-2">
                <div class="hpanel">
                    <div class="panel-heading">
                        <center>Sample Information</center>
                    </div>
                    <div class="panel-body">

                        <div class="form-group">
                            <label class="col-sm-4 control-label">Type of Sample</label>
                            <div class="col-sm-8">
                                <select class="form-control" required name="sampletype" id="sampletype">
                                    <option value=""> Select One </option>
                                    @foreach ($sampletypes as $sampletype)
                                        <option value="{{ $sampletype->id }}"

                                        @if (isset($viralsample) && $viralsample->sampletype == $sampletype->id)
                                            selected
                                        @endif

                                        > {{ $sampletype->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="hr-line-dashed"></div>

                        <div class="form-group">
                            <label class="col-sm-4 control-label">Current Regimen</label>
                            <div class="col-sm-8">
                                <select class="form-control" required name="prophylaxis" id="prophylaxis">
                                    <option value=""> Select One </option>
                                    @foreach ($prophylaxis as $proph)
                                        <option value="{{ $proph->id }}"

                                        @if (isset($viralsample) && $viralsample->prophylaxis == $proph->id)
                                            selected
                                        @endif

                                        > {{ $proph->displaylabel }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-4 control-label">1st or 2nd Line Regimen</label>
                            <div class="col-sm-8">
                                <select class="form-control" required name="regimenline" id="regimenline">
                                    <option value=""> Select One </option>
                                    @foreach ($regimenlines as $regimenline)
                                        <option value="{{ $regimenline->id }}"

                                        @if (isset($viralsample) && $viralsample->regimenline == $regimenline->id)
                                            selected
                                        @endif

                                        > {{ $regimenline->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-4 control-label">Justification</label>
                            <div class="col-sm-8">
                                <select class="form-control" required name="justification" id="justification">
                                    <option value=""> Select One </option>
                                    @foreach ($justifications as $justification)
                                        <option value="{{ $justification->id }}"

                                        @if (isset($viralsample) && $viralsample->justification == $justification->id)
                                            selected
                                        @endif

                                        > {{ $justification->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>


                        <div class="hr-line-dashed"></div>                        

                        <div class="form-group">
                            <label class="col-sm-4 control-label">Date of Collection</label>
                            <div class="col-sm-8">
                                <div class="input-group date">
                                    <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                    <input type="text" id="datecollected" required class="form-control" value="{{ $viralsample->datecollected or '' }}" name="datecollected">
                                </div>
                            </div>                            
                        </div> 

                        @if($batch_dispatch == 0)

                            <div class="form-group">
                                <label class="col-sm-4 control-label">Date Dispatched from Facility</label>
                                <div class="col-sm-8">
                                    <div class="input-group date">
                                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                        <input type="text" id="datedispatched" class="form-control" value="{{ $viralsample->batch->datedispatchedfromfacility or '' }}" name="datedispatchedfromfacility">
                                    </div>
                                </div>                            
                            </div> 
                        @else
                            <input type="hidden" value="{{ $batch_dispatched }}" id="datedispatched">
                        @endif

                        <div></div>

                        @if(auth()->user()->user_type_id != 5)
                            @if($batch_no == 0)  
                                <div class="form-group">
                                    <label class="col-sm-4 control-label">Date Received</label>
                                    <div class="col-sm-8">
                                        <div class="input-group date">
                                            <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                            <input type="text" id="datereceived" required class="form-control" value="{{ $viralsample->batch->datereceived or '' }}" name="datereceived">
                                        </div>
                                    </div>                            
                                </div> 
                            @else
                                <input type="hidden" value="{{ $batch_received }}" id="datereceived">
                            @endif

                            <div class="form-group">
                                <label class="col-sm-4 control-label">Received Status</label>
                                <div class="col-sm-8">
                                        <select class="form-control" required name="receivedstatus" id="receivedstatus">

                                        <option value=""> Select One </option>
                                        @foreach ($receivedstatuses as $receivedstatus)
                                            <option value="{{ $receivedstatus->id }}"

                                            @if (isset($viralsample) && $viralsample->receivedstatus == $receivedstatus->id)
                                                selected
                                            @endif

                                            > {{ $receivedstatus->name }}
                                            </option>
                                        @endforeach

                                    </select>
                                </div>
                            </div>

                            <div class="form-group" id="rejection" >
                                <label class="col-sm-4 control-label">Rejected Reason</label>
                                <div class="col-sm-8">
                                        <select class="form-control" required name="rejectedreason" id="rejectedreason" disabled>

                                        <option value=""> Select One </option>
                                        @foreach ($rejectedreasons as $rejectedreason)
                                            <option value="{{ $rejectedreason->id }}"

                                            @if (isset($viralsample) && $viralsample->rejectedreason == $rejectedreason->id)
                                                selected
                                            @endif

                                            > {{ $rejectedreason->name }}
                                            </option>
                                        @endforeach

                                    </select>
                                </div>
                            </div>
                        @endif


                    </div>
                </div>
            </div>
        </div>


        <!-- <div class="row">
            <div class="col-lg-7 col-lg-offset-2">
                <div class="hpanel">
                    <div class="panel-heading">
                        <center>Infant Information</center>
                    </div>
                    <div class="panel-body">


                    </div>
                </div>
            </div>
        </div> -->


                
        <div class="row">
            <div class="col-lg-7 col-lg-offset-2">
                <div class="hpanel">
                    <div class="panel-body">
                        <div class="form-group"><label class="col-sm-4 control-label">Comments (from facility)</label>
                            <div class="col-sm-8"><textarea  class="form-control" name="comments"></textarea></div>
                        </div>
                        @if(auth()->user()->user_type_id != 5)
                            <div class="form-group"><label class="col-sm-4 control-label">Lab Comments</label>
                                <div class="col-sm-8"><textarea  class="form-control" name="labcomment"></textarea></div>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="hr-line-dashed"></div>
                <div class="form-group">
                    @if (isset($viralsample))
                        <div class="col-sm-4 col-sm-offset-4">
                            <button class="btn btn-primary" type="submit" name="submit_type" value="add">Update Sample</button>
                        </div>
                    @else
                        <div class="col-sm-8 col-sm-offset-2">
                            <button class="btn btn-success" type="submit" name="submit_type" value="release">Save & Release sample</button>
                            <button class="btn btn-primary" type="submit" name="submit_type" value="add">Save & Add sample</button>
                            <button class="btn btn-danger" type="submit" formnovalidate name="submit_type" value="cancel">Cancel & Release</button>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{ Form::close() }}

      </div>
    </div>

@endsection

@section('scripts')

    @component('/forms/scripts')
        @slot('js_scripts')
            <script src="{{ asset('js/datapicker/bootstrap-datepicker.js') }}"></script>
        @endslot


        @slot('val_rules')
           ,
            rules: {
                dob: {
                    lessThan: ["#datecollected", "Date of Birth", "Date Collected"]
                },
                datecollected: {
                    lessThan: ["#datedispatched", "Date Collected", "Date of Dispatch"]
                },
                datecollected: {
                    lessThan: ["#datereceived", "Date Collected", "Date Received"]
                },
                datedispatched: {
                    lessThan: ["#datereceived", "Date of Dispatch", "Date Received"]
                }                
            }
        @endslot



        $(".date").datepicker({
            startView: 0,
            todayBtn: "linked",
            keyboardNavigation: false,
            forceParse: true,
            autoclose: true,
            endDate: new Date(),
            format: "yyyy-mm-dd"
        });

    @endcomponent


    <script type="text/javascript">
        $(document).ready(function(){

            $("#rejection").hide();

            $("#patient").blur(function(){
                var patient = $(this).val();
                var facility = $("#facility_id").val();
                check_new_patient(patient, facility);
            });

            $("#sex").change(function(){
                var val = $(this).val();
                if(val == 2){
                    $("#pmtct").removeAttr("disabled");
                    $("#pmtct").attr("required", "required");
                }
                else{
                    $("#pmtct").attr("disabled", "disabled");
                    $("#pmtct").removeAttr("required");
                }
            });

            $("#receivedstatus").change(function(){
                var val = $(this).val();
                if(val == 2){
                    $("#rejection").show();
                    $("#rejectedreason").removeAttr("disabled");
                }
                else{
                    $("#rejection").hide();
                    $("#rejectedreason").attr("disabled", "disabled");
                }
            });

            
        });

        function check_new_patient(patient, facility_id){
            $.ajax({
               type: "POST",
               data: {
                _token : "{{ csrf_token() }}",
                patient : patient,
                facility_id : facility_id
               },
               url: "{{ url('/viralsample/new_patient') }}",

               success: function(data){

                    console.log(data);

                    $("#new_patient").val(data[0]);

                    if(data[0] == 0){
                        localStorage.setItem("new_patient", 0);
                        var patient = data[1];
                        var prev = data[2];

                        console.log(patient.dob);

                        $("#dob").val(patient.dob);
                        // $('#sex option[value='+ patient.sex + ']').attr('selected','selected').change();

                        $("#sex").val(patient.sex).change();

                        $('<input>').attr({
                            type: 'hidden',
                            name: 'patient_id',
                            value: patient.id,
                            id: 'hidden_patient',
                            class: 'patient_details'
                        }).appendTo("#samples_form");

                        $(".lockable").attr("disabled", "disabled");
                    }
                    else{
                        localStorage.setItem("new_patient", 1);
                        $(".lockable").removeAttr("disabled");
                        $(".lockable").val('').change();
                        $('#pcrtype option[value=1]').attr('selected','selected').change();
                        $("#hidden_pcr").val(1);

                        $('.patient_details').remove();
                    }

                }
            });
        }
    </script>



@endsection
