<html>
<link rel="stylesheet" type="text/css" href="{{ asset('css/worksheet_style.css') }}" media="screen" />
<style type="text/css">
<!--
.style1 {font-family: "Courier New", Courier, monospace}
.style4 {font-size: 12}
.style5 {font-family: "Courier New", Courier, monospace; font-size: 12; }
.style7 {font-size: x-small}
-->
</style>
<style>

 td
 {

 }
 .oddrow
 {
 background-color : #CCCCCC;
 }
 .evenrow
 {
 background-color : #F0F0F0;
 } #table1 {
border : solid 1px black;
width:1100px;
width:1180px;
}
 /*.style7 {font-size: medium}*/
.style10 {font-size: 16px}
</style>

<STYLE TYPE="text/css">
     P.breakhere {page-break-before: always}

}

</STYLE> 
<body onLoad="JavaScript:window.print();">
	<div align="center">
		<table border="0" class="data-table" align='center'>
			@foreach($samples as $sample)
				<tr>
					<td >
						@if(in_array(env('APP_LAB'), [5]))
							<div align="center">
								<span style="font-size: 12px;">
									Date Ordered{{ $sample->datecollected }} <br />
									Patient ID: {{ $sample->patient }} <br />
								</span>
							</div>
						@endif
						<div align="center">
							<img align="middle" src="data:image/png;base64,{{ DNS1D::getBarcodePNG($sample->id, 'C128') }}" alt="barcode"
							@if(in_array(env('APP_LAB'), [5]))
								height="40" width="100"
							@else
								height="30" width="80"
							@endif
							   />
						</div>
						<br />
						<div align="center"> {{ $sample->id }} </div> 
					</td>
				</tr>
			@endforeach				
		</table>
	</div>
</body>
</html>