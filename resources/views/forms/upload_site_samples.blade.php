@extends('layouts.master')

@component('/forms/css')
        <link href="{{ asset('css/jasny/jasny-bootstrap.min.css') }}" rel="stylesheet" type="text/css">
@endcomponent

@section('content')

    <div class="small-header">
        <div class="hpanel">
            <div class="panel-body">
                <h2 class="font-light m-b-xs">
                    Upload Facility Samples
                </h2>
            </div>
        </div>
    </div>



   <div class="content">
        <div>


        <form method="POST" action="{{ url($url . '/upload') }}" accept-charset="UTF-8" class="form-horizontal" enctype="multipart/form-data">
            @csrf

        <div class="row">
            <div class="col-lg-8 col-lg-offset-2">
                <div class="hpanel">
                    <div class="panel-body">

                        @if(Str::contains($url, ['reed', 'ampath']))

                        <div class="alert alert-warning">
                            <center>
                                The file must be a csv or excel file. <br />
                                The first row serves as the column header and is necessary for a successful upload. The columns must be named as below, spaces included. <br />
                                <b> Required Columns </b> <br />
                                MFL Code <br />
                                Identifier<br />
                                Patient Name<br />
                                Gender<br />
                                Age<br />
                                <b> Optional Columns </b> <br />
                                Phone Number<br />
                                National ID<br />
                                Occupation<br />
                                County<br />
                                Subcounty<br />
                                Date Collected<br />
                                Date Received<br />
                                (By default any date missing date will be filled with the current day. Date must be filled in the YYYY-MM-DD format e.g. 2020-07-15)

                            </center>
                        </div>

                        @elseif(Str::contains($url, ['wrp']))

                        <div class="alert alert-warning">
                            <center>
                                The file must be a csv or excel file.
                            </center>
                        </div>

                        @else

                        <div class="alert alert-warning">
                            <center>
                                The file must be a csv file.
                            </center>
                        </div>

                        @endif
                        <br />


                        <div class="fileinput fileinput-new input-group" data-provides="fileinput">
                            <div class="form-control" data-trigger="fileinput">
                                <i class="glyphicon glyphicon-file fileinput-exists"></i> 
                                <span class="fileinput-filename"></span>
                            </div>
                            @if(Str::contains($url, ['wrp', 'reed', 'ampath']))
                            <span class="input-group-addon btn btn-default btn-file">
                                <span class="fileinput-new">Select Excel/CSV</span>
                                <span class="fileinput-exists">Change</span>
                                <input id="upload" type="file" required accept=".xlsx, .xls, .csv" name="upload" >
                            </span>
                            @else
                            <span class="input-group-addon btn btn-default btn-file">
                                <span class="fileinput-new">Select CSV</span>
                                <span class="fileinput-exists">Change</span>
                                <input id="upload" type="file" required accept=".csv" name="upload" >
                            </span>
                            @endif
                            <a href="#" class="input-group-addon btn btn-default fileinput-exists" data-dismiss="fileinput">Remove</a>
                        </div>



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
        @endslot
    @endcomponent


@endsection
