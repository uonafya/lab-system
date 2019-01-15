

		@if($column == 1)
			<tr>
		@endif

@if($sample)
	@foreach($primers as $primer_key => $primer)
				<td>
					<?php
						$col = $primer_key+1;
						if($column == 2) $col += 6;

						if($col < 10) $col = '0' . $col;

						$bar = $sample->mid . '-Seq' . $primer . '_' . $rows[$row] . $col . '_' . $date_created;
						echo $bar;
					?>
					<br />

					&nbsp;&nbsp;&nbsp;<img src="data:image/png;base64,{{ DNS1D::getBarcodePNG($bar, 'C128') }}" alt="barcode" height="30" width="80"  />
				</td>
		@if($column == 2)
			</tr>
		@endif

	@endforeach

@else
	
	@if($row == 7)
			<td>Water</td>
			<td>Water</td>
			<td>Water</td>
			<td>Water</td>
			<td>Water</td>
			<td>pGEM</td>
	@elseif($row == 6)
			<td>Positive <br /> Control</td>
			<td>Positive <br /> Control</td>
			<td>Positive <br /> Control</td>
			<td>Positive <br /> Control</td>
			<td>Positive <br /> Control</td>
			<td>Positive <br /> Control</td>
	@else	
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
	@endif

@endif

		@if($column == 2)
			</tr>
		@endif