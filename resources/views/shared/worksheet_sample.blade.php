<td > 
	@if($sample->parentid)
		<div align='right'> 
			<table>
				<tr>
					<td style='background-color:#FAF156'><small>R ({{ $sample->parentid }})</small></td>
				</tr>
			</table> 
		</div>
	@endif
	{{--<span class='style7'>Sample: {{ $sample->patient->patient }}  {{$parent}}</span><br>
						<b>Facility:</b> {{ $sample->batch->facility->name }} <br />
						<b>Sample ID:</b> {{ $sample->patient->patient }} <br />
						<b>Date Collected:</b> {{ $sample->my_date_format('datecollected') }} <br />--}}
	<span class='style7'>
		<?php
			if(!$sample->batch){
				unset($sample->batch);
			}
		?>
		{{ $sample->batch->facility->name ??  $sample->batch->facility_id }} <br />
		{{ $sample->patient->patient }} - {{ $sample->my_date_format('datecollected') }} 
	</span>
	<br />

	@if($worksheet->machine_type == 1)
		&nbsp;&nbsp;&nbsp;<img src="data:image/png;base64,{{ DNS1D::getBarcodePNG($sample->id, 'C128') }}" alt="barcode" height="30" width="80"  />
	@else
		&nbsp;&nbsp;&nbsp;<img src="data:image/png;base64,{{ DNS1D::getBarcodePNG($sample->id, 'C39+') }}" alt="barcode" height="30" width="80"  />
	@endif

	<br />
	{{ $sample->id }}
</td>