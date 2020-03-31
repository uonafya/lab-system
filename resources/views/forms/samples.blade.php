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
            {{ Form::open(['url' => '/sample/' . $sample->id, 'method' => 'put', 'class'=>'form-horizontal']) }}
        @else
            {{ Form::open(['url'=>'/sample', 'method' => 'post', 'class'=>'form-horizontal', 'id' => 'samples_form']) }}
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

                        @if(env('APP_LAB') == 2)

                            <div class="alert alert-warning">
                                <center>
                                    Please fill the HEI number by starting the facility mfl code <br />
                                    Use the following format: MFL/YYYY/NNNNN
                                </center>
                            </div>
                            <br />

                        @endif

                        @isset($sample)
                            <div class="alert alert-warning">
                                <center>
                                    NB: If you edit the facility name, date received or date dispatched from the facility this will be reflected on the other samples in this batch.
                                </center>
                            </div>
                            <br />
                        @endisset

                        @if(!$batch)    
                          <div class="form-group">
                              <label class="col-sm-4 control-label">Facility 
                                <strong><div style='color: #ff0000; display: inline;'>*</div></strong>
                              </label>
                              <div class="col-sm-8">
                                <select class="form-control requirable" required name="facility_id" id="facility_id">
                                    @isset($sample)
                                        <option value="{{ $sample->batch->facility->id }}" selected>{{ $sample->batch->facility->facilitycode }} {{ $sample->batch->facility->name }}</option>
                                    @endisset

                                </select>
                              </div>
                          </div>
                        @else

                            <div class="alert alert-success">
                                <center> <b>Facility</b> - {{ $facility_name }}<br />  <b>Batch</b> - {{ $batch->id }} </center>
                            </div>
                            <br />

                            @if(session('last_patient'))

                                <div class="alert alert-success">
                                    <center> <b>Last Patient Entered</b> - {{ session('last_patient') }} </center>
                                </div>
                                <br />

                            @endif

                            <input type="hidden" name="facility_id" value="{{$batch->facility_id}}">
                        @endif
                        
                        @if(auth()->user()->user_type_id != 5 && env('APP_LAB') == 4)
                            <div class="form-group">
                                <label class="col-sm-4 control-label">High Priority</label>
                                <div class="col-sm-8">
                                <input type="checkbox" class="i-checks" name="highpriority" value="1"
                                    @if(isset($sample) && $sample->batch->highpriority)
                                        checked
                                    @endif

                                 />
                                </div>
                            </div>
                        @endif

                        
                        <div class="form-group ampath-div">
                            <label class="col-sm-4 control-label">(*for Ampath Sites only) AMRS Location</label>
                            <div class="col-sm-8">
                                <select class="form-control ampath-only" name="amrs_location">

                                  <option></option>
                                  @foreach ($amrs_locations as $amrs_location)
                                      <option value="{{ $amrs_location->id }}"

                                      @if (isset($sample) && $sample->amrs_location == $amrs_location->id)
                                          selected
                                      @endif

                                      > {{ $amrs_location->name }}
                                      </option>
                                  @endforeach

                                </select>
                            </div>
                        </div>

                        <div class="form-group ampath-div">
                            <label class="col-sm-4 control-label">(*for Ampath Sites only) AMRS Provider Identifier</label>
                            <div class="col-sm-8">
                                <input class="form-control ampath-only" name="provider_identifier" type="text" value="{{ $sample->provider_identifier ?? '' }}">
                            </div>
                        </div>

                        <div class="form-group alupe-div">
                            <label class="col-sm-4 control-label">Sample Type</label>
                            <div class="col-sm-8">
                                <select class="form-control" name="sample_type">
                                    <option></option>
                                    <option value="GAP Sample"> GAP Sample </option>
                                    <option value="Study Sample"> Study Sample </option>
                                </select>
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
                        <center>Infant Information</center>
                    </div>
                    <div class="panel-body" style="padding-bottom: 6px;">

                        @if( in_array(env('APP_LAB'), $sms))

                            <div class="form-group">
                                <label class="col-sm-4 control-label">Phone No (format 254725******)</strong>
                                </label>
                                <div class="col-sm-3">
                                    <input class="form-control" name="patient_phone_no" id="patient_phone_no" type="text" value="{{ $sample->patient->patient_phone_no ?? '' }}">
                                </div>

                                <div class="col-sm-1">Patient's Preferred Language</div>

                                <div class="col-sm-4">
                                    @foreach($languages as $key => $value)
                                        <label><input type="radio" class="i-checks" name="preferred_language" value={{ $key }} 

                                            @if(isset($sample) && $sample->patient->preferred_language == $key)
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
                            <label class="col-sm-4 control-label">Infant Name</label>
                            <div class="col-sm-8">
                                <input class="form-control" name="patient_name" id="patient_name" type="text" value="{{ $sample->patient->patient_name ?? '' }}">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-4 control-label">HEI ID Number
                                <strong><div style='color: #ff0000; display: inline;'>*</div></strong>
                            </label>
                            <div class="col-sm-8">
                                <input class="form-control requirable" required name="patient" type="text" value="{{ $sample->patient->patient ?? '' }}" id="patient">
                            </div>
                        </div>

                        @if(env('APP_LAB') == 4)

                            <div class="form-group">
                                <label class="col-sm-4 control-label">Automatically Add Slash to HEI Number</label>
                                <div class="col-sm-8">
                                <input type="checkbox" class="i-checks" name="automatic_slash" value="1" checked="checked" />
                                </div>
                            </div>

                        @endif

                        @if(!isset($sample))
                            
                            <div class="form-group">
                                <label class="col-sm-4 control-label">Confirm Re-Entry (Sample Exists but should not be flagged as a double-entry)</label>
                                <div class="col-sm-8">
                                <input type="checkbox" class="i-checks" name="reentry" value="1" />
                                </div>
                            </div>

                        @endif

                        <div class="form-group">
                            <label class="col-sm-4 control-label">PCR Type
                                <strong><div style='color: #ff0000; display: inline;'>*</div></strong>
                            </label>
                            <div class="col-sm-5">
                                <select class="form-control requirable" required name="pcrtype" id="pcrtype">

                                    <option></option>
                                    @foreach ($pcrtypes as $pcrtype)
                                        @continue($pcrtype->id == 5)
                                        <option value="{{ $pcrtype->id }}"

                                        @if (isset($sample) && $sample->pcrtype == $pcrtype->id)
                                            selected
                                        @endif

                                        > {!! $pcrtype->name !!}
                                        </option>
                                    @endforeach

                                </select>
                            </div>

                            <div class="col-sm-3">
                                <label> <input type="checkbox" class="i-checks" name="redraw" value=1
                                    @if(isset($sample) && $sample->redraw == 1)
                                        checked
                                    @endif

                                 > Tick only if sample redraw </label>
                            </div>

                        </div>

                        <!-- <input type="hidden" name="pcrtype" id="hidden_pcr"> -->

                        <div class="form-group">
                            <label class="col-sm-4 control-label">Date of Birth
                                <strong><div style='color: #ff0000; display: inline;'>*</div></strong>
                            </label>
                            <div class="col-sm-8">
                                <div class="input-group date date-dob">
                                    <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                    <input type="text" id="dob" required class="form-control lockable requirable" value="{{ $sample->patient->dob ?? '' }}" name="dob">
                                </div>
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

                                        @if (isset($sample) && $sample->patient->sex == $gender->id)
                                            selected
                                        @endif

                                        > {{ $gender->gender_description }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-4 control-label">Entry Point
                                <strong><div style='color: #ff0000; display: inline;'>*</div></strong>
                            </label>
                            <div class="col-sm-8">
                                <select class="form-control lockable requirable" required name="entry_point" id="entry_point">

                                    <option></option>
                                    @foreach ($entry_points as $entry_point)
                                        @continue(auth()->user()->user_type_id == 5 && $entry_point->id == 7)
                                        <option value="{{ $entry_point->id }}"

                                        @if (isset($sample) && $sample->patient->entry_point == $entry_point->id)
                                            selected
                                        @endif

                                        >{{ $entry_point->id }} &nbsp;  {{ $entry_point->name }}
                                        </option>
                                    @endforeach

                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-4 control-label">Infant Prophylaxis
                                <strong><div style='color: #ff0000; display: inline;'>*</div></strong>
                            </label>
                            <div class="col-sm-8">
                                <select class="form-control requirable" required name="regimen">

                                    <option></option>
                                    @foreach ($iprophylaxis as $ip)
                                        @continue(auth()->user()->user_type_id == 5 && $ip->id == 14)
                                        <option value="{{ $ip->id }}"

                                        @if (isset($sample) && $sample->regimen == $ip->id)
                                            selected
                                        @endif

                                        >{{ $ip->rank }} &nbsp; {{ $ip->name }}
                                        </option>
                                    @endforeach

                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-4 control-label">Infant Feeding Code
                                <strong><div style='color: #ff0000; display: inline;'>*</div></strong>
                            </label>
                            <div class="col-sm-8">
                                <select class="form-control requirable" required name="feeding">

                                    <option></option>
                                    @foreach ($feedings as $feeding)
                                        @continue(auth()->user()->user_type_id == 5 && $feeding->id == 5)
                                        <option value="{{ $feeding->id }}"

                                        @if (isset($sample) && $sample->feeding == $feeding->id)
                                            selected
                                        @endif

                                        > {{ $feeding->feeding_description }}
                                        </option>
                                    @endforeach

                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-4 control-label">CCC No</label>
                            <div class="col-sm-8">
                                <input class="form-control" name="enrollment_ccc_no" type="text" value="{{ $sample->patient->enrollment_ccc_no ?? '' }}" id="enrollment_ccc_no">
                            </div>
                        </div>




                        <div class="hr-line-dashed"></div>

                        {{--
                            <!-- @isset($sample)

                                @php

                                    $months = (int) $sample->age;
                                    $weeks = $sample->age - (int) $sample->age;

                                @endphp

                            @endisset

                            <div class="form-group">
                                <label class="col-sm-4 control-label">Age</label>
                                <div class="col-sm-8">
                                    <input class="form-control" type="text" required name="sample_months" placeholder="Months" value="{{ $months ?? '' }}">
                                </div>
                                <div class="col-sm-8 col-sm-offset-4 input-sm" style="margin-top: 1em;">
                                    <input class="form-control" type="text" required name="sample_weeks" placeholder="Weeks" value="{{ $weeks ?? '' }}">
                                </div>
                            </div>  -->
                        --}} 

                        <!-- <div class="hr-line-dashed"></div> -->


                    </div>
                </div>
            </div>
        </div>


        <div class="row">
            <div class="col-lg-12">
                <div class="hpanel">
                    <div class="panel-heading" style="padding-bottom: 2px;padding-top: 4px;">
                        <center>Mother Information</center>
                    </div>
                    <div class="panel-body" style="padding-bottom: 6px;">

                        <div class="form-group">
                            <label class="col-sm-4 control-label">Mother's Age</label>
                            <div class="col-sm-8">
                                <input class="form-control" id="mother_age" name="mother_age" type="text" value="{{ $sample->mother_age ?? '' }}" number="number" min=10 max=70>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-4 control-label">CCC No</label>
                            <div class="col-sm-8"><input class="form-control" id="ccc_no" name="ccc_no" type="text" value="{{ $sample->patient->mother->ccc_no ?? '' }}"></div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-4 control-label">PMTCT Regimen
                                <strong><div style='color: #ff0000; display: inline;'>*</div></strong>
                            </label>
                            <div class="col-sm-8"><select class="form-control requirable" required name="mother_prophylaxis">

                                <option></option>
                                @foreach ($interventions as $intervention)
                                    @continue(auth()->user()->user_type_id == 5 && $intervention->id == 7)
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
                            <label class="col-sm-4 control-label">VL result within last 6 months</label>
                            <div class="col-sm-5">
                                <input class="form-control" id="mother_last_result" name="mother_last_result" type="text"  number="number" placeholder="Input if result is a number e.g. 1200 cp/ml." 

                                    @if(isset($sample) && is_numeric($sample->mother_last_result))
                                        value="{{ $sample->mother_last_result ?? '' }}"
                                    @endif

                                >
                            </div>

                            <div class="col-sm-3">
                                <label> <input type="checkbox" class="i-checks" name="last_result" value="< LDL copies/ml"
                                    @if(isset($sample) && $sample->mother_last_rcategory == 1)
                                        checked
                                    @endif

                                 />Tick if result is <b> &lt; LDL cp/ml</b> </label>
                            </div>
                        </div> 

                        {{--<!-- <div class="form-group">
                            <label class="col-sm-4 control-label">HIV Status</label>
                            <div class="col-sm-8">
                                    <select class="form-control lockable" required name="hiv_status" id="hiv_status">

                                    <option></option>
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
                            <div class="col-sm-8"><input class="form-control" name="caregiver_phone" type="text" value="{{ $sample->patient->caregiver_phone ?? '' }}"></div>
                        </div> -->--}}

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

                        @if(isset($poc))
                            <input type="hidden" value=2 name="site_entry">

                            <div class="form-group">
                                <label class="col-sm-4 control-label">POC Site Sample Tested at
                                    <strong><div style='color: #ff0000; display: inline;'>*</div></strong>
                                </label>
                              <div class="col-sm-8">
                                <select class="form-control requirable" required name="lab_id" id="lab_id">
                                    @isset($sample)
                                        <option value="{{ $sample->batch->facility_lab->id }}" selected>{{ $sample->batch->facility_lab->facilitycode }} {{ $sample->batch->facility_lab->name }}</option>
                                    @endisset
                                </select>
                              </div>
                            </div>

                        @endif
                        
                        @if(auth()->user()->user_type_id != 5)
                            <div class="form-group">
                                <label class="col-sm-4 control-label">No of Spots
                                    <strong><div style='color: #ff0000; display: inline;'>*</div></strong>
                                </label>
                                <div class="col-sm-8">
                                    <input class="form-control requirable" required name="spots" number="number" min=1 max=5 type="text" value="{{ $sample->spots ?? '' }}">
                                </div>
                            </div>
                        @endif

                        <div class="form-group">
                            <label class="col-sm-4 control-label">Date of Collection
                                <strong><div style='color: #ff0000; display: inline;'>*</div></strong>
                            </label>
                            <div class="col-sm-8">
                                <div class="input-group date date-normal">
                                    <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                    <input type="text" id="datecollected" required class="form-control requirable" value="{{ $sample->datecollected ?? '' }}" name="datecollected">
                                </div>
                            </div>                            
                        </div> 
                        

                        <div class="form-group">
                            <label class="col-sm-4 control-label">Date Dispatched from Facility</label>
                            <div class="col-sm-8">
                                <div class="input-group date date-dispatched date_future">
                                    <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                    <input type="text" id="datedispatched" class="form-control" value="{{ $sample->batch->datedispatchedfromfacility ?? $batch->datedispatchedfromfacility ?? '' }}" name="datedispatchedfromfacility">
                                </div>
                            </div>                            
                        </div> 


                        <div></div>

                        @if(auth()->user()->user_type_id != 5 || isset($poc) || (isset($sample) && $sample->batch->site_entry == 2))
                            <div class="form-group">
                                <label class="col-sm-4 control-label">Date Received
                                    <strong><div style='color: #ff0000; display: inline;'>*</div></strong>
                                </label>
                                <div class="col-sm-8">
                                    <div class="input-group date date-normal">
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

                                        <option></option>
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

                                        <option></option>
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
                        
                        @if(auth()->user()->user_type_id == 5)
                            <div class="form-group">
                                <label class="col-sm-4 control-label">Entered By
                                    <strong><div style='color: #ff0000; display: inline;'>*</div></strong>
                                </label>
                                <div class="col-sm-8">
                                    <input class="form-control requirable" required name="entered_by"  type="text" value="{{ $sample->batch->entered_by ?? '' }}">
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
                    <div class="panel-body" style="padding-bottom: 6px;">
                        <div class="form-group"><label class="col-sm-4 control-label">Comments (from facility)</label>
                            <div class="col-sm-8">
                                <textarea  class="form-control" name="comments">{{ $sample->comments ?? '' }}</textarea>
                            </div>
                        </div>
                        @if(auth()->user()->user_type_id != 5)
                            <div class="form-group"><label class="col-sm-4 control-label">Lab Comments</label>
                                <div class="col-sm-8"><textarea  class="form-control" name="labcomment">
                                    {{ $sample->labcomment ?? '' }}
                                </textarea></div>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="hr-line-dashed"></div>
                <div class="form-group">
                    <center>

                        @if (isset($sample))
                            <div class="col-sm-8 col-sm-offset-4">
                                <button class="btn btn-primary" type="submit" name="submit_type" value="add">
                                    @if (isset($site_entry_approval))
                                        Save & Load Next Sample in Batch for Approval
                                    @else
                                        Update Sample
                                    @endif
                                </button>
                                {{--
                                    @empty($site_entry_approval)
                                        <button class="btn btn-primary" type="submit" name="submit_type" value="new_batch">
                                            Update Sample And Create New Batch
                                        </button>
                                    @endempty
                                --}}

                            </div>
                        @else
                            <div class="col-sm-10 col-sm-offset-1">
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
                    lessThan: ["#datecollected", "Date of Birth", "Date Collected"]
                },
                datecollected: {
                    lessThan: ["#datedispatched", "Date Collected", "Date Dispatched From Facility"],
                    @if(auth()->user()->user_type_id != 5)
                        lessThanTwo: ["#datereceived", "Date Collected", "Date Received"]
                    @endif
                },
                datedispatched: {
                    lessThan: ["#datereceived", "Date Dispatched From Facility", "Date Received"]
                } 
                               
            }
        @endslot

        // $(".date :not(.date-dob, .date-dispatched)").datepicker({
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
            startView: 1,
            todayBtn: "linked",
            keyboardNavigation: false,
            forceParse: true,
            autoclose: true,
            startDate: "-3y",
            endDate: new Date(),
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

        set_select_facility("facility_id", "{{ url('/facility/search') }}", 3, "Search for facility", false);
        set_select_facility("lab_id", "{{ url('/facility/search') }}", 3, "Search for facility", false);

    @endcomponent


    <script type="text/javascript">
        $(document).ready(function(){
            $("#rejection").hide();

            @if(env('APP_LAB') == 8 && auth()->user()->is_lab_user() && !isset($sample))
                $("#samples_form input,select").change(function(){
                    var frm = $('#samples_form');
                    // var data = JSON.stringify(frm.serializeObject());
                    var data = frm.serializeObject();
                    console.log(data);
                });  
            @endif

            @if(isset($sample))                
                @if($sample->receivedstatus == 2)
                    $("#rejection").show();
                    $("#rejectedreason").removeAttr("disabled");
                    $('.requirable').removeAttr("required");
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



            $("#receivedstatus").change(function(){
                var val = $(this).val();
                if(val == 2){
                    $("#rejection").show();
                    $("#rejectedreason").removeAttr("disabled");
                    $('.requirable').removeAttr("required");
                    // $("#rejectedreason").prop('disabled', false);
                }
                else{
                    $("#rejection").hide();
                    $("#rejectedreason").attr("disabled", "disabled");
                    $('.requirable').attr("required", "required");
                    // $("#enrollment_ccc_no").attr("disabled", "disabled");
                    // $("#rejectedreason").prop('disabled', true);

                }
            }); 

            $("#pcrtype").change(function(){
                var val = $(this).val();
                if(val == 4){
                    $("#enrollment_ccc_no").removeAttr("disabled");
                }
                else{
                    $("#enrollment_ccc_no").attr("disabled", "disabled");
                }
            }); 



            @if(!in_array(env('APP_LAB'), $amrs))
                $(".ampath-div").hide();
            @endif 

            @if(env('APP_LAB', 3))
                $(".alupe-div").hide();
            @endif  


        });


        function check_new_patient(patient, facility_id){
            $.ajax({
               type: "POST",
               data: {
                patient : patient,
                facility_id : facility_id
               },
               url: "{{ url('/sample/new_patient') }}",


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

                        $("#patient_name").val(patient.patient_name);
                        $("#patient_phone_no").val(patient.patient_phone_no);
                        $("#sex").val(patient.sex).change();
                        $("#entry_point").val(patient.entry_point).change();
                        $("#mother_age").val(mother.age);
                        // $("#hiv_status").val(mother.hiv_status).change();
                        $("#ccc_no").val(mother.ccc_no).change();
                        $("#pcrtype").val(prev.recommended_pcr).change();

                        // $('#pcrtype option[value=' + prev.recommended_pcr + ']').attr('selected','selected').change();
                        // $("#hidden_pcr").val(2);

                        // if(prev.previous_positive == 1){
                        //     $('#pcrtype option[value=4]').attr('selected','selected').change();
                        //     // $("#hidden_pcr").val(3); 
                        // }
                        $('<input>').attr({
                            type: 'hidden',
                            name: 'patient_id',
                            value: patient.id,
                            id: 'hidden_patient',
                            class: 'patient_details'
                        }).appendTo("#samples_form");

                        if(data[4] != 0)
                        {
                            set_message(data[4]);
                        }

                        // $('<input>').attr({
                        //     type: 'hidden',
                        //     name: 'dob',
                        //     value: patient.dob,
                        //     class: 'patient_details'
                        // }).appendTo("#samples_form");


                        // $(".lockable").attr("disabled", "disabled");
                    }
                    else{
                        localStorage.setItem("new_patient", 1);
                        // $(".lockable").removeAttr("disabled");
                        // $(".lockable").val('').change();
                        $('#pcrtype option[value=1]').attr('selected','selected').change();
                        // $("#hidden_pcr").val(1);

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
