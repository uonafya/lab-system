@extends('layouts.master')

@component('/tables/css')
    <link href="{{ asset('css/datapicker/datepicker3.css') }}" rel="stylesheet" type="text/css">
@endcomponent

@section('content')

<div class="content">
    <div class="row">
        <div class="col-md-12">
            Click To View: 
            <a href="{{ url('viralworksheet/index/0') }}" title="All Worksheets">
                All Worksheets
            </a> |
            <a href="{{ url('viralworksheet/index/1') }}" title="In-Process Worksheets">
                In-Process Worksheets
            </a> |
            <a href="{{ url('viralworksheet/index/2') }}" title="Tested Worksheets">
                Tested Worksheets
            </a> |
            <a href="{{ url('viralworksheet/index/3') }}" title="Approved Worksheets">
                Approved Worksheets
            </a> |
            <a href="{{ url('viralworksheet/index/4') }}" title="Cancelled Worksheets">
                Cancelled Worksheets
            </a>
            @if(env('APP_LAB') == 9)
                |
                <a href="{{ url('viralworksheet/index/11') }}" title="EMR (IQCare) Worksheets">
                    EMR (IQCare) Worksheets
                </a>
            @endif
        </div>
    </div>

    <br />

    <div class="row">
        <div class="col-md-12">
            Click To View: 
            <a href="{{ $myurl2 . 1 }}" title="Taqman Worksheets"> Taqman Worksheets </a> |
            <a href="{{ $myurl2 . 2 }}" title="Abbott Worksheets"> Abbott Worksheets </a> |
            <a href="{{ $myurl2 . 3 }}" title="C8800 Worksheets"> C8800 Worksheets </a> |
            <a href="{{ $myurl2 . 4 }}" title="Pantha Worksheets"> Pantha Worksheets </a> |
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
                    <b>{!! $machines->where('id', $mach->machine_type)->first()->machine !!}</b> : {{ $mach->total }}
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
                            <tr>
                                    <th> W No </th>
                                    <th> Date Created </th>
                                    <th> Created By </th>
                                    <th> Type </th>
                                    <th> Status </th>
                                    <th> # Detected </th>
                                    <th> # Undetected </th>
                                    <th> # Failed </th>
                                    <th> # No Result </th>
                                    <th> # Samples </th>
                                    <th> Date Run </th>
                                    <th> Date Updated </th>
                                    <th> Date Reviewed </th>
                                    <th> Task </th>                 
                            </tr>
                        </thead>
                        <tbody>

                            @foreach($worksheets as $key => $worksheet)
                                <tr>
                                    <td>{{ $worksheet->id }} </td>
                                    <td> {{ $worksheet->my_date_format('created_at') }} </td>
                                    <td> {{ $worksheet->surname . ' ' . $worksheet->oname }} </td>

                                    {{--<td> {!! $machines->where('machine', $worksheet->machine_type)->first()['string'] !!} </td>
                                    <td> {!! $statuses->where('status', $worksheet->status_id)->first()['string'] !!} </td>--}}

                                    <td> {!! $machines->where('id', $worksheet->machine_type)->first()->output !!} </td>


                                    <td> {!! $worksheet_statuses->where('id', $worksheet->status_id)->first()->output !!} </td>

                                    <td> {{ $detected->where('worksheet_id', $worksheet->id)->first()->totals ?? 0 }} </td>
                                    <td> {{ $undetected->where('worksheet_id', $worksheet->id)->first()->totals ?? 0 }} </td>
                                    <td> {{ $failed->where('worksheet_id', $worksheet->id)->first()->totals ?? 0 }} </td>
                                    <td> {{ $noresult->where('worksheet_id', $worksheet->id)->first()->totals ?? 0 }} </td>

                                    <td> {{ $worksheet->samples_no }} 
                                        @if($reruns->where('worksheet_id', $worksheet->id)->first())
                                            <span style="color: #ff0000;"> ({{ $reruns->where('worksheet_id', $worksheet->id)->first()->totals }}) </span>
                                        @endif
                                    </td>
                                    <td> {{ $worksheet->my_date_format('daterun') }} </td>
                                    <td> {{ $worksheet->my_date_format('dateuploaded') }} </td>
                                    <td> {{ $worksheet->my_date_format('datereviewed') }} </td>
                                    <td> 
                                        @include('shared.viral_links', ['worksheet_id' => $worksheet->id, 'worksheet_status' => $worksheet->status_id, 'machine_type' => $worksheet->machine_type, 'worksheet' => $worksheet])
                                    </td>
                                </tr>
                            @endforeach
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