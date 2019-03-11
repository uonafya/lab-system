@extends('layouts.master')

@component('/forms/css')
    <link href="{{ asset('css/jasny/jasny-bootstrap.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('css/datapicker/datepicker3.css') }}" rel="stylesheet" type="text/css">
@endcomponent

@section('content')

    <div class="small-header">
        <div class="hpanel">
            <div class="panel-body">
                <h2 class="font-light m-b-xs">
                    Update Results for Worksheet No {{ $worksheet->id }}
                </h2>
            </div>
        </div>
    </div>



   <div class="content">
        <div>

        @if(isset($upload_errors) && is_array($upload_errors))
            The following errors 
            @foreach($upload_errors as $error)
                {{ $error }} <br />
            @endforeach
        @endif

        {{ Form::open(['url'=>'/dr_worksheet/upload/' . $worksheet->id, 'method' => 'put', 'class'=>'form-horizontal', 'files' => true]) }}

        <input type="hidden" value="{{ auth()->user()->id }}" name="uploadedby">
        <input type="hidden" value="{{ date('Y-m-d') }}" name="dateuploaded">
        <input type="hidden" value="2" name="status_id">

        <input type="hidden" value="{{ $worksheet->my_date_format('created_at', 'Y-m-d') }}" id="datecreated">


        <div class="row">
            <div class="col-lg-8 col-lg-offset-2">
                <div class="hpanel">
                    <div class="panel-heading">
                        <center>Worksheet Information</center>
                    </div>
                    <div class="panel-body">

                        <div class="form-group">
                            <label class="col-sm-4 control-label">Worksheet No</label>
                            <div class="col-sm-8">
                                <input class="form-control" required type="text" value="{{ $worksheet->id ?? '' }}" disabled>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-4 control-label">Date Created</label>
                            <div class="col-sm-8">
                                <input class="form-control" required type="text" value="{{ $worksheet->my_date_format('created_at') ?? '' }}" disabled>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-4 control-label">Created By</label>
                            <div class="col-sm-8">
                                <input class="form-control" required type="text" value="{{ $worksheet->creator->full_name ?? '' }}" disabled>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-4 control-label">Date of Testing
                                <strong><div style='color: #ff0000; display: inline;'>*</div></strong>
                            </label>
                            <div class="col-sm-8">
                                <div class="input-group date">
                                    <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                    <input type="text" id="daterun" class="form-control" name="daterun">
                                </div>
                            </div>                            
                        </div>

                        <div class="hr-line-dashed"></div>

                        <p>The file must be an zip file eg {{ $worksheet->id }}.zip </p>


                        <div class="fileinput fileinput-new input-group" data-provides="fileinput">
                            <div class="form-control" data-trigger="fileinput">
                                <i class="glyphicon glyphicon-file fileinput-exists"></i> 
                                <span class="fileinput-filename"></span>
                            </div>
                            <span class="input-group-addon btn btn-default btn-file">
                                <span class="fileinput-new">Select ZIP</span>
                                <span class="fileinput-exists">Change</span>
                                <input id="upload" type="file" required accept=".zip" name="upload" >
                            </span>
                            <a href="#" class="input-group-addon btn btn-default fileinput-exists" data-dismiss="fileinput">Remove</a>
                        </div>


                        <div class="hr-line-dashed"></div>

                        <div class="form-group">
                            <div class="col-sm-8 col-sm-offset-4">
                                <button class="btn btn-success" type="submit">Submit</button>
                            </div>
                        </div>


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
            <script src="{{ asset('js/jasny/jasny-bootstrap.min.js') }}"></script>
            <script src="{{ asset('js/datapicker/bootstrap-datepicker.js') }}"></script>
        @endslot

        @slot('val_rules')
           ,
            rules: {
                daterun: {
                    greaterThan: ["#datecreated", "Date of Testing", "Date Created"]
                }                               
            }
        @endslot

        $(".date").datepicker({
            startView: 0,
            todayBtn: "linked",
            keyboardNavigation: false,
            forceParse: true,
            autoclose: true,
            startDate: '-5d',
            endDate: new Date(),
            format: "yyyy-mm-dd"
        });
    @endcomponent


@endsection
