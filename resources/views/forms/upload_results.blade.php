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
            
        <div class="row">
            <div class="col-lg-12">
                <div class="hpanel">
                    <div class="panel-body" style="padding-bottom: 6px;">
                        <div class="alert alert-warning">
                            <center>
                                If the worksheet contains duplicate results (the same lab id with more than one result) the system will automatically download an excel with the duplicate results. Please download the file and see the lab id which has repeated itself in the results excel. Fix the results excel and then upload the fixed results excel.
                            </center>
                        </div>
                    </div>
                </div>
            </div>                
        </div>

        @if($worksheet->route_name == 'covid_worksheet' && $worksheet->machine_type == 0)
            
            <div class="row">
                <div class="col-lg-12">
                    <div class="hpanel">
                        <div class="panel-body" style="padding-bottom: 6px;">
                            <div class="alert alert-warning">
                                <center>
                                    Acceptable results 
                                </center>
                            </div>
                        </div>
                    </div>
                </div>                
            </div>

        @endif

        @if($worksheet->status_id == 4)
            
            <div class="row">
                <div class="col-lg-12">
                    <div class="hpanel">
                        <div class="panel-body" style="padding-bottom: 6px;">
                            <div class="alert alert-warning">
                                <center>
                                    This is the page for cancelled worksheet <b>{{ $worksheet->id }}.</b> By uploading a worksheet here, all sample ids that appear in the upload will be transferred to this worksheet and the results will be updated. Please be careful when making the upload.  
                                </center>
                            </div>
                        </div>
                    </div>
                </div>                
            </div>

        @endif

        <form method="POST" action="{{ url($worksheet->route_name . '/upload/' . $worksheet->id) }}" class="form-horizontal" enctype="multipart/form-data">

            @csrf
            @method('PUT')

            <input type="hidden" value="{{ auth()->user()->id }}" name="uploadedby">
            <input type="hidden" value="{{ date('Y-m-d') }}" name="dateuploaded">
            <input type="hidden" value="2" name="status_id">


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
                                <label class="col-sm-4 control-label">Sorted By</label>
                                <div class="col-sm-8"><select class="form-control" required name="sortedby" id="sortedby">

                                    <option value=""> Select One </option>
                                    @foreach ($users as $user)
                                        <option value="{{ $user->id }}">
                                         {{ $user->full_name }}
                                        </option>
                                    @endforeach

                                </select></div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-4 control-label">Run By</label>
                                <div class="col-sm-8"><select class="form-control" name="runby" id="runby">
                                    
                                    <option value=""> Select One </option>
                                    @foreach ($users as $user)
                                        <option value="{{ $user->id }}">
                                         {{ $user->full_name }}
                                        </option>
                                    @endforeach

                                </select></div>
                            </div>

                            <div class="hr-line-dashed"></div>

                            @if($worksheet->machine_type == 2)

                                <div class="form-group">
                                    <label class="col-sm-4 control-label">Date of Testing (if not set, the default is today)</label>
                                    <div class="col-sm-8">
                                        <div class="input-group date">
                                            <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                            <input type="text" id="daterun" class="form-control" name="daterun">
                                        </div>
                                    </div>                            
                                </div>

                                <p>The file must be an excel file eg {{ $worksheet->id }}.xlsx </p>


                                <div class="fileinput fileinput-new input-group" data-provides="fileinput">
                                    <div class="form-control" data-trigger="fileinput">
                                        <i class="glyphicon glyphicon-file fileinput-exists"></i> 
                                        <span class="fileinput-filename"></span>
                                    </div>
                                    <span class="input-group-addon btn btn-default btn-file">
                                        <span class="fileinput-new">Select Excel</span>
                                        <span class="fileinput-exists">Change</span>
                                        <input id="upload" type="file" required accept=".xlsx, .xls, .csv" name="upload" >
                                    </span>
                                    <a href="#" class="input-group-addon btn btn-default fileinput-exists" data-dismiss="fileinput">Remove</a>
                                </div>


                            @else
                                <p>The file must be a csv file eg {{ $worksheet->id }}.csv </p>


                                <div class="fileinput fileinput-new input-group" data-provides="fileinput">
                                    <div class="form-control" data-trigger="fileinput">
                                        <i class="glyphicon glyphicon-file fileinput-exists"></i> 
                                        <span class="fileinput-filename"></span>
                                    </div>
                                    <span class="input-group-addon btn btn-default btn-file">
                                        <span class="fileinput-new">Select CSV</span>
                                        <span class="fileinput-exists">Change</span>
                                        <input id="upload" type="file" required accept=".csv" name="upload" >
                                    </span>
                                    <a href="#" class="input-group-addon btn btn-default fileinput-exists" data-dismiss="fileinput">Remove</a>
                                </div>



                            @endif


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

        </form>

      </div>
    </div>

@endsection

@section('scripts')

    @component('/forms/scripts')
        @slot('js_scripts')
            <script src="{{ asset('js/jasny/jasny-bootstrap.min.js') }}"></script>
            <script src="{{ asset('js/datapicker/bootstrap-datepicker.js') }}"></script>
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
