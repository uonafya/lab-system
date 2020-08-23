<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Page title -->
    <title>NHCSC</title>

    <!-- Vendor styles -->
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome/css/font-awesome.css') }}" />
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
	<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/bs4-4.1.1/dt-1.10.21/datatables.min.css"/>

    <link rel="stylesheet" href="{{ asset('css/toastr/toastr.min.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('css/select2/select2.min.css') }}" type="text/css">
    <link href="{{ asset('css/datapicker/datepicker3.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('css/summernote/summernote.css') }}" rel="stylesheet" type="text/css">

    <style type="text/css">
    	.bg-purple{
    		color: #fff;
    		background-color: #e38fe3;
    	}
    </style>

</head>
<body>
	<header class="sticky-top">
		<div class="container-fluid navbar-brand border-bottom bg-purple">
			<h3 class="text-center">NHCSC Uliza-NASCOP PLATFORM</h3>
		</div>

		<div class="d-flex flex-column flex-md-row align-items-center p-1 px-md-4 mb-3 border-bottom box-shadow bg-white">
			<div class="text-center mr-md-auto">
				<h4>
					Welcome, Timothy Ngugi  - NASCOP TWG |
					Super Admin
				</h4>
			</div>

			<nav class="my-2 my-md-0 mr-md-3 text-uppercase">
				<a class="p-2" routerlinkactive="active" href="/ulizaplatform/cases">Cases</a>
				<a class="p-2" routerlinkactive="active" href="/ulizaplatform/kisianlabs">Lab</a><a class="p-2" routerlinkactive="active" href="/ulizaplatform/reports">Reports</a>
				<a class="p-2" routerlinkactive="active" href="/ulizaplatform/dashboard">DashBoard</a>
				<a class="p-2" routerlinkactive="active" href="/ulizaplatform/users">Users</a>
			</nav>
			<a class="btn btn-outline-primary">Sign Out</a>
		</div>
	</header>


	<div class="container-fluid">
		<div class="row">
			@yield('content')
		</div>		
	</div>

	<!-- <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script> -->
	<script src="https://code.jquery.com/jquery-3.5.1.min.js" integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
	<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>

	<script src="{{ asset('js/toastr/toastr.min.js') }}"></script>
    <script src="{{ asset('js/datapicker/bootstrap-datepicker.js') }}"></script>
    <script src="{{ asset('js/summernote/summernote.js') }}"></script>
	<script src="{{ asset('js/select2/select2.full.min.js') }}"></script>
	<script src="{{ asset('js/validate/jquery.validate.min.js') }}"></script>


	<script type="text/javascript">	    
	    $(document).ready(function(){
	        $.ajaxSetup({
	            headers: {
	                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
	            }
	        });

	        @php
	            $toast_message = session()->pull('toast_message');
	            $toast_error = session()->pull('toast_error');
	        @endphp
        
	        @if($toast_message)
	            setTimeout(function(){
	                toastr.options = {
	                    closeButton: false,
	                    progressBar: false,
	                    showMethod: 'slideDown',
	                    timeOut: 10000
	                };
	                @if($toast_error)
	                    toastr.error("{!! $toast_message !!}", "Warning!");
	                @else
	                    toastr.success("{!! $toast_message !!}");
	                @endif
	            });
	        @endif

        });
    </script>

	@yield('scripts')
</body>
</html>