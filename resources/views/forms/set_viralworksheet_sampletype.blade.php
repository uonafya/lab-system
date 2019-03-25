@extends('layouts.master')

@component('/forms/css')
@endcomponent

@section('content')

    <div class="small-header">
        <div class="hpanel">
            <div class="panel-body">
                <h2 class="font-light m-b-xs">
                    Select Sample Type
                </h2>
            </div>
        </div>
    </div>

   <div class="content">
        <div>

        {{ Form::open(['url' => url('viralworksheet/set_sampletype'), 'method' => 'post', 'class'=>'form-horizontal']) }}

        <div class="row">
            <div class="col-lg-8 col-lg-offset-2">
                <div class="hpanel">
                    <div class="panel-heading">
                        <center> </center>
                    </div>
                    <div class="panel-body">
                        <input type="hidden" name="machine_type" value="{{ $machine_type }}">
                        @if($limit)
                            <input type="hidden" name="limit" value="{{ $limit }}">
                        @endif

                        @if($calibration)
                            <input type="hidden" name="calibration" value="{{ $calibration }}">

                            <div class="form-group">
                                <label class="col-sm-4 control-label">Calibration Sample Number</label>
                                <div class="col-sm-8">
                                    <select class="form-control lockable" required name="limit" id="limit">
                                        <option></option>
                                        <option value="13">24</option>
                                        <option value="37">48</option>
                                        <option value="61">72</option>
                                        <option value="85">96</option>
                                    </select>
                                </div>
                            </div>

                        @endif

                        <div class="form-group">
                            <label class="col-sm-4 control-label">Worksheet Sample Type
                                <strong><div style='color: #ff0000; display: inline;'>*</div></strong>
                           </label>
                            <div class="col-sm-8">
                                <select class="form-control lockable" required name="sampletype" id="sampletype">
                                    <option></option>
                                    @foreach ($worksheet_sampletypes as $worksheet_sampletype)
                                        <option value="{{ $worksheet_sampletype->id }}"> {{ $worksheet_sampletype->name }} </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        @if(env('APP_LAB') != 1)

                            <div class="form-group">
                                <label class="col-sm-4 control-label">Samples Entered By/Received By</label>
                                <div class="col-sm-8">
                                    {{--<select class="form-control" name="entered_by" id="entered_by">                                    
                                        <option value=""> Select One </option>
                                        @foreach ($users as $user)
                                            <option value="{{ $user->id }}">
                                             {{ $user->full_name }}
                                            </option>
                                        @endforeach
                                    </select>--}}

                                    @foreach ($users as $user)
                                        <label> 
                                            <input name="entered_by[]" type="checkbox" class="i-checks" value="{{ $user->id }}" />
                                            {{ $user->full_name }}
                                        </label>
                                    @endforeach

                                </div>
                            </div>

                        @endif

                        <div class="hr-line-dashed"></div>




                        <div class="form-group">
                            <div class="col-sm-8 col-sm-offset-4">
                                <button class="btn btn-success" type="submit">Set Sample Type</button>
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

    @endcomponent

@endsection
