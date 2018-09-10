@if($sample->parentid)
	$parent = "- {$sample->parentid}";
	$rr = "
			<div align='right'> 
				<table>
					<tr>
						<td style='background-color:#FAF156'><small>R ({{ $sample->parentid }})</small></td>
					</tr>
				</table> 
			</div>
			";
@else
	$parent = "";
	$rr = "";

@endif

<td > 
	{!! $rr !!} 
	{{--<span class='style7'>Sample: {{ $sample->patient->patient }}  {{$parent}}</span><br>
						<b>Facility:</b> {{ $sample->batch->facility->name }} <br />
						<b>Sample ID:</b> {{ $sample->patient->patient }} <br />
						<b>Date Collected:</b> {{ $sample->my_date_format('datecollected') }} <br />--}}
	<span class='style7'>
		{{ $sample->batch->facility->name }} <br />
		{{ $sample->patient->patient }} - {{ $sample->my_date_format('datecollected') }} 
	</span>


	<img src="data:image/png;base64,{{ DNS1D::getBarcodePNG($sample->id, 'C39+') }}" alt="barcode" height="30" width="100"  />
	<br />
	{{ $sample->id }}
</td>