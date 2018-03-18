@extends('layouts.master')

@component('/forms/css')
    <link href="{{ asset('css/datapicker/datepicker3.css') }}" rel="stylesheet" type="text/css">
@endcomponent

@section('content')

    <div class="small-header">
        <div class="hpanel">
            <div class="panel-body">
                <h2 class="font-light m-b-xs">
                    Create Worksheet
                </h2>
            </div>
        </div>
    </div>


   <div class="content">
        <div>
            
        @if($create)

            <div class="row">
                <div class="col-lg-9 col-lg-offset-1">
                    <div class="hpanel">
                        <div class="panel-heading">
                            <center>Viralload Samples</center>
                        </div>
                        <div class="panel-body">
                            @include('shared/viralsamples-partial')
                        </div>
                    </div>
                </div>                
            </div>

            @if (isset($worksheet))
                {{ Form::open(['url' => '/viralworksheet/' . $worksheet->id, 'method' => 'put', 'class'=>'form-horizontal']) }}
            @else
                {{ Form::open(['url'=>'/viralworksheet', 'method' => 'post', 'class'=>'form-horizontal', 'id' => 'worksheets_form']) }}
            @endif

            <input type="hidden" value="{{ $machine_type }}" name="machine_type" >

            <div class="row">
                <div class="col-lg-9 col-lg-offset-1">
                    <div class="hpanel">
                        <div class="panel-heading">
                            <center>Worksheet Information</center>
                        </div>
                        <div class="panel-body">

                            @if($machine_type == 1)

                                <div class="form-group">
                                    <label class="col-sm-4 control-label">Lot No</label>
                                    <div class="col-sm-8">
                                        <input class="form-control" required name="lot_no" type="text" value="{{ $worksheet->lot_no or '' }}">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-sm-4 control-label">Date Cut</label>
                                    <div class="col-sm-8">
                                        <div class="input-group date date_cut">
                                            <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                            <input type="text" required class="form-control" value="{{ $worksheet->datecut or '' }}" name="datecut">
                                        </div>
                                    </div>                            
                                </div>
                                
                                <div class="form-group">
                                    <label class="col-sm-4 control-label">HIQCAP Kit No</label>
                                    <div class="col-sm-8">
                                        <input class="form-control" required name="hiqcap_no" type="text" value="{{ $worksheet->hiqcap_no or '' }}">
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label class="col-sm-4 control-label">Rack No</label>
                                    <div class="col-sm-8">
                                        <input class="form-control" required name="rack_no" type="text" value="{{ $worksheet->rack_no or '' }}">
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label class="col-sm-4 control-label">Spek Kit No</label>
                                    <div class="col-sm-8">
                                        <input class="form-control" required name="spekkit_no" type="text" value="{{ $worksheet->spekkit_no or '' }}">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-sm-4 control-label">KIT EXP</label>
                                    <div class="col-sm-8">
                                        <div class="input-group date date_exp">
                                            <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                            <input type="text" required class="form-control" value="{{ $worksheet->kitexpirydate or '' }}" name="kitexpirydate">
                                        </div>
                                    </div>                            
                                </div>

                            @else

                                <div class="form-group">
                                    <label class="col-sm-4 control-label">Sample Prep</label>
                                    <div class="col-sm-3">
                                        <input class="form-control" required name="sample_prep_lot_no" type="text" value="{{ $worksheet->sample_prep_lot_no or '' }}">
                                    </div>
                                    <div class="col-sm-5">
                                        <div class="input-group date date_exp">
                                            <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                            <input type="text" required class="form-control" value="{{ $worksheet->sampleprepexpirydate or '' }}" name="sampleprepexpirydate" placeholder="Expiry Date">
                                        </div>
                                    </div>                            
                                </div>

                                <div class="form-group">
                                    <label class="col-sm-4 control-label">Bulk Lysis Buffer</label>
                                    <div class="col-sm-3">
                                        <input class="form-control" required name="bulklysis_lot_no" type="text" value="{{ $worksheet->bulklysis_lot_no or '' }}">
                                    </div>
                                    <div class="col-sm-5">
                                        <div class="input-group date date_exp">
                                            <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                            <input type="text" required class="form-control" value="{{ $worksheet->bulklysisexpirydate or '' }}" name="bulklysisexpirydate" placeholder="Expiry Date">
                                        </div>
                                    </div>                            
                                </div>

                                <div class="form-group">
                                    <label class="col-sm-4 control-label">Control</label>
                                    <div class="col-sm-3">
                                        <input class="form-control" required name="control_lot_no" type="text" value="{{ $worksheet->control_lot_no or '' }}">
                                    </div>
                                    <div class="col-sm-5">
                                        <div class="input-group date date_exp">
                                            <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                            <input type="text" required class="form-control" value="{{ $worksheet->controlexpirydate or '' }}" name="controlexpirydate" placeholder="Expiry Date">
                                        </div>
                                    </div>                            
                                </div>

                                <div class="form-group">
                                    <label class="col-sm-4 control-label">Calibrator</label>
                                    <div class="col-sm-3">
                                        <input class="form-control" required name="calibrator_lot_no" type="text" value="{{ $worksheet->calibrator_lot_no or '' }}">
                                    </div>
                                    <div class="col-sm-5">
                                        <div class="input-group date date_exp">
                                            <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                            <input type="text" required class="form-control" value="{{ $worksheet->calibratorexpirydate or '' }}" name="calibratorexpirydate" placeholder="Expiry Date">
                                        </div>
                                    </div>                            
                                </div>

                                <div class="form-group">
                                    <label class="col-sm-4 control-label">Amplification Kit</label>
                                    <div class="col-sm-3">
                                        <input class="form-control" required name="amplification_kit_lot_no" type="text" value="{{ $worksheet->amplification_kit_lot_no or '' }}">
                                    </div>
                                    <div class="col-sm-5">
                                        <div class="input-group date date_exp">
                                            <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                            <input type="text" required class="form-control" value="{{ $worksheet->amplificationexpirydate or '' }}" name="amplificationexpirydate" placeholder="Expiry Date">
                                        </div>
                                    </div>                            
                                </div>

                            @endif

                            <div class="form-group">
                                <div class="col-sm-8 col-sm-offset-4">
                                    <button class="btn btn-success" type="submit">Save & Print Worksheet</button>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

            {{ Form::close() }}

        @else

            <p> There are only {{ $count }} samples. </p>

        @endif

        

      </div>
    </div>

@endsection

@section('scripts')

    @component('/forms/scripts')
        @slot('js_scripts')
            <script src="{{ asset('js/datapicker/bootstrap-datepicker.js') }}"></script>
        @endslot



        $(".date_cut").datepicker({
            startView: 0,
            todayBtn: "linked",
            keyboardNavigation: false,
            forceParse: true,
            autoclose: true,
            startDate: "-7d",
            endDate: "+0d",
            format: "yyyy-mm-dd"
        });

        $(".date_exp").datepicker({
            startView: 0,
            todayBtn: "linked",
            keyboardNavigation: false,
            forceParse: true,
            autoclose: true,
            startDate: "-5d",
            endDate: "+5y",
            format: "yyyy-mm-dd"
        });

    @endcomponent


    <script type="text/javascript">
        // $(document).ready(function(){
            
        // });
    </script>



@endsection
