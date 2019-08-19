@extends('layouts.master')

    @component('/tables/css')
        <link href="{{ asset('css/datapicker/datepicker3.css') }}" rel="stylesheet" type="text/css">
    @endcomponent

@section('content')

 
<div class="content">

    <div class="row">
        <div class="col-md-12">
            Click To View:
            <a href="{{ $myurl2 }}"> all samples</a> | 
            @foreach($dr_sample_statuses as $dr_sample_status)
                <a href="{{ $myurl2 . '/' . $dr_sample_status->id }}"> {{ $dr_sample_status->name }} samples</a> | 
            @endforeach
        </div>
    </div>

    <br />
    <br />

    <div class="row">
        <div class="col-md-4"> 
            <div class="form-group">
                <label class="col-sm-2 control-label">Select Date</label>
                <div class="col-sm-8">
                    <div class="input-group date">
                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                        <input type="text" id="filter_date" required class="form-control">
                    </div>
                </div> 

                <div class="col-sm-2">                
                    <button class="btn btn-primary" id="submit_date">Filter</button>  
                </div>                         
            </div> 
        </div>

        <div class="col-md-8"> 
            <div class="form-group">

                <label class="col-sm-1 control-label">From:</label>
                <div class="col-sm-4">
                    <div class="input-group date">
                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                        <input type="text" id="from_date" required class="form-control">
                    </div>
                </div> 

                <label class="col-sm-1 control-label">To:</label>
                <div class="col-sm-4">
                    <div class="input-group date">
                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                        <input type="text" id="to_date" required class="form-control">
                    </div>
                </div> 

                <div class="col-sm-2">                
                    <button class="btn btn-primary" id="date_range">Filter</button>  
                </div>                         
            </div> 

        </div>
    </div>

    <br />
    <br />

    {{ Form::open(['url' => 'dr_sample/index', 'method' => 'post', 'class' => 'my_form']) }}

        <div class="row">

            <div class="alert alert-success">
                <center>
                    Select facility and/or partner and/or subcounty. <br />
                    If you wish to get for a particular day, set only the From field. Set the To field also to get for a date range. <br />
                    Click on filter to get the list of batches based on selected criteria. <br />
                    The Download As Excel depends on all the selected criteria.
                </center>
            </div>
            
            <br />

            <div class="col-md-4"> 
                <div class="form-group">
                    <label class="col-sm-3 control-label">Select Facility</label>
                    <div class="col-sm-9">
                        <select class="form-control" name="facility_id" id="facility_id">
                            <option></option>
                            @if(isset($facility) && $facility)
                                <option value="{{ $facility->id }}" selected>{{ $facility->facilitycode }} {{ $facility->name }}</option>
                            @endif
                        </select>
                    </div>                        
                </div> 
            </div>
            <div class="col-md-4"> 
                <div class="form-group">
                    <label class="col-sm-3 control-label">Select Subcounty</label>
                    <div class="col-sm-9">
                        <select class="form-control" name="subcounty_id" id="subcounty_id">
                            <option></option>
                            @foreach ($subcounties as $subcounty)
                                <option value="{{ $subcounty->id }}"

                                @if (isset($subcounty_id) && $subcounty_id == $subcounty->id)
                                    selected
                                @endif

                                > {{ $subcounty->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>                        
                </div> 
            </div>
            <div class="col-md-4"> 
                <div class="form-group">
                    <label class="col-sm-3 control-label">Select Partner</label>
                    <div class="col-sm-9">
                        <select class="form-control" name="partner_id" id="partner_id">
                            <option></option>
                            @foreach ($partners as $partner)
                                <option value="{{ $partner->id }}"

                                @if (isset($partner_id) && $partner_id == $partner->id)
                                    selected
                                @endif

                                > {{ $partner->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>                        
                </div> 
            </div>
        </div>


        <div class="row">

            <div class="col-md-9"> 
                <div class="form-group">

                    <label class="col-sm-1 control-label">From:</label>
                    <div class="col-sm-4">
                        <div class="input-group date">
                            <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                            <input type="text" id="from_date" name="from_date" class="form-control">
                        </div>
                    </div> 

                    <label class="col-sm-1 control-label">To:</label>
                    <div class="col-sm-4">
                        <div class="input-group date">
                            <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                            <input type="text" id="to_date" name="to_date" class="form-control">
                        </div>
                    </div> 

                    <div class="col-sm-2">                
                        <button class="btn btn-primary" id="date_range" name="submit_type" value="date_range" type='submit'>Filter</button>  
                    </div>                         
                </div> 
            </div>

            <div class="col-md-3">
                <div class="form-group">              
                    <button class="btn btn-primary" name="submit_type" value="excel" type='submit'>Download Susceptability Report</button> 
                </div>                
            </div>
        </div>

        <br />
        <br />

    {{ Form::close() }}

    <div class="row">
        <div class="col-lg-12">
            <div class="hpanel">
                <div class="panel-heading">
                    <div class="panel-tools">
                        <a class="showhide"><i class="fa fa-chevron-up"></i></a>
                        <!-- <a class="closebox"><i class="fa fa-times"></i></a> -->
                    </div>
                    Drug Resistance Samples
                </div>
                <div class="panel-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered table-hover" >
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Sample Code / Patient ID</th>
                                    <th>Facility</th>
                                    <th>Lab ID</th>
                                    <th>Exatype Status</th>
                                    <th>Date Collected</th>
                                    <th>Date Received</th>
                                    <th>Reason</th>
                                    <!-- <th>Extraction Worksheet</th> -->
                                    <th>Sequencing Worksheet</th>
                                    <th>Has Errors</th>
                                    <th>Has Warnings</th>
                                    <th>Has Mutations</th>
                                    <th>Tasks</th>
                                </tr>
                            </thead>
                            <tbody> 
                                @foreach($dr_samples as $key => $sample)
                                    <tr>
                                        <td> {{ $key+1 }} </td>
                                        <td> {!! $sample->patient->hyper_link ?? '' !!} </td>
                                        <td> {{ $sample->patient->facility->name ?? '' }} </td>
                                        <td> {{ $sample->id }} </td>
                                        <td> {!! $dr_sample_statuses->where('id', $sample->status_id)->first()->output ?? '' !!} </td>
                                        <td> {{ $sample->datecollected }} </td>
                                        <td> {{ $sample->datereceived }} </td>
                                        <td> {{ $drug_resistance_reasons->where('id', $sample->dr_reason_id)->first()->name ?? '' }} </td>
                                        <!-- <td> {!! $sample->get_link('extraction_worksheet_id') !!} </td> -->
                                        <td> {!! $sample->get_link('worksheet_id') !!} </td>
                                        <td> {{ $sample->my_boolean_format('has_errors') }} </td>
                                        <td> {{ $sample->my_boolean_format('has_warnings') }} </td>
                                        <td> {{ $sample->my_boolean_format('has_mutations') }} </td>
                                        <td>
                                            <a href="{{ url('dr_sample/' . $sample->id) }}" target="_blank"> View Details </a> | 
                                            <a href="{{ url('dr_sample/' . $sample->id . '/edit') }}" target="_blank"> Edit </a> | 

                                            @if(!$sample->datereceived && auth()->user()->user_type_id <= 1)
                                                <a href="{{ url('dr_sample/' . $sample->id . '/edit') }}" target="_blank"> Verify Sample </a> | 
                                            @endif

                                            @if(in_array($sample->status_id, [1, 2, 3]))
                                                <a href="{{ url('dr_sample/results/' . $sample->id ) }}" target="_blank"> View Results </a> | 
                                                <a href="{{ url('dr_sample/download_results/' . $sample->id) }}"> Download </a> | 
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach


                            </tbody>
                        </table>
                    </div>

                    {{ $dr_samples->links() }}
                </div>
            </div>
        </div>
    </div>
</div>


@endsection

@section('scripts') 

    @component('/tables/scripts')
        @slot('js_scripts')
            <script src="{{ asset('js/datapicker/bootstrap-datepicker.js') }}"></script>
        @endslot

    @endcomponent

    <script type="text/javascript">
        
        $(document).ready(function(){
            localStorage.setItem("base_url", "{{ $myurl ?? '' }}/");
            
            set_select_facility("facility_id", "{{ url('/facility/search') }}", 3, "Search for facility", false);

            // $("#check_all").on('click', function(){
            //     var str = $(this).html();
            //     if(str == "Check All"){
            //         $(this).html("Uncheck All");
            //         $(".checks").prop('checked', true);
            //     }
            //     else{
            //         $(this).html("Check All");
            //         $(".checks").prop('checked', false);           
            //     }
            // });

            $(".date").datepicker({
                startView: 0,
                todayBtn: "linked",
                keyboardNavigation: false,
                forceParse: true,
                autoclose: true,
                format: "yyyy-mm-dd"
            });

            $('#submit_date').click(function(){
                var d = $('#filter_date').val();
                window.location.href = localStorage.getItem('base_url') + d;
            });

            $('#date_range').click(function(){
                var from = $('#from_date').val();
                var to = $('#to_date').val();
                window.location.href = localStorage.getItem('base_url') + from + '/' + to;
            });
        });
    </script>

@endsection