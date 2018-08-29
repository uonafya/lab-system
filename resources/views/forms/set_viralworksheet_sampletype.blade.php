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

        {{ Form::open(['url' => url('patient/' . $patient->id), 'method' => 'put', 'class'=>'form-horizontal', 'confirm_message' => 'Are you sure you would like to update this patient?']) }}

        <div class="row">
            <div class="col-lg-8 col-lg-offset-2">
                <div class="hpanel">
                    <div class="panel-heading">
                        <center> </center>
                    </div>
                    <div class="panel-body">

                        <div class="form-group">
                            <label class="col-sm-4 control-label">Sex</label>
                            <div class="col-sm-8">
                                <select class="form-control lockable" required name="sex" id="sex">
                                    <option value=""> Select One </option>
                                    @foreach ($genders as $gender)
                                        <option value="{{ $gender->id }}"

                                        @if (isset($patient) && $patient->sex == $gender->id)
                                            selected
                                        @endif

                                        > {{ $gender->gender_description }}
                                        </option>
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
