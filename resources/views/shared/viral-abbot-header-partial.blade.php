<table  class="table table-striped table-bordered table-hover">
	<thead>
		<tr>
			<th>&lt;LDL </th>
			<th>Detected</th>
			<th>Failed</th>
			<th>No Result</th>
			<th>Total</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td> {{ $subtotals['undetected'] }} </td>
			<td> {{ $subtotals['detected'] }} </td>
			<td> {{ $subtotals['failed'] }} </td>
			<td> {{ $subtotals['noresult'] }} </td>
			<td> {{ $subtotals['total'] }} </td>
		</tr>

	</tbody>

</table>

<br />



<table  class="table table-striped table-bordered table-hover">

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

		<td> {{ $worksheet->sampleprepexpirydate->toFormattedDateString()  }} </td>
		<td> {{ $worksheet->bulklysisexpirydate->toFormattedDateString() }} </td>
		<td> {{ $worksheet->controlexpirydate->toFormattedDateString() }} </td>
		<td> {{ $worksheet->calibratorexpirydate->toFormattedDateString()  }} </td>
		<td> {{ $worksheet->amplificationexpirydate->toFormattedDateString() }} </td> 
	</tr>
	<tr class="even">
		<td><strong>Sorted By	</strong>    </td>
		<td>______________________	</td>
		<td><strong>Bulked By	</strong>    </td>
		<td>______________________	</td>
		<td><strong>Run By	</strong>    </td>
		<td>______________________	</td>
		<td></td>
		<td></td>
	</tr>

</table>