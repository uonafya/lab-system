<!DOCTYPE html>
<html>
<head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Page title -->
    <title>EID/VL | LAB</title>

    <!-- Place favicon.ico and apple-touch-icon.png in the root directory -->
    <!--<link rel="shortcut icon" type="image/ico" href="favicon.ico" />-->

    <!-- Vendor styles -->
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome/css/font-awesome.css') }}" />
    <link rel="stylesheet" href="{{ asset('vendor/animate.css/animate.css') }}" />
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/dist/css/bootstrap.css') }}" />

    <style type="text/css">
        body.light-skin #menu {
            width: 240px;
        }
        #wrapper {
            margin: 0px 0px 0px 230px;
        }
        #toast-container > div {
            color: black;
        }
        .navbar-nav>li>a {
            padding: 15px 15px;
            font-size: 13px;
            color: black;
        }
        .btn {
            padding: 4px 8px;
            font-size: 12px;
        }
        .hpanel {
            margin-bottom: 4px;
        }
        .hpanel.panel-heading {
            padding-bottom: 2px;
            padding-top: 4px;
        }
    </style>

</head>
<!-- <body class="light-skin fixed-navbar sidebar-scroll"> -->
<body>

<!-- Main Wrapper -->
<div id="wrapper">

    <div class="content">

        <div class="row">
            <div class="col-lg-12">
                <div class="hpanel">
                    <div class="panel-body" style="padding-bottom: 6px;">

                        <div class="alert alert-warning">
                            <center>
                                Please fill the form correctly. <br />
                                Fields with an asterisk(*) are mandatory.
                            </center>
                        </div>
                        <br />

                        <input type="hidden" value=0 name="patient_id" id="patient_id">

   
                        <div class="form-group">
                            <label class="col-sm-4 control-label">Facility 
                                <strong><div style='color: #ff0000; display: inline;'>*</div></strong>
                            </label>

                            <div class="col-sm-8">
                                <input class="form-control requirable" required name="patient" type="text" value="
                                {{ $sample->patient->facility->facilitycode }} {{ $sample->patient->facility->name }}
                                " id="patient">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-4 control-label">Patient / Sample ID
                                <strong><div style='color: #ff0000; display: inline;'>*</div></strong>
                            </label>
                            <div class="col-sm-8">
                                <input class="form-control requirable" required name="patient" type="text" value="{{ $sample->patient->patient ?? '' }}" id="patient">
                            </div>
                        </div>

                        <div class="hr-line-dashed"></div>

                        <div class="form-group">
                            <label class="col-sm-4 control-label">Reason for DR test
                                <strong><div style='color: #ff0000; display: inline;'>*</div></strong>
                            </label>
                            <div class="col-sm-8">
                                <input class="form-control requirable" required name="dr_reasons" type="text" value="{{ $drug_resistance_reasons->where('id', $sample->dr_reason_id)->first()->name ?? '' }}" id="dr_reasons">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-4 control-label">Previous Regimen</label>
                            <div class="col-sm-8">
                                <input class="form-control" name="prev_prophylaxis" type="text" value="" id="prev_prophylaxis">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-4 control-label">Current Regimen
                                <strong><div style='color: #ff0000; display: inline;'>*</div></strong>
                            </label>
                            <div class="col-sm-8">
                                <input class="form-control requirable" required name="prophylaxis" type="text" value="" id="prophylaxis">
                            </div>
                        </div>

                        <div class="hr-line-dashed"></div>                        

                        <div class="form-group">
                            <label class="col-sm-4 control-label">Date Started on Previous Regimen</label>
                            <div class="col-sm-8">
                                <input class="form-control" name="date_prev_regimen" type="text" value="" id="date_prev_regimen">
                            </div>                          
                        </div>                      

                        <div class="form-group">
                            <label class="col-sm-4 control-label">Date Started on Current Regimen</label>
                            <div class="col-sm-8">
                                <input class="form-control" name="date_current_regimen" type="text" value="" id="date_current_regimen">
                            </div>                          
                        </div>

                        <div class="form-group">
                            <label class="col-sm-4 control-label">Date of Collection
                                <strong><div style='color: #ff0000; display: inline;'>*</div></strong>
                            </label>
                            <div class="col-sm-8">
                                <input class="form-control" name="datecollected" type="text" value="" id="datecollected">
                            </div>                           
                        </div>

                    </div>
                </div>
            </div>
        </div>

    </div>



    <!-- Footer-->
    <footer class="footer">
        <center>&copy; NASCOP 2010 - {{ @Date('Y') }} | All Rights Reserved</center>
    </footer>

</div>

<script src="{{ asset('vendor/jquery/dist/jquery.min.js') }}"></script>
<script src="{{ asset('vendor/jquery-ui/jquery-ui.min.js') }}"></script>
<script src="{{ asset('vendor/bootstrap/dist/js/bootstrap.min.js') }}"></script>

</body>
</html>
