<style type="text/css">
    .navbar.navbar-static-top a, .nav.navbar-nav li a {
        color: black;
    }
    @media (min-width: 768px)
    .navbar-nav {
        float: left;
        margin: 0;
        margin-right: 2em;
    }
    .navbar.navbar-static-top a, .nav.navbar-nav li a {
        color: black;
        padding-left: 10px;
        /* padding-right: 10px; */
    }
</style>
<div id="header">
    <div class="">
    </div>
    <a href="{{ url('/home') }} ">
        <div id="logo" class="light-version" style="padding-left: 2px; padding-top: 6px; width: 250px;">
            <span>
                <img src="{{ asset('img/logo.jpg') }}">
            </span>
        </div>
    </a>
    <nav role="navigation">
        <div class="header-link hide-menu"><i class="fa fa-bars"></i></div>
        <div class="small-logo">
            <span class="text-primary">@if(Auth::user()->user_type_id != 7) @if(Session('testingSystem') == 'CD4') CD4 @else EID - VL @endif @endif</span>
        </div>
        @if(Session('testingSystem') != 'CD4')
            @if(Session('pendingTasks') && !(env('APP_LAB') == 2 || env('APP_LAB') == 5))
                <form role="search" class="navbar-form-custom" style="width: 400px;">
                    <div class="form-group">
                        <h4 style="margin-top:1em;">{{ $pageTitle ?? '' }}</h4>
                    </div>
                </form>
            @endif
        @endif
        <div class="mobile-menu">
            <button type="button" class="navbar-toggle mobile-menu-toggle" data-toggle="collapse" data-target="#mobile-collapse">
                <i class="fa fa-chevron-down"></i>
            </button>
            <div class="collapse mobile-navbar" id="mobile-collapse">
                <ul class="nav navbar-nav">
                @if(!in_array(Auth::user()->user_type_id, [7,8]))
                    @if(Session('testingSystem') == 'CD4')
                        <li class="">
                            <a href="{{ url('home') }}">Home</a>
                        </li>
                        <li class="">
                            <a href="{{ url('cd4/sample') }}">Samples</a>
                        </li>
                        <li class="">
                            <a href="{{ url('cd4/worksheet') }}">Worksheets</a>
                        </li>
                        <li class="">
                            <a href="{{ url('cd4/sample/dispatch/2') }}">Results List</a>
                        </li>
                        <li class="">
                            <a href="{{ url('cd4/reports') }}">Reports</a>
                        </li>
                        <li class="">
                            <a href="{{ url('home') }}">Dashboard</a>
                        </li>
                    @else
                        @if(!Session('pendingTasks') || env('APP_LAB') == 2)
                            @if (Auth::user()->user_type_id == 5)
                                <li class="">
                                    <a href="{{ url('batch') }}">EID Samples</a>
                                </li>
                                <li class="">
                                    <a href="{{ url('batch/index/1') }}">EID Results</a>
                                </li>
                                <li class="">
                                    <a href="{{ url('viralbatch') }}">VL Samples</a>
                                </li>
                                <li class="">
                                    <a href="{{ url('viralbatch/index/1') }}">VL Results</a>
                                </li>
                                <li class="">
                                    <a href="{{ url('sample/list_poc') }}">EID POC Samples</a>
                                </li>
                                <li class="">
                                    <a href="{{ url('viralsample/list_poc') }}">VL POC Samples</a>
                                </li>

                                <li class="">
                                    <a href="{{ url('worklist') }}">POC Worklists</a>
                                </li>
                                <li class="">
                                    <a href="{{ url('sample/sms_log') }}">EID SMS Log</a>
                                </li>
                                <li class="">
                                    <a href="{{ url('viralsample/sms_log') }}">VL SMS Log</a>
                                </li>
                                <!-- <li class="">
                                    <a href="#">EID Reports</a>
                                </li>
                                <li class="">
                                    <a href="#">VL Reports</a>
                                </li> -->
                            @elseif(Auth::user()->user_type_id == 2)
                                <li>
                                    <a href="{{ url('home') }}">Home</a>
                                </li>
                                <li>
                                    <a href="{{ url('user') }}">Users</a>
                                </li>
                                <li>
                                    <a href="{{ url('facility') }}">Facilities</a>
                                </li>
                                <li>
                                    <a href="{{ url('facility/lab') }}">Lab Facilities</a>
                                </li>
                            @else
                                @if(!Session('pendingTasks') || env('APP_LAB') == 2)

                                    <li class=""> <a href="{{ url($widgets['prefix'] . 'batch') }}">Samples</a> </li>
                                    <li class=""> <a href="{{ url($widgets['prefix'] . 'worksheet') }}">Worksheets</a> </li>
                                    <li class=""> <a href="{{ url($widgets['prefix'] . 'batch/index/1') }}">Dispatched Results</a> </li>

                                    <li class="">
                                        <a href="{{ url('facility') }}">Facilities</a>
                                    </li>
                                    <li>
                                        <a href="{{ url('facility/lab') }}">Lab Facilities</a>
                                    </li>
                                    <li class="">
                                        <a href="{{ route('reports') }}">Reports</a>
                                    </li>
                                    <li class="">
                                        <a href="{{ route('dashboard') }}">Dashboard</a>
                                    </li>
                                    <li class="">
                                        @if(env('APP_LAB') != 7 && session('testingSystem') != 'DR')
                                            <a href="{{ url('reports/kits') }}">Kits
                                            <span class="label label-{{ $widgets['get_badge']($widgets['rejectedAllocations']) }}">
                                            {{ $widgets['rejectedAllocations'] }}
                                            </span>
                                            </a>       
                                        @endif    
                                    </li>
                                    @if(Auth::user()->user_type_id == 0)
                                        <li>
                                            <a href="{{ url('user') }}">Users</a>
                                        </li>
                                    @endif
                                @endif
                            @endif
                        @else
                            <li class="">
                                <a href="{{ url('pending') }}">Pending Tasks</a>
                            </li>
                        @endif
                    @endif
                @endif
                    <li>
                        <a class="" href="{{ url('/logout') }}"
                        onclick="event.preventDefault();
                        document.getElementById('logout-form').submit();">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
        <div class="navbar-right">
            <ul class="nav navbar-nav no-borders">
            @if(!in_array(Auth::user()->user_type_id, [7,8]))
                @if(Session('testingSystem') == 'CD4')
                    <li class="">
                        <a class="label-menu-corner" href="{{ url('home') }}">
                        <i class="pe-7s-home" style="font-size: 25px;"></i>
                            <span class="label label-danger"></span>
                        </a>
                    </li>
                    <li class="">
                        <a href="{{ url('cd4/sample') }}">Samples</a>
                    </li>
                    <li class="">
                        <a href="{{ url('cd4/worksheet') }}">Worksheets</a>
                    </li>
                    <li class="">
                        <a href="{{ url('cd4/sample/dispatch/2') }}">Results List</a>
                    </li>
                    <li class="">
                        <a href="{{ url('cd4/reports') }}">Reports</a>
                    </li>
                    <li class="">
                        <a href="{{ url('home') }}">Dashboard</a>
                    </li>
                @elseif(session('testingSystem') == 'DR')
                    <li class="">
                        <a href="{{ url('reports') }}">Report</a>
                    </li>                          
                @else
                    @if(!Session('pendingTasks') || env('APP_LAB') == 2)
                        @if (Auth::user()->user_type_id == 5)
                            <li class="">
                                <a href="{{ url('batch') }}">EID Samples</a>
                            </li>
                            <li class="">
                                <a href="{{ url('batch/index/1') }}">EID Results</a>
                            </li>
                            <li class="">
                                <a href="{{ url('viralbatch') }}">VL Samples</a>
                            </li>
                            <li class="">
                                <a href="{{ url('viralbatch/index/1') }}">VL Results</a>
                            </li>
                            <li class="">
                                <a href="{{ url('sample/list_poc') }}">EID POC Samples</a>
                            </li>
                            <li class="">
                                <a href="{{ url('viralsample/list_poc') }}">VL POC Samples</a>
                            </li>
                            <li class="">
                                <a href="{{ url('worklist') }}">POC Worklists</a>
                            </li>
                            <li class="">
                                <a href="{{ url('sample/sms_log') }}">EID SMS Log</a>
                            </li>
                            <li class="">
                                <a href="{{ url('viralsample/sms_log') }}">VL SMS Log</a>
                            </li>
                            <!-- <li class="">
                                <a href="#">EID Reports</a>
                            </li>
                            <li class="">
                                <a href="#">VL Reports</a>
                            </li> -->
                        @elseif(Auth::user()->user_type_id == 2)
                                <li>
                                    <a href="{{ url('home') }}">Home</a>
                                </li>
                                <li>
                                    <a href="{{ url('user') }}">Users</a>
                                </li>
                                <li>
                                    <a href="{{ url('facility') }}">Facilities</a>
                                </li>
                                <li>
                                    <a href="{{ url('facility/lab') }}">Lab Facilities</a>
                                </li>
                                <li>
                                    <a href="{{ url('sample/transfer_samples') }}">Transfer EID Samples</a>
                                </li>
                                <li>
                                    <a href="{{ url('viralsample/transfer_samples') }}">Transfer VL Samples</a>
                                </li>


                        @else
                            <li class="">
                                <a class="label-menu-corner" href="{{ url('home') }}">
                                <i class="pe-7s-home" style="font-size: 25px;"></i>
                                    <span class="label label-danger">
                                    @isset($widgets['pendingSamples'])
                                        @if(session('testingSystem') == 'Viralload')
                                            {{ $widgets['pendingSamples']['all']+$widgets['batchesForApproval']+$widgets['batchesNotReceived']+$widgets['batchesForDispatch']+$widgets['samplesForRepeat']+$widgets['rejectedForDispatch'] }}
                                        @else
                                            {{ $widgets['pendingSamples']+$widgets['batchesForApproval']+$widgets['batchesForDispatch']+$widgets['samplesForRepeat']+$widgets['rejectedForDispatch'] }}
                                        @endif
                                    @endisset
                                    </span>
                                </a>
                            </li>

                            <li class=""> <a href="{{ url($widgets['prefix'] . 'batch') }}">Samples</a> </li>
                            <li class=""> <a href="{{ url($widgets['prefix'] . 'worksheet') }}">Worksheets</a> </li>
                            <li class=""> <a href="{{ url($widgets['prefix'] . 'batch/index/1') }}">Dispatched Results</a> </li>

                            <li class="">
                                <a href="{{ url('facility') }}">Facilities</a>
                            </li>
                            <li>
                                <a href="{{ url('facility/lab') }}">Lab Facilities</a>
                            </li>
                            <li class="">
                                <a href="{{ route('reports') }}">Reports</a>
                            </li>
                            <li class="">
                                <a href="{{ route('dashboard') }}">Dashboard</a>
                            </li>
                            <li class="">
                                <a href="
                                    @if(session('testingSystem') == 'Viralload')
                                        {{ 'https://viralload.nascop.org/labs' }}
                                    @else
                                        {{ 'https://eid.nascop.org/labPerformance' }}
                                    @endif">Nascop Dashboard</a>
                            </li>
                            @if(env('APP_LAB') != 7 && session('testingSystem') != 'DR')
                            <li class="">                   <a href="{{ url('reports/kits') }}">Kits
            <span class="label label-{{ $widgets['get_badge']($widgets['rejectedAllocations']) }}">
            {{ $widgets['rejectedAllocations'] }}
            </span></a>
                            </li>
                            @endif
                            @if(Auth::user()->user_type_id == 0)
                                <li>
                                    <a href="{{ url($widgets['prefix'] . 'sample/transfer_samples') }}">Transfer</a>
                                </li>
                                <li>
                                    <a href="{{ url('user') }}">Users</a>
                                </li>
                            @endif
                        @endif
                    @else
                        <li class="">
                            <a href="{{ url('pending') }}">Pending Tasks</a>
                        </li>
                    @endif
                @endif
            @endif
                <li class="dropdown">
                        
                    <a href="{{ url('/logout') }}"
                        onclick="event.preventDefault();
                        document.getElementById('logout-form').submit();"
                         style="font-size: 25px;"
                    >
                        <i class="pe-7s-upload pe-rotate-90"></i>
                        <!-- <i class="fa fa-sign-out"></i> Log out -->

                    </a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                        {{ csrf_field() }}
                    </form>

                </li>
            </ul>
        </div>
    </nav>
</div>