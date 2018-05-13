@extends('layouts.master')

@component('/tables/css')
    <link href="{{ asset('css/datapicker/datepicker3.css') }}" rel="stylesheet" type="text/css">
@endcomponent

@section('content')

<div class="content">

    @empty($site_approval)

        <div class="row">
            <div class="col-md-12">
                Click To View: 
                {{--<a href="{{ url($pre . 'batch/index') }}">--}}
                <a href="{{ $myurl }}">
                    All Batches
                </a> |
                <a href="{{ $myurl2 }}/0">
                    In-Process Batches
                </a> |
                <a href="{{ $myurl2 }}/2">
                    Awaiting Dispatch
                </a> |
                <a href="{{ $myurl2 }}/1">
                    Dispatched Batches
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

    @endempty
    
    <div class="row">
        <div class="col-lg-12">
            <div class="hpanel">
                <div class="panel-heading">
                    <div class="panel-tools">
                        <a class="showhide"><i class="fa fa-chevron-up"></i></a>
                        <a class="closebox"><i class="fa fa-times"></i></a>
                    </div>
                    Batches table
                </div>
                <div class="panel-body">
                    <div class="table-responsive">
                        <form  method="post" action="{{ url($pre . 'batch/summaries/') }}  " >
                            {{ csrf_field() }}
                            <table class="table table-striped table-bordered table-hover" >
                                <thead>
                                    <tr class="colhead">
                                        <th rowspan="2">Batch No</th>

                                        @if(isset($batch_complete) && $batch_complete == 1)
                                            <th rowspan="2">Print Multiple</th>
                                        @endif

                                        <th rowspan="2">Facility</th>
                                        <th colspan="2">Date</th>
                                        <th rowspan="2">Entered By</th>
                                        <th colspan="4"># of samples</th>

                                        @if(isset($batch_complete) && $batch_complete == 1)
                                            <th rowspan="1">Date</th>
                                        @endif

                                        <th rowspan="1">TAT</th>
                                        <th rowspan="2">Status</th>
                                        <th rowspan="2">Task</th>
                                    </tr>
                                    <tr>
                                        <th>Received</th>
                                        <th>Entered</th>
                                        <th>Received</th>
                                        <th>Rejected</th>
                                        <th>Results</th>
                                        <th>No Result</th>

                                        @if(isset($batch_complete) && $batch_complete == 1)
                                            <th>Dispatched</th>
                                        @endif

                                        <th>(Dys)</th>            
                                    </tr>
                                </thead>
                                <tbody>

                                    @foreach($batches as $batch)
                                        <tr>
                                            <td> {{ $batch->id }} </td>

                                            @if(isset($batch_complete) && $batch_complete == 1)
                                                <td> <div align="center"><input name="batch_ids[]" type="checkbox"  value="{{ $batch->id }}"  /></div> </td>
                                            @endif

                                            <td> {{ $batch->name }} </td>
                                            <td> {{ $batch->datereceived }} </td> 
                                            <td> {{ $batch->datecreated }} </td>
                                            <td> {{ $batch->creator }} </td>
                                            <td> {{ $batch->total }} </td>
                                            <td> {{ $batch->rejected }} </td>
                                            <td> {{ $batch->result }} </td>
                                            <td> {{ $batch->noresult }} </td>

                                            @if(isset($batch_complete) && $batch_complete == 1)
                                                <td> {{ $batch->datedispatched }} </td>
                                            @endif
                                            

                                            <td> {{ $batch->tat() }} </td>
                                            <td> 
                                                @if($batch->batch_complete)
                                                    Complete
                                                @else
                                                    In-Process
                                                @endif
                                            </td>
                                            <td> 
                                                @if($batch->approval)
                                                    <a href="{{ url($pre . 'batch/site_approval/' . $batch->id) }}">View Samples For Approval</a>
                                                @else
                                                    <a href="{{ url($pre . 'batch/' . $batch->id) }}">View</a>

                                                    @if($batch->batch_complete == 1)
                                                        | <a href="{{ url($pre . 'batch/summary/' . $batch->id) }}" target="_blank"><i class='fa fa-print'></i> Summary</a> 
                                                        | <a href="{{ url($pre . 'batch/individual/' . $batch->id) }}" target="_blank"><i class='fa fa-print'></i> Individual </a> 
                                                        | <a href="{{ url($pre . 'batch/email/' . $batch->id) }}"><i class='fa fa-print'></i> Email </a>
                                                    @endif

                                                @endif
                                            </td>
                                        </tr>


                                    @endforeach

                                    @if(isset($batch_complete) && $batch_complete == 1)
                                        <tr>
                                            <td colspan="13"> 
                                                <center>
                                                    <input type="submit" name="Print" value="Print the Selected Batches" class="button"  />
                                                </center>
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </form>
                    </div>

                    {{-- {!!  $links !!} --}}

                    {{ $batches->links() }}
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