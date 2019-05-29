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
    {{--<link rel="stylesheet" href="{{ asset('vendor/fontawesome/css/font-awesome.css') }}" />
    <link rel="stylesheet" href="{{ asset('vendor/animate.css/animate.css') }}" />
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/dist/css/bootstrap.css') }}" /> --}}

    <link rel="stylesheet" href="http://lab.test.nascop.org/vendor/fontawesome/css/font-awesome.css" />
    <link rel="stylesheet" href="http://lab.test.nascop.org/vendor/animate.css/animate.css" />
    <link rel="stylesheet" href="http://lab.test.nascop.org/vendor/bootstrap/dist/css/bootstrap.css'" />

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
<!-- <div id="wrapper"> -->

    <!-- <div class="content"> -->

        <div class="row">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th> No </th>
                        @if($type == 'eid')
                            <th> HEI Number </th>
                        @else
                            <th> CCC Number </th>
                        @endif
                        <th> Batch </th>
                        <th> Date Collected </th>
                        <th> Date Received </th>
                        <th> Date Tested </th>
                        <th> Date Dispatched </th>
                        <th> Result </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($samples as $key => $sample)
                        <tr>
                            <td> {{ $key+1 }} </td>
                            <td> {{ $sample->patient }} </td>
                            <td> {{ $sample->batch_id }} </td>
                            <td> {{ $sample->my_date_format('datecollected') }} </td>
                            <td> {{ $sample->my_date_format('datereceived') }} </td>
                            <td> {{ $sample->my_date_format('datetested') }} </td>
                            <td> {{ $sample->my_date_format('datedispatched') }} </td>
                            <td> {{ $sample->result_name ?? $sample->result }} </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>            
        </div>

        <br />
        <br />
        <br />
        <br />

    <!-- </div> -->

    <!-- Footer-->
    <footer class="footer">
        <center>&copy; NASCOP 2010 - {{ @Date('Y') }} | All Rights Reserved</center>
    </footer>

<!-- </div> -->

{{--<script src="{{ asset('vendor/jquery/dist/jquery.min.js') }}"></script>
<script src="{{ asset('vendor/jquery-ui/jquery-ui.min.js') }}"></script>
<script src="{{ asset('vendor/bootstrap/dist/js/bootstrap.min.js') }}"></script>
<script src="{{ asset('vendor/iCheck/icheck.min.js') }}"></script>--}}

<script src="http://lab.test.nascop.org/vendor/jquery/dist/jquery.min.js"></script>
<script src="http://lab.test.nascop.org/vendor/jquery-ui/jquery-ui.min.js"></script>
<script src="http://lab.test.nascop.org/vendor/bootstrap/dist/js/bootstrap.min.js"></script>
<script src="http://lab.test.nascop.org/vendor/iCheck/icheck.min.js"></script>


</body>
</html>
