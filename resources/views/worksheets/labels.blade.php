<html>
<head>
	
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" type="text/css" href="{{ asset('css/worksheet_style.css') }}" media="screen" />


	<style type="text/css">
	    P.breakhere {page-break-before: always}
	</style> 
</head>
<body onLoad="JavaScript:window.print();">
	<div align="center">
		<!-- <table border="0" class="data-table"> -->
			@foreach($samples as $sample)
				<!-- <tr> -->
					<!-- <td > -->
						@if(in_array(env('APP_LAB'), [5]))
							<!-- <div align="center"> -->
								<span style="font-size: 12px;">
									Date Ordered: {{ $sample->datecollected }} <br />
									Patient ID: {{ $sample->patient }} <br />
								</span>
							<!-- </div> -->
						@endif
						<!-- <div align="center"> -->
							<img src="data:image/png;base64,{{ DNS1D::getBarcodePNG($sample->id, 'C128') }}" alt="barcode"
							@if(in_array(env('APP_LAB'), [5]))
								height="50" width="250"
							@else
								height="30" width="80"
							@endif
							   />
						<!-- </div> -->
						<br />
						<!-- <div align="center" style="font-size: 12px;"> </div>  -->
						<span style="font-size: 12px;"> Lab ID: {{ $sample->id }} </span>
					<!-- </td> -->
				<!-- </tr> -->

				@if(!$loop->last)
					<p class="breakhere"></p>
				@endif
			@endforeach				
		<!-- </table> -->
	</div>
</body>
</html>