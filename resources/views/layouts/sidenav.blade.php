<aside id="menu">
    <div id="navigation">
        <ul class="nav" id="side-menu">
        @if (auth()->user()->user_type_id == 1 || auth()->user()->user_type_id == 4)
            <li class="active">
                <a href="{{ url('home') }}"> <span class="nav-label">Tasks</span> 
                    <span class="label label-success pull-right">
                    {{ $widgets['pendingSamples']+$widgets['batchesForApproval'][0]->totalsamples+$widgets['batchesForDispatch']+$widgets['samplesForRepeat']+$widgets['rejectedForDispatch'][0]->rejectfordispatch }}
                    </span>
                </a>
            </li>
        @endif
            <li>
                <a href="#"> <span class="nav-label">Dashboard</span> </a>
            </li>
        @if (auth()->user()->user_type_id == 1 || auth()->user()->user_type_id == 4)
            <li>
                <a href="#"><span class="nav-label">Samples</span><span class="fa arrow"></span> </a>
                <ul class="nav nav-second-level">
                    <li><a href="{{ url('sample/create') }}">Add</a></li>
                    <li><a href="{{ url('sample') }}">View</a></li>
                </ul>
            </li>
            <li>
                <a href="#"><span class="nav-label">Worksheets</span><span class="fa arrow"></span> </a>
                <ul class="nav nav-second-level">
                    <li><a href=" {{ url('worksheet') }}">Worksheets</a></li>
                    <li><a href="{{ url('worksheet/create_abbot') }}">Create Abbott Worksheet(24)</a></li>
                    <li><a href="{{ url('worksheet/create_abbot') }}">Create Abbott Worksheet(48)</a></li>
                    <li><a href="{{ url('worksheet/create_abbot') }}">Create Abbott Worksheet(96)</a></li>
                </ul>
            </li>
        @endif
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
                <a href="#"> <span class="nav-label">Facilities</span></a>
            </li>
        @endif
        @if (auth()->user()->user_type_id == 2)
        	<li>
                <a href="#"> <span class="nav-label">Districts</span></a>
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
        </ul>
    </div>
</aside>