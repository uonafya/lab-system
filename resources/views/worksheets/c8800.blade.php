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
 .style7 {font-size: medium}
.style10 {font-size: 16px}
</style>

<STYLE TYPE="text/css">
     P.breakhere {page-break-before: always}

}

</STYLE> 
<body 
	@isset($print)
		onLoad="JavaScript:window.print();"
	@endisset
>
	<div align="center">

		<table border="0" class="data-table">

			<tr>
				<td><b>Worksheet No</b></td>
				<td> {{ $worksheet->id }} </td>
				<td><b>Created By</b></td>
				<td> {{ $worksheet->creator->full_name ?? '' }} </td>
				<td></td>
			</tr>

			<tr>
				<td><b>CDC Worksheet No</b></td>
				<td> {{ $worksheet->cdcworksheetno }} </td>
				<td><b>Date Created</b></td>
				<td> {{ $worksheet->my_date_format('created_at') }} </td>
				<td></td>
			</tr>
			<tr>
				<td colspan="5"><b>WORKSHEET SAMPLES [3 Controls]</b></td>
			</tr>
			<tr>
				<td><b>Lab ID</b></td>
				<td><b>Lab ID</b></td>
				<td><b>CCC No</b></td>
				<td><b>Facility</b></td>
				<td><b>Date Collected</b></td>
			</tr>

			@foreach($samples->where('parentid', '!=', 0) as $sample)

				@php
					$parent = "- {$sample->parentid}";
					$rr = "
							<div align='right'> 
								<table>
									<tr>
										<td style='background-color:#FAF156'><small>R </small></td>
									</tr>
								</table> 
							</div>
							";
				@endphp

				<tr>
					<td> {{ $sample->id }} </td>
					<td>
						{!! $rr !!} 
						<img src="data:image/png;base64,{{ DNS1D::getBarcodePNG($sample->id, 'C39+') }}" alt="barcode" height="30" width="100"  />						
					</td>
					<td> {{ $sample->patient->patient }} </td>
					<td> {{ $sample->batch->facility->name }} </td>
					<td> {{ $sample->my_date_format('datecollected') }} </td>
				</tr>

			@endforeach

			@foreach($samples->where('parentid', 0) as $sample)
				<tr>
					<td> {{ $sample->id }} </td>
					<td>
						<img src="data:image/png;base64,{{ DNS1D::getBarcodePNG($sample->id, 'C39+') }}" alt="barcode" height="30" width="100"  />						
					</td>
					<td> {{ $sample->patient->patient }} </td>
					<td> {{ $sample->batch->facility->name }} </td>
					<td> {{ $sample->my_date_format('datecollected') }} </td>
				</tr>

			@endforeach

				
		</table>
	</div>
</body>
</html>