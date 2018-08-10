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

    <div class="content">
        <div>

        @if (isset($sample))
            {{ Form::open(['url' => '/dr_sample/' . $sample->id, 'method' => 'put', 'class'=>'form-horizontal']) }}
        @else
            {{ Form::open(['url'=>'/dr_sample', 'method' => 'post', 'class'=>'form-horizontal', 'id' => 'samples_form']) }}

        @endif

        <input type="hidden" value=0 name="new_patient" id="new_patient">

        <div class="row">
            <div class="col-lg-12">
                <div class="hpanel">
                    <div class="panel-body" style="padding-bottom: 6px;">

                        <div class="alert alert-warning">
                            <center>
                                Please fill the form correctly. <br />
                                Fields with an asterisk(*) are mandatory.
                            </center>
                        </div>
                        <br />

                        <input type="hidden" value=0 name="patient_id" id="patient_id">

   
                        <div class="form-group">
                            <label class="col-sm-4 control-label">Facility 
                                <strong><div style='color: #ff0000; display: inline;'>*</div></strong>
                            </label>
                            <div class="col-sm-8">
                                <select class="form-control requirable" required name="facility_id" id="facility_id">
                                    @isset($sample)
                                    <option value="{{ $sample->patient->facility->id }}" selected>{{ $sample->patient->facility->facilitycode }} {{ $sample->patient->facility->name }}</option>
                                    @endisset
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-4 control-label">Patient / Sample ID
                                <strong><div style='color: #ff0000; display: inline;'>*</div></strong>
                            </label>
                            <div class="col-sm-8">
                                <input class="form-control requirable" required name="patient" type="text" value="{{ $sample->patient->patient ?? '' }}" id="patient">
                            </div>
                        </div>

                        <div class="hr-line-dashed"></div>

                        <div class="form-group">
                            <label class="col-sm-4 control-label">Reason for DR test
                                <strong><div style='color: #ff0000; display: inline;'>*</div></strong>
                            </label>
                            <div class="col-sm-8">
                                <select class="form-control requirable" required name="dr_reasons" id="dr_reasons">
                                    <option value=""> Select One </option>
                                    @foreach ($drug_resistance_reasons as $reason)
                                        <option value="{{ $reason->id }}"

                                        @if (isset($sample) && $sample->dr_reason_id == $reason->id)
                                            selected
                                        @endif

                                        > {{ $reason->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-4 control-label">Previous Regimen</label>
                            <div class="col-sm-8">
                                <select class="form-control" name="prev_prophylaxis" id="prev_prophylaxis">
                                    <option value=""> Select One </option>
                                    @foreach ($prophylaxis as $proph)
                                        <option value="{{ $proph->id }}"

                                        @if (isset($sample) && $sample->prev_prophylaxis == $proph->id)
                                            selected
                                        @endif

                                        > {{ $proph->displaylabel }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-4 control-label">Current Regimen
                                <strong><div style='color: #ff0000; display: inline;'>*</div></strong>
                            </label>
                            <div class="col-sm-8">
                                <select class="form-control requirable" required name="prophylaxis" id="prophylaxis">
                                    <option value=""> Select One </option>
                                    @foreach ($prophylaxis as $proph)
                                        <option value="{{ $proph->id }}"

                                        @if (isset($sample) && $sample->prophylaxis == $proph->id)
                                            selected
                                        @endif

                                        > {{ $proph->displaylabel }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="hr-line-dashed"></div>                        

                        <div class="form-group">
                            <label class="col-sm-4 control-label">Date Started on Previous Regimen</label>
                            <div class="col-sm-8">
                                <div class="input-group date">
                                    <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                    <input type="text" id="date_prev_regimen" class="form-control" value="{{ $sample->date_prev_regimen ?? '' }}" name="date_prev_regimen">
                                </div>
                            </div>                            
                        </div>                      

                        <div class="form-group">
                            <label class="col-sm-4 control-label">Date Started on Current Regimen</label>
                            <div class="col-sm-8">
                                <div class="input-group date">
                                    <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                    <input type="text" id="date_current_regimen" class="form-control" value="{{ $sample->date_current_regimen ?? '' }}" name="date_current_regimen">
                                </div>
                            </div>                            
                        </div> 
                        

                    </div>
                </div>
            </div>
        </div>


        <div class="row">
            <div class="col-lg-12">
                <div class="hpanel">
                    <div class="panel-heading" style="padding-bottom: 2px;padding-top: 4px;">
                        <center>Sample Information</center>
                    </div>
                    <div class="panel-body" style="padding-bottom: 6px;">

                        <div class="form-group">
                            <label class="col-sm-4 control-label">Date of Collection
                                <strong><div style='color: #ff0000; display: inline;'>*</div></strong>
                            </label>
                            <div class="col-sm-8">
                                <div class="input-group date">
                                    <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                    <input type="text" id="datecollected" required class="form-control requirable" value="{{ $sample->datecollected ?? '' }}" name="datecollected">
                                </div>
                            </div>                            
                        </div> 


                        <div class="form-group">
                            <label class="col-sm-4 control-label">Date Received
                                <strong><div style='color: #ff0000; display: inline;'>*</div></strong>
                            </label>
                            <div class="col-sm-8">
                                <div class="input-group date">
                                    <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                    <input type="text" id="datereceived" required class="form-control requirable" value="{{ $sample->batch->datereceived ?? $batch->datereceived ?? '' }}" name="datereceived">
                                </div>
                            </div>                            
                        </div>

                        <div class="form-group">
                            <label class="col-sm-4 control-label">Received Status
                                <strong><div style='color: #ff0000; display: inline;'>*</div></strong>
                            </label>
                            <div class="col-sm-8">
                                    <select class="form-control requirable" required name="receivedstatus" id="receivedstatus">

                                    <option value=""> Select One </option>
                                    @foreach ($received_statuses as $receivedstatus)
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
                                    @foreach ($rejected_reasons as $rejectedreason)
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

                    </div>
                </div>
            </div>
        </div>

                
        <div class="row">
            <div class="col-sm-12">
                <div class="form-group">
                    <center>
                        <div class="col-sm-10 col-sm-offset-1">
                            <button class="btn btn-success" type="submit" name="submit_type" value="release" id='submit_form_button'>Save</button>

                            <button class="btn btn-danger" type="submit" formnovalidate name="submit_type" value="cancel">Cancel</button>
                        </div>
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
                datecollected: {
                    lessThanTwo: ["#datereceived", "Date Collected", "Date Received"]
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

        set_select_facility("facility_id", "{{ url('/facility/search') }}", 3, "Search for facility", false);
        set_select_facility("lab_id", "{{ url('/facility/search') }}", 3, "Search for facility", false);

    @endcomponent


    <script type="text/javascript">
        $(document).ready(function(){
            $("#rejection").hide();
            $("#patient").blur(function(){
                var patient = $(this).val();
                var facility = $("#facility_id").val();
                check_new_patient(patient, facility);
            });

            $("#facility_id").change(function(){
                var val = $(this).val();

                if(val == 7148 || val == '7148'){
                    $('.requirable').removeAttr("required");
                }
                else{
                    $('.requirable').attr("required", "required");
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
                    var patient = data[1];

                    if(data[0] == 0){
                        $("#submit_form_button").removeAttr("disabled");
                        $("#patient_id").val(patient.id);
                    }
                    else{
                        $("#submit_form_button").attr("disabled", "disabled");
                        set_warning("This patient was not found.")
                    }

                }
            });
        }

    </script>



@endsection
