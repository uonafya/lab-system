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
			<tr class="odd">
				<td colspan="3"><strong>WorkSheet Details</strong>	</td>
				<td colspan="2"><strong>Extraction Reagent</strong>	</td>
				<td colspan="3"><strong>Amplification Reagent</strong></td>
			</tr>
			<tr class="odd">
				
				<td> <strong>Worksheet/Template No</strong> </td>
				<td> {{ $worksheet->id }} </td>
				<td><strong>&nbsp;</strong>	</td>
				<td><strong>Sample Prep</strong>	</td>
				<td><strong>Bulk Lysis Buffer</strong>	</td>
				<td><strong>Control</strong>	</td>
				<td><strong>Calibrator</strong>	</td>
				<td><strong>Amplification Kit</strong>	</td>			
			</tr>
			<tr class="even">
				<td ><strong>Date Created</strong>		</td>
				<td > {{ $worksheet->created_at }} </td>
				<td><strong>Lot No	</strong>	</td>
				<td> {{ $worksheet->sample_prep_lot_no }} </td>
				<td> {{ $worksheet->bulklysis_lot_no }} </td>
				<td> {{ $worksheet->control_lot_no }} </td>
				<td> {{ $worksheet->calibrator_lot_no }} </td>
				<td> {{ $worksheet->amplification_kit_lot_no }} </td>
			</tr>
			<tr class="even">
				<td><strong>Created By	</strong>    </td>
				<td> {{ $worksheet->creator->full_name }} </td>
				<td><strong>Expiry Dates</strong>	</td>

				{{-- <td> {{ $worksheet->sampleprepexpirydate->toFormattedDateString()  }} </td>
				<td> {{ $worksheet->bulklysisexpirydate->toFormattedDateString() }} </td>
				<td> {{ $worksheet->controlexpirydate->toFormattedDateString() }} </td>
				<td> {{ $worksheet->calibratorexpirydate->toFormattedDateString()  }} </td>
				<td> {{ $worksheet->amplificationexpirydate->toFormattedDateString() }} </td> --}}

				<td> {{ $worksheet->sampleprepexpirydate }} </td>
				<td> {{ $worksheet->bulklysisexpirydate }} </td>
				<td> {{ $worksheet->controlexpirydate }} </td>
				<td> {{ $worksheet->calibratorexpirydate }} </td>
				<td> {{ $worksheet->amplificationexpirydate }} </td>
			</tr>
			<tr class="even">
				<td><strong>Sorted By	</strong>    </td>
				<td>_____________________________	</td>
				<td><strong>Bulked By	</strong>    </td>
				<td>_____________________________	</td>
				<td><strong>Run By	</strong>    </td>
				<td>_____________________________	</td>
			</tr>
			<tr >
				<th colspan="8" ><small> <strong> WORKSHEET SAMPLES [2 Controls]</strong></small>		</th>
			</tr>
		</table>
		<table border="0" class="data-table">

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
						<span class='style7'>Sample: {{ $sample->patient->patient }}  {{$parent}}</span><br> 

						<img src="data:image/png;base64,{{ DNS1D::getBarcodePNG($sample->id, 'C39+') }}" alt="barcode" height="30" width="100"  />
							&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp; 
						<br /> 
						{{ $sample->id or '' }}

					</td>



					@php $count++; @endphp

					@if($count % 8 == 0)
						</tr><tr><td colspan=8>&nbsp;</td></tr><tr>
					@endif
				@endforeach

				<td align=center > PC </td><td  align=center > NC </td>
			</tr>
				
		</table>
	</div>
</body>
</html>