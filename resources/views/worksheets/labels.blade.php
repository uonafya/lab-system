<html>
<head>
	
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/dist/css/bootstrap.css') }}" />
	<link rel="stylesheet" type="text/css" href="{{ asset('css/worksheet_style.css') }}" media="screen" />


	<style type="text/css">
	    .breakhere {page-break-before: always}
	</style> 
</head>
<body onLoad="JavaScript:window.print();">
	<div class="container">
		@foreach($samples as $key => $sample)
			@if((($key % 2) == 2) || !in_array(env('APP_LAB'), [4]))
			<div class="row">
			@endif
				<div @if(in_array(env('APP_LAB'), [4])) class="col-md-6" @else class="col-md-12" @endif >
					<div align="center">
						@if(in_array(env('APP_LAB'), [5]))								
							<span style="font-size: 12px;">
								Date Ordered: {{ $sample->datecollected }} <br />
								Patient ID: {{ $sample->patient }} <br />
							</span>
						@endif
							<img src="data:image/png;base64,{{ DNS1D::getBarcodePNG($sample->id, 'C128') }}" alt="barcode" 
							@if(in_array(env('APP_LAB'), [5]))
								height="50" width="250"
							@else
								height="30" width="80"
							@endif
							   />
						<br />
						<span style="font-size: 12px;"> Lab ID: {{ $sample->id }} </span>
					</div>
				</div>
			@if((($key % 2) == 1) || !in_array(env('APP_LAB'), [4]))
			</div>
			@endif

			@if(!$loop->last)
				@if((($key % 2) == 1) || !in_array(env('APP_LAB'), [4]))
					<p class="breakhere"></p>
				@endif
			@endif
		@endforeach	
	</div>
</body>

<script src="{{ asset('vendor/jquery/dist/jquery.min.js') }}"></script>
<script src="{{ asset('vendor/jquery-ui/jquery-ui.min.js') }}"></script>
<script src="{{ asset('vendor/bootstrap/dist/js/bootstrap.min.js') }}"></script>
</html>