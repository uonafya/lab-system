<style type="text/css">
    body.light-skin #side-menu li a {
        font-weight: 380;
    }
    body.light-skin #side-menu li a {
        color: black;
    }
    hr {
        margin-top: 0px;
        margin-bottom: 0px;
    }
    #menu {
        background-color: white;
    }
</style>
<aside id="menu">
    <div id="navigation">
        <ul class="nav" id="side-menu" style=" padding-top: 12px;padding-left: 8px;">
            <!-- <li class="active">
                <a href="#"><span class="nav-label">MENU</span><span class="fa arrow"></span> </a>
                <ul class="nav nav-second-level"> -->
        @if (auth()->user()->user_type_id == 1 || auth()->user()->user_type_id == 4)
           {{--<!--  <li class="active">
                <a href="{{!! url('home') !!}}"> <span class="nav-label">Tasks</span> 
                    <span class="label label-success pull-right">
                    $widgets['pendingSamples']+$widgets['batchesForApproval']+$widgets['batchesForDispatch']+$widgets['samplesForRepeat']+$widgets['rejectedForDispatch'][0]->rejectfordispatch
                    </span>
                </a>
            </li> -->--}}
        @endif
        
        @if (auth()->user()->user_type_id == 1 || auth()->user()->user_type_id == 4)
            @if (session('testingSystem') == 'EID' || session('testingSystem') == null)
                
                <!-- <li>
                    <a href="#"><span class="nav-label">Samples</span><span class="fa arrow"></span> </a>
                    <ul class="nav nav-second-level">
                        <li><a href="{{ url('sample/create') }}">Add</a></li>
                        <li><a href="{{ url('batch') }}">View</a></li>
                    </ul>
                </li> -->
                <li><a href="{{ url('patient') }}">View Patients</a></li>
                <hr />
                <li><a href="{{ url('sample/create') }}">Add Samples</a></li>
                <hr />
                <li>
                    <a href=" {{ url('batch/site_approval') }}">Approve Site Entry Batches<span class="label label-warning pull-right">{{ $widgets['batchesForApproval'] }}</span></a>
                </li>
                <hr />
                <!-- <li>
                    <a href="#"><span class="nav-label">Worksheets</span><span class="fa arrow"></span> </a>
                    <ul class="nav nav-second-level">
                        <li><a href=" {{ url('worksheet') }}">Worksheets</a></li>
                        <li><a href="{{ url('worksheet/create/1') }}">Create Taqman(24)</a></li>
                        <li><a href="{{ url('worksheet/create/2') }}">Create Abbott Worksheet(96)</a></li>
                    </ul>
                </li> -->
                <li><a href="{{ url('worksheet/create/1') }}">Create Taqman Worksheet(24)</a></li>
                <hr />
                <li><a href="{{ url('worksheet/create/2') }}">Create Abbott Worksheet(96)</a></li>
                <hr />
                <!-- <li>
                    <a href="#"><span class="nav-label">Batches</span><span class="fa arrow"></span> </a>
                    <ul class="nav nav-second-level">
                        <li><a href=" {{ url('batch') }}">View</a></li>
                        <li><a href=" {{ url('batch/site_approval') }}">Approve Site Entry</a></li>
                        <li><a href=" {{ url('batch/dispatch') }}">Dispatch</a></li>
                    </ul>
                </li> -->
                <!-- <li><a href=" {{ url('batch') }}">View Batches</a></li> -->
                <li><a href=" {{ url('worksheet/index/1') }}">Update Results<span class="label label-warning pull-right">{{ $widgets['resultsForUpdate'] }}</span>
                    </a>
                </li>
                <hr />
                <li><a href=" {{ url('batch/dispatch') }}">Dispatch Results<span class="label label-warning pull-right">{{ $widgets['batchesForDispatch'] }}</span>
                    </a>
                </li>
                <hr />
            @endif
            @if (session('testingSystem') == 'Viralload')
                <!-- <li>
                    <a href="#"><span class="nav-label">Viralload Samples</span><span class="fa arrow"></span> </a>
                    <ul class="nav nav-second-level">
                        <li><a href="{{ url('viralsample/create') }}">Add</a></li>
                        <li><a href="{{ url('viralbatch') }}">View</a></li>
                    </ul>
                </li> -->
                <li><a href="{{ url('viralpatient') }}">View Patients</a></li>
                <hr />
                <li><a href="{{ url('viralsample/create') }}">Add Samples</a></li>
                <hr />
                <li><a href="{{ url('viralsample/nhrl') }}">Approve NHRL Samples</a></li>
                <hr />
                <li>
                    <a href=" {{ url('viralbatch/site_approval') }}">Approve Site Entry<span class="label label-warning pull-right">{{ $widgets['batchesForApproval'] }}</span></a>
                </li>
                <hr />
                <!-- <li>
                    <a href="#"><span class="nav-label">Viralload Worksheets</span><span class="fa arrow"></span> </a>
                    <ul class="nav nav-second-level">
                        <li><a href=" {{ url('viralworksheet') }}">Worksheets</a></li>
                        <li><a href="{{ url('viralworksheet/create/1') }}">Create Taqman(24)</a></li>
                        <li><a href="{{ url('viralworksheet/create/2') }}">Create Abbott Worksheet(96)</a></li>
                        <li><a href="{{ url('viralworksheet/create/3') }}">Create C8800 Worksheet(96)</a></li>
                        <li><a href="{{ url('viralworksheet/create/4') }}">Create Panther Worksheet(96)</a></li>
                    </ul>
                </li> -->
                <li><a href="{{ url('viralworksheet/create/1') }}">Create Taqman(24)</a></li>
                <hr />
                <li><a href="{{ url('viralworksheet/create/2') }}">Create Abbott Worksheet(96)</a></li>
                <hr />
                <li><a href="{{ url('viralworksheet/create/3') }}">Create C8800 Worksheet(96)</a></li>
                <hr />
                <li><a href="{{ url('viralworksheet/create/4') }}">Create Panther Worksheet(96)</a></li>
                <hr />
                <!-- <li>
                    <a href="#"><span class="nav-label">Viralload Batches</span><span class="fa arrow"></span> </a>
                    <ul class="nav nav-second-level">
                        <li><a href=" {{ url('viralbatch') }}">View</a></li>
                        <li><a href=" {{ url('viralbatch/site_approval') }}">Approve Site Entry</a></li>
                        <li><a href=" {{ url('viralbatch/dispatch') }}">Dispatch</a></li>
                    </ul>
                </li> -->
                <li><a href=" {{ url('viralworksheet/index/1') }}">Update Results
                    <span class="label label-warning pull-right">{{ $widgets['resultsForUpdate'] }}</span>
                    </a>
                </li>
                <hr />
                <li>
                    <a href=" {{ url('viralbatch/dispatch') }}">Dispatch Results<span class="label label-warning pull-right">{{ $widgets['batchesForDispatch'] }}</span></a>
                </li>
                <hr />
            @endif
            <!-- <li>
                <a href="#"><span class="nav-label">Results</span><span class="label label-warning pull-right">{{ $widgets['batchesForDispatch'] }}<span class="fa arrow"></span></span></a>
                <ul class="nav nav-second-level">
                    <li>
                        <a href="{{ url('batch/dispatch') }}">Dispatch Results
                            <span class="label label-warning pull-right">{{ $widgets['batchesForDispatch'] }}</span>
                        </a>
                    </li>
                </ul>
            </li> -->
            <!-- <li>
                <a href="{{ url('batch/dispatch') }}">Dispatch Results
                    <span class="label label-warning pull-right">{{ $widgets['batchesForDispatch'] }}</span>
                </a>
            </li> -->
        @endif
        @if (auth()->user()->user_type_id == 5)
            <li>
                <a href="{{ url('patient') }}">EID Patient List</a>
            </li>
            <hr />
            <li>
                <a href="{{ url('viralpatient') }}">VL Patient List</a>
            </li>
            <hr />
            <li>
                <a href="{{ url('sample/create') }}">Add EID Sample</a>
            </li>
            <hr />
            <li>
                <a href="{{ url('viralsample/create') }}">Add VL Sample</a>
            </li>
            <hr />
            <li>
                <a href="{{ url('sample/create_poc') }}">Add POC EID Sample</a>
            </li>
            <hr />
            <li>
                <a href="{{ url('viralsample/create_poc') }}">Add POC VL Sample</a>
            </li>
            <hr />
            <li>
                <a href="{{ url('worklist/create/1') }}">Create POC EID Worklist</a>
            </li>
            <hr />
            <li>
                <a href="{{ url('worklist/create/2') }}">Create POC VL Worklist</a>
            </li>
            <hr />
            <li>
                <a href="{{ url('sample/list_poc') }}">Update POC EID Results</a>
            </li>
            <hr />
            <li>
                <a href="{{ url('viralsample/list_poc') }}">Update POC VL Results</a>
            </li>
            <hr />
            <li>
                <a href="{{ url('downloads/POC') }}">POC User Guide</a>
            </li>
            <hr />
        @endif
            <!-- <li>
                <a href="#"> <span class="nav-label">Search</span></a>
            </li> -->
        {{--
        @if (auth()->user()->user_type_id == 1)
            <li>
                <a href="#"><span class="nav-label">Results</span><span class="label label-warning pull-right">{{ $widgets['batchesForDispatch'] }}<span class="fa arrow"></span></span></a>
                <ul class="nav nav-second-level">
                    <li><a href="#">Update Results</a></li>
                    <li>
                        <a href="{{ url('batch/dispatch') }}">Dispatch Results
                            <span class="label label-warning pull-right">{{ $widgets['batchesForDispatch'] }}</span>
                        </a>
                    </li>
                </ul>
            </li>
            <hr />
            <li>
                <a href="#"><span class="nav-label">Requisitions</span><span class="fa arrow"></span> </a>
                <ul class="nav nav-second-level">
                    <li><a href="#">Make Requisition</a></li>
                    <li><a href="#">Requisition List</a></li>
                </ul>
            </li>
            <hr />
        @endif
        @if (auth()->user()->user_type_id == 1 || auth()->user()->user_type_id == 4)
            <li>
                <a href="#"> <span class="nav-label">Verify Batch Entry</span><span class="label label-danger pull-right">20</span> </a>
            </li>
            <hr />
        @endif
        @if (auth()->user()->user_type_id == 2 || auth()->user()->user_type_id == 4)
        	<li>
                <a href="{{ route('facility.index') }}"> <span class="nav-label">Facilities</span></a>
            </li>
        @endif
        @if (auth()->user()->user_type_id == 2)
        	<li>
                <a href="{{ route('district.index') }}"> <span class="nav-label">Districts</span></a>
            </li>
            <hr />
            <li>
                <a href="#"> <span class="nav-label">User Activity Log</span></a>
            </li>
            <hr />
        @endif
        @if (auth()->user()->user_type_id == 1 || auth()->user()->user_type_id == 4)
            <li>
                <a href="#"> <span class="nav-label">KITS</span></a>
            </li>
        @endif
        @if (auth()->user()->user_type_id == 1 || auth()->user()->user_type_id == 2)
            <li>
                <a href="#"><span class="nav-label">SMS Printer</span><span class="fa arrow"></span> </a>
                <ul class="nav nav-second-level">
                    <li><a href="#">Add SMS Printer</a></li>
                    <li><a href="#">View SMS Printer</a></li>
                </ul>
            </li>
            <hr />
        @endif
            <li>
                <a href="https://eid.nascop.org"> <span class="nav-label">NASCOP</span><span class="label label-success pull-right">National</span></a>
            </li>
            <hr />
        @if (auth()->user()->user_type_id == 1 || auth()->user()->user_type_id == 4)
            <li>
                <a href="#"> <span class="nav-label">Add Quarterly Kit Deliveries</span> <span class="label label-success pull-right">Special</span></a>
            </li>
            <hr />
        @endif
        @if (auth()->user()->user_type_id != 5)
            <li>
                <a href="#"> <span class="nav-label">Change Password</span></a>
            </li>
            <hr />
        @endif
        @if (auth()->user()->user_type_id != 2)
            <li>
                <a href="#"> <span class="nav-label">User Manual</span></a>
            </li>
            <hr />
            <li>
                <a href="#"><span class="nav-label">Download Forms</span><span class="fa arrow"></span> </a>
                <ul class="nav nav-second-level">
                    <li><a href="#">EID Form</a></li>
                    <li><a href="#">VL Form</a></li>
                </ul>
            </li>
            <hr />
        @endif
        --}}
            <li><a href="{{ url('downloads/VL') }}">Download VL Form</a></li>
            <li><a href="{{ url('downloads/EID') }}">Download EID Form</a></li>
        <!-- </ul>
        </li>
        <li>
            <a href="#"><span class="nav-label">SEARCH</span><span class="fa arrow"></span> </a>
            <ul class="nav nav-second-level">
                <li><a href="#"><select class="select2" id="sampleSearch"></select></a></li>
                <li><a href="#"><select class="select2"></select></a></li>
                <li><a href="#"><select class="select2"></select></a></li>
                <li><a href="#"><select class="select2"></select></a></li>
            </ul>
        </li> -->
        @if(session('testingSystem') == 'Viralload')
            <li><a href="#"><select class="form-control" id="sidebar_viralfacility_search"></select></a></li>
            <li><a href="#"><select class="form-control" id="sidebar_viralbatch_search"></select></a></li>
            <li><a href="#"><select class="form-control" id="sidebar_viralpatient_search"></select></a></li>
            <li><a href="#"><select class="form-control" id="sidebar_viralworksheet_search"></select></a></li>
            <li><a href="#"><select class="form-control" id="sidebar_virallabID_search"></select></a></li>
        @else
            <li><a href="#"><select class="form-control" id="sidebar_facility_search"></select></a></li>
            <li><a href="#"><select class="form-control" id="sidebar_batch_search"></select></a></li>
            <li><a href="#"><select class="form-control" id="sidebar_patient_search"></select></a></li>
            <li><a href="#"><select class="form-control" id="sidebar_worksheet_search"></select></a></li>
            <li><a href="#"><select class="form-control" id="sidebar_labID_search"></select></a></li>
        @endif
        </ul>
    </div>
</aside>