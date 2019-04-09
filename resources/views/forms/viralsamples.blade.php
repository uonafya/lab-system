@extends('layouts.master')

@component('/forms/css')
    <link href="{{ asset('css/datapicker/datepicker3.css') }}" rel="stylesheet" type="text/css">
@endcomponent

@section('content')

   <div class="content">
        <div>


        @if (isset($viralsample))
            {{ Form::open(['url' => '/viralsample/' . $viralsample->id, 'method' => 'put', 'class'=>'form-horizontal']) }}
        @else
            {{ Form::open(['url'=>'/viralsample', 'method' => 'post', 'class'=>'form-horizontal', 'id' => 'samples_form']) }}
        @endif

        <input type="hidden" value=0 name="new_patient" id="new_patient">

        @if ($errors->any())
            <div class="row">
                <div class="col-lg-12">
                    <div class="hpanel">
                        <div class="panel-body" style="padding-bottom: 6px;">
                            <div class="alert alert-danger">
                                <center>
                                    The sample was not saved due to the following errors: <br />
                                    @foreach ($errors->all() as $error)
                                        {{ $error }} <br />
                                    @endforeach
                                </center>
                            </div>
                        </div>
                    </div>
                </div>                
            </div>
        @endif

        <div class="row">
            <div id="similar_samples"></div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <div class="hpanel">
                    <div class="panel-body">

                        <div class="alert alert-warning">
                            <center>
                                Please fill the form correctly. <br />
                                Fields with an asterisk(*) are mandatory.
                            </center>
                        </div>
                        <br />

                        @if(env('APP_LAB') == 2)

                            <div class="alert alert-warning">
                                <center>
                                    Please fill the ccc number by starting with the facility mfl code 
                                </center>
                            </div>
                            <br />

                        @endif

                        @isset($viralsample)
                            <div class="alert alert-warning">
                                <center>
                                    NB: If you edit the facility name, date received or date dispatched from the facility this will be reflected on the other samples in this batch.
                                </center>
                            </div>
                            <br />
                        @endisset

                        @if(isset($form_sample_type) && $form_sample_type)
                            <input type="hidden" name="form_sample_type" value="{{$form_sample_type}}">
                            <input type="hidden" name="sampletype" value="{{$form_sample_type}}">
                        @endif


                        @if(!$batch || isset($viralsample))    
                          <div class="form-group">
                              <label class="col-sm-4 control-label">Facility
                                <strong><div style='color: #ff0000; display: inline;'>*</div></strong>
                                </label>
                              <div class="col-sm-8">
                                <select class="form-control requirable" required name="facility_id" id="facility_id">
                                    @isset($viralsample)
                                        <option value="{{ $viralsample->batch->facility->id }}" selected>{{ $viralsample->batch->facility->facilitycode }} {{ $viralsample->batch->facility->name }}</option>
                                    @endisset
                                </select>
                              </div>
                          </div>
                        @else

                            <div class="alert alert-success">
                                <center> <b>Facility</b> - {{ $facility_name }}<br />  <b>Batch</b> - {{ $batch->id }} </center>
                            </div>
                            <br />

                            @if(session('viral_last_patient'))

                                <div class="alert alert-success">
                                    <center> <b>Last Patient Entered</b> - {{ session('viral_last_patient') }} </center>
                                </div>
                                <br />

                            @endif
                            
                            <input type="hidden" name="facility_id" value="{{$batch->facility_id}}">
                        @endif
                        
                        @if(auth()->user()->user_type_id != 5 && in_array(env('APP_LAB'), [4, 2]))
                            <div class="form-group">
                                <label class="col-sm-4 control-label">High Priority</label>
                                <div class="col-sm-8">
                                <input type="checkbox" class="i-checks" name="highpriority" value="1"
                                    @if(isset($viralsample) && $viralsample->batch->highpriority)
                                        checked
                                    @endif

                                 />
                                </div>
                            </div>
                        @endif

                      <div class="form-group ampath-div">
                          <label class="col-sm-4 control-label">(*for Ampath Sites only) AMRS Location</label>
                          <div class="col-sm-8"><select class="form-control ampath-only" name="amrs_location">

                              <option></option>
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

                      @if(env('APP_LAB') == 8)

                            <div class="form-group">
                                <label class="col-sm-4 control-label">Specimen Label ID </label>
                                <div class="col-sm-8">
                                    <input class="form-control" name="label_id" type="text" value="{{ $viralsample->label_id ?? '' }}" id="label_id">
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-4 control-label">Area Name </label>
                                <div class="col-sm-8">
                                    <input class="form-control" name="areaname" type="text" value="{{ $viralsample->areaname ?? '' }}" id="areaname">
                                </div>
                            </div>

                        @endif

                    </div>
                </div>
            </div>
        </div>


        <div class="row">
            <div class="col-lg-12">
                <div class="hpanel">
                    <div class="panel-heading">
                        <center>Patient Information</center>
                    </div>
                    <div class="panel-body">

                        @if( in_array(env('APP_LAB'), $sms))

                            <div class="form-group">
                                <label class="col-sm-4 control-label">Phone No (format 254725******)</strong>
                                </label>
                                <div class="col-sm-3">
                                    <input class="form-control" name="patient_phone_no" id="patient_phone_no" type="text" value="{{ $viralsample->patient->patient_phone_no ?? '' }}">
                                </div>

                                <div class="col-sm-1">Patient's Preferred Language</div>

                                <div class="col-sm-4">
                                    @foreach($languages as $key => $value)
                                        <label><input type="radio" class="i-checks" name="preferred_language" value={{ $key }} 

                                            @if(isset($viralsample) && $viralsample->patient->preferred_language == $key)
                                                checked="checked"
                                            @endif
                                            > 
                                            {{ $value }} 
                                        </label>

                                    @endforeach
                                </div>
                            </div>

                        @endif

                        <div class="form-group">
                            <label class="col-sm-4 control-label">Patient / Sample ID
                                <strong><div style='color: #ff0000; display: inline;'>*</div></strong>
                            </label>
                            <div class="col-sm-8">
                                <input class="form-control requirable" required name="patient" type="text" value="{{ $viralsample->patient->patient ?? '' }}" id="patient">
                            </div>
                        </div>

                        @if(!isset($viralsample))

                            <div class="form-group">
                                <label class="col-sm-4 control-label">Confirm Re-Entry (Sample Exists but should not be flagged as a double-entry)</label>
                                <div class="col-sm-8">
                                <input type="checkbox" class="i-checks" name="reentry" value="1" />
                                </div>
                            </div>

                        @endif

                        <div class="form-group ampath-div">
                            <label class="col-sm-4 control-label">(*for Ampath Sites only) AMRS Provider Identifier</label>
                            <div class="col-sm-8">
                                <input class="form-control ampath-only" name="provider_identifier" type="text" value="{{ $viralsample->provider_identifier ?? '' }}">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-4 control-label">Patient Names</label>
                            <div class="col-sm-8">
                                <input class="form-control" name="patient_name" type="text" value="{{ $viralsample->patient->patient_name ?? '' }}">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-4 control-label">Date Of Birth
                                <strong><div style='color: #ff0000; display: inline;'>*</div></strong>
                            </label>
                            <div class="col-sm-8">
                                <div class="input-group date date-dob">
                                    <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                    <input type="text" id="dob" class="form-control lockable" value="{{ $viralsample->patient->dob ?? '' }}" name="dob">
                                </div>
                            </div>                            
                        </div>


                        <div class="form-group">
                            <label class="col-sm-4 control-label">Age (In Years)</label>
                            <div class="col-sm-8">
                                <input class="form-control" type="text" name="age" id='age' number='number' placeholder="Fill this or set the DOB." value="{{ $viralsample->age ?? '' }}">
                            </div>
                        </div>



                        <div class="form-group">
                            <label class="col-sm-4 control-label">Sex
                                <strong><div style='color: #ff0000; display: inline;'>*</div></strong>
                            </label>
                            <div class="col-sm-8">
                                <select class="form-control lockable requirable" required name="sex" id="sex">

                                    <option></option>
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
                                <select class="form-control requirable" name="pmtct" id="pmtct">

                                    <option></option>
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

                        <div class="hr-line-dashed"></div>

                        @if(isset($form_sample_type) && $form_sample_type)

                            <div class="form-group">
                                <label class="col-sm-4 control-label">Sample Type
                                    <strong><div style='color: #ff0000; display: inline;'>*</div></strong>
                                </label>
                                <div class="col-sm-8">
                                    <input class="form-control" disabled type="text" value="{{ $sampletypes->where('id', $form_sample_type)->first()->name ?? '' }}">
                                </div>
                            </div>

                        @else

                            <div class="form-group">
                                <label class="col-sm-4 control-label">Sample Type
                                    <strong><div style='color: #ff0000; display: inline;'>*</div></strong>
                                </label>
                                <div class="col-sm-8">
                                    <select class="form-control requirable" required name="sampletype" id="sampletype">
                                        <option></option>
                                        @foreach ($sampletypes as $sampletype)
                                            <option value="{{ $sampletype->id }}"

                                            @if (isset($viralsample) && $viralsample->sampletype == $sampletype->id)
                                                selected
                                            @endif

                                            >{{ $sampletype->id }} &nbsp; {{ $sampletype->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div> 

                        @endif

                        <div class="hr-line-dashed"></div>                      

                        <div class="form-group">
                            <label class="col-sm-4 control-label">Date of Collection
                                <strong><div style='color: #ff0000; display: inline;'>*</div></strong>
                            </label>
                            <div class="col-sm-8">
                                <div class="input-group date date-normal">
                                    <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                    <input type="text" id="datecollected" required class="form-control requirable" value="{{ $viralsample->datecollected ?? '' }}" name="datecollected">
                                </div>
                            </div>                            
                        </div>                      

                        <div class="form-group">
                            <label class="col-sm-4 control-label">Date of Separation</label>
                            <div class="col-sm-8">
                                <div class="input-group date date-normal">
                                    <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                    <input type="text" id="dateseparated" class="form-control" value="{{ $viralsample->dateseparated ?? '' }}" name="dateseparated">
                                </div>
                            </div>                            
                        </div>  

                        <div class="form-group">
                            <label class="col-sm-4 control-label">Date Started on ART
                                <strong><div style='color: #ff0000; display: inline;'>*</div></strong>
                            </label>
                            <div class="col-sm-8">
                                <div class="input-group date date-art">
                                    <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                    <input type="text" id="initiation_date" 
                                    @if(!isset($viralsample) || ($viralsample && $viralsample->patient->initiation_date))
                                        required 
                                    @endif
                                    class="form-control lockable requirable" value="{{ $viralsample->patient->initiation_date ?? '' }}" name="initiation_date">
                                </div>
                            </div>                            
                        </div>

                        <div class="form-group">
                            <label class="col-sm-4 control-label">Date Dispatched from Facility</label>
                            <div class="col-sm-8">
                                <div class="input-group date date-dispatched">
                                    <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                    <input type="text" id="datedispatched" class="form-control" value="{{ $viralsample->batch->datedispatchedfromfacility ?? $batch->datedispatchedfromfacility ?? '' }}" name="datedispatchedfromfacility">
                                </div>
                            </div>                            
                        </div>

                        <div class="hr-line-dashed"></div>

                        <div class="form-group">
                            <label class="col-sm-4 control-label">Current Regimen
                                <strong><div style='color: #ff0000; display: inline;'>*</div></strong>
                            </label>
                            <div class="col-sm-8">
                                <select class="form-control requirable" required name="prophylaxis" id="prophylaxis">
                                    <option></option>
                                    @foreach ($prophylaxis as $proph)
                                        @continue($proph->id == 16 && auth()->user()->user_type_id == 5)
                                        <option value="{{ $proph->id }}"

                                        @if (isset($viralsample) && $viralsample->prophylaxis == $proph->id)
                                            selected
                                        @endif

                                        > {!! $proph->displaylabel !!}
                                        </option>

                                        @if($proph->id == 9)
                                            <option value="18">12 &nbsp;ABC+3TC+DTG</option>
                                        @endif
                                    @endforeach
                                    
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-4 control-label">Date Initiated on Current Regimen</label>
                            <div class="col-sm-8">
                                <div class="input-group date date-art">
                                    <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                    <input type="text" id="dateinitiatedonregimen" class="form-control" value="{{ $viralsample->dateinitiatedonregimen ?? '' }}" name="dateinitiatedonregimen">
                                </div>
                            </div>                            
                        </div>

                        <div class="form-group">
                            <label class="col-sm-4 control-label">1st or 2nd Line Regimen
                                <strong><div style='color: #ff0000; display: inline;'>*</div></strong>
                            </label>
                            <div class="col-sm-8">
                                <select class="form-control requirable" required name="regimenline" id="regimenline">
                                    <option></option>
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

                        <div class="hr-line-dashed"></div>  

                        <div class="form-group">
                            <label class="col-sm-4 control-label">Justification
                                <strong><div style='color: #ff0000; display: inline;'>*</div></strong>
                            </label>
                            <div class="col-sm-8">
                                <select class="form-control requirable" required name="justification" id="justification">
                                    <option></option>
                                    @foreach ($justifications as $justification)
                                        @continue($justification->id == 8 && auth()->user()->user_type_id == 5)
                                        <option value="{{ $justification->id }}"

                                        @if (isset($viralsample) && $viralsample->justification == $justification->id)
                                            selected
                                        @endif

                                        > {!! $justification->displaylabel !!}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="hr-line-dashed"></div> 


                    </div>
                </div>
            </div>
        </div>



        <div class="row">
            <div class="col-lg-12">
                <div class="hpanel">
                    <!-- <div class="panel-heading">
                        <center>Sample Information</center>
                    </div> -->
                    <div class="panel-body">

                        @if(isset($poc))
                            <input type="hidden" value=2 name="site_entry">

                            <div class="form-group">
                              <label class="col-sm-4 control-label">POC Site Sample Tested at
                                <strong><div style='color: #ff0000; display: inline;'>*</div></strong>
                                </label>
                              <div class="col-sm-8">
                                <select class="form-control requirable" required name="lab_id" id="lab_id">
                                    @isset($viralsample)
                                        <option value="{{ $viralsample->batch->facility_lab->id }}" selected>{{ $viralsample->batch->facility_lab->facilitycode }} {{ $viralsample->batch->facility_lab->name }}</option>
                                    @endisset
                                </select>
                              </div>
                            </div>

                        @endif

                        <div class="form-group alupe-div">
                            <label class="col-sm-4 control-label">VL Test Request Number</label>
                            <div class="col-sm-8">
                                <input class="form-control" name="vl_test_request_no" number="number" min=0 max=10 type="text" value="{{ $viralsample->vl_test_request_no ?? '' }}">
                            </div>
                        </div>


                        <div class="hr-line-dashed"></div> 

                        <div></div>

                        @if(auth()->user()->user_type_id != 5 || isset($poc) || (isset($viralsample) && $viralsample->batch->site_entry == 2))

                            <div class="form-group">
                                <label class="col-sm-4 control-label">Date Received
                                    <strong><div style='color: #ff0000; display: inline;'>*</div></strong>
                                </label>
                                <div class="col-sm-8">
                                    <div class="input-group date date-normal">
                                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                        <input type="text" id="datereceived" required class="form-control requirable" value="{{ $viralsample->batch->datereceived ?? $batch->datereceived ?? '' }}" name="datereceived">
                                    </div>
                                </div>                            
                            </div> 

                            <div class="form-group">
                                <label class="col-sm-4 control-label">Received Status
                                    <strong><div style='color: #ff0000; display: inline;'>*</div></strong>
                                </label>
                                <div class="col-sm-8">
                                        <select class="form-control requirable" required name="receivedstatus" id="receivedstatus">

                                        <option></option>
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

                                        <option></option>
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
                        
                        @if(auth()->user()->user_type_id == 5)
                            <div class="form-group">
                                <label class="col-sm-4 control-label">Entered By
                                    <strong><div style='color: #ff0000; display: inline;'>*</div></strong>
                                </label>
                                <div class="col-sm-8">
                                    <input class="form-control requirable" required name="entered_by"  type="text" value="{{ $viralsample->batch->entered_by ?? '' }}">
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
            <div class="col-lg-12">
                <div class="hpanel">
                    <div class="panel-body">
                        <div class="form-group"><label class="col-sm-4 control-label">Comments (from facility)</label>
                            <div class="col-sm-8"><textarea  class="form-control" name="comments"> {{ $viralsample->comments ?? '' }} </textarea></div>
                        </div>
                        @if(auth()->user()->user_type_id != 5)
                            <div class="form-group"><label class="col-sm-4 control-label">Lab Comments</label>
                                <div class="col-sm-8"><textarea  class="form-control" name="labcomment"> {{ $viralsample->labcomment ?? '' }} </textarea></div>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="hr-line-dashed"></div>
                <div class="form-group">
                    <center>
                        @if(isset($viralsample))
                            <div class="col-sm-4 col-sm-offset-4">
                                <button class="btn btn-primary" type="submit" name="submit_type" value="add">
                                        @if (isset($site_entry_approval))
                                            Save & Load Next Sample in Batch for Approval
                                        @else
                                            Update Sample
                                        @endif
                                </button>
                            </div>
                        @else
                            <div class="col-sm-8 col-sm-offset-2">
                                <button class="btn btn-success" type="submit" name="submit_type" value="release">Save & Release sample</button>
                                <button class="btn btn-primary" type="submit" name="submit_type" value="add">Save & Add sample</button>
                                    
                                @isset($batch)
                                    <button class="btn btn-danger" type="submit" formnovalidate name="submit_type" value="cancel">Cancel & Release</button>
                                @endisset
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
                    lessThan: ["#datecollected", "Date of Birth", "Date Collected"],
                    lessThanTwo: ["#initiation_date", "Date of Birth", "ART Inititation Date"]
                },
                initiation_date:{
                    GreaterThanSpecific: ["1990-01-01", "Date of Initiating ART"]
                },
                datecollected: {
                    lessThan: ["#datedispatched", "Date Collected", "Date Dispatched From Facility"],
                    lessThanTwo: ["#datereceived", "Date Collected", "Date Received"]
                },
                datedispatched: {
                    lessThan: ["#datereceived", "Date Dispatched From Facility", "Date Received"]
                },
                age: {
                    required: '#dob:blank'
                }
            }
        @endslot


        //$(".date :not(.date-dob, .date-art, .date-dispatched)").datepicker({
        $(".date-normal").datepicker({
            startView: 0,
            todayBtn: "linked",
            keyboardNavigation: false,
            forceParse: true,
            autoclose: true,
            startDate: "-6m",
            endDate: new Date(),
            format: "yyyy-mm-dd"
        });

        $(".date-dob").datepicker({
            startView: 2,
            keyboardNavigation: false,
            forceParse: true,
            autoclose: true,
            startDate: '-100y',
            endDate: "-1m",
            format: "yyyy-mm-dd"
        });

        $(".date-dispatched").datepicker({
            startView: 0,
            todayBtn: "linked",
            keyboardNavigation: false,
            forceParse: true,
            autoclose: true,
            startDate: "-6m",
            endDate: "+7d",
            format: "yyyy-mm-dd"
        });

        // $("#dateinitiatedontreatment").datepicker({
        $(".date-art").datepicker({
            startView: 2,
            todayBtn: "linked",
            keyboardNavigation: false,
            forceParse: true,
            autoclose: true,
            startDate: '-24y',
            endDate: new Date(),
            format: "yyyy-mm-dd"
        });

        set_select_facility("facility_id", "{{ url('/facility/search') }}", 3, "Search for facility", false);
        set_select_facility("lab_id", "{{ url('/facility/search') }}", 3, "Search for facility", false);

    @endcomponent


    <script type="text/javascript">
        $(document).ready(function(){


            @if(env('APP_LAB') == 3 && auth()->user()->is_lab_user() && !isset($viralsample))
                $("#samples_form input,select").change(function(){
                    var frm = $('#samples_form');
                    var data = frm.serializeObject();
                    check_similar_samples(data);
                });  
            @endif


            $("#rejection").hide();

            @if(isset($viralsample))                
                @if($viralsample->receivedstatus == 2)
                    $("#rejection").show();
                    $("#rejectedreason").removeAttr("disabled");
                    $('.requirable').removeAttr("required");
                @endif

                @if($viralsample->patient->sex == 1)
                    $("#pmtct").attr("disabled", "disabled");
                @endif
            @else
                $("#patient").blur(function(){
                    var patient = $(this).val();
                    var facility = $("#facility_id").val();
                    check_new_patient(patient, facility);
                });
            @endif

            $("#facility_id").change(function(){
                var val = $(this).val();

                if(val == 7148 || val == '7148'){
                    $('.requirable').removeAttr("required");
                }
                else{
                    $('.requirable').attr("required", "required");
                }
            });  

            $("#sampletype").change(function(){
                var val = $(this).val();
                if(val == 3 || val == 4){
                    $("#dateseparated").attr("disabled", "disabled");
                }
                else{
                    $("#dateseparated").removeAttr("disabled");
                }
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
                    $('.requirable').removeAttr("required");
                }
                else{
                    $("#rejection").hide();
                    $("#rejectedreason").attr("disabled", "disabled");
                    $('.requirable').attr("required", "required");
                }
            });


            @if(!in_array(env('APP_LAB'), $amrs))
                $(".ampath-div").hide();
            @endif 

            @if(env('APP_LAB') != 2)
                $(".alupe-div").hide();
            @endif  

            
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
                        $("#initiation_date").val(patient.initiation_date);
                        $("#patient_phone_no").val(patient.patient_phone_no);
                        // $('#sex option[value='+ patient.sex + ']').attr('selected','selected').change();

                        $("#sex").val(patient.sex).change();

                        $('<input>').attr({
                            type: 'hidden',
                            name: 'patient_id',
                            value: patient.id,
                            id: 'hidden_patient',
                            class: 'patient_details'
                        }).appendTo("#samples_form");

                        if(data[3] != 0)
                        {
                            set_message(data[3]);
                        }

                        // $(".lockable").attr("disabled", "disabled");
                    }
                    else{
                        localStorage.setItem("new_patient", 1);
                        // $(".lockable").removeAttr("disabled");
                        // $(".lockable").val('').change();

                        $('.patient_details').remove();
                    }

                }
            });
        }

        function check_similar_samples(json_data){
            json_data['_token'] = "{{ csrf_token() }}";
            $.ajax({
               type: "POST",
               data: json_data,
               url: "{{ url('/viralsample/similar') }}",
               success: function(data){
                    $("#similar_samples").html(data);
                    // console.log(data);
                }
            });
        }
    </script>



@endsection
