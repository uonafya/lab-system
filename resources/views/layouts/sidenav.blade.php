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
        <ul class="nav" id="side-menu" style=" @if(env('APP_LAB') == 5 && Session('testingSystem') != 'CD4') padding-top: 0px; @else padding-top: 12px; @endif padding-left: 8px;">
        @if(!(Session('testingSystem') == 'CD4' || Auth::user()->user_type_id == 5))
            @if(env('APP_LAB') == 5)
                <li class="label label-success" style="border-radius: 0px;"><a href="#" id="cd4Switch" style="color: white; font-weight: 600; font-size:700;font-size: 12px;">Switch to CD4</a></li>
            @endif
        @endif
            <!-- <li class="active">
                <a href="#"><span class="nav-label">MENU</span><span class="fa arrow"></span> </a>
                <ul class="nav nav-second-level"> -->
        @if (Auth::user()->user_type_id == 1 || Auth::user()->user_type_id == 4)
           {{--<!--  <li class="active">
                <a href="{{!! url('home') !!}}"> <span class="nav-label">Tasks</span> 
                    <span class="label label-success pull-right">
                    $widgets['pendingSamples']+$widgets['batchesForApproval']+$widgets['batchesForDispatch']+$widgets['samplesForRepeat']+$widgets['rejectedForDispatch']
                    </span>
                </a>
            </li> -->--}}
        @endif
        
        @if (Auth::user()->is_lab_user())
            @if (session('testingSystem') == 'EID' || session('testingSystem') == null)
                
                <!-- <li>
                    <a href="#"><span class="nav-label">Samples</span><span class="fa arrow"></span> </a>
                    <ul class="nav nav-second-level">
                        <li><a href="{{ url('sample/create') }}">Add</a></li>
                        <li><a href="{{ url('batch') }}">View</a></li>
                    </ul>
                </li> -->
                @if(in_array(env('APP_LAB'), [1, 8, 9]))
                    <li><a href="{{ url('sample/upload') }}">Upload Data Entry Samples</a></li>
                    <hr />
                @endif
                <li><a href="{{ url('sample/create') }}">Add Samples</a></li>
                <hr />
                <li>
                    <a href=" {{ url('batch/site_approval') }}">Approve Site Entry Batches<span class="label label-warning pull-right">{{ $widgets['batchesForApproval'] ?? 0 }}</span></a>
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
                @if(in_array(env('APP_LAB'), [8, 9, 2, 3]))
                    <li><a href="{{ url('worksheet/create/2/22') }}">Create Abbott Worksheet(24)</a></li>
                    <hr />
                    <li><a href="{{ url('worksheet/create/2/46') }}">Create Abbott Worksheet(48)</a></li>
                    <hr />
                    <li><a href="{{ url('worksheet/create/2/70') }}">Create Abbott Worksheet(72)</a></li>
                    <hr />
                @endif
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
                <li><a href=" {{ url('worksheet/index/1') }}">Update Results<span class="label label-warning pull-right">{{ $widgets['resultsForUpdate'] ?? 0 }}</span>
                    </a>
                </li>
                <hr />
                <li><a href=" {{ url('batch/dispatch') }}">Dispatch Results<span class="label label-warning pull-right">{{ $widgets['batchesForDispatch'] ?? 0 }}</span>
                    </a>
                </li>
                <hr />
                @if(in_array(env('APP_LAB'), [2, 4]))
                    <li><a href=" {{ url('batch/to_print') }}">Batches Due For Printing</span>
                        </a>
                    </li>
                    <hr />
                @endif
            @endif
            @if (session('testingSystem') == 'Viralload')
                <!-- <li>
                    <a href="#"><span class="nav-label">Viralload Samples</span><span class="fa arrow"></span> </a>
                    <ul class="nav nav-second-level">
                        <li><a href="{{ url('viralsample/create') }}">Add</a></li>
                        <li><a href="{{ url('viralbatch') }}">View</a></li>
                    </ul>
                </li> -->
                @if(in_array(env('APP_LAB'), [1, 8, 9]))
                    <li><a href="{{ url('viralsample/upload') }}">Upload Data Entry Samples</a></li>
                    <hr />
                @endif
                @if(env('APP_LAB') == 4 && Auth::user()->user_type_id != 5)
                    <li><a href="{{ url('viralsample/create/1') }}">Add Plasma Samples</a></li>
                    <li><a href="{{ url('viralsample/create/2') }}">Add Whole Blood Samples</a></li>
                    <li><a href="{{ url('viralsample/create/3') }}">Add DBS Samples</a></li>
                    <hr />
                @else
                    <li><a href="{{ url('viralsample/create') }}">Add Samples</a></li>
                    <hr />
                @endif
                @if(env('APP_LAB') == 2 && (auth()->user()->user_type_id == 0 || auth()->user()->lab_id == 7))
                    <li><a href="{{ url('viralsample/nhrl') }}">Approve NHRL Samples</a></li>
                    <hr />
                @endif
                @if(env('APP_LAB') == 2 && (in_array(auth()->user()->user_type_id, [0, 8]) || auth()->user()->lab_id == 10))
                    <li><a href="{{ url('viralsample/nhrl') }}">Approve EDARP Samples</a></li>
                    <hr />
                @endif
                <li>
                    <a href=" {{ url('viralbatch/site_approval') }}">Approve Site Entry<span class="label label-warning pull-right">{{ $widgets['batchesForApproval'] ?? 0 }}</span></a>
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
                @if(env('APP_LAB') != 8)
                    <li><a href="{{ url('viralworksheet/set_sampletype/1') }}">Create Taqman(24)</a></li>
                    <hr />
                @endif
                @if(in_array(env('APP_LAB'), [8, 9, 2, 3]))
                    <li><a href="{{ url('viralworksheet/set_sampletype/2/0/21') }}">Create Abbott Worksheet(24)</a></li>
                    <hr />
                    <li><a href="{{ url('viralworksheet/set_sampletype/2/0/45') }}">Create Abbott Worksheet(48)</a></li>
                    <hr />
                    <li><a href="{{ url('viralworksheet/set_sampletype/2/0/69') }}">Create Abbott Worksheet(72)</a></li>
                    <hr />
                @endif
                <li><a href="{{ url('viralworksheet/set_sampletype/2') }}">Create Abbott Worksheet(96)</a></li>
                <hr />
                <li><a href="{{ url('viralworksheet/set_sampletype/2/1') }}">Create Abbott Calibration Worksheet</a></li>
                <hr />
                @if(!in_array(env('APP_LAB'), [6, 8, 9]))
                    <li><a href="{{ url('viralworksheet/set_sampletype/3') }}">Create C8800 Worksheet(96)</a></li>
                    <hr />
                    <li><a href="{{ url('viralworksheet/set_sampletype/4') }}">Create Panther Worksheet(96)</a></li>
                    <hr />
                @endif
                <!-- <li>
                    <a href="#"><span class="nav-label">Viralload Batches</span><span class="fa arrow"></span> </a>
                    <ul class="nav nav-second-level">
                        <li><a href=" {{-- url('viralbatch') --}}">View</a></li>
                        <li><a href=" {{-- url('viralbatch/site_approval') --}}">Approve Site Entry</a></li>
                        <li><a href=" {{-- url('viralbatch/dispatch') --}}">Dispatch</a></li>
                    </ul>
                </li> -->
                <li><a href=" {{ url('viralworksheet/index/1') }}">Update Results
                    <span class="label label-warning pull-right">{{ $widgets['resultsForUpdate'] ?? 0 }}</span>
                    </a>
                </li>
                <hr />
                <li>
                    <a href=" {{ url('viralbatch/dispatch') }}">Dispatch Results<span class="label label-warning pull-right">{{ $widgets['batchesForDispatch'] ?? 0 }}</span></a>
                </li>
                <hr />
                @if(in_array(env('APP_LAB'), [2, 4]))
                    <li><a href=" {{ url('viralbatch/to_print') }}">Batches Due For Printing</span>
                        </a>
                    </li>
                    <hr />
                @endif
            @endif
            @if (session('testingSystem') == 'DR')
                <li><a href="{{ url('dr_sample/create') }}">Add Samples</a></li>
                <hr />
                <li><a href="{{ url('dr_sample') }}">Samples List</a></li>
                <hr />
                <li><a href="{{ url('dr_sample/index/11') }}">Verify Site Entry Samples</a></li>
                <hr />
                @if(env('APP_LAB') != 7)
                    <li><a href="{{ url('dr_patient') }}">Potential DR Patients List</a></li>
                    <hr />
                @endif
                <!-- <li><a href="{{ url('dr_extraction_worksheet/create/48') }}">Create Extraction Worksheet (48)</a></li>
                <hr />
                <li><a href="{{ url('dr_extraction_worksheet/create/96') }}">Create Extraction Worksheet (96)</a></li>
                <hr />
                <li><a href="{{ url('dr_extraction_worksheet') }}">Extraction Worksheet List</a></li>
                <hr /> -->
                <li><a href="{{ url('dr_worksheet/create') }}">Create Sequencing Worksheet (Bulk Template)</a></li>
                <hr />
                <li><a href="{{ url('dr_worksheet') }}">Sequencing Worksheet (Bulk Template) List</a></li>
                <hr />
                
            @endif
            <!-- <li>
                <a href="#"><span class="nav-label">Results</span><span class="label label-warning pull-right">{{-- $widgets['batchesForDispatch'] --}}<span class="fa arrow"></span></span></a>
                <ul class="nav nav-second-level">
                    <li>
                        <a href="{{-- url('batch/dispatch') --}}">Dispatch Results
                            <span class="label label-warning pull-right">{{-- $widgets['batchesForDispatch'] --}}</span>
                        </a>
                    </li>
                </ul>
            </li> -->
            <!-- <li>
                <a href="{{-- url('batch/dispatch') --}}">Dispatch Results
                    <span class="label label-warning pull-right">{{-- $widgets['batchesForDispatch'] --}}</span>
                </a>
            </li> -->
        @endif
        @if (in_array(Auth::user()->user_type_id, [5,10]))
            @if(env('APP_LAB') == 7)
                <li>
                    <a href="{{ url('dr_sample/create') }}">Add DR Sample</a>
                </li>
                <li>
                    <a href="{{ url('dr_sample') }}">DR Sample List</a>
                </li>
                <li>
                    <a href="{{ url('dr_sample/index/1') }}">DR Results</a>
                </li>

            @else
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
                    <a href="{{ url('sample/list_poc/1') }}">Update POC EID Results</a>
                </li>
                <hr />
                <li>
                    <a href="{{ url('viralsample/list_poc/1') }}">Update POC VL Results</a>
                </li>
                <hr />
                <li>
                    <a href="{{ url('facility/reports/EID') }}">EID Reports</a>
                </li>
                <hr>
                <li>
                    <a href="{{ url('facility/reports/VL') }}">VL Reports</a>
                </li>
                <hr>
                <li>
                    <a href="http://lab-2.test.nascop.org/download/poc">POC User Guide</a>
                </li>
                <li>
                    <a href="http://lab-2.test.nascop.org/download/eid_req">EID Form</a>
                </li>
                <li>
                    <a href="http://lab-2.test.nascop.org/download/vl_req">VL Form</a>
                </li>
                <li>
                    <a href="http://lab-2.test.nascop.org/download/remotelogin">Remote Login SOP</a>
                </li>

            @endif
        @elseif (Auth::user()->user_type_id == 8)
            <li><a href="{{ url('viralsample/nhrl') }}">Approve EDARP Samples</a></li>
            <hr />
            <li>
                <a href="http://lab-2.test.nascop.org/download/eid_req">EID Form</a>
            </li>
            <li>
                <a href="http://lab-2.test.nascop.org/download/vl_req">VL Form</a>
            </li>
            <hr />
        
        @endif
            <!-- <li>
                <a href="#"> <span class="nav-label">Search</span></a>
            </li> -->
        {{--
            @if (Auth::user()->user_type_id == 1 || Auth::user()->user_type_id == 0)
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
            @if (Auth::user()->user_type_id == 1 || Auth::user()->user_type_id == 4 || Auth::user()->user_type_id == 0)
                <li>
                    <a href="#"> <span class="nav-label">Verify Batch Entry</span><span class="label label-danger pull-right">20</span> </a>
                </li>
                <hr />
            @endif
        --}}
        @if(!in_array(Session('testingSystem'), ['CD4', 'DR']) && in_array(Auth::user()->user_type_id, [0, 7]))
            <li>
                <a href="{{ url('sample/list_poc') }}">View POC EID Samples</a>
            </li>
            <hr />
            <li>
                <a href="{{ url('viralsample/list_poc') }}">View POC VL Samples</a>
            </li>
            <hr />
        @endif
            
        <!-- Admin Side Bar -->
        @if (in_array(Auth::user()->user_type_id, [0, 2]))
            <li>
                <a href="{{ url('user/create') }}"><span class="nav-label">Add Users</span></a>
            </li>
            <hr />
            <li>
                <a href="{{ url('users/activity') }}"><span class="nav-label">Users Activity</span></a>
            </li>
            <hr />
            <li>
                <a href="{{ url('facility/create') }}"><span class="nav-label">Add Facilty</span></a>
            </li>
            <hr />
            <li>
                <a href="{{ url('lab') }}"><span class="nav-label">Edit Lab Contacts</span></a>
            </li>
            <hr />
            <li>
                <a href="{{ url('allocationcontacts') }}"><span class="nav-label">Allocation Contacts</span></a>
            </li>
            <hr />
            @if(in_array(env('APP_LAB'), [1, 5]))
                <li>
                    <a href="{{ url('email/create') }}"><span class="nav-label">Add Email</span></a>
                </li>
                <hr />
                <li>
                    <a href="{{ url('email') }}"><span class="nav-label">View Emails</span></a>
                </li>
                <hr />
            @endif
        @endif

        @if(Session('testingSystem') == 'CD4')
            <li>
                <a href="{{ url('cd4/sample/create') }}"><span class="nav-label">Add Sample</span></a>
            </li>
            <hr />
            <li>
                <a href="{{ url('cd4/worksheet/create/38') }}"><span class="nav-label">Create Worksheet (38)</span></a>
            </li>
            <hr />
            <li>
                <a href="{{ url('cd4/worksheet/create/40') }}"><span class="nav-label">Create Worksheet (40)</span></a>
            </li>
            <hr />
            <li>
                <a href="{{ url('cd4/worksheet/index/1') }}"><span class="nav-label">Update Results</span><span class="label label-warning pull-right">{{ $widgets['CD4resultsForUpdate'] ?? 0 }}</span></a>
            </li>
            <hr />
            <li>
                <a href="{{ url('cd4/worksheet/state/1') }}"><span class="nav-label">Dispatch Results</span><span class="label label-warning pull-right">{{ $widgets['CD4resultsForDispatch'] ?? 0 }}</span></a>
            </li>
            <hr />
        @endif
        <!-- Admin Side Bar -->

        {{--
        @if (Auth::user()->user_type_id == 1 || Auth::user()->user_type_id == 4)
            <li>
                <a href="#"> <span class="nav-label">KITS</span></a>
            </li>
        @endif
        @if (Auth::user()->user_type_id == 1 || Auth::user()->user_type_id == 2)
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
        @if (Auth::user()->user_type_id != 5)
            <li>
                <a href="#"> <span class="nav-label">Change Password</span></a>
            </li>
            <hr />
        @endif
        @if (Auth::user()->user_type_id != 2)
            <li>
                <a href="#"> <span class="nav-label">User Manual</span></a>
            </li>
            <hr />
        @endif
        --}}
        @if (!in_array(Auth::user()->user_type_id,[5]) || env('APP_LAB') == 7)
            <li><a href="{{ url('user/passwordReset') }}">Change Password</a></li>
            <hr />
        @endif
        @if(!(in_array(Session('testingSystem'), ['CD4', 'DR']) || in_array(Auth::user()->user_type_id, [5, 8]) ))
            <li>
            @if(env('APP_LAB') == 4)
                @if(Auth::user()->user_type_id != 4)
                    <a href="{{ url('kitsdeliveries') }}"> <span class="nav-label">Add Quarterly Kit Deliveries</span></a>
                @endif
            @else
                <a href="{{ url('kitsdeliveries') }}"> <span class="nav-label">Add Quarterly Kit Deliveries</span></a>
            @endif
            </li>
            <hr />
        @endif
        @if(!in_array(Session('testingSystem'), ['CD4', 'DR']) && in_array(Auth::user()->user_type_id, [0, 1]))
            <li><a href="{{ url('lablogs') }}">Lab Equipments/Performance</a></li>
            <li><a href="{{ url('equipmentbreakdown') }}">Report Equipment Breakdown</a></li>
            <hr />
        @endif
        @if(Auth::user()->user_type_id != 2)
            @if(in_array(Auth::user()->user_type_id, [5,10]))
                @if(env('APP_LAB') == 7)
                    <!-- DR Searches -->
                    <li><a href="#"><select class="form-control" id="sidebar_dr_facility_search"></select></a></li>
                    <li><a href="#"><select class="form-control" id="dr_patient_search"></select></a></li>
                    <li><a href="#"><select class="form-control" id="dr_nat_id_search"></select></a></li>
                    <li><a href="#"><select class="form-control" id="dr_sample_search"></select></a></li>
                @else
                    <!-- EID Searches -->
                    <li><a href="#"><select class="form-control" id="sidebar_batch_search"></select></a></li>
                    <li><a href="#"><select class="form-control" id="sidebar_patient_search"></select></a></li>
                    <li><a href="#"><select class="form-control" id="sidebar_labID_search"></select></a></li>
                    <!-- VL Searches -->
                    <li><a href="#"><select class="form-control" id="sidebar_viralbatch_search"></select></a></li>
                    <li><a href="#"><select class="form-control" id="sidebar_viralpatient_search"></select></a></li>
                    <li><a href="#"><select class="form-control" id="sidebar_virallabID_search"></select></a></li>
                @endif
            @else
                @if(session('testingSystem') == 'Viralload')
                    <li><a href="http://lab-2.test.nascop.org/download/vl_req">Download VL Form</a></li>
                    <li><a href="#"><select class="form-control" id="sidebar_viralfacility_search"></select></a></li>
                    <li><a href="#"><select class="form-control" id="sidebar_viralbatch_search"></select></a></li>
                    <li><a href="#"><select class="form-control" id="sidebar_viralpatient_search"></select></a></li>
                    <li><a href="#"><select class="form-control" id="sidebar_viralworksheet_search"></select></a></li>
                    <li><a href="#"><select class="form-control" id="sidebar_virallabID_search"></select></a></li>
                    @if(env('APP_LAB') == 5)
                        <li><a href="#"><select class="form-control" id="sidebar_viral_order_no_search"></select></a></li>
                    @endif
                @elseif(session('testingSystem') == 'EID')
                    <li><a href="http://lab-2.test.nascop.org/download/eid_req">Download EID Form</a></li>
                    <li><a href="#"><select class="form-control" id="sidebar_facility_search"></select></a></li>
                    <li><a href="#"><select class="form-control" id="sidebar_batch_search"></select></a></li>
                    <li><a href="#"><select class="form-control" id="sidebar_patient_search"></select></a></li>
                    <li><a href="#"><select class="form-control" id="sidebar_worksheet_search"></select></a></li>
                    <li><a href="#"><select class="form-control" id="sidebar_labID_search"></select></a></li>
                    @if(env('APP_LAB') == 5)
                        <li><a href="#"><select class="form-control" id="sidebar_order_no_search"></select></a></li>
                    @endif
                @elseif(Session('testingSystem') == 'CD4')
                    <li><a href="#"><select class="form-control" id="sidebar_cd4_patientname"></select></a></li>
                    <li><a href="#"><select class="form-control" id="sidebar_cd4labID_search"></select></a></li>
                    <li><a href="#"><select class="form-control" id="sibebar_cd4medrecNo_search"></select></a></li>
                    <li><a href="#"><select class="form-control" id="sidebar_cd4worksheet_search"></select></a></li>
                    <li><a href="#"><select class="form-control" id="sidebar_cd4facility_search"></select></a></li>
                @elseif(Session('testingSystem') == 'DR')
                    <li><a href="#"><select class="form-control" id="sidebar_dr_facility_search"></select></a></li>
                    <li><a href="#"><select class="form-control" id="dr_patient_search"></select></a></li>
                    <li><a href="#"><select class="form-control" id="dr_nat_id_search"></select></a></li>
                    <li><a href="#"><select class="form-control" id="dr_sample_search"></select></a></li>
                @endif
            @endif
        @endif
        </ul>
    </div>
</aside>