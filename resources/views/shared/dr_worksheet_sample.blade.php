

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

@endforeach