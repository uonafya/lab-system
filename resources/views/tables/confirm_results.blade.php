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
                    <div class="panel-tools">
                        <a class="showhide"><i class="fa fa-chevron-up"></i></a>
                        <a class="closebox"><i class="fa fa-times"></i></a>
                    </div>
                    Standard table
                </div>
                <div class="panel-body">
                    <form  method="post" action="{{ url('confirm_results') }}  " name="worksheetform"  onSubmit="return confirm('Are you sure you want to approve the below test results as final results?');" >
                        {{ method_field('PUT') }} {{ csrf_field() }}

                        <table class="table table-striped table-bordered table-hover data-table" >
                            <thead>
                                <tr>
                                    <th>Sample ID</th>
                                    <th>Lab ID</th>
                                    <th>Run</th>
                                    <th>Result</th>                
                                    <th>Interpretation</th>                
                                    <th>Action</th>                
                                    <th>Approved</th>                
                                    <th>Approved Date</th>                
                                    <th>Approved By</th>                
                                    <th>Task</th>                
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td >LPC</td>
                                    <td >-</td>
                                    <td >-</td>
                                    <td ><small><strong>
                                        <font color='#FF0000'> {{ $worksheet->pos_control_result }} </font>
                                         </strong></small>
                                     </td>
                                    <td ><small><strong><font color='#FF0000'> {{ $worksheet->pos_control_interpretation }} </font></strong></small> </td>
                                    <td >Control </td>
                                    <td >&nbsp; </td>
                                    <td >&nbsp; </td>
                                    <td >&nbsp; </td>
                                    <td >&nbsp; </td>   
                                </tr>

                                <tr>
                                    <td >NC</td>
                                    <td >-</td>
                                    <td >-</td>
                                    <td ><small><strong>
                                        <font color='#339900'> {{ $worksheet->neg_control_result }} </font>
                                         </strong></small>
                                     </td>
                                    <td ><small><strong><font color='#339900'> {{ $worksheet->neg_control_interpretation }} </font></strong></small> </td>
                                    <td >Control </td>
                                    <td >&nbsp; </td>
                                    <td >&nbsp; </td>
                                    <td >&nbsp; </td>
                                    <td >&nbsp; </td>   
                                </tr>

                                @foreach($samples as $key => $sample)
                                    <tr>
                                        <td> {{ $sample->patient->patient }}  </td>
                                        <td> {{ $sample->id }}  </td>
                                        <td> {{ $sample->run }} </td>
                                        <td> {{ $sample->interpretation }} </td>
                                        <td> 
                                            @if($sample->approvedby)
                                                @foreach($results as $result)
                                                    @if($sample->result == $result->id)
                                                        {{ $result->name }}
                                                    @endif
                                                @endforeach

                                            @else
                                                <select name="action[]">
                                                    @foreach($results as $result)
                                                        <option value="{{$result->id}}"
                                                            @if($sample->result == $result->id)
                                                                selected
                                                            @endif
                                                            > {{ $result->name }} </option>
                                                    @endforeach
                                                </select>

                                            @endif
                                        </td>

                                        <td> 
                                            @if($sample->approvedby)
                                                @foreach($actions as $action)
                                                    @if($sample->repeatt == $action->id)
                                                        {{ $action->name }}
                                                    @endif
                                                @endforeach

                                            @else
                                                <select name="action[]">
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
                                        <td> {{ $sample->approver->full_name }} </td>
                                        <td> {{  }} </td>
                                    </tr>

                                @endforeach


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

    @endcomponent

@endsection