@extends('layouts.master')

@component('/forms/css')
    <link href="{{ asset('css/datapicker/datepicker3.css') }}" rel="stylesheet" type="text/css">
@endcomponent

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
            {{ Form::open(['url'=>'/sample', 'method' => 'post', 'class'=>'form-horizontal']) }}
        @endif

        <div class="row">
            <div class="col-lg-7 col-lg-offset-2">
                <div class="hpanel">
                    <div class="panel-body">

                      <div class="form-group">
                          <label class="col-sm-4 control-label">Facility</label>
                          <div class="col-sm-8"><select class="form-control" required name="facility_id">

                              <option value=""> Select One </option>
                              @foreach ($facilities as $facility)
                                  <option value="{{ $facility->id }}"

                                  @if (isset($sample) && $sample->facility_id == $facility->id)
                                      selected
                                  @endif

                                  > {{ $facility->name }}
                                  </option>
                              @endforeach

                          </select></div>
                      </div>


                      <div class="form-group">
                          <label class="col-sm-4 control-label">(*for Ampath Sites only) AMRS Location</label>
                          <div class="col-sm-8"><select class="form-control ampath-only" name="amrs_location_id">

                              <option value=""> Select One </option>
                              @foreach ($amrs_locations as $amrs_location)
                                  <option value="{{ $amrs_location->id }}"

                                  @if (isset($sample) && $sample->facility_id == $amrs_location->id)
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
                        <center>Infant Information</center>
                    </div>
                    <div class="panel-body">

                        <div class="form-group">
                            <label class="col-sm-4 control-label">Patient / Sample ID</label>
                            <div class="col-sm-8">
                                <input class="form-control" required name="patient_id" type="text" value="{{ $sample->patient_id or '' }}">
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
                                <input class="form-control ampath-only" required name="patient_name" type="text" value="{{ $sample->patient_name or '' }}">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-4 control-label">Sex</label>
                            <div class="col-sm-8"><select class="form-control" required name="gender">

                                <option value=""> Select One </option>
                                @foreach ($genders as $gender)
                                    <option value="{{ $gender->gender }}"

                                    @if (isset($sample) && $sample->patient->gender == $gender->gender)
                                        selected
                                    @endif

                                    > {{ $gender->gender_description }}
                                    </option>
                                @endforeach

                            </select></div>
                        </div>

                        <div class="hr-line-dashed"></div>

                        <div class="form-group">
                            <label class="col-sm-4 control-label">Age</label>
                            <div class="col-sm-8">
                                <input class="form-control" type="text" required name="sample_months" placeholder="Months" value="{{ $sample->sample_months or '' }}">
                            </div>
                            <div class="col-sm-8 col-sm-offset-4 input-sm" style="margin-top: 1em;">
                                <input class="form-control" type="text" required name="sample_weeks" placeholder="Weeks" value="{{ $sample->sample_weeks or '' }}">
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
            <div class="col-lg-7 col-lg-offset-2">
                <div class="hpanel">
                    <div class="panel-heading">
                        <center>Mother Information</center>
                    </div>
                    <div class="panel-body">

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
                            <div class="col-sm-8"><select class="form-control" required name="feeding_id">

                                <option value=""> Select One </option>
                                @foreach ($feedings as $feeding)
                                    <option value="{{ $feeding->id }}"

                                    @if (isset($sample) && $sample->feeding_id == $feeding->id)
                                        selected
                                    @endif

                                    > {{ $feeding->feeding_description }}
                                    </option>
                                @endforeach

                            </select></div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-4 control-label">Entry Point</label>
                            <div class="col-sm-8"><select class="form-control" required name="entry_point_id">

                                <option value=""> Select One </option>
                                @foreach ($entry_points as $entry_point)
                                    <option value="{{ $entry_point->id }}"

                                    @if (isset($sample) && $sample->entry_point_id == $entry_point->id)
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
                                    <select class="form-control" required name="hiv_status">

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
            <div class="col-lg-7 col-lg-offset-2">
                <div class="hpanel">
                    <div class="panel-heading">
                        <center>Sample Information</center>
                    </div>
                    <div class="panel-body">

                        <div class="form-group">
                            <label class="col-sm-4 control-label">No of Spots</label>
                            <div class="col-sm-8">
                                <input class="form-control" required name="spots" number="number" min=1 max=5 type="text" value="{{ $sample->spots or '' }}">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-4 control-label">Date of Collection</label>
                            <div class="col-sm-8">
                                <div class="input-group date">
                                    <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                    <input type="text" id="datecollected" required class="form-control" value="{{ $sample->datecollected or '' }}" name="datecollected">
                                </div>
                            </div>                            
                        </div> 

                        <div class="form-group">
                            <label class="col-sm-4 control-label">Date Dispatched from Facility</label>
                            <div class="col-sm-8">
                                <div class="input-group date">
                                    <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                    <input type="text" id="datedispatched" class="form-control" value="{{ $sample->datedispatched or '' }}" name="datedispatched">
                                </div>
                            </div>                            
                        </div> 

                        <div class="form-group">
                            <label class="col-sm-4 control-label">Date Received</label>
                            <div class="col-sm-8">
                                <div class="input-group date">
                                    <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                    <input type="text" id="datereceived" required class="form-control" value="{{ $sample->datereceived or '' }}" name="datereceived">
                                </div>
                            </div>                            
                        </div> 

                        <div class="form-group">
                            <label class="col-sm-4 control-label">PCR Type</label>
                            <div class="col-sm-8">
                                    <select class="form-control" required name="pcrtype">

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

                        <div class="form-group">
                            <label class="col-sm-4 control-label">Received Status</label>
                            <div class="col-sm-8">
                                    <select class="form-control" required name="receivedstatus">

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
                            <div class="col-sm-8"><textarea  class="form-control"></textarea></div>
                        </div>
                        <div class="form-group"><label class="col-sm-4 control-label">Lab Comments</label>
                            <div class="col-sm-8"><textarea  class="form-control"></textarea></div>
                        </div>
                    </div>
                </div>
                <div class="hr-line-dashed"></div>
                <div class="form-group">
                    <div class="col-sm-8 col-sm-offset-2">
                        <button class="btn btn-success" type="submit" name="submit_type" value="release">Save & Release sample</button>
                        <button class="btn btn-primary" type="submit" name="submit_type" value="add">Save & Add sample</button>
                        <button class="btn btn-danger" type="submit" name="submit_type" value="cancel">Cancel & Release</button>
                    </div>
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
                    lessThan: ["#datedispatched", "Date Collected", "Date of Dispatch"]
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
            startDate: new Date(),
            format: "yyyy-mm-dd"
        });

    @endcomponent



@endsection
