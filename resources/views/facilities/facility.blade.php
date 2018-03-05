@extends('layouts.master')

    @component('/tables/css')
    @endcomponent

@section('content')

<div class="p-lg">
    <div class="content animate-panel" data-child="hpanel">
    {{ Form::open(['url' => '/facility/', 'method' => 'put', 'class'=>'form-horizontal']) }}
        <div class="row">
            <div class="col-lg-5">
                <div class="hpanel">
                    <div class="panel-heading" style="padding-bottom: 2px;padding-top: 4px;">
                        <center>Facility Information</center>
                    </div>
                    <div class="panel-body" style="padding-bottom: 6px;">
                        <div class="form-group">
                            <label class="col-sm-4 control-label">Code</label>
                            <div class="col-sm-8">
                                <input class="form-control" required name="facilitycode" type="text" value="{{ $facility->facilitycode }}" id="facilitycode" disabled>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-4 control-label">Name</label>
                            <div class="col-sm-8">
                                <input class="form-control" required name="name" type="text" value="{{ $facility->facility }}" id="name" disabled="true">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-4 control-label">Sub County</label>
                            <div class="col-sm-8">
                                <input class="form-control" required name="subconty" type="text" value="{{ $facility->subcounty }}" id="subconty" disabled="true">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-4 control-label">County</label>
                            <div class="col-sm-8">
                                <input class="form-control" required name="county" type="text" value="{{ $facility->county }}" id="county" disabled="true">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-4 control-label">Laboratory</label>
                            <div class="col-sm-8">
                                <input class="form-control" required name="lab" type="text" value="{{ $facility->lab }}" id="lab" disabled="true">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="hpanel">
                    <div class="panel-heading" style="padding-bottom: 2px;padding-top: 4px;">
                        <center>Facility Contact Details</center>
                    </div>
                    <div class="panel-body" style="padding-bottom: 6px;">
                        <div class="form-group">
                            <label class="col-sm-4 control-label">Postal Address</label>
                            <div class="col-sm-8">
                                <input class="form-control" required name="PostalAddress" type="text" value="{{ $facility->PostalAddress }}" id="PostalAddress" disabled="true">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-4 control-label">Physical Address</label>
                            <div class="col-sm-8">
                                <input class="form-control" required name="physicaladdress" type="text" value="{{ $facility->physicaladdress }}" id="physicaladdress" disabled="true">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="col-sm-4 control-label">Telephone 1</label>
                                    <div class="col-sm-8">
                                        <input class="form-control" required name="telephone" type="text" value="{{ $facility->telephone }}" id="telephone" disabled="true">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-4 control-label">Fax</label>
                                    <div class="col-sm-8">
                                        <input class="form-control" required name="fax" type="text" value="{{ $facility->fax }}" id="fax" disabled="true">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="col-sm-4 control-label">Telephone 2</label>
                                    <div class="col-sm-8">
                                        <input class="form-control" required name="telephone2" type="text" value="{{ $facility->telephone2 }}" id="telephone2" disabled="true">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-4 control-label">Email</label>
                                    <div class="col-sm-8">
                                        <input class="form-control" required name="email" type="email" value="{{ $facility->email }}" id="email" disabled="true">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-4 control-label">SMS Printer No.</label>
                            <div class="col-sm-8">
                                <input class="form-control" required name="smsprinterphoneno" type="text" value="{{ $facility->smsprinterphoneno }}" id="smsprinterphoneno" disabled="true">
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
                        <center>Facility Contact Person</center>
                    </div>
                    <div class="panel-body" style="padding-bottom: 6px;">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="col-sm-4 control-label">Full Name(s)</label>
                                    <div class="col-sm-8">
                                        <input class="form-control" required name="contactperson" type="text" value="{{ $facility->contactperson }}" id="contactperson" disabled="true">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-4 control-label">Contact Tel. No. 1</label>
                                    <div class="col-sm-8">
                                        <input class="form-control" required name="contacttelephone" type="text" value="{{ $facility->contacttelephone }}" id="contacttelephone" disabled="true">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="col-sm-4 control-label">Contact Email Address</label>
                                    <div class="col-sm-8">
                                        <input class="form-control" required name="ContactEmail" type="text" value="{{ $facility->ContactEmail }}" id="ContactEmail" disabled="true">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-4 control-label">Contact Tel No. 2</label>
                                    <div class="col-sm-8">
                                        <input class="form-control" required name="contacttelephone2" type="text" value="{{ $facility->contacttelephone2 }}" id="contacttelephone2" disabled="true">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row" style="display: none;">
            <div class="col-lg-12">
                <div class="hpanel">
                    <div class="panel-heading" style="padding-bottom: 2px;padding-top: 4px;">
                        <center>Facility G4S Details</center>
                    </div>
                    <div class="panel-body" style="padding-bottom: 6px;">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="col-sm-4 control-label">G4S Branch Name</label>
                                    <div class="col-sm-8">
                                        <input class="form-control" required name="contactperson" type="text" value="{{ $facility->contactperson }}" id="contactperson" disabled="true">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-4 control-label">G4S Phone No. 1</label>
                                    <div class="col-sm-8">
                                        <input class="form-control" required name="contacttelephone" type="text" value="{{ $facility->contacttelephone }}" id="contacttelephone" disabled="true">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-4 control-label">G4S Phone No. 3</label>
                                    <div class="col-sm-8">
                                        <input class="form-control" required name="contacttelephone" type="text" value="{{ $facility->contacttelephone }}" id="contacttelephone" disabled="true">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="col-sm-4 control-label">G4S Location</label>
                                    <div class="col-sm-8">
                                        <input class="form-control" required name="ContactEmail" type="text" value="{{ $facility->ContactEmail }}" id="ContactEmail" disabled="true">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-4 control-label">G4S Phone No. 2</label>
                                    <div class="col-sm-8">
                                        <input class="form-control" required name="contacttelephone2" type="text" value="{{ $facility->contacttelephone2 }}" id="contacttelephone2" disabled="true">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-4 control-label">G4S Fax</label>
                                    <div class="col-sm-8">
                                        <input class="form-control" required name="contacttelephone" type="text" value="{{ $facility->contacttelephone }}" id="contacttelephone" disabled="true">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="hr-line-dashed"></div>
            <div class="form-group">
                <div class="col-sm-8 col-sm-offset-5">
                    <button class="btn btn-success">Edit</button>
                    <a href="{{ route('facility.index') }}" class="btn btn-primary">Go Back</a>
                </div>
            </div>
        </div>
    {{ Form::close() }}
    </div>
</div>
@endsection

@section('scripts') 

    @component('/tables/scripts')

    @endcomponent

@endsection