@extends('layouts.master')

    @component('/tables/css')
    @endcomponent

@section('content')

<div class="content">

    <div class="row">
        <div class="col-lg-12">
            <div class="hpanel">
                <div class="panel-heading" style="margin-bottom: 0.5em;">
                    Worksheet Summary
                                        
                    <div class="panel-tools">
                        <a href="{{ url('/viralworksheet/cancel_upload/' . $worksheet->id) }} ">
                            <button class="btn btn-danger">Cancel Upload</button>
                        </a>
                    </div>
                    
                </div>
                <div class="panel-body">                    

                    @if($worksheet->machine_type == 1)
                        @include('shared/viral-other-header-partial')
                    @else
                        @include('shared/viral-abbot-header-partial')
                    @endif


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
                        <!-- <a class="closebox"><i class="fa fa-times"></i></a> -->
                    </div>
                    Confirm Results
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
                                    <th></th>                 
                                    <th>Action</th>               
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
                                </tr>

                                @php
                                    if($worksheet->status_id == 3){
                                        $class = 'noneditable';
                                        $editable = false;                                  
                                    }
                                    else{
                                        $class = 'editable';
                                        $editable = true;  
                                    }
                                @endphp

                                @foreach($samples as $key => $sample)
                                    @include('shared/confirm_viral_result_row', ['sample' => $sample])
                                @endforeach

                                {{--@foreach($samples->where('parentid', '=', 0) as $key => $sample)
                                    @include('shared/confirm_viral_result_row', ['sample' => $sample])
                                @endforeach--}}

                                @if($worksheet->status_id != 3)

                                    @if((!in_array(env('APP_LAB'), $double_approval) && $worksheet->uploadedby != auth()->user()->id) || 
                                     (in_array(env('APP_LAB'), $double_approval) && ($worksheet->reviewedby != auth()->user()->id || !$worksheet->reviewedby)) )

                                        <tr bgcolor="#999999">
                                            <td  colspan="12" bgcolor="#00526C" >
                                                <center>
                                                    <!-- <input type="submit" name="approve" value="Confirm & Approve Results" class="button"  /> -->
                                                    <button class="btn btn-success" type="submit">Confirm & Approve Results</button>
                                                </center>
                                            </td>
                                        </tr>

                                    @else

                                        <tr>
                                            <td  colspan="12">
                                                <center>
                                                    You are not permitted to complete the approval. Another user should be the one to complete the approval process.
                                                </center>
                                            </td>
                                        </tr>

                                    @endif

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
        $(".editable.dilutionfactor").val(1).change();
        $('.noneditable').attr("disabled", "disabled");     
    @endcomponent

@endsection