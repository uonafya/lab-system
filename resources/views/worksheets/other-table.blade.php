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
			<tr >
				<td class="comment style1 style4"> Worksheet No		</td>
				<td class="comment"> <span class="style5"> {{ $worksheet->id }} </span></td>
				<td class="comment style1 style4">Lot No </td>
				<td><span class="comment style1 style4"> {{ $worksheet->lot_no }}78 </span></td>
				<td><span class="style5">Date Cut </span></td>
				<td colspan="2">{{ $worksheet->datecut or '' }}</td>
			</tr>
			<tr >
				<td class="comment style1 style4"> Date Created		</td>
				<td class="comment" ><span class="style5">{{ $worksheet->created_at }}</span></td>
				<td class="comment style1 style4">HIQCAP Kit No</td>	
				<td><span class="comment style1 style4">{{ $worksheet->hiqcap_no }}</span></td>	
				<td><span class="style5">Reviewed By  </span></td>
				<td colspan="2">N/A</td>
			</tr>
			<tr >
				<td class="comment style1 style4"> Created By	    </td>
				<td class="comment"  ><span class="style5"> {{ $worksheet->creator->full_name }}</span></td>
				<td class="comment style1 style4"> Rack <strong>#</strong></td>
				<td><span class="comment style1 style4">{{ $worksheet->rack_no }}</span></td>
				<td><span class="style5">Date Reviewed</span></td>
				<td colspan="2">N/A</td>
		    </tr>
		    <tr ></tr>
			<tr >
				<td><span class="style5">Spek Kit No		</span></td>
				<td  colspan=""> <span class="style5">{{ $worksheet->spekkit_no }}</span> </td>
				<td><span class="style5">KIT EXP </span></td>
				<td><span class="style4">{{ $worksheet->kitexpirydate or '' }}</span></td>
				<td colspan="3">&nbsp;</td>
			</tr>
			<tr class="even">
				<td><strong>Sorted By	</strong>    </td>
				<td>_____________________________	</td>
						
				<td><strong>Run By	</strong>    </td>
				<td>_____________________________	</td>
			</tr>

			<tr>
				@php $count = 0; @endphp

				@foreach($samples as $sample)
					

					@php
						if($sample->parentid == 0){
							$parent = "";
							$rr = "";
						}else{
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
						}
					@endphp

					<td > 
						{{ $rr }} 
						<span class='style7'>Sample: {{ $sample->patient->patient or '' }}  {{$parent}}</span>
						<br /> 

						<img src="data:image/png;base64,{{ DNS1D::getBarcodePNG($sample->id, 'C39+') }}" alt="barcode" height="30" width="100" />
						<br /> 
						{{ $sample->id or '' }}

					</td>



					@php $count++; @endphp

					@if($count % 7 == 0)
						</tr><tr><td colspan=7>&nbsp;</td></tr><tr>
					@endif
				@endforeach

				<td  align=center colspan=2> PC </td><td  align=center colspan=3> NC </td>
			</tr>
				
		</table>

	</div>
</body>
</html>