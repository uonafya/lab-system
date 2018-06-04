@extends('layouts.master')

@component('/forms/css')
    
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
            <div class="row">
                <div class="col-lg-12">
                {{ Form::open(['url' => '/facility', 'method' => 'post', 'class'=>'form-horizontal']) }}
                    <div class="hpanel">
                        <div class="panel-heading" style="padding-bottom: 2px;padding-top: 4px;">
                            <center>Facility Information</center>
                        </div>
                        <div class="panel-body" style="padding-bottom: 6px;">
                            <div class="form-group">
                                <label class="col-sm-4 control-label">Facility Name</label>
                                <div class="col-sm-8">
                                    <input class="form-control" name="name" id="name" type="text" value="{{ $facility->name ?? '' }}" >
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-4 control-label">MFL Code</label>
                                <div class="col-sm-8">
                                    <input class="form-control" name="facilitycode" id="facilitycode" type="text" value="{{ $facility->facilitycode ?? '' }}" >
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-4 control-label">DHIS Code</label>
                                <div class="col-sm-8">
                                    <input class="form-control" name="DHIScode" id="DHIScode" type="text" value="{{ $facility->DHIScode ?? '' }}" >
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-4 control-label">Facility Type</label>
                                <div class="col-sm-8">
                                    <select class="form-control" required name="ftype" id="ftype">
                                        <option value="" selected disabled>Select Facility Type</option>
                                    @foreach ($facilitytype as $ftype)
                                        <option value="{{ $ftype->name }}">{{ $ftype->name }}</option>
                                    @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-4 control-label">Ward</label>
                                <div class="col-sm-8">
                                    <select class="form-control" required name="wardid" id="wardid">
                                        <option value="" selected disabled>Select Ward</option>
                                    @foreach ($wards as $ward)
                                        <option value="{{ $ward->id }}">{{ $ward->name }}</option>
                                    @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-4 control-label">Districts</label>
                                <div class="col-sm-8">
                                    <select class="form-control" required name="district" id="district">
                                        <option value="" selected disabled>Select District</option>
                                    @foreach ($districts as $district)
                                        <option value="{{ $district->id }}">{{ $district->name }}</option>
                                    @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-4 control-label">Partners</label>
                                <div class="col-sm-8">
                                    <select class="form-control" required name="partner" id="partner">
                                        <option value="" selected disabled>Select Partner</option>
                                    @foreach ($partners as $partner)
                                        <option value="{{ $partner->id }}">{{ $partner->name }}</option>
                                    @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="col-sm-4 control-label">Longitude</label>
                                    <div class="col-sm-8">
                                        <input class="form-control" name="longitude" id="longitude" type="text" value="{{ $facility->longitude ?? '' }}" >
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="col-sm-4 control-label">Latitude</label>
                                    <div class="col-sm-8">
                                        <input class="form-control" name="latitude" id="latitude" type="text" value="{{ $facility->latitude ?? '' }}" >
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-4 control-label">Burden</label>
                                <div class="col-sm-8">
                                    <input class="form-control" name="burden" id="burden" type="text" value="{{ $facility->burden ?? '' }}" >
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-4 control-label">Art Patients</label>
                                <div class="col-sm-8">
                                    <input class="form-control" name="artpatients" id="artpatients" type="text" value="{{ $facility->artpatients ?? '' }}" >
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-4 control-label">PMTCT Nos.</label>
                                <div class="col-sm-8">
                                    <input class="form-control" name="pmtctnos" id="pmtctnos" type="text" value="{{ $facility->pmtctnos ?? '' }}" >
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-4 control-label">Male Less than 15</label>
                                <div class="col-sm-8">
                                    <input class="form-control" name="Mless15" id="Mless15" type="text" value="{{ $facility->Mless15 ?? '' }}" >
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-4 control-label">Male Greater than 15</label>
                                <div class="col-sm-8">
                                    <input class="form-control" name="Mmore15" id="Mmore15" type="text" value="{{ $facility->Mmore15 ?? '' }}" >
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-4 control-label">Female Less than 15</label>
                                <div class="col-sm-8">
                                    <input class="form-control" name="Fless15" id="Fless15" type="text" value="{{ $facility->Fless15 ?? '' }}" >
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-4 control-label">Female Greater than 15</label>
                                <div class="col-sm-8">
                                    <input class="form-control" name="Fmore15" id="Fmore15" type="text" value="{{ $facility->Fmore15 ?? '' }}" >
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-4 control-label">Total ART Patients</label>
                                <div class="col-sm-8">
                                    <input class="form-control" name="totalart" id="totalart" type="text" value="{{ $facility->totalart ?? '' }}" >
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-4 control-label">Date</label>
                                <div class="col-sm-8">
                                    <div class="input-group date">
                                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                        <input type="text" id="asofdate" required class="form-control lockable" value="{{ $facility->asofdate ?? '' }}" name="asofdate">
                                    </div>
                                </div>                            
                            </div>
                            <div class="form-group">
                                <label class="col-sm-4 control-label">Telephone</label>
                                <div class="col-sm-8">
                                    <input class="form-control" name="telephone" id="telephone" type="text" value="{{ $facility->telephone ?? '' }}" >
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-4 control-label">Telephone 2</label>
                                <div class="col-sm-8">
                                    <input class="form-control" name="telephone2" id="telephone2" type="text" value="{{ $facility->telephone2 ?? '' }}" >
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-4 control-label">Fax</label>
                                <div class="col-sm-8">
                                    <input class="form-control" name="fax" id="fax" type="text" value="{{ $facility->fax ?? '' }}" >
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-4 control-label">Email</label>
                                <div class="col-sm-8">
                                    <input class="form-control" name="email" id="email" type="email" value="{{ $facility->email ?? '' }}" >
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-4 control-label">Postal Address</label>
                                <div class="col-sm-8">
                                    <input class="form-control" name="PostalAddress" id="PostalAddress" type="text" value="{{ $facility->PostalAddress ?? '' }}" >
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-4 control-label">Contact Person</label>
                                <div class="col-sm-8">
                                    <input class="form-control" name="contactperson" id="contactperson" type="text" value="{{ $facility->contactperson ?? '' }}" >
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-4 control-label">ART Site</label>
                                <div class="col-sm-8">
                                    <select class="form-control" name="ART" id="ART">
                                        <option value="Y">YES</option>
                                        <option value="N">NO</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-4 control-label">PMTCT Site</label>
                                <div class="col-sm-8">
                                    <select class="form-control" name="PMTCT" id="PMTCT">
                                        <option value="Y">YES</option>
                                        <option value="N">NO</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="hr-line-dashed"></div>
                    <div class="form-group">
                        <center>
                            <div class="col-sm-10 col-sm-offset-1">
                                <button class="btn btn-success submit" type="submit" name="submit_type" value="paswordreset">Save User</button>
                                <button class="btn btn-danger" type="reset" formnovalidate name="submit_type" value="cancel">Reset</button>
                            </div>
                        </center>
                    </div>
                {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')

    @component('/forms/scripts')
        @slot('js_scripts')
            
        @endslot

        @slot('val_rules')
           
        @endslot

    @endcomponent


    <script type="text/javascript">
        $(document).ready(function(){
            $(".submit").click(function(e){
                password = $("#password").val();
                confirm = $("#confirm-password").val();
                if (password !== confirm) {
                    e.preventDefault();
                    set_warning("Passwords do not match");
                    $("#confirm-password").val("");
                    $("#confirm-password").focus();
                }
            });
        });
    </script>
@endsection
