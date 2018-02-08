<table border="0" class="data-table">
	<tr >
		<td class="comment style1 style4"> Worksheet No		</td>
		<td class="comment"> <span class="style5"> {{ $worksheet->id }} </span></td>
		<td class="comment style1 style4">Lot No </td>
		<td><span class="comment style1 style4"> {{ $worksheet->lot_no }}78 </span></td>
		<td><span class="style5">Date Cut </span></td>
		<td colspan="2">{{ $worksheet->datecut->toFormattedDateString() or '' }}</td>
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
		<td><span class="style4">{{ $worksheet->kitexpirydate->toFormattedDateString() or '' }}</span></td>
		<td colspan="3">&nbsp;</td>
	</tr>
	<tr class="even">
		<td><strong>Sorted By	</strong>    </td>
		<td>_____________________________	</td>
				
		<td><strong>Run By	</strong>    </td>
		<td>_____________________________	</td>
	</tr>

	<tr>
		@php $count = 1; @endphp

		@foreach($samples as $sample)
			@if($sample->parentid != 0)

				- {{ $sample->parentid }}
				<div align='right'> 
					<table>
						<tr>
							<td style='background-color:#FAF156'><small>R </small></td>
						</tr>
					</table> 
				</div>
			@else

			@endif

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
				{{ $RR }} 
				<span class='style7'>Sample: {{ $sample->patient->patient }}  {{$parent}}</span><br> 

				<img src="data:image/png;base64,{{DNS1D::getBarcodePNG($sample->id, 'C39+', 3, 33, [255, 255, 255], true)}}" alt="barcode" />
					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp; 

			</td>



			@php $count++; @endphp

			@if($count % 7 == 0)
				</tr><tr><td colspan=7>&nbsp;</td></tr><tr>
			@endif
		@endforeach

		<td  align=center colspan=2> PC </td><td  align=center colspan=3> NC </td>
	</tr>
		
</table>