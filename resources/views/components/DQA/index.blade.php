<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta http-equiv="Content-Language" content="en">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Analytics Dashboard - This is an example dashboard created using build-in elements and components.</title>
    <meta name="viewport"
        content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, shrink-to-fit=no">
    <meta name="description" content="This is an example dashboard created using build-in elements and components.">

    <meta name="msapplication-tap-highlight" content="no">
    <link href="{{ asset('css/main.d810cf0ae7f39f28f336.css') }}" rel="stylesheet">
</head>

<body>
    
    <div class="app-container app-theme-white body-tabs-shadow fixed-header fixed-sidebar">
        
        {{-- <div class="app-main">
            <div class="app-main__outer"> --}}
                <div class="app-main__inner" style="margin:  2%;" >
                    <div class="tabs-animation">
                    
                        <div class="row">
                            <div class="col-md-6 col-xl-3">
                                <div
                                    class="card mb-3 widget-chart widget-chart2 text-left card-btm-border card-shadow-success border-success">
                                    <div class="widget-chat-wrapper-outer">
                                        <div class="widget-chart-content pt-3 pl-3 pb-1">
                                            <div class="widget-chart-flex">
                                                <div class="widget-numbers">
                                                    <div class="widget-chart-flex">
                                                        <div class="fsize-4">
                                                            <small class="opacity-5">ccc-no</small> <br>
                                                            <span> 10, 900 </span>
                                                            
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <h6 class="widget-subheading mb-0 opacity-5">Incorrect CCC-Number as of MAY-2022-01 </h6>
                                           
                                            <button style="margin: 10%" class="mb-2 mr-2 btn-pill btn-transition btn btn-outline-primary">See this ccc-no's</button>
                                            
                                        </div>
                                        <div class="no-gutters widget-chart-wrapper mt-3 mb-3 pl-2 he-auto row">
                                            <div class="col-md-9">
                                                <div id="dashboard-sparklines-1"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-xl-3">
                                <div
                                    class="card mb-3 widget-chart widget-chart2 text-left card-btm-border card-shadow-primary border-primary">
                                    <div class="widget-chat-wrapper-outer">
                                        <div class="widget-chart-content pt-3 pl-3 pb-1">
                                            <div class="widget-chart-flex">
                                                <div class="widget-numbers">
                                                    <div class="widget-chart-flex">
                                                        <div class="fsize-4">
                                                            <small class="opacity-5">Batches </small> <br>
                                                            <span> 900 </span>
                                                            
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <h6 class="widget-subheading mb-0 opacity-5">Batches with duplicates </h6>
                                           
                                            <button style="margin: 10%" class="mb-2 mr-2 btn-pill btn-transition btn btn-outline-primary">Batches  duplicates</button>
                                            
                                        </div>
                                        <div class="no-gutters widget-chart-wrapper mt-3 mb-3 pl-2 he-auto row">
                                            <div class="col-md-9">
                                                <div id="dashboard-sparklines-2"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-xl-3">
                                <div
                                    class="card mb-3 widget-chart widget-chart2 text-left card-btm-border card-shadow-warning border-warning">
                                    <div class="widget-chat-wrapper-outer">
                                        <div class="widget-chart-content pt-3 pl-3 pb-1">
                                            <div class="widget-chart-flex">
                                                <div class="widget-numbers">
                                                    <div class="widget-chart-flex">
                                                        <div class="fsize-4">
                                                            <small class="opacity-5">Samples</small> <br>
                                                            <span> 10, 900 </span>
                                                            
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <h6 class="widget-subheading mb-0 opacity-5">Samples with Duplicate </h6>
                                           
                                            <button style="margin: 10%" class="mb-2 mr-2 btn-pill btn-transition btn btn-outline-primary">Samples  Duplicate</button>
                                            
                                        </div>
                                        <div class="no-gutters widget-chart-wrapper mt-3 mb-3 pl-2 he-auto row">
                                            <div class="col-md-9">
                                                <div id="dashboard-sparklines-3"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-xl-3">
                                <div
                                    class="card mb-3 widget-chart widget-chart2 text-left card-btm-border card-shadow-danger border-danger">
                                    <div class="widget-chat-wrapper-outer">
                                        <div class="widget-chart-content pt-3 pl-3 pb-1">
                                            <div class="widget-chart-flex">
                                                <div class="widget-numbers">
                                                    <div class="widget-chart-flex">
                                                        <div class="fsize-4">
                                                            <small class="opacity-5">Facilities</small> <br>
                                                            <span> 1,900 </span>
                                                            
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <h6 class="widget-subheading mb-0 opacity-5">Duplicate Facilities</h6>
                                           
                                            <button style="margin: 10%" class="mb-2 mr-2 btn-pill btn-transition btn btn-outline-primary">Duplicate Facilities</button>
                                            
                                        </div>
                                        <div class="no-gutters widget-chart-wrapper mt-3 mb-3 pl-2 he-auto row">
                                            <div class="col-md-9">
                                                <div id="dashboard-sparklines-4"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        
                        
                    </div>
                   
                </div>
                
            </div>
        {{-- </div>
    </div> --}}
    
    <div class="app-drawer-overlay d-none animated fadeIn"></div>
    <script type="text/javascript" src="{{ asset('assets/scripts/main.d810cf0ae7f39f28f336.js')}}"></script>
</body>

</html>
