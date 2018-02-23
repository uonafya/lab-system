@extends('layouts.master')

@component('/forms/css')
        <link href="{{ asset('css/jasny/jasny-bootstrap.min.css') }}" rel="stylesheet" type="text/css">
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

        {{ Form::open(['url'=>'/worksheet/upload/' . $worksheet->id, 'method' => 'put', 'class'=>'form-horizontal', 'files' => true]) }}

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
                                <input class="form-control" required type="text" value="{{ $worksheet->id or '' }}" disabled>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-4 control-label">Date Created</label>
                            <div class="col-sm-8">
                                <input class="form-control" required type="text" value="{{ $worksheet->created_at or '' }}" disabled>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-4 control-label">Created By</label>
                            <div class="col-sm-8">
                                <input class="form-control" required type="text" value="{{ $worksheet->creator->full_name or '' }}" disabled>
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
                            <p>The file must be an excel file eg {{ $worksheet->id }}.xlsx </p>


                            <div class="fileinput fileinput-new input-group" data-provides="fileinput">
                                <div class="form-control" data-trigger="fileinput">
                                    <i class="glyphicon glyphicon-file fileinput-exists"></i> 
                                    <span class="fileinput-filename"></span>
                                </div>
                                <span class="input-group-addon btn btn-default btn-file">
                                    <span class="fileinput-new">Select Excel</span>
                                    <span class="fileinput-exists">Change</span>
                                    <input id="upload" type="file" accept="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" name="upload" >
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
                                    <input id="upload" type="file" accept=".csv" name="upload" >
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

        {{ Form::close() }}

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
