@extends('layouts.master')

@component('/tables/css')
    <link href="{{ asset('css/datapicker/datepicker3.css') }}" rel="stylesheet" type="text/css">
@endcomponent

@section('content')

<div class="content">

    @isset($quarantine_sites)
    <div class="row">
        <div class="col-md-12">
            Click To View: 
            <a href="{{ $myurl2 }}">
                All Samples
            </a> |
            <a href="{{ $myurl2 }}/2">
                Dispatched Samples
            </a> |
            <a href="{{ $myurl2 }}/0">
                Samples Pending Receipt at the Lab
            </a>
        </div>
    </div>

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

    @if(auth()->user()->user_type_id != 5)

        <form action="{{ url('covid_sample/index') }}" method="POST" class="my_form">
            @csrf

            @isset($to_print)
                <input type="hidden" name="to_print" value="1">
            @endisset

            <input type="hidden" name="type" value="{{ $type }}">

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

                <div class="col-md-6"> 
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
                <div class="col-md-6"> 
                    <div class="form-group">
                        <label class="col-sm-3 control-label">Select Quarantine Site</label>
                        <div class="col-sm-9">
                            <select class="form-control" name="quarantine_site_id" id="quarantine_site_id">
                                <option></option>
                                @foreach ($quarantine_sites as $quarantine_site)
                                    <option value="{{ $quarantine_site->id }}"

                                    @if (isset($quarantine_site_id) && $quarantine_site_id == $quarantine_site->id)
                                        selected
                                    @endif

                                    > {{ $quarantine_site->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>                        
                    </div> 
                </div>

            <br />

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
                    <!-- <div class="form-group">              
                        <button class="btn btn-primary" name="submit_type" value="excel" type='submit'>Download as Excel</button> 
                    </div>   -->              
                </div>
            </div>

        </form>

    @endif

    @endisset
    
    <div class="row">
        <div class="col-lg-12">
            <div class="hpanel">
                <div class="panel-heading">
                    Covid-19 Samples
                </div>
                <div class="panel-body">
                    <div class="table-responsive">
                        @if(isset($type) && $type == 2)
                        <form  method="post" action="{{ url('covid_sample/print_multiple/') }}" onsubmit="return confirm('Are you sure you want to delete the selected batches?');">
                            @csrf
                        @endif

                        <table class="table table-striped table-bordered table-hover @empty($quarantine_sites) data-table @endempty " >
                            <thead>
                                <tr class="colhead">
                                    <th rowspan="2">Lab ID</th>
                                    <th rowspan="2">Facility</th>
                                    <th rowspan="2">Identifier</th>
                                    <th rowspan="2">Worksheet</th>
                                    <th colspan="4">Date</th>
                                    <th rowspan="2">Entered By</th>
                                    <th rowspan="2">Received By</th>
                                    <th rowspan="2">Received</th>
                                    <th rowspan="2">Results</th>                                    
                                    <th rowspan="2">Task</th>
                                    @if(isset($type) && $type == 2)
                                        <th rowspan="2">Print Multiple</th>
                                    @else
                                        <th rowspan="2">Delete</th>
                                    @endif
                                </tr>
                                <tr>
                                    <th>Collected</th>
                                    <th>Received</th>
                                    <th>Tested</th>
                                    <th>Dispatched</th>    
                                </tr>
                            </thead>
                            <tbody>

                                @foreach($samples as $sample)
                                    @continue($sample->repeatt == 1 && auth()->user()->is_facility())
                                    <tr>
                                        <td> {{ $sample->id }} </td>
                                        <td> {{ $sample->facilityname }} </td>
                                        <td> {{ $sample->identifier }} </td>
                                        <td> {!! $sample->get_link('worksheet_id') !!} </td>
                                        <td> {{ $sample->my_date_format('datecollected') }} </td>
                                        <td> {{ $sample->my_date_format('datereceived') }} </td>
                                        <td> {{ $sample->my_date_format('datetested') }} </td>
                                        <td> {{ $sample->my_date_format('datedispatched') }} </td>
                                        @if($sample->surname == '' || !$sample->surname)
                                            <td> {{ $sample->entered_by }} </td>
                                        @else
                                            <td> {{ $sample->surname . ' ' . $sample->oname }} </td>
                                        @endif

                                        <td> {{ $sample->rsurname . ' ' . $sample->roname }} </td>

                                        <td> 
                                            @if($sample->receivedstatus == 1)
                                                Received
                                            @elseif($sample->receivedstatus == 2)
                                                Rejected
                                            @endif
                                        </td>

                                        <td> {!! $sample->get_prop_name($results, 'result', 'name_colour')  !!}</td>
                                        <td>
                                            {!! $sample->edit_link !!}  |
                                            @if($sample->datedispatched)
                                                <a href="/covid_sample/result/{{ $sample->id }}">Result</a> |
                                            @endif                                         
                                        </td>
                                        @if(isset($type) && $type == 2)
                                            <td> 
                                                <div align="center">
                                                    <input name="sample_ids[]" type="checkbox" class="checks" value="{{ $sample->id }}"  />
                                                </div>
                                            </td>
                                        @else
                                            <td> {!! $sample->delete_form !!} </td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        @if(isset($type) && $type == 2)
                        <button type="submit">Print Multiple Samples</button>
                        </form>
                        @endif

                    </div>

                    @isset($quarantine_sites)
                        {{ $samples->links() }}
                    @endisset
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

            $("#check_all").on('click', function(){
                var str = $(this).html();
                if(str == "Check All"){
                    $(this).html("Uncheck All");
                    $(".checks").prop('checked', true);
                }
                else{
                    $(this).html("Check All");
                    $(".checks").prop('checked', false);           
                }
            });

        });
        
    </script>

@endsection