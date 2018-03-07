@extends('layouts.master')

    @component('/tables/css')
    @endcomponent

@section('content')

<div class="normalheader ">
    <div class="hpanel">
        <div class="panel-body">
            <a class="small-header-action" href="#">
                <div class="clip-header">
                    <i class="fa fa-arrow-up"></i>
                </div>
            </a>

            <div id="hbreadcrumb" class="pull-right m-t-lg">
                <ol class="hbreadcrumb breadcrumb">
                    <li>
                        <a href="index-2.html">Dashboard</a></li>
                    <li>
                        <span>Tables</span>
                    </li>
                    <li class="active">
                        <span>DataTables</span>
                    </li>
                </ol>
            </div>
            <h2 class="font-light m-b-xs">
                DataTables
            </h2>
            <small>Advanced interaction controls to any HTML table</small>
        </div>
    </div>
</div>
 
<div class="content">

    <div class="row">
        <div class="col-lg-12">
            <div class="hpanel">
                <div class="panel-heading">
                    Worksheet Summary
                    <div class="panel-tools">
                        <a class="showhide"><i class="fa fa-chevron-up"></i></a>
                        <a class="closebox"><i class="fa fa-times"></i></a>
                    </div>
                    
                </div>
                <div class="panel-body">

                    @include('shared/viral-abbot-header-partial')

                </div>
            </div>
        </div>        
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="hpanel">
                <div class="panel-heading">
                    <div class="panel-tools">
                        <a class="showhide"><i class="fa fa-chevron-up"></i></a>
                        <a class="closebox"><i class="fa fa-times"></i></a>
                    </div>
                    Standard table
                </div>
                <div class="panel-body">
                    <form  method="post" action="{{ url('viralworksheet/approve/' . $worksheet->id) }}  " name="worksheetform"  onSubmit="return confirm('Are you sure you want to approve the below test results as final results?');" >
                        {{ method_field('PUT') }} {{ csrf_field() }}

                        <table class="table table-striped table-bordered table-hover" >
                            <thead>
                                <tr class="colhead">
                                    <th>Sample ID</th>
                                    <th>Lab ID</th>
                                    <th>Run</th>
                                    <th>Result</th>                
                                    <th>Dilution Factor</th>                
                                    <th>Interpretation</th>                
                                    <th>Units</th>                
                                    <th>Action</th>                
                                    <th>Approved</th>                
                                    <th>Approved Date</th>                
                                    <th>Approved By</th>                
                                    <th>Task</th>                
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td >HPC</td>
                                    <td >-</td>
                                    <td >-</td>
                                    <td ><small><strong><font color='#FF0000'> {{ $worksheet->highpos_control_interpretation }} </font></strong></small> </td>
                                    <td >&nbsp; </td>
                                    <td ><small><strong>
                                        <font color='#FF0000'> {{ $worksheet->highpos_control_result }} </font>
                                         </strong></small>
                                     </td>
                                    <td >&nbsp; </td>
                                    <td >&nbsp; </td>
                                    <td >&nbsp; </td>
                                    <td >&nbsp; </td>   
                                    <td >&nbsp; </td>   
                                    <td >&nbsp; </td>   
                                </tr>
                                <tr>
                                    <td >LPC</td>
                                    <td >-</td>
                                    <td >-</td>
                                    <td ><small><strong><font color='#FF0000'> {{ $worksheet->lowpos_control_interpretation }} </font></strong></small> </td>
                                    <td >&nbsp; </td>
                                    <td ><small><strong>
                                        <font color='#FF0000'> {{ $worksheet->lowpos_control_result }} </font>
                                         </strong></small>
                                     </td>
                                    <td >&nbsp; </td>
                                    <td >&nbsp; </td>
                                    <td >&nbsp; </td>
                                    <td >&nbsp; </td>   
                                    <td >&nbsp; </td>   
                                    <td >&nbsp; </td>   
                                </tr>

                                <tr>
                                    <td >NC</td>
                                    <td >-</td>
                                    <td >-</td>
                                    <td ><small><strong><font color='#339900'> {{ $worksheet->neg_control_interpretation }} </font></strong></small> </td>
                                    <td >&nbsp; </td>
                                    <td ><small><strong>
                                        <font color='#339900'> {{ $worksheet->neg_control_result }} </font>
                                         </strong></small>
                                     </td>
                                    <td >&nbsp; </td>
                                    <td >&nbsp; </td>
                                    <td >&nbsp; </td>
                                    <td >&nbsp; </td>   
                                    <td >&nbsp; </td>   
                                    <td >&nbsp; </td>   
                                </tr>

                                @foreach($samples as $key => $sample)
                                    <tr>
                                        <td> 
                                            {{ $sample->patient->patient }}  
                                            <input type="hidden" name="samples[]" value="{{ $sample->id }} ">
                                            <input type="hidden" name="batches[]" value="{{ $sample->batch_id }}">
                                        </td>
                                        <td> {{ $sample->id }}  </td>
                                        <td> {{ $sample->run }} </td>
                                        <td> {{ $sample->interpretation }} </td>


                                        <td> 
                                            <select class="dilutionfactor" name="dilutiontype[]">
                                                @foreach($dilutions as $dilution)
                                                    <option value="{{$dilution->id}}"
                                                        @if($sample->dilutionfactor == $dilution->id)
                                                            selected
                                                        @endif
                                                        > {{ $dilution->dilutiontype }} </option>
                                                @endforeach
                                            </select>
                                        </td>

                                        <td> 
                                            @if($sample->approvedby)
                                                {{ $sample->result }}
                                            @else
                                                <div><label> <input type="checkbox" class="i-checks"  name="redraws[]" value="{{ $key }}"> Collect New Sample </label></div>
                                            @endif
                                        </td>

                                        <td></td>

                                        <td> 
                                            @if($sample->approvedby)
                                                @foreach($actions as $action)
                                                    @if($sample->repeatt == $action->id)
                                                        {{ $action->name }}
                                                    @endif
                                                @endforeach

                                            @else
                                                <select name="actions[]">
                                                    <option>Choose Action</option>
                                                    @foreach($actions as $action)
                                                        <option value="{{$action->id}}"
                                                            @if($sample->repeatt == $action->id)
                                                                selected
                                                            @endif
                                                            > {{ $action->name }} </option>
                                                    @endforeach
                                                </select>

                                            @endif
                                        </td>

                                        <td> <div align="center"><input name="approved[]" type="checkbox"  value="{{ $key }}" checked /></div> </td>
                                        <td> {{ $sample->dateapproved }} </td>
                                        <td> {{ $sample->approver->full_name or '' }} </td>
                                        <td> 
                                            <a href="{{ url('sample/' . $sample->id) }}" title='Click to view Details' target='_blank'> Details</a> | 
                                            <a href="{{ url('sample/runs/' . $sample->id) }}" title='Click to View Runs' target='_blank'>Runs </a>  
                                        </td>
                                    </tr>

                                @endforeach

                                @if($worksheet->status != 3)

                                    <tr bgcolor="#999999">
                                        <td  colspan="12" bgcolor="#00526C" >
                                            <center>
                                                <input type="submit" name="approve" value="Confirm & Approve Results" class="button"  />
                                            </center>
                                        </td>
                                    </tr>

                                @endif


                            </tbody>
                        </table>


                    </form>
                </div>
            </div>
        </div>
    </div>
</div>


@endsection

@section('scripts') 

    @component('/tables/scripts')
        $(".dilutionfactor").val(3).change();        
    @endcomponent

@endsection