<style type="text/css">
    .navbar.navbar-static-top a, .nav.navbar-nav li a {
        color: black;
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
        <!-- <div>
            <button>Switch to {{ session()->pull('sys_name') }}</button>
        </div> -->
        <div class="mobile-menu">
            <button type="button" class="navbar-toggle mobile-menu-toggle" data-toggle="collapse" data-target="#mobile-collapse">
                <i class="fa fa-chevron-down"></i>
            </button>
            <div class="collapse mobile-navbar" id="mobile-collapse">
                <ul class="nav navbar-nav">
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
                                {{ url('viralworksheet/index/3') }}
                            @else
                                {{ url('worksheet/index/3') }}
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
                <li class="">
                    <a class="label-menu-corner" href="{{ url('home') }}">
                    <i class="pe-7s-home" style="font-size: 25px;"></i>
                        <span class="label label-danger">
                        @if(session('testingSystem') == 'Viralload')
                            {{ $widgets['pendingSamples']['all']+$widgets['batchesForApproval'][0]->totalsamples+$widgets['batchesNotReceived']+$widgets['batchesForDispatch']+$widgets['samplesForRepeat']+$widgets['rejectedForDispatch'] }}
                        @else
                            {{ $widgets['pendingSamples']+$widgets['batchesForApproval'][0]->totalsamples+$widgets['batchesForDispatch']+$widgets['samplesForRepeat']+$widgets['rejectedForDispatch'][0]->rejectfordispatch }}
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
                            {{ url('viralworksheet/index/3') }}
                        @else
                            {{ url('worksheet/index/3') }}
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