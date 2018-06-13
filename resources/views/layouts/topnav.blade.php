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
    <div id="logo" class="light-version" style="padding-left: 2px; padding-top: 6px; width: 250px;">
        <span>
            <img src="{{ asset('img/logo.jpg') }}">
        </span>
    </div>
    <nav role="navigation">
        <div class="header-link hide-menu"><i class="fa fa-bars"></i></div>
        <div class="small-logo">
            <span class="text-primary">EID - VL</span>
        </div>
        @if(Session('pendingTasks'))
            <form role="search" class="navbar-form-custom" style="width: 400px;">
                <div class="form-group">
                    <h4 style="margin-top:1em;">{{ $pageTitle ?? '' }}</h4>
                </div>
            </form>
        @endif
        <div class="mobile-menu">
            <button type="button" class="navbar-toggle mobile-menu-toggle" data-toggle="collapse" data-target="#mobile-collapse">
                <i class="fa fa-chevron-down"></i>
            </button>
            <div class="collapse mobile-navbar" id="mobile-collapse">
                <ul class="nav navbar-nav">
                @if (Auth()->user()->user_type_id == 5)
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
                        <a href="#">VL POC Samples</a>
                    </li>
                    <li class="">
                        <a href="#">POC Worklists</a>
                    </li>
                    <li class="">
                        <a href="#">EID Reports</a>
                    </li>
                    <li class="">
                        <a href="#">VL Reports</a>
                    </li>
                @elseif(Auth()->user()->user_type_id == 2)
                    <li>
                        <a href="">Home</a>
                    </li>
                    <li>
                        <a href="{{ url('users') }}">Users</a>
                    </li>
                    <li>
                        <a href="{{ url('facilities') }}">Facilities</a>
                    </li>
                @else
                    @if(!Session('pendingTasks'))
                        <li class="">
                            <a href="
                                @if(session('testingSystem') == 'Viralload')
                                    {{ url('viralbatch') }}
                                @else
                                    {{ url('batch') }}
                                @endif">Samples</a>
                        </li>
                        
                        @if(session('testingSystem') != 'Viralload')
                        <li class="">
                            <a href="#">Requisitions</a>
                        </li>
                        @endif
                        
                        <li class="">
                            <a href="
                                @if(session('testingSystem') == 'Viralload')
                                    {{ url('viralworksheet') }}
                                @else
                                    {{ url('worksheet') }}
                                @endif">Worksheets</a>
                        </li>
                        <li class="">
                            <a href="
                                @if(session('testingSystem') == 'Viralload')
                                    {{ url('viralbatch/index/4/1') }}
                                @else
                                    {{ url('batch/index/4/1') }}
                                @endif">Dispatched Results</a>
                        </li>
                        <li class="">
                            <a href="{{ url('facility') }}">Facilities</a>
                        </li>
                        <li class="">
                            <a href="{{ route('reports') }}">Reports</a>
                        </li>
                        <li class="">
                            <a href="{{ route('dashboard') }}">Dashboard</a>
                        </li>
                        
                        @if(session('testingSystem') != 'Viralload')
                        <li class="">
                            <a href="#">Kits</a>
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
            @if (Auth()->user()->user_type_id == 5)
                <li class="">
                    <a href="{{ url('batch') }}">EID Samples</a>
                </li>
                <li class="">
                    <a href="{{ url('batch/index/4/1') }}">EID Results</a>
                </li>
                <li class="">
                    <a href="{{ url('viralbatch') }}">VL Samples</a>
                </li>
                <li class="">
                    <a href="{{ url('viralbatch/index/4/1') }}">VL Results</a>
                </li>
                <li class="">
                    <a href="{{ url('sample/list_poc') }}">EID POC Samples</a>
                </li>
                <li class="">
                    <a href="#">VL POC Samples</a>
                </li>
                <li class="">
                    <a href="#">POC Worklists</a>
                </li>
                <li class="">
                    <a href="#">EID Reports</a>
                </li>
                <li class="">
                    <a href="#">VL Reports</a>
                </li>
            @elseif(Auth()->user()->user_type_id == 2)
                    <li>
                        <a href="">Home</a>
                    </li>
                    <li>
                        <a href="{{ url('users') }}">Users</a>
                    </li>
                    <li>
                        <a href="{{ url('facility') }}">Facilities</a>
                    </li>
            @else
                @if(!Session('pendingTasks'))
                    <li class="">
                        <a class="label-menu-corner" href="{{ url('home') }}">
                        <i class="pe-7s-home" style="font-size: 25px;"></i>
                            <span class="label label-danger">
                            @if(session('testingSystem') == 'Viralload')
                                {{ $widgets['pendingSamples']['all']+$widgets['batchesForApproval']+$widgets['batchesNotReceived']+$widgets['batchesForDispatch']+$widgets['samplesForRepeat']+$widgets['rejectedForDispatch'] }}
                            @else
                                {{ $widgets['pendingSamples']+$widgets['batchesForApproval']+$widgets['batchesForDispatch']+$widgets['samplesForRepeat']+$widgets['rejectedForDispatch'] }}
                            @endif
                            </span>
                        </a>
                    </li>
                    <li class="">
                        <a href="
                            @if(session('testingSystem') == 'Viralload')
                                {{ url('viralbatch') }}
                            @else
                                {{ url('batch') }}
                            @endif">Samples</a>
                    </li>
                    
                    @if(session('testingSystem') != 'Viralload')
                    <li class="">
                        <a href="#">Requisitions</a>
                    </li>
                    @endif
                    
                    <li class="">
                        <a href="
                            @if(session('testingSystem') == 'Viralload')
                                {{ url('viralworksheet') }}
                            @else
                                {{ url('worksheet') }}
                            @endif">Worksheets</a>
                    </li>
                    <li class="">
                        <a href="
                            @if(session('testingSystem') == 'Viralload')
                                {{ url('viralbatch/index/1') }}
                            @else
                                {{ url('batch/index/1') }}
                            @endif">Dispatched Results</a>
                    </li>
                    <li class="">
                        <a href="{{ url('facility') }}">Facilities</a>
                    </li>
                    <li class="">
                        <a href="{{ route('reports') }}">Reports</a>
                    </li>
                    <li class="">
                        <a href="{{ route('dashboard') }}">Dashboard</a>
                    </li>
                    
                    @if(session('testingSystem') != 'Viralload')
                    <li class="">
                        <a href="#">Kits</a>
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