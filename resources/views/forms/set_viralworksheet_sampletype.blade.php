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
                        @if($calibration)
                            <input type="hidden" name="calibration" value="{{ $calibration }}">
                        @endif

                        <div class="form-group">
                            <label class="col-sm-4 control-label">Worksheet Sample Type</label>
                            <div class="col-sm-8">
                                <select class="form-control lockable" required name="sampletype" id="sampletype">
                                    <option value=""> Select One </option>
                                    @foreach ($worksheet_sampletypes as $worksheet_sampletype)
                                        <option value="{{ $worksheet_sampletype->id }}"> {{ $worksheet_sampletype->name }} </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

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
