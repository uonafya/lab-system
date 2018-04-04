@extends('layouts.master')

@component('/forms/css')
    <link href="{{ asset('css/datapicker/datepicker3.css') }}" rel="stylesheet" type="text/css">
@endcomponent

@section('content')

    <div class="row">

        @if (isset($sample))
            {{ Form::open(['url' => '/sample/' . $sample->id, 'method' => 'put', 'class'=>'val-form']) }}
        @else
            {{ Form::open(['url'=>'/sample', 'method' => 'post', 'class'=>'val-form']) }}
        @endif

        <div class="form-group">
            <label>Facility</label>
            <select class="form-control" required name="facility_id">

                <option value=""> Select One </option>
                @foreach ($facilities as $facility)
                    <option value="{{ $facility->id }}"

                    @if (isset($sample) && $sample->facility_id == $facility->id)
                        selected
                    @endif

                    > {{ $facility->name }}
                    </option>
                @endforeach

            </select>
        </div>


        <div class="form-group">
            <label>(*for Ampath Sites only) AMRS Location</label>
            <select class="form-control ampath-only" name="amrs_location_id">

                <option value=""> Select One </option>
                @foreach ($amrs_locations as $amrs_location)
                    <option value="{{ $amrs_location->id }}"

                    @if (isset($sample) && $sample->facility_id == $amrs_location->id)
                        selected
                    @endif

                    > {{ $amrs_location->name }}
                    </option>
                @endforeach

            </select>
        </div>

        <p> Infant Information </p>

        <div class="form-group">
            <label>Patient / Sample ID</label>
            <input class="form-control" required name="patient_id" type="text" value="{{ $sample->patient_id ?? '' }}">
        </div>

        <div class="form-group">
            <label>(*for Ampath Sites only) AMRS Provider Identifier</label>
            <input class="form-control ampath-only" required name="amrs_provider_id" type="text" value="{{ $sample->amrs_provider_id ?? '' }}">
        </div>

        <div class="form-group">
            <label>(*for Ampath Sites only) Patient Names</label>
            <input class="form-control ampath-only" required name="patient_name" type="text" value="{{ $sample->patient_name ?? '' }}">
        </div>

        <div class="form-group">
            <label>Sex</label>
            <select class="form-control" required name="gender_id">

                <option value=""> Select One </option>
                @foreach ($genders as $gender)
                    <option value="{{ $gender->id }}"

                    @if (isset($sample) && $sample->gender_id == $gender->id)
                        selected
                    @endif

                    > {{ $gender->gender }}
                    </option>
                @endforeach

            </select>
        </div>

        <div class="form-group">
            <label>Age</label>
            <input class="form-control" required name="sample_months" type="text" value="{{ $sample->sample_months ?? '' }}">
            <input class="form-control" required name="sample_weeks" type="text" value="{{ $sample->sample_weeks ?? '' }}">
        </div>

        <div class="form-group">
            <label>Infant Prophylaxis</label>
            <select class="form-control" required name="infant_prophylaxis_id">

                <option value=""> Select One </option>
                @foreach ($iprophylaxis as $ip)
                    <option value="{{ $gender->id }}"

                    @if (isset($sample) && $sample->infant_prophylaxis_id == $ip->id)
                        selected
                    @endif

                    > {{ $ip->prophylaxis }}
                    </option>
                @endforeach

            </select>
        </div>

        <p> Mother Information </p>

        <div class="form-group">
            <label>PMTCT Intervention</label>
            <select class="form-control" required name="intervention_id">

                <option value=""> Select One </option>
                @foreach ($interventions as $intervention)
                    <option value="{{ $gender->id }}"

                    @if (isset($sample) && $sample->intervention_id == $intervention->id)
                        selected
                    @endif

                    > {{ $intervention->intervention }}
                    </option>
                @endforeach

            </select>
        </div>

        <div class="form-group">
            <label>Feeding Types</label>
            <select class="form-control" required name="feeding_id">

                <option value=""> Select One </option>
                @foreach ($interventions as $intervention)
                    <option value="{{ $gender->id }}"

                    @if (isset($sample) && $sample->feeding_id == $intervention->id)
                        selected
                    @endif

                    > {{ $intervention->prophylaxis }}
                    </option>
                @endforeach

            </select>
        </div>

        <div class="form-group">
            <label>sample Type</label>
            <select class="form-control" required name="sample_type_id">

                <option value=""> Select an option </option>
                @foreach ($sample_types as $sample_type)
                    <option value="{{ $sample_type->id }}"

                    @if (isset($sample) && $sample->sample_type_id == $sample_type->id)
                        selected
                    @endif

                    > {{ $sample_type->sample_type }}
                    </option>
                @endforeach

            </select>
        </div>



        <button type="submit" class="btn btn-primary">Submit</button>

        {{ Form::close() }}

    </div>
@endsection

@section('scripts')

    @component('/forms/scripts')
        @slot('js_scripts')
            <script src="{{ asset('js/datapicker/bootstrap-datepicker.js') }}"></script>
        @endslot



        $(".date").datepicker({
            startView: 0,
            todayBtn: "linked",
            keyboardNavigation: false,
            forceParse: true,
            autoclose: true,
            startDate: new Date(),
            format: "yyyy-mm-dd"
        });

    @endcomponent



@endsection
