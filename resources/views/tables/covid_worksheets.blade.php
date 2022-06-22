@extends('layouts.master')

@component('/tables/css')
    <link href="{{ asset('css/datapicker/datepicker3.css') }}" rel="stylesheet" type="text/css">
@endcomponent

@section('content')
 
<div class="content">
    <div class="row">
        <div class="col-md-12">
            Click To View: 
            <a href="{{ url($link_extra . 'worksheet/index/0') }}" title="All Worksheets">
                All Worksheets
            </a> |
            <a href="{{ url($link_extra . 'worksheet/index/1') }}" title="In-Process Worksheets">
                In-Process Worksheets
            </a> |
            <a href="{{ url($link_extra . 'worksheet/index/12') }}" title="In-Process Worksheets">
                In-Process Worksheets (With Reruns)
            </a> |
            <a href="{{ url($link_extra . 'worksheet/index/2') }}" title="Tested Worksheets">
                Tested Worksheets
            </a> |
            <a href="{{ url($link_extra . 'worksheet/index/3') }}" title="Approved Worksheets">
                Approved Worksheets
            </a> |
            <a href="{{ url($link_extra . 'worksheet/index/4') }}" title="Cancelled Worksheets">
                Cancelled Worksheets
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

    {{--<div class="row">
        @foreach($status_count as $c)
            <div class="col-sm-3">
                <b>{!! $worksheet_statuses->where('id', $c->status_id)->first()->state ?? '' !!}: </b> {{ $c->total }}                
            </div>
        @endforeach        
    </div>--}}

    @foreach($worksheet_statuses as $worksheet_status)
        @continue(!$status_count->where('status_id', $worksheet_status->id)->sum('total'))
        <div class="row">
            <div class="col-sm-2">
                <b>{{ $worksheet_status->state }}:</b> {{ $status_count->where('status_id', $worksheet_status->id)->sum('total') }}
            </div>

            @foreach($status_count->where('status_id', $worksheet_status->id) as $mach)
                <div class="col-sm-2">
                    <b>{!! $machines->where('id', $mach->machine_type)->first()->machine ?? '' !!}</b> : {{ $mach->total }}
                </div>
            @endforeach
        </div>
    @endforeach
    
    <div class="row">
        <div class="col-lg-12">
            <div class="hpanel">
                <div class="panel-heading">
                    <div class="panel-tools">
                        <a class="showhide"><i class="fa fa-chevron-up"></i></a>
                        <!-- <a class="closebox"><i class="fa fa-times"></i></a> -->
                    </div>
                    Worksheets
                </div>
                <div class="panel-body">
                    <table class="table table-striped table-bordered table-hover" >
                        <thead>
                            <tr class="colhead">
                                <th rowspan="2">W No</th>
                                <th rowspan="2">Date Created</th>
                                <th rowspan="2">Created By</th>
                                <th rowspan="2">Type</th>
                                <th rowspan="2">Status</th>
                                <th colspan="7">Samples</th>
                                <th colspan="3">Date</th>
                                <th rowspan="2">Combined</th>
                                <th rowspan="2">Task</th>
                            </tr>
                            <tr>
                                <th>POS</th>
                                <th>Presumed POS</th>
                                <th>NEG</th>
                                <th>Failed</th>
                                <th>Redraw</th>
                                <th>No Result</th>
                                <th>Total</th>
                                <th>Run</th>
                                <th>Updated</th>
                                <th>Reviewed</th>                
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($worksheets as $key => $worksheet)
                                <tr>
                                    <td>{{ $worksheet->id }} </td>
                                    <td> {{ $worksheet->my_date_format('created_at') }} </td>
                                    <td> {{ $worksheet->creator->full_name ?? '' }} </td>
                                    <td> {!! $worksheet->machine !!} </td>
                                    <td> {!! $worksheet->status !!} </td>
                                    <td> {{ $worksheet->pos }} </td>
                                    <td> {{ $worksheet->presumed_pos }} </td>
                                    <td> {{ $worksheet->neg }} </td>
                                    <td> {{ $worksheet->failed_samples }} </td>
                                    <td> {{ $worksheet->redraw }} </td>
                                    <td> {{ $worksheet->noresult }} </td>
                                    <td> {{ $worksheet->sample_count }}
                                        @if($worksheet->rerun)
                                            <span style="color: #ff0000;"> ({{ $worksheet->rerun }}) </span>
                                        @endif
                                    </td>
                                    <td> {{ $worksheet->my_date_format('daterun') }} </td>
                                    <td> {{ $worksheet->my_date_format('dateuploaded') }} </td>
                                    <td> {{ $worksheet->my_date_format('datereviewed') }} </td>
                                    <td> 
                                        @if($worksheet->combined == 1)
                                            <b>EID ({{ $worksheet->other_samples()->count() }})</b>
                                        @elseif($worksheet->combined == 2)
                                            <b>Viralload ({{ $worksheet->other_samples()->count() }})</b>
                                        @endif 
                                    </td>
                                    <td> 
                                        @include('shared.worksheet_links', ['worksheet' => $worksheet])

                                        <!-- $worksheet->mylinks  -->
                                    </td>
                                </tr>
                            @endforeach

                            @php
                                // echo $rows;
                            @endphp 
                        </tbody>
                    </table>

                    {{ $worksheets->links() }} 
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
            localStorage.setItem("base_url", "{{ $myurl }}/");

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