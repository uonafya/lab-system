@extends('layouts.master')

@component('/forms/css')
    <link href="{{ asset('css/datapicker/datepicker3.css') }}" rel="stylesheet" type="text/css">
@endcomponent

@section('custom_css')
    <style type="text/css">
        .hpanel {
            margin-bottom: 4px;
        }
        .hpanel.panel-heading {
            padding-bottom: 2px;
            padding-top: 4px;
        }
    </style>
@endsection

@section('content')

    <div class="small-header">
        <div class="hpanel">
            <div class="panel-body">
                <h2 class="font-light m-b-xs">
                    ADD SAMPLE
                </h2>
            </div>
        </div>
    </div>


   <div class="content">
        <div>


        @if (isset($sample))
            {{ Form::open(['url' => '/sample/' . $sample->id, 'method' => 'put', 'class'=>'form-horizontal']) }}
        @else
            {{ Form::open(['url'=>'/sample', 'method' => 'post', 'class'=>'form-horizontal', 'id' => 'samples_form']) }}
        @endif

        <input type="hidden" value=0 name="new_patient" id="new_patient">

        <div class="row">
            <div class="col-lg-10 col-lg-offset-1">
                <div class="hpanel">
                    <div class="panel-body" style="padding-bottom: 6px;">

                        @if($facility_id == 0)    
                          <div class="form-group">
                              <label class="col-sm-4 control-label">Facility</label>
                              <div class="col-sm-8">
                                <select class="form-control" required name="facility_id" id="facility_id">

                                  <option value=""> Select One </option>
                                  @foreach ($facilities as $facility)
                                      <option value="{{ $facility->id }}"

                                      @if (isset($sample) && $sample->patient->facility_id == $facility->id)
                                          selected
                                      @endif

                                      > {{ $facility->name }}
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

                                  @if (isset($sample) && $sample->amrs_location == $amrs_location->id)
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
            <div class="col-lg-10 col-lg-offset-1">
                <div class="hpanel">
                    <div class="panel-heading" style="padding-bottom: 2px;padding-top: 4px;">
                        <center>Infant Information</center>
                    </div>
                    <div class="panel-body" style="padding-bottom: 6px;">

                        <div class="form-group">
                            <label class="col-sm-4 control-label">Patient / Sample ID</label>
                            <div class="col-sm-8">
                                <input class="form-control" required name="patient" type="text" value="{{ $sample->patient->patient or '' }}" id="patient">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-4 control-label">(*for Ampath Sites only) AMRS Provider Identifier</label>
                            <div class="col-sm-8">
                                <input class="form-control ampath-only" name="provider_identifier" type="text" value="{{ $sample->provider_identifier or '' }}">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-4 control-label">(*for Ampath Sites only) Patient Names</label>
                            <div class="col-sm-8">
                                <input class="form-control ampath-only" name="patient_name" type="text" value="{{ $sample->patient_name or '' }}">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-4 control-label">Sex</label>
                            <div class="col-sm-8"><select class="form-control lockable" required name="sex" id="sex">

                                <option value=""> Select One </option>
                                @foreach ($genders as $gender)
                                    <option value="{{ $gender->id }}"

                                    @if (isset($sample) && $sample->patient->sex == $gender->id)
                                        selected
                                    @endif

                                    > {{ $gender->gender_description }}
                                    </option>
                                @endforeach


                            </select></div>
                        </div>

                        <div class="hr-line-dashed"></div>

                        @isset($sample)

                            @php

                                $months = (int) $sample->age;
                                $weeks = $sample->age - (int) $sample->age;

                            @endphp

                        @endisset

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
                            <label class="col-sm-4 control-label">Date of Birth</label>
                            <div class="col-sm-8">
                                <div class="input-group date">
                                    <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                    <input type="text" id="dob" required class="form-control lockable" value="{{ $sample->patient->dob or '' }}" name="dob">
                                </div>
                            </div>                            
                        </div>

                        <div class="hr-line-dashed"></div>

                        <div class="form-group">
                            <label class="col-sm-4 control-label">Infant Prophylaxis</label>
                            <div class="col-sm-8"><select class="form-control" required name="regimen">

                                <option value=""> Select One </option>
                                @foreach ($iprophylaxis as $ip)
                                    <option value="{{ $ip->id }}"

                                    @if (isset($sample) && $sample->regimen == $ip->id)
                                        selected
                                    @endif

                                    > {{ $ip->name }}
                                    </option>
                                @endforeach

                            </select></div>
                        </div>


                    </div>
                </div>
            </div>
        </div>


        <div class="row">
            <div class="col-lg-10 col-lg-offset-1">
                <div class="hpanel">
                    <div class="panel-heading" style="padding-bottom: 2px;padding-top: 4px;">
                        <center>Mother Information</center>
                    </div>
                    <div class="panel-body" style="padding-bottom: 6px;">

                        <div class="form-group">
                            <label class="col-sm-4 control-label">CCC No</label>
                            <div class="col-sm-8"><input class="form-control" id="ccc_no" name="ccc_no" type="text" value="{{ $sample->patient->mother->ccc_no or '' }}"></div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-4 control-label">PMTCT Intervention</label>
                            <div class="col-sm-8"><select class="form-control" required name="mother_prophylaxis">

                                <option value=""> Select One </option>
                                @foreach ($interventions as $intervention)
                                    <option value="{{ $intervention->id }}"

                                    @if (isset($sample) && $sample->mother_prophylaxis == $intervention->id)
                                        selected
                                    @endif

                                    > {{ $intervention->name }}
                                    </option>
                                @endforeach

                            </select></div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-4 control-label">Feeding Types</label>
                            <div class="col-sm-8"><select class="form-control" required name="feeding">

                                <option value=""> Select One </option>
                                @foreach ($feedings as $feeding)
                                    <option value="{{ $feeding->id }}"

                                    @if (isset($sample) && $sample->feeding == $feeding->id)
                                        selected
                                    @endif

                                    > {{ $feeding->feeding_description }}
                                    </option>
                                @endforeach

                            </select></div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-4 control-label">Entry Point</label>
                            <div class="col-sm-8"><select class="form-control lockable" required name="entry_point" id="entry_point">

                                <option value=""> Select One </option>
                                @foreach ($entry_points as $entry_point)
                                    <option value="{{ $entry_point->id }}"

                                    @if (isset($sample) && $sample->patient->mother->entry_point == $entry_point->id)
                                        selected
                                    @endif

                                    > {{ $entry_point->name }}
                                    </option>
                                @endforeach

                            </select></div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-4 control-label">HIV Status</label>
                            <div class="col-sm-8">
                                    <select class="form-control lockable" required name="hiv_status" id="hiv_status">

                                    <option value=""> Select One </option>
                                    @foreach ($hiv_statuses as $hiv_status)
                                        <option value="{{ $hiv_status->id }}"

                                        @if (isset($sample) && $sample->patient->mother->hiv_status == $hiv_status->id)
                                            selected
                                        @endif

                                        > {{ $hiv_status->name }}
                                        </option>
                                    @endforeach

                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-4 control-label">Caregiver Phone No</label>
                            <div class="col-sm-8"><input class="form-control" name="caregiver_phone" type="text" value="{{ $sample->patient->caregiver_phone or '' }}"></div>
                        </div>

                    </div>
                </div>
            </div>
        </div>


        <div class="row">
            <div class="col-lg-10 col-lg-offset-1">
                <div class="hpanel">
                    <div class="panel-heading" style="padding-bottom: 2px;padding-top: 4px;">
                        <center>Sample Information</center>
                    </div>
                    <div class="panel-body" style="padding-bottom: 6px;">
                        
                        @if(auth()->user()->user_type_id != 5)
                            <div class="form-group">
                                <label class="col-sm-4 control-label">No of Spots</label>
                                <div class="col-sm-8">
                                    <input class="form-control" required name="spots" number="number" min=1 max=5 type="text" value="{{ $sample->spots or '' }}">
                                </div>
                            </div>
                        @endif

                        <div class="form-group">
                            <label class="col-sm-4 control-label">Date of Collection</label>
                            <div class="col-sm-8">
                                <div class="input-group date">
                                    <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                    <input type="text" id="datecollected" required class="form-control" value="{{ $sample->datecollected or '' }}" name="datecollected">
                                </div>
                            </div>                            
                        </div> 

                        @if($batch_dispatch == 0)

                            <div class="form-group">
                                <label class="col-sm-4 control-label">Date Dispatched from Facility</label>
                                <div class="col-sm-8">
                                    <div class="input-group date">
                                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                        <input type="text" id="datedispatched" class="form-control" value="{{ $sample->batch->datedispatched or '' }}" name="datedispatchedfromfacility">
                                    </div>
                                </div>                            
                            </div> 
                        @else
                            <input type="hidden" value="{{ $batch_dispatched }}" name="datedispatchedfromfacility" id="datedispatched">
                        @endif

                        <div></div>

                        @if(auth()->user()->user_type_id != 5)
                            @if($batch_no == 0)  
                                <div class="form-group">
                                    <label class="col-sm-4 control-label">Date Received</label>
                                    <div class="col-sm-8">
                                        <div class="input-group date">
                                            <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                            <input type="text" id="datereceived" required class="form-control" value="{{ $sample->batch->datereceived or '' }}" name="datereceived">
                                        </div>
                                    </div>                            
                                </div>
                            @else
                                <input type="hidden" value="{{ $batch_received }}" name="datereceived" id="datereceived">
                            @endif 
                        @endif

                        <div class="form-group">
                            <label class="col-sm-4 control-label">PCR Type</label>
                            <div class="col-sm-8">
                                <select class="form-control" required name="pcrtype" id="pcrtype" disabled>

                                    <option value=""> Select One </option>
                                    @foreach ($pcrtypes as $pcrtype)
                                        <option value="{{ $pcrtype->id }}"

                                        @if (isset($sample) && $sample->pcrtype == $pcrtype->id)
                                            selected
                                        @endif

                                        > {{ $pcrtype->alias }}
                                        </option>
                                    @endforeach

                                </select>
                            </div>
                        </div>

                        <input type="hidden" value="" name="pcrtype" id="hidden_pcr"> 

                        @if(auth()->user()->user_type_id != 5)

                            <div class="form-group">
                                <label class="col-sm-4 control-label">Received Status</label>
                                <div class="col-sm-8">
                                        <select class="form-control" required name="receivedstatus" id="receivedstatus">

                                        <option value=""> Select One </option>
                                        @foreach ($receivedstatuses as $receivedstatus)
                                            <option value="{{ $receivedstatus->id }}"

                                            @if (isset($sample) && $sample->receivedstatus == $receivedstatus->id)
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

                                            @if (isset($sample) && $sample->rejectedreason == $rejectedreason->id)
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
            <div class="col-lg-10 col-lg-offset-1">
                <div class="hpanel">
                    <div class="panel-body" style="padding-bottom: 6px;">
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
                    <center>

                        @if (isset($sample))
                            <div class="col-sm-4 col-sm-offset-4">
                                <button class="btn btn-primary" type="submit" name="submit_type" value="add">Update Sample</button>
                            </div>
                        @else
                            <div class="col-sm-10 col-sm-offset-1">
                                <button class="btn btn-success" type="submit" name="submit_type" value="release">Save & Release sample</button>
                                <button class="btn btn-primary" type="submit" name="submit_type" value="add">Save & Add sample</button>
                                <button class="btn btn-danger" type="submit" formnovalidate name="submit_type" value="cancel">Cancel & Release</button>
                            </div>
                        @endif
                    </center>
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

        function check_new_patient(patient_id, facility){
            $.ajax({
               type: "GET",
               url: "{{ url('/sample/new_patient') }}/"+patient_id+"/"+facility ,

               success: function(data){

                    console.log(data);

                    $("#new_patient").val(data[0]);

                    if(data[0] == 0){
                        localStorage.setItem("new_patient", 0);
                        var patient = data[1];
                        var mother = data[2];
                        var prev = data[3];

                        console.log(patient.dob);

                        $("#dob").val(patient.dob);
                        // $('#sex option[value='+ patient.sex + ']').attr('selected','selected').change();

                        $("#sex").val(patient.sex).change();
                        $("#hiv_status").val(mother.hiv_status).change();
                        $("#entry_point").val(mother.entry_point).change();
                        $("#ccc_no").val(mother.ccc_no).change();

                        $('#pcrtype option[value=2]').attr('selected','selected').change();
                        $("#hidden_pcr").val(2);

                        if(prev.previous_positive == 1){
                            $('#pcrtype option[value=3]').attr('selected','selected').change();
                            $("#hidden_pcr").val(3);
                        }
                        $('<input>').attr({
                            type: 'hidden',
                            name: 'patient_id',
                            value: patient.id,
                            id: 'hidden_patient',
                            class: 'patient_details'
                        }).appendTo("#samples_form");

                        $('<input>').attr({
                            type: 'hidden',
                            name: 'dob',
                            value: patient.dob,
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



            /*$('<input>').attr({
                type: 'hidden',
                id: 'foo',
                name: 'bar'
            }).appendTo('form');*/

        }
    </script>



@endsection
