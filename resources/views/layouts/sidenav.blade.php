<style type="text/css">
    body.light-skin #side-menu li a {
        font-weight: 380;
    }
</style>
<aside id="menu">
    <div id="navigation">
        <ul class="nav" id="side-menu">
            <!-- <li class="active">
                <a href="#"><span class="nav-label">MENU</span><span class="fa arrow"></span> </a>
                <ul class="nav nav-second-level"> -->
        @if (auth()->user()->user_type_id == 1 || auth()->user()->user_type_id == 4)
           <!--  <li class="active">
                <a href="{{ url('home') }}"> <span class="nav-label">Tasks</span> 
                    <span class="label label-success pull-right">
                    {{ $widgets['pendingSamples']+$widgets['batchesForApproval'][0]->totalsamples+$widgets['batchesForDispatch']+$widgets['samplesForRepeat']+$widgets['rejectedForDispatch'][0]->rejectfordispatch }}
                    </span>
                </a>
            </li> -->
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
                <li><a href="{{ url('sample/create') }}">Add Samples</a></li>
                <!-- <li>
                    <a href="#"><span class="nav-label">Worksheets</span><span class="fa arrow"></span> </a>
                    <ul class="nav nav-second-level">
                        <li><a href=" {{ url('worksheet') }}">Worksheets</a></li>
                        <li><a href="{{ url('worksheet/create/1') }}">Create Taqman(24)</a></li>
                        <li><a href="{{ url('worksheet/create/2') }}">Create Abbott Worksheet(96)</a></li>
                    </ul>
                </li> -->
                <li><a href="{{ url('worksheet/create/1') }}">Create Taqman Worksheet(24)</a></li>
                <li><a href="{{ url('worksheet/create/2') }}">Create Abbott Worksheet(96)</a></li>
                <!-- <li>
                    <a href="#"><span class="nav-label">Batches</span><span class="fa arrow"></span> </a>
                    <ul class="nav nav-second-level">
                        <li><a href=" {{ url('batch') }}">View</a></li>
                        <li><a href=" {{ url('batch/site_approval') }}">Approve Site Entry</a></li>
                        <li><a href=" {{ url('batch/dispatch') }}">Dispatch</a></li>
                    </ul>
                </li> -->
                <!-- <li><a href=" {{ url('batch') }}">View Batches</a></li> -->
                <li><a href=" {{ url('batch/site_approval') }}">Approve Site Entry Batches</a></li>
                <li><a href=" {{ url('batch/dispatch') }}">Dispatch Results
                    <span class="label label-warning pull-right">{{ $widgets['batchesForDispatch'] }}</span>
                    </a>
                </li>
            @endif
            @if (session('testingSystem') == 'Viralload')
                <!-- <li>
                    <a href="#"><span class="nav-label">Viralload Samples</span><span class="fa arrow"></span> </a>
                    <ul class="nav nav-second-level">
                        <li><a href="{{ url('viralsample/create') }}">Add</a></li>
                        <li><a href="{{ url('viralbatch') }}">View</a></li>
                    </ul>
                </li> -->
                <li><a href="{{ url('viralsample/create') }}">Add Samples</a></li>
                <li><a href="{{ url('viralbatch') }}">View Samples</a></li>
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
                <li><a href=" {{ url('viralworksheet') }}">Worksheets</a></li>
                <li><a href="{{ url('viralworksheet/create/1') }}">Create Taqman(24)</a></li>
                <li><a href="{{ url('viralworksheet/create/2') }}">Create Abbott Worksheet(96)</a></li>
                <li><a href="{{ url('viralworksheet/create/3') }}">Create C8800 Worksheet(96)</a></li>
                <li><a href="{{ url('viralworksheet/create/4') }}">Create Panther Worksheet(96)</a></li>
                <!-- <li>
                    <a href="#"><span class="nav-label">Viralload Batches</span><span class="fa arrow"></span> </a>
                    <ul class="nav nav-second-level">
                        <li><a href=" {{ url('viralbatch') }}">View</a></li>
                        <li><a href=" {{ url('viralbatch/site_approval') }}">Approve Site Entry</a></li>
                        <li><a href=" {{ url('viralbatch/dispatch') }}">Dispatch</a></li>
                    </ul>
                </li> -->
                <li><a href=" {{ url('viralbatch') }}">View Batches</a></li>
                <li><a href=" {{ url('viralbatch/site_approval') }}">Approve Site Entry</a></li>
                <li><a href=" {{ url('viralbatch/dispatch') }}">Batch Dispatch</a></li>
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
                <a href="#"><span class="nav-label">Samples</span><span class="fa arrow"></span> </a>
                <ul class="nav nav-second-level">
                    <li><a href="{{ url('sample/create') }}">Add</a></li>
                    <li><a href="{{ url('batch') }}">View</a></li>
                </ul>
            </li>

            <li>
                <a href="#"><span class="nav-label">Viralload Samples</span><span class="fa arrow"></span> </a>
                <ul class="nav nav-second-level">
                    <li><a href="{{ url('viralsample/create') }}">Add</a></li>
                    <li><a href="{{ url('viralbatch') }}">View</a></li>
                </ul>
            </li>


        @endif

            <li>
                <a href="{{ url('/search') }} "> <span class="nav-label">Search</span></a>
            </li>
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
            <li>
                <a href="#"><span class="nav-label">Requisitions</span><span class="fa arrow"></span> </a>
                <ul class="nav nav-second-level">
                    <li><a href="#">Make Requisition</a></li>
                    <li><a href="#">Requisition List</a></li>
                </ul>
            </li>
        @endif
        @if (auth()->user()->user_type_id == 1 || auth()->user()->user_type_id == 4)
            <li>
                <a href="#"> <span class="nav-label">Verify Batch Entry</span><span class="label label-danger pull-right">20</span> </a>
            </li>
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
            <li>
                <a href="#"> <span class="nav-label">User Activity Log</span></a>
            </li>
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
        @endif
            <li>
                <a href="https://eid.nascop.org"> <span class="nav-label">NASCOP</span><span class="label label-success pull-right">National</span></a>
            </li>
        @if (auth()->user()->user_type_id == 1 || auth()->user()->user_type_id == 4)
            <li>
                <a href="#"> <span class="nav-label">Add Quarterly Kit Deliveries</span> <span class="label label-success pull-right">Special</span></a>
            </li>
        @endif
        @if (auth()->user()->user_type_id != 5)
            <li>
                <a href="#"> <span class="nav-label">Change Password</span></a>
            </li>
        @endif
        @if (auth()->user()->user_type_id != 2)
            <li>
                <a href="#"> <span class="nav-label">User Manual</span></a>
            </li>
            <li>
                <a href="#"><span class="nav-label">Download Forms</span><span class="fa arrow"></span> </a>
                <ul class="nav nav-second-level">
                    <li><a href="#">EID Form</a></li>
                    <li><a href="#">VL Form</a></li>
                </ul>
            </li>
        @endif
        --}}
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
            <li><a href="#"><select class="form-control" id="viralbatch_search"></select></a></li>
            <li><a href="#"><select class="form-control" id="viralpatient_search"></select></a></li>
            <li><a href="#"><select class="form-control" id="viralworksheet_search"></select></a></li>
        @else
            <li><a href="#"><select class="form-control" id="facility_search"></select></a></li>
            <li><a href="#"><select class="form-control" id="batch_search"></select></a></li>
            <li><a href="#"><select class="form-control" id="patient_search"></select></a></li>
            <li><a href="#"><select class="form-control" id="worksheet_search"></select></a></li>
        @endif
        </ul>
    </div>
</aside>