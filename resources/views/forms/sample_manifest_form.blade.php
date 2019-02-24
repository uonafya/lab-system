@extends('layouts.master')

@component('/forms/css')
    <link href="{{ asset('css/datapicker/datepicker3.css') }}" rel="stylesheet" type="text/css">
@endcomponent

@section('content')

    <div class="row">

    {{ Form::open(['url'=>'/sample', 'method' => 'post', 'class'=>'val-form']) }}
    
        <div class="form-group">
            <label>Facility Sending Samples</label>
            <select class="form-control requirable" required name="facility_id" id="facility_id"></select>
        </div>

        <div class="form-group">
            <div class="row">
                <div class="col-md-6">
                    <label class="col-sm-4 control-label">From Date:
                        <strong><div style='color: #ff0000; display: inline;'>*</div></strong>
                    </label>
                    <div class="col-sm-8">
                        <div class="input-group date date-normal">
                            <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                            <input type="text" id="datecollected" required class="form-control requirable" value="" name="from">
                        </div>
                    </div> 
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <label class="col-sm-4 control-label">To Date:
                        <strong><div style='color: #ff0000; display: inline;'>*</div></strong>
                    </label>
                    <div class="col-sm-8">
                        <div class="input-group date date-normal">
                            <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                            <input type="text" id="to" required class="form-control requirable" value="" name="to">
                        </div>
                    </div> 
                </div>
            </div>                           
        </div>

        <button type="submit" class="btn btn-primary">Generate Facility Manifest</button>

    {{ Form::close() }}

    </div>
@endsection

@section('scripts')

    @component('/forms/scripts')
        @slot('js_scripts')
            <script src="{{ asset('js/datapicker/bootstrap-datepicker.js') }}"></script>
        @endslot
        
        
        @slot('val_rules')
           ,
            rules: {
                from: {
                    lessThan: ["#to", "From Date", "To Date"]
                }                               
            }
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
        set_select_facility("facility_id", "{{ url('/facility/search') }}", 3, "Search for facility", false);
    @endcomponent



@endsection
