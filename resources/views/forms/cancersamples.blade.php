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
            @if(isset($sample))
            <form action="{{ url('/cancersample/' . $sample->id) }}" class="form-horizontal" method="POST" id='samples_form'>
                @method('PUT')
            @else
            <form action="{{ url('/cancersample') }}" class="form-horizontal" method="POST" id='samples_form'>
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

                            <div class="form-group">
                                <label class="col-sm-4 control-label">Patient / Sample ID
                                    <strong><div style='color: #ff0000; display: inline;'>*</div></strong>
                                </label>
                                <div class="col-sm-8">
                                    <input class="form-control requirable" required name="patient" type="text" value="{{ $viralsample->patient->patient ?? '' }}" id="patient">
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
                                        <input type="text" id="dob" class="form-control lockable"
                                        @if(auth()->user()->user_type_id == 5)
                                            required
                                        @endif
                                         value="{{ $viralsample->patient->dob ?? '' }}" name="dob">
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
                                <label class="col-sm-4 control-label">Entry Point</label>
                                <div class="col-sm-8">
                                    <input class="form-control" name="entry_point" type="text" value="{{ $viralsample->patient->entry_point ?? '' }}">
                                </div>
                            </div>

                            <div class="hr-line-dashed"></div>
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
                            <div class="form-group">
                                <label class="col-sm-4 control-label">HIV Status
                                    <strong><div style='color: #ff0000; display: inline;'>*</div></strong>
                                </label>
                                <div class="col-sm-8">
                                    <select class="form-control lockable requirable" required name="hiv_status" id="hiv_status">

                                        <option></option>
                                        @foreach ($hivstatuses as $status)
                                            <option value="{{ $status->id }}"

                                            @if (isset($viralsample) && $viralsample->patient->hiv_status == $status->id)
                                                selected
                                            @endif

                                            > {{ $status->name }}
                                            </option>
                                        @endforeach


                                    </select>
                                </div>
                            </div>
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

                                            > {{ $justification->displaylabel }}
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
            </form>
        </div>
    </div>
@endsection