<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Page title -->
    <title>{{ config('app.name', 'EID/VL') }}</title>

    <link rel="stylesheet" href="{{ asset('vendor/fontawesome/css/font-awesome.css') }}" />
    <link rel="stylesheet" href="{{ asset('vendor/metisMenu/dist/metisMenu.css') }}" />
    <link rel="stylesheet" href="{{ asset('vendor/animate.css/animate.css') }}" />
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/dist/css/bootstrap.css') }}" />

    <link rel="stylesheet" href="{{ asset('fonts/pe-icon-7-stroke/css/pe-icon-7-stroke.css') }}" />
    <link rel="stylesheet" href="{{ asset('fonts/pe-icon-7-stroke/css/helper.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/style.css') }}" />

</head>
<body class="light-skin fixed-navbar sidebar-scroll">

<!-- Header -->
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
        <div class="mobile-menu">
            <button type="button" class="navbar-toggle mobile-menu-toggle" data-toggle="collapse" data-target="#mobile-collapse">
                <i class="fa fa-chevron-down"></i>
            </button>
            <div class="collapse mobile-navbar" id="mobile-collapse">
                <ul class="nav navbar-nav">
                    <li>
                        <a class="" href="#">Login</a>
                    </li>
                    <li>
                        <a class="" href="#">Logout</a>
                    </li>
                    <li>
                        <a class="" href="#">Profile</a>
                    </li>
                </ul>
            </div>
        </div>
        <div class="navbar-right">
            <ul class="nav navbar-nav no-borders">
                <li class="dropdown">
                    <a class="dropdown-toggle" href="#" data-toggle="dropdown">
                        <i class="pe-7s-speaker"></i>
                    </a>
                    <ul class="dropdown-menu hdropdown notification animated flipInX">
                        <li>
                            <a>
                                <span class="label label-success">NEW</span> It is a long established.
                            </a>
                        </li>
                        <li>
                            <a>
                                <span class="label label-warning">WAR</span> There are many variations.
                            </a>
                        </li>
                        <li>
                            <a>
                                <span class="label label-danger">ERR</span> Contrary to popular belief.
                            </a>
                        </li>
                        <li class="summary"><a href="#">See all notifications</a></li>
                    </ul>
                </li>
                <li class="dropdown">
                    <a class="dropdown-toggle" href="#" data-toggle="dropdown">
                        <i class="pe-7s-keypad"></i>
                    </a>

                    <div class="dropdown-menu hdropdown bigmenu animated flipInX">
                        <table>
                            <tbody>
                            <tr>
                                <td>
                                    <a href="projects.html">
                                        <i class="pe pe-7s-portfolio text-info"></i>
                                        <h5>Projects</h5>
                                    </a>
                                </td>
                                <td>
                                    <a href="mailbox.html">
                                        <i class="pe pe-7s-mail text-warning"></i>
                                        <h5>Email</h5>
                                    </a>
                                </td>
                                <td>
                                    <a href="contacts.html">
                                        <i class="pe pe-7s-users text-success"></i>
                                        <h5>Contacts</h5>
                                    </a>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <a href="forum.html">
                                        <i class="pe pe-7s-comment text-info"></i>
                                        <h5>Forum</h5>
                                    </a>
                                </td>
                                <td>
                                    <a href="analytics.html">
                                        <i class="pe pe-7s-graph1 text-danger"></i>
                                        <h5>Analytics</h5>
                                    </a>
                                </td>
                                <td>
                                    <a href="file_manager.html">
                                        <i class="pe pe-7s-box1 text-success"></i>
                                        <h5>Files</h5>
                                    </a>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </li>
                <li class="dropdown">
                    <a class="dropdown-toggle label-menu-corner" href="#" data-toggle="dropdown">
                        <i class="pe-7s-mail"></i>
                        <span class="label label-success">4</span>
                    </a>
                    <ul class="dropdown-menu hdropdown animated flipInX">
                        <div class="title">
                            You have 4 new messages
                        </div>
                        <li>
                            <a>
                                It is a long established.
                            </a>
                        </li>
                        <li>
                            <a>
                                There are many variations.
                            </a>
                        </li>
                        <li>
                            <a>
                                Lorem Ipsum is simply dummy.
                            </a>
                        </li>
                        <li>
                            <a>
                                Contrary to popular belief.
                            </a>
                        </li>
                        <li class="summary"><a href="#">See All Messages</a></li>
                    </ul>
                </li>
                <li>
                    <a href="#" id="sidebar" class="right-sidebar-toggle">
                        <i class="pe-7s-upload pe-7s-news-paper"></i>
                    </a>
                </li>
                <li class="dropdown">
                    <a href="login.html">
                        <i class="pe-7s-upload pe-rotate-90"></i>
                    </a>
                </li>
            </ul>
        </div>
    </nav>
</div>

<!-- Navigation -->
<aside id="menu">
    <div id="navigation">
        <ul class="nav" id="side-menu">
            <li class="active">
                <a href="index-2.html"> <span class="nav-label">Dashboard</span> <span class="label label-success pull-right">v.1</span> </a>
            </li>
            <li>
                <a href="analytics.html"> <span class="nav-label">Analytics</span><span class="label label-warning pull-right">NEW</span> </a>
            </li>
            <li>
                <a href="#"><span class="nav-label">Interface</span><span class="fa arrow"></span> </a>
                <ul class="nav nav-second-level">
                    <li><a href="panels.html">Panels design</a></li>
                    <li><a href="typography.html">Typography</a></li>
                    <li><a href="buttons.html">Colors &amp; Buttons</a></li>
                    <li><a href="components.html">Components</a></li>
                    <li><a href="alerts.html">Alerts</a></li>
                    <li><a href="modals.html">Modals</a></li>
                    <li><a href="loading_buttons.html">Loading buttons</a></li>
                    <li><a href="draggable.html">Draggable panels</a></li>
                    <li><a href="code_editor.html">Code editor</a></li>
                    <li><a href="email_template.html">Email template</a></li>
                    <li><a href="nestable_list.html">List</a></li>
                    <li><a href="tour.html">Tour</a></li>
                    <li><a href="icons.html">Icons library</a></li>
                </ul>
            </li>
            <li>
                <a href="#"><span class="nav-label">App views</span><span class="fa arrow"></span> </a>
                <ul class="nav nav-second-level">
                    <li><a href="contacts.html">Contacts</a></li>
                    <li><a href="projects.html">Projects</a></li>
                    <li><a href="project.html">Project detail</a></li>
                    <li><a href="app_plans.html">App plans</a></li>
                    <li><a href="social_board.html">Social board</a></li>
                    <li><a href="faq.html">FAQ</a></li>
                    <li><a href="timeline.html">Timeline</a></li>
                    <li><a href="notes.html">Notes</a></li>
                    <li><a href="profile.html">Profile</a></li>
                    <li><a href="mailbox.html">Mailbox</a></li>
                    <li><a href="mailbox_compose.html">Email compose</a></li>
                    <li><a href="mailbox_view.html">Email view</a></li>
                    <li><a href="blog.html">Blog</a></li>
                    <li><a href="blog_details.html">Blog article</a></li>
                    <li><a href="forum.html">Forum</a></li>
                    <li><a href="forum_details.html">Forum details</a></li>
                    <li><a href="gallery.html">Gallery</a></li>
                    <li><a href="calendar.html">Calendar</a></li>
                    <li><a href="invoice.html">Invoice</a></li>
                    <li><a href="file_manager.html">File manager</a></li>
                    <li><a href="chat_view.html">Chat view</a></li>
                    <li><a href="search.html">Search view</a></li>
                </ul>
            </li>
            <li>
                <a href="#"><span class="nav-label">Charts</span><span class="fa arrow"></span> </a>
                <ul class="nav nav-second-level">
                    <li><a href="chartjs.html">ChartJs</a></li>
                    <li><a href="flot.html">Flot charts</a></li>
                    <li><a href="inline.html">Inline graphs</a></li>
                    <li><a href="chartist.html">Chartist</a></li>
                    <li><a href="c3.html">C3 Charts</a></li>
                </ul>
            </li>
            <li>
                <a href="#"><span class="nav-label">Box transitions</span><span class="fa arrow"></span> </a>
                <ul class="nav nav-second-level">
                    <li><a href="overview.html"><span class="label label-success pull-right">Start</span> Overview </a>  </li>
                    <li><a href="transition_two.html">Columns from up</a></li>
                    <li><a href="transition_one.html">Columns custom</a></li>
                    <li><a href="transition_three.html">Panels zoom</a></li>
                    <li><a href="transition_four.html">Rows from down</a></li>
                    <li><a href="transition_five.html">Rows from right</a></li>
                </ul>
            </li>
            <li>
                <a href="#"><span class="nav-label">Common views</span><span class="fa arrow"></span> </a>
                <ul class="nav nav-second-level">
                    <li><a href="login.html">Login</a></li>
                    <li><a href="register.html">Register</a></li>
                    <li><a href="error_one.html">Error 404</a></li>
                    <li><a href="error_two.html">Error 505</a></li>
                    <li><a href="lock.html">Lock screen</a></li>
                    <li><a href="password_recovery.html">Passwor recovery</a></li>
                </ul>
            </li>
            <li>
                <a href="#"><span class="nav-label">Tables</span><span class="fa arrow"></span> </a>
                <ul class="nav nav-second-level">
                    <li><a href="tables_design.html">Tables design</a></li>
                    <li><a href="datatables.html">Data tables</a></li>
                    <li><a href="footable.html">Foo Table</a></li>

                </ul>
            </li>
            <li>
                <a href="widgets.html"> <span class="nav-label">Widgets</span> <span class="label label-success pull-right">Special</span></a>
            </li>
            <li>
                <a href="#"><span class="nav-label">Forms</span><span class="fa arrow"></span> </a>
                <ul class="nav nav-second-level">
                    <li><a href="forms_elements.html">Forms elements</a></li>
                    <li><a href="forms_extended.html">Forms extended</a></li>
                    <li><a href="text_editor.html">Text editor</a></li>
                    <li><a href="wizard.html">Wizard</a></li>
                    <li><a href="validation.html">Validation</a></li>
                </ul>
            </li>
            <li>
                <a href="options.html"> <span class="nav-label">Layout options</span></a>
            </li>
            <li>
                <a href="grid_system.html"> <span class="nav-label">Grid system</span></a>
            </li>
            <li>
                <a href="landing_page.html"> <span class="nav-label">Landing page</span></a>
            </li>
            <li>
                <a href="package.html"> <span class="nav-label">Package</span></a>
            </li>

        </ul>
    </div>
</aside>

<!-- Main Wrapper -->
<div id="wrapper">

    @yield('content')

    <!-- Right sidebar -->
    <div id="right-sidebar" class="animated fadeInRight">

        <div class="p-m">
            <button id="sidebar-close" class="right-sidebar-toggle sidebar-button btn btn-default m-b-md"><i class="pe pe-7s-close"></i>
            </button>
            <div>
                <span class="font-bold no-margins"> Analytics </span>
                <br>
                <small> Lorem Ipsum is simply dummy text of the printing simply all dummy text.</small>
            </div>
            <div class="row m-t-sm m-b-sm">
                <div class="col-lg-6">
                    <h3 class="no-margins font-extra-bold text-success">300,102</h3>

                    <div class="font-bold">98% <i class="fa fa-level-up text-success"></i></div>
                </div>
                <div class="col-lg-6">
                    <h3 class="no-margins font-extra-bold text-success">280,200</h3>

                    <div class="font-bold">98% <i class="fa fa-level-up text-success"></i></div>
                </div>
            </div>
            <div class="progress m-t-xs full progress-small">
                <div style="width: 25%" aria-valuemax="100" aria-valuemin="0" aria-valuenow="25" role="progressbar"
                     class=" progress-bar progress-bar-success">
                    <span class="sr-only">35% Complete (success)</span>
                </div>
            </div>
        </div>
        <div class="p-m bg-light border-bottom border-top">
            <span class="font-bold no-margins"> Social talks </span>
            <br>
            <small> Lorem Ipsum is simply dummy text of the printing simply all dummy text.</small>
            <div class="m-t-md">
                <div class="social-talk">
                    <div class="media social-profile clearfix">
                        <a class="pull-left">
                            <img src="images/a1.jpg" alt="profile-picture">
                        </a>

                        <div class="media-body">
                            <span class="font-bold">John Novak</span>
                            <small class="text-muted">21.03.2015</small>
                            <div class="social-content small">
                                Injected humour, or randomised words which don't look even slightly believable.
                            </div>
                        </div>
                    </div>
                </div>
                <div class="social-talk">
                    <div class="media social-profile clearfix">
                        <a class="pull-left">
                            <img src="images/a3.jpg" alt="profile-picture">
                        </a>

                        <div class="media-body">
                            <span class="font-bold">Mark Smith</span>
                            <small class="text-muted">14.04.2015</small>
                            <div class="social-content">
                                Many desktop publishing packages and web page editors.
                            </div>
                        </div>
                    </div>
                </div>
                <div class="social-talk">
                    <div class="media social-profile clearfix">
                        <a class="pull-left">
                            <img src="images/a4.jpg" alt="profile-picture">
                        </a>

                        <div class="media-body">
                            <span class="font-bold">Marica Morgan</span>
                            <small class="text-muted">21.03.2015</small>

                            <div class="social-content">
                                There are many variations of passages of Lorem Ipsum available, but the majority have
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="p-m">
            <span class="font-bold no-margins"> Sales in last week </span>
            <div class="m-t-xs">
                <div class="row">
                    <div class="col-xs-6">
                        <small>Today</small>
                        <h4 class="m-t-xs">$170,20 <i class="fa fa-level-up text-success"></i></h4>
                    </div>
                    <div class="col-xs-6">
                        <small>Last week</small>
                        <h4 class="m-t-xs">$580,90 <i class="fa fa-level-up text-success"></i></h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-xs-6">
                        <small>Today</small>
                        <h4 class="m-t-xs">$620,20 <i class="fa fa-level-up text-success"></i></h4>
                    </div>
                    <div class="col-xs-6">
                        <small>Last week</small>
                        <h4 class="m-t-xs">$140,70 <i class="fa fa-level-up text-success"></i></h4>
                    </div>
                </div>
            </div>
            <small> Lorem Ipsum is simply dummy text of the printing simply all dummy text.
                Many desktop publishing packages and web page editors.
            </small>
        </div>

    </div>

    <!-- Footer-->
    <footer class="footer">
        <center>&copy; NASCOP 2010 - {{ @Date('Y') }} | All Rights Reserved</center>
    </footer>

</div>

<script src="{{ asset('vendor/jquery/dist/jquery.min.js') }}"></script>
<script src="{{ asset('vendor/jquery-ui/jquery-ui.min.js') }}"></script>
<script src="{{ asset('vendor/slimScroll/jquery.slimscroll.min.js') }}"></script>
<script src="{{ asset('vendor/bootstrap/dist/js/bootstrap.min.js') }}"></script>
<script src="{{ asset('vendor/jquery-flot/jquery.flot.js') }}"></script>
<script src="{{ asset('vendor/jquery-flot/jquery.flot.resize.js') }}"></script>
<script src="{{ asset('vendor/jquery-flot/jquery.flot.pie.js') }}"></script>
<script src="{{ asset('vendor/flot.curvedlines/curvedLines.js') }}"></script>
<script src="{{ asset('vendor/jquery.flot.spline/index.js') }}"></script>
<script src="{{ asset('vendor/metisMenu/dist/metisMenu.min.js') }}"></script>
<script src="{{ asset('vendor/iCheck/icheck.min.js') }}"></script>
<script src="{{ asset('vendor/peity/jquery.peity.min.js') }}"></script>
<script src="{{ asset('vendor/sparkline/index.js') }}"></script>
<script src="{{ asset('js/homer.js') }}"></script>
<script src="{{ asset('js/charts.js') }}"></script>

<script>

    $(function () {

        /**
         * Flot charts data and options
         */
        var data1 = [ [0, 55], [1, 48], [2, 40], [3, 36], [4, 40], [5, 60], [6, 50], [7, 51] ];
        var data2 = [ [0, 56], [1, 49], [2, 41], [3, 38], [4, 46], [5, 67], [6, 57], [7, 59] ];

        var chartUsersOptions = {
            series: {
                splines: {
                    show: true,
                    tension: 0.4,
                    lineWidth: 1,
                    fill: 0.4
                },
            },
            grid: {
                tickColor: "#f0f0f0",
                borderWidth: 1,
                borderColor: 'f0f0f0',
                color: '#6a6c6f'
            },
            colors: [ "#62cb31", "#efefef"],
        };

        $.plot($("#flot-line-chart"), [data1, data2], chartUsersOptions);

        /**
         * Flot charts 2 data and options
         */
        var chartIncomeData = [
            {
                label: "line",
                data: [ [1, 10], [2, 26], [3, 16], [4, 36], [5, 32], [6, 51] ]
            }
        ];

        var chartIncomeOptions = {
            series: {
                lines: {
                    show: true,
                    lineWidth: 0,
                    fill: true,
                    fillColor: "#64cc34"

                }
            },
            colors: ["#62cb31"],
            grid: {
                show: false
            },
            legend: {
                show: false
            }
        };

        $.plot($("#flot-income-chart"), chartIncomeData, chartIncomeOptions);



    });

</script>
</body>
</html>