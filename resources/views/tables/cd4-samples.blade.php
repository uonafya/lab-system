@extends('layouts.master')

    @component('/tables/css')
    @endcomponent

@section('content')

<div class="content">
    <div class="row">
        <div class="col-lg-12">
            <div class="hpanel">
                <div class="panel-body">
                    <div class="row">
                                              
                    </div>
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered table-hover" >
                            <thead>
                                <tr> 
                                    <th>Serial #</th>
                                    <th>Lab #</th>
                                    <th>Medical Record #</th>
                                    <th>Patient Names</th>
                                    <th>Age</th>
                                    <th>Facility Name</th>
                                    <th>Date Collected</th>
                                    <th>Date Received</th>
                                    <th>Viability</th>
                                    <th>Status</th>
                                    <th>Worksheet</th>
                                    <th>Date Tested</th>
                                    <th>CD4 abs</th>
                                    <th>Date Printed</th>
                                    <th>Task</th>
                                </tr>
                            </thead>
                            <tbody> 
                                
                            </tbody>
                        </table>
                    </div>

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