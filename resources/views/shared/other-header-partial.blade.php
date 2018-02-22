<table  class="table table-striped table-bordered table-hover">
	<thead>
		<tr>
			<th>Negative</th>
			<th>Positive</th>
			<th>Failed</th>
			<th>Redraw</th>
			<th>No Result</th>
			<th>Total</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td> {{ $subtotals['neg'] }} </td>
			<td> {{ $subtotals['pos'] }} </td>
			<td> {{ $subtotals['failed'] }} </td>
			<td> {{ $subtotals['redraw'] }} </td>
			<td> {{ $subtotals['noresult'] }} </td>
			<td> {{ $subtotals['total'] }} </td>
		</tr>

	</tbody>

</table>

<br />



<table  class="table table-striped table-bordered table-hover">

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

</table>