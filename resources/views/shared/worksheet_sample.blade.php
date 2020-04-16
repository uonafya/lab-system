<td > 
	@if($sample->parentid)
		<div align='right'  style='background-color:#FAF156'> 
			<small>R ({{ $sample->parentid }})</small>
		</div>
	@endif
	{{--<span class='style7'>Sample: {{ $sample->patient->patient }}  {{$parent}}</span><br>
						<b>Facility:</b> {{ $sample->batch->facility->name }} <br />
						<b>Sample ID:</b> {{ $sample->patient->patient }} <br />
						<b>Date Collected:</b> {{ $sample->my_date_format('datecollected') }} <br />--}}
	<span class='style7'
	@if(env('APP_LAB') == 5)
		style="font-size: 12px;" 
	@endif
	>
		<?php
			if(!$sample->batch){
				unset($sample->batch);
			}
		?>
		<b>{{ $sample->batch->facility->name ??  $sample->batch->facility_id ?? $sample->patient->facility->name ?? '' }}</b> 
		{{ $sample->patient->patient ?? $sample->patient->identifier ?? '' }}
		@if(env('APP_LAB') != 5) 
			<br /> Date Collected - {{ $sample->my_date_format('datecollected') }} 
		@endif 
		@if(env('APP_LAB') == 8)
			<br /> Label ID - {{ $sample->label_id }}
		@endif

		@if(env('APP_LAB') == 2 && get_class($worksheet) == "App\Worksheet")
			<br /> Date Received - {{ $sample->batch->my_date_format('datereceived') }} 
			<br /> Batch Number - {{ $sample->batch_id }} 
		@endif
		@if(env('APP_LAB') == 4 && $sample->parentid)
			<br /> Previous Worksheet - {{ $sample->prev_worksheet }}
		@endif
	</span>
	<br />

		&nbsp;&nbsp;&nbsp;

		@if(isset($covid))
			<img src="data:image/png;base64,{{ DNS1D::getBarcodePNG($sample->id, 'C39') }}" alt="barcode" height="30" width="40"  />
		@else
			<img src="data:image/png;base64,{{ DNS1D::getBarcodePNG($sample->id, 'C128') }}" alt="barcode" height="30" width="80"  />
		@endif

	<br />
	{{ $sample->id }}

	@if(in_array(env('APP_LAB'), [9, 2]))
		@if(env('APP_LAB') == 9)
			@if(get_class($worksheet) == "App\Viralworksheet")
				- ({{ $i+3 }})
			@else
				- ({{ $i+2 }})
			@endif
		@else
			- ({{ $i }})
		@endif
	@endif

</td>